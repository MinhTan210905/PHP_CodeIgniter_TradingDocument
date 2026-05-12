<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HCMUE BookSwap | Trao Đổi Tài Liệu Sinh Viên Sư Phạm</title>
    <meta name="description" content="Nền tảng trao đổi sách và tài liệu học tập dành cho sinh viên HCMUE - Đại học Sư phạm TP.HCM">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo_hcmue.png') ?>">
    <style>
        :root {
            --nav-height: 72px;
            --primary: #1E40AF;
            --primary-mid: #2563EB;
            --accent: #F59E0B;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --bg-page: #F7F8FC;
        }
        body { padding-top: var(--nav-height); }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<nav class="navbar-hcmue">
    <div class="container h-100 d-flex align-items-center justify-content-between gap-3">

        <!-- Brand -->
        <a href="<?= site_url('trade') ?>" class="brand-logo flex-shrink-0">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" class="brand-icon-img" alt="Logo HCMUE">
            <div class="brand-text">
                <div class="brand-main">HCMUE BookSwap</div>
                <div class="brand-sub">Trao đổi tài liệu sinh viên</div>
            </div>
        </a>

        <!-- Right actions -->
        <div class="d-flex align-items-center gap-2">
            <?php if ($this->session->userdata('logged_in')): ?>
                <!-- Inbox -->
                <a href="<?= site_url('message/inbox') ?>" class="nav-icon-btn" title="Hộp thư">
                    <i class="fas fa-comment-dots"></i>
                    <?php if (isset($unread_count) && $unread_count > 0): ?>
                        <span class="nav-badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <!-- Đơn hàng -->
                <a href="<?= site_url('orders') ?>" class="nav-icon-btn" title="Đơn hàng của tôi">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if (isset($pending_count) && $pending_count > 0): ?>
                        <span class="nav-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <!-- Đăng bài -->
                <button class="btn-dang-bai" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-plus"></i> Đăng Sách
                </button>
                <!-- User Chip -->
                <div class="dropdown">
                    <a href="javascript:void(0)" class="nav-user-chip" data-bs-toggle="dropdown">
                        <div class="nav-user-avatar">
                            <?= strtoupper(substr($this->session->userdata('full_name'), 0, 1)) ?>
                        </div>
                        <span style="font-size:0.82rem; font-weight:600;">
                            <?= $this->session->userdata('full_name') ?>
                        </span>
                        <i class="fas fa-chevron-down" style="font-size:0.65rem; opacity:0.7;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 rounded-3">
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('profile') ?>">
                                <i class="fas fa-user-circle me-2 text-primary"></i>Trang cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('orders') ?>">
                                <i class="fas fa-shopping-bag me-2 text-success"></i>Đơn hàng của tôi
                                <?php if (isset($pending_count) && $pending_count > 0): ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:0.7rem;"><?= $pending_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('seller/' . $this->session->userdata('user_id')) ?>">
                                <i class="fas fa-store me-2 text-info"></i>Sàn của tôi
                            </a>
                        </li>
                        <?php if ($this->session->userdata('role') === 'admin'): ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('admin') ?>">
                                <i class="fas fa-cog me-2 text-warning"></i>Quản trị Admin
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= site_url('auth/logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?= site_url('auth/register') ?>" class="btn-dang-bai text-decoration-none">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
                <a href="<?= site_url('auth') ?>" class="nav-icon-btn" title="Đăng nhập">
                    <i class="fas fa-sign-in-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Modal Đăng Bài -->
<?php if ($this->session->userdata('logged_in')): ?>
<div class="modal fade modal-hcmue" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-medical me-2"></i>Đăng Sách / Tài Liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= site_url('trade/create') ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label-hcmue">Tên sách / Tài liệu *</label>
                        <input type="text" class="form-control form-control-hcmue" name="title" required
                               placeholder="VD: Giáo trình C++ - Lập trình Hướng đối tượng...">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label-hcmue">Danh mục môn học *</label>
                            <select class="form-select form-control-hcmue" name="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php if (isset($categories)): foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['category_name'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-hcmue">Giá Pass (VNĐ) *</label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-hcmue" name="price"
                                       required placeholder="VD: 50000" min="0">
                                <span class="input-group-text fw-bold text-muted" style="border-radius:0 10px 10px 0; font-size:0.85rem;">đ</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-hcmue">Số lượng *</label>
                            <input type="number" class="form-control form-control-hcmue" name="quantity"
                                   required placeholder="1" min="1" max="99" value="1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-hcmue">Mô tả tình trạng sách</label>
                        <textarea class="form-control form-control-hcmue" name="description" rows="3"
                                  placeholder="Sách còn bao nhiêu %, có ghi chú không, tặng kèm gì..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label-hcmue">Hình ảnh thực tế <small class="fw-normal text-muted">(tỉ lệ 4:3 đẹp nhất)</small></label>
                        <input type="file" class="form-control form-control-hcmue" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary-hcmue w-100 py-3 fs-6 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Bài Đăng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
