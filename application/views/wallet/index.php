<?php $cur_uid = $this->session->userdata('user_id'); ?>

<style>
/* ============================================================
   HCMUEPay Wallet — Premium UI Styles
   ============================================================ */
.wallet-hero {
    background: linear-gradient(145deg, #0F172A 0%, #1E3A8A 50%, #2563EB 100%);
    border-radius: 20px;
    padding: 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.wallet-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    border-radius: 50%;
}
.wallet-hero::after {
    content: '';
    position: absolute;
    bottom: -30px; left: 30%;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(96,165,250,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.wallet-balance-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    opacity: 0.7;
    font-weight: 600;
}
.wallet-balance-amount {
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1.1;
    text-shadow: 0 2px 12px rgba(0,0,0,0.2);
}
.wallet-balance-amount small {
    font-size: 1rem;
    font-weight: 600;
    opacity: 0.7;
    margin-left: 4px;
}
.wallet-sub-balance {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.12);
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
}
.wallet-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    border-radius: 14px;
    border: 1.5px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.06);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 600;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none;
    min-width: 80px;
}
.wallet-action-btn:hover {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.3);
    color: #fff;
    transform: translateY(-2px);
}
.wallet-action-btn i {
    font-size: 1.2rem;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255,255,255,0.12);
}

