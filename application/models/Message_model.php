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
                COALESCE(meta.is_pinned, 0) as is_pinned,
                COALESCE(meta.is_muted, 0) as is_muted,
                meta.deleted_at,
                (SELECT COUNT(*) FROM messages msg_u
                 LEFT JOIN user_conversation_meta meta_u ON meta_u.user_id = ? AND meta_u.other_user_id = u.id
                 WHERE msg_u.receiver_id = ? AND msg_u.sender_id = u.id AND msg_u.is_read = 0
                   AND (meta_u.deleted_at IS NULL OR msg_u.created_at > meta_u.deleted_at)) as unread_count
            FROM messages m
            JOIN users u ON u.id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
            LEFT JOIN posts p ON p.id = m.post_id
            LEFT JOIN user_conversation_meta meta ON meta.user_id = ? AND meta.other_user_id = u.id
            WHERE m.id IN (
                SELECT MAX(id) FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY IF(sender_id < receiver_id, CONCAT(sender_id,'_',receiver_id), CONCAT(receiver_id,'_',sender_id))
            )
            AND (m.sender_id = ? OR m.receiver_id = ?)
            AND (meta.deleted_at IS NULL OR m.created_at > meta.deleted_at)
            ORDER BY COALESCE(meta.is_pinned, 0) DESC, m.created_at DESC
        ";
        return $this->db->query($sql, [
            $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id
        ])->result_array();
    }

    // Lấy toàn bộ tin nhắn của 1 hội thoại giữa 2 người (lọc tin nhắn đã xóa mềm)
    public function get_conversation($user_id, $other_id) {
        $this->db->select('messages.*, 
            s.username as sender_username, s.full_name as sender_name,
            r.username as receiver_username, r.full_name as receiver_name,
            p.title as post_title, p.id as post_id_ref');
        $this->db->from('messages');
        $this->db->join('users s', 's.id = messages.sender_id', 'left');
        $this->db->join('users r', 'r.id = messages.receiver_id', 'left');
        $this->db->join('posts p', 'p.id = messages.post_id', 'left');
        
        // Kết hợp cài đặt hội thoại của người dùng hiện tại
        $this->db->join('user_conversation_meta meta', 'meta.user_id = ' . (int)$user_id . ' AND meta.other_user_id = ' . (int)$other_id, 'left');
        
        $this->db->group_start();
            $this->db->group_start();
                $this->db->where('messages.sender_id', $user_id);
                $this->db->where('messages.receiver_id', $other_id);
            $this->db->group_end();
            $this->db->or_group_start();
                $this->db->where('messages.sender_id', $other_id);
                $this->db->where('messages.receiver_id', $user_id);
            $this->db->group_end();
        $this->db->group_end();
        
        // Loại bỏ tin nhắn cũ trước thời điểm xóa mềm (nếu có)
        $this->db->group_start();
            $this->db->where('meta.deleted_at IS NULL');
            $this->db->or_where('messages.created_at > meta.deleted_at');
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

    // Đếm tin nhắn chưa đọc thông minh (loại trừ các hội thoại bị tắt thông báo hoặc đã xóa mềm)
    public function count_unread($user_id) {
        $sql = "
            SELECT COUNT(*) as count 
            FROM messages m
            LEFT JOIN user_conversation_meta meta 
              ON meta.user_id = ? AND meta.other_user_id = m.sender_id
            WHERE m.receiver_id = ? 
              AND m.is_read = 0
              AND (meta.is_muted IS NULL OR meta.is_muted = 0)
              AND (meta.deleted_at IS NULL OR m.created_at > meta.deleted_at)
        ";
        $row = $this->db->query($sql, [$user_id, $user_id])->row_array();
        return isset($row['count']) ? (int)$row['count'] : 0;
    }

    // [AJAX Polling] Lấy tin nhắn mới hơn after_id trong hội thoại 2 người (lọc tin nhắn đã xóa mềm)
    public function get_new_messages($user_id, $other_id, $after_id = 0) {
        $this->db->select('messages.*,
            s.username as sender_username, s.full_name as sender_name,
            p.title as post_title, p.id as post_id_ref');
        $this->db->from('messages');
        $this->db->join('users s', 's.id = messages.sender_id', 'left');
        $this->db->join('posts p', 'p.id = messages.post_id', 'left');
        
        // Kết hợp cài đặt hội thoại của người dùng hiện tại
        $this->db->join('user_conversation_meta meta', 'meta.user_id = ' . (int)$user_id . ' AND meta.other_user_id = ' . (int)$other_id, 'left');
        
        $this->db->group_start();
            $this->db->group_start();
                $this->db->where('messages.sender_id', $user_id);
                $this->db->where('messages.receiver_id', $other_id);
            $this->db->group_end();
            $this->db->or_group_start();
                $this->db->where('messages.sender_id', $other_id);
                $this->db->where('messages.receiver_id', $user_id);
            $this->db->group_end();
        $this->db->group_end();
        
        // Loại bỏ tin nhắn cũ trước thời điểm xóa mềm (nếu có)
        $this->db->group_start();
            $this->db->where('meta.deleted_at IS NULL');
            $this->db->or_where('messages.created_at > meta.deleted_at');
        $this->db->group_end();
        
        if ($after_id > 0) {
            $this->db->where('messages.id >', $after_id);
        }
        $this->db->order_by('messages.created_at', 'ASC');
        return $this->db->get()->result_array();
    }

    // Bật/tắt ghim cuộc hội thoại
    public function toggle_pin($user_id, $other_id) {
        $meta = $this->db->get_where('user_conversation_meta', [
            'user_id' => $user_id,
            'other_user_id' => $other_id
        ])->row_array();

        if ($meta) {
            $new_val = $meta['is_pinned'] ? 0 : 1;
            $this->db->where('id', $meta['id']);
            $this->db->update('user_conversation_meta', ['is_pinned' => $new_val]);
            return $new_val;
        } else {
            $this->db->insert('user_conversation_meta', [
                'user_id' => $user_id,
                'other_user_id' => $other_id,
                'is_pinned' => 1
            ]);
            return 1;
        }
    }

    // Bật/tắt tắt tiếng cuộc hội thoại
    public function toggle_mute($user_id, $other_id) {
        $meta = $this->db->get_where('user_conversation_meta', [
            'user_id' => $user_id,
            'other_user_id' => $other_id
        ])->row_array();

        if ($meta) {
            $new_val = $meta['is_muted'] ? 0 : 1;
            $this->db->where('id', $meta['id']);
            $this->db->update('user_conversation_meta', ['is_muted' => $new_val]);
            return $new_val;
        } else {
            $this->db->insert('user_conversation_meta', [
                'user_id' => $user_id,
                'other_user_id' => $other_id,
                'is_muted' => 1
            ]);
            return 1;
        }
    }

    // Xóa mềm cuộc hội thoại
    public function soft_delete_conversation($user_id, $other_id) {
        $meta = $this->db->get_where('user_conversation_meta', [
            'user_id' => $user_id,
            'other_user_id' => $other_id
        ])->row_array();

        $now = date('Y-m-d H:i:s');
        if ($meta) {
            $this->db->where('id', $meta['id']);
            $this->db->update('user_conversation_meta', ['deleted_at' => $now]);
        } else {
            $this->db->insert('user_conversation_meta', [
                'user_id' => $user_id,
                'other_user_id' => $other_id,
                'deleted_at' => $now
            ]);
        }
        return true;
    }
}

