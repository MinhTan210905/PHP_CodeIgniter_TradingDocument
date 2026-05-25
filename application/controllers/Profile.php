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
        $this->load->library(['session', 'upload']);
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

    // Cập nhật thông tin cơ bản (Tên, Avatar, SĐT...)
    public function update_info() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        
        $full_name = $this->input->post('full_name', TRUE);
        $phone     = $this->input->post('phone', TRUE);
        
        if (!empty($phone)) {
            if (!preg_match('/^0[0-9]{9}$/', $phone)) {
                $this->session->set_flashdata('error', '❌ Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0!');
                redirect('profile');
                return;
            }
        }
        
        $update_data = [];
        if ($full_name) $update_data['full_name'] = $full_name;
        if ($phone !== NULL) $update_data['phone'] = $phone;

        // Xử lý upload avatar mới nếu có
        if (!empty($_FILES['avatar']['name'])) {
            $upload_dir = FCPATH . 'assets/uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, TRUE);
            }

            $config['upload_path']   = $upload_dir;
            $config['allowed_types'] = 'gif|jpg|png|jpeg|webp';
            $config['max_size']      = 2048; // 2MB
            $config['encrypt_name']  = TRUE;

            $this->upload->initialize($config);

            if ($this->upload->do_upload('avatar')) {
                $data = $this->upload->data();
                $update_data['avatar'] = 'assets/uploads/avatars/' . $data['file_name'];
            } else {
                $this->session->set_flashdata('error', 'Lỗi tải lên ảnh đại diện: ' . strip_tags($this->upload->display_errors()));
                redirect('profile');
                return;
            }
        }

        if (!empty($update_data)) {
            $this->Auth_model->update_user($user_id, $update_data);
            
            // Cập nhật cả session data cho full_name, avatar nếu nó vừa đổi
            if (isset($update_data['full_name'])) {
                $this->session->set_userdata('full_name', $update_data['full_name']);
            }
            if (isset($update_data['avatar'])) {
                $this->session->set_userdata('avatar', $update_data['avatar']);
            }
            
            $this->session->set_flashdata('success', 'Đã cập nhật thông tin cá nhân thành công!');
        }
        
        redirect('profile');
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

    // Cập nhật số điện thoại (Legacy support, keep if UI form still uses it, but UI should be replaced)
    public function update_phone() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $phone   = $this->input->post('phone', TRUE);

        if (!empty($phone)) {
            if (!preg_match('/^0[0-9]{9}$/', $phone)) {
                $this->session->set_flashdata('error', '❌ Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0!');
                redirect('profile');
                return;
            }
        }

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
        $this->session->set_flashdata('success', 'Tài khoản của bạn đã được xóa vĩnh viễn.');
        $this->session->sess_destroy();
        redirect('auth');
    }

    // Cập nhật mật khẩu
    public function change_password() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        
        $old_password = $this->input->post('old_password');
        $new_password = $this->input->post('new_password');
        $confirm_password = $this->input->post('confirm_password');

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            $this->session->set_flashdata('error', 'Vui lòng nhập đầy đủ các trường mật khẩu!');
            redirect('profile');
            return;
        }

        if ($new_password !== $confirm_password) {
            $this->session->set_flashdata('error', 'Mật khẩu xác nhận không khớp!');
            redirect('profile');
            return;
        }

        $user = $this->Auth_model->get_user_by_id($user_id);
        if (!password_verify($old_password, $user['password'])) {
            $this->session->set_flashdata('error', 'Mật khẩu cũ không chính xác!');
            redirect('profile');
            return;
        }

        $this->Auth_model->update_user($user_id, ['password' => password_hash($new_password, PASSWORD_DEFAULT)]);
        $this->session->set_flashdata('success', '✅ Đã thay đổi mật khẩu thành công!');
        redirect('profile');
    }
}
