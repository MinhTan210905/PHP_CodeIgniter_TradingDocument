<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Message_model $Message_model
 * @property Auth_model $Auth_model
 */
class Message extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Message_model', 'Auth_model']);
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    // Hộp thư đến
    public function inbox() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $data['conversations'] = $this->Message_model->get_conversations($user_id);
        $data['unread_count']  = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('messages/inbox', $data);
        $this->load->view('partials/footer');
    }

    // Xem hội thoại với 1 người
    public function conversation($other_id) {
        $this->require_login();
        $user_id    = $this->session->userdata('user_id');
        $other_user = $this->Auth_model->get_user_by_id($other_id);

        if (!$other_user) { show_404(); }

        // Đánh dấu đã đọc
        $this->Message_model->mark_as_read($other_id, $user_id);

        $data['messages']      = $this->Message_model->get_conversation($user_id, $other_id);
        $data['other_user']    = $other_user;
        $data['unread_count']  = $this->Message_model->count_unread($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('messages/conversation', $data);
        $this->load->view('partials/footer');
    }

    // Gửi tin nhắn
    public function send() {
        $this->require_login();
        $sender_id   = $this->session->userdata('user_id');
        $receiver_id = $this->input->post('receiver_id');
        $content     = trim($this->input->post('content', TRUE));
        $post_id     = $this->input->post('post_id') ?: NULL;

        if (empty($content) || !$receiver_id) {
            redirect('message/inbox');
            return;
        }

        $this->Message_model->send_message([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'post_id'     => $post_id,
            'content'     => $content,
            'is_read'     => 0
        ]);

        redirect('message/conversation/' . $receiver_id);
    }

    // [AJAX] Gửi tin nhắn — trả về JSON
    public function send_ajax() {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $sender_id   = $this->session->userdata('user_id');
        $receiver_id = $this->input->post('receiver_id');
        $content     = trim($this->input->post('content', TRUE));
        $post_id     = $this->input->post('post_id') ?: NULL;

        if (empty($content) || !$receiver_id) {
            echo json_encode(['status' => 'error', 'message' => 'Nội dung trống!']);
            return;
        }

        $this->Message_model->send_message([
            'sender_id'   => $sender_id,
            'receiver_id' => $receiver_id,
            'post_id'     => $post_id,
            'content'     => $content,
            'is_read'     => 0
        ]);

        $new_id = $this->db->insert_id();
        echo json_encode(['status' => 'ok', 'id' => $new_id]);
    }

    // [AJAX] Lấy các tin nhắn MỚI hơn after_id — dùng để poll realtime
    public function poll_messages($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id  = $this->session->userdata('user_id');
        $after_id = (int)$this->input->get('after_id');

        // Đánh dấu đã đọc luôn
        $this->Message_model->mark_as_read($other_id, $user_id);

        $msgs = $this->Message_model->get_new_messages($user_id, $other_id, $after_id);

        echo json_encode(['status' => 'ok', 'messages' => $msgs]);
    }

    // [AJAX] Lấy tổng số tin nhắn chưa đọc của user hiện tại
    public function total_unread() {
        if (!$this->session->userdata('logged_in')) {
            echo json_encode(['status' => 'error', 'count' => 0]);
            return;
        }
        $user_id = $this->session->userdata('user_id');
        $count = $this->Message_model->count_unread($user_id);
        echo json_encode(['status' => 'ok', 'count' => $count]);
    }
}
