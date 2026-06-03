<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Message_model $Message_model
 * @property Auth_model $Auth_model
 */
class Message extends MY_Controller {

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

    // Trigger sự kiện Pusher WebSocket
    private function trigger_pusher_message($msg_id, $event = 'new-message', $notify_sender = false) {
        $msg = $this->Message_model->get_message_by_id($msg_id);
        if (!$msg) return;

        $app_id  = getenv('PUSHER_APP_ID') ?: (isset($_ENV['PUSHER_APP_ID']) ? $_ENV['PUSHER_APP_ID'] : '');
        $key     = getenv('PUSHER_KEY') ?: (isset($_ENV['PUSHER_KEY']) ? $_ENV['PUSHER_KEY'] : '');
        $secret  = getenv('PUSHER_SECRET') ?: (isset($_ENV['PUSHER_SECRET']) ? $_ENV['PUSHER_SECRET'] : '');
        $cluster = getenv('PUSHER_CLUSTER') ?: (isset($_ENV['PUSHER_CLUSTER']) ? $_ENV['PUSHER_CLUSTER'] : 'ap1');

        if ($app_id && $key && $secret) {
            if (file_exists(FCPATH . 'vendor/autoload.php')) {
                try {
                    require_once FCPATH . 'vendor/autoload.php';
                    $options = array(
                        'cluster' => $cluster,
                        'useTLS' => true,
                        'timeout' => 2 // Hạn chế tối đa việc treo request trên hosting miễn phí (như InfinityFree)
                    );
                    $pusher = new Pusher\Pusher($key, $secret, $app_id, $options);
                    
                    // Gửi sự kiện cho người nhận
                    $pusher->trigger('chat-channel-' . $msg['receiver_id'], $event, ['message' => $msg]);
                    
                    // Nếu cần thông báo lại cho cả người gửi (thường dùng khi cập nhật trạng thái)
                    if ($notify_sender) {
                        $pusher->trigger('chat-channel-' . $msg['sender_id'], $event, ['message' => $msg]);
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Pusher error: ' . $e->getMessage());
                }
            } else {
                log_message('error', 'Pusher error: vendor/autoload.php not found');
            }
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

        $new_id = $this->db->insert_id();

        // Kích hoạt thông báo real-time qua Pusher
        $this->trigger_pusher_message($new_id);

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
        
        // Kích hoạt thông báo real-time qua Pusher
        $this->trigger_pusher_message($new_id);
        
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

    // [AJAX] Lấy danh sách cuộc hội thoại của user hiện tại
    public function get_conversations_ajax() {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        $conversations = $this->Message_model->get_conversations($user_id);
        
        foreach($conversations as &$conv) {
            $conv['avatar_url'] = (!empty($conv['avatar']) && file_exists(FCPATH . $conv['avatar'])) ? base_url($conv['avatar']) : '';
            $conv['initial'] = strtoupper(substr($conv['full_name'] ?: $conv['username'], 0, 1));
            $conv['time_str'] = date('H:i d/m', strtotime($conv['created_at']));
            $conv['content_escaped'] = htmlspecialchars($conv['content']);
            $conv['full_name_escaped'] = htmlspecialchars($conv['full_name'] ?: $conv['username']);
            $conv['post_title_escaped'] = htmlspecialchars($conv['post_title'] ?: '');
        }
        
        echo json_encode(['status' => 'ok', 'conversations' => $conversations]);
    }

    // [AJAX] Lấy danh sách tin nhắn của 1 cuộc hội thoại
    public function get_messages_ajax($other_id) {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id = $this->session->userdata('user_id');
        
        // Đánh dấu đã đọc luôn
        $this->Message_model->mark_as_read($other_id, $user_id);
        
        $messages = $this->Message_model->get_conversation($user_id, $other_id);
        $other_user = $this->Auth_model->get_user_by_id($other_id);
        
        echo json_encode([
            'status' => 'ok',
            'messages' => $messages,
            'other_user' => [
                'id' => $other_user['id'],
                'full_name' => htmlspecialchars($other_user['full_name'] ?: $other_user['username']),
                'avatar_url' => (!empty($other_user['avatar']) && file_exists(FCPATH . $other_user['avatar'])) ? base_url($other_user['avatar']) : ''
            ]
        ]);
    }

    // [AJAX] Gửi yêu cầu hẹn gặp giao nhận sách
    public function send_meetup_ajax() {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $sender_id   = $this->session->userdata('user_id');
        $receiver_id = $this->input->post('receiver_id');
        $post_id     = $this->input->post('post_id') ?: NULL;
        $location    = trim($this->input->post('location', TRUE));
        $date        = trim($this->input->post('date', TRUE));
        $time        = trim($this->input->post('time', TRUE));

        if (empty($receiver_id) || empty($location) || empty($date) || empty($time)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin lịch hẹn!']);
            return;
        }

        // Tạo chuỗi DATETIME (YYYY-MM-DD HH:MM:00)
        $meetup_time = $date . ' ' . $time . ':00';

        $this->Message_model->send_message([
            'sender_id'       => $sender_id,
            'receiver_id'     => $receiver_id,
            'post_id'         => $post_id,
            'content'         => 'Đã gửi một yêu cầu hẹn gặp.',
            'message_type'    => 'meetup',
            'meetup_location' => $location,
            'meetup_time'     => $meetup_time,
            'meetup_status'   => 'pending',
            'is_read'         => 0
        ]);

        $new_id = $this->db->insert_id();
        
        // Kích hoạt thông báo real-time qua Pusher
        $this->trigger_pusher_message($new_id, 'new-message', false);
        
        echo json_encode(['status' => 'ok', 'id' => $new_id]);
    }

    // [AJAX] Phản hồi lịch hẹn (Chấp nhận / Từ chối)
    public function respond_meetup_ajax() {
        $this->require_login();
        if (!$this->input->is_ajax_request()) { show_404(); }

        $user_id   = $this->session->userdata('user_id');
        $msg_id    = (int)$this->input->post('message_id');
        $action    = $this->input->post('action'); // 'accepted' or 'rejected'

        if (!in_array($action, ['accepted', 'rejected'])) {
            echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ!']);
            return;
        }

        $msg = $this->Message_model->get_message_by_id($msg_id);
        
        if (!$msg) {
            echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy lịch hẹn!']);
            return;
        }

        // Chỉ người nhận lời hẹn mới có quyền phản hồi
        if ($msg['receiver_id'] != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện hành động này!']);
            return;
        }

        if ($msg['meetup_status'] != 'pending') {
            echo json_encode(['status' => 'error', 'message' => 'Lịch hẹn này đã được phản hồi trước đó!']);
            return;
        }

        // Cập nhật trạng thái
        $this->Message_model->update_meetup_status($msg_id, $action);

        // Kích hoạt Pusher báo trạng thái mới (cho CẢ NGƯỜI GỬI và NGƯỜI NHẬN để update UI)
        $this->trigger_pusher_message($msg_id, 'update-message', true);

        // Gửi thêm một tin nhắn tự động từ hệ thống để thông báo vào chat
        $full_name = $this->session->userdata('full_name');
        $status_vn = ($action == 'accepted') ? 'CHẤP NHẬN' : 'TỪ CHỐI';
        $time_str  = date('H:i d/m/Y', strtotime($msg['meetup_time']));
        $sys_content = "Hệ thống: $full_name đã $status_vn lịch hẹn tại " . $msg['meetup_location'] . " lúc $time_str.";

        $this->Message_model->send_message([
            'sender_id'       => $user_id, // Gửi lại cho người kia
            'receiver_id'     => $msg['sender_id'],
            'post_id'         => $msg['post_id'],
            'content'         => $sys_content,
            'message_type'    => 'text',
            'is_read'         => 0
        ]);
        $sys_new_id = $this->db->insert_id();
        $this->trigger_pusher_message($sys_new_id, 'new-message', false);

        echo json_encode(['status' => 'ok']);
    }
}
