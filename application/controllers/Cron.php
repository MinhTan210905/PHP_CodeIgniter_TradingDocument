<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property Order_model $Order_model
 * @property Trade_model $Trade_model
 * @property Wallet_model $Wallet_model
 * @property Message_model $Message_model
 * @property Rating_model $Rating_model
 * @property Wishlist_model $Wishlist_model
 */
class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Order_model', 'Trade_model', 'Wallet_model', 'Message_model', 'Rating_model', 'Wishlist_model']);
    }

    public function run() {
        // Có thể bảo vệ endpoint này bằng token hoặc giới hạn gọi liên tục nếu muốn.
        // Ở đây được gọi tự do dạng ngầm bằng fetch.

        $this->process_auto_delivered();
        $this->process_auto_rating();
        $this->process_wishlist_notifications();

        // Trả về JSON để client biết đã chạy xong
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['status' => 'success', 'message' => 'Cron executed successfully']));
    }

    private function process_auto_delivered() {
        $orders = $this->Order_model->get_delivering_over_24h();

        foreach ($orders as $order) {
            $order_id = $order['id'];

            // Sách đã được trừ số lượng từ lúc xác nhận đơn hàng (confirm)

            // Cập nhật trạng thái
            if ($order['payment_method'] === 'cod') {
                $this->Order_model->update_status($order_id, 'completed', ['payment_status' => 'paid']);
            } else {
                $this->Order_model->update_status($order_id, 'completed');
            }

            // Nếu thanh toán qua ví, giải ngân cho người bán
            if ($order['payment_method'] === 'wallet' && $order['payment_status'] === 'paid') {
                $this->Wallet_model->release_escrow($order['seller_id'], $order_id, $order['price'] * $order['quantity']);
            }

            // Thông báo
            $this->Message_model->send_message([
                'sender_id'   => $order['buyer_id'], // Giả lập người mua gửi
                'receiver_id' => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'content'     => "✅ [Tự động] Hệ thống đã xác nhận nhận sách \"{$order['post_title']}\" do quá 24h. Giao dịch hoàn tất!",
            ]);

            $this->Message_model->send_message([
                'sender_id'   => $order['seller_id'], // Giả lập người bán gửi
                'receiver_id' => $order['buyer_id'],
                'post_id'     => $order['post_id'],
                'content'     => "🎉 [Tự động] Hệ thống đã tự động hoàn thành đơn sách \"{$order['post_title']}\" sau 24h giao hàng. Hãy để lại đánh giá cho người bán tại đây: " . site_url('orders/rate/' . $order_id),
            ]);
        }
    }

    private function process_auto_rating() {
        $orders = $this->Order_model->get_completed_unrated_over_24h();

        foreach ($orders as $order) {
            // Thêm đánh giá 5 sao
            $this->Rating_model->add_rating([
                'reviewer_id' => $order['buyer_id'],
                'seller_id'   => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'order_id'    => $order['id'],
                'stars'       => 5,
                'comment'     => 'Đánh giá tự động: Người mua không phản hồi sau 24h nhận hàng.',
            ]);

            // Thông báo
            $this->Message_model->send_message([
                'sender_id'   => 0, // 0 là hệ thống
                'receiver_id' => $order['seller_id'],
                'post_id'     => $order['post_id'],
                'content'     => "⭐ [Tự động] Hệ thống đã tự động đánh giá 5 sao cho giao dịch đơn hàng #" . $order['id'] . ". Cảm ơn bạn!",
            ]);
        }
    }

    /**
     * Quét bài đăng mới và so khớp với danh sách mong muốn
     * Gửi thông báo qua tin nhắn nội bộ + email khi tìm thấy sách phù hợp (>=70%)
     */
    private function process_wishlist_notifications() {
        $wishlists = $this->Wishlist_model->get_all_active();
        if (empty($wishlists)) return;

        // Lấy bài đăng mới được duyệt trong 24h gần nhất
        $this->db->select('posts.*, users.full_name as seller_name');
        $this->db->from('posts');
        $this->db->join('users', 'users.id = posts.user_id', 'left');
        $this->db->where('posts.status', 'available');
        $this->db->where('posts.created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $this->db->order_by('posts.created_at', 'DESC');
        $recent_posts = $this->db->get()->result_array();

        if (empty($recent_posts)) return;

        foreach ($wishlists as $wish) {
            $wish_title_lower = mb_strtolower(trim($wish['book_title']), 'UTF-8');

            foreach ($recent_posts as $post) {
                // Không tự gợi ý sách chính mình đăng
                if ((int)$post['user_id'] === (int)$wish['user_id']) continue;

                // Không gửi trùng: chỉ thông báo bài mới hơn bài cuối đã thông báo
                if ($wish['last_notified_post_id'] && (int)$post['id'] <= (int)$wish['last_notified_post_id']) continue;

                $post_title_lower = mb_strtolower(trim($post['title']), 'UTF-8');

                // Sử dụng hàm so khớp thông minh tiếng Việt chuẩn hóa từ Wishlist_model
                if ($this->Wishlist_model->check_title_match($wish_title_lower, $post_title_lower)) {
                    $price_formatted = number_format($post['price'], 0, ',', '.') . 'đ';
                    $detail_url = site_url('trade/detail/' . $post['id']);

                    // 1. Gửi tin nhắn nội bộ
                    $this->Message_model->send_message([
                        'sender_id'   => (int)$post['user_id'],
                        'receiver_id' => (int)$wish['user_id'],
                        'post_id'     => (int)$post['id'],
                        'content'     => "📚 [Gợi ý] Có bài đăng mới phù hợp với mong muốn \"{$wish['book_title']}\": \"{$post['title']}\" — Giá: {$price_formatted}. Xem chi tiết: {$detail_url}",
                    ]);

                    // 2. Gửi email thông báo
                    $this->_send_wishlist_email(
                        $wish['email'],
                        $wish['full_name'],
                        $wish['book_title'],
                        $post['title'],
                        $price_formatted,
                        $post['seller_name'] ?? 'Người bán',
                        $detail_url
                    );

                    // Cập nhật bài cuối đã thông báo
                    $this->Wishlist_model->update_last_notified($wish['id'], $post['id']);

                    // Chỉ gửi 1 thông báo cho mỗi wishlist/lần quét (tránh spam)
                    break;
                }
            }
        }
    }

    /**
     * Gửi email HTML thông báo sách phù hợp
     */
    private function _send_wishlist_email($to_email, $to_name, $wish_title, $post_title, $price, $seller_name, $detail_url) {
        $this->load->library('email');
        $this->email->initialize(['mailtype' => 'html']);
        $this->email->from($this->config->item('smtp_user') ?? 'no-reply@hcmue.edu.vn', 'HCMUE BookSwap');
        $this->email->to($to_email);
        $this->email->subject('[HCMUE BookSwap] Sách bạn đang tìm đã có người đăng bán!');

        $full_name = htmlspecialchars($to_name);
        $wish_esc  = htmlspecialchars($wish_title);
        $post_esc  = htmlspecialchars($post_title);
        $seller_esc = htmlspecialchars($seller_name);

        $html = "
        <!DOCTYPE html>
        <html lang='vi'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Inter,Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 20px;'>
                <tr><td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);max-width:560px;width:100%;'>
                        <tr>
                            <td style='background:linear-gradient(135deg,#003F8A,#0052B4);padding:28px 36px;text-align:center;'>
                                <h1 style='margin:0;color:#ffffff;font-size:20px;font-weight:800;'>📚 HCMUE BookSwap</h1>
                                <p style='margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:12px;'>Thông báo sách phù hợp với mong muốn của bạn</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:32px 36px 24px;'>
                                <p style='margin:0 0 14px;font-size:15px;color:#374151;'>Xin chào <strong>{$full_name}</strong>,</p>
                                <p style='margin:0 0 20px;font-size:14px;color:#6B7280;line-height:1.7;'>
                                    Hệ thống phát hiện có bài đăng mới phù hợp với mong muốn <strong>\"{$wish_esc}\"</strong> của bạn:
                                </p>
                                <div style='background:#F0F5FF;border:1px solid #DBEAFE;border-radius:12px;padding:20px;margin:0 0 24px;'>
                                    <p style='margin:0 0 8px;font-size:16px;font-weight:700;color:#1E3A8A;'>{$post_esc}</p>
                                    <p style='margin:0 0 4px;font-size:13px;color:#6B7280;'>Giá: <strong style='color:#DC2626;'>{$price}</strong></p>
                                    <p style='margin:0;font-size:13px;color:#6B7280;'>Người bán: <strong>{$seller_esc}</strong></p>
                                </div>
                                <a href='{$detail_url}' style='display:inline-block;background:linear-gradient(135deg,#003F8A,#0052B4);color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px;'>Xem chi tiết sách</a>
                            </td>
                        </tr>
                        <tr>
                            <td style='background:#F8FAFC;padding:16px 36px;border-top:1px solid #E5E7EB;text-align:center;'>
                                <p style='margin:0;font-size:11px;color:#9CA3AF;'>Bạn nhận email này vì đã đăng ký theo dõi sách trên HCMUE BookSwap.<br>Để tắt thông báo, vui lòng vào <a href='" . site_url('wishlist') . "' style='color:#2563EB;'>Danh sách mong muốn</a> và tắt theo dõi.</p>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            </table>
        </body></html>";

        $this->email->message($html);
        $this->email->send(); // Không cần kiểm tra kết quả, chạy ngầm
    }
}
