<?php
// Helper labels
$status_labels = [
    'pending'    => ['label' => 'Chờ xác nhận',     'class' => 'badge-pending',   'icon' => 'fa-hourglass-half'],
    'confirmed'  => ['label' => 'Đã xác nhận',      'class' => 'badge-confirmed', 'icon' => 'fa-handshake'],
    'processing' => ['label' => 'Đang xử lý',       'class' => 'badge-confirmed', 'icon' => 'fa-truck-loading'],
    'delivering' => ['label' => 'Đang giao',        'class' => 'badge-confirmed', 'icon' => 'fa-motorcycle'],
    'completed'  => ['label' => 'Hoàn thành',       'class' => 'badge-completed', 'icon' => 'fa-check-circle'],
    'disputed'   => ['label' => 'Tranh chấp',       'class' => 'badge-disputed',  'icon' => 'fa-exclamation-triangle'],
    'rejected'   => ['label' => 'Đã từ chối',       'class' => 'badge-rejected',  'icon' => 'fa-times-circle'],
    'cancelled'  => ['label' => 'Đã hủy',           'class' => 'badge-cancelled', 'icon' => 'fa-ban'],
];
$active_tab = $active_tab ?? 'buy';
?>
<style>
.order-tab-btn { border:none;background:transparent;padding:10px 20px;font-weight:600;font-size:0.9rem;color:var(--text-muted);border-bottom:2.5px solid transparent;transition:all 0.2s;cursor:pointer; }
.order-tab-btn.active { color:var(--hcmue-blue);border-bottom-color:var(--hcmue-blue); }
.order-card { border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,63,138,0.07);background:#fff;margin-bottom:14px;transition:box-shadow 0.2s; }
.order-card:hover { box-shadow:0 6px 24px rgba(0,63,138,0.13); }
.badge-pending   { background:#FEF3C7;color:#92400E; }
.badge-confirmed { background:#DBEAFE;color:#1E40AF; }
.badge-completed { background:#D1FAE5;color:#065F46; }
.badge-disputed  { background:#FEE2E2;color:#991B1B; }
.badge-rejected  { background:#F3F4F6;color:#6B7280; }
.badge-cancelled { background:#F3F4F6;color:#9CA3AF; }
.order-status-badge { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:700; }
.order-book-img { width:64px;height:80px;object-fit:cover;border-radius:8px;flex-shrink:0; }
</style>

<div class="container py-4" style="max-width:860px;">
    <h2 class="section-title mb-0">
        <i class="fas fa-shopping-bag"></i> Đơn hàng của tôi
    </h2>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mt-3">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-hcmue alert-dismissible fade show mt-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="d-flex border-bottom mb-4 mt-3">
        <button class="order-tab-btn <?= $active_tab === 'buy' ? 'active' : '' ?>" onclick="switchTab('buy')">
            <i class="fas fa-shopping-cart me-1"></i> Đơn Mua
            <?php if(count($orders_as_buyer)): ?>
                <span class="ms-1" style="background:#E8F0FD;color:var(--hcmue-blue);border-radius:20px;padding:1px 8px;font-size:0.75rem;"><?= count($orders_as_buyer) ?></span>
            <?php endif; ?>
        </button>
        <button class="order-tab-btn <?= $active_tab === 'sell' ? 'active' : '' ?>" onclick="switchTab('sell')">
            <i class="fas fa-store me-1"></i> Đơn Bán
            <?php if($pending_count): ?>
                <span class="ms-1" style="background:#FEF3C7;color:#92400E;border-radius:20px;padding:1px 8px;font-size:0.75rem;"><?= $pending_count ?> chờ</span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Tab Mua -->
    <div id="tab-buy" class="<?= $active_tab !== 'buy' ? 'd-none' : '' ?>">
        <div class="d-flex mb-3">
            <button class="btn btn-sm btn-primary-hcmue rounded-pill me-2 buy-sub-btn active" onclick="switchSubTab('buy', 'current')">Đơn hiện tại</button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill buy-sub-btn" onclick="switchSubTab('buy', 'past')">Đã mua / Đã hủy</button>
        </div>

        <?php if (empty($orders_as_buyer)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-shopping-cart" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="mt-3">Bạn chưa có đơn mua nào.</p>
                <a href="<?= site_url('trade') ?>" class="btn btn-primary-hcmue px-4">Khám phá sách ngay</a>
            </div>
        <?php else: ?>
            <div id="buy-empty" class="text-center py-5 text-muted d-none">
                <i class="fas fa-box-open" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="mt-3">Không có đơn hàng nào trong mục này.</p>
            </div>
            <?php foreach($orders_as_buyer as $o): ?>
            <?php 
                $sl = $status_labels[$o['status']] ?? $status_labels['cancelled']; 
                $phase = in_array($o['status'], ['pending', 'confirmed', 'processing', 'delivering', 'disputed']) ? 'current' : 'past';
            ?>
            <div class="order-card p-3 buy-card" data-phase="<?= $phase ?>">
                <div class="d-flex gap-3 align-items-start">
                    <!-- Ảnh -->
                    <?php
                        $img_src = (!empty($o['image_url']) && file_exists(FCPATH . $o['image_url']))
                                   ? base_url($o['image_url'])
                                   : base_url('assets/images/default_book.jpg');
                    ?>
                    <img src="<?= $img_src ?>" class="order-book-img" alt="<?= htmlspecialchars($o['post_title']) ?>"
                         onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">

                    <!-- Info -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                            <span class="fw-bold" style="font-size:0.92rem;"><?= htmlspecialchars($o['post_title']) ?></span>
                            <span class="order-status-badge <?= $sl['class'] ?>">
                                <i class="fas <?= $sl['icon'] ?>"></i> <?= $sl['label'] ?>
                            </span>
                        </div>
                        <div class="text-muted mb-2" style="font-size:0.8rem;">
                            <i class="fas fa-user me-1"></i>Người bán: <strong><?= htmlspecialchars($o['seller_name']) ?></strong>
                            &nbsp;·&nbsp;
                            <i class="fas fa-book me-1"></i><?= $o['quantity'] ?> cuốn
                            &nbsp;·&nbsp;
                            <i class="fas fa-tag me-1"></i><?= number_format($o['price'] * $o['quantity'], 0, ',', '.') ?>đ
                        </div>
                        <?php if ($o['note']): ?>
                            <div class="text-muted" style="font-size:0.78rem;"><i class="fas fa-sticky-note me-1"></i><?= htmlspecialchars($o['note']) ?></div>
                        <?php endif; ?>
                        <?php if ($o['reject_reason'] && in_array($o['status'], ['rejected','disputed'])): ?>
                            <div class="text-danger mt-1" style="font-size:0.78rem;"><i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($o['reject_reason']) ?></div>
                        <?php endif; ?>

                        <!-- Actions người mua -->
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            <a href="<?= site_url('orders/detail/' . $o['id']) ?>"
                               class="btn btn-sm btn-outline-secondary rounded-3" style="font-size:0.78rem;">
                                <i class="fas fa-eye me-1"></i>Chi tiết
                            </a>
                            <?php if ($o['status'] === 'confirmed'): ?>
                                <a href="<?= site_url('orders/checkout/' . $o['id']) ?>"
                                   class="btn btn-sm btn-primary-hcmue rounded-3 fw-bold" style="font-size:0.78rem;">
                                    <i class="fas fa-wallet me-1"></i>Thanh toán / Chọn PTTT
                                </a>
                            <?php elseif ($o['status'] === 'processing'): ?>
                                <span class="btn btn-sm btn-outline-secondary rounded-3 disabled" style="font-size:0.78rem;">
                                    <i class="fas fa-hourglass-half me-1"></i>Chờ giao hàng
                                </span>
                            <?php elseif ($o['status'] === 'delivering'): ?>
                                <a href="<?= site_url('orders/received/' . $o['id']) ?>"
                                   class="btn btn-sm btn-success rounded-3 fw-bold" style="font-size:0.78rem;"
                                   onclick="return confirm('Xác nhận đã nhận sách?');">
                                    <i class="fas fa-check me-1"></i>Đã nhận hàng
                                </a>
                                <button class="btn btn-sm btn-outline-danger rounded-3" style="font-size:0.78rem;"
                                        data-bs-toggle="modal" data-bs-target="#disputeModal<?= $o['id'] ?>">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Chưa nhận được
                                </button>
                            <?php endif; ?>
                            <?php if ($o['status'] === 'completed'): ?>
                                <a href="<?= site_url('orders/rate/' . $o['id']) ?>"
                                   class="btn btn-sm rounded-3 fw-bold" style="font-size:0.78rem;background:var(--hcmue-gold);color:var(--hcmue-blue);">
                                    <i class="fas fa-star me-1"></i>Đánh giá
                                </a>
                            <?php endif; ?>
                            <?php if ($o['status'] === 'pending'): ?>
                                <a href="<?= site_url('orders/cancel/' . $o['id']) ?>"
                                   class="btn btn-sm btn-outline-danger rounded-3" style="font-size:0.78rem;"
                                   onclick="return confirm('Hủy yêu cầu mua này?');">
                                    <i class="fas fa-times me-1"></i>Hủy yêu cầu
                                </a>
                            <?php endif; ?>
                            <?php 
                            $seller_avatar = '';
                            if (!empty($o['seller_avatar']) && file_exists(FCPATH . $o['seller_avatar'])) {
                                $seller_avatar = base_url($o['seller_avatar']);
                            }
                            ?>
                            <a href="javascript:void(0)" onclick="window.openDirectChat(<?= $o['seller_id'] ?>, '<?= htmlspecialchars($o['seller_name'], ENT_QUOTES) ?>', '<?= $seller_avatar ?>')"
                               class="btn btn-sm btn-primary-hcmue rounded-3" style="font-size:0.78rem;">
                                <i class="fas fa-comment me-1"></i>Nhắn tin
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Tranh chấp -->
            <div class="modal fade" id="disputeModal<?= $o['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-header" style="background:linear-gradient(135deg,#D93025,#E53935);border-radius:16px 16px 0 0;">
                            <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Báo vấn đề</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="<?= site_url('orders/dispute/' . $o['id']) ?>" method="POST">
                            <div class="modal-body p-4">
                                <p class="text-muted" style="font-size:0.88rem;">Mô tả vấn đề bạn gặp phải với đơn hàng này:</p>
                                <textarea class="form-control form-control-hcmue" name="dispute_reason" rows="3"
                                          placeholder="VD: Chưa nhận được sách, sách không đúng mô tả..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger rounded-3 fw-bold">Gửi báo cáo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tab Bán -->
    <div id="tab-sell" class="<?= $active_tab !== 'sell' ? 'd-none' : '' ?>">
        <div class="d-flex mb-3">
            <button class="btn btn-sm btn-primary-hcmue rounded-pill me-2 sell-sub-btn active" onclick="switchSubTab('sell', 'current')">Đang giao dịch</button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill sell-sub-btn" onclick="switchSubTab('sell', 'past')">Đã hoàn thành / Hủy</button>
        </div>

        <?php if (empty($orders_as_seller)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-store" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="mt-3">Bạn chưa có đơn bán nào.</p>
            </div>
        <?php else: ?>
            <div id="sell-empty" class="text-center py-5 text-muted d-none">
                <i class="fas fa-box-open" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="mt-3">Không có đơn hàng nào trong mục này.</p>
            </div>
            <?php foreach($orders_as_seller as $o): ?>
            <?php 
                $sl = $status_labels[$o['status']] ?? $status_labels['cancelled']; 
                $phase = in_array($o['status'], ['pending', 'confirmed', 'processing', 'delivering', 'disputed']) ? 'current' : 'past';
            ?>
            <div class="order-card p-3 sell-card" data-phase="<?= $phase ?>">
                <div class="d-flex gap-3 align-items-start">
                    <?php
                        $img_src = (!empty($o['image_url']) && file_exists(FCPATH . $o['image_url']))
                                   ? base_url($o['image_url'])
                                   : base_url('assets/images/default_book.jpg');
                    ?>
                    <img src="<?= $img_src ?>" class="order-book-img" alt="<?= htmlspecialchars($o['post_title']) ?>"
                         onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
                            <span class="fw-bold" style="font-size:0.92rem;"><?= htmlspecialchars($o['post_title']) ?></span>
                            <span class="order-status-badge <?= $sl['class'] ?>">
                                <i class="fas <?= $sl['icon'] ?>"></i> <?= $sl['label'] ?>
                            </span>
                        </div>
                        <div class="text-muted mb-2" style="font-size:0.8rem;">
                            <i class="fas fa-user me-1"></i>Người mua: <strong><?= htmlspecialchars($o['buyer_name']) ?></strong>
                            &nbsp;·&nbsp;
                            <i class="fas fa-book me-1"></i><?= $o['quantity'] ?> cuốn
                            &nbsp;·&nbsp;
                            <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>
                        </div>
                        <?php if ($o['note']): ?>
                            <div class="mb-2 p-2 rounded-3" style="background:#F8FAFC;font-size:0.8rem;">
                                <i class="fas fa-comment-dots me-1 text-muted"></i><?= htmlspecialchars($o['note']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Actions người bán -->
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            <a href="<?= site_url('orders/detail/' . $o['id']) ?>"
                               class="btn btn-sm btn-outline-secondary rounded-3" style="font-size:0.78rem;">
                                <i class="fas fa-eye me-1"></i>Chi tiết
                            </a>
                            <?php if ($o['status'] === 'pending'): ?>
                                <a href="<?= site_url('orders/confirm/' . $o['id']) ?>"
                                   class="btn btn-sm btn-success rounded-3 fw-bold" style="font-size:0.78rem;"
                                   onclick="return confirm('Xác nhận đơn hàng này?');">
                                    <i class="fas fa-check me-1"></i>Xác nhận
                                </a>
                                <button class="btn btn-sm btn-outline-danger rounded-3" style="font-size:0.78rem;"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal<?= $o['id'] ?>">
                                    <i class="fas fa-times me-1"></i>Từ chối
                                </button>
                            <?php endif; ?>
                            <?php if ($o['status'] === 'confirmed'): ?>
                                <span class="text-warning fw-bold d-inline-flex align-items-center" style="font-size:0.78rem;background:#FFFBEB;padding:4px 10px;border-radius:6px;border:1px solid #FEF3C7;">
                                    <i class="fas fa-hourglass-half me-1"></i>Chờ người mua thanh toán/chọn PTTT
                                </span>
                                <a href="<?= site_url('orders/cancel/' . $o['id']) ?>"
                                   class="btn btn-sm btn-outline-secondary rounded-3 ms-2" style="font-size:0.78rem;"
                                   onclick="return confirm('Hủy đơn hàng này?');">
                                    <i class="fas fa-ban me-1"></i>Hủy
                                </a>
                            <?php elseif ($o['status'] === 'processing'): ?>
                                <a href="<?= site_url('orders/detail/' . $o['id']) ?>"
                                   class="btn btn-sm btn-success rounded-3 fw-bold" style="font-size:0.78rem;">
                                    <i class="fas fa-qrcode me-1"></i>Tiến hành giao (Quét QR)
                                </a>
                                <a href="<?= site_url('orders/cancel/' . $o['id']) ?>"
                                   class="btn btn-sm btn-outline-secondary rounded-3 ms-2" style="font-size:0.78rem;"
                                   onclick="return confirm('Hủy đơn hàng này?');">
                                    <i class="fas fa-ban me-1"></i>Hủy
                                </a>
                            <?php elseif ($o['status'] === 'delivering'): ?>
                                <span class="text-success fw-bold d-inline-flex align-items-center" style="font-size:0.78rem;background:#F0FDF4;padding:4px 10px;border-radius:6px;border:1px solid #D1FAE5;">
                                    <i class="fas fa-box me-1"></i>Chờ người mua nhận hàng
                                </span>
                            <?php endif; ?>
                            <?php 
                            $buyer_avatar = '';
                            if (!empty($o['buyer_avatar']) && file_exists(FCPATH . $o['buyer_avatar'])) {
                                $buyer_avatar = base_url($o['buyer_avatar']);
                            }
                            ?>
                            <a href="javascript:void(0)" onclick="window.openDirectChat(<?= $o['buyer_id'] ?>, '<?= htmlspecialchars($o['buyer_name'], ENT_QUOTES) ?>', '<?= $buyer_avatar ?>')"
                               class="btn btn-sm btn-primary-hcmue rounded-3" style="font-size:0.78rem;">
                                <i class="fas fa-comment me-1"></i>Nhắn tin
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Từ chối -->
            <div class="modal fade" id="rejectModal<?= $o['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow">
                        <div class="modal-header" style="background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-light));border-radius:16px 16px 0 0;">
                            <h5 class="modal-title text-white fw-bold"><i class="fas fa-times-circle me-2"></i>Từ chối đơn hàng</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="<?= site_url('orders/reject/' . $o['id']) ?>" method="POST">
                            <div class="modal-body p-4">
                                <p class="text-muted" style="font-size:0.88rem;">Lý do từ chối (sẽ gửi cho người mua):</p>
                                <textarea class="form-control form-control-hcmue" name="reject_reason" rows="3"
                                          placeholder="VD: Sách đã được đặt trước, không còn phù hợp..." required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-danger rounded-3 fw-bold">Xác nhận từ chối</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('tab-buy').classList.toggle('d-none', tab !== 'buy');
    document.getElementById('tab-sell').classList.toggle('d-none', tab !== 'sell');
    document.querySelectorAll('.order-tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', (i === 0 && tab === 'buy') || (i === 1 && tab === 'sell'));
    });
    history.replaceState(null, '', '?tab=' + tab);
}

function switchSubTab(mainTab, phase) {
    const cards = document.querySelectorAll(`.${mainTab}-card`);
    const emptyMsg = document.getElementById(`${mainTab}-empty`);
    let visibleCount = 0;

    cards.forEach(card => {
        if (card.dataset.phase === phase) {
            card.classList.remove('d-none');
            visibleCount++;
        } else {
            card.classList.add('d-none');
        }
    });

    if (emptyMsg) {
        if (visibleCount === 0 && cards.length > 0) {
            emptyMsg.classList.remove('d-none');
        } else {
            emptyMsg.classList.add('d-none');
        }
    }

    // Toggle button active state
    document.querySelectorAll(`.${mainTab}-sub-btn`).forEach(btn => {
        btn.classList.remove('btn-primary-hcmue');
        btn.classList.add('btn-outline-secondary');
    });

    const activeBtn = event ? event.currentTarget : document.querySelector(`.${mainTab}-sub-btn`);
    if (activeBtn) {
        activeBtn.classList.remove('btn-outline-secondary');
        activeBtn.classList.add('btn-primary-hcmue');
    }
}

// Chạy khởi tạo lọc sub-tab mặc định
document.addEventListener('DOMContentLoaded', () => {
    switchSubTab('buy', 'current');
    switchSubTab('sell', 'current');
});
</script>
