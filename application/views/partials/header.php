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
    <!-- Thư viện quét mã vạch ISBN qua Camera -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- Thư viện Pusher JS (Real-time Chat) -->
    <script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
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
        /* Cực hình: Căn giữa tuyệt đối số thông báo đỏ trên header - Chống cache trình duyệt */
        .navbar-hcmue .nav-badge {
            position: absolute !important;
            top: -6px !important;
            right: -6px !important;
            background: #EF4444 !important;
            color: #ffffff !important;
            font-size: 9px !important;
            font-weight: 800 !important;
            border-radius: 50% !important;
            width: 18px !important;
            height: 18px !important;
            display: flex;
            align-items: center !important;
            justify-content: center !important;
            border: 1.5px solid #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1 !important;
            box-sizing: border-box !important;
        }

        /* Announcement Marquee Bar */
        .announcement-bar {
            background: linear-gradient(90deg, #F0F9FF 0%, #E0F2FE 100%);
            border-bottom: 1px solid #BAE6FD;
            color: #0369A1;
            font-size: 0.82rem;
            font-weight: 600;
            height: 38px;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 0 45px 0 20px;
            z-index: 999;
        }
        .announcement-marquee {
            display: flex;
            align-items: center;
            white-space: nowrap;
            animation: marquee-scroll 25s linear infinite;
            padding-left: 100%;
        }
        .announcement-marquee:hover {
            animation-play-state: paused;
        }
        .announcement-close {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #0369A1;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .announcement-close:hover {
            opacity: 1;
            background: rgba(3, 105, 161, 0.08);
        }
        @keyframes marquee-scroll {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
        }
    </style>
        
        /* Cực hình: Căn giữa tuyệt đối số thông báo đỏ trên header - Chống cache trình duyệt */
        .navbar-hcmue .nav-badge {
            position: absolute !important;
            top: -6px !important;
            right: -6px !important;
            background: #EF4444 !important;
            color: #ffffff !important;
            font-size: 9px !important;
            font-weight: 800 !important;
            border-radius: 50% !important;
            width: 18px !important;
            height: 18px !important;
            display: flex;
            align-items: center !important;
            justify-content: center !important;
            border: 1.5px solid #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1 !important;
            box-sizing: border-box !important;
=======
        /* Announcement Marquee Bar */
        .announcement-bar {
            background: linear-gradient(90deg, #F0F9FF 0%, #E0F2FE 100%);
            border-bottom: 1px solid #BAE6FD;
            color: #0369A1;
            font-size: 0.82rem;
            font-weight: 600;
            height: 38px;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
            padding: 0 45px 0 20px;
            z-index: 999;
        }
        .announcement-marquee {
            display: flex;
            align-items: center;
            white-space: nowrap;
            animation: marquee-scroll 25s linear infinite;
            padding-left: 100%;
        }
        .announcement-marquee:hover {
            animation-play-state: paused;
        }
        .announcement-close {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #0369A1;
            font-size: 0.85rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .announcement-close:hover {
            opacity: 1;
            background: rgba(3, 105, 161, 0.08);
        }
        @keyframes marquee-scroll {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
>>>>>>> origin/FindTrendingWord
        }
    </style>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=4.3') ?>">
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

                <!-- Đơn hàng -->
                <a href="<?= site_url('orders') ?>" class="nav-icon-btn" title="Đơn hàng của tôi">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if (isset($pending_count) && $pending_count > 0): ?>
                        <span class="nav-badge"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <!-- Tin nhắn -->
                <?php 
                $CI_h =& get_instance();
                $CI_h->load->model('Message_model');
                $header_unread_count = $CI_h->Message_model->count_unread($this->session->userdata('user_id'));
                ?>
                <a href="<?= site_url('message/inbox') ?>" class="nav-icon-btn" title="Tin nhắn / Trò chuyện">
                    <i class="fas fa-comment-dots"></i>
                    <?php if ($header_unread_count > 0): ?>
                        <span class="nav-badge bg-danger" id="inboxUnreadBadge"><?= $header_unread_count ?></span>
                    <?php else: ?>
                        <span class="nav-badge bg-danger" id="inboxUnreadBadge" style="display:none;"></span>
                    <?php endif; ?>
                </a>
                <!-- Mong muốn sách -->
                <a href="<?= site_url('wishlist') ?>" class="nav-icon-btn" title="Danh sách mong muốn">
                    <i class="fas fa-heart"></i>
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
                                <i class="fas fa-heart me-2 text-danger"></i>Danh sách mong muốn
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

<?php
$CI_ann =& get_instance();
$CI_ann->load->model('Setting_model');
$site_announcement = $CI_ann->Setting_model->get('site_announcement', 'Chào mừng đến với diễn đàn pass tài liệu của Trường Đại học Sư phạm thành phố Hồ Chí Minh');
$is_home = ($CI_ann->router->fetch_class() === 'trade' && $CI_ann->router->fetch_method() === 'index');
if (!$is_home && !empty(trim($site_announcement))):
?>
<div class="announcement-bar" id="siteAnnouncementBar">
    <div class="d-flex align-items-center me-3" style="position: absolute; left: 16px; background: inherit; z-index: 2; padding-right: 10px;">
        <i class="fas fa-bullhorn text-primary me-2" style="font-size:0.9rem;"></i>
    </div>
    <div class="announcement-marquee">
        <span><?= htmlspecialchars($site_announcement) ?></span>
    </div>
    <button type="button" class="announcement-close" onclick="dismissAnnouncement()" title="Đóng thông báo">
        <i class="fas fa-times"></i>
    </button>
</div>
<script>
function dismissAnnouncement() {
    const bar = document.getElementById('siteAnnouncementBar');
    if (bar) {
        bar.style.display = 'none';
        sessionStorage.setItem('dismiss_announcement', '1');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('dismiss_announcement') === '1') {
        const bar = document.getElementById('siteAnnouncementBar');
        if (bar) bar.style.display = 'none';
    }
});
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
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control form-control-hcmue" name="title" id="isbnBookTitle" required
                                   placeholder="VD: Giáo trình C++ - Lập trình Hướng đối tượng...">
                            <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-1 flex-shrink-0 isbn-scan-btn" 
                                    onclick="openIsbnScanner()" title="Quét mã vạch ISBN để tự động điền thông tin sách">
                                <i class="fas fa-barcode"></i>
                                <span class="d-none d-md-inline">Quét ISBN</span>
                            </button>
                        </div>
                        <!-- Kết quả tra cứu ISBN -->
                        <div id="isbnLookupResult" class="mt-2" style="display:none;"></div>
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
                    <div class="row g-3 mb-3">
                        <div class="col-md-12">
                            <label class="form-label-hcmue">Tình trạng sách *</label>
                            <select class="form-select form-control-hcmue" name="item_condition" required>
                                <option value="used" selected>Đã sử dụng</option>
                                <option value="new">Mới</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-hcmue">Mô tả tình trạng sách</label>
                        <textarea class="form-control form-control-hcmue" name="description" rows="3"
                                  placeholder="Sách còn bao nhiêu %, có ghi chú không, tặng kèm gì..."></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-hcmue">Ảnh bìa sách (Ảnh chính) *</label>
                            <input type="file" class="form-control form-control-hcmue" name="image" accept="image/*" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-hcmue">Ảnh chi tiết khác (Nhiều ảnh)</label>
                            <input type="file" class="form-control form-control-hcmue" name="additional_images[]" accept="image/*" multiple>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label-hcmue">Tài liệu đọc thử PDF (Tùy chọn, tối đa 20MB)</label>
                        <input type="file" class="form-control form-control-hcmue" name="pdf_file" accept="application/pdf">
                        <div class="form-text text-muted" style="font-size:0.75rem;">Đăng kèm file PDF (tối đa 20MB) để người mua đọc thử một vài trang sách trước khi chọn mua.</div>
                    </div>
                    <button type="submit" class="btn btn-primary-hcmue w-100 py-3 fs-6 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Bài Đăng
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!--     MODAL QUÉT MÃ VẠCH ISBN (CAMERA)      -->
<!-- ========================================== -->
<div class="modal fade" id="isbnScannerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:none;border-radius:20px;overflow:hidden;">
            <div class="modal-header border-0 py-3 px-4" style="background:linear-gradient(135deg,#1E3A8A 0%,#2563EB 100%);">
                <h6 class="modal-title text-white fw-bold d-flex align-items-center gap-2 mb-0">
                    <i class="fas fa-barcode"></i> Quét Mã Vạch ISBN
                </h6>
                <button type="button" class="btn-close btn-close-white" onclick="closeIsbnScanner()"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Khung camera -->
                <div id="isbn-scanner-container" style="position:relative;width:100%;min-height:320px;background:#0F172A;">
                    <div id="isbn-reader" style="width:100%;"></div>
                    <!-- Laser scan effect -->
                    <div id="isbn-scan-laser" style="display:none;position:absolute;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#3B82F6,#60A5FA,#3B82F6,transparent);box-shadow:0 0 15px #3B82F6,0 0 40px rgba(59,130,246,0.3);z-index:10;animation:laserScan 2s ease-in-out infinite;"></div>
                </div>
                
                <!-- Trạng thái -->
                <div id="isbn-scan-status" class="text-center py-3 px-4">
                    <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i>Đưa mã vạch ISBN (mặt sau sách) vào khung hình</p>
                </div>
                
                <!-- Nhập ISBN thủ công -->
                <div class="px-4 pb-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <hr class="flex-grow-1" style="border-color:#E2E8F0;"><span class="text-muted small fw-semibold">HOẶC NHẬP THỦ CÔNG</span><hr class="flex-grow-1" style="border-color:#E2E8F0;">
                    </div>
                    <div class="input-group">
                        <input type="text" class="form-control rounded-start-3 border-light shadow-none" id="isbnManualInput" 
                               placeholder="Nhập mã ISBN (VD: 9780131103627)" maxlength="13"
                               style="background:#F8FAFC;">
                        <button class="btn btn-primary-hcmue px-3 fw-bold" type="button" onclick="lookupIsbnManual()" style="border-radius:0 10px 10px 0;">
                            <i class="fas fa-search me-1"></i>Tra cứu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS cho Scanner -->
<style>
@keyframes laserScan {
    0%, 100% { top: 20%; }
    50% { top: 75%; }
}
.isbn-scan-btn {
    border-radius: 10px !important;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 8px 14px;
    border: 1.5px solid var(--primary-mid) !important;
    color: var(--primary-mid) !important;
    transition: all 0.2s ease;
}
.isbn-scan-btn:hover {
    background: var(--primary-mid) !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.25);
}
.isbn-result-card {
    background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%);
    border: 1.5px solid #BFDBFE;
    border-radius: 12px;
    padding: 12px 14px;
    animation: isbnFadeIn 0.4s ease;
}
@keyframes isbnFadeIn {
    from { opacity:0; transform: translateY(-8px); }
    to { opacity:1; transform: translateY(0); }
}
.isbn-result-card .isbn-book-cover {
    width: 50px; height: 68px;
    border-radius: 6px; object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
#isbn-reader video { border-radius: 0 !important; }
#isbn-reader { position: relative; }
#isbn-reader__scan_region { background: #0F172A !important; }
#isbn-reader__dashboard { background: #F8FAFC !important; border-top: 1px solid #E2E8F0; padding: 8px !important; }
#isbn-reader__dashboard button { 
    background: #2563EB !important; color: #fff !important; 
    border: none !important; border-radius: 8px !important; 
    padding: 6px 16px !important; font-weight: 600 !important;
    font-size: 0.82rem !important;
}
#isbn-reader__dashboard select {
    border-radius: 8px !important; border: 1.5px solid #E2E8F0 !important;
    padding: 4px 8px !important; font-size: 0.82rem !important;
}
</style>

