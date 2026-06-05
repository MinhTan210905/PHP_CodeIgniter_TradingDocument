<div class="container py-4" style="max-width:760px;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.82rem;">
            <li class="breadcrumb-item"><a href="<?= site_url('trade') ?>" class="text-decoration-none" style="color:var(--hcmue-blue);">Trang chủ</a></li>
            <li class="breadcrumb-item active text-muted">Danh sách mong muốn</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
        <div style="background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-light));padding:24px 28px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="text-white fw-bold" style="font-size:1.15rem;">
                        <i class="fas fa-heart me-2"></i>Danh sách mong muốn sách
                    </div>
                    <div class="text-white opacity-75" style="font-size:0.82rem;">
                        Hệ thống sẽ tự động thông báo khi có sách phù hợp được đăng bán.
                    </div>
                </div>
                <span class="badge rounded-pill" style="background:var(--hcmue-gold);color:var(--hcmue-blue);font-weight:800;font-size:0.85rem;padding:8px 16px;">
                    <?= $wishlist_count ?>/<?= $max_wishlists ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert" style="font-size:0.88rem;">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert" style="font-size:0.88rem;">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form Thêm mong muốn -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--hcmue-blue);"><i class="fas fa-plus-circle me-2"></i>Thêm sách mong muốn</h6>
        <form action="<?= site_url('wishlist/add') ?>" method="POST" class="d-flex gap-2">
            <input type="text" name="book_title" class="form-control rounded-3" 
                   placeholder="Nhập tên sách bạn đang tìm kiếm..." 
                   maxlength="200" required
                   style="font-size:0.9rem;"
                   <?= $wishlist_count >= $max_wishlists ? 'disabled' : '' ?>>
            <button type="submit" class="btn btn-primary-hcmue rounded-3 fw-bold px-4 flex-shrink-0"
                    <?= $wishlist_count >= $max_wishlists ? 'disabled' : '' ?>
                    style="white-space:nowrap;">
                <i class="fas fa-plus me-1"></i>Thêm
            </button>
        </form>
        <?php if($wishlist_count >= $max_wishlists): ?>
            <div class="text-muted mt-2" style="font-size:0.78rem;">
                <i class="fas fa-info-circle me-1"></i>Đã đạt giới hạn <?= $max_wishlists ?> mong muốn. Vui lòng xóa bớt để thêm mới.
            </div>
        <?php endif; ?>
    </div>

    <!-- Danh sách mong muốn -->
    <?php if (empty($wishlists)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center">
            <i class="fas fa-inbox" style="font-size:3rem;color:#CBD5E1;"></i>
            <p class="mt-3 text-muted" style="font-size:0.92rem;">Chưa có mong muốn nào. Hãy thêm tên sách bạn đang tìm kiếm!</p>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
            <div class="p-3" style="background:#F8FAFC;">
                <div class="fw-bold" style="font-size:0.82rem;color:var(--hcmue-blue);">
                    <i class="fas fa-list me-1"></i>Danh sách theo dõi (<?= $wishlist_count ?> mục)
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($wishlists as $item): ?>
                    <div class="list-group-item d-flex align-items-center gap-3 px-4 py-3" style="border-color:#F1F5F9;">
                        <!-- Icon trạng thái -->
                        <div style="width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                    background:<?= $item['is_active'] ? '#DBEAFE' : '#F3F4F6' ?>;
                                    color:<?= $item['is_active'] ? 'var(--hcmue-blue)' : '#9CA3AF' ?>;">
                            <i class="fas fa-book" style="font-size:0.9rem;"></i>
                        </div>

                        <!-- Thông tin -->
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-truncate" style="font-size:0.9rem;color:<?= $item['is_active'] ? '#1F2937' : '#9CA3AF' ?>;">
                                <?= htmlspecialchars($item['book_title']) ?>
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <i class="far fa-clock me-1"></i>Đăng ký: <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                <?php if (!$item['is_active']): ?>
                                    <span class="ms-2 badge bg-secondary" style="font-size:0.65rem;">Đã tắt</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['match_post_id']) && $item['match_status'] === 'available'): ?>
                                <div class="mt-2 p-2 rounded-3 d-flex align-items-center justify-content-between gap-2" style="background:#ECFDF5; border: 1px solid #A7F3D0; font-size:0.8rem;">
                                    <span style="color:#065F46;" class="text-truncate">
                                        <i class="fas fa-bullhorn me-1"></i> Có sách bán: <strong><?= htmlspecialchars($item['match_title']) ?></strong> (<?= number_format($item['match_price'], 0, ',', '.') ?>đ)
                                    </span>
                                    <a href="<?= site_url('trade/detail/' . $item['match_post_id']) ?>" class="btn btn-xs btn-success rounded-pill px-2 py-1 fw-bold text-white text-decoration-none flex-shrink-0" style="font-size:0.7rem; background:#059669; border:none;">
                                        Xem ngay <i class="fas fa-chevron-right ms-1" style="font-size:0.6rem;"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Toggle Switch -->
                        <a href="<?= site_url('wishlist/toggle/' . $item['id']) ?>" 
                           class="btn btn-sm rounded-pill px-3 fw-bold"
                           style="font-size:0.75rem;white-space:nowrap;
                                  background:<?= $item['is_active'] ? '#DBEAFE' : '#F3F4F6' ?>;
                                  color:<?= $item['is_active'] ? 'var(--hcmue-blue)' : '#9CA3AF' ?>;"
                           title="<?= $item['is_active'] ? 'Tắt thông báo' : 'Bật thông báo' ?>">
                            <i class="fas <?= $item['is_active'] ? 'fa-bell' : 'fa-bell-slash' ?> me-1"></i>
                            <?= $item['is_active'] ? 'Đang bật' : 'Đã tắt' ?>
                        </a>

                        <!-- Nút Xóa -->
                        <a href="<?= site_url('wishlist/delete/' . $item['id']) ?>"
                           class="btn btn-sm btn-outline-danger rounded-pill px-2"
                           onclick="return confirm('Xóa mong muốn này khỏi danh sách?');"
                           title="Xóa mong muốn">
                            <i class="fas fa-trash-alt" style="font-size:0.75rem;"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ghi chú hướng dẫn -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mt-4" style="background:#F8FAFC;">
        <h6 class="fw-bold mb-2" style="color:var(--hcmue-blue);font-size:0.85rem;"><i class="fas fa-info-circle me-2"></i>Cách hoạt động</h6>
        <ul class="mb-0 text-muted" style="font-size:0.82rem;padding-left:18px;">
            <li>Nhập tên sách bạn đang tìm kiếm, ví dụ: <em>"Giáo trình Cấu trúc dữ liệu"</em></li>
            <li>Khi có người đăng bán sách có tên tương tự (≥70% khớp), hệ thống sẽ gửi thông báo qua <strong>tin nhắn</strong> và <strong>email</strong> cho bạn.</li>
            <li>Bạn có thể <strong>tạm tắt</strong> thông báo cho từng mong muốn để tránh bị làm phiền.</li>
            <li>Giới hạn tối đa <strong><?= $max_wishlists ?> mong muốn</strong> mỗi tài khoản.</li>
        </ul>
    </div>
</div>
