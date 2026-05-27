<?php
$file = 'd:/Student_HCMUE/PHP/htdocs/PHP_CodeIgniter_TradingDocument/application/controllers/Orders.php';
$content = file_get_contents($file);

// 1. request_buy
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã gửi yêu cầu mua! Vui lòng chờ người bán xác nhận.');",
    "\$this->trigger_pusher_order(\$post['user_id'], 'Bạn có yêu cầu mua mới!', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã gửi yêu cầu mua! Vui lòng chờ người bán xác nhận.');",
    $content
);

// 2. confirm
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã xác nhận đơn hàng! Chờ người mua thanh toán.');",
    "\$this->trigger_pusher_order(\$order['buyer_id'], 'Đơn hàng của bạn đã được người bán xác nhận!', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã xác nhận đơn hàng! Chờ người mua thanh toán.');",
    $content
);

// 3. reject
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã từ chối đơn hàng.');",
    "\$this->trigger_pusher_order(\$order['buyer_id'], 'Người bán đã từ chối yêu cầu mua của bạn.', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã từ chối đơn hàng.');",
    $content
);

// 4. process_checkout
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã xác nhận phương thức thanh toán. Chờ giao hàng!');",
    "\$this->trigger_pusher_order(\$order['seller_id'], 'Người mua đã chọn phương thức thanh toán.', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã xác nhận phương thức thanh toán. Chờ giao hàng!');",
    $content
);

// 5. verify_handover (QR)
$content = preg_replace(
    "/\\\$this->session->set_flashdata\\('success', 'Xác nhận giao dịch thành công! Tiền đã được chuyển vào ví của bạn.'\\);/i",
    "\$this->trigger_pusher_order(\$order['buyer_id'], 'Giao dịch gặp mặt trực tiếp đã hoàn thành!', \$order_id);\n        \$this->session->set_flashdata('success', 'Xác nhận giao dịch thành công! Tiền đã được chuyển vào ví của bạn.');",
    $content
);

// 6. delivered
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã xác nhận giao cho đơn vị vận chuyển!');",
    "\$this->trigger_pusher_order(\$order['buyer_id'], 'Người bán đã giao hàng cho đơn vị vận chuyển!', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã xác nhận giao cho đơn vị vận chuyển!');",
    $content
);

// 7. received
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã xác nhận nhận hàng! Đơn hàng hoàn tất.');",
    "\$this->trigger_pusher_order(\$order['seller_id'], 'Người mua đã nhận được hàng! Giao dịch hoàn tất.', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã xác nhận nhận hàng! Đơn hàng hoàn tất.');",
    $content
);

// 8. cancel
$content = str_replace(
    "\$this->session->set_flashdata('success', 'Đã hủy đơn hàng.');",
    "\$other_id = (\$order['buyer_id'] == \$user_id) ? \$order['seller_id'] : \$order['buyer_id'];\n        \$this->trigger_pusher_order(\$other_id, 'Đối tác đã hủy đơn hàng.', \$order_id);\n        \$this->session->set_flashdata('success', 'Đã hủy đơn hàng.');",
    $content
);

file_put_contents($file, $content);
echo "Done";