<!-- JS Logic Quét ISBN (v2 - Cải thiện quét + Hỗ trợ sách VN) -->
<script>
let isbnHtml5QrCode = null;
let isbnScannerRunning = false;

// Mở Modal scanner
function openIsbnScanner() {
    const modal = new bootstrap.Modal(document.getElementById('isbnScannerModal'));
    modal.show();
    
    // Đợi modal mở xong rồi khởi tạo camera
    document.getElementById('isbnScannerModal').addEventListener('shown.bs.modal', function startScanner() {
        if (isbnScannerRunning) return;
        initIsbnScanner();
        this.removeEventListener('shown.bs.modal', startScanner);
    }, { once: true });
}

// Đóng scanner và giải phóng camera
function closeIsbnScanner() {
    stopIsbnScanner();
    const modalEl = document.getElementById('isbnScannerModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// Khởi tạo camera quét mã vạch (v2: vùng quét rộng hơn, fps cao hơn)
function initIsbnScanner() {
    const readerId = 'isbn-reader';
    
    const config = {
        fps: 15,
        qrbox: function(viewfinderWidth, viewfinderHeight) {
            // Vùng quét chiếm 85% chiều rộng, cao 40% — rất rộng để dễ quét
            return {
                width: Math.floor(viewfinderWidth * 0.85),
                height: Math.floor(viewfinderHeight * 0.4)
            };
        },
        aspectRatio: 1.5,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.ITF
        ]
    };
    
    // Danh sách cấu hình thử nghiệm từ tốt nhất đến cơ bản nhất để tránh lỗi OverconstrainedError/NotFoundError
    const constraintAttempts = [
        // 1. Ưu tiên camera sau chất lượng HD 720p (tốt nhất cho quét mã vạch trên điện thoại)
        {
            facingMode: "environment",
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        // 2. Camera sau với độ phân giải mặc định
        {
            facingMode: "environment"
        },
        // 3. Mặc định (bất kỳ camera nào khả dụng - rất quan trọng đối với laptop chỉ có webcam trước)
        {}
    ];
    
    let attemptIndex = 0;
    let lastError = null;
    
    function attemptStart() {
        if (attemptIndex >= constraintAttempts.length) {
            let errorMsg = '<i class="fas fa-exclamation-triangle me-1 text-warning"></i>Không thể truy cập camera. Hãy nhập ISBN thủ công bên dưới.';
            
            // Phân tích nguyên nhân lỗi cụ thể để hướng dẫn người dùng
            if (lastError) {
                const errName = lastError.name || '';
                const errMsg = lastError.message || '';
                
                if (errName === 'NotAllowedError' || errMsg.toLowerCase().includes('permission') || errMsg.toLowerCase().includes('denied')) {
                    errorMsg = '<i class="fas fa-lock me-1"></i>Trình duyệt đang bị <strong>CHẶN quyền camera</strong>. Hãy nhấp vào biểu tượng 🔒 hoặc 📹 ở thanh địa chỉ để cấp lại quyền!';
                } else if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    errorMsg = '<i class="fas fa-shield-alt me-1"></i>Bảo mật trình duyệt chỉ cho phép bật Camera qua kết nối bảo mật <strong>HTTPS</strong> hoặc <strong>localhost</strong>!';
                } else if (errName === 'NotReadableError' || errMsg.toLowerCase().includes('could not start') || errMsg.toLowerCase().includes('in use')) {
                    errorMsg = '<i class="fas fa-sync fa-spin me-1"></i>Camera đang bị ứng dụng khác sử dụng (Zoom, Meet, v.v.). Vui lòng đóng chúng và thử lại!';
                } else {
                    errorMsg = `<i class="fas fa-exclamation-triangle me-1"></i>Không thể mở camera (${errName || 'Lỗi'}): ${errMsg || 'Vui lòng nhập thủ công'}`;
                }
            }
            
            updateScanStatus(errorMsg, 'text-danger');
            return;
        }
        
        // Sửa lỗi thư viện: Mỗi lần thử phải tạo một đối tượng sạch và xóa DOM cũ
        document.getElementById(readerId).innerHTML = '';
        isbnHtml5QrCode = new Html5Qrcode(readerId);
        
        const currentConstraints = constraintAttempts[attemptIndex];
        console.log(`Đang thử khởi động camera (Cố gắng ${attemptIndex + 1}):`, currentConstraints);
        
        isbnHtml5QrCode.start(
            currentConstraints,
            config,
            onIsbnScanSuccess,
            (errorMessage) => { /* Lỗi quét liên tục - bỏ qua */ }
        ).then(() => {
            isbnScannerRunning = true;
            document.getElementById('isbn-scan-laser').style.display = 'block';
            
            let statusText = '<i class="fas fa-video me-1 text-success"></i>Camera đang hoạt động — giơ mã vạch lên, giữ yên trong 2 giây';
            if (currentConstraints.width) {
                statusText = '<i class="fas fa-video me-1 text-success"></i>Camera HD đang hoạt động — giơ mã vạch lên, giữ yên trong 2 giây';
            }
            updateScanStatus(statusText, 'text-dark');
            
            // Cố gắng cấu hình autofocus/zoom nâng cao sau khi camera đã mở thành công
            try {
                const videoEl = document.querySelector('#isbn-reader video');
                if (videoEl && videoEl.srcObject) {
                    const track = videoEl.srcObject.getVideoTracks()[0];
                    const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                    const constraintsToApply = {};
                    
                    if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                        constraintsToApply.focusMode = 'continuous';
                    }
                    if (capabilities.zoom) {
                        constraintsToApply.zoom = 1.0;
                    }
                    
                    if (Object.keys(constraintsToApply).length > 0) {
                        track.applyConstraints({ advanced: [constraintsToApply] });
                    }
                }
            } catch(e) { 
                console.log('Không thể áp dụng cấu hình camera nâng cao:', e);
            }
        }).catch(err => {
            console.warn(`Thử camera lần ${attemptIndex + 1} thất bại:`, err);
            lastError = err;
            attemptIndex++;
            attemptStart();
        });
    }
    
    attemptStart();
}

