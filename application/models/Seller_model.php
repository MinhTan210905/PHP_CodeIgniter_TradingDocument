<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Seller_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Lấy thông tin public của seller
    public function get_seller_info($seller_id) {
        return $this->db->get_where('users', ['id' => $seller_id])->row_array();
    }

    // Lấy sách đang bán của seller (tab Đang bán)
    public function get_active_posts($seller_id) {
        $this->db->select('posts.*, categories.category_name, categories.icon as cat_icon,
            COALESCE(AVG(ratings.stars), 0) as avg_rating,
            COUNT(DISTINCT ratings.id)      as total_ratings,
            COUNT(DISTINCT comments.id)     as comment_count');
        $this->db->from('posts');
        $this->db->join('categories', 'categories.id  = posts.category_id', 'left');
        $this->db->join('ratings',    'ratings.post_id = posts.id',         'left');
        $this->db->join('comments',   'comments.post_id= posts.id',         'left');
        $this->db->where('posts.user_id', $seller_id);
        $this->db->where('posts.status',  'available');
        $this->db->group_by('posts.id');
        $this->db->order_by('posts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy sách đã pass của seller (tab Đã Pass)
    public function get_sold_posts($seller_id) {
        $this->db->select('posts.*, categories.category_name,
            COUNT(DISTINCT orders.id) as total_orders');
        $this->db->from('posts');
        $this->db->join('categories', 'categories.id  = posts.category_id', 'left');
        $this->db->join('orders',     'orders.post_id  = posts.id AND orders.status = "completed"', 'left');
        $this->db->where('posts.user_id', $seller_id);
        $this->db->where('posts.status',  'sold');
        $this->db->group_by('posts.id');
        $this->db->order_by('posts.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy đánh giá của seller kèm tên sách (tab Đánh giá)
    public function get_ratings($seller_id) {
        $this->db->select('ratings.*, 
            buyer.full_name  as buyer_name,  buyer.username  as buyer_username, buyer.avatar as buyer_avatar,
            posts.title      as post_title');
        $this->db->from('ratings');
        $this->db->join('users buyer', 'buyer.id  = ratings.reviewer_id', 'left');
        $this->db->join('posts',       'posts.id  = ratings.post_id',     'left');
        $this->db->where('ratings.seller_id', $seller_id);
        $this->db->order_by('ratings.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Thống kê tóm tắt cho seller header
    public function get_stats($seller_id) {
        $active  = $this->db->where('user_id', $seller_id)->where('status', 'available')->count_all_results('posts');
        $sold    = $this->db->where('user_id', $seller_id)->where('status', 'sold')->count_all_results('posts');
        $ratings = $this->db->where('seller_id', $seller_id)->count_all_results('ratings');

        $this->db->select_avg('stars', 'avg');
        $this->db->where('seller_id', $seller_id);
        $avg_row = $this->db->get('ratings')->row_array();

        return [
            'active_posts'  => $active,
            'sold_posts'    => $sold,
            'total_ratings' => $ratings,
            'avg_rating'    => $avg_row['avg'] ? round($avg_row['avg'], 1) : 0,
        ];
    }
}
