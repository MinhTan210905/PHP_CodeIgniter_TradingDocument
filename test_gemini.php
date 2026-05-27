<?php
$api_url_base = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=";
$env_content = file_get_contents('.env');
preg_match('/GEMINI_API_KEY=(.*)/', $env_content, $matches);
$api_key = trim(str_replace(['"', "'"], '', $matches[1] ?? ''));

$message = "web này dùng để làm gì á";
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
        'maxOutputTokens' => 800
    ]
]);

$url = $api_url_base . $api_key;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
echo "RESPONSE:\n" . $response . "\n";