// Dừng scanner
function stopIsbnScanner() {
    if (isbnHtml5QrCode && isbnScannerRunning) {
        isbnHtml5QrCode.stop().then(() => {
            isbnScannerRunning = false;
            document.getElementById('isbn-scan-laser').style.display = 'none';
        }).catch(err => console.error('Stop error:', err));
    }
}

// Khi quét thành công
function onIsbnScanSuccess(decodedText, decodedResult) {
    // Chỉ chấp nhận mã có 10 hoặc 13 chữ số (ISBN)
    const isbn = decodedText.replace(/[^0-9X]/gi, '');
    if (isbn.length < 10) return;
    
    // Dừng quét ngay để không bị gọi liên tục
    stopIsbnScanner();
    
    // Hiệu ứng thành công
    updateScanStatus(`<i class="fas fa-check-circle me-1 text-success"></i>Đã quét thành công: <strong>${isbn}</strong> — Đang tra cứu...`, 'text-success fw-bold');
    
    // Gọi chuỗi tra cứu
    fetchBookByIsbn(isbn);
}

// Nhập ISBN thủ công
function lookupIsbnManual() {
    const input = document.getElementById('isbnManualInput');
    const isbn = input.value.trim().replace(/[^0-9X]/gi, '');
    if (isbn.length < 10 || isbn.length > 13) {
        alert('Mã ISBN phải có 10 hoặc 13 ký tự!');
        return;
    }
    updateScanStatus(`<i class="fas fa-spinner fa-spin me-1 text-primary"></i>Đang tra cứu ISBN: <strong>${isbn}</strong>...`, 'text-primary');
    fetchBookByIsbn(isbn);
}

