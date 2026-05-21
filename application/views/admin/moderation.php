<style>
/* Glassmorphism Theme cho Admin Moderation */
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

.ai-score-bar {
    height: 10px;
    border-radius: 5px;
    background-color: #e9ecef;
    overflow: hidden;
    margin-top: 5px;
}

.ai-score-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.5s ease;
}

.bg-gradient-danger {
    background: linear-gradient(90deg, #ff416c 0%, #ff4b2b 100%);
}

.bg-gradient-warning {
    background: linear-gradient(90deg, #f6d365 0%, #fda085 100%);
}

.bg-gradient-success {
    background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
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

.badge-ai {
    padding: 0.4em 0.8em;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
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
    background: var(--primary-mid);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: #1e293b;">
                    <i class="fas fa-shield-alt text-primary me-2"></i>Quản trị Kiểm Duyệt AI
                </h2>
                <p class="text-muted mb-0">Theo dõi và xử lý các nội dung vi phạm tiêu chuẩn cộng đồng được AI PhoBERT phát hiện.</p>
            </div>
            <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-pill shadow-sm px-4">
                <i class="fas fa-arrow-left me-2"></i>Về Dashboard
            </a>
        </div>

        <div class="glass-container">
            <!-- Tabs -->
            <ul class="nav nav-pills nav-pills-custom mb-4" id="moderationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending" type="button" role="tab">
                        <i class="fas fa-exclamation-triangle me-2"></i>Cần Xử Lý
                        <?php if (count($flagged_comments) > 0): ?>
                            <span class="badge bg-danger ms-2"><?= count($flagged_comments) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="pill" data-bs-target="#logs" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Lịch Sử AI
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="moderationTabsContent">
                
                <!-- Tab Cần Xử Lý -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    
                    <?php if (empty($flagged_comments)): ?>
                        <div class="text-center py-5">
                            <img src="<?= base_url('assets/images/empty_box.png') ?>" alt="Empty" style="width:120px; opacity:0.5; margin-bottom:1rem;">
                            <h5 class="text-muted">Không có nội dung nào cần xử lý.</h5>
                            <p class="text-muted small">AI đang hoạt động tốt và chưa phát hiện vi phạm mới.</p>
                        </div>
                    <?php else: ?>
                        
                        <div class="row row-cols-1 row-cols-md-2 g-4">
                            <?php foreach ($flagged_comments as $c): ?>
                                <div class="col">
                                    <div class="glass-card p-4 h-100 position-relative">
                                        <div class="position-absolute top-0 end-0 mt-3 me-3">
                                            <span class="badge badge-ai bg-warning text-dark border border-warning">
                                                <i class="fas fa-flag me-1"></i>Bị Cắm Cờ
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold me-3" style="width:40px; height:40px;">
                                                <?= strtoupper(substr($c['user']['full_name'] ?? '?', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= $c['user']['full_name'] ?? 'Người dùng Ẩn' ?></h6>
                                                <small class="text-muted">Bình luận vào: <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
                                            </div>
                                        </div>

                                        <div class="p-3 mb-3 rounded-3" style="background: rgba(255,0,0,0.05); border-left: 4px solid #ff4b2b;">
                                            <p class="mb-1 text-dark" style="font-size:0.95rem;">
                                                "<?= htmlspecialchars($c['content']) ?>"
                                            </p>
                                        </div>

                                        <?php if(isset($c['post'])): ?>
                                            <p class="small text-muted mb-3">
                                                <i class="fas fa-book me-1"></i> Bài viết: <a href="<?= site_url('trade/view/' . $c['post']['id']) ?>" target="_blank" class="text-decoration-none fw-bold"><?= htmlspecialchars($c['post']['title']) ?></a>
                                            </p>
                                        <?php endif; ?>

                                        <?php 
                                            // Phân tích % từ ai_score (VD: 0.85 -> 85%)
                                            $score_percent = number_format($c['ai_score'] * 100, 1);
                                            $bar_class = 'bg-gradient-warning';
                                            if ($c['ai_score'] > 0.8) $bar_class = 'bg-gradient-danger';
                                        ?>
                                        
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small fw-bold text-muted">Độ tin cậy (AI):</span>
                                                <span class="small fw-bold text-danger"><?= $score_percent ?>% (Toxic)</span>
                                            </div>
                                            <div class="ai-score-bar">
                                                <div class="ai-score-fill <?= $bar_class ?>" style="width: <?= $score_percent ?>%;"></div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-auto">
                                            <form action="<?= site_url('admin/moderation_action') ?>" method="POST" class="flex-grow-1">
                                                <input type="hidden" name="type" value="comment">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-outline-success w-100 rounded-pill fw-bold" onclick="return confirm('Bạn xác nhận nội dung này là AN TOÀN?')">
                                                    <i class="fas fa-check me-1"></i>Bỏ qua
                                                </button>
                                            </form>
                                            
                                            <form action="<?= site_url('admin/moderation_action') ?>" method="POST" class="flex-grow-1">
                                                <input type="hidden" name="type" value="comment">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold" onclick="return confirm('Xóa vĩnh viễn nội dung vi phạm này?')">
                                                    <i class="fas fa-trash-alt me-1"></i>Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Tab Lịch Sử AI -->
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-glass table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại</th>
                                    <th>Nội dung Text</th>
                                    <th>Dự đoán</th>
                                    <th>Quyết định</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($ai_logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Chưa có dữ liệu log kiểm duyệt.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($ai_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted fw-bold"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?php if($log['content_type'] == 'comment'): ?>
                                                <span class="badge bg-info text-dark rounded-pill"><i class="fas fa-comment me-1"></i>Bình luận</span>
                                            <?php elseif($log['content_type'] == 'post'): ?>
                                                <span class="badge bg-primary rounded-pill"><i class="fas fa-book me-1"></i>Bài đăng</span>
                                            <?php elseif($log['content_type'] == 'message'): ?>
                                                <span class="badge bg-secondary rounded-pill"><i class="fas fa-envelope me-1"></i>Tin nhắn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($log['raw_text']) ?>">
                                                <?= htmlspecialchars($log['raw_text']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            // Hiển thị nhãn
                                            $lbl = $log['prediction_label'];
                                            if ($lbl == 0) echo '<span class="badge bg-success">Nhãn 0 (Sạch)</span>';
                                            elseif ($lbl == 1) echo '<span class="badge bg-warning text-dark">Nhãn 1 (Cảnh báo)</span>';
                                            elseif ($lbl == 2) echo '<span class="badge bg-danger">Nhãn 2 (Cấm)</span>';
                                            else echo '<span class="badge bg-secondary">Unknown</span>';
                                            ?>
                                            <div class="mt-1 small text-muted">
                                                L0: <?= number_format($log['label_0_score'], 2) ?> | 
                                                L1: <?= number_format($log['label_1_score'], 2) ?> | 
                                                <span class="<?= $log['label_2_score']>0.5 ? 'text-danger fw-bold' : '' ?>">L2: <?= number_format($log['label_2_score'], 2) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($log['action_taken'] == 'allow'): ?>
                                                <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i>Cho phép</span>
                                            <?php elseif($log['action_taken'] == 'flag'): ?>
                                                <span class="text-warning fw-bold"><i class="fas fa-flag me-1"></i>Gắn cờ</span>
                                            <?php elseif($log['action_taken'] == 'block'): ?>
                                                <span class="text-danger fw-bold"><i class="fas fa-ban me-1"></i>Chặn</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
