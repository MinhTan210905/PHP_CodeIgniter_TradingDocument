<div class="container py-4">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#10B981,#059669);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;">
                <i class="fas fa-money-check-alt"></i>
            </div>
            <div>
                <h2 style="font-size:1.2rem;font-weight:800;color:var(--primary);margin:0;">Kiểm duyệt thanh toán</h2>
                <span class="text-muted" style="font-size:0.8rem;">Xác nhận chuyển tiền cho người bán sau khi giao dịch hoàn tất</span>
            </div>
        </div>
        <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-3 px-3" style="font-size:0.85rem;">
            <i class="fas fa-arrow-left me-1"></i> Về Dashboard
        </a>
    </div>

    <!-- YÊU CẦU RÚT TIỀN (VÍ HCMUEPAY) -->
    <h5 class="mb-3 fw-bold" style="color:var(--primary);"><i class="fas fa-wallet me-2"></i>Yêu cầu rút tiền (Ví HCMUEPay)</h5>
    <?php if (empty($withdrawals)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#F0FDF4;border:1.5px dashed #86EFAC!important;">
            <i class="fas fa-check-circle" style="font-size:2rem;color:#22C55E;"></i>
            <p class="mt-2 mb-0 fw-semibold" style="color:#166534;">Không có yêu cầu rút tiền nào cần duyệt!</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                    <thead style="background:#F8FAFC;">
                        <tr class="text-muted" style="font-size:0.77rem;">
                            <th style="padding:12px 16px;">Mã YC</th>
                            <th>Người dùng</th>
                            <th>Số tiền rút</th>
                            <th>Thông tin ngân hàng</th>
                            <th>Ngày yêu cầu</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($withdrawals as $w): ?>
                        <tr>
                            <td style="padding:10px 16px;"><span class="fw-bold text-primary">#<?= $w['id'] ?></span></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($w['full_name']) ?></div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($w['email']) ?></div>
                            </td>
                            <td class="text-danger fw-bold"><?= number_format($w['amount'],0,',','.') ?>đ</td>
                            <td>
                                <div><strong>NH:</strong> <?= htmlspecialchars($w['bank_name']) ?></div>
                                <div><strong>STK:</strong> <?= htmlspecialchars($w['account_number']) ?></div>
                                <div><strong>Tên:</strong> <?= htmlspecialchars($w['account_name']) ?></div>
                            </td>
                            <td class="text-muted" style="font-size:0.77rem;">
                                <?= date('d/m/Y H:i', strtotime($w['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= site_url('admin/approve_withdrawal/'.$w['id']) ?>"
                                       class="btn btn-sm btn-success rounded-3 fw-bold" style="font-size:0.78rem;"
                                       onclick="return confirm('Xác nhận ĐÃ CHUYỂN TIỀN thành công cho yêu cầu này?');">
                                        <i class="fas fa-check me-1"></i>Duyệt
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3 fw-bold" style="font-size:0.78rem;"
                                            data-bs-toggle="modal" data-bs-target="#rejectWithdrawModal<?= $w['id'] ?>">
                                        <i class="fas fa-times me-1"></i>Từ chối
                                    </button>
                                </div>

                                <!-- Modal Từ chối rút tiền -->
                                <div class="modal fade" id="rejectWithdrawModal<?= $w['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 rounded-4 shadow">
                                            <div class="modal-header" style="background:linear-gradient(135deg,#DC2626,#EF4444);border-radius:16px 16px 0 0;">
                                                <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Từ chối rút tiền #<?= $w['id'] ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= site_url('admin/reject_withdrawal/'.$w['id']) ?>" method="POST">
                                                <div class="modal-body p-4">
                                                    <p class="text-muted mb-2">Lý do từ chối (tiền sẽ được hoàn lại vào ví người dùng):</p>
                                                    <textarea class="form-control" name="note" rows="3" required placeholder="VD: Sai thông tin ngân hàng..."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger rounded-3 fw-bold">Xác nhận từ chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- LỊCH SỬ DUYỆT RÚT TIỀN -->
    <h5 class="mb-3 fw-bold mt-5" style="color:#64748B;"><i class="fas fa-history me-2"></i>Lịch sử duyệt rút tiền</h5>
    <?php if (empty($processed_withdrawals)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#F8FAFC;border:1.5px dashed #CBD5E1!important;">
            <p class="mt-2 mb-0 fw-semibold" style="color:#475569;">Chưa có lịch sử duyệt rút tiền nào.</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                    <thead style="background:#F1F5F9;">
                        <tr class="text-muted" style="font-size:0.77rem;">
                            <th style="padding:12px 16px;">Mã YC</th>
                            <th>Người dùng</th>
                            <th>Số tiền rút</th>
                            <th>Ngân hàng</th>
                            <th>Trạng thái</th>
                            <th>Thời gian duyệt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($processed_withdrawals as $w): ?>
                        <tr>
                            <td style="padding:10px 16px;"><span class="text-muted">#<?= $w['id'] ?></span></td>
                            <td>
                                <div><?= htmlspecialchars($w['full_name']) ?></div>
                            </td>
                            <td class="fw-bold"><?= number_format($w['amount'],0,',','.') ?>đ</td>
                            <td class="text-muted" style="font-size:0.75rem;">
                                <?= htmlspecialchars($w['bank_name']) ?> - <?= htmlspecialchars($w['account_number']) ?>
                            </td>
                            <td>
                                <?php if($w['status'] === 'approved'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1"><i class="fas fa-check me-1"></i>Đã duyệt</span>
                                <?php elseif($w['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" title="<?= htmlspecialchars($w['admin_note']) ?>"><i class="fas fa-times me-1"></i>Từ chối</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:0.77rem;">
                                <?= date('d/m/Y H:i', strtotime($w['processed_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- ĐƠN HÀNG TRANH CHẤP (CẦN ADMIN PHÂN XỬ) -->
    <h5 class="mb-3 fw-bold mt-5" style="color:#DC2626;"><i class="fas fa-gavel me-2"></i>Đơn hàng tranh chấp
        <?php if (!empty($disputed_orders)): ?>
            <span class="badge bg-danger rounded-pill ms-2" style="font-size:0.75rem;"><?= count($disputed_orders) ?></span>
        <?php endif; ?>
    </h5>
    <?php if (empty($disputed_orders)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#F0FDF4;border:1.5px dashed #86EFAC!important;">
            <i class="fas fa-check-circle" style="font-size:2rem;color:#22C55E;"></i>
            <p class="mt-2 mb-0 fw-semibold" style="color:#166534;">Không có đơn hàng tranh chấp nào cần phân xử!</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#FEF2F2;border:1.5px dashed #FECACA!important;">
            <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#DC2626;"></i>
            <p class="mt-2 mb-3 fw-semibold" style="color:#991B1B;">Có <?= count($disputed_orders) ?> đơn hàng đang tranh chấp cần xử lý!</p>
            <a href="<?= site_url('admin/disputes') ?>" class="btn btn-danger rounded-3 fw-bold px-4">
                <i class="fas fa-gavel me-2"></i>Đi tới Quản lý Tranh chấp
            </a>
        </div>
    <?php endif; ?>

    <!-- QUẢN LÝ GIAO DỊCH ĐƠN HÀNG -->
    <h5 class="mb-3 fw-bold mt-5" style="color:var(--primary);"><i class="fas fa-shopping-cart me-2"></i>Quản lý giao dịch đơn hàng</h5>
    <?php if (empty($completed_orders)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center" style="background:#F8FAFC;border:1.5px dashed #CBD5E1!important;">
            <i class="fas fa-check-circle" style="font-size:2rem;color:#94A3B8;"></i>
            <p class="mt-2 mb-0 fw-semibold" style="color:#475569;">Không có đơn hàng nào cần xử lý thanh toán!</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                    <thead style="background:#F8FAFC;">
                        <tr class="text-muted" style="font-size:0.77rem;">
                            <th style="padding:12px 16px;">Mã đơn</th>
                            <th>Sản phẩm</th>
                            <th>Người bán</th>
                            <th>Người mua</th>
                            <th>Giá</th>
                            <th>Ngày hoàn tất</th>
                            <th class="text-center">PT Thanh toán</th>
                            <th class="text-center">Trạng thái TT</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($completed_orders as $o): ?>
                        <tr>
                            <td style="padding:10px 16px;">
                                <span class="fw-bold" style="color:var(--primary);">#<?= $o['id'] ?></span>
                            </td>
                            <td>
                                <a href="<?= site_url('orders/detail/'.$o['id']) ?>" class="text-decoration-none fw-semibold text-dark" style="font-size:0.84rem;" target="_blank">
                                    <?= htmlspecialchars(mb_strimwidth($o['post_title'], 0, 40, '...')) ?>
                                    <i class="fas fa-external-link-alt ms-1" style="font-size:0.6rem;color:#9CA3AF;"></i>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:28px;height:28px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.7rem;flex-shrink:0;">
                                        <?= strtoupper(substr($o['seller_name'] ?: $o['seller_username'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($o['seller_name'] ?: $o['seller_username']) ?></span>
                                </div>
                            </td>
                            <td style="font-size:0.82rem;"><?= htmlspecialchars($o['buyer_name'] ?: $o['buyer_username']) ?></td>
                            <td class="text-danger fw-bold"><?= number_format($o['price'] * $o['quantity'],0,',','.') ?>đ</td>
                            <td class="text-muted" style="font-size:0.77rem;">
                                <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($o['updated_at'])) ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($o['payment_method']) && $o['payment_method'] === 'wallet'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                        <i class="fas fa-wallet me-2"></i>Ví HCMUEPay
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                        <i class="fas fa-handshake me-2"></i>COD trực tiếp
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($o['payment_status']) && $o['payment_status'] === 'paid'): ?>
                                    <span style="background:#D1FAE5;color:#065F46;font-size:0.75rem;font-weight:700;padding:3px 12px;border-radius:20px;">
                                        <i class="fas fa-check me-1"></i>Đã chuyển
                                    </span>
                                <?php else: ?>
                                    <span style="background:#FEF3C7;color:#92400E;font-size:0.75rem;font-weight:700;padding:3px 12px;border-radius:20px;">
                                        <i class="fas fa-clock me-1"></i>Chưa chuyển
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (empty($o['payment_status']) || $o['payment_status'] !== 'paid'): ?>
                                    <a href="<?= site_url('admin/confirm_payment/'.$o['id']) ?>"
                                       class="btn btn-sm fw-bold rounded-3"
                                       style="background:#D1FAE5;color:#065F46;font-size:0.78rem;padding:5px 14px;"
                                       onclick="return confirm('Xác nhận đã chuyển tiền cho người bán đơn #<?= $o['id'] ?>?');">
                                        <i class="fas fa-money-bill-wave me-1"></i> Xác nhận chuyển tiền
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.78rem;">✅ Hoàn tất</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