// ═══════════════════════════════════════════════
// CHUỖI TRA CỨU: Google ISBN → Google Query → OpenLibrary → Điền ISBN
// ═══════════════════════════════════════════════

// Bước 1: Google Books tìm chính xác theo ISBN
function fetchBookByIsbn(isbn) {
    fetch(`https://www.googleapis.com/books/v1/volumes?q=isbn:${isbn}&maxResults=1`)
        .then(res => res.json())
        .then(data => {
            if (data.totalItems > 0 && data.items && data.items.length > 0) {
                const book = data.items[0].volumeInfo;
                applyBookDataToForm(book, isbn);
            } else {
                // Bước 2: Thử tìm bằng mã ISBN như một chuỗi tìm kiếm chung
                fetchBookByGoogleQuery(isbn);
            }
        })
        .catch(err => {
            console.error('Google Books ISBN Error:', err);
            fetchBookByGoogleQuery(isbn);
        });
}

// Bước 2: Google Books tìm bằng chuỗi tìm kiếm chung (tìm được nhiều sách VN hơn)
function fetchBookByGoogleQuery(isbn) {
    fetch(`https://www.googleapis.com/books/v1/volumes?q=${isbn}&maxResults=3`)
        .then(res => res.json())
        .then(data => {
            if (data.totalItems > 0 && data.items && data.items.length > 0) {
                // Tìm cuốn sách khớp ISBN nhất
                let bestMatch = data.items[0];
                for (const item of data.items) {
                    const ids = item.volumeInfo.industryIdentifiers || [];
                    if (ids.some(id => id.identifier === isbn)) {
                        bestMatch = item;
                        break;
                    }
                }
                applyBookDataToForm(bestMatch.volumeInfo, isbn);
            } else {
                // Bước 3: Thử OpenLibrary
                fetchBookFromOpenLibrary(isbn);
            }
        })
        .catch(err => {
            console.error('Google Books Query Error:', err);
            fetchBookFromOpenLibrary(isbn);
        });
}

