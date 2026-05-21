<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | HCMUE BookSwap</title>
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
            display: flex; align-items: stretch;
            background: #F7F8FC;
            -webkit-font-smoothing: antialiased;
        }

        /* LEFT panel (narrow) */
        .auth-panel-left {
            flex: 0 0 42%;
            background: linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 56px 48px;
            position: relative; overflow: hidden;
        }
        .auth-panel-left::after {
            content: '';
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .left-content { position: relative; z-index: 1; width: 100%; max-width: 340px; }
        .left-logo {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.22);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
        }
        .left-logo img { width: 52px; height: 52px; object-fit: contain; }
        .left-headline {
            font-size: 1.75rem; font-weight: 900;
            color: #fff; line-height: 1.2;
            letter-spacing: -0.5px; margin-bottom: 12px;
        }
        .left-subline {
            font-size: 0.88rem; color: rgba(255,255,255,0.68);
            line-height: 1.7; margin-bottom: 32px;
        }
        .step-item {
            display: flex; align-items: flex-start; gap: 12px;
            margin-bottom: 18px;
        }
        .step-num {
            width: 28px; height: 28px; border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.30);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 800; color: #fff;
            flex-shrink: 0; margin-top: 2px;
        }
        .step-text strong { display: block; color: #fff; font-size: 0.85rem; font-weight: 700; }
        .step-text span { color: rgba(255,255,255,0.58); font-size: 0.78rem; }

        /* RIGHT panel */
        .auth-panel-right {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 52px;
            overflow-y: auto;
        }
        .auth-form-wrap { width: 100%; max-width: 400px; }

        .auth-logo-top {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 28px;
        }
        .auth-logo-top img { width: 36px; height: 36px; object-fit: contain; }
        .auth-logo-top span { font-weight: 800; font-size: 0.88rem; color: var(--primary); }

        .auth-title {
            font-size: 1.6rem; font-weight: 900;
            color: var(--text-dark); margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .auth-subtitle { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; }

        .form-label { font-size: 0.80rem; font-weight: 600; color: #374151; margin-bottom: 5px; display: block; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); font-size: 13px; pointer-events: none;
        }
        .form-control {
            width: 100%; padding: 10px 12px 10px 36px;
            border: 1.5px solid var(--border); border-radius: 11px;
            font-size: 0.86rem; font-family: inherit;
            background: #FAFBFC; color: var(--text-dark);
            transition: all 0.2s; outline: none;
        }
        .form-control:focus {
            border-color: #93C5FD;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            background: #fff;
        }

        .btn-register {
            width: 100%; padding: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            color: #fff; border: none; border-radius: 12px;
            font-weight: 700; font-size: 0.92rem; font-family: inherit;
            cursor: pointer; transition: all 0.22s;
            box-shadow: 0 4px 14px rgba(37,99,235,0.28);
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 4px;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.38);
        }

        .divider { display: flex; align-items: center; gap: 12px; color: #CBD5E1; font-size: 0.78rem; margin: 18px 0; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background: var(--border); }

        .alert { border-radius: 12px; border: none; font-size: 0.85rem; }
        .auth-footer-link { font-size: 0.85rem; color: var(--text-muted); text-align: center; }
        .auth-footer-link a { color: var(--primary-mid); font-weight: 700; text-decoration: none; }
        .auth-footer-link a:hover { text-decoration: underline; }

        .section-divider {
            font-size: 0.72rem; font-weight: 700; color: var(--text-muted);
            letter-spacing: 0.8px; text-transform: uppercase;
            margin: 16px 0 10px; padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
        }

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
        <h1 class="left-headline">Tham gia<br>cộng đồng! 🎓</h1>
        <p class="left-subline">Chỉ mất 1 phút để đăng ký và bắt đầu trao đổi sách với hàng trăm sinh viên HCMUE.</p>

        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-text">
                <strong>Tạo tài khoản</strong>
                <span>Điền email sinh viên HCMUE của bạn</span>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-text">
                <strong>Xác thực OTP</strong>
                <span>Mã xác thực gửi đến email trong 5 phút</span>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-text">
                <strong>Bắt đầu BookSwap!</strong>
                <span>Đăng bài, nhắn tin và giao dịch ngay</span>
            </div>
        </div>
    </div>
</div>

<!-- RIGHT PANEL -->
<div class="auth-panel-right">
    <div class="auth-form-wrap">
        <a href="<?= base_url() ?>" class="auth-logo-top" style="text-decoration:none;">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo">
            <span>HCMUE BookSwap</span>
        </a>

        <a href="<?= base_url() ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:0.82rem;color:var(--text-muted);text-decoration:none;margin-bottom:20px;transition:all 0.2s;">
            <i class="fas fa-arrow-left" style="font-size:11px;"></i> Về trang chủ
        </a>

        <h1 class="auth-title">Tạo tài khoản</h1>
        <p class="auth-subtitle">Tham gia ngay — hoàn toàn miễn phí!</p>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('auth/register_post') ?>" method="POST">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <div class="mb-3">
                <label class="form-label">Họ và Tên *</label>
                <div class="input-wrap">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" name="full_name" required placeholder="Nguyễn Văn A">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">Tên đăng nhập *</label>
                    <div class="input-wrap">
                        <i class="fas fa-at input-icon"></i>
                        <input type="text" class="form-control" name="username" required placeholder="nguyenvana">
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">Số điện thoại</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" class="form-control" name="phone" placeholder="0912345678">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email sinh viên *</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#FAFBFC; border: 1.5px solid var(--border); border-right: none; border-radius: 11px 0 0 11px; color: var(--text-muted); font-size: 13px; display: flex; align-items: center; justify-content: center; width: 38px;">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="text" class="form-control" name="email" required placeholder="VD: 4901104131" 
                           style="border-radius: 0; border-left: none; border-right: none; padding-left: 8px; background:#FAFBFC; height: auto;">
                    <span class="input-group-text fw-bold" style="background:#EFF6FF; border: 1.5px solid var(--border); border-left: none; border-radius: 0 11px 11px 0; font-size: 0.83rem; color: var(--primary-mid); display: flex; align-items: center;">
                        @student.hcmue.edu.vn
                    </span>
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-6">
                    <label class="form-label">Mật khẩu *</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="password" required 
                               pattern="^(?=.*[A-Za-z])(?=.*\d).{6,}$" 
                               title="Mật khẩu phải chứa ít nhất 6 ký tự, bao gồm cả chữ cái và chữ số." 
                               placeholder="Ít nhất 6 ký tự, cả chữ và số">
                    </div>
                </div>
                <div class="col-6">
                    <label class="form-label">Xác nhận mật khẩu *</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-control" name="confirm_password" required placeholder="Nhập lại">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i>Tạo tài khoản
            </button>
        </form>

        <div class="divider">hoặc</div>
        <p class="auth-footer-link">
            Đã có tài khoản? <a href="<?= site_url('auth') ?>">Đăng nhập →</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
