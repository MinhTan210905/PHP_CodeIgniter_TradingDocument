<?php
$status_labels = [
    'pending'    => ['label' => 'Chờ xác nhận',     'color' => '#92400E', 'bg' => '#FEF3C7', 'icon' => 'fa-hourglass-half'],
    'confirmed'  => ['label' => 'Đã xác nhận',       'color' => '#1E40AF', 'bg' => '#DBEAFE', 'icon' => 'fa-handshake'],
    'processing' => ['label' => 'Đang xử lý',       'color' => '#B45309', 'bg' => '#FEF3C7', 'icon' => 'fa-truck'],
    'completed'  => ['label' => 'Hoàn thành',        'color' => '#065F46', 'bg' => '#D1FAE5', 'icon' => 'fa-check-circle'],
    'disputed'   => ['label' => 'Tranh chấp',        'color' => '#991B1B', 'bg' => '#FEE2E2', 'icon' => 'fa-exclamation-triangle'],
    'rejected'   => ['label' => 'Đã từ chối',        'color' => '#6B7280', 'bg' => '#F3F4F6', 'icon' => 'fa-times-circle'],
    'cancelled'  => ['label' => 'Đã hủy',            'color' => '#9CA3AF', 'bg' => '#F3F4F6', 'icon' => 'fa-ban'],
];
$sl       = $status_labels[$order['status']] ?? $status_labels['cancelled'];
$user_id  = $this->session->userdata('user_id');
$timeline = [
    'pending'    => 1,
    'confirmed'  => 2,
    'processing' => 3,
    'completed'  => 3,
    'disputed'   => 3,
    'rejected'   => 2,
    'cancelled'  => 2,
];
$cur_step = $timeline[$order['status']] ?? 1;
?>
<div class="container py-4" style="max-width:760px;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.82rem;">
            <li class="breadcrumb-item"><a href="<?= site_url('orders') ?>" class="text-decoration-none" style="color:var(--hcmue-blue);">Đơn hàng</a></li>
            <li class="breadcrumb-item active text-muted">Chi tiết #<?= $order['id'] ?></li>
        </ol>
    </nav>

    <!-- Status Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <div style="background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-light));padding:20px 24px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white fw-bold" style="font-size:1.1rem;">Đơn hàng #<?= $order['id'] ?></div>
                    <div class="text-white opacity-75" style="font-size:0.8rem;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <span style="background:<?= $sl['bg'] ?>;color:<?= $sl['color'] ?>;padding:6px 16px;border-radius:20px;font-size:0.82rem;font-weight:700;">
                    <i class="fas <?= $sl['icon'] ?> me-1"></i><?= $sl['label'] ?>
                </span>
            </div>
        </div>

        <!-- Timeline -->
        <div class="px-4 py-3" style="background:#F8FAFC;">
            <div class="d-flex align-items-center justify-content-between position-relative">
                <div style="position:absolute;top:14px;left:8%;right:8%;height:2px;background:#E5E7EB;z-index:0;"></div>
                <?php
                $steps = [
                    ['icon' => 'fa-shopping-cart', 'label' => 'Yêu cầu'],
                    ['icon' => 'fa-handshake',      'label' => 'Xác nhận'],
                    ['icon' => 'fa-qrcode',        'label' => $order['status'] === 'completed' ? 'Hoàn thành' : ($order['status'] === 'disputed' ? 'Tranh chấp' : 'Giao nhận')],
                ];
                foreach ($steps as $i => $step):
                    $done    = ($i + 1) <= $cur_step;
                    $current = ($i + 1) === $cur_step;
                ?>
                <div class="text-center" style="z-index:1;flex:1;">
                    <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle"
                         style="width:30px;height:30px;
                                background:<?= $done ? 'var(--hcmue-blue)' : '#E5E7EB' ?>;
                                color:<?= $done ? '#fff' : '#9CA3AF' ?>;
                                font-size:0.75rem;
                                <?= $current ? 'box-shadow:0 0 0 4px rgba(0,63,138,0.2);' : '' ?>">
                        <i class="fas <?= $step['icon'] ?>"></i>
                    </div>
                    <div style="font-size:0.7rem;color:<?= $done ? 'var(--hcmue-blue)' : '#9CA3AF' ?>;font-weight:<?= $done ? '700' : '400' ?>;margin-top:4px;">
                        <?= $step['label'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Thông tin sách -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--hcmue-blue);"><i class="fas fa-book me-2"></i>Thông tin sách</h6>
        <div class="d-flex gap-3">
            <?php
                $img_src = (!empty($order['image_url']) && file_exists(FCPATH . $order['image_url']))
                           ? base_url($order['image_url'])
                           : base_url('assets/images/default_book.jpg');
            ?>
            <img src="<?= $img_src ?>" style="width:80px;height:100px;object-fit:cover;border-radius:10px;flex-shrink:0;"
                 onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">
            <div>
                <div class="fw-bold mb-1"><?= htmlspecialchars($order['post_title']) ?></div>
                <div class="text-muted" style="font-size:0.85rem;">
                    Đơn giá: <strong class="text-danger"><?= number_format($order['price'], 0, ',', '.') ?>đ</strong>
                    &nbsp;·&nbsp; Số lượng: <strong><?= $order['quantity'] ?> cuốn</strong>
                </div>
                <div class="fw-bold mt-1" style="color:var(--hcmue-blue);font-size:0.95rem;">
                    Tổng: <?= number_format($order['price'] * $order['quantity'], 0, ',', '.') ?>đ
                </div>
                <?php if ($order['note']): ?>
                    <div class="mt-2 p-2 rounded-3" style="background:#F8FAFC;font-size:0.82rem;color:#6B7280;">
                        <i class="fas fa-sticky-note me-1"></i>Ghi chú: <?= htmlspecialchars($order['note']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Thông tin 2 bên -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100">
                <div class="fw-bold mb-2" style="font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Người bán</div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:38px;height:38px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--hcmue-gold);font-weight:800;flex-shrink:0;">
                        <?= strtoupper(substr($order['seller_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($order['seller_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem;">@<?= $order['seller_username'] ?></div>
                    </div>
                </div>
                <?php if ($is_buyer && $order['seller_phone']): ?>
                    <div class="mt-2" style="font-size:0.82rem;">
                        <i class="fas fa-phone me-1" style="color:var(--hcmue-blue);"></i>
                        <a href="tel:<?= $order['seller_phone'] ?>" style="color:var(--hcmue-blue);font-weight:600;"><?= $order['seller_phone'] ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100">
                <div class="fw-bold mb-2" style="font-size:0.82rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Người mua</div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:38px;height:38px;background:var(--hcmue-gold);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--hcmue-blue);font-weight:800;flex-shrink:0;">
                        <?= strtoupper(substr($order['buyer_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($order['buyer_name']) ?></div>
                        <div class="text-muted" style="font-size:0.78rem;">@<?= $order['buyer_username'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lý do từ chối/tranh chấp -->
    <?php if ($order['reject_reason'] && in_array($order['status'], ['rejected','disputed','cancelled'])): ?>
    <div class="alert border-0 rounded-4 mb-4" style="background:#FEE2E2;color:#991B1B;">
        <i class="fas fa-info-circle me-2"></i>
        <strong><?= $order['status'] === 'disputed' ? 'Lý do tranh chấp' : 'Lý do từ chối' ?>:</strong>
        <?= htmlspecialchars($order['reject_reason']) ?>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="d-flex gap-2 flex-wrap">
        <!-- Nút Quay lại bảo toàn Tab đã xem -->
        <a href="<?= site_url('orders?tab=' . ($is_buyer ? 'buy' : 'sell')) ?>" class="btn btn-outline-secondary rounded-3">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>

        <!-- === CÁC NÚT HÀNH ĐỘNG DÀNH CHO NGƯỜI MUA === -->
        <?php if ($is_buyer): ?>
            <!-- Nút Nhắn tin -->
            <a href="<?= site_url('message/conversation/' . $order['seller_id']) ?>"
               class="btn btn-outline-primary rounded-3">
                <i class="fas fa-comment-dots me-1"></i>Nhắn tin người bán
            </a>

            <!-- Hành động Pending -->
            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?= site_url('orders/cancel/' . $order['id']) ?>" 
                   class="btn btn-outline-danger rounded-3 fw-semibold"
                   onclick="return confirm('Hủy yêu cầu mua này?');">
                    <i class="fas fa-times-circle me-1"></i>Hủy yêu cầu
                </a>
            <?php endif; ?>

            <!-- Hành động Confirmed / Processing / Delivering -->
            <?php if ($order['status'] === 'confirmed'): ?>
                <a href="<?= site_url('orders/checkout/' . $order['id']) ?>"
                   class="btn btn-primary-hcmue rounded-3 fw-bold">
                    <i class="fas fa-wallet me-1"></i>Thanh toán / Chọn phương thức
                </a>
            <?php elseif ($order['status'] === 'processing'): ?>
                <button class="btn btn-primary-hcmue rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#buyerQrModal">
                    <i class="fas fa-qrcode me-1"></i>Mã QR / OTP Giao nhận
                </button>
                <button class="btn btn-outline-danger rounded-3 fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#disputeModalDetail">
                    <i class="fas fa-exclamation-triangle me-1"></i>Khiếu nại / Yêu cầu hủy
                </button>
            <?php endif; ?>

            <!-- Hành động Completed -->
            <?php if ($order['status'] === 'completed'): ?>
                <a href="<?= site_url('orders/rate/' . $order['id']) ?>"
                   class="btn rounded-3 fw-bold text-dark" style="background:var(--hcmue-gold);">
                    <i class="fas fa-star me-1"></i>Đánh giá người bán
                </a>
                <button class="btn btn-outline-danger rounded-3 fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#reportSellerModal">
                    <i class="fas fa-flag me-1"></i>Báo cáo người bán
                </button>
            <?php endif; ?>

        <!-- === CÁC NÚT HÀNH ĐỘNG DÀNH CHO NGƯỜI BÁN === -->
        <?php else: ?>
            <!-- Nút Nhắn tin -->
            <a href="<?= site_url('message/conversation/' . $order['buyer_id']) ?>"
               class="btn btn-outline-primary rounded-3">
                <i class="fas fa-comment-dots me-1"></i>Nhắn tin người mua
            </a>

            <!-- Hành động Pending -->
            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?= site_url('orders/confirm/' . $order['id']) ?>"
                   class="btn btn-success rounded-3 fw-bold"
                   onclick="return confirm('Xác nhận đơn hàng này?');">
                    <i class="fas fa-check-circle me-1"></i>Xác nhận đơn
                </a>
                <button class="btn btn-danger rounded-3 fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#rejectModalDetail">
                    <i class="fas fa-times-circle me-1"></i>Từ chối bán
                </button>
            <?php endif; ?>

            <!-- Hành động Processing -->
            <?php if ($order['status'] === 'processing'): ?>
                <button class="btn btn-success rounded-3 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#qrScanModal">
                    <i class="fas fa-qrcode me-1"></i>Quét QR / Nhập OTP giao hàng
                </button>
                <a href="<?= site_url('orders/cancel/' . $order['id']) ?>"
                   class="btn btn-outline-danger rounded-3 fw-semibold"
                   onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng này chứ? (Tiền sẽ hoàn lại cho người mua nếu có)');">
                    <i class="fas fa-ban me-1"></i>Hủy đơn
                </a>
            <?php endif; ?>

            <!-- Hành động Confirmed -->
            <?php if ($order['status'] === 'confirmed'): ?>
                <a href="<?= site_url('orders/cancel/' . $order['id']) ?>"
                   class="btn btn-outline-danger rounded-3 fw-semibold"
                   onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng đã chốt này chứ?');">
                    <i class="fas fa-ban me-1"></i>Hủy đơn
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================== -->
<!-- === CÁC MODALS PHỤC VỤ HÀNH ĐỘNG TRONG CHI TIẾT === -->
<!-- ========================================== -->

<!-- Modal Từ chối bán (Cho Người bán) -->
<div class="modal fade" id="rejectModalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-light));border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-times-circle me-2"></i>Từ chối đơn hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('orders/reject/' . $order['id']) ?>" method="POST">
                  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="modal-body p-4">
                    <p class="text-muted" style="font-size:0.88rem;">Vui lòng nhập lý do từ chối bán tài liệu này (người mua sẽ nhận được thông báo hiển thị lý do này):</p>
                    <textarea class="form-control" name="reject_reason" rows="3" style="border-radius:12px; font-size:0.88rem;"
                              placeholder="Ví dụ: Tài liệu hiện đã hết hoặc không đủ số lượng để cung cấp..." required></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">Xác nhận từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Khiếu nại / Báo tranh chấp (Cho Người mua) -->
<div class="modal fade" id="disputeModalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#D93025,#E53935);border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Khiếu nại / Yêu cầu hủy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('orders/dispute/' . $order['id']) ?>" method="POST">
                  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="modal-body p-4">
                    <p class="text-muted" style="font-size:0.88rem;">Vui lòng mô tả chi tiết vấn đề gặp phải để Ban quản trị hỗ trợ giải quyết (Tiền của bạn vẫn đang được tạm giữ an toàn):</p>
                    <textarea class="form-control" name="dispute_reason" rows="3" style="border-radius:12px; font-size:0.88rem;"
                              placeholder="Ví dụ: Chưa nhận được sách như đã hẹn, hoặc tài liệu không đúng mô tả..." required></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">Gửi khiếu nại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Báo cáo người bán (Cho Người mua khi giao dịch đã xong) -->
<div class="modal fade" id="reportSellerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#D93025,#E53935);border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-flag me-2"></i>Báo cáo người bán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('orders/report_seller/' . $order['id']) ?>" method="POST">
                  <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <div class="modal-body p-4">
                    <p class="text-muted" style="font-size:0.88rem;">Vì giao dịch đã được xác nhận (quét QR) nên dòng tiền đã được chuyển giao và không thể hoàn lại tự động.</p>
                    <p class="text-muted" style="font-size:0.88rem;">Tuy nhiên, nếu bạn phát hiện hành vi lừa đảo tinh vi sau đó (VD: sách giả, rách trang bên trong mà lúc gặp không để ý), bạn có thể báo cáo tài khoản này để Admin xem xét xử lý (Ban tài khoản).</p>
                    <textarea class="form-control mt-2" name="report_reason" rows="3" style="border-radius:12px; font-size:0.88rem;"
                              placeholder="Mô tả rõ hành vi gian lận của người bán..." required></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">Gửi báo cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hiển thị QR cho Người Mua -->
<div class="modal fade" id="buyerQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-light));border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-qrcode me-2"></i>Mã xác nhận giao nhận</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted mb-3" style="font-size:0.9rem;">Khi gặp mặt người bán, hãy đưa mã QR này cho người bán quét để hoàn tất giao dịch.</p>
                <div class="d-flex justify-content-center mb-4">
                    <div id="qrcodeDisplay" style="padding:15px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);"></div>
                </div>
                <div class="mb-2 text-muted" style="font-size:0.85rem;">Hoặc đọc mã OTP 6 số này cho người bán:</div>
                <div class="fw-bold fs-2" style="color:var(--hcmue-gold); letter-spacing:8px;"><?= isset($order['otp_code']) ? htmlspecialchars($order['otp_code']) : '------' ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Quét QR / Nhập OTP cho Người Bán -->
<div class="modal fade" id="qrScanModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669,#10B981);border-radius:16px 16px 0 0;">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-qrcode me-2"></i>Xác thực giao hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeQrModal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <ul class="nav nav-pills nav-fill mb-3 rounded-3 p-1 bg-light" id="qrTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-3 fw-bold" id="scan-tab" data-bs-toggle="tab" data-bs-target="#scan" type="button" role="tab">Quét mã QR</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-3 fw-bold" id="otp-tab" data-bs-toggle="tab" data-bs-target="#otp" type="button" role="tab">Nhập mã OTP</button>
                    </li>
                </ul>
                <div class="tab-content" id="qrTabContent">
                    <div class="tab-pane fade show active" id="scan" role="tabpanel">
                        <div id="qr-reader" style="width:100%; border-radius:12px; overflow:hidden;" class="mb-3"></div>
                        <div class="text-muted" style="font-size:0.85rem;">Đưa camera vào mã QR trên màn hình của người mua.</div>
                    </div>
                    <div class="tab-pane fade" id="otp" role="tabpanel">
                        <div class="py-4">
                            <label class="form-label text-muted fw-bold mb-3">Nhập mã OTP 6 số từ người mua</label>
                            <input type="text" id="otpInput" class="form-control form-control-lg text-center fw-bold mb-4" style="font-size:2rem; letter-spacing:8px;" maxlength="6" placeholder="------">
                            <button class="btn btn-success rounded-3 fw-bold px-5 py-2 w-100" id="btnVerifyOtp">Xác thực OTP</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($is_buyer && $order['status'] === 'processing' && !empty($order['qr_token'])): ?>
        new QRCode(document.getElementById("qrcodeDisplay"), {
            text: "<?= htmlspecialchars($order['qr_token']) ?>",
            width: 200,
            height: 200,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
        <?php endif; ?>

        <?php if (!$is_buyer && $order['status'] === 'processing'): ?>
        let html5QrcodeScanner = null;

        const qrModal = document.getElementById('qrScanModal');
        qrModal.addEventListener('show.bs.modal', function () {
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 }, false);
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        });

        qrModal.addEventListener('hidden.bs.modal', function () {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => {
                    console.error("Failed to clear html5QrcodeScanner. ", error);
                });
                html5QrcodeScanner = null;
            }
        });

        let isVerifying = false;
        function verifyHandover(code) {
            if (isVerifying) return;
            isVerifying = true;
            
            // Tạm ẩn scanner để tránh quét liên tục
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }

            $.post('<?= site_url("orders/verify_handover") ?>', { 
                code: code,
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
            }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert('Thành công: ' + res.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + res.message);
                    isVerifying = false;
                    // Resume scanning
                    if (document.getElementById('scan-tab').classList.contains('active')) {
                         html5QrcodeScanner = new Html5QrcodeScanner(
                            "qr-reader", { fps: 10, qrbox: {width: 250, height: 250}, aspectRatio: 1.0 }, false);
                         html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                    }
                }
            }).fail(function() {
                alert('Lỗi kết nối máy chủ.');
                isVerifying = false;
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            verifyHandover(decodedText);
        }

        function onScanFailure(error) {
            // Ignore scan failures
        }

        document.getElementById('btnVerifyOtp').addEventListener('click', function() {
            const otp = document.getElementById('otpInput').value.trim();
            if (otp.length === 6) {
                verifyHandover(otp);
            } else {
                alert('Vui lòng nhập đủ 6 số OTP');
            }
        });
        <?php endif; ?>
    });
</script>