// Bước 3: OpenLibrary API
function fetchBookFromOpenLibrary(isbn) {
    fetch(`https://openlibrary.org/api/books?bibkeys=ISBN:${isbn}&format=json&jscmd=data`)
        .then(res => res.json())
        .then(data => {
            const key = `ISBN:${isbn}`;
            if (data[key]) {
                applyOpenLibraryData(data[key], isbn);
            } else {
                // Bước 4: Thử search OpenLibrary
                fetchBookFromOpenLibrarySearch(isbn);
            }
        })
        .catch(err => {
            fetchBookFromOpenLibrarySearch(isbn);
        });
}

// Bước 4: OpenLibrary Search API
function fetchBookFromOpenLibrarySearch(isbn) {
    fetch(`https://openlibrary.org/search.json?isbn=${isbn}&limit=1`)
        .then(res => res.json())
        .then(data => {
            if (data.numFound > 0 && data.docs && data.docs.length > 0) {
                const doc = data.docs[0];
                const bookInfo = {
                    title: doc.title || '',
                    authors: (doc.author_name || []).join(', '),
                    publisher: (doc.publisher || []).slice(0, 2).join(', '),
                    publishedDate: doc.first_publish_year ? String(doc.first_publish_year) : '',
                    thumbnail: doc.cover_i ? `https://covers.openlibrary.org/b/id/${doc.cover_i}-M.jpg` : '',
                    pageCount: doc.number_of_pages_median || ''
                };
                
                const titleInput = document.querySelector('#createPostModal input[name="title"]');
                const descInput = document.querySelector('#createPostModal textarea[name="description"]');
                
                if (titleInput) titleInput.value = bookInfo.title;
                if (descInput) {
                    let desc = '';
                    if (bookInfo.authors) desc += `📚 Tác giả: ${bookInfo.authors}\n`;
                    if (bookInfo.publisher) desc += `🏢 NXB: ${bookInfo.publisher}\n`;
                    if (bookInfo.publishedDate) desc += `📅 Năm XB: ${bookInfo.publishedDate}\n`;
                    if (bookInfo.pageCount) desc += `📄 Số trang: ${bookInfo.pageCount}\n`;
                    desc += `🔖 ISBN: ${isbn}\n`;
                    descInput.value = desc;
                }
                
                showIsbnResult(bookInfo, isbn);
                closeIsbnScanner();
            } else {
                // Bước cuối: Không tìm thấy — vẫn điền ISBN vào form
                applyIsbnOnlyToForm(isbn);
            }
        })
        .catch(err => {
            applyIsbnOnlyToForm(isbn);
        });
}

