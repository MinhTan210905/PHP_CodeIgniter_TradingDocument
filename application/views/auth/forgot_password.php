<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu | HCMUE Pass Sách</title>
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
        }

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
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.06) 0%, transparent 50%),
                              radial-gradient(circle at 80% 80%, rgba(255,255,255,0.04) 0%, transparent 40%);
        }
        .left-content { position: relative; z-index: 1; width: 100%; max-width: 380px; }
        .left-logo {
            width: 90px; height: 90px; background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.25); border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .left-logo img { width: 55px; height: 55px; object-fit: contain; }
        .left-headline { font-size: 2.8rem; font-weight: 900; color: #ffffff; line-height: 1.15; margin-bottom: 20px; }

        .auth-panel-right {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px; background: #ffffff;
        }
        .auth-form-wrap { width: 100%; max-width: 400px; }
        .auth-logo-top {
            display: none; align-items: center; gap: 12px; margin-bottom: 30px;
        }
        .auth-logo-top img { width: 45px; height: 45px; }
        .auth-logo-top span { font-size: 1.4rem; font-weight: 800; color: var(--primary); }
        .auth-title { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
        .auth-subtitle { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 32px; line-height: 1.5; }

        .form-label { font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: #94A3B8; font-size: 1.1rem; pointer-events: none;
        }
        .form-control {
            height: 52px; padding-left: 48px; border: 2px solid #E2E8F0; border-radius: 14px;
            font-size: 0.95rem; font-weight: 500; color: var(--text-dark); transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary-mid); box-shadow: 0 0 0 4px rgba(37,99,235,0.1); outline: none;
        }
        .btn-login {
            width: 100%; height: 52px; background: var(--primary); color: #fff;
            border: none; border-radius: 14px; font-size: 1rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 4px 12px rgba(30,64,175,0.25); transition: all 0.2s; cursor: pointer;
        }
        .btn-login:hover { background: var(--primary-mid); transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37,99,235,0.3); }

        @media (max-width: 991px) {
            .auth-panel-left { display: none; }
            .auth-panel-right { padding: 30px 20px; }
            .auth-logo-top { display: flex; }
        }
    </style>
</head>
<body>

<div class="auth-panel-left">
    <div class="left-content">
        <div class="left-logo">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo HCMUE">
        </div>
        <h1 class="left-headline">Quên<br>Mật khẩu? 🔑</h1>
        <p class="left-subline" style="color:rgba(255,255,255,0.85);font-size:1.05rem;line-height:1.6;">Đừng lo lắng! Hãy nhập email sinh viên của bạn để nhận mã khôi phục tài khoản.</p>
    </div>
</div>

<div class="auth-panel-right">
    <div class="auth-form-wrap">

        <a href="<?= base_url() ?>" class="auth-logo-top" style="text-decoration:none;">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo">
            <span>HCMUE BookSwap</span>
        </a>

        <a href="<?= site_url('auth/login') ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;color:var(--text-muted);text-decoration:none;margin-bottom:24px;transition:all 0.2s;">
            <i class="fas fa-arrow-left" style="font-size:11px;"></i> Quay lại đăng nhập
        </a>

        <h1 class="auth-title">Khôi phục mật khẩu</h1>
        <p class="auth-subtitle">Vui lòng nhập địa chỉ email bạn đã sử dụng để đăng ký.</p>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('auth/forgot_password_post') ?>" method="POST">
            <div class="mb-4">
                <label class="form-label">Email sinh viên</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" class="form-control" name="email" required
                           placeholder="nguyenvana@student.hcmue.edu.vn">
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-paper-plane"></i> Gửi mã xác nhận
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
