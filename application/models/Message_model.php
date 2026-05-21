<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Message_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Lấy danh sách hội thoại (inbox) của 1 user — nhóm theo người còn lại
    public function get_conversations($user_id) {
        $sql = "
            SELECT 
                m.*,
                u.id as other_user_id, u.username, u.full_name, u.avatar,
                p.title as post_title,
                (SELECT COUNT(*) FROM messages 
                 WHERE receiver_id = ? AND sender_id = u.id AND is_read = 0) as unread_count
            FROM messages m
            JOIN users u ON u.id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
            LEFT JOIN posts p ON p.id = m.post_id
            WHERE m.id IN (
                SELECT MAX(id) FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY IF(sender_id < receiver_id, CONCAT(sender_id,'_',receiver_id), CONCAT(receiver_id,'_',sender_id))
            )
            AND (m.sender_id = ? OR m.receiver_id = ?)
            ORDER BY m.created_at DESC
        ";
        return $this->db->query($sql, [
            $user_id, $user_id, $user_id, $user_id, $user_id, $user_id
        ])->result_array();
    }

    // Lấy toàn bộ tin nhắn của 1 hội thoại giữa 2 người
    public function get_conversation($user_id, $other_id) {
        $this->db->select('messages.*, 
            s.username as sender_username, s.full_name as sender_name,
            r.username as receiver_username, r.full_name as receiver_name,
            p.title as post_title, p.id as post_id_ref');
        $this->db->from('messages');
        $this->db->join('users s', 's.id = messages.sender_id', 'left');
        $this->db->join('users r', 'r.id = messages.receiver_id', 'left');
        $this->db->join('posts p', 'p.id = messages.post_id', 'left');
        $this->db->group_start();
            $this->db->where('messages.sender_id', $user_id);
            $this->db->where('messages.receiver_id', $other_id);
        $this->db->group_end();
        $this->db->or_group_start();
            $this->db->where('messages.sender_id', $other_id);
            $this->db->where('messages.receiver_id', $user_id);
        $this->db->group_end();
        $this->db->order_by('messages.created_at', 'ASC');
        return $this->db->get()->result_array();
    }

    // Gửi tin nhắn
    public function send_message($data) {
        return $this->db->insert('messages', $data);
    }

    // Đánh dấu đã đọc
    public function mark_as_read($sender_id, $receiver_id) {
        $this->db->where('sender_id', $sender_id);
        $this->db->where('receiver_id', $receiver_id);
        return $this->db->update('messages', ['is_read' => 1]);
    }

    // Đếm tin nhắn chưa đọc
    public function count_unread($user_id) {
        return $this->db->where('receiver_id', $user_id)
                        ->where('is_read', 0)
                        ->count_all_results('messages');
    }

    // [AJAX Polling] Lấy tin nhắn mới hơn after_id trong hội thoại 2 người
    public function get_new_messages($user_id, $other_id, $after_id = 0) {
        $this->db->select('messages.*,
            s.username as sender_username, s.full_name as sender_name,
            p.title as post_title, p.id as post_id_ref');
        $this->db->from('messages');
        $this->db->join('users s', 's.id = messages.sender_id', 'left');
        $this->db->join('posts p', 'p.id = messages.post_id', 'left');
        $this->db->group_start();
            $this->db->where('messages.sender_id', $user_id);
            $this->db->where('messages.receiver_id', $other_id);
        $this->db->group_end();
        $this->db->or_group_start();
            $this->db->where('messages.sender_id', $other_id);
            $this->db->where('messages.receiver_id', $user_id);
        $this->db->group_end();
        if ($after_id > 0) {
            $this->db->where('messages.id >', $after_id);
        }
        $this->db->order_by('messages.created_at', 'ASC');
        return $this->db->get()->result_array();
    }
}
