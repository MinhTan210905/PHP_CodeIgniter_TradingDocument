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

        $this->load->model('Ai_moderation_model');
        $ai_analysis = $this->Ai_moderation_model->analyze_text($content);
        if ($ai_analysis['action'] === 'block') {
            $this->Ai_moderation_model->log_moderation('message', 0, $sender_id, $content, $ai_analysis);
            $this->session->set_flashdata('error', 'Tin nhắn bị chặn tự động do chứa ngôn từ không phù hợp!');
            redirect('message/conversation/' . $receiver_id);
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
        $this->Ai_moderation_model->log_moderation('message', $new_id, $sender_id, $content, $ai_analysis);

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

        $this->load->model('Ai_moderation_model');
        $ai_analysis = $this->Ai_moderation_model->analyze_text($content);
        if ($ai_analysis['action'] === 'block') {
            $this->Ai_moderation_model->log_moderation('message', 0, $sender_id, $content, $ai_analysis);
            echo json_encode(['status' => 'error', 'message' => 'Tin nhắn bị chặn tự động do chứa ngôn từ thù địch!']);
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
        $this->Ai_moderation_model->log_moderation('message', $new_id, $sender_id, $content, $ai_analysis);
        
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

    // [AJAX] Bật/tắt ghim cuộc hội thoại
    public function toggle_pin_ajax($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $new_val = $this->Message_model->toggle_pin($user_id, $other_id);

        echo json_encode([
            'status' => 'ok',
            'is_pinned' => $new_val,
            'message' => $new_val ? 'Đã ghim cuộc hội thoại thành công!' : 'Đã bỏ ghim cuộc hội thoại thành công!'
        ]);
    }

    // [AJAX] Bật/tắt tắt tiếng cuộc hội thoại
    public function toggle_mute_ajax($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $new_val = $this->Message_model->toggle_mute($user_id, $other_id);

        echo json_encode([
            'status' => 'ok',
            'is_muted' => $new_val,
            'message' => $new_val ? 'Đã tắt thông báo cuộc hội thoại!' : 'Đã bật thông báo cuộc hội thoại!'
        ]);
    }

    // [AJAX] Xóa mềm cuộc hội thoại (Soft Delete)
    public function delete_chat_ajax($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $this->Message_model->soft_delete_conversation($user_id, $other_id);

        // Đồng thời đánh dấu đã đọc các tin nhắn cũ để giảm badge đếm
        $this->Message_model->mark_as_read($other_id, $user_id);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Đã xóa cuộc hội thoại thành công!'
        ]);
    }

    // [AJAX] Đánh dấu đã đọc cuộc hội thoại trực tiếp
    public function mark_read_ajax($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $this->Message_model->mark_as_read($other_id, $user_id);

        echo json_encode([
            'status' => 'ok',
            'message' => 'Đã đánh dấu đã đọc thành công!'
        ]);
    }
}
