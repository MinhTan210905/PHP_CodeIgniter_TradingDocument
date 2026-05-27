<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lấy danh sách mong muốn của 1 user
     */
    public function get_by_user($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('book_wishlists')->result_array();
    }

    /**
     * Đếm số mong muốn của user
     */
    public function count_by_user($user_id) {
        return $this->db->where('user_id', $user_id)->count_all_results('book_wishlists');
    }

    /**
     * Thêm sách mong muốn (giới hạn 10/user)
     * @return bool|string true nếu thành công, chuỗi lỗi nếu thất bại
     */
    public function add($user_id, $book_title) {
        // Giới hạn 10 mong muốn/user
        if ($this->count_by_user($user_id) >= 10) {
            return 'Bạn đã đạt giới hạn tối đa 10 mong muốn. Vui lòng xóa bớt trước khi thêm mới.';
        }

        // Kiểm tra trùng lặp (so sánh không phân biệt hoa thường)
        $existing = $this->db->where('user_id', $user_id)
                             ->where('LOWER(book_title)', mb_strtolower($book_title, 'UTF-8'))
                             ->get('book_wishlists')
                             ->row();
        if ($existing) {
            return 'Tên sách này đã có trong danh sách mong muốn của bạn.';
        }

        $this->db->insert('book_wishlists', [
            'user_id'    => $user_id,
            'book_title' => trim($book_title),
            'is_active'  => 1,
        ]);
        return true;
    }

    /**
     * Bật/tắt thông báo cho 1 mong muốn
     */
    public function toggle($id, $user_id) {
        $row = $this->db->where('id', $id)->where('user_id', $user_id)->get('book_wishlists')->row_array();
        if (!$row) return false;

        $new_status = $row['is_active'] ? 0 : 1;
        $this->db->where('id', $id)->update('book_wishlists', ['is_active' => $new_status]);
        return $new_status;
    }

    /**
     * Xóa mong muốn
     */
    public function delete($id, $user_id) {
        $this->db->where('id', $id);
        $this->db->where('user_id', $user_id);
        return $this->db->delete('book_wishlists');
    }

    /**
     * Lấy tất cả wishlist đang bật (dùng cho Cron quét)
     * JOIN users để lấy email + tên
     */
    public function get_all_active() {
        $this->db->select('book_wishlists.*, users.email, users.full_name');
        $this->db->from('book_wishlists');
        $this->db->join('users', 'users.id = book_wishlists.user_id', 'inner');
        $this->db->where('book_wishlists.is_active', 1);
        return $this->db->get()->result_array();
    }

    /**
     * Cập nhật ID bài đăng cuối cùng đã thông báo (tránh gửi trùng lặp)
     */
    public function update_last_notified($id, $post_id) {
        $this->db->where('id', $id);
        return $this->db->update('book_wishlists', ['last_notified_post_id' => $post_id]);
    }

    /**
     * Quét và gửi thông báo wishlist theo thời gian thực cho một bài đăng cụ thể
     */
    public function notify_wishlist_for_post($post_id) {
        // 1. Lấy thông tin bài đăng mới được duyệt
        $this->db->select('posts.*, users.full_name as seller_name');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->where('posts.id', $post_id);
        $this->db->where('posts.status', 'available');
        $post = $this->db->get()->row_array();

        if (empty($post)) return;

        // 2. Lấy tất cả wishlist đang hoạt động
        $wishlists = $this->get_all_active();
        if (empty($wishlists)) return;

        $post_title_lower = mb_strtolower(trim($post['title']), 'UTF-8');

        foreach ($wishlists as $wish) {
            // Không tự gợi ý sách chính mình đăng
            if ((int)$post['user_id'] === (int)$wish['user_id']) continue;

            // Không gửi trùng
            if ($wish['last_notified_post_id'] && (int)$post['id'] <= (int)$wish['last_notified_post_id']) continue;

            $wish_title_lower = mb_strtolower(trim($wish['book_title']), 'UTF-8');

            // So khớp thông minh
            if ($this->check_title_match($wish_title_lower, $post_title_lower)) {
                $price_formatted = number_format($post['price'], 0, ',', '.') . 'đ';
                $detail_url = site_url('trade/detail/' . $post['id']);

                // B. Gửi email thông báo HTML
                $this->_send_wishlist_email(
                    $wish['email'],
                    $wish['full_name'],
                    $wish['book_title'],
                    $post['title'],
                    $price_formatted,
                    $post['seller_name'] ?? 'Người bán',
                    $detail_url
                );

                // Cập nhật bài cuối đã thông báo
                $this->update_last_notified($wish['id'], $post['id']);
            }
        }
    }

    /**
     * So khớp thông minh tiếng Việt không dấu và tỉ lệ trùng lặp từ (>70%)
     */
    public function check_title_match($wish_title, $post_title) {
        $wish = mb_strtolower(trim($wish_title), 'UTF-8');
        $post = mb_strtolower(trim($post_title), 'UTF-8');

        // 1. Chứa trực tiếp
        if (mb_strpos($post, $wish) !== false || mb_strpos($wish, $post) !== false) {
            return true;
        }

        // 2. Chuyển đổi bỏ dấu diacritics
        $wish_no_accent = $this->remove_accents($wish);
        $post_no_accent = $this->remove_accents($post);

        // Kiểm tra chứa trực tiếp sau khi bỏ dấu
        if (mb_strpos($post_no_accent, $wish_no_accent) !== false || mb_strpos($wish_no_accent, $post_no_accent) !== false) {
            return true;
        }

        // 3. Tính độ trùng lặp từ ghép tiếng Việt (Word overlap)
        $wish_words = array_filter(explode(' ', $wish_no_accent));
        $post_words = array_filter(explode(' ', $post_no_accent));

        if (empty($wish_words) || empty($post_words)) {
            return false;
        }

        $intersect = array_intersect($wish_words, $post_words);
        $match_percent = (count($intersect) / count($wish_words)) * 100;

        if ($match_percent >= 70) {
            return true;
        }

        // 4. Fallback dùng similar_text trên chuỗi bỏ dấu
        $sim_percent = 0;
        similar_text($wish_no_accent, $post_no_accent, $sim_percent);
        if ($sim_percent >= 70) {
            return true;
        }

        return false;
    }

    /**
     * Hàm loại bỏ dấu tiếng Việt chuẩn xác
     */
    public function remove_accents($str) {
        $accents_map = [
            'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
            'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
            'ì','í','ị','ỉ','ĩ',
            'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
            'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
            'ỳ','ý','ỵ','ỷ','ỹ',
            'đ',
            'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
            'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
            'Ì','Í','Ị','Ỉ','Ĩ',
            'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
            'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
            'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
            'Đ'
        ];
        $non_accents_map = [
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y',
            'd',
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y',
            'd'
        ];
        return str_replace($accents_map, $non_accents_map, $str);
    }

    /**
     * Gửi email HTML thông báo sách phù hợp
     */
    private function _send_wishlist_email($to_email, $to_name, $wish_title, $post_title, $price, $seller_name, $detail_url) {
        $this->load->library('email');
        $this->email->clear();
        
        $smtp_user = $this->config->item('smtp_user');
        if (empty($smtp_user)) {
            $smtp_user = getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : 'no-reply@hcmue.edu.vn');
        }
        $this->email->from($smtp_user, 'HCMUE BookSwap');
        $this->email->to($to_email);
        $this->email->subject('[HCMUE BookSwap] Sách bạn đang tìm đã có người đăng bán!');

        $full_name = htmlspecialchars($to_name);
        $wish_esc  = htmlspecialchars($wish_title);
        $post_esc  = htmlspecialchars($post_title);
        $seller_esc = htmlspecialchars($seller_name);

        $html = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
                <tr><td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:560px;width:100%;'>
                        <tr>
                            <td style='background:linear-gradient(135deg,#003F8A,#0052B4);padding:28px 36px;text-align:center;'>
                                <h1 style='margin:0;color:#ffffff;font-size:20px;font-weight:800;'>📚 HCMUE BookSwap</h1>
                                <p style='margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:12px;'>Thông báo sách phù hợp với mong muốn của bạn</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:32px 36px 24px;'>
                                <p style='margin:0 0 14px;font-size:15px;color:#374151;'>Xin chào <strong>{$full_name}</strong>,</p>
                                <p style='margin:0 0 20px;font-size:14px;color:#6B7280;line-height:1.7;'>
                                     Hệ thống phát hiện có bài đăng mới phù hợp với mong muốn <strong>\"{$wish_esc}\"</strong> của bạn:
                                </p>
                                <div style='background:#F0F5FF;border:1px solid #DBEAFE;border-radius:12px;padding:20px;margin:0 0 24px;'>
                                    <p style='margin:0 0 8px;font-size:16px;font-weight:700;color:#1E3A8A;'>{$post_esc}</p>
                                    <p style='margin:0 0 4px;font-size:13px;color:#6B7280;'>Giá: <strong style='color:#DC2626;'>{$price}</strong></p>
                                    <p style='margin:0;font-size:13px;color:#6B7280;'>Người bán: <strong>{$seller_esc}</strong></p>
                                </div>
                                <a href='{$detail_url}' style='display:inline-block;background:linear-gradient(135deg,#003F8A,#0052B4);color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px;'>Xem chi tiết sách</a>
                            </td>
                        </tr>
                        <tr>
                            <td style='background:#F8FAFC;padding:16px 36px;border-top:1px solid #E5E7EB;text-align:center;'>
                                <p style='margin:0;font-size:11px;color:#9CA3AF;'>Bạn nhận email này vì đã đăng ký theo dõi sách trên HCMUE BookSwap.<br>Để tắt thông báo, vui lòng vào <a href='" . site_url('wishlist') . "' style='color:#2563EB;'>Danh sách mong muốn</a> và tắt theo dõi.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body></html>";

        $this->email->message($html);
        $this->email->send();
    }
}
