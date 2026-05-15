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
        $data['pending_count']    = $this->Order_model->count_pending_for_seller($user_id);
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

        // Chỉ người liên quan mới xem được
        if ($order['seller_id'] != $user_id && $order['buyer_id'] != $user_id) {
            show_error('Bạn không có quyền xem đơn hàng này.', 403);
        }

        $data['order']        = $order;
        $data['is_seller']    = ($order['seller_id'] == $user_id);
        $data['is_buyer']     = ($order['buyer_id']  == $user_id);
        $data['unread_count'] = $this->Message_model->count_unread($user_id);
        $data['pending_count']= $this->Order_model->count_pending_for_seller($user_id);

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

        $this->session->set_flashdata('success', '✅ Đã gửi yêu cầu mua! Chờ người bán xác nhận nhé.');
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

        // Thông báo cho người mua
        $seller_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $seller_id,
            'receiver_id' => $order['buyer_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [{$seller_name}] đã xác nhận đơn hàng \"{$order['post_title']}\". Hãy liên hệ để hẹn giao nhận sách nhé! Xem chi tiết: " . site_url('orders/detail/' . $order_id),
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

        $this->session->set_flashdata('success', 'Đã từ chối đơn hàng.');
        redirect('orders?tab=sell');
    }

    // Người mua xác nhận đã nhận hàng
    public function received($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'confirmed') {
            $this->session->set_flashdata('error', 'Không thể xác nhận đơn hàng này!');
            redirect('orders?tab=buy');
            return;
        }

        // Một lớp kiểm tra cuối cùng trước khi trừ số lượng
        $post = $this->Trade_model->get_post_by_id($order['post_id']);
        if (!$post || $post['quantity'] < $order['quantity']) {
            $this->session->set_flashdata('error', '❌ Rất tiếc, mặt hàng này hiện tại đã hết hàng hoặc đã được bán cho người khác trước!');
            redirect('orders?tab=buy');
            return;
        }

        $this->Order_model->update_status($order_id, 'completed');

        // Trừ số lượng sách
        $this->Trade_model->decrement_quantity($order['post_id'], $order['quantity']);

        // Gửi tin nhắn tự động dẫn đến trang đánh giá
        $this->Message_model->send_message([
            'sender_id'   => $order['seller_id'],
            'receiver_id' => $buyer_id,
            'post_id'     => $order['post_id'],
            'content'     => "🎉 Cảm ơn bạn đã nhận sách \"{$order['post_title']}\"! Hãy để lại đánh giá cho người bán nhé: " . site_url('orders/rate/' . $order_id),
        ]);

        // Thông báo cho người bán
        $buyer_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $buyer_id,
            'receiver_id' => $order['seller_id'],
            'post_id'     => $order['post_id'],
            'content'     => "✅ [{$buyer_name}] đã xác nhận nhận sách \"{$order['post_title']}\". Giao dịch hoàn tất!",
        ]);

        $this->session->set_flashdata('success', '✅ Đã xác nhận nhận hàng! Hãy đánh giá người bán nhé.');
        redirect('orders/rate/' . $order_id);
    }

    // Người mua báo tranh chấp
    public function dispute($order_id) {
        $this->require_login();
        $buyer_id = $this->session->userdata('user_id');
        $order    = $this->Order_model->get_order_by_id($order_id);
        $reason   = $this->input->post('dispute_reason', TRUE) ?: 'Chưa nhận được hàng.';

        if (!$order || $order['buyer_id'] != $buyer_id || $order['status'] !== 'confirmed') {
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
        $data['pending_count'] = $this->Order_model->count_pending_for_seller($buyer_id);

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
            ($order['seller_id'] == $user_id && in_array($order['status'], ['pending', 'confirmed']))
        );

        if (!$can_cancel) {
            $this->session->set_flashdata('error', 'Không thể hủy đơn hàng này!');
            redirect('orders');
            return;
        }

        $this->Order_model->update_status($order_id, 'cancelled');

        $other_id = ($order['buyer_id'] == $user_id) ? $order['seller_id'] : $order['buyer_id'];
        $user_name = $this->session->userdata('full_name');
        $this->Message_model->send_message([
            'sender_id'   => $user_id,
            'receiver_id' => $other_id,
            'post_id'     => $order['post_id'],
            'content'     => "❌ [{$user_name}] đã hủy đơn hàng \"{$order['post_title']}\".",
        ]);

        $this->session->set_flashdata('success', 'Đã hủy đơn hàng.');
        redirect('orders');
    }
}
