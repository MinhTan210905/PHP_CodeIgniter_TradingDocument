<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | HCMUE Pass Sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --hcmue-blue: #003F8A;
            --hcmue-blue-mid: #0052B4;
            --hcmue-gold: #F5A623;
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #003F8A 0%, #0052B4 50%, #1565C0 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.25);
            backdrop-filter: blur(10px);
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .auth-logo a {
            display: block;
            width: 90px; height: 90px;
            margin: 0 auto 20px;
        }
        .auth-logo a img {
            width: 100%; height: 100%;
            object-fit: contain;
            transition: opacity 0.25s ease, transform 0.25s ease;
        }
        .auth-logo a:hover img {
            opacity: 0.75;
            transform: scale(0.95);
        }
        .auth-title {
            font-size: 1.6rem; font-weight: 800;
            color: var(--hcmue-blue); text-align: center;
            margin-bottom: 4px;
        }
        .auth-subtitle {
            font-size: 0.85rem; color: #6B7280;
            text-align: center; margin-bottom: 28px;
        }
        .form-label {
            font-size: 0.83rem; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }
        .form-control {
            border: 1.5px solid #E5E9F2; border-radius: 12px;
            padding: 12px 14px; font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--hcmue-blue-mid);
            box-shadow: 0 0 0 3px rgba(0,63,138,0.1);
        }
        .input-group .form-control { border-radius: 12px 0 0 12px; }
        .input-group-text {
            border: 1.5px solid #E5E9F2;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: #F9FAFB; cursor: pointer;
            color: #6B7280;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--hcmue-blue), var(--hcmue-blue-mid));
            color: #fff; border: none; border-radius: 12px;
            padding: 13px; font-weight: 700; font-size: 0.95rem;
            width: 100%; transition: all 0.25s;
            box-shadow: 0 4px 16px rgba(0,63,138,0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,63,138,0.4);
        }
        .divider {
            display: flex; align-items: center; gap: 12px;
            color: #9CA3AF; font-size: 0.8rem; margin: 20px 0;
        }
        .divider::before, .divider::after {
            content:''; flex:1; height:1px; background:#E5E9F2;
        }
        .alert-danger { border-radius: 12px; border: none; }
        .link-hcmue { color: var(--hcmue-blue-mid); font-weight: 600; text-decoration: none; }
        .link-hcmue:hover { text-decoration: underline; }
        .bg-dots {
            position: fixed; top:0; left:0; right:0; bottom:0;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 28px 28px; pointer-events: none; z-index: 0;
        }
        .auth-card { position: relative; z-index: 1; }
    </style>
</head>
<body>
<div class="bg-dots"></div>

<div class="auth-card">
    <div class="auth-logo">
        <a href="<?= base_url() ?>" title="Về trang chủ HCMUE Pass Sách">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo HCMUE">
        </a>
    </div>
    <h1 class="auth-title">Chào mừng trở lại!</h1>
    <p class="auth-subtitle">Đăng nhập vào <strong>HCMUE Pass Sách</strong></p>

    <div id="dynamic-alert"></div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form id="loginForm" action="<?= site_url('auth/login_post') ?>" method="POST">
        <div class="mb-3">
            <label class="form-label">Email sinh viên</label>
            <input type="email" class="form-control" name="email" required
                   placeholder="vd: nguyenvana@student.hcmue.edu.vn">
        </div>
        <div class="mb-4">
            <label class="form-label">Mật khẩu</label>
            <div class="input-group">
                <input type="password" class="form-control" name="password" id="pwd" required
                       placeholder="Nhập mật khẩu của bạn">
                <span class="input-group-text" onclick="togglePwd()">
                    <i class="fas fa-eye" id="pwd-icon"></i>
                </span>
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
        </button>
    </form>

    <div class="divider">hoặc</div>

    <p class="text-center mb-0" style="font-size:0.88rem; color:#6B7280;">
        Chưa có tài khoản?
        <a href="<?= site_url('auth/register') ?>" class="link-hcmue">Đăng ký ngay</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
    const pwd  = document.getElementById('pwd');
    const icon = document.getElementById('pwd-icon');
    if (pwd.type === 'password') {
        pwd.type = 'text'; icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type = 'password'; icon.className = 'fas fa-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const btn = form.querySelector('.btn-login');
    const originalBtnContent = btn.innerHTML;
    const alertContainer = document.getElementById('dynamic-alert');
    
    // Đang loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...';
    alertContainer.innerHTML = ''; // Xoá thông báo cũ

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Đăng nhập thành công, chuyển hướng
            window.location.href = data.redirect;
        } else {
            // Sai tài khoản mật khẩu: Không load trang, hiện alert đỏ
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
        }
    })
    .catch(error => {
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-wifi me-2"></i>Đã xảy ra lỗi hệ thống, vui lòng thử lại!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        btn.disabled = false;
        btn.innerHTML = originalBtnContent;
    });
});
</script>
</body>
</html>
