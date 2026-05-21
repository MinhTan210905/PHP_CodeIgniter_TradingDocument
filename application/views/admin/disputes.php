<div class="container py-4">
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#DC2626,#EF4444);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;">
                <i class="fas fa-gavel"></i>
            </div>
            <div>
                <h2 style="font-size:1.2rem;font-weight:800;color:var(--primary);margin:0;">Quản lý Tranh chấp</h2>
                <span class="text-muted" style="font-size:0.8rem;">Phân xử các đơn hàng bị khiếu nại</span>
            </div>
        </div>
        <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-3 px-3" style="font-size:0.85rem;">
            <i class="fas fa-arrow-left me-1"></i> Về Dashboard
        </a>
    </div>

    <!-- DANH SÁCH ĐƠN TRANH CHẤP -->
    <?php if (empty($disputed_orders)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#F0FDF4;border:1.5px dashed #86EFAC!important;">
            <i class="fas fa-check-circle" style="font-size:2rem;color:#22C55E;"></i>
            <p class="mt-2 mb-0 fw-semibold" style="color:#166534;">Không có đơn hàng tranh chấp nào cần phân xử!</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                    <thead style="background:#FEF2F2;">
                        <tr style="color:#991B1B; font-size:0.77rem;">
                            <th style="padding:12px 16px;">Mã Đơn</th>
                            <th>Sản phẩm</th>
                            <th>Người mua (Khiếu nại)</th>
                            <th>Người bán (Bị khiếu nại)</th>
                            <th>Giá trị</th>
                            <th>Thời gian</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($disputed_orders as $d): ?>
                        <tr>
                            <td style="padding:10px 16px;"><span class="fw-bold text-danger">#<?= $d['id'] ?></span></td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($d['post_title']) ?>">
                                    <?= htmlspecialchars($d['post_title']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($d['buyer_name'] ?: $d['buyer_username']) ?></td>
                            <td><?= htmlspecialchars($d['seller_name'] ?: $d['seller_username']) ?></td>
                            <td class="fw-bold"><?= number_format($d['price'] * $d['quantity'], 0, ',', '.') ?>đ</td>
                            <td class="text-muted" style="font-size:0.77rem;">
                                <?= date('d/m/Y H:i', strtotime($d['updated_at'])) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger rounded-3 fw-bold" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#disputeModal<?= $d['id'] ?>">
                                    <i class="fas fa-eye me-1"></i>Xem & Xử lý
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODALS CHI TIẾT TRANH CHẤP -->
        <?php foreach($disputed_orders as $d): ?>
        <div class="modal fade" id="disputeModal<?= $d['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <!-- Header Modal -->
                    <div class="modal-header d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:linear-gradient(135deg,#FEF2F2,#FECACA);border-radius:16px 16px 0 0;padding:16px 20px; border-bottom: none;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;background:#DC2626;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold m-0" style="color:#991B1B;font-size:1.1rem;">
                                    Chi tiết tranh chấp Đơn #<?= $d['id'] ?>
                                </h5>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($d['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="<?= site_url('orders/detail/'.$d['id']) ?>" class="btn btn-sm btn-outline-danger rounded-3 fw-bold me-2" style="font-size:0.78rem;" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>Xem đơn
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <!-- Thông tin 2 bên -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background:#DBEAFE;">
                                    <div class="fw-bold mb-1" style="font-size:0.78rem;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fas fa-shopping-cart me-1"></i>Người mua (Khiếu nại)
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                            <?= strtoupper(substr($d['buyer_name'] ?: $d['buyer_username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($d['buyer_name'] ?: $d['buyer_username']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background:#FEF3C7;">
                                    <div class="fw-bold mb-1" style="font-size:0.78rem;color:#92400E;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fas fa-store me-1"></i>Người bán (Bị khiếu nại)
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                            <?= strtoupper(substr($d['seller_name'] ?: $d['seller_username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($d['seller_name'] ?: $d['seller_username']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin sách & Giá -->
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3" style="background:#F8FAFC;">
                            <i class="fas fa-book" style="font-size:1.2rem;color:var(--primary);"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size:0.9rem;"><?= htmlspecialchars($d['post_title']) ?></div>
                                <div class="text-muted" style="font-size:0.8rem;">
                                    SL: <?= $d['quantity'] ?> · Giá: <strong class="text-danger"><?= number_format($d['price'] * $d['quantity'], 0, ',', '.') ?>đ</strong>
                                    <?php if (!empty($d['payment_method'])): ?>
                                        · <span class="badge bg-<?= $d['payment_method'] === 'wallet' ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $d['payment_method'] === 'wallet' ? 'success' : 'secondary' ?>" style="font-size:0.7rem;">
                                            <?= $d['payment_method'] === 'wallet' ? 'Ví HCMUEPay' : 'COD' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Lý do khiếu nại -->
                        <div class="p-3 rounded-3 mb-3" style="background:#FEE2E2;border:1px solid #FECACA;">
                            <div class="fw-bold mb-1" style="font-size:0.8rem;color:#991B1B;">
                                <i class="fas fa-flag me-1"></i>Lý do khiếu nại từ người mua:
                            </div>
                            <div style="font-size:0.88rem;color:#7F1D1D;line-height:1.6;">
                                <?= nl2br(htmlspecialchars($d['reject_reason'])) ?>
                            </div>
                        </div>

                        <!-- Minh chứng giao hàng -->
                        <?php if (!empty($d['delivery_proof'])): ?>
                        <div class="p-3 rounded-3 mb-3" style="background:#D1FAE5;border:1px solid #A7F3D0;">
                            <div class="fw-bold mb-2" style="font-size:0.8rem;color:#065F46;">
                                <i class="fas fa-camera me-1"></i>Minh chứng giao hàng từ người bán:
                            </div>
                            <div class="text-center">
                                <img src="<?= base_url($d['delivery_proof']) ?>" alt="Minh chứng" class="img-fluid rounded-3 shadow-sm" style="max-height:300px;object-fit:contain;cursor:pointer;" onclick="window.open(this.src, '_blank')">
                                <div class="text-muted mt-1" style="font-size:0.72rem;"><i class="fas fa-search-plus me-1"></i>Nhấn ảnh để xem phóng to</div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="p-3 rounded-3 mb-3" style="background:#FFF7ED;border:1px solid #FED7AA;">
                            <div style="font-size:0.85rem;color:#C2410C;">
                                <i class="fas fa-exclamation-circle me-1"></i><strong>Người bán chưa gửi minh chứng giao hàng.</strong> Đây có thể là bằng chứng bất lợi cho người bán.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="modal-footer" style="background:#F9FAFB; border-radius:0 0 16px 16px;">
                        <!-- Nút phân xử -->
                        <button class="btn btn-danger rounded-3 fw-bold flex-grow-1" style="font-size:0.85rem;"
                                data-bs-toggle="modal" data-bs-target="#refundModal<?= $d['id'] ?>">
                            <i class="fas fa-undo me-1"></i>Đồng ý khiếu nại (Hoàn tiền Người mua)
                        </button>
                        <button class="btn btn-success rounded-3 fw-bold flex-grow-1" style="font-size:0.85rem;"
                                data-bs-toggle="modal" data-bs-target="#releaseModal<?= $d['id'] ?>">
                            <i class="fas fa-check-circle me-1"></i>Từ chối khiếu nại (Giải ngân Người bán)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Đồng ý khiếu nại (Hoàn tiền) -->
        <div class="modal fade" id="refundModal<?= $d['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header" style="background:linear-gradient(135deg,#DC2626,#EF4444);border-radius:16px 16px 0 0;">
                        <h5 class="modal-title text-white fw-bold"><i class="fas fa-undo me-2"></i>Hoàn tiền Đơn #<?= $d['id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= site_url('admin/resolve_dispute_refund/'.$d['id']) ?>" method="POST">
                        <div class="modal-body p-4">
                            <div class="alert alert-danger rounded-3 mb-3" style="font-size:0.85rem;">
                                <i class="fas fa-exclamation-triangle me-2"></i><strong>Thao tác này sẽ:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Hủy đơn hàng #<?= $d['id'] ?></li>
                                    <?php if ($d['payment_method'] === 'wallet' && $d['payment_status'] === 'paid'): ?>
                                        <li>Hoàn <strong><?= number_format($d['price'] * $d['quantity'], 0, ',', '.') ?>đ</strong> về ví người mua</li>
                                    <?php endif; ?>
                                    <li>Thông báo cho cả 2 bên qua chat</li>
                                </ul>
                            </div>
                            <label class="form-label fw-bold" style="font-size:0.88rem;">Ghi chú kết luận của Admin:</label>
                            <textarea class="form-control rounded-3" name="admin_note" rows="3" required
                                      placeholder="VD: Người bán không có minh chứng giao hàng hợp lệ..."></textarea>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-light rounded-3" data-bs-toggle="modal" data-bs-target="#disputeModal<?= $d['id'] ?>">Trở lại</button>
                            <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4"
                                    onclick="return confirm('Xác nhận HOÀN TIỀN cho người mua đơn #<?= $d['id'] ?>?');">
                                <i class="fas fa-undo me-1"></i>Xác nhận hoàn tiền
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Từ chối khiếu nại (Giải ngân) -->
        <div class="modal fade" id="releaseModal<?= $d['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header" style="background:linear-gradient(135deg,#059669,#10B981);border-radius:16px 16px 0 0;">
                        <h5 class="modal-title text-white fw-bold"><i class="fas fa-check-circle me-2"></i>Giải ngân Đơn #<?= $d['id'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= site_url('admin/resolve_dispute_release/'.$d['id']) ?>" method="POST">
                        <div class="modal-body p-4">
                            <div class="alert alert-success rounded-3 mb-3" style="font-size:0.85rem;">
                                <i class="fas fa-check-circle me-2"></i><strong>Thao tác này sẽ:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Đánh dấu đơn hàng #<?= $d['id'] ?> là <strong>Hoàn thành</strong></li>
                                    <?php if ($d['payment_method'] === 'wallet' && $d['payment_status'] === 'paid'): ?>
                                        <li>Giải ngân <strong><?= number_format($d['price'] * $d['quantity'], 0, ',', '.') ?>đ</strong> vào ví người bán</li>
                                    <?php endif; ?>
                                    <li>Thông báo cho cả 2 bên qua chat</li>
                                </ul>
                            </div>
                            <label class="form-label fw-bold" style="font-size:0.88rem;">Ghi chú kết luận của Admin:</label>
                            <textarea class="form-control rounded-3" name="admin_note" rows="3" required
                                      placeholder="VD: Người bán đã có minh chứng giao hàng rõ ràng với GPS..."></textarea>
                        </div>
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-light rounded-3" data-bs-toggle="modal" data-bs-target="#disputeModal<?= $d['id'] ?>">Trở lại</button>
                            <button type="submit" class="btn btn-success rounded-3 fw-bold px-4"
                                    onclick="return confirm('Xác nhận GIẢI NGÂN cho người bán đơn #<?= $d['id'] ?>?');">
                                <i class="fas fa-check me-1"></i>Xác nhận giải ngân
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr class="my-5" style="opacity:0.1;">

    <!-- LỊCH SỬ PHÂN XỬ TRANH CHẤP -->
    <div class="d-flex align-items-center gap-3 mb-4 mt-5">
        <div style="width:46px;height:46px;background:linear-gradient(135deg,#059669,#10B981);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.3rem;">
            <i class="fas fa-history"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:800;color:var(--primary);margin:0;">Lịch sử Phân xử</h2>
            <span class="text-muted" style="font-size:0.8rem;">Danh sách các tranh chấp đã được giải quyết hoặc đảo ngược quyết định</span>
        </div>
    </div>

    <?php if (empty($resolved_orders)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 text-center mb-5" style="background:#F8FAFC;border:1.5px dashed #CBD5E1!important;">
            <i class="fas fa-folder-open" style="font-size:2rem; color:#94A3B8;"></i>
            <p class="mt-2 mb-0 fw-semibold text-muted" style="font-size:0.88rem;">Chưa có lịch sử phân xử tranh chấp nào.</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                    <thead style="background:#ECFDF5;">
                        <tr style="color:#065F46; font-size:0.77rem;">
                            <th style="padding:12px 16px;">Mã Đơn</th>
                            <th>Sản phẩm</th>
                            <th>Người mua</th>
                            <th>Người bán</th>
                            <th>Phán quyết cuối</th>
                            <th>Giá trị</th>
                            <th>Thời gian xử lý</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($resolved_orders as $r): ?>
                        <?php 
                            $is_cancelled = $r['status'] === 'cancelled';
                            $status_badge_class = $is_cancelled ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success';
                            $status_badge_text = $is_cancelled ? 'Hoàn tiền Buyer' : 'Giải ngân Seller';
                        ?>
                        <tr>
                            <td style="padding:10px 16px;"><span class="fw-bold text-secondary">#<?= $r['id'] ?></span></td>
                            <td>
                                <div class="fw-bold text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($r['post_title']) ?>">
                                    <?= htmlspecialchars($r['post_title']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($r['buyer_name'] ?: $r['buyer_username']) ?></td>
                            <td><?= htmlspecialchars($r['seller_name'] ?: $r['seller_username']) ?></td>
                            <td>
                                <span class="badge <?= $status_badge_class ?>" style="font-size:0.72rem; padding:4px 8px; border-radius:6px;">
                                    <?= $status_badge_text ?>
                                </span>
                            </td>
                            <td class="fw-bold"><?= number_format($r['price'] * $r['quantity'], 0, ',', '.') ?>đ</td>
                            <td class="text-muted" style="font-size:0.77rem;">
                                <?= date('d/m/Y H:i', strtotime($r['updated_at'])) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 fw-bold" style="font-size:0.75rem;" data-bs-toggle="modal" data-bs-target="#resolvedModal<?= $r['id'] ?>">
                                    <i class="fas fa-search me-1"></i>Chi tiết & Thay đổi
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODALS CHI TIẾT TRANH CHẤP ĐÃ XỬ LÝ (LỊCH SỬ) -->
        <?php foreach($resolved_orders as $r): ?>
        <?php 
            $is_cancelled = $r['status'] === 'cancelled';
            $verdict_color = $is_cancelled ? '#DC2626' : '#059669';
            $verdict_bg = $is_cancelled ? '#FEF2F2' : '#ECFDF5';
            $verdict_title = $is_cancelled ? 'Đồng ý khiếu nại (Hoàn tiền Người mua)' : 'Từ chối khiếu nại (Giải ngân Người bán)';
        ?>
        <div class="modal fade" id="resolvedModal<?= $r['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <!-- Header Modal -->
                    <div class="modal-header d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:linear-gradient(135deg,#F1F5F9,#E2E8F0);border-radius:16px 16px 0 0;padding:16px 20px; border-bottom: none;">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:40px;height:40px;background:#64748B;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold m-0" style="color:#334155;font-size:1.1rem;">
                                    Lịch sử tranh chấp Đơn #<?= $r['id'] ?>
                                </h5>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <i class="far fa-clock me-1"></i>Hoàn tất: <?= date('d/m/Y H:i', strtotime($r['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="<?= site_url('orders/detail/'.$r['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-3 fw-bold me-2" style="font-size:0.78rem;" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>Xem đơn
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    
                    <div class="modal-body p-4">
                        <!-- Trạng thái phán quyết cũ -->
                        <div class="p-3 rounded-3 mb-3 d-flex align-items-center gap-3" style="background: <?= $verdict_bg ?>; color: <?= $verdict_color ?>; border: 1px solid <?= $is_cancelled ? '#FCA5A5' : '#6EE7B7' ?>;">
                            <i class="fas <?= $is_cancelled ? 'fa-undo' : 'fa-check-circle' ?>" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong style="font-size: 0.88rem;">Phán quyết hiện tại: <?= $verdict_title ?></strong>
                                <div class="mt-1" style="font-size:0.8rem; opacity:0.85;">
                                    Số tiền giao dịch: <strong><?= number_format($r['price'] * $r['quantity'], 0, ',', '.') ?>đ</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin 2 bên -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background:#DBEAFE;">
                                    <div class="fw-bold mb-1" style="font-size:0.78rem;color:#1E40AF;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fas fa-shopping-cart me-1"></i>Người mua (Nhận bồi hoàn nếu hoàn tiền)
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                            <?= strtoupper(substr($r['buyer_name'] ?: $r['buyer_username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($r['buyer_name'] ?: $r['buyer_username']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background:#FEF3C7;">
                                    <div class="fw-bold mb-1" style="font-size:0.78rem;color:#92400E;text-transform:uppercase;letter-spacing:0.5px;">
                                        <i class="fas fa-store me-1"></i>Người bán (Nhận bồi hoàn nếu giải ngân)
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                            <?= strtoupper(substr($r['seller_name'] ?: $r['seller_username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($r['seller_name'] ?: $r['seller_username']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nhật ký phán quyết trước -->
                        <div class="p-3 rounded-3 mb-3 bg-light border">
                            <div class="fw-bold mb-1" style="font-size:0.8rem; color:#475569;">
                                <i class="fas fa-history me-1"></i>Lịch sử kết luận tranh chấp:
                            </div>
                            <div style="font-size:0.85rem;color:#334155;line-height:1.6; white-space: pre-wrap;"><?= htmlspecialchars($r['reject_reason']) ?></div>
                        </div>

                        <!-- Minh chứng giao hàng -->
                        <?php if (!empty($r['delivery_proof'])): ?>
                        <div class="p-3 rounded-3 mb-3" style="background:#D1FAE5;border:1px solid #A7F3D0;">
                            <div class="fw-bold mb-2" style="font-size:0.8rem;color:#065F46;">
                                <i class="fas fa-camera me-1"></i>Minh chứng giao hàng từ người bán:
                            </div>
                            <div class="text-center">
                                <img src="<?= base_url($r['delivery_proof']) ?>" alt="Minh chứng" class="img-fluid rounded-3 shadow-sm" style="max-height:220px;object-fit:contain;cursor:pointer;" onclick="window.open(this.src, '_blank')">
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Form Đảo Ngược Quyết Định (Kháng Cáo) -->
                        <div class="p-3 rounded-3 mt-4" style="background:#FFF7ED; border:1px solid #FED7AA;">
                            <h6 class="fw-bold text-warning mb-2" style="font-size:0.85rem; color:#C2410C !important;">
                                <i class="fas fa-arrows-spin me-1"></i> Đảo ngược quyết định phân xử (Kháng cáo thành công)
                            </h6>
                            <p class="text-muted mb-3" style="font-size:0.78rem;">
                                Nếu Admin phát hiện phán quyết trước đó bị sai sót hoặc nhận được bằng chứng kháng cáo hợp lệ từ bên thua cuộc, bạn có thể bấm nút đảo ngược. Tiền sẽ được rút từ ví của bên được bồi hoàn cũ (cho phép số dư ví âm) để chuyển trả lại cho bên đối diện.
                            </p>
                            
                            <form action="<?= site_url('admin/reverse_dispute_decision/'.$r['id']) ?>" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark small" style="font-size:0.8rem;">Ghi chú/Lý do đảo phán quyết:</label>
                                    <textarea class="form-control rounded-3" name="admin_note" rows="2" required style="font-size:0.82rem;"
                                              placeholder="Nhập lý do thay đổi phán quyết chi tiết..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning w-100 rounded-3 fw-bold text-white shadow-sm" style="font-size:0.84rem; background:linear-gradient(135deg,#EA580C,#F97316); border:none;"
                                        onclick="return confirm('⚠️ CẢNH BÁO: Bạn chắc chắn muốn ĐẢO NGƯỢC quyết định phân xử cho đơn #<?= $r['id'] ?>? Thao tác này sẽ cập nhật lại số dư ví HCMUEPay của cả hai bên.');">
                                    <i class="fas fa-arrows-rotate me-1"></i>Xác nhận Đảo ngược Phán quyết
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
