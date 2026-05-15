<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property Seller_model $Seller_model
 * @property Message_model $Message_model
 * @property Order_model $Order_model
 */
class Seller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Seller_model', 'Message_model', 'Order_model']);
        $this->load->library('session');
        $this->load->helper(['url']);
    }

    // Sàn công khai của người bán /seller/{id}
    public function view($seller_id) {
        $seller = $this->Seller_model->get_seller_info($seller_id);
        if (!$seller) { show_404(); }

        $data['seller']       = $seller;
        $data['stats']        = $this->Seller_model->get_stats($seller_id);
        $data['active_posts'] = $this->Seller_model->get_active_posts($seller_id);
        $data['sold_posts']   = $this->Seller_model->get_sold_posts($seller_id);
        $data['ratings']      = $this->Seller_model->get_ratings($seller_id);
        $data['active_tab']   = $this->input->get('tab') ?: 'active';

        $data['unread_count'] = 0;
        $data['pending_count']= 0;
        if ($this->session->userdata('logged_in')) {
            $uid = $this->session->userdata('user_id');
            $data['unread_count']  = $this->Message_model->count_unread($uid);
            $data['pending_count'] = $this->Order_model->count_pending_for_seller($uid);
        }

        $this->load->view('partials/header', $data);
        $this->load->view('seller/view', $data);
        $this->load->view('partials/footer');
    }
}
