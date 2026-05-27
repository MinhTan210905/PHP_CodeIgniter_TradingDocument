<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_moderation_model extends CI_Model {

    // Chuyển sang sử dụng Google Gemini API (gemini-3.5-flash) - cực nhanh và hiểu tiếng Việt cực đỉnh
    private $api_url_base = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=";
    private $api_key = "";

    public function __construct() {
        parent::__construct();
        $this->load->database();
        // Lấy GEMINI_API_KEY từ file .env (nếu không có thì dùng biến môi trường chung)
        $this->api_key = getenv('GEMINI_API_KEY') ?: (isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : '');
    }

    public function analyze_text($text) {
        $text = trim($text);
        
        $fallback_res = [
            'action' => 'allow',
            'label'  => 0,
            'score'  => 1.0,
            'scores' => [0 => 1.0, 1 => 0.0, 2 => 0.0],
            'error'  => null
        ];

        if (empty($text)) {
            return $fallback_res;
        }

        // Lớp 1: Lọc thô siêu nhanh (Local Filter)
        // Nếu phát hiện từ khóa thô tục rõ ràng -> Block ngay lập tức (0ms, không tốn API)
        if ($this->local_toxic_filter($text)) {
            return [
                'action' => 'block',
                'label'  => 2,
                'score'  => 1.0,
                'scores' => [0 => 0.0, 1 => 0.0, 2 => 1.0],
                'error'  => 'Matched local toxicity filter'
            ];
        }

        // Lớp 2: Kiểm duyệt thông minh bằng Gemini API (gemini-1.5-flash)
        if (empty($this->api_key)) {
            log_message('error', 'AI Moderation: GEMINI_API_KEY is not defined in .env.');
            return $fallback_res;
        }

        $prompt = "Bạn là hệ thống kiểm duyệt nội dung ẩn danh của một ứng dụng trao đổi sách cho sinh viên Đại học Sư Phạm (HCMUE). Hãy phân tích đoạn văn bản sau đây xem có chứa từ ngữ thô tục, chửi thề, kích động, quấy rối, xúc phạm, hoặc ngôn từ độc hại (toxic) theo ngôn ngữ mạng / Gen Z của Việt Nam hay không.\n\nĐoạn văn bản: \"" . $text . "\"\n\nTrả lời CHỈ BẰNG một chuỗi JSON duy nhất, không kèm markdown, không kèm giải thích, có định dạng sau:\n{\"is_toxic\": true/false, \"confidence\": 0.0->1.0}";

        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 800,
                'responseMimeType' => 'application/json'
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            log_message('error', 'AI Moderation cURL Error: ' . $err);
            $fallback_res['error'] = 'cURL Error: ' . $err;
            return $fallback_res;
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            $err_msg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown error';
            log_message('error', 'AI Moderation Gemini API Error: ' . $err_msg . ' | HTTP Code: ' . $http_code);
            $fallback_res['error'] = 'Gemini API Error: ' . $err_msg;
            return $fallback_res;
        }

        // Lấy text JSON từ kết quả trả về của Gemini
        $generated_text = '';
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = trim($result['candidates'][0]['content']['parts'][0]['text']);
        }

        if (empty($generated_text)) {
            return $fallback_res;
        }

        // Parse JSON từ Gemini
        $ai_response = json_decode($generated_text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'AI Moderation Gemini parsing error. Raw output: ' . $generated_text);
            return $fallback_res;
        }

        $is_toxic   = isset($ai_response['is_toxic']) ? filter_var($ai_response['is_toxic'], FILTER_VALIDATE_BOOLEAN) : false;
        $confidence = isset($ai_response['confidence']) ? (float)$ai_response['confidence'] : 0.0;

        $toxic_score     = $is_toxic ? $confidence : (1.0 - $confidence);
        $non_toxic_score = $is_toxic ? (1.0 - $confidence) : $confidence;

        // Quyết định action: block nếu AI phán đoán là toxic
        $action     = 'allow';
        $pred_label = 0;  // 0 = clean
        if ($is_toxic && $confidence > 0.5) {
            $action     = 'block';
            $pred_label = 2;  // 2 = toxic (giữ tương thích với schema database)
        }

        return [
            'action' => $action,
            'label'  => $pred_label,
            'score'  => $toxic_score > $non_toxic_score ? $toxic_score : $non_toxic_score,
            'scores' => [0 => $non_toxic_score, 1 => 0.0, 2 => $toxic_score],
            'error'  => null
        ];
    }

    private function api_url_clean_token($token) {
        return trim(str_replace(['"', "'"], '', $token));
    }

    private function local_toxic_filter($text) {
        $text = mb_strtolower($text, 'UTF-8');
        
        // Danh sách các từ khóa tục tĩu, nhạy cảm, xúc phạm hoặc toxic bằng tiếng Việt phổ biến
        $bad_words = [
            'địt', 'đụ', 'đéo', 'lồn', 'cặc', 'buồi', 'chó đẻ', 'súc sinh', 'óc chó', 'ngu lồn', 
            'hãm lồn', 'mất dạy', 'vô duyên', 'khốn nạn', 'đm', 'dkm', 'đmm', 'vcl', 'vkl', 'đứt bóng', 
            'bú cu', 'chịch', 'nứng', 'bứng', 'nện', 'nương', 'đớp', 'đồ ngu', 'đồ tồi', 'khốn nạn', 
            'mẹ kiếp', 'đốn mạt', 'vô giáo dục', 'đồ điên', 'con điên', 'thằng điên', 'lũ điên',
            'đầu bò', 'đầu trâu', 'óc lợn', 'ngu xuẩn', 'ngu ngốc', 'ăn cứt', 'ăn phân', 'hút máu',
            'chết đi', 'đồ hèn', 'ti tiện', 'đê tiện'
        ];

        foreach ($bad_words as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/u';
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        // Khớp thêm một số cụm từ ghép tục tĩu phổ biến không có dấu hoặc viết tắt
        $raw_bad_patterns = [
            '/\b(dkm|dm|cl|vcl|vkl|dmm|cc|vl|clgt)\b/i',
            '/địt\s*(mẹ|cha|cụ|mị|cmn)/iu',
            '/đụ\s*(má|mẹ|cha|cụ)/iu',
            '/óc\s*(chó|lợn|bò|trâu)/iu',
            '/ngu\s*(lồn|vcl|vkl|hãm|đần)/iu',
            '/đầu\s*(buồi|cặc|bò|tôm)/iu'
        ];
        
        foreach ($raw_bad_patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }

    public function log_moderation($type, $content_id, $user_id, $text, $analysis) {
        $data = [
            'content_type'     => $type,
            'content_id'       => $content_id,
            'user_id'          => $user_id,
            'raw_text'         => $text,
            'label_0_score'    => $analysis['scores'][0],
            'label_1_score'    => $analysis['scores'][1],
            'label_2_score'    => $analysis['scores'][2],
            'prediction_label' => $analysis['label'],
            'action_taken'     => $analysis['action']
        ];
        return $this->db->insert('ai_moderation_logs', $data);
    }

    public function get_flagged_logs($limit = 100, $offset = 0) {
        $this->db->select('ai_moderation_logs.*, users.username, users.full_name');
        $this->db->from('ai_moderation_logs');
        $this->db->join('users', 'users.id = ai_moderation_logs.user_id', 'left');
        $this->db->order_by('ai_moderation_logs.created_at', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result_array();
    }
}
