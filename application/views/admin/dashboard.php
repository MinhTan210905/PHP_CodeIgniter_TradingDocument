<div class="container py-4">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-3 mb-4">
        <div style="width:46px;height:46px;background:var(--hcmue-gold);border-radius:12px;display:flex;align-items:center;justify-content:center;color:var(--hcmue-blue);font-size:1.3rem;">
            <i class="fas fa-cog"></i>
        </div>
        <div>
            <h2 style="font-size:1.2rem;font-weight:800;color:var(--hcmue-blue);margin:0;">Quản trị Admin</h2>
            <span class="text-muted" style="font-size:0.8rem;">HCMUE BookSwap</span>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="<?= site_url('admin/payments') ?>" class="btn rounded-3 fw-bold text-white position-relative" style="font-size:0.85rem;background:linear-gradient(135deg,#10B981,#059669);">
                <i class="fas fa-money-check-alt me-1"></i> Kiểm duyệt Thanh toán
                <?php $total_payment_alerts = (isset($total_withdrawals) ? $total_withdrawals : 0); ?>
                <?php if ($total_payment_alerts > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $total_payment_alerts ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('admin/disputes') ?>" class="btn rounded-3 fw-bold text-white position-relative" style="font-size:0.85rem;background:linear-gradient(135deg,#DC2626,#EF4444);">
                <i class="fas fa-gavel me-1"></i> Tranh chấp
                <?php if (isset($total_disputes) && $total_disputes > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                        <?= $total_disputes ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="<?= site_url('admin/users') ?>" class="btn btn-primary-hcmue rounded-3 fw-bold" style="font-size:0.85rem;">
                <i class="fas fa-users me-1"></i> Quản lý Người dùng
            </a>
            <a href="<?= site_url('admin/categories') ?>" class="btn btn-outline-secondary rounded-3 fw-bold" style="font-size:0.85rem;">
                <i class="fas fa-tags me-1"></i> Quản lý Danh mục
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <?php
        $stats = [
            ['label'=>'Tổng bài đăng', 'value'=>$total_posts,     'icon'=>'fas fa-clipboard-list', 'color'=>'#003F8A', 'bg'=>'#EEF5FF'],
            ['label'=>'Chờ duyệt',     'value'=>$total_pending,   'icon'=>'fas fa-hourglass-half', 'color'=>'#B45309', 'bg'=>'#FEF3C7'],
            ['label'=>'Đang Rao',      'value'=>$total_available,  'icon'=>'fas fa-check-circle',   'color'=>'#059669', 'bg'=>'#ECFDF5'],
            ['label'=>'Đã Pass',       'value'=>$total_sold,       'icon'=>'fas fa-lock',           'color'=>'#6B7280', 'bg'=>'#F3F4F6'],
            ['label'=>'Người dùng',    'value'=>$total_users,      'icon'=>'fas fa-users',          'color'=>'#D97706', 'bg'=>'#FEF3C7'],
            ['label'=>'Tranh chấp',    'value'=>isset($total_disputes) ? $total_disputes : 0, 'icon'=>'fas fa-gavel', 'color'=>'#DC2626', 'bg'=>'#FEE2E2'],
        ];
        ?>
        <?php foreach($stats as $s): ?>
        <div class="col-6 col-md">
            <div class="card border-0 rounded-4 p-3 shadow-sm text-center">
                <div style="width:44px;height:44px;background:<?= $s['bg'] ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                    <i class="<?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.1rem;"></i>
                </div>
                <div style="font-size:1.6rem;font-weight:800;color:<?= $s['color'] ?>;"><?= $s['value'] ?></div>
                <div class="text-muted" style="font-size:0.78rem;"><?= $s['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== CẤU HÌNH HỆ THỐNG ===== -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0" style="color:var(--primary);">
                <i class="fas fa-tools me-2"></i>Cấu hình hệ thống (Admin Only)
            </h6>
        </div>
        <hr class="my-3" style="border-color:#F1F5F9;">
        <form action="<?= site_url('admin/update_settings') ?>" method="POST">
            <div class="row align-items-center">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_approve_new" id="autoApproveNew" value="1"
                               <?= (isset($app_settings['auto_approve_new']) && $app_settings['auto_approve_new'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label fw-600" for="autoApproveNew" style="font-size:0.88rem;">
                            Tự động duyệt bài mới
                        </label>
                    </div>
                    <small class="text-muted d-block" style="font-size:0.75rem;">Bật: Khỏi cần Admin bấm duyệt</small>
                </div>
                
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="auto_approve_edit" id="autoApproveEdit" value="1"
                               <?= (isset($app_settings['auto_approve_edit']) && $app_settings['auto_approve_edit'] == '1') ? 'checked' : '' ?>>
                        <label class="form-check-label fw-600" for="autoApproveEdit" style="font-size:0.88rem;">
                            Tự động duyệt khi sửa bài
                        </label>
                    </div>
                    <small class="text-muted d-block" style="font-size:0.75rem;">Tắt: Đưa bài về chờ duyệt lại</small>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-600 text-nowrap mb-0" for="autoApproveMinStars" style="font-size:0.88rem;">
                            <i class="fas fa-star text-warning me-1"></i>Duyệt theo Đánh giá (sao):
                        </label>
                        <input type="number" step="0.1" min="0" max="5" 
                               class="form-control form-control-sm" 
                               name="auto_approve_min_stars" 
                               id="autoApproveMinStars" 
                               style="border-radius:8px; font-size:0.83rem; width:80px;"
                               value="<?= htmlspecialchars($app_settings['auto_approve_min_stars'] ?? '0') ?>"
                               placeholder="VD: 4.5">
                    </div>
                    <small class="text-muted d-block" style="font-size:0.75rem; margin-top:2px;">Nhập từ 0 đến 5 (0 là tắt). Tự duyệt nếu người bán có số sao >= mức này.</small>
                </div>

                <div class="col-md-2 text-md-end">
                    <button type="submit" class="btn btn-primary-hcmue px-3 fw-bold w-100" style="border-radius:10px;font-size:0.83rem; height:36px;">
                        Lưu Cấu Hình
                    </button>
                </div>
            </div>
            
            <!-- Dòng cấu hình thông báo chạy chữ -->
            <div class="row mt-3">
                <div class="col-12">
                    <label class="fw-600 mb-1.5" for="siteAnnouncement" style="font-size:0.88rem;">
                        <i class="fas fa-bullhorn text-primary me-1"></i>Thông báo chạy chữ (Marquee Banner):
                    </label>
                    <input type="text" class="form-control" name="site_announcement" id="siteAnnouncement" 
                           placeholder="Nhập nội dung thông báo (VD: thông báo bảo trì, chào mừng tân sinh viên...)" 
                           value="<?= htmlspecialchars($app_settings['site_announcement'] ?? 'Chào mừng đến với diễn đàn pass tài liệu của Trường Đại học Sư phạm thành phố Hồ Chí Minh') ?>"
                           style="border-radius:10px; font-size:0.85rem;">
                    <small class="text-muted d-block mt-1" style="font-size:0.75rem;">Để trống nếu muốn tắt thông báo chạy chữ ở ngoài trang chủ.</small>
                </div>
            </div>
        </form>
    </div>

    <!-- ===== KHU DUYỆT BÀI ===== -->
    <?php if (!empty($pending_posts)): ?>
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <!-- Header nổi bật -->
        <div class="p-4 d-flex align-items-center gap-3" style="background:linear-gradient(135deg,#B45309,#D97706);">
            <div style="width:42px;height:42px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <h5 style="color:#fff;font-weight:800;margin:0;">Bài đăng chờ duyệt</h5>
                <span style="color:rgba(255,255,255,0.8);font-size:0.82rem;"><?= count($pending_posts) ?> bài cần xem xét</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead style="background:#FFFBEB;">
                    <tr class="text-muted" style="font-size:0.77rem;">
                        <th style="padding:12px 16px;">Tên sách</th>
                        <th>Người đăng</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Thời gian đăng</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_posts as $p): ?>
                    <tr>
                        <td style="padding:12px 16px;">
                            <a href="<?= site_url('trade/detail/'.$p['id']) ?>"
                               class="text-decoration-none fw-semibold text-dark"
                               style="font-size:0.88rem;" target="_blank">
                                <?= htmlspecialchars(mb_strimwidth($p['title'], 0, 55, '...')) ?>
                                <i class="fas fa-external-link-alt ms-1" style="font-size:0.65rem;color:#9CA3AF;"></i>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:28px;height:28px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.7rem;flex-shrink:0;">
                                    <?= strtoupper(substr($p['full_name'] ?: $p['username'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($p['full_name'] ?: $p['username']) ?></span>
                            </div>
                        </td>
                        <td class="text-muted"><?= $p['category_name'] ?></td>
                        <td class="text-danger fw-bold"><?= number_format($p['price'],0,',','.') ?>đ</td>
                        <td class="text-muted" style="font-size:0.77rem;">
                            <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <!-- Nút Duyệt -->
                                <a href="<?= site_url('admin/approve_post/'.$p['id']) ?>"
                                   class="btn btn-sm fw-bold"
                                   style="background:#D1FAE5;color:#065F46;border-radius:8px;font-size:0.8rem;padding:5px 14px;"
                                   onclick="return confirm('Duyệt và đăng bài này lên trang chủ?');">
                                    <i class="fas fa-check me-1"></i> Duyệt
                                </a>
                                <!-- Nút Từ chối -->
                                <a href="<?= site_url('admin/reject_post/'.$p['id']) ?>"
                                   class="btn btn-sm fw-bold"
                                   style="background:#FEE2E2;color:#991B1B;border-radius:8px;font-size:0.8rem;padding:5px 14px;"
                                   onclick="return confirm('Từ chối và xóa bài này?');">
                                    <i class="fas fa-times me-1"></i> Từ chối
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 rounded-4 shadow-sm mb-4 p-4 text-center" style="background:#F0FDF4;border:1.5px dashed #86EFAC!important;">
        <i class="fas fa-check-circle" style="font-size:2rem;color:#22C55E;"></i>
        <p class="mt-2 mb-0 fw-semibold" style="color:#166534;">Không có bài nào đang chờ duyệt!</p>
    </div>
    <?php endif; ?>

    <!-- ===== BẢNG BÀI ĐÃ DUYỆT ===== -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="p-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="mb-0 fw-bold" style="color:var(--hcmue-blue);">
                <i class="fas fa-list me-2"></i>Bài đã được duyệt (<?= count($recent_posts) ?>)
            </h6>
            <div class="d-flex gap-2 align-items-center">
                <div class="btn-group" role="group" id="postStatusFilter">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-start-3 fw-bold active" data-filter="all" style="font-size:0.78rem;padding:4px 14px;">
                        Tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold" data-filter="available" style="font-size:0.78rem;padding:4px 14px;">
                        <i class="fas fa-circle me-1" style="font-size:5px;vertical-align:middle;"></i>Đang rao
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-end-3 fw-bold" data-filter="sold" style="font-size:0.78rem;padding:4px 14px;">
                        <i class="fas fa-lock me-1" style="font-size:10px;"></i>Đã Pass
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.84rem;">
                <thead style="background:#F8FAFC;">
                    <tr class="text-muted" style="font-size:0.77rem;">
                        <th style="padding:12px 16px;">Tên sách</th>
                        <th>Người đăng</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Ngày duyệt</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="approvedPostsBody">
                    <?php foreach($recent_posts as $p): ?>
                    <tr data-status="<?= $p['status'] ?>">
                        <td style="padding:10px 16px;">
                            <a href="<?= site_url('trade/detail/'.$p['id']) ?>"
                               class="text-decoration-none fw-semibold text-dark"
                               style="font-size:0.84rem;">
                                <?= htmlspecialchars(mb_strimwidth($p['title'], 0, 50, '...')) ?>
                            </a>
                        </td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($p['full_name'] ?: $p['username']) ?></td>
                        <td class="text-danger fw-bold"><?= number_format($p['price'],0,',','.') ?>đ</td>
                        <td>
                            <?php if ($p['status']==='available'): ?>
                                <span class="status-badge-avail">Còn sách</span>
                            <?php else: ?>
                                <span class="status-badge-sold">Đã Pass</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:0.77rem;"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= site_url('trade/edit/'.$p['id']) ?>"
                                   class="btn btn-sm btn-outline-primary rounded-2"
                                   style="font-size:0.75rem;padding:4px 10px;" title="Chỉnh sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="<?= site_url('admin/delete_post/'.$p['id']) ?>"
                                   class="btn btn-sm btn-outline-danger rounded-2"
                                   onclick="return confirm('Xóa bài đăng này?');"
                                   style="font-size:0.75rem;padding:4px 10px;">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Lọc tab Đang rao / Đã Pass & Check updates realtime -->
    <script>
    document.querySelectorAll('#postStatusFilter button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#postStatusFilter button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('#approvedPostsBody tr').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        });
    });

    // Realtime kiểm tra thay đổi số lượng bài viết / thanh toán chờ duyệt
    (function() {
        const currentPendingPosts = <?= count($pending_posts) ?>;
        const currentWithdrawals = <?= isset($total_withdrawals) ? (int)$total_withdrawals : 0 ?>;

        setInterval(function() {
            fetch('<?= site_url("admin/check_updates") ?>', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    if (data.pending_posts !== currentPendingPosts || data.pending_withdrawals !== currentWithdrawals) {
                        // Tự động tải lại trang khi có bài đăng mới chờ duyệt hoặc yêu cầu thanh toán mới
                        location.reload();
                    }
                }
            })
            .catch(err => console.warn('Lỗi kiểm tra cập nhật admin:', err));
        }, 7000);
    })();
    </script>

</div>
