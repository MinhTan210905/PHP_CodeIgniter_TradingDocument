<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * REST API Controller — HCMUE Pass Sách
 * 
 * Endpoints:
 *   === AUTH ===
 *   POST   /api/auth/login           → Đăng nhập, trả về token (session)
 *   POST   /api/auth/register        → Đăng ký tài khoản
 *
 *   === POSTS (Sách) ===
 *   GET    /api/posts                → Danh sách sách đang bán
 *   GET    /api/posts/search?q=&cat= → Tìm kiếm sách
 *   GET    /api/posts/detail/:id     → Chi tiết sách
 *   POST   /api/posts/create         → Đăng sách mới
 *   DELETE /api/posts/delete/:id     → Xóa sách
 *
 *   === ORDERS (Đơn hàng) ===
 *   GET    /api/orders               → Đơn hàng của tôi
 *   GET    /api/orders/detail/:id    → Chi tiết đơn
 *   POST   /api/orders/request/:id   → Gửi yêu cầu mua
 *   PUT    /api/orders/confirm/:id   → Xác nhận đơn (seller)
 *   PUT    /api/orders/reject/:id    → Từ chối đơn (seller)
 *   PUT    /api/orders/received/:id  → Đã nhận hàng (buyer)
 *   PUT    /api/orders/dispute/:id   → Báo tranh chấp (buyer)
 *   PUT    /api/orders/cancel/:id    → Hủy đơn
 *   POST   /api/orders/rate/:id      → Đánh giá đơn hàng
 *
 *   === SELLER (Sàn người bán) ===
 *   GET    /api/seller/:id           → Thông tin sàn người bán
 *   GET    /api/seller/:id/posts     → Sách của người bán
 *   GET    /api/seller/:id/ratings   → Đánh giá người bán
 */