// Bước cuối: Khi không API nào tìm thấy — vẫn điền ISBN vào mô tả cho tiện
function applyIsbnOnlyToForm(isbn) {
    const descInput = document.querySelector('#createPostModal textarea[name="description"]');
    if (descInput) {
        const existing = descInput.value.trim();
        const isbnLine = `🔖 ISBN: ${isbn}`;
        descInput.value = existing ? existing + '\n' + isbnLine : isbnLine;
    }
    
    updateScanStatus(`<i class="fas fa-info-circle me-1 text-warning"></i>Không tìm thấy trên cơ sở dữ liệu quốc tế (sách VN thường chưa được đăng ký online). ISBN đã được ghi vào mô tả — bạn tự nhập tên sách nhé!`, 'text-warning');
    
    // Hiện kết quả dạng cảnh báo nhẹ thay vì lỗi đỏ
    const resultDiv = document.getElementById('isbnLookupResult');
    if (resultDiv) {
        resultDiv.innerHTML = `
            <div class="isbn-result-card" style="background:#FFFBEB;border-color:#FDE68A;">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-lightbulb text-warning mt-1"></i>
                    <div>
                        <span class="small fw-semibold text-dark">ISBN <strong>${isbn}</strong> chưa có trên Google Books / OpenLibrary.</span>
                        <div class="text-muted" style="font-size:0.75rem;margin-top:4px;">
                            Đa số sách Việt Nam chưa được đăng ký trên cơ sở dữ liệu quốc tế. ISBN đã được tự động ghi vào phần mô tả — bạn chỉ cần nhập tên sách và tác giả thủ công là được!
                        </div>
                    </div>
                </div>
            </div>
        `;
        resultDiv.style.display = 'block';
    }
    
    closeIsbnScanner();
}

