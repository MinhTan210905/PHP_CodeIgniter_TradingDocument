<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_moderation_model extends CI_Model {

    private $api_url = "https://api-inference.huggingface.co/models/Minhtan210905/phobert-toxic-genz-v2";
    private $api_key = "";

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->api_key = getenv('HF_API_KEY') ?: (isset($_ENV['HF_API_KEY']) ? $_ENV['HF_API_KEY'] : '');
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

        if (empty($this->api_key)) {
            log_message('error', 'AI Moderation: HF_API_KEY is not defined in .env.');
            return $fallback_res;
        }

        $payload = json_encode(['inputs' => $text]);
        $ch = curl_init($this->api_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->api_url_clean_token($this->api_key),
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

        if (isset($result['error']) && strpos($result['error'], 'loading') !== false) {
            log_message('error', 'AI Moderation: Model is currently loading on HF.');
            $fallback_res['error'] = 'Model loading';
            return $fallback_res;
        }

        if (empty($result) || isset($result['error'])) {
            $err_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
            log_message('error', 'AI Moderation API Error: ' . $err_msg . ' | HTTP Code: ' . $http_code);
            $fallback_res['error'] = 'API Error: ' . $err_msg;
            return $fallback_res;
        }

        $predictions = isset($result[0]) ? $result[0] : [];
        if (empty($predictions)) {
            return $fallback_res;
        }

        $scores = [0 => 0.0, 1 => 0.0, 2 => 0.0];
        foreach ($predictions as $pred) {
            $label_str = $pred['label'];
            $score_val = (float)$pred['score'];

            $label_num = 0;
            if (preg_match('/\d+/', $label_str, $matches)) {
                $label_num = (int)$matches[0];
            }

            if (isset($scores[$label_num])) {
                $scores[$label_num] = $score_val;
            }
        }

        $pred_label = 0;
        $max_score = 0.0;
        foreach ($scores as $lbl => $sc) {
            if ($sc > $max_score) {
                $max_score = $sc;
                $pred_label = $lbl;
            }
        }

        $action = 'allow';
        // Nhãn 3 (label 2) bị cấm, nhãn 1 2 (label 0, 1) bình thường
        if ($pred_label == 2 && $scores[2] > 0.50) {
            $action = 'block';
        }

        return [
            'action' => $action,
            'label'  => $pred_label,
            'score'  => $max_score,
            'scores' => $scores,
            'error'  => null
        ];
    }

    private function api_url_clean_token($token) {
        return trim(str_replace(['"', "'"], '', $token));
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
