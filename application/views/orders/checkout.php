<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <a href="<?= site_url('orders?tab=buy') ?>" class="btn btn-link text-decoration-none text-muted mb-3 px-0">
                <i class="fas fa-arrow-left me-1"></i> Quay lại đơn hàng
            </a>

            <div class="card border-0 rounded-4 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h4 class="fw-bold" style="color:var(--hcmue-blue);">
                        <i class="fas fa-wallet me-2"></i>Thanh toán đơn hàng
                    </h4>
                    <p class="text-muted" style="font-size:0.9rem;">Mã đơn hàng: <span class="fw-bold text-dark">#<?= $order['id'] ?></span></p>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger rounded-3" style="font-size:0.9rem;">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $this->session->flashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Thông tin sách -->
                    <div class="d-flex gap-3 mb-4 p-3 rounded-3" style="background:#F8FAFC;">
                        <?php
                            $img_src = (!empty($order['image_url']) && file_exists(FCPATH . $order['image_url']))
                                       ? base_url($order['image_url'])
                                       : base_url('assets/images/default_book.jpg');
                        ?>
                        <img src="<?= $img_src ?>" alt="Book" style="width:60px;height:80px;object-fit:cover;border-radius:6px;">
                        <div>
                            <h6 class="fw-bold mb-1"><?= htmlspecialchars($order['post_title']) ?></h6>
                            <div class="text-muted" style="font-size:0.85rem;">
                                Số lượng: <?= $order['quantity'] ?> cuốn
                            </div>
                            <div class="text-danger fw-bold mt-1">
                                <?= number_format($order['price'], 0, ',', '.') ?>đ / cuốn
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 mb-4">

                    <!-- Tổng thanh toán -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold" style="font-size:1.1rem;color:#475569;">Tổng thanh toán:</span>
                        <span class="fw-bold text-danger" style="font-size:1.5rem;"><?= number_format($total_amount, 0, ',', '.') ?>đ</span>
                    </div>

                    <!-- Phương thức thanh toán -->
                    <form action="<?= site_url('orders/process_checkout/'.$order['id']) ?>" method="POST" id="checkoutForm">
                        <div class="p-3 rounded-3 mb-3 payment-method-card" style="border:1.5px solid var(--hcmue-blue);background:#F0F9FF;cursor:pointer;" onclick="selectPayment('wallet')">
                            <div class="d-flex align-items-center mb-2">
                                <input class="form-check-input me-2 mt-0" type="radio" name="payment_method" value="wallet" id="payWallet" checked>
                                <div style="width:32px;height:32px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;margin-right:12px;">
                                    <i class="fas fa-money-check-alt"></i>
                                </div>
                                <h6 class="fw-bold mb-0" style="color:var(--hcmue-blue);">Thanh toán qua Ví HCMUEPay</h6>
                            </div>
                            <div class="ms-5" style="font-size:0.85rem;color:#475569;">
                                Tiền của bạn sẽ được giữ an toàn bởi hệ thống cho đến khi bạn xác nhận "Đã nhận được sách".
                            </div>
                            
                            <div class="ms-5 mt-3 pt-3 wallet-info-box" style="border-top:1px dashed #CBD5E1;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Số dư ví hiện tại:</span>
                                    <span class="fw-bold" style="color:var(--primary);"><?= number_format($wallet['balance'], 0, ',', '.') ?>đ</span>
                                </div>
                                <?php if($wallet['balance'] < $total_amount): ?>
                                    <div class="text-danger mt-2 fw-semibold" style="font-size:0.85rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i>Số dư ví không đủ. Vui lòng nạp thêm tiền!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mb-4 payment-method-card" style="border:1.5px solid #E2E8F0;background:#F8FAFC;cursor:pointer;" onclick="selectPayment('cod')">
                            <div class="d-flex align-items-center mb-2">
                                <input class="form-check-input me-2 mt-0" type="radio" name="payment_method" value="cod" id="payCod">
                                <div style="width:32px;height:32px;background:#64748B;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;margin-right:12px;">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <h6 class="fw-bold mb-0" style="color:#475569;">Giao dịch trực tiếp (COD)</h6>
                            </div>
                            <div class="ms-5" style="font-size:0.85rem;color:#475569;">
                                Giao sách trực tiếp tại trường và trả tiền mặt cho người bán.
                            </div>
                        </div>

                        <!-- Action -->
                        <div id="btnWalletWrap">
                            <?php if($wallet['balance'] >= $total_amount): ?>
                                <button type="submit" class="btn btn-primary-hcmue w-100 rounded-pill py-2 fw-bold" style="font-size:1.05rem;"
                                        onclick="return confirm('Xác nhận thanh toán <?= number_format($total_amount, 0, ',', '.') ?>đ cho đơn hàng này qua ví?');">
                                    <i class="fas fa-check-circle me-2"></i>Xác nhận Thanh toán
                                </button>
                            <?php else: ?>
                                <a href="<?= site_url('wallet') ?>" class="btn w-100 rounded-pill py-2 fw-bold text-white" style="font-size:1.05rem;background:linear-gradient(135deg,#DC2626,#EF4444);">
                                    <i class="fas fa-plus-circle me-2"></i>Nạp tiền vào ví ngay
                                </a>
                                <div class="text-center mt-3 text-muted" style="font-size:0.85rem;">
                                    Bạn còn thiếu <strong><?= number_format($total_amount - $wallet['balance'], 0, ',', '.') ?>đ</strong>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="btnCodWrap" class="d-none">
                            <button type="submit" class="btn w-100 rounded-pill py-2 fw-bold text-white" style="font-size:1.05rem;background:linear-gradient(135deg,#64748B,#475569);"
                                    onclick="return confirm('Bạn xác nhận chọn hình thức Giao dịch trực tiếp?');">
                                <i class="fas fa-hand-holding-usd me-2"></i>Chốt đơn - Giao dịch trực tiếp
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Box hướng dẫn nạp tiền nếu thiếu -->
            <?php if($wallet['balance'] < $total_amount): ?>
                <div class="card border-0 rounded-4 shadow-sm" id="depositHintBox">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-university text-muted mb-2" style="font-size:2rem;"></i>
                        <h6 class="fw-bold">Nạp tiền vào ví HCMUEPay</h6>
                        <p class="text-muted" style="font-size:0.85rem;margin-bottom:1rem;">
                            Chuyển khoản quét mã QR của Admin để nạp tiền vào ví. Giao dịch an toàn, tiền sẽ được hoàn lại nếu mua không thành công!
                        </p>
                        <a href="<?= site_url('wallet') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">
                            Xem hướng dẫn nạp tiền / QR Code Admin
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function selectPayment(method) {
    document.getElementById('payWallet').checked = (method === 'wallet');
    document.getElementById('payCod').checked = (method === 'cod');

    const walletCard = document.getElementById('payWallet').closest('.payment-method-card');
    const codCard = document.getElementById('payCod').closest('.payment-method-card');

    if (method === 'wallet') {
        walletCard.style.borderColor = 'var(--hcmue-blue)';
        walletCard.style.background = '#F0F9FF';
        codCard.style.borderColor = '#E2E8F0';
        codCard.style.background = '#F8FAFC';

        document.getElementById('btnWalletWrap').classList.remove('d-none');
        document.getElementById('btnCodWrap').classList.add('d-none');
        if (document.getElementById('depositHintBox')) document.getElementById('depositHintBox').style.display = 'block';
    } else {
        codCard.style.borderColor = '#475569';
        codCard.style.background = '#F1F5F9';
        walletCard.style.borderColor = '#E2E8F0';
        walletCard.style.background = '#F8FAFC';

        document.getElementById('btnWalletWrap').classList.add('d-none');
        document.getElementById('btnCodWrap').classList.remove('d-none');
        if (document.getElementById('depositHintBox')) document.getElementById('depositHintBox').style.display = 'none';
    }
}
</script>
