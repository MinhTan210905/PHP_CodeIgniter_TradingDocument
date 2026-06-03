<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Session $session
 * @property CI_Input $input
 * @property Wishlist_model $Wishlist_model
 * @property Message_model $Message_model
 * @property Order_model $Order_model
 */
class Wishlist extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Wishlist_model', 'Message_model', 'Order_model']);
        $this->load->helper(['form', 'url']);
        $this->load->library('session');
    }

    /**
     * Helper: Yêu cầu đăng nhập
     */
    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Vui lòng đăng nhập để sử dụng tính năng này.');
            redirect('auth');
        }
    }

    /**
     * Trang quản lý danh sách mong muốn
     */
    public function index() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $data['wishlists']    = $this->Wishlist_model->get_by_user($user_id);
        $data['wishlist_count'] = count($data['wishlists']);
        $data['max_wishlists'] = 10;
        $data['unread_count']  = $this->Message_model->count_unread($user_id);
        $data['pending_count'] = $this->Order_model->count_action_required($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('wishlist/index', $data);
        $this->load->view('partials/footer');
    }

    /**
     * Thêm sách mong muốn
     */
    public function add() {
        $this->require_login();
        $user_id    = $this->session->userdata('user_id');
        $book_title = trim($this->input->post('book_title', TRUE));

        if (empty($book_title)) {
            $this->session->set_flashdata('error', 'Vui lòng nhập tên sách mong muốn.');
            redirect('wishlist');
            return;
        }

        if (mb_strlen($book_title, 'UTF-8') > 200) {
            $this->session->set_flashdata('error', 'Tên sách không được vượt quá 200 ký tự.');
            redirect('wishlist');
            return;
        }

        $result = $this->Wishlist_model->add($user_id, $book_title);

        if ($result === true) {
            $this->session->set_flashdata('success', '✅ Đã thêm "' . htmlspecialchars($book_title) . '" vào danh sách mong muốn.');
        } else {
            $this->session->set_flashdata('error', $result);
        }

        redirect('wishlist');
    }

    /**
     * Bật/tắt thông báo
     */
    public function toggle($id) {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $new_status = $this->Wishlist_model->toggle($id, $user_id);

        if ($new_status === false) {
            $this->session->set_flashdata('error', 'Không tìm thấy mong muốn này.');
        } else {
            $label = $new_status ? 'bật' : 'tắt';
            $this->session->set_flashdata('success', "✅ Đã {$label} thông báo cho mong muốn này.");
        }

        redirect('wishlist');
    }

    /**
     * Xóa mong muốn
     */
    public function delete($id) {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $this->Wishlist_model->delete($id, $user_id);
        $this->session->set_flashdata('success', '✅ Đã xóa mong muốn khỏi danh sách.');

        redirect('wishlist');
    }
}
