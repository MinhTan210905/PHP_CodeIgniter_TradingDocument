<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Tạo đơn hàng mới (người mua gửi yêu cầu)
    public function create_order($data) {
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }

    // Lấy đơn theo ID (kèm thông tin đầy đủ)
    public function get_order_by_id($id) {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price, posts.quantity as post_quantity,
            seller.full_name as seller_name, seller.username as seller_username, seller.phone as seller_phone,
            buyer.full_name  as buyer_name,  buyer.username  as buyer_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id    = orders.post_id',    'left');
        $this->db->join('users seller','seller.id   = orders.seller_id',  'left');
        $this->db->join('users buyer', 'buyer.id    = orders.buyer_id',   'left');
        $this->db->where('orders.id', $id);
        return $this->db->get()->row_array();
    }

    // Lấy đơn mua của 1 user (tab Mua)
    public function get_orders_as_buyer($buyer_id) {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price,
            seller.full_name as seller_name, seller.username as seller_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id  = orders.post_id',   'left');
        $this->db->join('users seller','seller.id = orders.seller_id', 'left');
        $this->db->where('orders.buyer_id', $buyer_id);
        $this->db->order_by('orders.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Lấy đơn bán của 1 user (tab Bán)
    public function get_orders_as_seller($seller_id) {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price,
            buyer.full_name  as buyer_name,  buyer.username as buyer_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id = orders.post_id',   'left');
        $this->db->join('users buyer', 'buyer.id = orders.buyer_id',  'left');
        $this->db->where('orders.seller_id', $seller_id);
        $this->db->order_by('orders.created_at', 'DESC');
        return $this->db->get()->result_array();
    }

    // Kiểm tra người mua đã có đơn pending/confirmed với bài này chưa
    public function has_active_order($post_id, $buyer_id) {
        $this->db->where('post_id', $post_id);
        $this->db->where('buyer_id', $buyer_id);
        $this->db->where_in('status', ['pending', 'confirmed']);
        return $this->db->get('orders')->num_rows() > 0;
    }

    // Kiểm tra đơn đã completed (để mở khóa đánh giá)
    public function has_completed_order($post_id, $buyer_id) {
        $this->db->where('post_id',  $post_id);
        $this->db->where('buyer_id', $buyer_id);
        $this->db->where('status',   'completed');
        return $this->db->get('orders')->row_array();
    }

    // Cập nhật trạng thái đơn
    public function update_status($id, $status, $extra = []) {
        $data = array_merge(['status' => $status], $extra);
        $this->db->where('id', $id);
        return $this->db->update('orders', $data);
    }

    // Đếm đơn pending cho seller (thông báo)
    public function count_pending_for_seller($seller_id) {
        return $this->db->where('seller_id', $seller_id)
                        ->where('status', 'pending')
                        ->count_all_results('orders');
    }

    // Lấy đơn confirmed của người mua (để hiện nút "Đã nhận")
    public function get_confirmed_order($post_id, $buyer_id) {
        $this->db->where('post_id',  $post_id);
        $this->db->where('buyer_id', $buyer_id);
        $this->db->where('status',   'confirmed');
        return $this->db->get('orders')->row_array();
    }

    // Lấy tổng số đơn hoàn thành của seller (cho thống kê)
    public function count_completed_as_seller($seller_id) {
        return $this->db->where('seller_id', $seller_id)
                        ->where('status', 'completed')
                        ->count_all_results('orders');
    }
    // [ADMIN] Lấy tất cả đơn hoàn thành (để kiểm duyệt thanh toán)
    public function get_completed_orders() {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price,
            seller.full_name as seller_name, seller.username as seller_username,
            buyer.full_name  as buyer_name,  buyer.username  as buyer_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id    = orders.post_id',   'left');
        $this->db->join('users seller','seller.id   = orders.seller_id', 'left');
        $this->db->join('users buyer', 'buyer.id    = orders.buyer_id',  'left');
        $this->db->where('orders.status', 'completed');
        $this->db->order_by('orders.updated_at', 'DESC');
        return $this->db->get()->result_array();
    }
    // Lấy các đơn hàng đang giao (delivering) trên 24h
    public function get_delivering_over_24h() {
        $this->db->select('orders.*, posts.title as post_title');
        $this->db->from('orders');
        $this->db->join('posts', 'posts.id = orders.post_id', 'left');
        $this->db->where('orders.status', 'delivering');
        $this->db->where('orders.updated_at <', date('Y-m-d H:i:s', strtotime('-24 hours')));
        return $this->db->get()->result_array();
    }

    // Lấy các đơn hàng đã hoàn thành (completed) trên 24h mà chưa có đánh giá
    public function get_completed_unrated_over_24h() {
        $this->db->select('orders.*');
        $this->db->from('orders');
        $this->db->where('orders.status', 'completed');
        $this->db->where('orders.updated_at <', date('Y-m-d H:i:s', strtotime('-24 hours')));
        // Lọc những đơn chưa có đánh giá
        $this->db->where('NOT EXISTS (SELECT 1 FROM ratings WHERE ratings.order_id = orders.id)', '', FALSE);
        return $this->db->get()->result_array();
    }

    // [ADMIN] Lấy tất cả đơn đang tranh chấp (để Admin phân xử)
    public function get_disputed_orders() {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price,
            seller.full_name as seller_name, seller.username as seller_username,
            buyer.full_name  as buyer_name,  buyer.username  as buyer_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id    = orders.post_id',   'left');
        $this->db->join('users seller','seller.id   = orders.seller_id', 'left');
        $this->db->join('users buyer', 'buyer.id    = orders.buyer_id',  'left');
        $this->db->where('orders.status', 'disputed');
        $this->db->order_by('orders.updated_at', 'ASC'); // Đơn cũ nhất hiển thị trước
        return $this->db->get()->result_array();
    }

    // [ADMIN] Lấy lịch sử tất cả đơn hàng đã được phân xử tranh chấp xong
    public function get_resolved_disputes() {
        $this->db->select('orders.*, 
            posts.title as post_title, posts.image_url, posts.price,
            seller.full_name as seller_name, seller.username as seller_username,
            buyer.full_name  as buyer_name,  buyer.username  as buyer_username');
        $this->db->from('orders');
        $this->db->join('posts',       'posts.id    = orders.post_id',   'left');
        $this->db->join('users seller','seller.id   = orders.seller_id', 'left');
        $this->db->join('users buyer', 'buyer.id    = orders.buyer_id',  'left');
        $this->db->group_start();
        $this->db->where('orders.status', 'completed');
        $this->db->or_where('orders.status', 'cancelled');
        $this->db->group_end();
        $this->db->like('orders.reject_reason', '[ADMIN] Kết luận:');
        $this->db->order_by('orders.updated_at', 'DESC');
        return $this->db->get()->result_array();
    }
}
