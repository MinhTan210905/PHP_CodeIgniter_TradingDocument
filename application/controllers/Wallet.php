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
class Wallet extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Wallet_model', 'Auth_model', 'Message_model', 'Order_model']);
        $this->load->library('session');
        $this->load->helper(['url', 'form']);
    }

    private function require_login() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Bạn cần đăng nhập để thực hiện thao tác này.');
            redirect('auth');
            exit;
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
        $data['pending_count'] = $this->Order_model->count_action_required($user_id);

        $this->load->view('partials/header', $data);
        $this->load->view('wallet/index', $data);
        $this->load->view('partials/footer');
    }

    // =========================================================
    // NẠP TIỀN (PayOS Live Mode & Mock Mode)
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
            $keys = $this->_get_payos_keys();
            if (empty($keys['client_id']) || empty($keys['api_key']) || empty($keys['checksum_key'])) {
                $this->session->set_flashdata('error', 'Cấu hình PayOS trong file .env chưa đầy đủ!');
                redirect('wallet');
                return;
            }

            // PayOS orderCode phải là số nguyên duy nhất (dùng timestamp + random)
            $order_code = intval(time() + rand(100, 999));

            // Chuẩn bị tham số ký (sắp xếp tăng dần theo bảng chữ cái A-Z)
            $params = [
                'amount'      => (int)$amount,
                'cancelUrl'   => base_url('wallet/payos_cancel?orderCode=' . $order_code),
                'description' => 'NapTienHCMUEPay', // Chỉ ký chữ và số không dấu không dấu cách để tránh lỗi ký
                'orderCode'   => $order_code,
                'returnUrl'   => base_url('wallet/payos_callback?orderCode=' . $order_code)
            ];

            $signature = $this->_generate_payos_signature($params, $keys['checksum_key']);
            $params['signature'] = $signature;

            // Gọi API PayOS để tạo liên kết thanh toán (checkoutUrl)
            $payload = json_encode($params);
            $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "x-client-id: " . $keys['client_id'],
                "x-api-key: " . $keys['api_key'],
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                log_message('error', 'PayOS cURL Error: ' . $err);
                $this->session->set_flashdata('error', 'Lỗi kết nối cổng thanh toán PayOS: ' . $err);
                redirect('wallet');
                return;
            }

            $res_data = json_decode($response, true);
            if (isset($res_data['code']) && $res_data['code'] === '00' && isset($res_data['data']['checkoutUrl'])) {
                // Chuyển hướng người dùng đến trang thanh toán của PayOS
                redirect($res_data['data']['checkoutUrl']);
                return;
            } else {
                $err_msg = isset($res_data['desc']) ? $res_data['desc'] : 'Không xác định';
                log_message('error', 'PayOS API Error: ' . $response);
                $this->session->set_flashdata('error', 'Lỗi tạo liên kết thanh toán PayOS: ' . $err_msg);
                redirect('wallet');
                return;
            }
        }

        // CHẾ ĐỘ MOCK: Nạp tiền trực tiếp vào ví (dùng để demo)
        $result = $this->Wallet_model->deposit(
            $user_id, 
            $amount, 
            '🎉 Nạp tiền HCMUEPay (+' . number_format($amount, 0, ',', '.') . 'đ)',
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

    // =========================================================
    // HELPER: Lấy danh sách API Keys của PayOS từ file .env
    // =========================================================
    private function _get_payos_keys() {
        $keys = [
            'client_id'    => '',
            'api_key'      => '',
            'checksum_key' => ''
        ];
        
        $env_file = FCPATH . '.env';
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $k = trim($parts[0]);
                    $v = trim(trim($parts[1]), '"\'');
                    if ($k === 'PAYOS_CLIENT_ID') {
                        $keys['client_id'] = $v;
                    } elseif ($k === 'PAYOS_API_KEY') {
                        $keys['api_key'] = $v;
                    } elseif ($k === 'PAYOS_CHECKSUM_KEY') {
                        $keys['checksum_key'] = $v;
                    }
                }
            }
        }
        return $keys;
    }

    // =========================================================
    // HELPER: Tạo chữ ký (Signature) của PayOS bằng HMAC SHA256
    // =========================================================
    private function _generate_payos_signature($data, $checksum_key) {
        ksort($data);
        $query_parts = [];
        foreach ($data as $k => $v) {
            $query_parts[] = "$k=$v";
        }
        $query_str = implode('&', $query_parts);
        return hash_hmac('sha256', $query_str, $checksum_key);
    }

    // =========================================================
    // CALLBACK: Xử lý khi thanh toán PayOS thành công (returnUrl)
    // =========================================================
    public function payos_callback() {
        $this->require_login();
        $user_id = $this->session->userdata('user_id');
        $order_code = $this->input->get('orderCode', TRUE);

        if (empty($order_code)) {
            $this->session->set_flashdata('error', 'Mã đơn hàng PayOS không hợp lệ!');
            redirect('wallet');
            return;
        }

        // Gọi trực tiếp API lên PayOS để lấy thông tin chuẩn từ Server-to-Server
        $keys = $this->_get_payos_keys();
        if (empty($keys['client_id']) || empty($keys['api_key'])) {
            $this->session->set_flashdata('error', 'Cấu hình PayOS chưa đầy đủ để xác thực giao dịch!');
            redirect('wallet');
            return;
        }

        $ch = curl_init('https://api-merchant.payos.vn/v2/payment-requests/' . $order_code);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-client-id: " . $keys['client_id'],
            "x-api-key: " . $keys['api_key'],
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'PayOS Verify Transaction cURL Error: ' . $err);
            $this->session->set_flashdata('error', 'Không thể kết nối đến PayOS để xác thực giao dịch!');
            redirect('wallet');
            return;
        }

        $res_data = json_decode($response, true);
        if (isset($res_data['code']) && $res_data['code'] === '00' && isset($res_data['data'])) {
            $tx_data = $res_data['data'];
            $tx_status = $tx_data['status'];
            $amount = (float)$tx_data['amount'];

            if ($tx_status === 'PAID') {
                // Kiểm tra trùng mã tham chiếu trong DB để tránh nạp tiền trùng lặp
                $is_exists = $this->db->where('payos_reference', $order_code)->count_all_results('hcmuepay_transactions');
                if ($is_exists > 0) {
                    $this->session->set_flashdata('info', 'Giao dịch nạp tiền này đã được cộng số dư trước đó!');
                    redirect('wallet');
                    return;
                }

                // Cộng tiền vào ví thành viên
                $desc = 'Nạp tiền tự động qua PayOS (Mã: ' . $order_code . ')';
                $result = $this->Wallet_model->deposit($user_id, $amount, $desc, $order_code);

                if ($result) {
                    $this->session->set_flashdata('success', '✅ Nạp thành công ' . number_format($amount, 0, ',', '.') . 'đ qua cổng PayOS!');
                } else {
                    $this->session->set_flashdata('error', 'Lỗi hệ thống khi cộng tiền vào ví!');
                }
            } else {
                $this->session->set_flashdata('error', 'Giao dịch PayOS chưa được thanh toán (Trạng thái: ' . $tx_status . ')');
            }
        } else {
            $err_msg = isset($res_data['desc']) ? $res_data['desc'] : 'Không tìm thấy giao dịch trên cổng thanh toán';
            $this->session->set_flashdata('error', 'Xác thực PayOS thất bại: ' . $err_msg);
        }

        redirect('wallet');
    }

    // =========================================================
    // CANCEL: Xử lý khi hủy thanh toán PayOS (cancelUrl)
    // =========================================================
    public function payos_cancel() {
        $this->session->set_flashdata('warning', 'Bạn đã hủy yêu cầu nạp tiền qua cổng PayOS.');
        redirect('wallet');
    }
}
