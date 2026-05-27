<div class="container py-5" style="max-width: 700px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-edit me-2 text-primary"></i>Chỉnh sửa bài đăng
            </h5>
            <a href="<?= site_url('profile') ?>" class="btn btn-sm btn-light rounded-3 text-muted">
                <i class="fas fa-times"></i>
            </a>
        </div>
        <div class="card-body p-4">
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger small mb-3"><?= $this->session->flashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= site_url('trade/update/' . $post['id']) ?>" method="POST" enctype="multipart/form-data">
                <!-- Tiêu đề -->
                <div class="mb-3">
                    <label class="form-label fw-600 text-secondary small">Tiêu đề bài viết <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 border-light shadow-none" name="title" 
                           style="background:#f8fafc;" value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Danh mục -->
                    <div class="col-md-6">
                        <label class="form-label fw-600 text-secondary small">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 border-light shadow-none" name="category_id" style="background:#f8fafc;" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($post['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= $cat['category_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Giá -->
                    <div class="col-md-6">
                        <label class="form-label fw-600 text-secondary small">Giá bán (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" class="form-control border-light shadow-none" name="price" 
                                   style="background:#f8fafc;border-radius:10px 0 0 10px;" value="<?= $post['price'] ?>" min="0">
                            <span class="input-group-text border-light bg-light small" style="border-radius:0 10px 10px 0;">đ</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Số lượng -->
                    <div class="col-md-4">
                        <label class="form-label fw-600 text-secondary small">Số lượng hiện có</label>
                        <input type="number" class="form-control rounded-3 border-light shadow-none" name="quantity" 
                               style="background:#f8fafc;" value="<?= $post['quantity'] ?>" min="1">
                    </div>
                    <!-- Ảnh bìa -->
                    <div class="col-md-4">
                        <label class="form-label fw-600 text-secondary small">Ảnh bìa (Đổi mới)</label>
                        <input type="file" class="form-control form-control-sm rounded-3 border-light shadow-none" name="image" accept="image/*" style="background:#f8fafc;">
                        <?php if(!empty($post['image_url'])): ?>
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <div style="width: 50px; height: 60px; overflow:hidden; border-radius:6px; border:1px solid #eee;">
                                    <img src="<?= base_url($post['image_url']) ?>" alt="CurImg" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <small class="text-muted" style="font-size:0.65rem;">Ảnh bìa hiện tại</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Ảnh chi tiết -->
                    <div class="col-md-4">
                        <label class="form-label fw-600 text-secondary small">Thêm ảnh chi tiết mới</label>
                        <input type="file" class="form-control form-control-sm rounded-3 border-light shadow-none" name="additional_images[]" accept="image/*" multiple style="background:#f8fafc;">
                        
                        <?php if(!empty($additional_images)): ?>
                            <div class="d-flex gap-1 mt-2 overflow-x-auto pb-1" style="scrollbar-width: none;">
                                <?php foreach($additional_images as $sub_img): ?>
                                    <div style="width: 40px; height: 50px; overflow:hidden; border-radius:5px; border:1px solid #eee; flex-shrink:0;">
                                        <img src="<?= base_url($sub_img['image_url']) ?>" alt="DetailImg" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="font-size:0.65rem; color:var(--primary); font-weight:600; margin-top:2px;">
                                <i class="fas fa-images me-1"></i> Đã đăng <?= count($additional_images) ?> ảnh phụ
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tài liệu đọc thử PDF -->
                <div class="mb-3">
                    <label class="form-label fw-600 text-secondary small">Tài liệu đọc thử PDF (Tùy chọn, tối đa 20MB)</label>
                    <input type="file" class="form-control form-control-sm rounded-3 border-light shadow-none" name="pdf_file" accept="application/pdf" style="background:#f8fafc;">
                    <?php if(!empty($post['pdf_url'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-2" style="font-size:0.75rem;">
                                <i class="fas fa-file-pdf me-1"></i> Đã có file đọc thử
                            </span>
                            <a href="<?= base_url($post['pdf_url']) ?>" target="_blank" class="text-primary small text-decoration-none fw-semibold">
                                <i class="fas fa-external-link-alt me-1"></i> Xem file hiện tại
                            </a>
                        </div>
                        <div class="form-text text-muted mt-1" style="font-size:0.7rem;">Tải lên file PDF mới để thay thế file hiện tại.</div>
                    <?php else: ?>
                        <div class="form-text text-muted mt-1" style="font-size:0.7rem;">Chưa có tài liệu đọc thử. Bạn có thể tải lên file PDF để người mua có thể xem thử một vài trang sách.</div>
                    <?php endif; ?>
                </div>

                <!-- Tình trạng sách -->
                <div class="mb-4">
                    <label class="form-label fw-600 text-secondary small">Tình trạng sách *</label>
                    <select class="form-select form-control-sm rounded-3 border-light shadow-none" name="item_condition" style="background:#f8fafc;" required>
                        <option value="used" <?= (!isset($post['item_condition']) || $post['item_condition'] === 'used') ? 'selected' : '' ?>>Đã sử dụng</option>
                        <option value="new" <?= (isset($post['item_condition']) && $post['item_condition'] === 'new') ? 'selected' : '' ?>>Mới</option>
                    </select>
                </div>

                <!-- Mô tả -->
                <div class="mb-4">
                    <label class="form-label fw-600 text-secondary small">Mô tả chi tiết</label>
                    <textarea class="form-control rounded-3 border-light shadow-none" name="description" rows="4" 
                              style="background:#f8fafc;" placeholder="Nhập mô tả về tình trạng sách, độ mới..."><?= htmlspecialchars($post['description']) ?></textarea>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= site_url('trade') ?>" class="btn btn-light px-4 rounded-3 text-secondary fw-bold">Hủy</a>
                    <button type="submit" class="btn btn-primary-hcmue px-4 rounded-3 fw-bold shadow-sm">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
