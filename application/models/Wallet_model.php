<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Wallet_model - Quản lý ví điện tử HCMUEPay
 * 
 * @property CI_DB_query_builder $db
 */
class Wallet_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lấy hoặc tạo ví cho user
     * Mỗi user có duy nhất 1 ví, tự động tạo nếu chưa có
     */
    public function get_or_create_wallet($user_id) {
        $wallet = $this->db->where('user_id', $user_id)->get('hcmuepay_wallets')->row_array();
        
        if (!$wallet) {
            $this->db->insert('hcmuepay_wallets', ['user_id' => $user_id]);
            $wallet = $this->db->where('user_id', $user_id)->get('hcmuepay_wallets')->row_array();
        }
        
        return $wallet;
    }

    /**
     * Lấy ví theo wallet_id
     */
    public function get_wallet_by_id($id) {
        return $this->db->where('id', $id)->get('hcmuepay_wallets')->row_array();
    }

    // =========================================================
    // NẠP TIỀN VÀO VÍ (Deposit — PayOS Webhook hoặc Mock)
    // =========================================================

    /**
     * Nạp tiền vào ví — cộng số dư khả dụng
     * @param int    $user_id    Chủ ví
     * @param float  $amount     Số tiền nạp
     * @param string $desc       Nội dung giao dịch
     * @param string $payos_ref  Mã tham chiếu PayOS (nullable)
     * @return bool
     */
    public function deposit($user_id, $amount, $desc = '', $payos_ref = null) {
        $wallet = $this->get_or_create_wallet($user_id);
        
        $this->db->trans_start();
        
        // Cộng số dư
        $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
        $this->db->where('id', $wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // Ghi lịch sử
        $this->db->insert('hcmuepay_transactions', [
            'wallet_id'       => $wallet['id'],
            'amount'          => $amount,
            'type'            => 'deposit',
            'status'          => 'completed',
            'description'     => $desc ?: 'Nạp tiền vào ví HCMUEPay',
            'payos_reference' => $payos_ref,
        ]);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =========================================================
    // THANH TOÁN ĐƠN HÀNG (Trừ ví Buyer → Tạm giữ Escrow)
    // =========================================================

    /**
     * Thanh toán đơn hàng bằng ví: Trừ balance buyer, cộng holding_balance seller
     * @param int   $buyer_id
     * @param int   $seller_id
     * @param int   $order_id
     * @param float $amount
     * @return bool|string  TRUE nếu thành công, string lỗi nếu thất bại
     */
    public function pay_order($buyer_id, $seller_id, $order_id, $amount) {
        // Gọi get_or_create_wallet trước để đảm bảo ví tồn tại trước khi khóa
        $this->get_or_create_wallet($buyer_id);
        $this->get_or_create_wallet($seller_id);
        
        // Bắt đầu transaction để khóa row
        $this->db->trans_start();
        
        // Khóa ví của buyer và seller bằng FOR UPDATE (Pessimistic Locking) để chống Race Condition
        $buyer_wallet = $this->db->query("SELECT * FROM hcmuepay_wallets WHERE user_id = ? FOR UPDATE", array($buyer_id))->row_array();
        $seller_wallet = $this->db->query("SELECT * FROM hcmuepay_wallets WHERE user_id = ? FOR UPDATE", array($seller_id))->row_array();
        
        // Kiểm tra số dư trên dòng đã được khóa an toàn
        if ((float)$buyer_wallet['balance'] < (float)$amount) {
            $this->db->trans_rollback(); // Huỷ transaction
            return 'Số dư ví không đủ! Bạn cần nạp thêm ' . number_format($amount - $buyer_wallet['balance'], 0, ',', '.') . 'đ.';
        }
        
        // 1. Trừ balance người mua
        $this->db->set('balance', 'balance - ' . (float)$amount, FALSE);
        $this->db->where('id', $buyer_wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // 2. Cộng holding_balance người bán (tạm giữ)
        $this->db->set('holding_balance', 'holding_balance + ' . (float)$amount, FALSE);
        $this->db->where('id', $seller_wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // 3. Ghi lịch sử cho buyer (chi tiền)
        $this->db->insert('hcmuepay_transactions', [
            'wallet_id'   => $buyer_wallet['id'],
            'order_id'    => $order_id,
            'amount'      => -$amount,
            'type'        => 'payment',
            'status'      => 'completed',
            'description' => 'Thanh toán đơn hàng #' . $order_id,
        ]);
        
        // 4. Ghi lịch sử cho seller (tạm giữ)
        $this->db->insert('hcmuepay_transactions', [
            'wallet_id'   => $seller_wallet['id'],
            'order_id'    => $order_id,
            'amount'      => $amount,
            'type'        => 'receive',
            'status'      => 'pending', // chưa "completed" cho đến khi buyer xác nhận nhận sách
            'description' => 'Tạm giữ tiền đơn hàng #' . $order_id . ' (Chờ giao sách)',
        ]);
        
        $this->db->trans_complete();
        return $this->db->trans_status() ? TRUE : 'Lỗi hệ thống khi xử lý thanh toán.';
    }

    // =========================================================
    // GIẢI NGÂN (Buyer xác nhận nhận sách → Chuyển holding → balance Seller)
    // =========================================================

    /**
     * Giải ngân khi đơn hàng hoàn thành
     */
    public function release_escrow($seller_id, $order_id, $amount) {
        $seller_wallet = $this->get_or_create_wallet($seller_id);
        
        $this->db->trans_start();
        
        // Chuyển từ holding_balance → balance
        $this->db->set('holding_balance', 'holding_balance - ' . (float)$amount, FALSE);
        $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
        $this->db->where('id', $seller_wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // Cập nhật transaction sang completed
        $this->db->where('wallet_id', $seller_wallet['id']);
        $this->db->where('order_id', $order_id);
        $this->db->where('type', 'receive');
        $this->db->where('status', 'pending');
        $this->db->update('hcmuepay_transactions', ['status' => 'completed', 'description' => 'Nhận tiền đơn hàng #' . $order_id . ' (Đã giao sách thành công)']);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =========================================================
    // HOÀN TIỀN (Refund khi đơn bị hủy/tranh chấp)
    // =========================================================

    /**
     * Hoàn tiền về ví buyer khi đơn bị hủy
     */
    public function refund_order($buyer_id, $seller_id, $order_id, $amount) {
        $buyer_wallet  = $this->get_or_create_wallet($buyer_id);
        $seller_wallet = $this->get_or_create_wallet($seller_id);
        
        $this->db->trans_start();
        
        // 1. Trừ holding_balance seller
        $this->db->set('holding_balance', 'GREATEST(holding_balance - ' . (float)$amount . ', 0)', FALSE);
        $this->db->where('id', $seller_wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // 2. Cộng lại balance buyer
        $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
        $this->db->where('id', $buyer_wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // 3. Ghi lịch sử hoàn tiền
        $this->db->insert('hcmuepay_transactions', [
            'wallet_id'   => $buyer_wallet['id'],
            'order_id'    => $order_id,
            'amount'      => $amount,
            'type'        => 'refund',
            'status'      => 'completed',
            'description' => 'Hoàn tiền đơn hàng #' . $order_id,
        ]);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =========================================================
    // LỊCH SỬ GIAO DỊCH
    // =========================================================

    /**
     * Lấy lịch sử giao dịch của user (phân trang)
     */
    public function get_transactions($user_id, $limit = 20, $offset = 0) {
        $wallet = $this->get_or_create_wallet($user_id);
        
        return $this->db->where('wallet_id', $wallet['id'])
                        ->order_by('created_at', 'DESC')
                        ->limit($limit, $offset)
                        ->get('hcmuepay_transactions')
                        ->result_array();
    }

    /**
     * Đếm tổng số giao dịch
     */
    public function count_transactions($user_id) {
        $wallet = $this->get_or_create_wallet($user_id);
        return $this->db->where('wallet_id', $wallet['id'])->count_all_results('hcmuepay_transactions');
    }

    // =========================================================
    // YÊU CẦU RÚT TIỀN
    // =========================================================

    /**
     * Tạo yêu cầu rút tiền
     */
    public function create_withdraw_request($user_id, $data) {
        $wallet = $this->get_or_create_wallet($user_id);
        $amount = (float)$data['amount'];
        
        if ($wallet['balance'] < $amount) {
            return 'Số dư không đủ để rút!';
        }
        
        $this->db->trans_start();
        
        // Trừ balance ngay (giữ chỗ)
        $this->db->set('balance', 'balance - ' . $amount, FALSE);
        $this->db->where('id', $wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // Tạo yêu cầu
        $this->db->insert('hcmuepay_withdraw_requests', [
            'user_id'        => $user_id,
            'amount'         => $amount,
            'bank_name'      => $data['bank_name'],
            'account_number' => $data['account_number'],
            'account_name'   => $data['account_name'],
        ]);
        
        // Ghi lịch sử
        $this->db->insert('hcmuepay_transactions', [
            'wallet_id'   => $wallet['id'],
            'amount'      => -$amount,
            'type'        => 'withdraw',
            'status'      => 'pending',
            'description' => 'Yêu cầu rút ' . number_format($amount, 0, ',', '.') . 'đ về ' . $data['bank_name'],
        ]);
        
        $this->db->trans_complete();
        return $this->db->trans_status() ? TRUE : 'Lỗi hệ thống!';
    }

    /**
     * Lấy danh sách yêu cầu rút tiền của user
     */
    public function get_withdraw_requests($user_id) {
        return $this->db->where('user_id', $user_id)
                        ->order_by('created_at', 'DESC')
                        ->get('hcmuepay_withdraw_requests')
                        ->result_array();
    }

    /**
     * Admin: Lấy tất cả yêu cầu rút tiền (pending)
     */
    public function get_all_pending_withdrawals() {
        $this->db->select('hcmuepay_withdraw_requests.*, users.full_name, users.email');
        $this->db->from('hcmuepay_withdraw_requests');
        $this->db->join('users', 'users.id = hcmuepay_withdraw_requests.user_id', 'left');
        $this->db->where('hcmuepay_withdraw_requests.status', 'pending');
        $this->db->order_by('hcmuepay_withdraw_requests.created_at', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Admin: Lấy lịch sử yêu cầu rút tiền đã xử lý (approved, rejected)
     */
    public function get_all_processed_withdrawals($limit = 50) {
        $this->db->select('hcmuepay_withdraw_requests.*, users.full_name, users.email');
        $this->db->from('hcmuepay_withdraw_requests');
        $this->db->join('users', 'users.id = hcmuepay_withdraw_requests.user_id', 'left');
        $this->db->where('hcmuepay_withdraw_requests.status !=', 'pending');
        $this->db->order_by('hcmuepay_withdraw_requests.processed_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    /**
     * Admin: Duyệt yêu cầu rút tiền
     */
    public function approve_withdrawal($id) {
        $this->db->where('id', $id);
        return $this->db->update('hcmuepay_withdraw_requests', [
            'status'       => 'approved',
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Admin: Từ chối yêu cầu rút tiền (hoàn lại balance)
     */
    public function reject_withdrawal($id, $note = '') {
        $req = $this->db->where('id', $id)->get('hcmuepay_withdraw_requests')->row_array();
        if (!$req || $req['status'] !== 'pending') return FALSE;
        
        $this->db->trans_start();
        
        // Hoàn balance
        $wallet = $this->get_or_create_wallet($req['user_id']);
        $this->db->set('balance', 'balance + ' . (float)$req['amount'], FALSE);
        $this->db->where('id', $wallet['id']);
        $this->db->update('hcmuepay_wallets');
        
        // Cập nhật trạng thái
        $this->db->where('id', $id);
        $this->db->update('hcmuepay_withdraw_requests', [
            'status'       => 'rejected',
            'admin_note'   => $note,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Đếm yêu cầu rút tiền đang chờ (badge cho Admin)
     */
    public function count_pending_withdrawals() {
        return $this->db->where('status', 'pending')->count_all_results('hcmuepay_withdraw_requests');
    }

    /**
     * Admin đảo ngược phán quyết tranh chấp: Thu hồi tiền bên này hoàn/bồi thường cho bên kia
     * @param int    $buyer_id
     * @param int    $seller_id
     * @param int    $order_id
     * @param float  $amount
     * @param string $reverse_to  'buyer' (Bồi thường Buyer, thu hồi Seller) hoặc 'seller' (Giải ngân Seller, thu hồi Buyer)
     * @return bool
     */
    public function reverse_dispute_wallets($buyer_id, $seller_id, $order_id, $amount, $reverse_to) {
        $buyer_wallet  = $this->get_or_create_wallet($buyer_id);
        $seller_wallet = $this->get_or_create_wallet($seller_id);

        $this->db->trans_start();

        if ($reverse_to === 'buyer') {
            // 1. Thu hồi từ Seller (Cho phép âm tài khoản)
            $this->db->set('balance', 'balance - ' . (float)$amount, FALSE);
            $this->db->where('id', $seller_wallet['id']);
            $this->db->update('hcmuepay_wallets');

            // 2. Bồi thường / Hoàn tiền cho Buyer
            $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
            $this->db->where('id', $buyer_wallet['id']);
            $this->db->update('hcmuepay_wallets');

            // 3. Ghi lịch sử giao dịch thu hồi (Seller)
            $this->db->insert('hcmuepay_transactions', [
                'wallet_id'   => $seller_wallet['id'],
                'order_id'    => $order_id,
                'amount'      => -$amount,
                'type'        => 'withdraw',
                'status'      => 'completed',
                'description' => 'Thu hồi tiền đơn #' . $order_id . ' do Admin đảo ngược phân xử khiếu nại',
            ]);

            // 4. Ghi lịch sử giao dịch bồi hoàn (Buyer)
            $this->db->insert('hcmuepay_transactions', [
                'wallet_id'   => $buyer_wallet['id'],
                'order_id'    => $order_id,
                'amount'      => $amount,
                'type'        => 'refund',
                'status'      => 'completed',
                'description' => 'Nhận tiền bồi hoàn đơn #' . $order_id . ' do Admin đảo ngược phân xử',
            ]);

        } else if ($reverse_to === 'seller') {
            // 1. Thu hồi từ Buyer (Cho phép âm tài khoản)
            $this->db->set('balance', 'balance - ' . (float)$amount, FALSE);
            $this->db->where('id', $buyer_wallet['id']);
            $this->db->update('hcmuepay_wallets');

            // 2. Giải ngân cho Seller
            $this->db->set('balance', 'balance + ' . (float)$amount, FALSE);
            $this->db->where('id', $seller_wallet['id']);
            $this->db->update('hcmuepay_wallets');

            // 3. Ghi lịch sử giao dịch thu hồi (Buyer)
            $this->db->insert('hcmuepay_transactions', [
                'wallet_id'   => $buyer_wallet['id'],
                'order_id'    => $order_id,
                'amount'      => -$amount,
                'type'        => 'withdraw',
                'status'      => 'completed',
                'description' => 'Thu hồi tiền đơn #' . $order_id . ' do Admin đảo ngược phán quyết tranh chấp',
            ]);

            // 4. Ghi lịch sử giao dịch giải ngân (Seller)
            $this->db->insert('hcmuepay_transactions', [
                'wallet_id'   => $seller_wallet['id'],
                'order_id'    => $order_id,
                'amount'      => $amount,
                'type'        => 'receive',
                'status'      => 'completed',
                'description' => 'Nhận tiền giải ngân đơn #' . $order_id . ' do Admin đảo ngược phán quyết',
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
