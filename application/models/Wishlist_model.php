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
}