class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Trade_model', 'Order_model', 'Seller_model', 'Rating_model', 'Message_model']);
        $this->load->library(['form_validation', 'session']);
        $this->load->helper('url');
        $this->output->set_content_type('application/json');

        // CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        if ($this->input->server('REQUEST_METHOD') === 'OPTIONS') { exit(0); }
    }

    // ── Helper ──────────────────────────────────────────────
    private function _json($data, $code = 200) {
        $this->output->set_status_header($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function _require_auth() {
        if (!$this->session->userdata('logged_in')) {
            $this->_json(['status' => 401, 'message' => 'Chưa đăng nhập. Vui lòng gọi /api/auth/login trước.'], 401);
            return false;
        }
        return true;
    }

    private function _uid() {
        return (int) $this->session->userdata('user_id');
    }

    // ═══════════════════════════════════════════════════════
    //  AUTH
    // ═══════════════════════════════════════════════════════

    public function login() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận POST.'], 405); return;
        }
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if (!$username || !$password) {
            $this->_json(['status' => 400, 'message' => 'Thiếu username hoặc password.'], 400); return;
        }
        $user = $this->db->get_where('users', ['username' => $username])->row_array();
        if (!$user || !password_verify($password, $user['password'])) {
            $this->_json(['status' => 401, 'message' => 'Sai tài khoản hoặc mật khẩu.'], 401); return;
        }
        $this->session->set_userdata([
            'user_id' => $user['id'], 'username' => $user['username'],
            'full_name' => $user['full_name'], 'role' => $user['role'], 'logged_in' => TRUE
        ]);
        $this->_json(['status' => 200, 'message' => 'Đăng nhập thành công!', 'data' => [
            'id' => $user['id'], 'username' => $user['username'],
            'full_name' => $user['full_name'], 'role' => $user['role']
        ]]);
    }

    public function register() {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận POST.'], 405); return;
        }
        $this->form_validation->set_rules('full_name', 'Họ tên', 'required|min_length[2]');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[3]|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Mật khẩu', 'required|min_length[6]');
        if (!$this->form_validation->run()) {
            $this->_json(['status' => 400, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $this->form_validation->error_array()], 400); return;
        }
        $this->db->insert('users', [
            'full_name' => $this->input->post('full_name', TRUE),
            'username'  => $this->input->post('username', TRUE),
            'email'     => $this->input->post('email', TRUE),
            'password'  => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'role'      => 'user'
        ]);
        $this->_json(['status' => 201, 'message' => 'Đăng ký thành công!', 'data' => ['id' => $this->db->insert_id()]], 201);
    }

    // ═══════════════════════════════════════════════════════
    //  POSTS (Sách)
    // ═══════════════════════════════════════════════════════

    public function posts() {
        $posts = $this->Trade_model->get_all_posts();
        $this->_json(['status' => 200, 'total' => count($posts), 'data' => $posts]);
    }

    public function search() {
        $cat = $this->input->get('cat');
        $q   = $this->input->get('q');
        $posts = $this->Trade_model->get_all_posts($cat, $q);
        $this->_json(['status' => 200, 'total' => count($posts), 'filters' => ['cat' => $cat, 'q' => $q], 'data' => $posts]);
    }

    public function detail($id = 0) {
        if ($id <= 0) { $this->_json(['status' => 400, 'message' => 'ID không hợp lệ.'], 400); return; }
        $post = $this->Trade_model->get_post_detail($id);
        if (!$post) { $this->_json(['status' => 404, 'message' => 'Không tìm thấy bài đăng.'], 404); return; }
        $this->_json(['status' => 200, 'data' => $post]);
    }

    public function create_post_api() {
        if (!$this->_require_auth()) return;
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận POST.'], 405); return;
        }
        $this->form_validation->set_rules('category_id', 'Danh mục', 'required|integer');
        $this->form_validation->set_rules('title', 'Tiêu đề', 'required|min_length[5]');
        $this->form_validation->set_rules('price', 'Giá', 'required|numeric');
        if (!$this->form_validation->run()) {
            $this->_json(['status' => 400, 'errors' => $this->form_validation->error_array()], 400); return;
        }
        $id = $this->Trade_model->insert_post([
            'user_id'     => $this->_uid(),
            'category_id' => (int) $this->input->post('category_id'),
            'title'       => $this->input->post('title', TRUE),
            'description' => $this->input->post('description', TRUE),
            'price'       => (float) $this->input->post('price'),
            'quantity'    => max(1, (int) $this->input->post('quantity')),
            'image_url'   => '',
            'status'      => ($this->session->userdata('role') === 'admin') ? 'available' : 'pending'
        ]);
        $this->_json(['status' => 201, 'message' => 'Đăng sách thành công!', 'data' => ['id' => $id]], 201);
    }

    public function delete_post_api($id = 0) {
        if (!$this->_require_auth()) return;
        if ($this->input->server('REQUEST_METHOD') !== 'DELETE') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận DELETE.'], 405); return;
        }
        $post = $this->Trade_model->get_post_by_id($id);
        if (!$post) { $this->_json(['status' => 404, 'message' => 'Không tìm thấy bài đăng.'], 404); return; }
        if ($post['user_id'] != $this->_uid() && $this->session->userdata('role') !== 'admin') {
            $this->_json(['status' => 403, 'message' => 'Không có quyền xóa bài này.'], 403); return;
        }
        $this->Trade_model->delete_post($id);
        $this->_json(['status' => 200, 'message' => 'Xóa thành công.']);
    }

    // ═══════════════════════════════════════════════════════
    //  ORDERS (Đơn hàng)
    // ═══════════════════════════════════════════════════════

    public function orders_list() {
        if (!$this->_require_auth()) return;
        $uid = $this->_uid();
        $this->_json(['status' => 200, 'data' => [
            'as_buyer'  => $this->Order_model->get_orders_as_buyer($uid),
            'as_seller' => $this->Order_model->get_orders_as_seller($uid),
        ]]);
    }

    public function order_detail($id = 0) {
        if (!$this->_require_auth()) return;
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order) { $this->_json(['status' => 404, 'message' => 'Đơn hàng không tồn tại.'], 404); return; }
        $uid = $this->_uid();
        if ($order['seller_id'] != $uid && $order['buyer_id'] != $uid) {
            $this->_json(['status' => 403, 'message' => 'Không có quyền xem đơn này.'], 403); return;
        }
        $this->_json(['status' => 200, 'data' => $order]);
    }

    public function order_request($post_id = 0) {
        if (!$this->_require_auth()) return;
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận POST.'], 405); return;
        }
        $uid  = $this->_uid();
        $post = $this->Trade_model->get_post_by_id($post_id);
        if (!$post) { $this->_json(['status' => 404, 'message' => 'Bài đăng không tồn tại.'], 404); return; }
        if ($post['user_id'] == $uid) { $this->_json(['status' => 400, 'message' => 'Không thể tự mua sách của mình.'], 400); return; }
        if ($post['status'] !== 'available') { $this->_json(['status' => 400, 'message' => 'Sách đã hết hàng.'], 400); return; }
        if ($this->Order_model->has_active_order($post_id, $uid)) {
            $this->_json(['status' => 409, 'message' => 'Bạn đã có đơn đang chờ cho sách này.'], 409); return;
        }
        $qty = max(1, (int) $this->input->post('quantity'));
        if ($qty > (int) $post['quantity']) {
            $this->_json(['status' => 400, 'message' => 'Số lượng vượt tồn kho (' . $post['quantity'] . ').'], 400); return;
        }
        $order_id = $this->Order_model->create_order([
            'post_id' => $post_id, 'seller_id' => $post['user_id'], 'buyer_id' => $uid,
            'quantity' => $qty, 'note' => $this->input->post('note', TRUE),
        ]);
        $this->_json(['status' => 201, 'message' => 'Gửi yêu cầu mua thành công!', 'data' => ['order_id' => $order_id]], 201);
    }

    public function order_confirm($id = 0) {
        if (!$this->_require_auth()) return;
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order || $order['seller_id'] != $this->_uid() || $order['status'] !== 'pending') {
            $this->_json(['status' => 400, 'message' => 'Không thể xác nhận đơn này.'], 400); return;
        }
        $this->Order_model->update_status($id, 'confirmed');
        $this->_json(['status' => 200, 'message' => 'Đã xác nhận đơn hàng.']);
    }

    public function order_reject($id = 0) {
        if (!$this->_require_auth()) return;
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order || $order['seller_id'] != $this->_uid() || $order['status'] !== 'pending') {
            $this->_json(['status' => 400, 'message' => 'Không thể từ chối đơn này.'], 400); return;
        }
        $reason = $this->input->post('reason', TRUE) ?: 'Người bán từ chối.';
        $this->Order_model->update_status($id, 'rejected', ['reject_reason' => $reason]);
        $this->_json(['status' => 200, 'message' => 'Đã từ chối đơn hàng.']);
    }

    public function order_received($id = 0) {
        if (!$this->_require_auth()) return;
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order || $order['buyer_id'] != $this->_uid() || $order['status'] !== 'confirmed') {
            $this->_json(['status' => 400, 'message' => 'Không thể xác nhận nhận hàng.'], 400); return;
        }
        $this->Order_model->update_status($id, 'completed');
        $this->Trade_model->decrement_quantity($order['post_id'], $order['quantity']);
        $this->_json(['status' => 200, 'message' => 'Đã xác nhận nhận hàng! Hãy đánh giá người bán.']);
    }

    public function order_dispute($id = 0) {
        if (!$this->_require_auth()) return;
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order || $order['buyer_id'] != $this->_uid() || $order['status'] !== 'confirmed') {
            $this->_json(['status' => 400, 'message' => 'Không thể báo tranh chấp.'], 400); return;
        }
        $reason = $this->input->post('reason', TRUE) ?: 'Chưa nhận được hàng.';
        $this->Order_model->update_status($id, 'disputed', ['reject_reason' => $reason]);
        $this->_json(['status' => 200, 'message' => 'Đã báo tranh chấp.']);
    }

    public function order_cancel($id = 0) {
        if (!$this->_require_auth()) return;
        $uid   = $this->_uid();
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order) { $this->_json(['status' => 404, 'message' => 'Đơn không tồn tại.'], 404); return; }
        $can = ($order['buyer_id'] == $uid && $order['status'] === 'pending')
            || ($order['seller_id'] == $uid && in_array($order['status'], ['pending', 'confirmed']));
        if (!$can) { $this->_json(['status' => 400, 'message' => 'Không thể hủy đơn này.'], 400); return; }
        $this->Order_model->update_status($id, 'cancelled');
        $this->_json(['status' => 200, 'message' => 'Đã hủy đơn hàng.']);
    }

    public function order_rate($id = 0) {
        if (!$this->_require_auth()) return;
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            $this->_json(['status' => 405, 'message' => 'Chỉ chấp nhận POST.'], 405); return;
        }
        $uid   = $this->_uid();
        $order = $this->Order_model->get_order_by_id($id);
        if (!$order || $order['buyer_id'] != $uid || $order['status'] !== 'completed') {
            $this->_json(['status' => 400, 'message' => 'Chưa thể đánh giá đơn này.'], 400); return;
        }
        if ($this->Rating_model->has_rated_order($id, $uid)) {
            $this->_json(['status' => 409, 'message' => 'Bạn đã đánh giá đơn này rồi.'], 409); return;
        }
        $stars = (int) $this->input->post('stars');
        if ($stars < 1 || $stars > 5) {
            $this->_json(['status' => 400, 'message' => 'Số sao phải từ 1 đến 5.'], 400); return;
        }
        $this->Rating_model->add_rating([
            'reviewer_id' => $uid, 'seller_id' => $order['seller_id'],
            'post_id' => $order['post_id'], 'order_id' => $id,
            'stars' => $stars, 'comment' => $this->input->post('comment', TRUE),
        ]);
        $this->_json(['status' => 201, 'message' => 'Đánh giá thành công!'], 201);
    }

    // ═══════════════════════════════════════════════════════
    //  SELLER (Sàn người bán)
    // ═══════════════════════════════════════════════════════

    public function seller_info($id = 0) {
        $seller = $this->Seller_model->get_seller_info($id);
        if (!$seller) { $this->_json(['status' => 404, 'message' => 'Người bán không tồn tại.'], 404); return; }
        $stats = $this->Seller_model->get_stats($id);
        unset($seller['password']); // Không lộ mật khẩu
        $this->_json(['status' => 200, 'data' => ['seller' => $seller, 'stats' => $stats]]);
    }

    public function seller_posts($id = 0) {
        $this->_json(['status' => 200, 'data' => [
            'active' => $this->Seller_model->get_active_posts($id),
            'sold'   => $this->Seller_model->get_sold_posts($id),
        ]]);
    }

    public function seller_ratings($id = 0) {
        $this->_json(['status' => 200, 'data' => $this->Seller_model->get_ratings($id)]);
    }
}
