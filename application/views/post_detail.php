<style>
.post-thumb-item {
    width: 65px;
    height: 80px;
    flex-shrink: 0;
    cursor: pointer;
    border: 2.5px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0.6;
    background: #fff;
}
.post-thumb-item:hover {
    opacity: 0.9;
    border-color: #E2E8F0;
}
.post-thumb-item.active {
    opacity: 1 !important;
    border-color: var(--primary) !important;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.18);
}
.post-thumb-row::-webkit-scrollbar {
    height: 5px;
}
.post-thumb-row::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
    border-radius: 10px;
}
.post-thumb-row::-webkit-scrollbar-track {
    background: #F8FAFC;
}
.main-img-container {
    background: #FFFFFF;
    transition: all 0.3s ease;
}
</style>
<div class="container py-4" style="max-width:900px;">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:0.82rem;">
            <li class="breadcrumb-item"><a href="<?= site_url('trade') ?>" class="text-decoration-none" style="color:var(--primary);">Trang chủ</a></li>
            <li class="breadcrumb-item active text-muted"><?= htmlspecialchars($post['title']) ?></li>
        </ol>
    </nav>

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

    <!-- Post Detail Card -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="row g-0">
            <!-- Image Column (Multi-Image support) -->
            <div class="col-md-5 d-flex flex-column bg-light" style="border-right: 1px solid #F1F5F9;">
                <?php
                    $img_path = FCPATH . $post['image_url'];
                    $img_src  = (!empty($post['image_url']) && file_exists($img_path))
                                ? base_url($post['image_url'])
                                : base_url('assets/images/default_book.jpg');
                ?>
                <!-- Khung ảnh chính -->
                <div class="main-img-container bg-white d-flex align-items-center justify-content-center p-3" style="height:380px; overflow:hidden; position:relative;">
                    <img id="mainImageDisplay" src="<?= $img_src ?>"
                         class="img-fluid"
                         style="object-fit:contain; max-height:100%; max-width:100%; border-radius:8px; transition: opacity 0.15s ease;"
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         onerror="this.onerror=null;this.src='<?= base_url('assets/images/default_book.jpg') ?>';">
                </div>
                
                <!-- Hàng ảnh phụ (Thumbnails) -->
                <?php if(!empty($additional_images)): ?>
                <div class="p-3 bg-white border-top">
                    <div class="d-flex gap-2 overflow-x-auto pb-2 post-thumb-row" style="scrollbar-width: thin;">
                        <!-- Thumbnail ảnh chính -->
                        <div class="post-thumb-item active" onclick="changeMainImage('<?= $img_src ?>', this)">
                            <img src="<?= $img_src ?>" class="w-100 h-100" style="object-fit:cover;" onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">
                        </div>
                        
                        <!-- Thumbnails ảnh phụ -->
                        <?php foreach($additional_images as $img): 
                            $sub_path = FCPATH . $img['image_url'];
                            $sub_src = (file_exists($sub_path)) ? base_url($img['image_url']) : '';
                            if(!$sub_src) continue;
                        ?>
                        <div class="post-thumb-item" onclick="changeMainImage('<?= $sub_src ?>', this)">
                            <img src="<?= $sub_src ?>" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-muted mt-1 text-center" style="font-size:0.72rem;">
                        <i class="fas fa-info-circle me-1"></i>Click để xem ảnh chi tiết
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- Info -->
            <div class="col-md-7 p-4 d-flex flex-column">
                <!-- Status + Category -->
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <span class="badge-cat"><i class="fas fa-tag"></i> <?= $post['category_name'] ?></span>
                    <?php if($post['status'] === 'available'): ?>
                        <span class="status-badge-avail"><i class="fas fa-circle" style="font-size:6px;"></i> Còn Sách</span>
                    <?php else: ?>
                        <span class="status-badge-sold"><i class="fas fa-lock" style="font-size:10px;"></i> Đã Pass</span>
                    <?php endif; ?>
                </div>

                <h1 style="font-size:1.3rem;font-weight:800;color:#1A1A2E;line-height:1.4;" class="mb-3">
                    <?= htmlspecialchars($post['title']) ?>
                </h1>

                <!-- Price -->
                <p class="price-tag mb-3"><?= number_format($post['price'], 0, ',', '.') ?>đ</p>

                <!-- Description -->
                <p class="text-muted mb-3" style="font-size:0.88rem;line-height:1.7;">
                    <?= nl2br(htmlspecialchars($post['description'] ?: 'Không có mô tả chi tiết.')) ?>
                </p>

                <hr style="border-color:#F1F5F9;">

                <!-- Seller Info -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:44px;height:44px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;">
                        <?php if (!empty($post['avatar']) && file_exists(FCPATH . $post['avatar'])): ?>
                            <img src="<?= base_url($post['avatar']) ?>" alt="Avt" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="color:#F5A623;font-weight:800;font-size:1.1rem;"><?= strtoupper(substr($post['full_name'] ?: $post['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?= site_url('seller/' . $post['seller_id']) ?>"
                           class="text-decoration-none" style="font-weight:700;font-size:0.9rem;color:var(--primary);">
                            <?= htmlspecialchars($post['full_name'] ?: $post['username']) ?>
                            <i class="fas fa-external-link-alt ms-1" style="font-size:0.65rem;"></i>
                        </a>
                        <div class="star-display">
                            <?php if ($post['avg_rating'] > 0): ?>
                                <?php for($s=1;$s<=5;$s++): ?>
                                    <i class="<?= $s <= round($post['avg_rating']) ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                                <span style="color:#6B7280;font-size:0.78rem;"><?= number_format($post['avg_rating'],1) ?>/5 (<?= $post['total_ratings'] ?> đánh giá)</span>
                            <?php else: ?>
                                <span class="no-rating">Chưa có đánh giá</span>
                            <?php endif; ?>
                        </div>
                        <!-- SĐT -->
                        <?php if ($post['phone_visible'] && $post['phone']): ?>
                            <div class="mt-1" style="font-size:0.82rem;color:var(--primary);">
                                <i class="fas fa-phone me-1"></i>
                                <a href="tel:<?= $post['phone'] ?>" style="color:var(--primary);font-weight:600;"><?= $post['phone'] ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-auto">
                <?php $logged_in = $this->session->userdata('logged_in'); ?>
                <?php $cur_uid   = $this->session->userdata('user_id'); ?>

                <?php if ($post['status'] === 'available' && $logged_in && $post['seller_id'] != $cur_uid): ?>
                    <!-- Form yêu cầu mua -->
                    <form action="<?= site_url('orders/request_buy/' . $post['id']) ?>" method="POST"
                          class="p-3 rounded-4 mb-3" style="background:#F0F7FF;border:1.5px solid #DBEAFE;">
                        <div class="fw-bold mb-2" style="font-size:0.88rem;color:var(--primary);"><i class="fas fa-shopping-bag me-1"></i>Yêu cầu mua sách</div>
                        <div class="d-flex gap-2 mb-2">
                            <div style="flex:0 0 120px;">
                                <label class="form-label-hcmue">Số lượng</label>
                                <input type="number" name="quantity" min="1" max="<?= $post['quantity'] ?>" value="1"
                                       class="form-control form-control-hcmue text-center" style="font-weight:700;">
                            </div>
                            <div class="flex-grow-1">
                                <label class="form-label-hcmue">Ghi chú cho người bán (tùy chọn)</label>
                                <input type="text" name="note" class="form-control form-control-hcmue"
                                       placeholder="VD: Mình đang ở KTX A, có thể gặp buổi sáng...">
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary-hcmue flex-grow-1 py-2"
                                    onclick="return confirm('Gửi yêu cầu mua sách này?');">
                                <i class="fas fa-shopping-cart me-1"></i>Gửi yêu cầu mua
                            </button>
                            <a href="<?= site_url('message/conversation/' . $post['seller_id'] . '?post_id=' . $post['id']) ?>"
                               class="btn btn-outline-secondary py-2" title="Nhắn tin hỏi thêm">
                                <i class="fas fa-comment-dots"></i>
                            </a>
                            <?php if ($post['phone_visible'] && $post['phone']): ?>
                                <a href="tel:<?= $post['phone'] ?>" class="btn btn-outline-success py-2" title="Gọi điện">
                                    <i class="fas fa-phone"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php elseif ($post['status'] === 'sold' && $logged_in && $post['seller_id'] != $cur_uid): ?>
                    <div class="alert border-0 rounded-3 mb-3" style="background:#F3F4F6;color:#6B7280;font-size:0.88rem;">
                        <i class="fas fa-lock me-2"></i>Sách này đã hết hàng.
                        <a href="<?= site_url('seller/' . $post['seller_id']) ?>" class="ms-2" style="color:var(--primary);font-weight:600;">Xem sàn người bán</a>
                    </div>
                <?php elseif (!$logged_in): ?>
                    <a href="<?= site_url('auth') ?>" class="btn btn-primary-hcmue w-100 py-2 mb-3">
                        <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để mua sách
                    </a>
                <?php endif; ?>

                <?php if ($logged_in && ($post['seller_id'] == $cur_uid || $this->session->userdata('role') === 'admin')): ?>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <a href="<?= site_url('trade/edit/' . $post['id']) ?>"
                           class="btn btn-outline-secondary py-2 fw-bold">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa thông tin
                        </a>
                        <?php if ($post['status'] === 'available'): ?>
                            <a href="<?= site_url('trade/update_status/' . $post['id']) ?>"
                               class="btn btn-outline-success py-2 fw-bold"
                               onclick="return confirm('Đánh dấu Đã Pass (hết hàng)?');">
                                <i class="fas fa-check-circle me-1"></i>Đã Pass
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('trade/delete/' . $post['id']) ?>"
                           class="btn btn-outline-danger py-2"
                           onclick="return confirm('Xóa bài này?');"><i class="fas fa-trash"></i>
                        </a>
                    </div>
                <?php endif; ?>
                </div>

                <p class="text-muted mt-3 mb-0" style="font-size:0.75rem;">
                    <i class="far fa-clock me-1"></i>Đăng ngày <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ===== ĐÁNH GIÁ NGƯỜI BÁN ===== -->
    <?php $is_own = ($logged_in && $post['seller_id'] == $cur_uid); ?>
    <?php if ($logged_in && !$is_own): ?>
    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:var(--primary);">
            <i class="fas fa-star me-2" style="color:var(--accent);"></i>Đánh giá người bán
        </h5>
        <div class="alert border-0 rounded-3" style="background:#F0F7FF;color:var(--primary);font-size:0.88rem;">
            <i class="fas fa-info-circle me-2"></i>
            Đánh giá chỉ dành cho người đã mua và xác nhận nhận sách.
            <a href="<?= site_url('orders?tab=buy') ?>" class="ms-1 fw-bold" style="color:var(--primary);">Xem đơn mua của bạn →</a>
        </div>
    </div>
    <?php endif; ?>


    <!-- ===== BÌNH LUẬN ===== -->
    <div class="card border-0 rounded-4 shadow-sm p-4" id="comments">
        <h5 class="fw-bold mb-4" style="color:var(--primary);">
            <i class="fas fa-comments me-2"></i>Bình luận (<?= count($comments) ?>)
        </h5>

        <!-- Danh sách bình luận -->
        <?php if (empty($comments)): ?>
            <p class="text-muted mb-4" style="font-size:0.87rem;">
                Chưa có bình luận nào. Hãy là người đầu tiên!
            </p>
        <?php else: ?>
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach($comments as $cmt): ?>
                <div class="d-flex gap-3">
                    <div style="width:36px;height:36px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                        <?= strtoupper(substr($cmt['full_name'] ?: $cmt['username'], 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <div style="background:#F8FAFC;border-radius:12px;padding:12px 14px;">
                            <div class="fw-bold mb-1" style="font-size:0.82rem;color:var(--primary);">
                                <?= htmlspecialchars($cmt['full_name'] ?: $cmt['username']) ?>
                            </div>
                            <p class="mb-0" style="font-size:0.88rem;line-height:1.6;">
                                <?= nl2br(htmlspecialchars($cmt['content'])) ?>
                            </p>
                        </div>
                        <div class="text-muted mt-1" style="font-size:0.72rem;">
                            <i class="far fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($cmt['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form bình luận -->
        <?php if ($logged_in): ?>
            <form action="<?= site_url('comment/add/' . $post['id']) ?>" method="POST"
                  class="d-flex gap-3 align-items-start">
                <div style="width:36px;height:36px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                    <?= strtoupper(substr($this->session->userdata('full_name'), 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <textarea class="form-control form-control-hcmue mb-2" name="content" rows="2"
                              placeholder="Hỏi thêm về tình trạng sách, hẹn chỗ gặp..." required></textarea>
                    <button type="submit" class="btn btn-primary-hcmue px-4 py-2" style="font-size:0.87rem;">
                        <i class="fas fa-paper-plane me-1"></i> Gửi
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-light border rounded-3 mb-0" style="font-size:0.87rem;">
                <i class="fas fa-lock me-2 text-muted"></i>
                <a href="<?= site_url('auth') ?>" style="color:var(--primary);font-weight:600;">Đăng nhập</a>
                để bình luận.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script đổi ảnh chi tiết như Shopee -->
<script>
function changeMainImage(newSrc, thumbElement) {
    const mainDisplay = document.getElementById('mainImageDisplay');
    if (!mainDisplay) return;

    // Tạo hiệu ứng mờ dần nhẹ (fade effect)
    mainDisplay.style.opacity = '0.3';

    setTimeout(() => {
        mainDisplay.src = newSrc;
        mainDisplay.style.opacity = '1';
    }, 120);

    // Cập nhật trạng thái viền đỏ/xanh nổi bật của thumbnail active
    document.querySelectorAll('.post-thumb-item').forEach(item => {
        item.classList.remove('active');
    });
    thumbElement.classList.add('active');
}
</script>
