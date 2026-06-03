<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Rating_model $Rating_model
 * @property Trade_model $Trade_model
 */
class Rating extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Rating_model', 'Trade_model']);
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    // Gửi đánh giá sao cho người bán
    public function add($post_id) {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Đăng nhập để đánh giá!');
            redirect('auth');
            return;
        }

        $reviewer_id = $this->session->userdata('user_id');
        $post        = $this->Trade_model->get_post_by_id($post_id);

        if (!$post) { show_404(); }

        // Không tự đánh giá chính mình
        if ($post['user_id'] == $reviewer_id) {
            $this->session->set_flashdata('error', 'Bạn không thể tự đánh giá bản thân!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        // Đã đánh giá rồi thì thôi
        if ($this->Rating_model->has_rated($reviewer_id, $post_id)) {
            $this->session->set_flashdata('error', 'Bạn đã đánh giá bài đăng này rồi!');
            redirect('trade/detail/' . $post_id);
            return;
        }

        $stars = (int) $this->input->post('stars');
        if ($stars < 1 || $stars > 5) {
            redirect('trade/detail/' . $post_id);
            return;
        }

        $this->Rating_model->add_rating([
            'reviewer_id' => $reviewer_id,
            'seller_id'   => $post['user_id'],
            'post_id'     => $post_id,
            'stars'       => $stars,
            'comment'     => $this->input->post('comment', TRUE)
        ]);

        $this->session->set_flashdata('success', 'Đánh giá của bạn đã được ghi nhận!');
        redirect('trade/detail/' . $post_id);
    }
}
