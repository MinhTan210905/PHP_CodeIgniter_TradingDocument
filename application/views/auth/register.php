<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | HCMUE Pass Sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --hcmue-blue:#003F8A; --hcmue-blue-mid:#0052B4; --hcmue-gold:#F5A623; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh; font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #003F8A 0%, #0052B4 50%, #1565C0 100%);
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .bg-dots {
            position: fixed; top:0;left:0;right:0;bottom:0;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 28px 28px; pointer-events:none; z-index:0;
        }
        .auth-card {
            background: rgba(255,255,255,0.97); border-radius: 24px;
            padding: 40px 38px; width: 100%; max-width: 480px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.25);
            position: relative; z-index:1;
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .auth-logo {
            width: 70px; height: 70px;
            margin: 0 auto 18px;
            display: flex; align-items: center; justify-content: center;
        }
        .auth-logo img {
            width: 100%; height: 100%;
            object-fit: contain;
        }
        .auth-title { font-size:1.5rem;font-weight:800;color:var(--hcmue-blue);text-align:center;margin-bottom:4px; }
        .auth-subtitle { font-size:0.83rem;color:#6B7280;text-align:center;margin-bottom:24px; }
        .form-label { font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:6px; }
        .form-control {
            border:1.5px solid #E5E9F2;border-radius:10px;
            padding:11px 14px;font-size:0.88rem;transition:all 0.2s;
        }
        .form-control:focus {
            border-color:var(--hcmue-blue-mid);
            box-shadow:0 0 0 3px rgba(0,63,138,0.1);
        }
        .input-group .form-control { border-radius:10px 0 0 10px; }
        .input-group-text {
            border:1.5px solid #E5E9F2;border-left:none;
            border-radius:0 10px 10px 0;background:#F9FAFB;
            cursor:pointer;color:#6B7280;
        }
        .btn-register {
            background:linear-gradient(135deg,var(--hcmue-blue),var(--hcmue-blue-mid));
            color:#fff;border:none;border-radius:12px;
            padding:13px;font-weight:700;font-size:0.95rem;
            width:100%;transition:all 0.25s;
            box-shadow:0 4px 16px rgba(0,63,138,0.3);
        }
        .btn-register:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,63,138,0.4); }
        .link-hcmue { color:var(--hcmue-blue-mid);font-weight:600;text-decoration:none; }
        .link-hcmue:hover { text-decoration:underline; }
        .tip-text { font-size:0.75rem;color:#9CA3AF;margin-top:4px; }
        .alert { border-radius:12px;border:none; }
        .phone-toggle-label {
            display:flex;align-items:center;gap:8px;
            font-size:0.8rem;color:#6B7280;cursor:pointer;
            margin-top:6px;
        }
    </style>
</head>
<body>
<div class="bg-dots"></div>
<div class="auth-card">
    <div class="auth-logo">
        <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo HCMUE">
    </div>
    <h1 class="auth-title">Tạo tài khoản mới</h1>
    <p class="auth-subtitle">Tham gia cộng đồng <strong>HCMUE Pass Sách</strong></p>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('auth/register_post') ?>" method="POST">
        <div class="mb-3">
            <label class="form-label">Họ và Tên *</label>
            <input type="text" class="form-control" name="full_name" required
                   placeholder="Nguyễn Văn A">
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label">Tên đăng nhập *</label>
                <input type="text" class="form-control" name="username" required
                       placeholder="nguyenvana" pattern="[a-z0-9_]+" title="Chỉ dùng chữ thường, số, dấu _">
                <p class="tip-text">Chỉ dùng chữ thường, số, _</p>
            </div>
            <div class="col-6">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" name="phone"
                       placeholder="0912345678">
                <p class="tip-text">Để người mua liên hệ</p>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Email sinh viên (Mã số sinh viên) *</label>
            <div class="input-group">
                <input type="text" class="form-control" name="email_prefix" required
                       placeholder="Ví dụ: 4901104131">
                <span class="input-group-text bg-light text-muted fw-bold">@student.hcmue.edu.vn</span>
            </div>
            <p class="tip-text mt-1">Chỉ cần nhập phần đầu của email (MSSV).</p>
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu *</label>
            <div class="input-group">
                <input type="password" class="form-control" name="password" id="pwd1" required
                       placeholder="Tối thiểu 6 ký tự" minlength="6">
                <span class="input-group-text" onclick="togglePwd('pwd1','icon1')">
                    <i class="fas fa-eye" id="icon1"></i>
                </span>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Xác nhận mật khẩu *</label>
            <div class="input-group">
                <input type="password" class="form-control" name="confirm_password" id="pwd2" required
                       placeholder="Nhập lại mật khẩu" minlength="6">
                <span class="input-group-text" onclick="togglePwd('pwd2','icon2')">
                    <i class="fas fa-eye" id="icon2"></i>
                </span>
            </div>
        </div>
        <button type="submit" class="btn-register">
            <i class="fas fa-user-plus me-2"></i>Tạo Tài Khoản
        </button>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:0.87rem;color:#6B7280;">
        Đã có tài khoản? <a href="<?= site_url('auth') ?>" class="link-hcmue">Đăng nhập</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd(id, iconId) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    if (f.type === 'password') { f.type = 'text'; i.className = 'fas fa-eye-slash'; }
    else { f.type = 'password'; i.className = 'fas fa-eye'; }
}
</script>
</body>
</html>