// ═══════════════════════════════════════════════
// ÁP DỤNG DỮ LIỆU VÀO FORM
// ═══════════════════════════════════════════════

// Áp dụng dữ liệu từ Google Books vào form
function applyBookDataToForm(book, isbn) {
    const title = book.title + (book.subtitle ? ': ' + book.subtitle : '');
    const authors = (book.authors || []).join(', ');
    const publisher = book.publisher || '';
    const publishedDate = book.publishedDate || '';
    const description = book.description || '';
    const thumbnail = book.imageLinks ? (book.imageLinks.thumbnail || book.imageLinks.smallThumbnail || '') : '';
    const pageCount = book.pageCount || '';
    
    const titleInput = document.querySelector('#createPostModal input[name="title"]');
    const descInput = document.querySelector('#createPostModal textarea[name="description"]');
    
    if (titleInput) titleInput.value = title;
    if (descInput) {
        let desc = '';
        if (authors) desc += `📚 Tác giả: ${authors}\n`;
        if (publisher) desc += `🏢 NXB: ${publisher}\n`;
        if (publishedDate) desc += `📅 Năm XB: ${publishedDate}\n`;
        if (pageCount) desc += `📄 Số trang: ${pageCount}\n`;
        desc += `🔖 ISBN: ${isbn}\n`;
        if (description) desc += `\n${description.substring(0, 300)}${description.length > 300 ? '...' : ''}`;
        descInput.value = desc;
    }
    
    showIsbnResult({ title, authors, publisher, publishedDate, thumbnail, isbn, pageCount }, isbn);
    closeIsbnScanner();
    updateScanStatus(`<i class="fas fa-check-circle me-1 text-success"></i>Đã tìm thấy và tự động điền thông tin!`, 'text-success');
}

