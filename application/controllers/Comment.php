<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Comment_model $Comment_model
 */
class Comment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Comment_model');
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    // Thêm bình luận vào bài đăng
    public function add($post_id) {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Đăng nhập để bình luận!');
            redirect('auth');
            return;
        }

        $content = trim($this->input->post('content', TRUE));
        $user_id = $this->session->userdata('user_id');

        // Gọi AI kiểm duyệt nội dung bình luận
        $this->load->model('Ai_moderation_model');
        $ai_analysis = $this->Ai_moderation_model->analyze_text($content);

        $moderation_status = 'approved';
        if ($ai_analysis['action'] === 'block') {
            $moderation_status = 'flagged';
            $this->session->set_flashdata('error', 'Bình luận của bạn chứa ngôn từ không phù hợp và đang được quản trị viên xem xét!');
        }

        $comment_data = [
            'post_id'           => $post_id,
            'user_id'           => $user_id,
            'content'           => $content,
            'moderation_status' => $moderation_status,
            'ai_score'          => $ai_analysis['score']
        ];

        $this->Comment_model->add_comment($comment_data);
        $comment_id = $this->db->insert_id();

        // Ghi log kiểm duyệt
        $this->Ai_moderation_model->log_moderation('comment', $comment_id, $user_id, $content, $ai_analysis);

        redirect('trade/detail/' . $post_id . '#comments');
    }

    // Xóa bình luận
    public function delete($id, $post_id) {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
            return;
        }
        $comment = $this->Comment_model->get_comment_by_id($id);
        $user_id = $this->session->userdata('user_id');
        $role    = $this->session->userdata('role');

        if (!$comment || ($comment['user_id'] != $user_id && $role !== 'admin')) {
            $this->session->set_flashdata('error', 'Bạn không có quyền xóa bình luận này!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        $this->Comment_model->delete_comment($id);
        redirect('trade/detail/' . $post_id . '#comments');
    }
}
