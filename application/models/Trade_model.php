<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Trade_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        // Tự động kiểm tra và thêm cột pdf_url vào bảng posts nếu chưa tồn tại
        try {
            if (!$this->db->field_exists('pdf_url', 'posts')) {
                $this->load->dbforge();
                $fields = [
                    'pdf_url' => [
                        'type' => 'VARCHAR',
                        'constraint' => '255',
                        'default' => NULL,
                        'null' => TRUE
                    ]
                ];
                $this->dbforge->add_column('posts', $fields);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Trade_model: không thể thêm cột pdf_url: ' . $e->getMessage());
        }
        
        // Tự động kiểm tra và thêm cột item_condition vào bảng posts nếu chưa tồn tại
        try {
            if (!$this->db->field_exists('item_condition', 'posts')) {
                $this->load->dbforge();
                $fields = [
                    'item_condition' => [
                        'type' => 'ENUM("new","used")',
                        'default' => 'used',
                        'null' => FALSE
                    ]
                ];
                $this->dbforge->add_column('posts', $fields);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Trade_model: không thể thêm cột item_condition: ' . $e->getMessage());
        }

        // Tự động cập nhật cột status để hỗ trợ trạng thái rejected nếu chưa có
        try {
            $query = $this->db->query("SHOW COLUMNS FROM posts LIKE 'status'");
            if ($query && $query->num_rows() > 0) {
                $row = $query->row_array();
                $type = $row['Type'];
                if (strpos($type, 'rejected') === false) {
                    $this->db->query("ALTER TABLE posts MODIFY COLUMN status ENUM('pending','available','sold','rejected') DEFAULT 'pending'");
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Trade_model: không thể cập nhật cột status của posts: ' . $e->getMessage());
        }
    }

    // Read: Lấy toàn bộ bài đăng còn hàng (trang chủ — không hiện sold)
    public function get_all_posts($filters = [])
    {
        $this->db->select('posts.*, users.username, users.full_name, users.phone, users.phone_visible, users.avatar,
            categories.category_name, categories.icon as cat_icon,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT ratings.id) as total_ratings,
            COUNT(DISTINCT comments.id) as comment_count');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->join('categories', 'categories.id = posts.category_id', 'left');
        $this->db->join('ratings', 'ratings.seller_id = posts.user_id', 'left');
        $this->db->join('comments', 'comments.post_id = posts.id', 'left');

        if (!empty($filters['category_id'])) {
            $this->db->where('posts.category_id', $filters['category_id']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $this->db->group_start();
            $this->db->like('posts.title', $keyword);
            
            // Tách từ để tìm kiếm tương đối (Match bất kỳ từ nào)
            $words = explode(' ', $keyword);
            if (count($words) > 1) {
                foreach ($words as $word) {
                    $word = trim($word);
                    if ($word !== '') {
                        $this->db->or_like('posts.title', $word);
                        $this->db->or_like('posts.description', $word);
                    }
                }
            } else {
                $this->db->or_like('posts.description', $keyword);
            }
            $this->db->group_end();
        }
        
        // Lọc theo tình trạng
        if (!empty($filters['condition'])) {
            $this->db->where('posts.item_condition', $filters['condition']);
        }
        // Lọc theo khoảng giá
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $this->db->where('posts.price >=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $this->db->where('posts.price <=', $filters['max_price']);
        }

        // Chỉ hiện bài CÒN HÀNG trên trang chủ
        $this->db->where('posts.status', 'available');

        // Group by vì có hàm tổng hợp
        $this->db->group_by([
            'posts.id', 
            'posts.user_id', 
            'posts.category_id', 
            'posts.title', 
            'posts.description', 
            'posts.price', 
            'posts.quantity', 
            'posts.image_url', 
            'posts.pdf_url', 
            'posts.item_condition', 
            'posts.status', 
            'posts.created_at', 
            'users.username', 
            'users.full_name', 
            'users.phone', 
            'users.phone_visible', 
            'users.avatar',
            'categories.category_name', 
            'categories.icon'
        ]);
        
        // HAVING để lọc theo Rating/Shop yêu thích sau khi GROUP BY
        $having_clauses = [];
        if (!empty($filters['shop_type']) && $filters['shop_type'] === 'favorite') {
            $having_clauses[] = 'avg_rating >= 4.0';
        }
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $having_clauses[] = 'avg_rating >= ' . (float)$filters['rating'];
        }
        if (count($having_clauses) > 0) {
            $this->db->having(implode(' AND ', $having_clauses));
        }

        // Sắp xếp
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'popular':
                    // Sắp xếp theo số bình luận + đánh giá
                    $this->db->order_by('(COUNT(DISTINCT comments.id) + COUNT(DISTINCT ratings.id))', 'DESC');
                    break;
                case 'relevance':
                    // Sắp xếp theo mức độ liên quan (ưu tiên tiêu đề khớp chính xác/ngắn hơn lên trước)
                    $this->db->order_by('LENGTH(posts.title)', 'ASC');
                    break;
                case 'price_asc':
                    $this->db->order_by('posts.price', 'ASC');
                    break;
                case 'price_desc':
                    $this->db->order_by('posts.price', 'DESC');
                    break;
                case 'latest':
                default:
                    $this->db->order_by('posts.created_at', 'DESC');
                    break;
            }
        } else {
            $this->db->order_by('posts.created_at', 'DESC');
        }

        return $this->db->get()->result_array();
    }

    // Lấy tất cả bài đã duyệt (bao gồm cả đang rao và đã pass) dành cho Admin
    public function get_all_approved_posts()
    {
        $this->db->select('posts.*, users.username, users.full_name, users.phone, users.phone_visible,
            categories.category_name, categories.icon as cat_icon,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT ratings.id) as total_ratings,
            COUNT(DISTINCT comments.id) as comment_count');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->join('categories', 'categories.id = posts.category_id', 'left');
        $this->db->join('ratings', 'ratings.seller_id = posts.user_id', 'left');
        $this->db->join('comments', 'comments.post_id = posts.id', 'left');

        $this->db->where_in('posts.status', ['available', 'sold']);

        $this->db->group_by(['posts.id', 'posts.user_id', 'posts.category_id', 'posts.title', 'posts.description', 'posts.price', 'posts.quantity', 'posts.image_url', 'posts.status', 'posts.created_at', 'users.username', 'users.full_name', 'users.phone', 'users.phone_visible', 'categories.category_name', 'categories.icon']);
        $this->db->order_by('posts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Tìm kiếm bao gồm cả sách đã hết hàng (sold)
    public function search_posts($keyword = NULL, $category_id = NULL) {
        $this->db->select('posts.*, users.username, users.full_name, users.avatar,
            categories.category_name, categories.icon as cat_icon,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT ratings.id) as total_ratings');
        $this->db->from('posts');
        $this->db->join('users',      'users.id       = posts.user_id',    'left');
        $this->db->join('categories', 'categories.id  = posts.category_id','left');
        $this->db->join('ratings',    'ratings.seller_id = posts.user_id', 'left');
        if ($keyword) {
            $keyword = trim($keyword);
            $this->db->group_start();
            $this->db->like('posts.title', $keyword);
            
            // Tách từ để tìm kiếm tương đối (Match bất kỳ từ nào)
            $words = explode(' ', $keyword);
            if (count($words) > 1) {
                foreach ($words as $word) {
                    $word = trim($word);
                    if ($word !== '') {
                        $this->db->or_like('posts.title', $word);
                        $this->db->or_like('posts.description', $word);
                    }
                }
            } else {
                $this->db->or_like('posts.description', $keyword);
            }
            $this->db->group_end();
        }
        if ($category_id) {
            $this->db->where('posts.category_id', $category_id);
        }
        // Chỉ hiện bài đã duyệt (available + sold)
        $this->db->where_in('posts.status', ['available', 'sold']);
        $this->db->group_by(['posts.id', 'posts.user_id', 'posts.category_id', 'posts.title', 'posts.description', 'posts.price', 'posts.quantity', 'posts.image_url', 'posts.status', 'posts.created_at', 'users.username', 'users.full_name', 'users.avatar', 'categories.category_name', 'categories.icon']);
        $this->db->order_by('posts.status', 'ASC'); // available trước, sold sau
        $this->db->order_by('posts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy chi tiết 1 bài đăng + bình luận
    public function get_post_detail($id)
    {
        $this->db->select('posts.*, users.username, users.full_name, users.phone, users.phone_visible, users.id as seller_id, users.avatar,
            categories.category_name,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT ratings.id) as total_ratings');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->join('categories', 'categories.id = posts.category_id', 'left');
        $this->db->join('ratings', 'ratings.seller_id = posts.user_id', 'left');
        $this->db->where('posts.id', $id);
        $this->db->group_by(['posts.id', 'posts.user_id', 'posts.category_id', 'posts.title', 'posts.description', 'posts.price', 'posts.quantity', 'posts.image_url', 'posts.status', 'posts.created_at', 'users.username', 'users.full_name', 'users.phone', 'users.phone_visible', 'users.id', 'users.avatar', 'categories.category_name']);
        return $this->db->get()->row_array();
    }

    // Lấy danh sách ảnh phụ của bài viết
    public function get_post_images($post_id) {
        $this->db->where('post_id', $post_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('post_images')->result_array();
    }

    public function get_categories()
    {
        return $this->db->get('categories')->result_array();
    }

    public function get_category_by_id($id)
    {
        return $this->db->get_where('categories', ['id' => $id])->row_array();
    }

    public function insert_category($data)
    {
        return $this->db->insert('categories', $data);
    }

    public function update_category($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('categories', $data);
    }

    public function delete_category($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('categories');
    }

    // Create: Thêm bài đăng mới
    public function insert_post($data)
    {
        return $this->db->insert('posts', $data);
    }

    // Update: Trừ số lượng khi pass, tự chuyển sold nếu hết
    public function decrement_quantity($post_id, $qty = 1) {
        $post = $this->get_post_by_id($post_id);
        if (!$post) return false;
        $new_qty = max(0, (int)$post['quantity'] - $qty);
        $new_status = ($new_qty <= 0) ? 'sold' : 'available';
        $this->db->where('id', $post_id);
        return $this->db->update('posts', ['quantity' => $new_qty, 'status' => $new_status]);
    }

    // Update: Cộng lại số lượng khi hủy đơn, tự chuyển available nếu trước đó là sold
    public function increment_quantity($post_id, $qty = 1) {
        $post = $this->get_post_by_id($post_id);
        if (!$post) return false;
        $new_qty = (int)$post['quantity'] + $qty;
        $new_status = ($post['status'] === 'sold') ? 'available' : $post['status'];
        $this->db->where('id', $post_id);
        return $this->db->update('posts', ['quantity' => $new_qty, 'status' => $new_status]);
    }

    // Update: Chuyển trạng thái thủ công
    public function update_status($id, $status) {
        $this->db->where('id', $id);
        return $this->db->update('posts', ['status' => $status]);
    }

    // Update: Chỉnh sửa thông tin bài viết generic
    public function update_post($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('posts', $data);
    }

    // Delete: Xóa bài đăng kèm dọn dẹp file vật lý
    public function delete_post($id)
    {
        // 1. Lấy thông tin bài đăng
        $post = $this->get_post_by_id($id);
        if ($post) {
            // 2. Lấy danh sách ảnh phụ
            $additional_images = $this->get_post_images($id);

            // 3. Xóa ảnh bìa chính (tránh xóa ảnh mặc định default.png)
            if (!empty($post['image_url']) && $post['image_url'] !== 'assets/uploads/default.png') {
                $main_img_path = FCPATH . $post['image_url'];
                if (file_exists($main_img_path)) {
                    @unlink($main_img_path);
                }
            }

            // 4. Xóa file PDF đọc thử nếu có
            if (!empty($post['pdf_url'])) {
                $pdf_path = FCPATH . $post['pdf_url'];
                if (file_exists($pdf_path)) {
                    @unlink($pdf_path);
                }
            }

            // 5. Xóa tất cả ảnh phụ từ uploads
            if (!empty($additional_images)) {
                foreach ($additional_images as $img) {
                    if (!empty($img['image_url'])) {
                        $sub_img_path = FCPATH . $img['image_url'];
                        if (file_exists($sub_img_path)) {
                            @unlink($sub_img_path);
                        }
                    }
                }
            }
        }

        // 6. Xóa từ DB (sẽ cascade tự động xóa hàng post_images, comments, v.v.)
        $this->db->where('id', $id);
        return $this->db->delete('posts');
    }

    // Lấy bài đăng theo ID (dùng để kiểm tra quyền sở hữu)
    public function get_post_by_id($id)
    {
        return $this->db->get_where('posts', ['id' => $id])->row_array();
    }

    // Lấy bài đăng của 1 user cụ thể
    public function get_posts_by_user($user_id)
    {
        $this->db->select('posts.*, categories.category_name,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT comments.id) as comment_count');
        $this->db->from('posts');
        $this->db->join('categories', 'categories.id = posts.category_id', 'left');
        $this->db->join('ratings', 'ratings.seller_id = posts.user_id', 'left');
        $this->db->join('comments', 'comments.post_id = posts.id', 'left');
        $this->db->where('posts.user_id', $user_id);
        $this->db->group_by(['posts.id', 'posts.user_id', 'posts.category_id', 'posts.title', 'posts.description', 'posts.price', 'posts.quantity', 'posts.image_url', 'posts.status', 'posts.created_at', 'categories.category_name']);
        $this->db->order_by('posts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }
    // Lấy bài đang chờ duyệt (admin)
    public function get_pending_posts()
    {
        $this->db->select('posts.*, users.username, users.full_name, categories.category_name');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->join('categories', 'categories.id = posts.category_id', 'left');
        $this->db->where('posts.status', 'pending');
        $this->db->order_by('posts.created_at', 'ASC');
        return $this->db->get()->result_array();
    }

    // Lấy tổng số bài chờ duyệt
    public function count_pending()
    {
        return $this->db->where('status', 'pending')->count_all_results('posts');
    }
}
