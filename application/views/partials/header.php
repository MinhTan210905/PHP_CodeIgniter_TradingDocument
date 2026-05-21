<?php
if (!isset($categories) || empty($categories)) {
    $CI =& get_instance();
    $CI->load->model('Trade_model');
    $categories = $CI->Trade_model->get_categories();
}
header('Content-Type: text/html; charset=UTF-8');
?>
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
            --nav-height: 76px;
            --primary: #1E40AF;
            --primary-mid: #2563EB;
            --accent: #F59E0B;
            --bg-page: #F7F8FC;
        }
        body { padding-top: var(--nav-height); }
        .navbar-hcmue {
            background: linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%) !important;
            box-shadow: 0 4px 12px rgba(30,64,175,0.15) !important;
            border-bottom: none !important;
        }
        .navbar-hcmue .brand-main {
            color: #ffffff !important;
        }
        .navbar-hcmue .brand-sub {
            color: rgba(255,255,255,0.7) !important;
        }
        .navbar-hcmue .nav-icon-btn {
            color: rgba(255,255,255,0.85) !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        .navbar-hcmue .nav-icon-btn:hover {
            background: rgba(255,255,255,0.1) !important;
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.4) !important;
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=4.1') ?>">
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
                <?php 
                $CI =& get_instance();
                $CI->load->model('Message_model');
                $header_unread = $CI->Message_model->count_unread($this->session->userdata('user_id'));
                ?>
                <a href="<?= site_url('message/inbox') ?>" class="nav-icon-btn" title="Hộp thư">
                    <i class="fas fa-comment-dots"></i>
                    <span class="nav-badge" id="inboxUnreadBadge" style="<?= $header_unread > 0 ? '' : 'display:none;' ?>"><?= $header_unread ?></span>
                </a>
                <!-- Đơn hàng -->
                <a href="<?= site_url('orders') ?>" class="nav-icon-btn" title="Đơn hàng của tôi">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if (isset($pending_count) && $pending_count > 0): ?>
                        <span class="nav-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <!-- Mong muốn sách -->
                <a href="<?= site_url('wishlist') ?>" class="nav-icon-btn" title="Danh sách mong muốn">
                    <i class="fas fa-bell"></i>
                </a>
                <!-- Đăng bài -->
                <button class="btn-dang-bai" data-bs-toggle="modal" data-bs-target="#createPostModal">
                    <i class="fas fa-plus"></i> Đăng Sách
                </button>
                <!-- User Chip -->
                <div class="dropdown">
                    <a href="javascript:void(0)" class="nav-user-chip position-relative" data-bs-toggle="dropdown">
                        <div class="nav-user-avatar" style="overflow:hidden; display:flex; align-items:center; justify-content:center;">
                            <?php 
                            $ses_avt = $this->session->userdata('avatar');
                            if (!empty($ses_avt) && file_exists(FCPATH . $ses_avt)): ?>
                                <img src="<?= base_url($ses_avt) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($this->session->userdata('full_name'), 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <span style="font-size:0.82rem; font-weight:600;">
                            <?= $this->session->userdata('full_name') ?>
                        </span>
                        <?php if ($this->session->userdata('role') === 'admin'): ?>
                            <?php 
                            // Tính tổng việc cần admin xử lý (cho chấm đỏ trên avatar)
                            $CI_h =& get_instance();
                            if (!isset($admin_total_pending)) {
                                $CI_h->load->model('Trade_model');
                                $CI_h->load->model('Wallet_model');
                                $admin_total_pending = $CI_h->Trade_model->count_pending() 
                                    + $CI_h->Wallet_model->count_pending_withdrawals()
                                    + $CI_h->db->where('status', 'disputed')->count_all_results('orders')
                                    + $CI_h->db->where('moderation_status', 'flagged')->count_all_results('comments');
                            }
                            ?>
                            <?php if ($admin_total_pending > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size:0.6rem; min-width:18px; padding: 3px 5px;">
                                    <?= $admin_total_pending ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down" style="font-size:0.65rem; opacity:0.7;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2 shadow border-0 rounded-3">
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('profile') ?>">
                                <i class="fas fa-user-circle me-2 text-primary"></i>Trang cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('wishlist') ?>">
                                <i class="fas fa-bell me-2 text-warning"></i>Danh sách mong muốn
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
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('wallet') ?>">
                                <i class="fas fa-wallet me-2" style="color:#059669;"></i>Ví HCMUEPay
                            </a>
                        </li>
                        <?php if ($this->session->userdata('role') === 'admin'): ?>
                        <?php 
                            $CI =& get_instance();
                            $CI->load->model('Trade_model');
                            $CI->load->model('Wallet_model');
                            $admin_pending_posts = $CI->Trade_model->count_pending();
                            $admin_pending_withdraw = $CI->Wallet_model->count_pending_withdrawals();
                            $admin_pending_disputes = $CI->db->where('status', 'disputed')->count_all_results('orders');
                            $admin_pending_comments = $CI->db->where('moderation_status', 'flagged')->count_all_results('comments');
                            $admin_total_pending = $admin_pending_posts + $admin_pending_withdraw + $admin_pending_disputes + $admin_pending_comments;
                        ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('admin') ?>">
                                <i class="fas fa-cog me-2 text-warning"></i>Quản trị Admin
                                <?php if($admin_total_pending > 0): ?>
                                    <span class="badge bg-danger ms-1" style="font-size:0.7rem;"><?= $admin_total_pending ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if($admin_pending_disputes > 0): ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('admin/disputes') ?>">
                                <i class="fas fa-gavel me-2 text-danger"></i>Tranh chấp cần xử lý
                                <span class="badge bg-danger ms-1" style="font-size:0.7rem;"><?= $admin_pending_disputes ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if($admin_pending_comments > 0): ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= site_url('admin/moderation') ?>">
                                <i class="fas fa-shield-alt me-2 text-warning"></i>Nội dung vi phạm (AI)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:0.7rem;"><?= $admin_pending_comments ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
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
                <a href="<?= site_url('auth') ?>" class="btn text-white fw-bold px-3" style="font-size:0.85rem;">
                    Đăng nhập
                </a>
                <a href="<?= site_url('auth/register') ?>" class="btn-dang-bai text-decoration-none">
                    Đăng ký
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if ($this->session->userdata('logged_in')): ?>
<script>
// Tự động kiểm tra và cập nhật badge tin nhắn chưa đọc ở header mỗi 4 giây
(function() {
    const badge = document.getElementById('inboxUnreadBadge');
    if (!badge) return;

    function checkTotalUnread() {
        fetch('<?= site_url("message/total_unread") ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                const count = parseInt(data.count);
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(err => console.warn('Lỗi đồng bộ tin nhắn:', err));
    }

    // Chạy định kỳ 4 giây
    setInterval(checkTotalUnread, 4000);
})();
</script>
<?php endif; ?>

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
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-hcmue">Ảnh bìa sách (Ảnh chính) *</label>
                            <input type="file" class="form-control form-control-hcmue" name="image" accept="image/*" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-hcmue">Ảnh chi tiết khác (Nhiều ảnh)</label>
                            <input type="file" class="form-control form-control-hcmue" name="additional_images[]" accept="image/*" multiple>
                        </div>
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
