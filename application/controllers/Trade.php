<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Upload $upload
 * @property CI_DB_query_builder $db
 * @property Trade_model $Trade_model
 * @property Comment_model $Comment_model
 * @property Rating_model $Rating_model
 * @property Message_model $Message_model
 * @property Order_model $Order_model
 * @property Setting_model $Setting_model
 */
class Trade extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Trade_model', 'Comment_model', 'Rating_model', 'Message_model', 'Order_model', 'Setting_model']);
        $this->load->helper(['form', 'url']);
        $this->load->library(['session', 'upload']);
    }

    // Helper: Kiểm tra đăng nhập
    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
            redirect('auth');
        }
    }

    // Helper: Kiểm tra admin
    private function require_admin() {
        if ($this->session->userdata('role') !== 'admin') {
            show_error('Bạn không có quyền thực hiện thao tác này.', 403);
        }
    }

    // Trang chủ - Hiển thị danh sách
    public function index() {
        $category_id = $this->input->get('cat');
        $keyword     = $this->input->get('q');

        $data['posts']      = $this->Trade_model->get_all_posts($category_id, $keyword);
        $data['categories'] = $this->Trade_model->get_categories();
        $data['active_cat'] = $category_id;
        $data['keyword']    = $keyword;

        $data['unread_count'] = 0;
        $data['pending_count'] = 0;
        if ($this->session->userdata('logged_in')) {
            $uid = $this->session->userdata('user_id');
            $data['unread_count']  = $this->Message_model->count_unread($uid);
            $data['pending_count'] = $this->Order_model->count_pending_for_seller($uid);
        }

        $this->load->view('partials/header', $data);
        $this->load->view('home', $data);
        $this->load->view('partials/footer');
    }

    // Chi tiết bài đăng + bình luận
    public function detail($id) {
        $data['post']       = $this->Trade_model->get_post_detail($id);
        $data['categories'] = $this->Trade_model->get_categories();
        $data['comments']   = $this->Comment_model->get_comments_by_post($id);
        $data['additional_images'] = $this->Trade_model->get_post_images($id);

        if (!$data['post']) {
            show_404();
        }

        $data['has_rated'] = FALSE;
        $data['post_reviews'] = $this->Rating_model->get_ratings_for_post($id);
        $data['post_rating_stats'] = $this->Rating_model->get_avg_rating_for_post($id);
        if ($this->session->userdata('logged_in')) {
            $uid = $this->session->userdata('user_id');
            $data['has_rated']     = $this->Rating_model->has_rated($uid, $id);
            $data['unread_count']  = $this->Message_model->count_unread($uid);
            $data['pending_count'] = $this->Order_model->count_pending_for_seller($uid);
        } else {
            $data['unread_count']  = 0;
            $data['pending_count'] = 0;
        }

        $this->load->view('partials/header', $data);
        $this->load->view('post_detail', $data);
        $this->load->view('partials/footer');
    }

    // Xử lý tạo bài đăng + Upload ảnh
    public function create() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        
        $title    = $this->input->post('title', TRUE);
        $category = $this->input->post('category_id');
        $price    = $this->input->post('price');

        if (empty($title) || empty($category) || !isset($price)) {
            $this->session->set_flashdata('error', 'Vui lòng nhập tiêu đề, danh mục và giá sản phẩm!');
            redirect('trade');
            return;
        }

        // Cấu hình upload thư mục assets/uploads/
        $upload_dir = FCPATH . 'assets/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, TRUE);
        }

        $config['upload_path']   = $upload_dir;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size']      = 5120; // 5MB
        $config['encrypt_name']  = TRUE;

        $this->upload->initialize($config);
        
        $image_url = ''; // Rỗng để lúc hiển thị view sẽ tự fallback default_book.jpg

        // Kiểm tra xem user có chọn file upload không
        if (!empty($_FILES['image']['name'])) {
            if ($this->upload->do_upload('image')) {
                $uploadData = $this->upload->data();
                $image_url  = 'assets/uploads/' . $uploadData['file_name'];
            } else {
                // Nếu có chọn file nhưng lỗi (quá dung lượng, sai định dạng...)
                $this->session->set_flashdata('error', 'Lỗi upload ảnh: ' . strip_tags($this->upload->display_errors()));
                redirect('trade');
                return;
            }
        }

        $auto_approve = ($this->Setting_model->get('auto_approve_new') === '1');
        $final_status = ($this->session->userdata('role') === 'admin' || $auto_approve) ? 'available' : 'pending';

        $ai_analysis = null;
        if ($auto_approve && $this->session->userdata('role') !== 'admin') {
            $this->load->model('Ai_moderation_model');
            $full_text = $this->input->post('title', TRUE) . "\n" . $this->input->post('description', TRUE);
            $ai_analysis = $this->Ai_moderation_model->analyze_text($full_text);
            
            if ($ai_analysis['action'] === 'block') {
                $final_status = 'pending';
            }
        }

        $post_data = [
            'user_id'     => $user_id,
            'category_id' => $this->input->post('category_id'),
            'title'       => $this->input->post('title', TRUE),
            'description' => $this->input->post('description', TRUE),
            'price'       => $this->input->post('price'),
            'quantity'    => max(1, (int) $this->input->post('quantity')),
            'image_url'   => $image_url,
            'status'      => $final_status
        ];

        $this->Trade_model->insert_post($post_data);
        $post_id = $this->db->insert_id();

        if ($ai_analysis) {
            $this->Ai_moderation_model->log_moderation('post', $post_id, $user_id, $full_text, $ai_analysis);
        }

        // UPLOAD NHIỀU ẢNH CHI TIẾT (Multi-Image Flow)
        if ($post_id && !empty($_FILES['additional_images']['name'][0])) {
            $filesCount = count($_FILES['additional_images']['name']);
            // Giới hạn tối đa 5 ảnh chi tiết để tối ưu lưu trữ
            $limitCount = min($filesCount, 5);

            for ($i = 0; $i < $limitCount; $i++) {
                if (empty($_FILES['additional_images']['name'][$i])) continue;

                $_FILES['tmp_file']['name']     = $_FILES['additional_images']['name'][$i];
                $_FILES['tmp_file']['type']     = $_FILES['additional_images']['type'][$i];
                $_FILES['tmp_file']['tmp_name'] = $_FILES['additional_images']['tmp_name'][$i];
                $_FILES['tmp_file']['error']    = $_FILES['additional_images']['error'][$i];
                $_FILES['tmp_file']['size']     = $_FILES['additional_images']['size'][$i];

                $this->upload->initialize($config);

                if ($this->upload->do_upload('tmp_file')) {
                    $fileData = $this->upload->data();
                    $this->db->insert('post_images', [
                        'post_id'   => $post_id,
                        'image_url' => 'assets/uploads/' . $fileData['file_name']
                    ]);
                }
            }
        }

        if ($final_status === 'available') {
            $this->session->set_flashdata('success', '✅ Đăng bài thành công!');
        } else {
            $this->session->set_flashdata('success', '✅ Đăng bài thành công! Bài của bạn đang chờ Admin duyệt.');
        }
        
        redirect('trade');
    }

    // Chuyển trạng thái Đã Pass / Còn sách — chỉ chủ bài hoặc admin
    public function update_status($id, $status = 'sold') {
        $this->require_login();
        $post = $this->Trade_model->get_post_by_id($id);

        if (!$post) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $role    = $this->session->userdata('role');

        if ($post['user_id'] != $user_id && $role !== 'admin') {
            $this->session->set_flashdata('error', 'Bạn không có quyền thực hiện thao tác này!');
            redirect('profile');
            return;
        }

        $allowed_status = ['sold', 'available'];
        if (!in_array($status, $allowed_status)) {
            $status = 'sold';
        }

        $this->Trade_model->update_status($id, $status);
        $msg = $status === 'sold' ? 'Đã chuyển trạng thái Đã Pass thành công!' : 'Đã khôi phục trạng thái Còn sách!';
        $this->session->set_flashdata('success', $msg);
        redirect('profile');
    }

    // Xóa bài đăng — chỉ chủ bài hoặc admin
    public function delete($id) {
        $this->require_login();
        $post = $this->Trade_model->get_post_by_id($id);

        if (!$post) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $role    = $this->session->userdata('role');

        if ($post['user_id'] != $user_id && $role !== 'admin') {
            $this->session->set_flashdata('error', 'Bạn không có quyền xóa bài này!');
            redirect('trade');
            return;
        }

        $this->Trade_model->delete_post($id);
        $this->session->set_flashdata('success', 'Đã xóa bài đăng!');
        redirect('trade');
    }

    // Form chỉnh sửa bài đăng
    public function edit($id) {
        $this->require_login();
        $post = $this->Trade_model->get_post_by_id($id);

        if (!$post) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $role    = $this->session->userdata('role');

        // Check ownership
        if ($post['user_id'] != $user_id && $role !== 'admin') {
            $this->session->set_flashdata('error', 'Bạn không có quyền sửa bài này!');
            redirect('trade');
            return;
        }

        $data['post']       = $post;
        $data['categories'] = $this->Trade_model->get_categories();
        $data['additional_images'] = $this->Trade_model->get_post_images($id);
        $data['unread_count']  = $this->Message_model->count_unread($user_id);
        $data['pending_count'] = $this->Order_model->count_pending_for_seller($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('edit_post', $data);
        $this->load->view('partials/footer');
    }

    // Xử lý lưu chỉnh sửa bài đăng
    public function update($id) {
        $this->require_login();
        $post = $this->Trade_model->get_post_by_id($id);

        if (!$post) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $role    = $this->session->userdata('role');

        if ($post['user_id'] != $user_id && $role !== 'admin') {
            $this->session->set_flashdata('error', 'Không có quyền thực hiện!');
            redirect('trade');
            return;
        }

        $title       = $this->input->post('title', TRUE);
        $category_id = $this->input->post('category_id');
        $price       = $this->input->post('price');
        $quantity    = max(1, (int)$this->input->post('quantity'));

        if (empty($title) || empty($category_id)) {
            $this->session->set_flashdata('error', 'Tiêu đề và Danh mục không được để trống!');
            redirect('trade/edit/' . $id);
            return;
        }

        $update_data = [
            'title'       => $title,
            'category_id' => $category_id,
            'description' => $this->input->post('description', TRUE),
            'price'       => $price,
            'quantity'    => $quantity
        ];

        // Kiểm tra xem có thay đổi thông tin nhạy cảm (tiêu đề, mô tả, ảnh) không
        $sensitive_changed = false;
        if ($post['title'] !== $title || $post['description'] !== $update_data['description']) {
            $sensitive_changed = true;
        }
        if (!empty($_FILES['image']['name']) || !empty($_FILES['additional_images']['name'][0])) {
            $sensitive_changed = true;
        }

        // Check and re-approve reset logic
        $auto_approve_edit = ($this->Setting_model->get('auto_approve_edit') === '1');
        $was_pending = false;

        $ai_analysis = null;
        if ($role !== 'admin' && $sensitive_changed) {
            if (!$auto_approve_edit) {
                $update_data['status'] = 'pending';
                $was_pending = true;
            } else {
                // Admin bật tự động duyệt, chạy AI kiểm duyệt thay thế
                $this->load->model('Ai_moderation_model');
                $full_text = $title . "\n" . $update_data['description'];
                $ai_analysis = $this->Ai_moderation_model->analyze_text($full_text);
                
                // Nhãn 3 (block) -> Cấm -> Đưa về chờ duyệt thủ công
                if ($ai_analysis['action'] === 'block') {
                    $update_data['status'] = 'pending';
                    $was_pending = true;
                }
            }
        }

        // Khởi tạo thư mục & cấu hình upload chung
        $upload_dir = FCPATH . 'assets/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, TRUE);

        $config['upload_path']   = $upload_dir;
        $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
        $config['max_size']      = 5120;
        $config['encrypt_name']  = TRUE;
        $this->upload->initialize($config);

        // 1. Xử lý upload ảnh bìa mới (nếu có)
        if (!empty($_FILES['image']['name'])) {
            if ($this->upload->do_upload('image')) {
                $up_data = $this->upload->data();
                $update_data['image_url'] = 'assets/uploads/' . $up_data['file_name'];
            }
        }

        // 2. Xử lý đăng THÊM ảnh chi tiết mới (Multi-Image Flow during Edit)
        if (!empty($_FILES['additional_images']['name'][0])) {
            $filesCount = count($_FILES['additional_images']['name']);
            $limitCount = min($filesCount, 5);

            for ($i = 0; $i < $limitCount; $i++) {
                if (empty($_FILES['additional_images']['name'][$i])) continue;

                $_FILES['tmp_file']['name']     = $_FILES['additional_images']['name'][$i];
                $_FILES['tmp_file']['type']     = $_FILES['additional_images']['type'][$i];
                $_FILES['tmp_file']['tmp_name'] = $_FILES['additional_images']['tmp_name'][$i];
                $_FILES['tmp_file']['error']    = $_FILES['additional_images']['error'][$i];
                $_FILES['tmp_file']['size']     = $_FILES['additional_images']['size'][$i];

                $this->upload->initialize($config);

                if ($this->upload->do_upload('tmp_file')) {
                    $fileData = $this->upload->data();
                    $this->db->insert('post_images', [
                        'post_id'   => $id,
                        'image_url' => 'assets/uploads/' . $fileData['file_name']
                    ]);
                }
            }
        }

        $this->Trade_model->update_post($id, $update_data);

        if ($ai_analysis) {
            $this->Ai_moderation_model->log_moderation('post', $id, $user_id, $full_text, $ai_analysis);
        }

        $msg = ($was_pending) 
               ? '✅ Cập nhật thành công! Bài đăng đang chờ duyệt lại do thay đổi nội dung.'
               : '✅ Cập nhật bài đăng thành công!';
        
        $this->session->set_flashdata('success', $msg);
        redirect('profile');
    }
}
