<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | HCMUE Pass Sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary     : #1E40AF;
            --primary-mid : #2563EB;
            --primary-pale: #EFF6FF;
            --accent      : #F59E0B;
            --text-dark   : #0F172A;
            --text-muted  : #64748B;
            --border      : #E2E8F0;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0; padding: 0;
            display: flex;
            background: #F7F8FC;
            -webkit-font-smoothing: antialiased;
        }

        /* ── LEFT PANEL ── */
        .auth-panel-left {
            flex: 0 0 52%;
            background: linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 55%, #2563EB 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 56px;
            position: relative;
            overflow: hidden;
        }
        .auth-panel-left::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.04) 0%, transparent 40%);
        }
        .auth-panel-left::after {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
        }
        .left-content { position: relative; z-index: 1; width: 100%; max-width: 380px; }
        .left-logo {
            width: 90px; height: 90px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.25);
            border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
            backdrop-filter: blur(12px);
        }
        .left-logo img { width: 60px; height: 60px; object-fit: contain; }
        .left-headline {
            font-size: 2rem; font-weight: 900;
            color: #fff; line-height: 1.15;
            letter-spacing: -0.6px;
            margin-bottom: 12px;
        }
        .left-subline {
            font-size: 0.95rem; color: rgba(255,255,255,0.72);
            line-height: 1.7; margin-bottom: 36px;
        }
        .left-feature {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }
        .left-feature:last-child { border-bottom: none; }
        .feature-icon {
            width: 40px; height: 40px; border-radius: 12px;
            background: rgba(255,255,255,0.12);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 16px; flex-shrink: 0;
        }
        .feature-text strong { display: block; color: #fff; font-size: 0.87rem; font-weight: 700; }
        .feature-text span { color: rgba(255,255,255,0.60); font-size: 0.78rem; }

        /* Floating book cards decoration */
        .float-card {
            position: absolute;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.80);
            backdrop-filter: blur(8px);
            z-index: 1;
            font-weight: 600;
        }
        .float-card-1 { bottom: 140px; right: 40px; transform: rotate(3deg); }
        .float-card-2 { bottom: 220px; right: 80px; transform: rotate(-2deg); }

        /* ── RIGHT PANEL ── */
        .auth-panel-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
        }
        .auth-form-wrap { width: 100%; max-width: 380px; }

        .auth-logo-top {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 32px;
        }
        .auth-logo-top img { width: 38px; height: 38px; object-fit: contain; }
        .auth-logo-top span { font-weight: 800; font-size: 0.9rem; color: var(--primary); }

        .auth-title {
            font-size: 1.75rem; font-weight: 900;
            color: var(--text-dark); margin-bottom: 6px;
            letter-spacing: -0.5px; line-height: 1.2;
        }
        .auth-subtitle { font-size: 0.87rem; color: var(--text-muted); margin-bottom: 28px; }

        .form-label {
            font-size: 0.82rem; font-weight: 600;
            color: #374151; margin-bottom: 6px; display: block;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px; pointer-events: none;
        }
        .form-control {
            width: 100%; padding: 11px 14px 11px 40px;
            border: 1.5px solid var(--border); border-radius: 12px;
            font-size: 0.88rem; font-family: inherit;
            background: #FAFBFC; color: var(--text-dark);
            transition: all 0.2s; outline: none;
        }
        .form-control:focus {
            border-color: #93C5FD;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            background: #fff;
        }
        .form-control.no-icon { padding-left: 14px; }
        .toggle-pwd {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); cursor: pointer;
            font-size: 14px; background: none; border: none; padding: 0;
        }
        .toggle-pwd:hover { color: var(--primary); }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            color: #fff; border: none; border-radius: 12px;
            font-weight: 700; font-size: 0.95rem; font-family: inherit;
            cursor: pointer; transition: all 0.22s;
            box-shadow: 0 4px 14px rgba(37,99,235,0.28);
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.38);
            filter: brightness(1.06);
        }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; }

        .divider {
            display: flex; align-items: center; gap: 12px;
            color: #CBD5E1; font-size: 0.78rem; margin: 22px 0;
            letter-spacing: 0.5px;
        }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background: var(--border); }

        .alert { border-radius: 12px; border: none; font-size: 0.85rem; }
        .auth-footer-link { font-size: 0.87rem; color: var(--text-muted); text-align: center; }
        .auth-footer-link a { color: var(--primary-mid); font-weight: 700; text-decoration: none; }
        .auth-footer-link a:hover { text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-panel-left { display: none; }
            .auth-panel-right { padding: 32px 24px; }
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="auth-panel-left">
    <div class="left-content">
        <div class="left-logo">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo HCMUE">
        </div>
        <h1 class="left-headline">Chào mừng<br>trở lại! 👋</h1>
        <p class="left-subline">Nền tảng trao đổi sách và tài liệu học tập dành riêng cho sinh viên <strong style="color:#fff;">Đại học Sư phạm TP.HCM</strong>.</p>

        <div class="left-feature">
            <div class="feature-icon"><i class="fas fa-book-open"></i></div>
            <div class="feature-text">
                <strong>Hàng nghìn đầu sách</strong>
                <span>Tất cả môn học, tất cả khoa</span>
            </div>
        </div>
        <div class="left-feature">
            <div class="feature-icon"><i class="fas fa-users"></i></div>
            <div class="feature-text">
                <strong>Cộng đồng sinh viên</strong>
                <span>Kết nối với bạn cùng trường dễ dàng</span>
            </div>
        </div>
        <div class="left-feature">
            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="feature-text">
                <strong>Giao dịch an toàn</strong>
                <span>Đánh giá & xác minh người bán</span>
            </div>
        </div>
    </div>

    <!-- Floating decoration -->
    <div class="float-card float-card-2">📚 Giáo trình CTDL &amp; Giải thuật — 45.000đ</div>
    <div class="float-card float-card-1">✅ Còn sách · Nguyễn Văn A · ⭐ 4.9</div>
</div>

<!-- RIGHT PANEL -->
<div class="auth-panel-right">
    <div class="auth-form-wrap">

        <a href="<?= base_url() ?>" class="auth-logo-top" style="text-decoration:none;">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo">
            <span>HCMUE BookSwap</span>
        </a>

        <a href="<?= base_url() ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;color:var(--text-muted);text-decoration:none;margin-bottom:24px;transition:all 0.2s;">
            <i class="fas fa-arrow-left" style="font-size:11px;"></i> Về trang chủ
        </a>

        <h1 class="auth-title">Đăng nhập</h1>
        <p class="auth-subtitle">Nhập thông tin tài khoản của bạn để tiếp tục.</p>

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
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" class="form-control" name="email" required
                           placeholder="nguyenvana@student.hcmue.edu.vn">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Mật khẩu</label>
                <div class="input-wrap">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" name="password" id="pwd" required
                           placeholder="Nhập mật khẩu của bạn">
                    <button type="button" class="toggle-pwd" onclick="togglePwd()">
                        <i class="fas fa-eye" id="pwd-icon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>Đăng Nhập
            </button>
        </form>

        <div class="divider">hoặc</div>

        <p class="auth-footer-link">
            Chưa có tài khoản? <a href="<?= site_url('auth/register') ?>">Đăng ký ngay →</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd() {
    const pwd  = document.getElementById('pwd');
    const icon = document.getElementById('pwd-icon');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn  = document.getElementById('loginBtn');
    const alertBox = document.getElementById('dynamic-alert');
    const origHTML = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Đang đăng nhập...';
    alertBox.innerHTML = '';

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.href = data.redirect;
        } else {
            alertBox.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            btn.disabled = false;
            btn.innerHTML = origHTML;
        }
    })
    .catch(() => {
        alertBox.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-wifi me-2"></i>Lỗi kết nối, vui lòng thử lại!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        btn.disabled = false;
        btn.innerHTML = origHTML;
    });
});
</script>
</body>
</html>
