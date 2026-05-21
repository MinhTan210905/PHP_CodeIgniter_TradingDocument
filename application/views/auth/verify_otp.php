<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận mã OTP | HCMUE BookSwap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#1E40AF; --primary-mid:#2563EB; --primary-pale:#EFF6FF; --accent:#F59E0B; --text-dark:#0F172A; --text-muted:#64748B; --border:#E2E8F0; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh; font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 55%, #2563EB 100%);
            display: flex; align-items: center; justify-content: center; padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .bg-dots {
            position: fixed; top:0;left:0;right:0;bottom:0;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 28px 28px; pointer-events:none; z-index:0;
        }
        .auth-card {
            background: rgba(255,255,255,0.97); border-radius: 24px;
            padding: 40px 38px; width: 100%; max-width: 420px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.25);
            position: relative; z-index:1;
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .auth-logo { margin: 0 auto 18px; text-align: center; }
        .auth-logo a { display: block; width: 115px; height: 115px; margin: 0 auto; }
        .auth-logo img { width: 100%; height: 100%; object-fit: contain; transition: opacity 0.2s, transform 0.2s; }
        .auth-logo a:hover img { opacity: 0.8; transform: scale(0.96); }
        .auth-title { font-size:1.5rem;font-weight:800;color:var(--primary);text-align:center;margin-bottom:4px;letter-spacing:-0.3px; }
        .auth-subtitle { font-size:0.88rem;color:var(--text-muted);text-align:center;margin-bottom:24px; line-height:1.5; }
        .form-label { font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:6px; }
        .form-control {
            border:1.5px solid var(--border);border-radius:10px;
            padding:11px 14px;font-size:1.8rem;transition:all 0.2s;
            text-align: center; letter-spacing: 8px; font-weight: bold; color: var(--primary);
        }
        .form-control:focus { border-color:#93C5FD; box-shadow:0 0 0 3px rgba(37,99,235,0.12); background:#fff; }
        .btn-verify {
            background:linear-gradient(135deg,var(--primary),var(--primary-mid));
            color:#fff;border:none;border-radius:12px;
            padding:13px;font-weight:700;font-size:0.95rem;
            width:100%;transition:all 0.25s;cursor:pointer;
            box-shadow:0 4px 16px rgba(37,99,235,0.28);
        }
        .btn-verify:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(37,99,235,0.38); }
        .link-hcmue { color:var(--primary-mid);font-weight:600;text-decoration:none; }
        .link-hcmue:hover { text-decoration:underline; }
        .alert { border-radius:12px;border:none; }
        .resend-area { margin-top: 20px; text-align: center; font-size: 0.85rem; color: var(--text-muted); }
        #resend-btn { background: none; border: none; color: var(--primary-mid); font-weight: 600; cursor: pointer; padding: 0; font-size: 0.85rem; text-decoration: underline; }
        #resend-btn:disabled { color: #CBD5E1; cursor: not-allowed; text-decoration: none; }
        #countdown { font-weight: 700; color: var(--primary); }
    </style>
</head>
<body>
<div class="bg-dots"></div>
<div class="auth-card">
    <div class="auth-logo">
        <a href="<?= base_url() ?>" title="Về trang chủ HCMUE BookSwap">
            <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" alt="Logo HCMUE">
        </a>
    </div>
    <h1 class="auth-title">Xác thực Email</h1>
    <p class="auth-subtitle">
        Chúng tôi đã gửi mã OTP 6 số đến email<br>
        <strong><?= htmlspecialchars($this->session->userdata('pending_reg')['email'] ?? '') ?></strong>
    </p>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mb-3">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('auth/verify_otp_post') ?>" method="POST">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <div class="mb-4">
            <label class="form-label text-center w-100">Nhập mã OTP</label>
            <input type="text" class="form-control" name="otp" id="otp-input" required
                   placeholder="000000" maxlength="6" pattern="\d{6}" autocomplete="one-time-code" inputmode="numeric">
        </div>
        
        <button type="submit" class="btn-verify">
            <i class="fas fa-check-circle me-2"></i>Xác nhận & Hoàn tất Đăng ký
        </button>
    </form>

    <div class="resend-area">
        Chưa nhận được mã?
        <button id="resend-btn" disabled onclick="resendOTP()">
            Gửi lại sau <span id="countdown">5:00</span>
        </button>
    </div>

    <p class="text-center mt-4 mb-0" style="font-size:0.87rem;color:#6B7280;">
        Sai email? <a href="<?= site_url('auth/register') ?>" class="link-hcmue">Quay lại đăng ký</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto focus OTP input
document.getElementById('otp-input').focus();

// Countdown timer 5 phút
let totalSeconds = 300;
const countdownEl = document.getElementById('countdown');
const resendBtn = document.getElementById('resend-btn');

const timer = setInterval(() => {
    totalSeconds--;
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
    countdownEl.textContent = m + ':' + String(s).padStart(2, '0');

    if (totalSeconds <= 0) {
        clearInterval(timer);
        resendBtn.disabled = false;
        resendBtn.innerHTML = 'Gửi lại OTP';
    }
}, 1000);

function resendOTP() {
    window.location.href = '<?= site_url('auth/resend_otp') ?>';
}
</script>
</body>
</html>
