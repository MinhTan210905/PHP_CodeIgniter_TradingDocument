<?php
$file = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/controllers/Orders.php';
$content = file_get_contents($file);

// Replace count_pending_for_seller with count_action_required
$content = str_replace('count_pending_for_seller', 'count_action_required', $content);

// Ensure ajax_get_action_required_count is there
if (strpos($content, 'ajax_get_action_required_count') === false) {
    $ajax = "
    // [AJAX] API lấy số đơn hàng cần xử lý
    public function ajax_get_action_required_count() {
        if (!\$this->session->userdata('logged_in')) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            return;
        }
        \$user_id = \$this->session->userdata('user_id');
        \$this->load->model('Order_model');
        \$count = \$this->Order_model->count_action_required(\$user_id);
        echo json_encode(['success' => true, 'count' => \$count]);
    }
";
    $content = preg_replace('/(public function __construct\(\) \{.*?\})/s', "$1\n$ajax", $content);
}

// Remove delivered and received completely
$content = preg_replace('/(\/\/ Người bán xác nhận đã giao hàng\s+public function delivered\(.*?\s+\})\s+\/\/ Người mua xác nhận đã nhận hàng/s', '// Người mua xác nhận đã nhận hàng', $content);
$content = preg_replace('/(\/\/ Người mua xác nhận đã nhận hàng\s+public function received\(.*?\s+\})\s+/s', '', $content);

// Add verify_handover, report_seller, trigger_pusher_order at the end of the class (before the last '}')
$new_methods = "

    // Helper gửi sự kiện Pusher cho đơn hàng
    private function trigger_pusher_order(\$user_id, \$message, \$order_id) {
        \$app_id  = getenv('PUSHER_APP_ID') ?: (isset(\$_ENV['PUSHER_APP_ID']) ? \$_ENV['PUSHER_APP_ID'] : '1906752');
        \$app_key = getenv('PUSHER_APP_KEY') ?: (isset(\$_ENV['PUSHER_APP_KEY']) ? \$_ENV['PUSHER_APP_KEY'] : 'e030a21054a86bd511ce');
        \$app_sec = getenv('PUSHER_APP_SECRET') ?: (isset(\$_ENV['PUSHER_APP_SECRET']) ? \$_ENV['PUSHER_APP_SECRET'] : '7cb493c0bc5894b4625b');
        \$app_clu = getenv('PUSHER_APP_CLUSTER') ?: (isset(\$_ENV['PUSHER_APP_CLUSTER']) ? \$_ENV['PUSHER_APP_CLUSTER'] : 'ap1');

        if (class_exists('Pusher\Pusher')) {
            try {
                \$pusher = new Pusher\Pusher(\$app_key, \$app_sec, \$app_id, ['cluster' => \$app_clu, 'useTLS' => true]);
                \$pusher->trigger('user-'.\$user_id, 'order-event', [
                    'message' => \$message,
                    'order_id' => \$order_id
                ]);
            } catch (Exception \$e) { }
        }
    }

    public function verify_handover() {
        \$this->require_login();
        \$seller_id = \$this->session->userdata('user_id');
        \$code = \$this->input->post('code', TRUE);

        if (empty(\$code)) {
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận trống!']);
            return;
        }

        \$this->db->where('seller_id', \$seller_id);
        \$this->db->where('status', 'processing');
        \$this->db->group_start();
        \$this->db->where('qr_token', \$code);
        \$this->db->or_where('otp_code', \$code);
        \$this->db->group_end();
        \$order = \$this->db->get('orders')->row_array();

        if (!\$order) {
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận không hợp lệ hoặc đơn hàng không tồn tại!']);
            return;
        }

        \$update_data = ['status' => 'completed'];
        if (\$order['payment_method'] === 'cod') {
            \$update_data['payment_status'] = 'paid';
        }
        \$this->db->where('id', \$order['id'])->update('orders', \$update_data);

        if (\$order['payment_method'] === 'wallet' && \$order['payment_status'] === 'paid') {
            \$this->load->model('Wallet_model');
            \$this->Wallet_model->release_escrow(\$order['seller_id'], \$order['id'], \$order['price'] * \$order['quantity']);
        }

        \$seller_name = \$this->session->userdata('full_name');
        \$this->Message_model->send_message([
            'sender_id'   => \$seller_id,
            'receiver_id' => \$order['buyer_id'],
            'post_id'     => \$order['post_id'],
            'content'     => \"🎉 [\$seller_name] đã giao sách và hoàn tất giao dịch. Cảm ơn bạn đã tin dùng HCMUE BookSwap! Hãy để lại đánh giá cho người bán tại đây: \" . site_url('orders/rate/' . \$order['id']),
        ]);
        
        \$this->trigger_pusher_order(\$order['buyer_id'], 'Đơn hàng đã giao thành công. Vui lòng đánh giá người bán!', \$order['id']);
        \$this->trigger_pusher_order(\$seller_id, 'Xác thực thành công. Giao dịch hoàn tất!', \$order['id']);

        echo json_encode(['success' => true, 'message' => 'Xác nhận giao hàng thành công! Giao dịch hoàn tất.']);
    }

    public function report_seller(\$order_id) {
        \$this->require_login();
        \$buyer_id = \$this->session->userdata('user_id');
        \$order = \$this->Order_model->get_order_by_id(\$order_id);

        if (!\$order || \$order['buyer_id'] != \$buyer_id || \$order['status'] !== 'completed') {
            \$this->session->set_flashdata('error', 'Không thể báo cáo đơn hàng này!');
            redirect('orders?tab=buy');
            return;
        }

        \$reason = \$this->input->post('report_reason', TRUE);
        // We will just use the disputes table for reports as well, with a special status 'reported'
        // Or if 'reports' doesn't exist, we fallback to disputes but without changing order status from completed.
        // Actually let's just insert into disputes table.
        \$this->db->insert('disputes', [
            'order_id' => \$order_id,
            'created_by' => \$buyer_id,
            'reason' => 'BÁO CÁO SAU GIAO DỊCH: ' . \$reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        \$this->session->set_flashdata('success', 'Đã gửi báo cáo thành công. Admin sẽ xem xét.');
        redirect('orders/detail/' . \$order_id);
    }

";

if (strpos($content, 'function verify_handover') === false) {
    // replace last }
    $content = preg_replace('/\}\s*$/', $new_methods . "\n}\n", $content);
}

file_put_contents($file, $content);
echo "Done fix_orders";