/* Transaction List */
.tx-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #F1F5F9;
    transition: background 0.15s;
}
.tx-item:last-child { border-bottom: none; }
.tx-item:hover { background: #FAFBFF; margin: 0 -16px; padding: 14px 16px; border-radius: 10px; }
.tx-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.tx-icon.deposit  { background: #ECFDF5; color: #059669; }
.tx-icon.payment  { background: #FEF2F2; color: #DC2626; }
.tx-icon.receive  { background: #EFF6FF; color: #2563EB; }
.tx-icon.withdraw { background: #FFF7ED; color: #EA580C; }
.tx-icon.refund   { background: #F5F3FF; color: #7C3AED; }
.tx-amount.positive { color: #059669; font-weight: 700; }
.tx-amount.negative { color: #DC2626; font-weight: 700; }

/* Withdraw requests status badges */
.wd-status-pending  { background: #FEF3C7; color: #92400E; }
.wd-status-approved { background: #D1FAE5; color: #065F46; }
.wd-status-rejected { background: #FEE2E2; color: #991B1B; }

/* Mock mode banner */
.mock-banner {
    background: linear-gradient(90deg, #FFF7ED, #FFFBEB);
    border: 1.5px solid #FDE68A;
    border-radius: 12px;
    padding: 12px 18px;
    font-size: 0.82rem;
}
</style>

<div class="container py-4" style="max-width: 850px;">

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-hcmue alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- WALLET HERO CARD -->
    <!-- ============================================================ -->
    <div class="wallet-hero mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="wallet-balance-label mb-1">
                    <i class="fas fa-wallet me-1"></i> Số dư ví HCMUEPay
                </div>
                <div class="wallet-balance-amount">
                    <?= number_format($wallet['balance'], 0, ',', '.') ?><small>đ</small>
                </div>
            </div>
            <div class="text-end">
                <div style="font-size:0.7rem; opacity:0.5; margin-bottom:4px;">ID VÍ: #<?= $wallet['id'] ?></div>
                <?php if ((float)$wallet['holding_balance'] > 0): ?>
                    <div class="wallet-sub-balance">
                        <i class="fas fa-lock" style="font-size:0.7rem;"></i>
                        Tạm giữ: <?= number_format($wallet['holding_balance'], 0, ',', '.') ?>đ
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-3">
            <button class="wallet-action-btn flex-fill" data-bs-toggle="modal" data-bs-target="#depositModal">
                <i class="fas fa-plus-circle"></i>
                Nạp tiền
            </button>
            <button class="wallet-action-btn flex-fill" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                <i class="fas fa-money-bill-transfer"></i>
                Rút tiền
            </button>
            <a href="<?= site_url('orders') ?>" class="wallet-action-btn flex-fill">
                <i class="fas fa-shopping-bag"></i>
                Đơn hàng
            </a>
        </div>
    </div>

    <!-- Mock Mode Banner -->
    <div class="mock-banner mb-4">
        <i class="fas fa-flask me-2 text-warning"></i>
        <strong>Chế độ Demo:</strong> Hệ thống đang chạy ở chế độ giả lập. Tiền nạp là tiền ảo để thử nghiệm. 
        Khi tích hợp PayOS, hệ thống sẽ tự động chuyển sang quét mã QR thật.
    </div>

    <div class="row g-4">
        <!-- ============================================================ -->
        <!-- LỊCH SỬ GIAO DỊCH -->
        <!-- ============================================================ -->
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>Lịch sử giao dịch
                    </h6>
                </div>
                <div class="card-body px-4 py-2">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt" style="font-size:2.5rem; color:#CBD5E1;"></i>
                            <p class="text-muted mt-3" style="font-size:0.88rem;">Chưa có giao dịch nào.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                                $icon_map = [
                                    'deposit'  => ['fas fa-arrow-down',         'deposit'],
                                    'payment'  => ['fas fa-shopping-cart',       'payment'],
                                    'receive'  => ['fas fa-hand-holding-dollar', 'receive'],
                                    'withdraw' => ['fas fa-arrow-up-from-bracket','withdraw'],
                                    'refund'   => ['fas fa-rotate-left',         'refund'],
                                ];
                                $ic = $icon_map[$tx['type']] ?? ['fas fa-circle', 'deposit'];
                                $is_positive = (float)$tx['amount'] >= 0;
                            ?>
                            <div class="tx-item">
                                <div class="tx-icon <?= $ic[1] ?>">
                                    <i class="<?= $ic[0] ?>"></i>
                                </div>
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="fw-bold" style="font-size:0.84rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($tx['description']) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:0.72rem;">
                                        <?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?>
                                        <?php if ($tx['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;">Đang xử lý</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="tx-amount <?= $is_positive ? 'positive' : 'negative' ?>" style="font-size:0.9rem; white-space:nowrap;">
                                    <?= $is_positive ? '+' : '' ?><?= number_format($tx['amount'], 0, ',', '.') ?>đ
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- YÊU CẦU RÚT TIỀN -->
        <!-- ============================================================ -->
        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0">
                        <i class="fas fa-money-check-alt me-2 text-success"></i>Lịch sử rút tiền
                    </h6>
                </div>
                <div class="card-body px-4 py-3">
                    <?php if (empty($withdrawals)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-piggy-bank" style="font-size:2rem; color:#CBD5E1;"></i>
                            <p class="text-muted mt-2" style="font-size:0.82rem;">Chưa có yêu cầu rút tiền.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $wd): ?>
                            <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color: #F1F5F9 !important;">
                                <div class="flex-grow-1" style="min-width:0;">
                                    <div class="fw-bold" style="font-size:0.82rem;">
                                        <?= number_format($wd['amount'], 0, ',', '.') ?>đ
                                    </div>
                                    <div class="text-muted" style="font-size:0.7rem;">
                                        <?= $wd['bank_name'] ?> • <?= $wd['account_number'] ?>
                                    </div>
                                    <div class="text-muted" style="font-size:0.65rem;">
                                        <?= date('d/m/Y H:i', strtotime($wd['created_at'])) ?>
                                    </div>
                                </div>
                                <?php
                                    $st_class = [
                                        'pending'  => 'wd-status-pending',
                                        'approved' => 'wd-status-approved',
                                        'rejected' => 'wd-status-rejected',
                                    ];
                                    $st_text = [
                                        'pending'  => 'Đang chờ',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                    ];
                                ?>
                                <span class="badge <?= $st_class[$wd['status']] ?? '' ?>" style="font-size:0.68rem; padding:4px 10px; border-radius:20px;">
                                    <?= $st_text[$wd['status']] ?? $wd['status'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Info Card -->
            <div class="card border-0 rounded-4 shadow-sm mt-4 p-4" style="background: linear-gradient(135deg, #EFF6FF, #F0FDF4);">
                <h6 class="fw-bold mb-3" style="font-size:0.85rem; color:#1E40AF;">
                    <i class="fas fa-shield-halved me-2"></i>An toàn & Minh bạch
                </h6>
                <ul class="mb-0" style="font-size:0.78rem; color:#475569; padding-left:18px;">
                    <li class="mb-2">Tiền mua sách sẽ được <strong>tạm giữ</strong> cho đến khi bạn xác nhận nhận hàng.</li>
                    <li class="mb-2">Người bán chỉ nhận tiền khi bạn bấm <strong>"Đã nhận sách"</strong>.</li>
                    <li>Rút tiền về ngân hàng sẽ được Admin xử lý trong <strong>24 giờ</strong>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: NẠP TIỀN -->
<!-- ============================================================ -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #059669, #10B981);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="fas fa-plus-circle me-2"></i>Nạp tiền vào ví
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('wallet/deposit') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary small">Số tiền nạp (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg rounded-3 border-light shadow-none" 
                                   name="amount" min="1000" max="10000000" step="1000" 
                                   placeholder="Nhập số tiền..." required
                                   style="background:#f8fafc; font-weight:700; font-size:1.2rem;">
                            <span class="input-group-text border-light bg-light fw-bold">đ</span>
                        </div>
                    </div>
                    
                    <!-- Quick amount buttons -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach([10000, 20000, 50000, 100000, 200000, 500000] as $q): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 fw-bold quick-amount-btn" 
                                    onclick="document.querySelector('#depositModal input[name=amount]').value=<?= $q ?>">
                                <?= number_format($q, 0, ',', '.') ?>đ
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="alert alert-info border-0 small mb-0" style="background:#EFF6FF; border-radius:10px;">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Chế độ Demo:</strong> Số tiền sẽ được cộng trực tiếp vào ví ảo của bạn để thử nghiệm.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-3 fw-bold px-4 shadow-sm">
                        <i class="fas fa-bolt me-1"></i> Nạp ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: RÚT TIỀN -->
<!-- ============================================================ -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #EA580C, #F97316);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="fas fa-money-bill-transfer me-2"></i>Rút tiền về ngân hàng
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('wallet/withdraw') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3 p-3 rounded-3" style="background:#F8FAFC;">
                        <div class="text-muted small">Số dư khả dụng</div>
                        <div class="fw-bold" style="font-size:1.3rem; color:#059669;">
                            <?= number_format($wallet['balance'], 0, ',', '.') ?>đ
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary small">Số tiền rút (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control rounded-3 border-light shadow-none" 
                               name="amount" min="10000" max="<?= $wallet['balance'] ?>" step="1000" required
                               style="background:#f8fafc; font-weight:600;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary small">Tên ngân hàng <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 border-light shadow-none" name="bank_name" required style="background:#f8fafc;">
                            <option value="">-- Chọn ngân hàng --</option>
                            <option value="MB Bank">MB Bank</option>
                            <option value="Vietcombank">Vietcombank</option>
                            <option value="VietinBank">VietinBank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="Techcombank">Techcombank</option>
                            <option value="ACB">ACB</option>
                            <option value="Sacombank">Sacombank</option>
                            <option value="TPBank">TPBank</option>
                            <option value="VPBank">VPBank</option>
                            <option value="Momo">Ví MoMo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary small">Số tài khoản <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 border-light shadow-none" 
                               name="account_number" required placeholder="VD: 0123456789"
                               style="background:#f8fafc;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600 text-secondary small">Tên chủ tài khoản <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 border-light shadow-none" 
                               name="account_name" required placeholder="VD: NGUYEN VAN A"
                               style="background:#f8fafc; text-transform: uppercase;">
                    </div>

                    <div class="alert alert-warning border-0 small mb-0" style="border-radius:10px;">
                        <i class="fas fa-clock me-1"></i>
                        Yêu cầu rút tiền sẽ được Admin xem xét và chuyển khoản thực tế trong vòng <strong>24 giờ</strong>.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn rounded-3 fw-bold px-4 shadow-sm text-white" style="background: linear-gradient(135deg, #EA580C, #F97316);">
                        <i class="fas fa-paper-plane me-1"></i> Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
