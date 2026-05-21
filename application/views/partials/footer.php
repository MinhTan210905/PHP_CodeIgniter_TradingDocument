<footer class="footer-hcmue-dark mt-auto pt-5" style="background: linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%) !important; border-top: none;">
    <div class="container pb-5">
        <div class="row g-4">
            <!-- Cột trái: Thông tin dự án -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= base_url('assets/images/logo_hcmue.png') ?>" 
                         style="width: 90px; height: auto; object-fit: contain;" 
                         alt="HCMUE Logo">
                    <div class="brand-title">HCMUE BookSwap</div>
                </div>
                
                <p class="brand-tagline mt-3">
                    Hệ thống trao đổi, mua bán tài liệu học tập và sách cũ dành riêng cho cộng đồng sinh viên Trường Đại học Sư phạm TP.HCM.
                </p>

                <div class="social-icons mb-4">
                    <a href="javascript:void(0)" class="social-btn" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="javascript:void(0)" class="social-btn" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="javascript:void(0)" class="social-btn" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="javascript:void(0)" class="social-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>

                <div class="info-text d-flex align-items-center gap-2 mb-2" style="color: rgba(255,255,255,0.75);">
                    <i class="fas fa-map-marker-alt" style="width: 16px; color: rgba(255,255,255,0.5);"></i>
                    <span>280 An Dương Vương, Phường 4, Quận 5, TP. Hồ Chí Minh</span>
                </div>
                <div class="info-text d-flex align-items-center gap-2" style="color: rgba(255,255,255,0.75);">
                    <i class="fas fa-envelope" style="width: 16px; color: rgba(255,255,255,0.5);"></i>
                    <span>contact@hcmue.edu.vn</span>
                </div>
            </div>

            <!-- Cột phải: Danh sách mong muốn sách -->
            <div class="col-lg-5">
                <div class="newsletter-box">
                    <h5 class="newsletter-title text-uppercase">Đăng ký nhận tin mới</h5>
                    <p class="newsletter-desc mt-2">
                        Nhận thông báo qua email và tin nhắn khi có người đăng bán sách bạn đang tìm kiếm.
                    </p>
                    
                    <?php if($this->session->userdata('logged_in')): ?>
                        <a href="<?= site_url('wishlist') ?>" class="newsletter-btn-static d-inline-flex align-items-center gap-2 text-decoration-none" style="background:#F59E0B !important; color:#ffffff !important; padding:12px 24px; border-radius:50px; font-weight:700; margin-top:16px; box-shadow: 0 2px 8px rgba(245,158,11,0.25);">
                            <i class="fas fa-bell"></i> Quản lý danh sách mong muốn
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('auth') ?>" class="newsletter-btn-static d-inline-flex align-items-center gap-2 text-decoration-none" style="background:#F59E0B !important; color:#ffffff !important; padding:12px 24px; border-radius:50px; font-weight:700; margin-top:16px; box-shadow: 0 2px 8px rgba(245,158,11,0.25);">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập để sử dụng
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div style="font-style: italic;">Phát triển để hỗ trợ cộng đồng sinh viên HCMUE.</div>
                <div>&copy; <?= date('Y') ?> HCMUE. All rights reserved.</div>
            </div>
        </div>
    </div>
</footer>

<!-- Poor Man's Cron: Chạy ngầm xử lý đơn hàng/đánh giá quá hạn 24h -->
<script>
    setTimeout(function() {
        fetch('<?= site_url('cron/run') ?>', { method: 'GET' })
            .catch(err => console.log('Cron err:', err));
    }, 5000); // Đợi 5 giây sau khi tải trang để không ảnh hưởng tốc độ load
</script>
