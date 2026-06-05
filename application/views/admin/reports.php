<style>
/* Glassmorphism Theme cho Admin Reports */
.glass-container {
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.08);
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    z-index: 1;
}

.glass-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 16px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.table-glass {
    border-collapse: separate;
    border-spacing: 0 10px;
}

.table-glass thead th {
    border: none;
    background: transparent;
    color: #4b5563;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-glass tbody tr {
    background: rgba(255, 255, 255, 0.85);
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-radius: 12px;
    transition: all 0.2s;
}

.table-glass tbody tr:hover {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.table-glass tbody td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
}

.table-glass tbody td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}

.table-glass tbody td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.nav-pills-custom .nav-link {
    color: #4b5563;
    background: rgba(255,255,255,0.5);
    border-radius: 30px;
    margin-right: 10px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-pills-custom .nav-link.active {
    background: var(--hcmue-blue, #003f8a);
    color: white;
    box-shadow: 0 4px 12px rgba(0, 63, 138, 0.3);
}

/* Background blob decorations for glass effect */
.bg-blob {
    position: absolute;
    filter: blur(80px);
    z-index: 0;
    opacity: 0.5;
}
.blob-1 {
    top: -100px;
    left: -100px;
    width: 300px;
    height: 300px;
    background: #ffb6ff;
    border-radius: 50%;
}
.blob-2 {
    bottom: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: #a1c4fd;
    border-radius: 50%;
}
</style>

<div class="position-relative overflow-hidden" style="min-height: calc(100vh - 76px); background: #f0f4f8;">
    <!-- Decorative Blobs -->
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <div class="container py-5 position-relative z-1">
        
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $this->session->flashdata('success'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $this->session->flashdata('error'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: #1e293b;">
                    <i class="fas fa-flag text-danger me-2"></i>Báo Cáo Người Dùng
                </h2>
                <p class="text-muted mb-0">Xem xét và giải quyết các báo cáo vi phạm gian lận hoặc hành vi xấu sau khi giao dịch hoàn tất.</p>
            </div>
            <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-pill shadow-sm px-4">
                <i class="fas fa-arrow-left me-2"></i>Về Dashboard
            </a>
        </div>

        <div class="glass-container">
            <?php 
                $pending_reports = array_filter($reports, function($r) { return $r['status'] === 'pending'; });
                $resolved_reports = array_filter($reports, function($r) { return $r['status'] !== 'pending'; });
            ?>
            <!-- Tabs -->
            <ul class="nav nav-pills nav-pills-custom mb-4" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>Chờ Xử Lý
                        <?php if (count($pending_reports) > 0): ?>
                            <span class="badge bg-danger ms-2"><?= count($pending_reports) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="resolved-tab" data-bs-toggle="pill" data-bs-target="#resolved" type="button" role="tab">
                        <i class="fas fa-check-circle me-2"></i>Đã Xử Lý / Bác Bỏ
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="reportTabsContent">
                
                <!-- Tab Chờ Xử Lý -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <?php if (empty($pending_reports)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Không có báo cáo nào cần xử lý.</h5>
                            <p class="text-muted small">Cộng đồng đang hoạt động rất văn minh và trung thực.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-glass table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Mã BC</th>
                                        <th>Người Báo Cáo</th>
                                        <th>Người Bị Báo Cáo</th>
                                        <th>Đơn Hàng / Sách</th>
                                        <th>Nội Dung Báo Cáo</th>
                                        <th>Thời Gian</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_reports as $r): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $r['id'] ?></td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($r['reporter_name'] ?: $r['reporter_username']) ?></span>
                                            <div class="small text-muted">@<?= htmlspecialchars($r['reporter_username']) ?></div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger"><?= htmlspecialchars($r['reported_name'] ?: $r['reported_username']) ?></span>
                                            <div class="small text-muted">@<?= htmlspecialchars($r['reported_username']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($r['order_id']): ?>
                                                <a href="<?= site_url('orders/detail/' . $r['order_id']) ?>" target="_blank" class="fw-bold text-decoration-none">
                                                    Đơn #<?= $r['order_id'] ?>
                                                </a>
                                                <div class="small text-truncate text-muted" style="max-width: 150px;" title="<?= htmlspecialchars($r['post_title'] ?? '') ?>">
                                                    <?= htmlspecialchars($r['post_title'] ?? 'N/A') ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Không có đơn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="p-2 rounded bg-light border-start border-danger border-3 small" style="max-width: 250px; white-space: normal; word-wrap: break-word;">
                                                <?= htmlspecialchars($r['reason']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="<?= site_url('admin/resolve_report/' . $r['id'] . '/resolved') ?>" 
                                                   class="btn btn-sm btn-success rounded-pill px-3 fw-bold" 
                                                   onclick="return confirm('Đánh dấu báo cáo #<?= $r['id'] ?> này là ĐÃ GIẢI QUYẾT?');">
                                                    <i class="fas fa-check me-1"></i>Xử lý xong
                                                </a>
                                                <a href="<?= site_url('admin/resolve_report/' . $r['id'] . '/dismissed') ?>" 
                                                   class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" 
                                                   onclick="return confirm('Bác bỏ báo cáo #<?= $r['id'] ?> này (không xử lý)?');">
                                                    <i class="fas fa-ban me-1"></i>Bác bỏ
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab Đã Xử Lý / Bác Bỏ -->
                <div class="tab-pane fade" id="resolved" role="tabpanel">
                    <?php if (empty($resolved_reports)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Chưa có lịch sử báo cáo nào được giải quyết.</h5>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-glass table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Mã BC</th>
                                        <th>Người Báo Cáo</th>
                                        <th>Người Bị Báo Cáo</th>
                                        <th>Đơn Hàng / Sách</th>
                                        <th>Nội Dung Báo Cáo</th>
                                        <th>Thời Gian</th>
                                        <th class="text-center">Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resolved_reports as $r): ?>
                                    <tr>
                                        <td class="fw-bold text-muted">#<?= $r['id'] ?></td>
                                        <td>
                                            <span class="text-muted"><?= htmlspecialchars($r['reporter_name'] ?: $r['reporter_username']) ?></span>
                                            <div class="small text-muted">@<?= htmlspecialchars($r['reporter_username']) ?></div>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= htmlspecialchars($r['reported_name'] ?: $r['reported_username']) ?></span>
                                            <div class="small text-muted">@<?= htmlspecialchars($r['reported_username']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($r['order_id']): ?>
                                                <a href="<?= site_url('orders/detail/' . $r['order_id']) ?>" target="_blank" class="text-secondary fw-bold text-decoration-none">
                                                    Đơn #<?= $r['order_id'] ?>
                                                </a>
                                                <div class="small text-truncate text-muted" style="max-width: 150px;">
                                                    <?= htmlspecialchars($r['post_title'] ?? 'N/A') ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Không có đơn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="p-2 rounded bg-light border-start border-secondary border-3 small text-muted" style="max-width: 250px; white-space: normal; word-wrap: break-word;">
                                                <?= htmlspecialchars($r['reason']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($r['status'] === 'resolved'): ?>
                                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i>Đã Giải Quyết</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="fas fa-ban me-1"></i>Đã Bác Bỏ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
