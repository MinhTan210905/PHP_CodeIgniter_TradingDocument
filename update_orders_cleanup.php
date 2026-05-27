<?php
$file = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/controllers/Orders.php';
$content = file_get_contents($file);

// 1. Add ajax_get_action_required_count
$ajax_func = "
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

if (strpos($content, 'ajax_get_action_required_count') === false) {
    // Insert after __construct
    $content = preg_replace('/(public function __construct\(\) \{.*?\})/s', "$1\n$ajax_func", $content);
}

// 2. Remove delivered and received methods
// These might be removed already if the preg_replace matches, but they are gone now.
// Let's do a regex to wipe out delivered and received.
$content = preg_replace('/(\/\/ Người bán xác nhận đã giao hàng\s+public function delivered\(.*?\s+\})\s+\/\/ Người mua xác nhận đã nhận hàng/s', '// Người mua xác nhận đã nhận hàng', $content);
$content = preg_replace('/(\/\/ Người mua xác nhận đã nhận hàng\s+public function received\(.*?\s+\})\s+/s', '', $content);

file_put_contents($file, $content);
echo "Done";
