<div class="container py-4">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="section-title flex-grow-1"><i class="fas fa-tags"></i> Quản lý Danh mục
            <span class="badge bg-secondary ms-2" style="font-size:0.75rem;font-weight:600;"><?= count($categories) ?></span>
        </h2>
        <div>
            <button class="btn btn-primary-hcmue rounded-3 px-3 fw-bold" style="font-size:0.85rem;" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-1"></i> Thêm danh mục
            </button>
            <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-3 px-3 ms-2" style="font-size:0.85rem;">
                <i class="fas fa-arrow-left me-1"></i> Về Dashboard
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead style="background:#F8FAFC;">
                    <tr class="text-muted" style="font-size:0.77rem;">
                        <th style="padding:12px 16px;">ID</th>
                        <th>Icon</th>
                        <th>Tên danh mục</th>
                        <th style="text-align:right;padding-right:16px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td style="padding:10px 16px;color:#9CA3AF;">#<?= $c['id'] ?></td>
                        <td>
                            <div style="width:36px;height:36px;background:#EFF6FF;color:var(--primary);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;">
                                <i class="<?= htmlspecialchars($c['icon']) ?>"></i>
                            </div>
                        </td>
                        <td class="fw-bold" style="color:#1E293B;"><?= htmlspecialchars($c['category_name']) ?></td>
                        <td style="text-align:right;padding-right:16px;">
                            <!-- Nút Sửa -->
                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-2 me-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategoryModal<?= $c['id'] ?>"
                                    title="Sửa">
                                <i class="fas fa-edit"></i> Sửa
                            </button>

                            <!-- Nút Xóa -->
                            <a href="<?= site_url('admin/delete_category/'.$c['id']) ?>" 
                               class="btn btn-sm btn-outline-danger rounded-3 px-2"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"
                               title="Xóa">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </a>
                        </td>
                    </tr>

                    <!-- Modal Edit cho từng danh mục -->
                    <div class="modal fade" id="editCategoryModal<?= $c['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <form action="<?= site_url('admin/edit_category/'.$c['id']) ?>" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" style="color:var(--primary);"><i class="fas fa-edit me-2"></i>Sửa Danh mục</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold mb-1">Tên danh mục</label>
                                        <input type="text" class="form-control" name="category_name" required value="<?= htmlspecialchars($c['category_name']) ?>" style="border-radius:10px;">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold mb-1">Class Icon (FontAwesome)</label>
                                        <input type="text" class="form-control" name="icon" placeholder="VD: fas fa-book" value="<?= htmlspecialchars($c['icon']) ?>" style="border-radius:10px;">
                                        <div class="form-text" style="font-size:0.75rem;">Lấy class từ <a href="https://fontawesome.com/v5/search?m=free" target="_blank">FontAwesome v5</a></div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary-hcmue rounded-3 fw-bold px-4">Lưu thay đổi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= site_url('admin/add_category') ?>" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:var(--primary);"><i class="fas fa-plus-circle me-2"></i>Thêm Danh mục Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold mb-1">Tên danh mục</label>
                    <input type="text" class="form-control" name="category_name" required placeholder="Nhập tên danh mục..." style="border-radius:10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold mb-1">Class Icon (FontAwesome)</label>
                    <input type="text" class="form-control" name="icon" placeholder="VD: fas fa-folder" style="border-radius:10px;">
                    <div class="form-text" style="font-size:0.75rem;">Lấy class từ <a href="https://fontawesome.com/v5/search?m=free" target="_blank">FontAwesome v5</a></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary-hcmue rounded-3 fw-bold px-4">Thêm mới</button>
            </div>
        </form>
    </div>
</div>
