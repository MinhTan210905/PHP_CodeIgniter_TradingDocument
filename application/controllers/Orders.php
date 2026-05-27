<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Order_model $Order_model
 * @property Trade_model $Trade_model
 * @property Rating_model $Rating_model
 * @property Message_model $Message_model
 */
class Orders extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Order_model', 'Trade_model', 'Rating_model', 'Message_model']);
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    // [AJAX] API lấy số đơn hàng cần xử lý
    public function ajax_get_action_required_count() {
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        $user_id = $this->session->userdata('user_id');
        $this->load->model('Order_model');
        $count = $this->Order_model->count_action_required($user_id);
        echo json_encode(['success' => true, 'count' => $count]);
    }


    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
            redirect('auth');
        }
    }

    // Trang quản lý đơn hàng (tab Mua + Bán)
    public function index() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $data['orders_as_buyer']  = $this->Order_model->get_orders_as_buyer($user_id);
        $data['orders_as_seller'] = $this->Order_model->get_orders_as_seller($user_id);
        $data['unread_count']     = $this->Message_model->count_unread($user_id);
        $data['pending_count']    = $this->Order_model->count_action_required($user_id);
        $data['active_tab']       = $this->input->get('tab') ?: 'buy';

        $this->load->view('partials/header', $data);
        $this->load->view('orders/index', $data);
        $this->load->view('partials/footer');
    }

    // Chi tiết đơn hàng
    public function detail($id) {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $order   = $this->Order_model->get_order_by_id($id);

        if (!$order) { show_404(); }

        // Chỉ người liên quan hoặc admin mới xem được
        if ($order['seller_id'] != $user_id && $order['buyer_id'] != $user_id && $this->session->userdata('role') !== 'admin') {
            show_error('Bạn không có quyền xem đơn hàng này.', 403);
        }

        $data['order']        = $order;
        $data['is_seller']    = ($order['seller_id'] == $user_id);
        $data['is_buyer']     = ($order['buyer_id']  == $user_id);
        $data['unread_count'] = $this->Message_model->count_unread($user_id);
        $data['pending_count']= $this->Order_model->count_action_required($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('orders/detail', $data);
        $this->load->view('partials/footer');
    }

    // Người mua gửi yêu cầu mua
    public function request_buy($post_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $post     = $this->Trade_model->get_post_by_id($post_id);

        if (!$post) { show_404(); }

        // Không tự mua của chính mình
        if ($post['user_id'] == $buyer_id) {
            $this->session->set_flashdata('error', 'Bạn không thể mua sách của chính mình!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        // Sách phải còn hàng
        if ($post['status'] !== 'available') {
            $this->session->set_flashdata('error', 'Sách này đã hết hàng!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        // Kiểm tra đã có đơn đang chờ chưa
        if ($this->Order_model->has_active_order($post_id, $buyer_id)) {
            $this->session->set_flashdata('error', 'Bạn đã có yêu cầu mua đang chờ xử lý cho sách này!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        $qty  = max(1, (int) $this->input->post('quantity'));
        $note = $this->input->post('note', TRUE);

        // Kiểm tra số lượng yêu cầu không vượt quá tồn kho
        if ($qty > (int)$post['quantity']) {
            $this->session->set_flashdata('error', 'Số lượng yêu cầu vượt quá số sách còn lại (' . $post['quantity'] . ' cuốn)!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        $order_id = $this->Order_model->create_order([
            'post_id'   => $post_id,
            'seller_id' => $post['user_id'],
            'buyer_id'  => $buyer_id,
            'quantity'  => $qty,
            'note'      => $note,
        ]);

        // Gửi thông báo cho người bán qua tin nhắn tự động
        $buyer_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $buyer_id,
            'receiver_id' => $post['user_id'],
            'post_id'     => $post_id,
            'content'     => "📦 [{$buyer_name}] vừa gửi yêu cầu mua [{$qty} cuốn] sách \"{$post['title']}\". Vào trang Đơn hàng để xác nhận: " . site_url('orders/detail/' . $order_id),
        ]);

        $this->session->set_flashdata('success', '✅ Gửi yêu cầu mua thành công! Vui lòng chờ người bán xác nhận.');
        redirect('orders?tab=buy');
    }

    // Người bán xác nhận đơn
    public function confirm($order_id) {
        $this->require_login();
        $seller_id = $this->session->userdata('user_id');
        $order     = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['seller_id'] != $seller_id || $order['status'] !== 'pending') {
            $this->session->set_flashdata('error', 'Không thể xác nhận đơn hàng này!');
            redirect('orders?tab=sell');
            return;
        }

        // Kiểm tra tồn kho thực tế trước khi cho phép xác nhận
        $post = $this->Trade_model->get_post_by_id($order['post_id']);
        if (!$post || $post['quantity'] < $order['quantity']) {
            $avail = $post ? $post['quantity'] : 0;
            $this->session->set_flashdata('error', "❌ Không đủ sách trong kho để xác nhận! Hiện tại còn {$avail} cuốn.");
            redirect('orders?tab=sell');
            return;
        }

        $this->Order_model->update_status($order_id, 'confirmed');

        // Trừ tồn kho ngay khi xác nhận để chống bán chồng (Overselling)
        $this->Trade_model->decrement_quantity($order['post_id'], $order['quantity']);

        // Thông báo cho người mua
        $seller_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $seller_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [{$seller_name}] đã xác nhận đơn hàng \"{$order['post_title']}\". Vui lòng liên hệ để thỏa thuận thời gian và địa điểm giao nhận sách. Xem chi tiết: " . site_url('orders/detail/' . $order_id),
        ]);

        $this->session->set_flashdata('success', 'Đã xác nhận đơn! Liên hệ với người mua để hẹn giao sách.');
        redirect('orders?tab=sell');
    }

    // Người bán từ chối đơn
    public function reject($order_id) {
        $this->require_login();
        $seller_id    = $this->session->userdata('user_id');
        $order        = $this->Order_model->get_order_by_id($order_id);
        $reject_reason= $this->input->post('reject_reason', TRUE) ?: 'Người bán từ chối.';

        if (!$order || $order['seller_id'] != $seller_id || $order['status'] !== 'pending') {
            $this->session->set_flashdata('error', 'Không thể từ chối đơn hàng này!');
            redirect('orders?tab=sell');
            return;
        }

        $this->Order_model->update_status($order_id, 'rejected', ['reject_reason' => $reject_reason]);

        // Thông báo người mua
        $this->Message_model->send_message([
            'sender_id'   => $seller_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "❌ Đơn hàng \"{$order['post_title']}\" đã bị từ chối. Lý do: {$reject_reason}",
        ]);

        $this->trigger_pusher_order($order['buyer_id'], 'Người bán đã từ chối yêu cầu mua của bạn.', $order_id);
        $this->session->set_flashdata('success', 'Đã từ chối đơn hàng.');
        redirect('orders?tab=sell');
    }

    // =========================================================================
    // THANH TOÁN (PAYMENT FLOW)
    // =========================================================================

    // Màn hình chọn phương thức thanh toán cho người mua
    public function checkout($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'confirmed') {
            $this->session->set_flashdata('error', 'Đơn hàng không hợp lệ hoặc không ở trạng thái chờ thanh toán.');
            redirect('orders?tab=buy');
            return;
        }

        if ($order['payment_status'] === 'paid') {
            $this->session->set_flashdata('success', 'Đơn hàng này đã được thanh toán!');
            redirect('orders?tab=buy');
            return;
        }

        $this->load->model('Wallet_model');
        $data['wallet'] = $this->Wallet_model->get_or_create_wallet($buyer_id);
        $data['order']  = $order;
        $data['total_amount'] = $order['price'] * $order['quantity'];
        $data['unread_count'] = $this->Message_model->count_unread($buyer_id);

        $this->load->view('partials/header', $data);
        $this->load->view('orders/checkout', $data);
        $this->load->view('partials/footer');
    }

    // Xử lý thanh toán / chọn phương thức
    public function process_checkout($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);
        $method   = $this->input->post('payment_method', TRUE);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'confirmed') {
            $this->session->set_flashdata('error', 'Đơn hàng không hợp lệ để thanh toán.');
            redirect('orders?tab=buy');
            return;
        }

        if (!in_array($method, ['wallet', 'cod'])) {
            $this->session->set_flashdata('error', 'Phương thức thanh toán không hợp lệ.');
            redirect('orders/checkout/' . $order_id);
            return;
        }

        $amount = $order['price'] * $order['quantity'];
        $buyer_name = $this->session->userdata('full_name');

        if ($method === 'wallet') {
            $this->load->model('Wallet_model');
            $result = $this->Wallet_model->pay_order($buyer_id, $order['seller_id'], $order_id, $amount);

            if ($result === TRUE) {
                $this->db->where('id', $order_id)->update('orders', [
                    'payment_method' => 'wallet',
                    'payment_status' => 'paid',
                    'status' => 'processing'
                ]);

                $this->Message_model->send_message([
                    'sender_id'   => $buyer_id,
                    'receiver_id' => $order['seller_id'],
                    'post_id'     => $order['post_id'],
                    'content'     => "💰 [{$buyer_name}] đã thanh toán thành công " . number_format($amount, 0, ',', '.') . "đ qua Ví HCMUEPay. Hệ thống đã tạm giữ số tiền này an toàn. Vui lòng tiến hành bàn giao sách cho người mua.",
                ]);

                $this->session->set_flashdata('success', '✅ Đã thanh toán! Vui lòng chờ người bán giao hàng.');
                redirect('orders?tab=buy');
            } else {
                $this->session->set_flashdata('error', $result);
                redirect('orders/checkout/' . $order_id);
            }
        } else {
            // COD
            $this->db->where('id', $order_id)->update('orders', [
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'status' => 'processing'
            ]);

            $this->Message_model->send_message([
                'sender_id'   => $buyer_id,
                'receiver_id' => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'content'     => "🤝 [{$buyer_name}] đã chọn Giao dịch trực tiếp (COD). Vui lòng chủ động liên hệ với người mua để hẹn thời gian và địa điểm giao nhận sách.",
            ]);

            $this->session->set_flashdata('success', '✅ Đã chốt đơn COD! Vui lòng chờ người bán giao hàng.');
            redirect('orders?tab=buy');
        }
    }

    if ($order['payment_method'] === 'cod') {
            $this->Order_model->update_status($order_id, 'completed', ['payment_status' => 'paid']);
        } else {
            $this->Order_model->update_status($order_id, 'completed');
        }

        // Nếu thanh toán qua ví, giải ngân cho người bán
        if ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid') {
            $this->load->model('Wallet_model');
            $this->Wallet_model->release_escrow($order['seller_id'], $order_id, $order['price'] * $order['quantity']);
        }

        // Gửi tin nhắn tự động dẫn đến trang đánh giá
        $this->Message_model->send_message([
            'sender_id'   => $order['seller_id'],
            'receiver_id' => $buyer_id,
            'post_id'     => $order['post_id'],
            'content'     => "🎉 Giao dịch hoàn tất! Cảm ơn bạn đã tin dùng HCMUE BookSwap. Hãy để lại đánh giá cho người bán tại đây: " . site_url('orders/rate/' . $order_id),
        ]);

        // Thông báo cho người bán
        $buyer_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $buyer_id,
            'receiver_id' => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [{$buyer_name}] đã xác nhận nhận sách \"{$order['post_title']}\". Giao dịch hoàn tất!",
        ]);

        $this->session->set_flashdata('success', '✅ Xác nhận nhận sách thành công! Vui lòng để lại đánh giá cho người bán.');
        redirect('orders/rate/' . $order_id);
    }

    // Người mua báo tranh chấp
    public function dispute($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);
        $reason   = $this->input->post('dispute_reason', TRUE) ?: 'Chưa nhận được hàng.';

        if (!$order || $order['buyer_id'] != $buyer_id || !in_array($order['status'], ['confirmed', 'processing', 'delivering'])) {
            $this->session->set_flashdata('error', 'Không thể báo cáo đơn hàng này!');
            redirect('orders?tab=buy');
            return;
        }

        $this->Order_model->update_status($order_id, 'disputed', ['reject_reason' => $reason]);

        // Thông báo cho người bán
        $buyer_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $buyer_id,
            'receiver_id' => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'content'     => "⚠️ [{$buyer_name}] báo vấn đề với đơn hàng \"{$order['post_title']}\". Lý do: {$reason}. Vui lòng liên hệ giải quyết.",
        ]);

        $this->session->set_flashdata('error', '⚠️ Đã báo tranh chấp. Hãy liên hệ người bán để giải quyết.');
        redirect('orders/detail/' . $order_id);
    }

    // Trang đánh giá sau khi nhận hàng
    public function rate($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'completed') {
            $this->session->set_flashdata('error', 'Bạn chưa thể đánh giá đơn hàng này!');
            redirect('orders?tab=buy');
            return;
        }

        // Kiểm tra đã đánh giá chưa
        $already_rated = $this->Rating_model->has_rated_order($order_id, $buyer_id);

        $data['order']         = $order;
        $data['already_rated'] = $already_rated;
        $data['unread_count']  = $this->Message_model->count_unread($buyer_id);
        $data['pending_count'] = $this->Order_model->count_action_required($buyer_id);

        $this->load->view('partials/header', $data);
        $this->load->view('orders/rate', $data);
        $this->load->view('partials/footer');
    }

    // Gửi đánh giá từ trang rate
    public function submit_rating($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'completed') {
            redirect('orders?tab=buy');
            return;
        }

        if ($this->Rating_model->has_rated_order($order_id, $buyer_id)) {
            $this->session->set_flashdata('error', 'Bạn đã đánh giá đơn hàng này rồi!');
            redirect('orders?tab=buy');
            return;
        }

        $stars = (int) $this->input->post('stars');
        if ($stars < 1 || $stars > 5) {
            $this->session->set_flashdata('error', 'Vui lòng chọn số sao!');
            redirect('orders/rate/' . $order_id);
            return;
        }

        $this->Rating_model->add_rating([
            'reviewer_id' => $buyer_id,
            'seller_id'   => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'order_id'    => $order_id,
            'stars'       => $stars,
            'comment'     => $this->input->post('comment', TRUE),
        ]);

        $this->session->set_flashdata('success', '⭐ Đánh giá của bạn đã được ghi nhận! Cảm ơn bạn.');
        redirect('orders?tab=buy');
    }

    // Hủy đơn (buyer hủy khi pending, seller hủy khi confirmed)
    public function cancel($order_id) {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $order   = $this->Order_model->get_order_by_id($order_id);

        if (!$order) { show_404(); }

        $can_cancel = (
            ($order['buyer_id']  == $user_id && $order['status'] === 'pending') ||
            ($order['seller_id'] == $user_id && in_array($order['status'], ['pending', 'confirmed', 'processing']))
        );

        if (!$can_cancel) {
            $this->session->set_flashdata('error', 'Không thể hủy đơn hàng này!');
            redirect('orders');
            return;
        }

        $was_confirmed = in_array($order['status'], ['confirmed', 'processing', 'delivering']);
        $this->Order_model->update_status($order_id, 'cancelled');

        if ($was_confirmed) {
            $this->Trade_model->increment_quantity($order['post_id'], $order['quantity']);
        }

        // Hoàn tiền nếu đã thanh toán
        // FIX #9: Lưu lại giá trị payment_status TRƯỚC khi update (tránh kiểm tra sai sau khi đã dùng)
        $was_wallet_paid = ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid');
        if ($was_wallet_paid) {
            $this->load->model('Wallet_model');
            $this->Wallet_model->refund_order($order['buyer_id'], $order['seller_id'], $order_id, $order['price'] * $order['quantity']);
            $this->db->where('id', $order_id)->update('orders', ['payment_status' => 'refunded']);
        }

        $other_id  = ($order['buyer_id'] == $user_id) ? $order['seller_id'] : $order['buyer_id'];
        $user_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $user_id,
            'receiver_id' => $other_id,
            'post_id'     => $order['post_id'],
            'content'     => "❌ [{$user_name}] đã hủy đơn hàng \"{$order['post_title']}\"." . ($was_wallet_paid ? " Hệ thống đã hoàn tiền lại vào ví." : ""),
        ]);

        $this->session->set_flashdata('success', 'Đã hủy đơn hàng' . ($was_wallet_paid ? ' và hoàn tiền.' : '.'));
        redirect('orders');
    }


    // Helper gửi sự kiện Pusher cho đơn hàng
    private function trigger_pusher_order($user_id, $message, $order_id) {
        $app_id  = getenv('PUSHER_APP_ID') ?: (isset($_ENV['PUSHER_APP_ID']) ? $_ENV['PUSHER_APP_ID'] : '1906752');
        $app_key = getenv('PUSHER_APP_KEY') ?: (isset($_ENV['PUSHER_APP_KEY']) ? $_ENV['PUSHER_APP_KEY'] : 'e030a21054a86bd511ce');
        $app_sec = getenv('PUSHER_APP_SECRET') ?: (isset($_ENV['PUSHER_APP_SECRET']) ? $_ENV['PUSHER_APP_SECRET'] : '7cb493c0bc5894b4625b');
        $app_clu = getenv('PUSHER_APP_CLUSTER') ?: (isset($_ENV['PUSHER_APP_CLUSTER']) ? $_ENV['PUSHER_APP_CLUSTER'] : 'ap1');

        if (class_exists('Pusher\Pusher')) {
            try {
                $pusher = new Pusher\Pusher($app_key, $app_sec, $app_id, ['cluster' => $app_clu, 'useTLS' => true]);
                $pusher->trigger('user-'.$user_id, 'order-event', [
                    'message' => $message,
                    'order_id' => $order_id
                ]);
            } catch (Exception $e) { }
        }
    }

    public function verify_handover() {
        $this->require_login();
        $seller_id = $this->session->userdata('user_id');
        $code = $this->input->post('code', TRUE);

        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận trống!']);
            return;
        }

        $this->db->where('seller_id', $seller_id);
        $this->db->where('status', 'processing');
        $this->db->group_start();
        $this->db->where('qr_token', $code);
        $this->db->or_where('otp_code', $code);
        $this->db->group_end();
        $order = $this->db->get('orders')->row_array();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận không hợp lệ hoặc đơn hàng không tồn tại!']);
            return;
        }

        $update_data = ['status' => 'completed'];
        if ($order['payment_method'] === 'cod') {
            $update_data['payment_status'] = 'paid';
        }
        $this->db->where('id', $order['id'])->update('orders', $update_data);

        if ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid') {
            $this->load->model('Wallet_model');
            $this->Wallet_model->release_escrow($order['seller_id'], $order['id'], $order['price'] * $order['quantity']);
        }

        $seller_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $seller_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "🎉 [$seller_name] đã giao sách và hoàn tất giao dịch. Cảm ơn bạn đã tin dùng HCMUE BookSwap! Hãy để lại đánh giá cho người bán tại đây: " . site_url('orders/rate/' . $order['id']),
        ]);
        
        $this->trigger_pusher_order($order['buyer_id'], 'Đơn hàng đã giao thành công. Vui lòng đánh giá người bán!', $order['id']);
        $this->trigger_pusher_order($seller_id, 'Xác thực thành công. Giao dịch hoàn tất!', $order['id']);

        echo json_encode(['success' => true, 'message' => 'Xác nhận giao hàng thành công! Giao dịch hoàn tất.']);
    }

    public function report_seller($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'completed') {
            $this->session->set_flashdata('error', 'Không thể báo cáo đơn hàng này!');
            redirect('orders?tab=buy');
            return;
        }

        $reason = $this->input->post('report_reason', TRUE);
        // We will just use the disputes table for reports as well, with a special status 'reported'
        // Or if 'reports' doesn't exist, we fallback to disputes but without changing order status from completed.
        // Actually let's just insert into disputes table.
        $this->db->insert('disputes', [
            'order_id' => $order_id,
            'created_by' => $buyer_id,
            'reason' => 'BÁO CÁO SAU GIAO DỊCH: ' . $reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->session->set_flashdata('success', 'Đã gửi báo cáo thành công. Admin sẽ xem xét.');
        redirect('orders/detail/' . $order_id);
    }


}
