<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property Trade_model $Trade_model
 * @property Auth_model $Auth_model
 * @property Message_model $Message_model
 */
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Trade_model', 'Auth_model', 'Message_model', 'Setting_model', 'Order_model']);
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    private function require_admin() {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            show_error('Bạn không có quyền truy cập trang này.', 403);
        }
    }

    // Dashboard tổng quan
    public function index() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        // Thống kê nhanh
        $data['total_posts']     = $this->db->count_all('posts');
        $data['total_users']     = $this->db->count_all('users');
        $data['total_sold']      = $this->db->where('status', 'sold')->count_all_results('posts');
        $data['total_available'] = $this->db->where('status', 'available')->count_all_results('posts');
        $data['total_pending']   = $this->Trade_model->count_pending();

        $this->load->model('Wallet_model');
        $data['total_withdrawals'] = $this->Wallet_model->count_pending_withdrawals();

        $data['recent_posts']  = $this->Trade_model->get_all_approved_posts();
        $data['pending_posts'] = $this->Trade_model->get_pending_posts();
        $data['unread_count']  = $this->Message_model->count_unread($user_id);
        
        // Lấy các cài đặt hiện tại
        $data['app_settings'] = $this->Setting_model->get_all();

        $this->load->view('partials/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('partials/footer');
    }

    // [AJAX] Kiểm tra xem có bài đăng hoặc yêu cầu thanh toán mới chờ duyệt không
    public function check_updates() {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        $this->load->model(['Trade_model', 'Wallet_model']);
        $total_pending = $this->Trade_model->count_pending();
        $total_withdrawals = $this->Wallet_model->count_pending_withdrawals();

        echo json_encode([
            'status' => 'ok',
            'pending_posts' => (int)$total_pending,
            'pending_withdrawals' => (int)$total_withdrawals
        ]);
    }

    // Cập nhật cấu hình hệ thống
    public function update_settings() {
        $this->require_admin();
        
        $auto_approve_new = $this->input->post('auto_approve_new') ? '1' : '0';
        $auto_approve_edit = $this->input->post('auto_approve_edit') ? '1' : '0';
        
        $this->Setting_model->set('auto_approve_new', $auto_approve_new);
        $this->Setting_model->set('auto_approve_edit', $auto_approve_edit);
        
        $this->session->set_flashdata('success', '✅ Đã cập nhật cấu hình hệ thống thành công!');
        redirect('admin');
    }

    // Quản lý người dùng
    public function users() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        $data['users']        = $this->db->get('users')->result_array();
        $data['unread_count'] = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/users', $data);
        $this->load->view('partials/footer');
    }

    // Admin xóa bài đăng bất kỳ
    public function delete_post($id) {
        $this->require_admin();
        $this->Trade_model->delete_post($id);
        $this->session->set_flashdata('success', 'Đã xóa bài đăng!');
        redirect('admin');
    }

    // Admin duyệt bài đăng
    public function approve_post($id) {
        $this->require_admin();
        $this->Trade_model->update_status($id, 'available');
        $this->session->set_flashdata('success', '✅ Đã duyệt và đăng bài lên trang chủ!');
        redirect('admin');
    }

    // Admin từ chối bài đăng
    public function reject_post($id) {
        $this->require_admin();
        $this->Trade_model->delete_post($id);
        $this->session->set_flashdata('success', 'Đã từ chối và xóa bài!');
        redirect('admin');
    }

    // Admin đổi role user
    public function toggle_role($id) {
        $this->require_admin();
        $user = $this->Auth_model->get_user_by_id($id);
        if ($user) {
            $new_role = ($user['role'] === 'admin') ? 'user' : 'admin';
            $this->Auth_model->update_user($id, ['role' => $new_role]);
            $this->session->set_flashdata('success', 'Đã thay đổi quyền người dùng!');
        }
        redirect('admin/users');
    }

    // Admin sửa thông tin user
    public function edit_user_post($id) {
        $this->require_admin();
        // Không cho sửa chính mình qua form này
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Không thể sửa thông tin chính mình qua trang quản lý!');
            redirect('admin/users');
            return;
        }

        $data = [
            'full_name' => $this->input->post('full_name', TRUE),
            'username'  => $this->input->post('username', TRUE),
            'email'     => $this->input->post('email', TRUE),
            'phone'     => $this->input->post('phone', TRUE),
            'role'      => $this->input->post('role', TRUE),
        ];

        // Nếu admin nhập mật khẩu mới thì đổi luôn
        $new_password = $this->input->post('new_password');
        if (!empty($new_password)) {
            $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $this->Auth_model->update_user($id, $data);
        $this->session->set_flashdata('success', 'Đã cập nhật thông tin người dùng #' . $id . '!');
        redirect('admin/users');
    }

    // Admin xóa tài khoản user
    public function delete_user($id) {
        $this->require_admin();
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Không thể tự xóa tài khoản Admin của chính mình!');
            redirect('admin/users');
            return;
        }
        $this->Auth_model->delete_user($id);
        $this->session->set_flashdata('success', 'Đã xóa tài khoản người dùng!');
        redirect('admin/users');
    }

    // Admin chặn (ban) tài khoản user
    public function ban_user($id) {
        $this->require_admin();
        if ($id == $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error', 'Không thể tự chặn tài khoản của chính mình!');
            redirect('admin/users');
            return;
        }
        $this->Auth_model->update_user($id, ['is_banned' => 1]);
        $this->session->set_flashdata('success', 'Đã chặn (ban) tài khoản người dùng!');
        redirect('admin/users');
    }

    // Admin bỏ chặn (unban) tài khoản user
    public function unban_user($id) {
        $this->require_admin();
        $this->Auth_model->update_user($id, ['is_banned' => 0]);
        $this->session->set_flashdata('success', 'Đã bỏ chặn tài khoản người dùng!');
        redirect('admin/users');
    }

    // =========================================================
    // QUẢN LÝ DANH MỤC
    // =========================================================

    public function categories() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        $data['categories'] = $this->Trade_model->get_categories();
        $data['unread_count'] = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/categories', $data);
        $this->load->view('partials/footer');
    }

    public function add_category() {
        $this->require_admin();
        $category_name = $this->input->post('category_name', TRUE);
        $icon = $this->input->post('icon', TRUE);

        if ($category_name) {
            $this->Trade_model->insert_category([
                'category_name' => $category_name,
                'icon' => $icon ?: 'fas fa-book'
            ]);
            $this->session->set_flashdata('success', 'Đã thêm danh mục mới thành công!');
        }
        redirect('admin/categories');
    }

    public function edit_category($id) {
        $this->require_admin();
        $category_name = $this->input->post('category_name', TRUE);
        $icon = $this->input->post('icon', TRUE);

        if ($category_name) {
            $this->Trade_model->update_category($id, [
                'category_name' => $category_name,
                'icon' => $icon ?: 'fas fa-book'
            ]);
            $this->session->set_flashdata('success', 'Đã cập nhật danh mục thành công!');
        }
        redirect('admin/categories');
    }

    public function delete_category($id) {
        $this->require_admin();
        
        // Kiểm tra xem danh mục này có bài đăng nào không
        $posts_in_category = $this->db->where('category_id', $id)->count_all_results('posts');
        if ($posts_in_category > 0) {
            $this->session->set_flashdata('error', 'Không thể xóa danh mục đang có bài đăng. Vui lòng chuyển bài đăng sang danh mục khác trước!');
            redirect('admin/categories');
            return;
        }

        $this->Trade_model->delete_category($id);
        $this->session->set_flashdata('success', 'Đã xóa danh mục thành công!');
        redirect('admin/categories');
    }

    // =========================================================
    // KIỂM DUYỆT THANH TOÁN (DEMO) & RÚT TIỀN
    // =========================================================

    public function payments() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        $this->load->model('Wallet_model');
        $data['withdrawals']      = $this->Wallet_model->get_all_pending_withdrawals();
        $data['processed_withdrawals'] = $this->Wallet_model->get_all_processed_withdrawals();
        $data['completed_orders'] = $this->Order_model->get_completed_orders();
        $data['unread_count']     = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/payments', $data);
        $this->load->view('partials/footer');
    }

    // Admin duyệt yêu cầu rút tiền
    public function approve_withdrawal($id) {
        $this->require_admin();
        $this->load->model('Wallet_model');
        $this->Wallet_model->approve_withdrawal($id);
        $this->session->set_flashdata('success', '✅ Đã xác nhận chuyển tiền (Duyệt rút tiền thành công)!');
        redirect('admin/payments');
    }

    // Admin từ chối yêu cầu rút tiền
    public function reject_withdrawal($id) {
        $this->require_admin();
        $this->load->model('Wallet_model');
        $note = $this->input->post('note', TRUE) ?: 'Quản trị viên từ chối';
        $this->Wallet_model->reject_withdrawal($id, $note);
        $this->session->set_flashdata('success', '✅ Đã từ chối yêu cầu rút tiền và hoàn số dư lại vào ví!');
        redirect('admin/payments');
    }

    // Admin xác nhận đã chuyển tiền cho người bán (đơn thủ công cũ - giữ lại nếu cần)
    public function confirm_payment($order_id) {
        $this->require_admin();
        $this->db->where('id', $order_id);
        $this->db->update('orders', ['payment_status' => 'paid']);
        $this->session->set_flashdata('success', '✅ Đã xác nhận chuyển tiền cho đơn hàng #' . $order_id);
        redirect('admin/payments');
    }
}
