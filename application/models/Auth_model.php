<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Tìm user theo email để đăng nhập
    public function get_user_by_email($email) {
        return $this->db->get_where('users', ['email' => $email])->row_array();
    }

    // Tìm user theo username (kiểm tra trùng khi đăng ký)
    public function get_user_by_username($username) {
        return $this->db->get_where('users', ['username' => $username])->row_array();
    }

    // Đăng ký tài khoản mới
    public function create_user($data) {
        return $this->db->insert('users', $data);
    }

    // Lấy thông tin user theo ID
    public function get_user_by_id($id) {
        return $this->db->get_where('users', ['id' => $id])->row_array();
    }

    // Cập nhật thông tin cá nhân
    public function update_user($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    // Bật / tắt hiển thị SĐT
    public function toggle_phone_visible($id, $current) {
        $this->db->where('id', $id);
        return $this->db->update('users', ['phone_visible' => $current ? 0 : 1]);
    }

    // Xóa tài khoản
    public function delete_user($id) {
        $this->db->where('id', $id);
        return $this->db->delete('users');
    }
}
