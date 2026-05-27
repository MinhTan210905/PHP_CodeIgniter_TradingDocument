<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbot extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load model
        $this->load->model('Chatbot_model');
    }

    public function ask() {
        // Chỉ nhận AJAX request
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $message = $this->input->post('message', TRUE);
        
        if (empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Tin nhắn không được rỗng.']);
            return;
        }

        // Gọi model xử lý câu hỏi với Gemini API
        $response = $this->Chatbot_model->ask($message);

        if (isset($response['error'])) {
            echo json_encode(['status' => 'error', 'message' => $response['error']]);
        } else {
            echo json_encode(['status' => 'success', 'reply' => $response['reply']]);
        }
    }
}
