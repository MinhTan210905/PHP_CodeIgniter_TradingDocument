<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property Trade_model $Trade_model
 * @property Auth_model $Auth_model
 * @property Message_model $Message_model
 */
class Admin extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Trade_model', 'Auth_model', 'Message_model', 'Setting_model', 'Order_model']);
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    private function require_admin() {
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') !== 'admin') {
            show_error('Bạn không có quyền truy cập trang này.', 403);
            exit;
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
        $data['total_disputes']    = $this->db->where('status', 'disputed')->count_all_results('orders');
        $data['total_reports']     = $this->db->where('status', 'pending')->count_all_results('user_reports');

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
        $auto_approve_min_stars = $this->input->post('auto_approve_min_stars');
        $site_announcement = $this->input->post('site_announcement', TRUE);
        
        $this->Setting_model->set('auto_approve_new', $auto_approve_new);
        $this->Setting_model->set('auto_approve_edit', $auto_approve_edit);
        $this->Setting_model->set('auto_approve_min_stars', $auto_approve_min_stars);
        $this->Setting_model->set('site_announcement', $site_announcement);
        
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

    // Admin từ chối bài đăng (chuyển sang trạng thái rejected thay vì xóa ngay)
    public function reject_post($id) {
        $this->require_admin();
        $this->Trade_model->update_status($id, 'rejected');
        $this->session->set_flashdata('success', 'Đã từ chối bài đăng!');
        redirect('admin');
    }

    // Admin đổi role user
    public function toggle_role($id) {
        $this->require_admin();
        $user = $this->Auth_model->get_user_by_id($id);
        if ($user) {
            // Không cho thay đổi quyền của Admin gốc
            if ($id == 1 || $user['username'] === 'admin') {
                $this->session->set_flashdata('error', '❌ Không thể thay đổi quyền của tài khoản Admin gốc!');
                redirect('admin/users');
                return;
            }
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

        // Không cho sửa tài khoản Admin gốc
        $user = $this->Auth_model->get_user_by_id($id);
        if ($id == 1 || ($user && $user['username'] === 'admin')) {
            $this->session->set_flashdata('error', '❌ Không thể sửa đổi thông tin của tài khoản Admin gốc!');
            redirect('admin/users');
            return;
        }

        $phone = $this->input->post('phone', TRUE);
        if (!empty($phone)) {
            if (!preg_match('/^0[0-9]{9}$/', $phone)) {
                $this->session->set_flashdata('error', '❌ Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0!');
                redirect('admin/users');
                return;
            }
        }

        $data = [
            'full_name' => $this->input->post('full_name', TRUE),
            'username'  => $this->input->post('username', TRUE),
            'email'     => $this->input->post('email', TRUE),
            'phone'     => $phone,
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
        
        // Không cho xóa tài khoản Admin gốc
        $user = $this->Auth_model->get_user_by_id($id);
        if ($id == 1 || ($user && $user['username'] === 'admin')) {
            $this->session->set_flashdata('error', '❌ Không thể xóa tài khoản Admin gốc!');
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

        // Không cho chặn tài khoản Admin gốc
        $user = $this->Auth_model->get_user_by_id($id);
        if ($id == 1 || ($user && $user['username'] === 'admin')) {
            $this->session->set_flashdata('error', '❌ Không thể chặn tài khoản Admin gốc!');
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

        // Không cho thao tác tài khoản Admin gốc
        $user = $this->Auth_model->get_user_by_id($id);
        if ($id == 1 || ($user && $user['username'] === 'admin')) {
            $this->session->set_flashdata('error', '❌ Không thể thao tác trên tài khoản Admin gốc!');
            redirect('admin/users');
            return;
        }

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
        $data['disputed_orders']  = $this->Order_model->get_disputed_orders();
        $data['unread_count']     = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/payments', $data);
        $this->load->view('partials/footer');
    }

    // =========================================================
    // QUẢN LÝ TRANH CHẤP ĐƠN HÀNG
    // =========================================================

    public function disputes() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        $data['disputed_orders']  = $this->Order_model->get_disputed_orders();
        $data['resolved_orders']  = $this->Order_model->get_resolved_disputes();
        $data['unread_count']     = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/disputes', $data);
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

    // =========================================================
    // PHÂN XỬ TRANH CHẤP ĐƠN HÀNG
    // =========================================================

    /**
     * Admin đồng ý khiếu nại → Hoàn tiền cho người mua
     * Chuyển trạng thái: disputed → cancelled
     * Hoàn tiền: holding_balance seller → balance buyer
     */
    public function resolve_dispute_refund($order_id) {
        $this->require_admin();
        $order = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['status'] !== 'disputed') {
            $this->session->set_flashdata('error', 'Đơn hàng không hợp lệ hoặc không ở trạng thái tranh chấp!');
            redirect('admin/disputes');
            return;
        }

        $admin_note = $this->input->post('admin_note', TRUE) ?: 'Admin đã xem xét và đồng ý khiếu nại.';
        $amount = $order['price'] * $order['quantity'];

        // Cập nhật trạng thái đơn hàng sang cancelled
        $this->Order_model->update_status($order_id, 'cancelled', [
            'reject_reason' => $order['reject_reason'] . "\n[ADMIN] Kết luận: " . $admin_note,
        ]);

        // Hoàn trả sách lại kho vì đơn bị hủy do tranh chấp
        $this->Trade_model->increment_quantity($order['post_id'], $order['quantity']);

        // Hoàn tiền nếu đã thanh toán bằng ví
        if ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid') {
            $this->load->model('Wallet_model');
            $this->Wallet_model->refund_order($order['buyer_id'], $order['seller_id'], $order_id, $amount);
            $this->db->where('id', $order_id)->update('orders', ['payment_status' => 'refunded']);
        }

        // Gửi thông báo cho cả 2 bên qua chat
        $admin_id = $this->session->userdata('user_id');

        $this->Message_model->send_message([
            'sender_id'   => $admin_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [Ban Quản trị] đã xem xét khiếu nại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Kết luận: ĐỒNG Ý khiếu nại. " . ($order['payment_method'] === 'wallet' ? 'Tiền đã được hoàn lại vào ví HCMUEPay của bạn.' : 'Đơn hàng đã được hủy.'),
        ]);

        $this->Message_model->send_message([
            'sender_id'   => $admin_id,
            'receiver_id' => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'content'     => "⚠️ [Ban Quản trị] đã phân xử đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Kết luận: Đồng ý khiếu nại của người mua. Lý do: " . $admin_note,
        ]);

        $this->session->set_flashdata('success', '✅ Đã xử lý tranh chấp đơn #' . $order_id . ': Hoàn tiền cho người mua.');
        redirect('admin/disputes');
    }

    /**
     * Admin từ chối khiếu nại → Giải ngân cho người bán
     * Chuyển trạng thái: disputed → completed
     * Giải ngân: holding_balance → balance seller
     */
    public function resolve_dispute_release($order_id) {
        $this->require_admin();
        $order = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['status'] !== 'disputed') {
            $this->session->set_flashdata('error', 'Đơn hàng không hợp lệ hoặc không ở trạng thái tranh chấp!');
            redirect('admin/disputes');
            return;
        }

        $admin_note = $this->input->post('admin_note', TRUE) ?: 'Admin đã xem xét và từ chối khiếu nại.';
        $amount = $order['price'] * $order['quantity'];

        // Cập nhật trạng thái đơn hàng sang completed
        $this->Order_model->update_status($order_id, 'completed', [
            'reject_reason' => $order['reject_reason'] . "\n[ADMIN] Kết luận: " . $admin_note,
        ]);

        // Giải ngân tiền cho người bán nếu đã thanh toán bằng ví
        if ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid') {
            $this->load->model('Wallet_model');
            $this->Wallet_model->release_escrow($order['seller_id'], $order_id, $amount);
        }

        // Gửi thông báo cho cả 2 bên qua chat
        $admin_id = $this->session->userdata('user_id');

        $this->Message_model->send_message([
            'sender_id'   => $admin_id,
            'receiver_id' => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [Ban Quản trị] đã xem xét khiếu nại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Kết luận: TỪ CHỐI khiếu nại. " . ($order['payment_method'] === 'wallet' ? 'Tiền đã được giải ngân vào ví HCMUEPay của bạn.' : 'Giao dịch hoàn tất.'),
        ]);

        $this->Message_model->send_message([
            'sender_id'   => $admin_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "⚠️ [Ban Quản trị] đã phân xử đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Kết luận: Từ chối khiếu nại. Lý do: " . $admin_note . ". Giao dịch được xác nhận hoàn thành.",
        ]);

        $this->session->set_flashdata('success', '✅ Đã xử lý tranh chấp đơn #' . $order_id . ': Giải ngân cho người bán.');
        redirect('admin/disputes');
    }

    /**
     * Admin đảo ngược phán quyết tranh chấp (Kháng cáo thành công)
     * Đảo trạng thái: completed <-> cancelled
     * Đảo ví: chuyển tiền từ bên thắng cũ sang bên thắng mới (Cho phép âm số dư)
     */
    public function reverse_dispute_decision($order_id) {
        $this->require_admin();
        $order = $this->Order_model->get_order_by_id($order_id);

        if (!$order) {
            $this->session->set_flashdata('error', 'Đơn hàng không tồn tại!');
            redirect('admin/disputes');
            return;
        }

        // Kiểm tra xem đơn hàng có phải đã từng phân xử tranh chấp không
        if (strpos($order['reject_reason'], '[ADMIN] Kết luận:') === false) {
            $this->session->set_flashdata('error', 'Đơn hàng này chưa từng được Admin phân xử tranh chấp!');
            redirect('admin/disputes');
            return;
        }

        $admin_note = $this->input->post('admin_note', TRUE) ?: 'Ban Quản trị đã xem xét lại lý do và đảo ngược quyết định.';
        $amount = $order['price'] * $order['quantity'];
        $current_status = $order['status'];

        $this->db->trans_start();

        if ($current_status === 'completed') {
            // Trước đây giải ngân cho Seller -> Nay đảo ngược hoàn tiền cho Buyer
            // Cập nhật trạng thái đơn hàng sang cancelled
            $this->Order_model->update_status($order_id, 'cancelled', [
                'reject_reason' => $order['reject_reason'] . "\n[ADMIN] Đảo ngược quyết định: " . $admin_note,
                'payment_status' => 'refunded'
            ]);

            // Hoàn trả sách lại kho vì đơn bị hủy
            $this->Trade_model->increment_quantity($order['post_id'], $order['quantity']);

            // Xử lý ví
            if ($order['payment_method'] === 'wallet') {
                $this->load->model('Wallet_model');
                $this->Wallet_model->reverse_dispute_wallets($order['buyer_id'], $order['seller_id'], $order_id, $amount, 'buyer');
            }

            // Gửi tin nhắn qua Chat
            $admin_id = $this->session->userdata('user_id');
            $this->Message_model->send_message([
                'sender_id'   => $admin_id,
                'receiver_id' => $order['buyer_id'],
                'post_id'     => $order['post_id'],
                'content'     => "✅ [Kháng cáo thành công] Ban Quản trị đã xem xét lại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Quyết định mới: ĐỒNG Ý khiếu nại. " . ($order['payment_method'] === 'wallet' ? 'Tiền bồi hoàn đã được cộng vào ví HCMUEPay của bạn.' : 'Đơn hàng đã được chuyển sang trạng thái hủy.'),
            ]);

            $this->Message_model->send_message([
                'sender_id'   => $admin_id,
                'receiver_id' => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'content'     => "⚠️ [Thay đổi phán quyết] Ban Quản trị đã xem xét lại khiếu nại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Quyết định mới: Đồng ý khiếu nại của người mua. Lý do: " . $admin_note . ". Số tiền giải ngân trước đó đã bị thu hồi từ ví HCMUEPay của bạn.",
            ]);

        } else if ($current_status === 'cancelled') {
            // Trước đây hoàn tiền cho Buyer -> Nay đảo ngược giải ngân cho Seller
            // Cập nhật trạng thái đơn hàng sang completed
            $this->Order_model->update_status($order_id, 'completed', [
                'reject_reason' => $order['reject_reason'] . "\n[ADMIN] Đảo ngược quyết định: " . $admin_note,
                'payment_status' => 'paid'
            ]);

            // Trừ lại sách vì đơn đã được hoàn thành lại
            $this->Trade_model->decrement_quantity($order['post_id'], $order['quantity']);

            // Xử lý ví
            if ($order['payment_method'] === 'wallet') {
                $this->load->model('Wallet_model');
                $this->Wallet_model->reverse_dispute_wallets($order['buyer_id'], $order['seller_id'], $order_id, $amount, 'seller');
            }

            // Gửi tin nhắn qua Chat
            $admin_id = $this->session->userdata('user_id');
            $this->Message_model->send_message([
                'sender_id'   => $admin_id,
                'receiver_id' => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'content'     => "✅ [Kháng cáo thành công] Ban Quản trị đã xem xét lại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Quyết định mới: TỪ CHỐI khiếu nại của người mua. " . ($order['payment_method'] === 'wallet' ? 'Tiền giải ngân bồi hoàn đã được cộng lại vào ví HCMUEPay của bạn.' : 'Giao dịch hoàn tất.'),
            ]);

            $this->Message_model->send_message([
                'sender_id'   => $admin_id,
                'receiver_id' => $order['buyer_id'],
                'post_id'     => $order['post_id'],
                'content'     => "⚠️ [Thay đổi phán quyết] Ban Quản trị đã xem xét lại khiếu nại đơn hàng #" . $order_id . " \"" . $order['post_title'] . "\". Quyết định mới: Từ chối khiếu nại của bạn. Lý do: " . $admin_note . ". Số tiền hoàn trả trước đó đã bị thu hồi từ ví HCMUEPay của bạn.",
            ]);
        }

        $this->db->trans_complete();

        $this->session->set_flashdata('success', '✅ Đã đảo ngược phán quyết tranh chấp đơn hàng #' . $order_id . ' thành công!');
        redirect('admin/disputes');
    }
    // ============================================
    // Quản lý kiểm duyệt nội dung (AI Moderation)
    // ============================================
    
    public function moderation() {
        $this->require_admin();
        $this->load->model('Ai_moderation_model');
        
        $data['flagged_comments'] = $this->db->where('moderation_status', 'flagged')->get('comments')->result_array();
        foreach($data['flagged_comments'] as &$comment) {
            $comment['user'] = $this->db->where('id', $comment['user_id'])->get('users')->row_array();
            $comment['post'] = $this->db->where('id', $comment['post_id'])->get('posts')->row_array();
        }
        
        $data['ai_logs'] = $this->Ai_moderation_model->get_flagged_logs(100, 0);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/moderation', $data);
        $this->load->view('partials/footer');
    }

    public function moderation_action() {
        $this->require_admin();
        $type   = $this->input->post('type');   // 'comment'
        $id     = (int)$this->input->post('id');
        $action = $this->input->post('action'); // 'approve' or 'delete'

        // Validate input
        if (empty($type) || empty($id) || !in_array($action, ['approve', 'delete'])) {
            $this->session->set_flashdata('error', 'Thao tác không hợp lệ!');
            redirect('admin/moderation');
            return;
        }

        if ($type == 'comment') {
            if ($action == 'approve') {
                $this->db->where('id', $id)->update('comments', ['moderation_status' => 'approved']);
                $this->session->set_flashdata('success', 'Đã duyệt bình luận thành công!');
            } else if ($action == 'delete') {
                $this->db->where('id', $id)->delete('comments');
                $this->session->set_flashdata('success', 'Đã xóa bình luận vi phạm!');
            }
        }
        redirect('admin/moderation');
    }

    // ============================================
    // Quản lý báo cáo người dùng (User Reports)
    // ============================================
    
    public function reports() {
        $this->require_admin();
        $user_id = $this->session->userdata('user_id');

        // Lấy danh sách báo cáo cùng thông tin người báo cáo, người bị báo cáo và đơn hàng liên quan
        $this->db->select('user_reports.*, 
            reporter.full_name as reporter_name, reporter.username as reporter_username,
            reported.full_name as reported_name, reported.username as reported_username,
            posts.title as post_title, posts.price, orders.quantity');
        $this->db->from('user_reports');
        $this->db->join('users reporter', 'reporter.id = user_reports.reporter_id', 'left');
        $this->db->join('users reported', 'reported.id = user_reports.reported_user_id', 'left');
        $this->db->join('orders', 'orders.id = user_reports.order_id', 'left');
        $this->db->join('posts', 'posts.id = orders.post_id', 'left');
        $this->db->order_by('user_reports.created_at', 'DESC');
        $data['reports'] = $this->db->get()->result_array();

        $data['unread_count'] = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('admin/reports', $data);
        $this->load->view('partials/footer');
    }

    public function resolve_report($report_id, $action) {
        $this->require_admin();
        if (!in_array($action, ['resolved', 'dismissed'])) {
            $this->session->set_flashdata('error', 'Thao tác không hợp lệ!');
            redirect('admin/reports');
            return;
        }

        $this->db->where('id', $report_id)->update('user_reports', ['status' => $action]);
        $this->session->set_flashdata('success', 'Đã xử lý báo cáo thành công!');
        redirect('admin/reports');
    }
}
