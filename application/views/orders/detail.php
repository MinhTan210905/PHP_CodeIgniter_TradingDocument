<?php
$status_labels = [
    'pending'   => ['label' => 'Chờ xác nhận',   'color' => '#92400E', 'bg' => '#FEF3C7', 'icon' => 'fa-hourglass-half'],
    'confirmed' => ['label' => 'Đã xác nhận',     'color' => '#1E40AF', 'bg' => '#DBEAFE', 'icon' => 'fa-handshake'],
    'completed' => ['label' => 'Hoàn thành',       'color' => '#065F46', 'bg' => '#D1FAE5', 'icon' => 'fa-check-circle'],
    'disputed'  => ['label' => 'Tranh chấp',       'color' => '#991B1B', 'bg' => '#FEE2E2', 'icon' => 'fa-exclamation-triangle'],
    'rejected'  => ['label' => 'Đã từ chối',       'color' => '#6B7280', 'bg' => '#F3F4F6', 'icon' => 'fa-times-circle'],
    'cancelled' => ['label' => 'Đã hủy',           'color' => '#9CA3AF', 'bg' => '#F3F4F6', 'icon' => 'fa-ban'],
];
$sl       = $status_labels[$order['status']] ?? $status_labels['cancelled'];
$user_id  = $this->session->userdata('user_id');
$timeline = [
    'pending'   => 1,
    'confirmed' => 2,
    'completed' => 3,
    'disputed'  => 3,
    'rejected'  => 2,
    'cancelled' => 2,
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
                    ['icon' => 'fa-box-open',        'label' => $order['status'] === 'completed' ? 'Hoàn thành' : ($order['status'] === 'disputed' ? 'Tranh chấp' : 'Nhận hàng')],
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
                <span class="btn btn-outline-secondary rounded-3 fw-bold disabled">
                    <i class="fas fa-hourglass-half me-1"></i>Chờ người bán đi giao sách
                </span>
            <?php elseif ($order['status'] === 'delivering'): ?>
                <a href="<?= site_url('orders/received/' . $order['id']) ?>"
                   class="btn btn-success rounded-3 fw-bold"
                   onclick="return confirm('Xác nhận bạn đã thực sự nhận được tài liệu này từ người bán?');">
                    <i class="fas fa-check me-1"></i>Đã nhận hàng
                </a>
                <button class="btn btn-outline-danger rounded-3 fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#disputeModalDetail">
                    <i class="fas fa-exclamation-triangle me-1"></i>Chưa nhận được (Báo cáo)
                </button>
            <?php endif; ?>

            <!-- Hành động Completed -->
            <?php if ($order['status'] === 'completed'): ?>
                <a href="<?= site_url('orders/rate/' . $order['id']) ?>"
                   class="btn rounded-3 fw-bold text-dark" style="background:var(--hcmue-gold);">
                    <i class="fas fa-star me-1"></i>Đánh giá người bán
                </a>
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
                        data-bs-toggle="modal" data-bs-target="#deliveryProofModal">
                    <i class="fas fa-camera me-1"></i>Đã giao hàng (Gửi minh chứng)
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
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Báo cáo vấn đề</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('orders/dispute/' . $order['id']) ?>" method="POST">
                <div class="modal-body p-4">
                    <p class="text-muted" style="font-size:0.88rem;">Vui lòng mô tả chi tiết vấn đề gặp phải để Ban quản trị hỗ trợ giải quyết:</p>
                    <textarea class="form-control" name="dispute_reason" rows="3" style="border-radius:12px; font-size:0.88rem;"
                              placeholder="Ví dụ: Chưa nhận được sách như đã hẹn, hoặc tài liệu không đúng mô tả..." required></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">Gửi phản hồi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Đã Giao Hàng (Tải lên minh chứng - Cho Người bán) -->
<div class="modal fade" id="deliveryProofModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#059669,#10B981);border-radius:16px 16px 0 0; width: 100%;">
                <h5 class="modal-title text-white fw-bold mb-0"><i class="fas fa-camera me-2"></i>Minh chứng giao hàng</h5>
                <div class="d-flex align-items-center gap-2">
                    <span id="countdownBadge" class="badge bg-danger d-flex align-items-center gap-1 px-2.5 py-1.5 fs-7 fw-bold shadow-sm" style="font-family: monospace;">
                        <i class="fas fa-hourglass-half animate-pulse"></i><span id="timerText">05:00</span>
                    </span>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" style="margin:0;"></button>
                </div>
            </div>
            <form action="<?= site_url('orders/delivered/' . $order['id']) ?>" method="POST" id="proofForm">
                <!-- Ẩn field chứa dữ liệu base64 của ảnh chụp -->
                <input type="hidden" name="delivery_proof_base64" id="deliveryProofBase64" required>
                
                <div class="modal-body p-3 text-center">
                    <p class="text-muted mb-2" style="font-size:0.85rem;">Vui lòng chụp ảnh minh chứng tại thời điểm giao hàng. Hệ thống sẽ tự động ghi nhận địa chỉ và thời gian thực vào ảnh.</p>
                    
                    <!-- Bảng trạng thái xác thực máy ảnh và vị trí -->
                    <div id="cameraStatus" class="alert alert-warning py-2 px-3 mb-3 rounded-3 text-center" style="font-size:0.85rem; font-weight: 500;">
                        <i class="fas fa-spinner fa-spin me-2"></i>Đang xác thực Máy ảnh và Vị trí GPS của bạn...
                    </div>
                    
                    <div id="cameraContainer" class="position-relative bg-dark rounded-4 overflow-hidden mb-3 mx-auto" style="aspect-ratio: 3/4; max-width: 300px; display:flex; justify-content:center; align-items:center;">
                        <video id="cameraVideo" autoplay playsinline style="width:100%; height:100%; object-fit:cover;"></video>
                        <canvas id="cameraCanvas" style="display:none;"></canvas>
                        <img id="photoResult" style="display:none; width:100%; height:100%; object-fit:cover;">
                        
                        <!-- Lớp phủ (Overlay) thông tin trên giao diện chụp -->
                        <div id="cameraOverlay" class="position-absolute text-start text-white p-2" style="bottom:10px; left:10px; right:10px; background:rgba(0,0,0,0.5); border-radius:8px; font-size:0.7rem; pointer-events: none;">
                            <div id="overlayTime" class="fw-bold mb-1"><i class="fas fa-clock me-1"></i>Đang lấy thời gian...</div>
                            <div id="overlayLoc"><i class="fas fa-map-marker-alt me-1"></i>Đang lấy vị trí...</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" id="btnCapture" class="btn btn-primary-hcmue rounded-circle shadow disabled" style="width:64px; height:64px;" disabled>
                            <i class="fas fa-camera fs-3"></i>
                        </button>
                        <button type="button" id="btnRetake" class="btn btn-secondary rounded-circle shadow" style="width:64px; height:64px; display:none;">
                            <i class="fas fa-redo fs-3"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" id="btnSubmitProof" class="btn btn-success rounded-3 fw-bold px-4 disabled">Xác nhận Đã giao</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById('deliveryProofModal');
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const photoResult = document.getElementById('photoResult');
    const btnCapture = document.getElementById('btnCapture');
    const btnRetake = document.getElementById('btnRetake');
    const btnSubmit = document.getElementById('btnSubmitProof');
    const base64Input = document.getElementById('deliveryProofBase64');
    
    const overlayTime = document.getElementById('overlayTime');
    const overlayLoc = document.getElementById('overlayLoc');
    
    let stream = null;
    let locationString = "Đang xác định vị trí...";
    let timeInterval = null;
    let countdownInterval = null;
    let remainingTime = 300; // 5 phút (300 giây)
    let hasLocation = false;
    let hasCamera = false;

    // Định nghĩa các cơ sở HCMUE để nhận diện tức thì trong phạm vi 300m
    const CS1 = { lat: 10.759928, lon: 106.678736, name: "ĐH Sư phạm TPHCM - CS An Dương Vương" };
    const CS2 = { lat: 10.789182, lon: 106.674895, name: "ĐH Sư phạm TPHCM - CS Lê Văn Sỹ" };

    // Tính khoảng cách Haversine giữa 2 tọa độ (mét)
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // bán kính Trái Đất tính theo mét
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c;
    }

    // Cập nhật trạng thái kích hoạt của Nút chụp hình
    function updateValidationStatus() {
        const cameraStatus = document.getElementById('cameraStatus');
        
        if (remainingTime <= 0) {
            cameraStatus.className = "alert alert-danger py-2 px-3 mb-3 rounded-3 text-center";
            cameraStatus.innerHTML = `<i class="fas fa-times-circle me-2"></i>Đã hết thời gian chụp minh chứng. Vui lòng đóng và mở lại!`;
            btnCapture.disabled = true;
            btnCapture.classList.add('disabled');
            return;
        }

        if (hasLocation && hasCamera) {
            cameraStatus.className = "alert alert-success py-2 px-3 mb-3 rounded-3 text-center";
            cameraStatus.innerHTML = `<i class="fas fa-check-circle me-2"></i>Đã xác thực thiết bị! Bạn có thể chụp ảnh minh chứng ngay.`;
            btnCapture.disabled = false;
            btnCapture.classList.remove('disabled');
        } else {
            btnCapture.disabled = true;
            btnCapture.classList.add('disabled');

            let statusHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Đang xác minh: `;
            let pending = [];
            if (!hasCamera) pending.push("Máy ảnh <i class='fas fa-camera ms-1'></i>");
            if (!hasLocation) pending.push("Vị trí GPS <i class='fas fa-map-marker-alt ms-1'></i>");
            
            statusHTML += pending.join(" và ") + "...";
            cameraStatus.className = "alert alert-warning py-2 px-3 mb-3 rounded-3 text-center";
            cameraStatus.innerHTML = statusHTML;
        }
    }

    // Xử lý khi hết thời gian 5 phút
    function handleTimeout() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        const cameraStatus = document.getElementById('cameraStatus');
        cameraStatus.className = "alert alert-danger py-2 px-3 mb-3 rounded-3 text-center";
        cameraStatus.innerHTML = `<i class="fas fa-times-circle me-2"></i>Hết thời gian phiên chụp minh chứng (5 phút). Vui lòng đóng và mở lại.`;
        
        video.style.display = 'none';
        photoResult.style.display = 'none';
        document.getElementById('cameraOverlay').style.display = 'none';
        base64Input.value = '';
        
        btnCapture.disabled = true;
        btnCapture.classList.add('disabled');
        btnCapture.style.display = 'inline-block';
        btnRetake.style.display = 'none';
        btnSubmit.classList.add('disabled');
    }

    // Dịch tọa độ thành địa chỉ chi tiết qua API OpenStreetMap Nominatim
    function fetchAddressFromCoords(lat, lon) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 3500); // 3.5s timeout

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, {
            signal: controller.signal,
            headers: { 'Accept-Language': 'vi,en;q=0.9' }
        })
        .then(res => res.json())
        .then(data => {
            clearTimeout(timeoutId);
            const addr = data.address;
            if (addr) {
                let parts = [];
                // 1. Tên địa điểm nổi bật nếu có
                const place = addr.amenity || addr.building || addr.shop || addr.tourism || addr.historic || addr.school || addr.university || addr.mall || addr.hotel;
                if (place) parts.push(place);
                
                // 2. Số nhà + Tên đường
                let street = "";
                if (addr.house_number) street += addr.house_number + " ";
                if (addr.road) street += addr.road;
                if (street) parts.push(street);
                
                // 3. Phường / Xã
                const ward = addr.quarter || addr.suburb || addr.neighbourhood || addr.village || addr.hamlet;
                if (ward && !parts.includes(ward)) parts.push(ward);
                
                // 4. Quận / Huyện
                const district = addr.city_district || addr.district || addr.county;
                if (district && !parts.includes(district)) parts.push(district);
                
                // 5. Tỉnh / Thành phố
                const city = addr.city || addr.state || addr.province;
                if (city && !parts.includes(city)) parts.push(city);
                
                let cleanAddr = parts.filter(p => p && typeof p === 'string').join(', ');
                cleanAddr = cleanAddr.replace(', Việt Nam', ''); // Bỏ quốc gia cho ngắn gọn
                
                locationString = cleanAddr || `GPS: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
            } else {
                locationString = `GPS: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
            }
            overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
            
            hasLocation = true;
            updateValidationStatus();
        })
        .catch(() => {
            clearTimeout(timeoutId);
            locationString = `GPS: ${lat.toFixed(5)}, ${lon.toFixed(5)}`;
            overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
            
            hasLocation = true;
            updateValidationStatus();
        });
    }

    // Xử lý khi có tọa độ latitude & longitude
    function handleLocationCoords(lat, lon) {
        const distCS1 = getDistance(lat, lon, CS1.lat, CS1.lon);
        const distCS2 = getDistance(lat, lon, CS2.lat, CS2.lon);

        if (distCS1 <= 300) {
            locationString = CS1.name;
            overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
            hasLocation = true;
            updateValidationStatus();
        } else if (distCS2 <= 300) {
            locationString = CS2.name;
            overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
            hasLocation = true;
            updateValidationStatus();
        } else {
            fetchAddressFromCoords(lat, lon);
        }
    }

    // Vẽ watermark thông tin giao hàng chuyên nghiệp, tự động xuống dòng và cân chỉnh kích thước
    function drawWatermark(ctx, canvasWidth, canvasHeight, timeStr, locStr) {
        const fontSize = Math.max(13, Math.round(canvasWidth * 0.028));
        ctx.font = "bold " + fontSize + "px Arial";
        
        const margin = Math.round(canvasWidth * 0.03);
        const padding = Math.round(canvasWidth * 0.02);
        const maxWidth = canvasWidth - (margin * 2) - (padding * 2);
        
        // Cắt chuỗi địa chỉ thành các dòng nhỏ phù hợp với bề rộng khung ảnh
        const words = locStr.split(" ");
        let lines = [];
        let currentLine = "Vị trí: " + words[0];
        
        for (let i = 1; i < words.length; i++) {
            let testLine = currentLine + " " + words[i];
            let metrics = ctx.measureText(testLine);
            if (metrics.width > maxWidth) {
                lines.push(currentLine);
                currentLine = words[i];
            } else {
                currentLine = testLine;
            }
        }
        lines.push(currentLine);
        
        const timeLine = "Thời gian: " + timeStr;
        const lineHeight = fontSize * 1.4;
        const totalLines = 1 + lines.length;
        const boxHeight = (totalLines * lineHeight) + (padding * 2);
        const boxY = canvasHeight - margin - boxHeight;
        
        // Vẽ hình chữ nhật nền đen mờ bo viền nhẹ
        ctx.fillStyle = "rgba(0, 0, 0, 0.65)";
        ctx.fillRect(margin, boxY, canvasWidth - (margin * 2), boxHeight);
        
        // Vẽ chữ
        ctx.fillStyle = "white";
        ctx.fillText(timeLine, margin + padding, boxY + padding + fontSize);
        
        for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], margin + padding, boxY + padding + fontSize + ((i + 1) * lineHeight));
        }
    }

    // Khi bật Modal
    modal.addEventListener('show.bs.modal', function () {
        // Cập nhật thời gian liên tục
        timeInterval = setInterval(() => {
            const now = new Date();
            overlayTime.innerHTML = `<i class="fas fa-clock me-1"></i>${now.toLocaleDateString('vi-VN')} ${now.toLocaleTimeString('vi-VN')}`;
        }, 1000);

        // Khởi động đếm ngược 5 phút
        remainingTime = 300;
        document.getElementById('timerText').innerText = "05:00";
        document.getElementById('countdownBadge').className = "badge bg-danger d-flex align-items-center gap-1 px-2.5 py-1.5 fs-7 fw-bold shadow-sm";
        
        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            remainingTime--;
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('timerText').innerText = "00:00";
                handleTimeout();
            } else {
                const m = Math.floor(remainingTime / 60).toString().padStart(2, '0');
                const s = (remainingTime % 60).toString().padStart(2, '0');
                document.getElementById('timerText').innerText = `${m}:${s}`;
                
                // Hiệu ứng cảnh báo khi còn dưới 1 phút (nhấp nháy nhanh badge đỏ)
                if (remainingTime <= 60) {
                    document.getElementById('countdownBadge').classList.add('animate-pulse');
                }
            }
        }, 1000);

        hasLocation = false;
        hasCamera = false;
        updateValidationStatus();

        // Hàm dự phòng lấy vị trí qua IP (Khi không dùng được GPS thiết bị hoặc không chạy qua HTTPS/localhost)
        const fallbackToIPLocation = () => {
            fetch('https://get.geojs.io/v1/ip/geo.json')
                .then(res => res.json())
                .then(data => {
                    const lat = parseFloat(data.latitude);
                    const lon = parseFloat(data.longitude);
                    if (!isNaN(lat) && !isNaN(lon)) {
                        handleLocationCoords(lat, lon);
                    } else {
                        let city = data.city ? data.city : "Không xác định";
                        locationString = `Khu vực: ${city} (Vị trí ước tính qua IP)`;
                        overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
                        hasLocation = true;
                        updateValidationStatus();
                    }
                })
                .catch(() => {
                    locationString = "Vị trí không xác định (Lỗi mạng)";
                    overlayLoc.innerHTML = `<i class="fas fa-map-marker-alt me-1"></i>${locationString}`;
                    hasLocation = false;
                    updateValidationStatus();
                });
        };

        // Lấy tọa độ (Ưu tiên GPS thiết bị có độ chính xác cao)
        if (navigator.geolocation && (window.isSecureContext || location.hostname === 'localhost')) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    handleLocationCoords(pos.coords.latitude, pos.coords.longitude);
                },
                err => {
                    if (err.code === err.PERMISSION_DENIED) {
                        const cameraStatus = document.getElementById('cameraStatus');
                        cameraStatus.className = "alert alert-danger py-2 px-3 mb-3 rounded-3 text-center";
                        cameraStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Lỗi định vị: Vui lòng cho phép quyền Vị trí (GPS) trong cài đặt để tiếp tục minh chứng.`;
                        hasLocation = false;
                        updateValidationStatus();
                    } else {
                        fallbackToIPLocation();
                    }
                },
                { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
            );
        } else {
            fallbackToIPLocation();
        }

        // Bật Camera (ưu tiên camera sau trên điện thoại di động)
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(s => {
                stream = s;
                video.srcObject = stream;
                video.style.display = 'block';
                photoResult.style.display = 'none';
                btnCapture.style.display = 'inline-block';
                btnRetake.style.display = 'none';
                btnSubmit.classList.add('disabled');
                
                hasCamera = true;
                updateValidationStatus();
            })
            .catch(err => {
                const cameraStatus = document.getElementById('cameraStatus');
                cameraStatus.className = "alert alert-danger py-2 px-3 mb-3 rounded-3 text-center";
                cameraStatus.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Lỗi camera: ${err.message}. Vui lòng cấp quyền truy cập Camera để tiếp tục.`;
                hasCamera = false;
                updateValidationStatus();
            });
    });

    // Khi tắt Modal
    modal.addEventListener('hidden.bs.modal', function () {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        clearInterval(timeInterval);
        clearInterval(countdownInterval);
        
        // Khôi phục trạng thái ban đầu của overlay và kết quả
        video.style.display = 'block';
        photoResult.style.display = 'none';
        document.getElementById('cameraOverlay').style.display = 'block';
        base64Input.value = '';
        btnSubmit.classList.add('disabled');
    });

    // Chụp ảnh và ghép Watermark
    btnCapture.addEventListener('click', function() {
        if (!stream || !hasCamera || !hasLocation) return;
        
        // Thiết lập kích thước canvas bằng kích thước video thực
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        
        // Vẽ frame video hiện tại lên canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Vẽ overlay text lên canvas dạng hộp watermark Shopee chuyên nghiệp
        const now = new Date();
        const timeStr = `${now.toLocaleDateString('vi-VN')} ${now.toLocaleTimeString('vi-VN')}`;
        const locStr = (typeof locationString !== 'undefined' ? locationString : "Vị trí không xác định");
        
        drawWatermark(ctx, canvas.width, canvas.height, timeStr, locStr);
        
        // Chuyển canvas thành Base64
        const dataURL = canvas.toDataURL('image/jpeg', 0.82);
        base64Input.value = dataURL;
        
        // Đổi view sang ảnh kết quả
        video.style.display = 'none';
        photoResult.src = dataURL;
        photoResult.style.display = 'block';
        
        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-block';
        btnSubmit.classList.remove('disabled');
    });

    // Chụp lại
    btnRetake.addEventListener('click', function() {
        if (remainingTime <= 0) return; // Nếu hết giờ thì không cho chụp lại
        
        video.style.display = 'block';
        photoResult.style.display = 'none';
        base64Input.value = '';
        
        btnCapture.style.display = 'inline-block';
        btnRetake.style.display = 'none';
        btnSubmit.classList.add('disabled');
    });

    // Gửi Form minh chứng
    btnSubmit.addEventListener('click', function() {
        if (base64Input.value && remainingTime > 0) {
            document.getElementById('proofForm').submit();
        }
    });
});
</script>

<!-- Hiển thị Minh chứng giao hàng -->
<?php if (!empty($order['delivery_proof'])): ?>
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4 mt-4">
        <h6 class="fw-bold mb-3" style="color:var(--hcmue-blue);"><i class="fas fa-camera-retro me-2"></i>Minh chứng giao hàng từ người bán</h6>
        <div class="text-center">
            <img src="<?= base_url($order['delivery_proof']) ?>" alt="Minh chứng giao hàng" class="img-fluid rounded-3 shadow-sm" style="max-height: 400px; object-fit: contain;">
            <div class="mt-2 text-muted" style="font-size:0.8rem;">Ảnh chụp minh chứng giao hàng từ người bán.</div>
        </div>
    </div>
<?php endif; ?>


