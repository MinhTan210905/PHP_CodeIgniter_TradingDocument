<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Wallet Controller - Trang quản lý ví HCMUEPay
 * 
 * @property CI_Session     $session
 * @property CI_Input       $input
 * @property Wallet_model   $Wallet_model
 * @property Auth_model     $Auth_model
 * @property Message_model  $Message_model
 * @property Order_model    $Order_model
 */
class Wallet extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Wallet_model', 'Auth_model', 'Message_model', 'Order_model']);
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    // =========================================================
    // TRANG CHÍNH VÍ HCMUEPAY
    // =========================================================
    public function index() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $data['wallet']       = $this->Wallet_model->get_or_create_wallet($user_id);
        $data['transactions'] = $this->Wallet_model->get_transactions($user_id, 15);
        $data['withdrawals']  = $this->Wallet_model->get_withdraw_requests($user_id);
        $data['user']         = $this->Auth_model->get_user_by_id($user_id);
        $data['unread_count'] = $this->Message_model->count_unread($user_id);
        $data['pending_count'] = $this->Order_model->count_pending_for_seller($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('wallet/index', $data);
        $this->load->view('partials/footer');
    }

    // =========================================================
    // NẠP TIỀN (Mock Mode — Giả lập QR PayOS)
    // =========================================================
    public function deposit() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $amount  = (float)$this->input->post('amount');

        if ($amount < 1000) {
            $this->session->set_flashdata('error', 'Số tiền nạp tối thiểu là 1.000đ!');
            redirect('wallet');
            return;
        }

        if ($amount > 10000000) {
            $this->session->set_flashdata('error', 'Số tiền nạp tối đa là 10.000.000đ!');
            redirect('wallet');
            return;
        }

        // ============================================
        // KIỂM TRA CHẾ ĐỘ PAYOS
        // ============================================
        $payos_mode = $this->_get_payos_mode();

        if ($payos_mode === 'live') {
            // TODO: Gọi PayOS API tạo QR Code tại đây
            // Tạm thời redirect sang trang QR (sẽ build ở Phase 2)
            $this->session->set_flashdata('info', 'Tính năng PayOS đang được tích hợp. Vui lòng sử dụng chế độ Demo.');
            redirect('wallet');
            return;
        }

        // CHẾ ĐỘ MOCK: Nạp tiền trực tiếp vào ví (dùng để demo)
        $result = $this->Wallet_model->deposit(
            $user_id, 
            $amount, 
            '🎉 Nạp tiền Demo HCMUEPay (+' . number_format($amount, 0, ',', '.') . 'đ)',
            'MOCK_' . time()
        );

        if ($result) {
            $this->session->set_flashdata('success', '✅ Nạp thành công ' . number_format($amount, 0, ',', '.') . 'đ vào ví HCMUEPay!');
        } else {
            $this->session->set_flashdata('error', '❌ Nạp tiền thất bại! Vui lòng thử lại.');
        }

        redirect('wallet');
    }

    // =========================================================
    // GỬI YÊU CẦU RÚT TIỀN
    // =========================================================
    public function withdraw() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');

        $amount         = (float)$this->input->post('amount');
        $bank_name      = $this->input->post('bank_name', TRUE);
        $account_number = $this->input->post('account_number', TRUE);
        $account_name   = $this->input->post('account_name', TRUE);

        if ($amount < 10000) {
            $this->session->set_flashdata('error', 'Số tiền rút tối thiểu là 10.000đ!');
            redirect('wallet');
            return;
        }

        if (empty($bank_name) || empty($account_number) || empty($account_name)) {
            $this->session->set_flashdata('error', 'Vui lòng điền đầy đủ thông tin ngân hàng!');
            redirect('wallet');
            return;
        }

        $result = $this->Wallet_model->create_withdraw_request($user_id, [
            'amount'         => $amount,
            'bank_name'      => $bank_name,
            'account_number' => $account_number,
            'account_name'   => $account_name,
        ]);

        if ($result === TRUE) {
            $this->session->set_flashdata('success', '✅ Đã gửi yêu cầu rút ' . number_format($amount, 0, ',', '.') . 'đ! Admin sẽ duyệt trong 24h.');
        } else {
            $this->session->set_flashdata('error', $result);
        }

        redirect('wallet');
    }

    // =========================================================
    // HELPER: Kiểm tra chế độ PayOS
    // =========================================================
    private function _get_payos_mode() {
        // Đọc file .env để kiểm tra key PayOS
        $env_file = FCPATH . '.env';
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                if (strpos($line, 'PAYOS_CLIENT_ID') !== false) {
                    $val = trim(explode('=', $line, 2)[1] ?? '', '"\'');
                    if (!empty($val) && strpos($val, 'your_') === false) {
                        return 'live';
                    }
                }
            }
        }
        return 'mock'; // Mặc định: chế độ Demo
    }
}