// Áp dụng dữ liệu từ OpenLibrary
function applyOpenLibraryData(book, isbn) {
    const title = book.title || '';
    const authors = (book.authors || []).map(a => a.name).join(', ');
    const publisher = (book.publishers || []).map(p => p.name).join(', ');
    const publishedDate = book.publish_date || '';
    const thumbnail = book.cover ? (book.cover.medium || book.cover.small || '') : '';
    const pageCount = book.number_of_pages || '';
    
    const titleInput = document.querySelector('#createPostModal input[name="title"]');
    const descInput = document.querySelector('#createPostModal textarea[name="description"]');
    
    if (titleInput) titleInput.value = title;
    if (descInput) {
        let desc = '';
        if (authors) desc += `📚 Tác giả: ${authors}\n`;
        if (publisher) desc += `🏢 NXB: ${publisher}\n`;
        if (publishedDate) desc += `📅 Năm XB: ${publishedDate}\n`;
        if (pageCount) desc += `📄 Số trang: ${pageCount}\n`;
        desc += `🔖 ISBN: ${isbn}\n`;
        descInput.value = desc;
    }
    
    showIsbnResult({ title, authors, publisher, publishedDate, thumbnail, isbn, pageCount }, isbn);
    closeIsbnScanner();
}

// ═══════════════════════════════════════════════
// HIỂN THỊ KẾT QUẢ
// ═══════════════════════════════════════════════

function showIsbnResult(bookInfo, isbn) {
    const resultDiv = document.getElementById('isbnLookupResult');
    if (!resultDiv) return;
    
    if (!bookInfo) {
        resultDiv.style.display = 'none';
        return;
    }
    
    const coverHtml = bookInfo.thumbnail 
        ? `<img src="${bookInfo.thumbnail}" class="isbn-book-cover" alt="Bìa sách">` 
        : `<div class="isbn-book-cover d-flex align-items-center justify-content-center" style="background:#E0E7FF;"><i class="fas fa-book text-primary" style="font-size:1.2rem;"></i></div>`;
    
    resultDiv.innerHTML = `
        <div class="isbn-result-card">
            <div class="d-flex gap-3 align-items-start">
                ${coverHtml}
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-bold text-dark" style="font-size:0.88rem;line-height:1.3;">${bookInfo.title}</div>
                    ${bookInfo.authors ? `<div class="text-muted small mt-1"><i class="fas fa-user-edit me-1"></i>${bookInfo.authors}</div>` : ''}
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        ${bookInfo.publisher ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.68rem;"><i class="fas fa-building me-1"></i>${bookInfo.publisher}</span>` : ''}
                        ${bookInfo.publishedDate ? `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.68rem;"><i class="far fa-calendar me-1"></i>${bookInfo.publishedDate}</span>` : ''}
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.68rem;"><i class="fas fa-barcode me-1"></i>${isbn}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light border-0 text-muted flex-shrink-0" onclick="clearIsbnResult()" title="Xóa">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-2 d-flex align-items-center gap-1">
                <i class="fas fa-check-circle text-success" style="font-size:0.7rem;"></i>
                <span class="small text-success fw-semibold">Thông tin đã được tự động điền vào form!</span>
            </div>
        </div>
    `;
    resultDiv.style.display = 'block';
}

function clearIsbnResult() {
    const resultDiv = document.getElementById('isbnLookupResult');
    if (resultDiv) {
        resultDiv.style.display = 'none';
        resultDiv.innerHTML = '';
    }
}

function updateScanStatus(html, className) {
    const statusEl = document.getElementById('isbn-scan-status');
    if (statusEl) {
        statusEl.innerHTML = `<p class="${className || 'text-muted'} small mb-0">${html}</p>`;
    }
}

// Dọn dẹp khi modal bị đóng
document.addEventListener('DOMContentLoaded', function() {
    const scannerModal = document.getElementById('isbnScannerModal');
    if (scannerModal) {
        scannerModal.addEventListener('hidden.bs.modal', function() {
            stopIsbnScanner();
        });
    }
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
