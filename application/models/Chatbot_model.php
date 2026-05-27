<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbot_model extends CI_Model {

    // Sử dụng gemini-3.5-flash theo hệ thống
    private $api_url_base = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=";
    private $api_key = "";

    public function __construct() {
        parent::__construct();
        $this->api_key = getenv('GEMINI_API_KEY') ?: (isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : '');
    }

    public function ask($message) {
        $message = trim($message);
        
        if (empty($message)) {
            return ['error' => 'Tin nhắn không được để trống.'];
        }

        if (empty($this->api_key)) {
            log_message('error', 'Chatbot: GEMINI_API_KEY is not defined.');
            return ['error' => 'Hệ thống AI hiện đang bảo trì (Thiếu API Key).'];
        }

        $system_instruction = "Bạn là HCMUE AI Assistant, nhân viên hỗ trợ ảo của nền tảng HCMUE BookSwap (Hệ thống trao đổi sách sinh viên Đại học Sư Phạm TP.HCM). 
Nhiệm vụ của bạn là hỗ trợ, hướng dẫn người dùng sử dụng trang web này một cách thân thiện, tự nhiên, ngắn gọn và nhiệt tình.
Các tính năng chính của website:
- Đăng bài: Đăng tin bán/trao đổi sách cũ.
- Tìm kiếm & Lọc sách: Tìm sách theo từ khóa, lọc theo danh mục, giá cả, tình trạng.
- Wishlist (Sách mong muốn): Thêm sách vào danh sách mong muốn để nhận email thông báo tự động khi có người đăng bán sách đó (hỗ trợ tìm kiếm gần đúng, không dấu).
- Nhắn tin (Chat): Nhắn tin trực tiếp giữa người mua và người bán ngay trên web.
- Quản lý cá nhân: Quản lý bài đăng của mình, thay đổi thông tin.
- Kiểm duyệt tự động: Có hệ thống AI (Gemini) tự động kiểm duyệt bình luận và bài viết (nếu có ngôn từ độc hại sẽ bị chặn).
- Admin Dashboard: Quản trị viên có thể duyệt bài, ẩn bài, xóa bài, khóa tài khoản, đánh giá chất lượng bài viết.
Hãy trả lời câu hỏi của người dùng một cách chính xác dựa trên thông tin trên. Nếu câu hỏi ngoài lề hoặc không liên quan đến trang web, giáo dục, hoặc sách, hãy từ chối một cách khéo léo và nhắc người dùng quay lại chủ đề chính.";

        $full_prompt = $system_instruction . "\n\nNgười dùng hỏi: \"" . $message . "\"\n\nHãy trả lời thật tự nhiên và hữu ích.";

        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $full_prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.6,
                'maxOutputTokens' => 8000
            ]
        ]);

        $url = $this->api_url_base . $this->api_url_clean_token($this->api_key);
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'Chatbot cURL Error: ' . $err);
            return ['error' => 'Lỗi kết nối đến máy chủ AI: ' . $err];
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            $err_msg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown error';
            log_message('error', 'Chatbot Gemini API Error: ' . $err_msg);
            return ['error' => 'Có lỗi xảy ra từ máy chủ AI. Vui lòng thử lại sau.'];
        }

        $reply = '';
        if (isset($result['candidates'][0]['content']['parts'])) {
            foreach ($result['candidates'][0]['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $reply .= $part['text'];
                }
            }
            $reply = trim($reply);
        }

        if (empty($reply)) {
            return ['error' => 'AI không có phản hồi.'];
        }

        // Chuyển Markdown cơ bản sang HTML (chỉ in đậm, in nghiêng, xuống dòng)
        $reply_html = htmlspecialchars($reply);
        $reply_html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $reply_html);
        $reply_html = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $reply_html);
        $reply_html = nl2br($reply_html);

        return ['reply' => $reply_html];
    }

    private function api_url_clean_token($token) {
        return trim(str_replace(['"', "'"], '', $token));
    }
}
