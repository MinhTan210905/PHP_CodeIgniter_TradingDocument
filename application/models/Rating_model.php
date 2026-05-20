<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rating_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Lấy điểm đánh giá trung bình của người bán
    public function get_avg_rating($seller_id) {
        $this->db->select_avg('stars', 'avg_stars');
        $this->db->select('COUNT(id) as total_ratings');
        $this->db->where('seller_id', $seller_id);
        $result = $this->db->get('ratings')->row_array();
        return [
            'avg'   => $result['avg_stars'] ? round($result['avg_stars'], 1) : 0,
            'total' => $result['total_ratings']
        ];
    }

    // Kiểm tra người dùng đã đánh giá bài này chưa
    public function has_rated($reviewer_id, $post_id) {
        return $this->db->get_where('ratings', [
            'reviewer_id' => $reviewer_id,
            'post_id'     => $post_id
        ])->num_rows() > 0;
    }

    // Kiểm tra đã đánh giá theo order_id (Shopee-style)
    public function has_rated_order($order_id, $reviewer_id) {
        return $this->db->get_where('ratings', [
            'order_id'    => $order_id,
            'reviewer_id' => $reviewer_id
        ])->num_rows() > 0;
    }

    // Thêm đánh giá
    public function add_rating($data) {
        return $this->db->insert('ratings', $data);
    }

    // Lấy danh sách đánh giá của người bán (kèm thông tin người đánh giá)
    public function get_ratings_for_seller($seller_id) {
        $this->db->select('ratings.*, users.username, users.full_name, users.avatar, posts.title as post_title');
        $this->db->from('ratings');
        $this->db->join('users', 'users.id = ratings.reviewer_id', 'left');
        $this->db->join('posts', 'posts.id = ratings.post_id', 'left');
        $this->db->where('ratings.seller_id', $seller_id);
        $this->db->order_by('ratings.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy danh sách đánh giá theo bài đăng (Shopee-style: hiện trên trang sản phẩm)
    public function get_ratings_for_post($post_id) {
        $this->db->select('ratings.*, users.username, users.full_name, users.avatar');
        $this->db->from('ratings');
        $this->db->join('users', 'users.id = ratings.reviewer_id', 'left');
        $this->db->where('ratings.post_id', $post_id);
        $this->db->order_by('ratings.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy điểm trung bình đánh giá theo bài đăng
    public function get_avg_rating_for_post($post_id) {
        $this->db->select_avg('stars', 'avg_stars');
        $this->db->select('COUNT(id) as total_ratings');
        $this->db->where('post_id', $post_id);
        $result = $this->db->get('ratings')->row_array();
        return [
            'avg'   => $result['avg_stars'] ? round($result['avg_stars'], 1) : 0,
            'total' => $result['total_ratings']
        ];
    }
}
