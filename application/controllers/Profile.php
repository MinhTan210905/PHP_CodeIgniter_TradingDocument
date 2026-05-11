<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Auth_model $Auth_model
 * @property Trade_model $Trade_model
 * @property Rating_model $Rating_model
 * @property Message_model $Message_model
 */
class Profile extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Auth_model', 'Trade_model', 'Rating_model', 'Message_model']);
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    // Trang cá nhân
    public function index() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $data['user']         = $this->Auth_model->get_user_by_id($user_id);
        $data['my_posts']     = $this->Trade_model->get_posts_by_user($user_id);
        $data['my_ratings']   = $this->Rating_model->get_ratings_for_seller($user_id);
        $data['avg_rating']   = $this->Rating_model->get_avg_rating($user_id);
        $data['unread_count'] = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('profile/index', $data);
        $this->load->view('partials/footer');
    }

    // Bật / tắt hiển thị SĐT
    public function toggle_phone() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $user    = $this->Auth_model->get_user_by_id($user_id);

        $this->Auth_model->toggle_phone_visible($user_id, $user['phone_visible']);
        $this->session->set_flashdata('success', $user['phone_visible']
            ? 'Đã ẩn số điện thoại của bạn.'
            : 'Đã hiển thị số điện thoại của bạn.');
        redirect('profile');
    }

    // Cập nhật số điện thoại
    public function update_phone() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $phone   = $this->input->post('phone', TRUE);

        $this->Auth_model->update_user($user_id, ['phone' => $phone]);
        $this->session->set_flashdata('success', 'Đã cập nhật số điện thoại!');
        redirect('profile');
    }

    // Người dùng tự xóa tài khoản
    public function delete_account() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        // Xác nhận mật khẩu trước khi xóa
        $password = $this->input->post('confirm_delete_password');
        $user     = $this->Auth_model->get_user_by_id($user_id);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->session->set_flashdata('error', 'Mật khẩu xác nhận không đúng!');
            redirect('profile');
            return;
        }

        // Xóa tài khoản và huỷ session
        $this->Auth_model->delete_user($user_id);
        $this->session->sess_destroy();
        $this->session->set_flashdata('success', 'Tài khoản của bạn đã được xóa vĩnh viễn.');
        redirect('auth');
    }
}
