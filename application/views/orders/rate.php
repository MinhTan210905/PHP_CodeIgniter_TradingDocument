<?php if ($already_rated): ?>
<div class="container py-4" style="max-width:560px;text-align:center;">
    <i class="fas fa-star" style="font-size:3rem;color:var(--hcmue-gold);"></i>
    <h4 class="fw-bold mt-3 mb-2">Đã đánh giá rồi!</h4>
    <p class="text-muted">Bạn đã đánh giá người bán cho đơn hàng này.</p>
    <a href="<?= site_url('orders') ?>" class="btn btn-primary-hcmue mt-2">Xem đơn hàng</a>
</div>
<?php else: ?>
<div class="container py-4" style="max-width:560px;">
    <div class="card border-0 rounded-4 shadow-sm p-4">
        <div class="text-center mb-4">
            <i class="fas fa-star" style="font-size:2.5rem;color:var(--hcmue-gold);"></i>
            <h4 class="fw-bold mt-2 mb-1">Đánh giá người bán</h4>
            <p class="text-muted" style="font-size:0.88rem;">
                Đơn hàng: <strong><?= htmlspecialchars($order['post_title']) ?></strong><br>
                Người bán: <strong><?= htmlspecialchars($order['seller_name']) ?></strong>
            </p>
        </div>

        <form action="<?= site_url('orders/submit_rating/' . $order['id']) ?>" method="POST">
            <!-- Chọn sao -->
            <div class="mb-4 text-center">
                <label class="form-label-hcmue mb-2">Chất lượng giao dịch</label>
                <div class="star-picker d-flex justify-content-center gap-3" id="starPicker">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="stars" id="star<?= $i ?>" value="<?= $i ?>" class="visually-hidden" required>
                        <label for="star<?= $i ?>" class="star-pick" data-val="<?= $i ?>">
                            <i class="far fa-star" style="font-size:2.2rem;cursor:pointer;color:#CBD5E1;transition:color 0.15s;"></i>
                        </label>
                    <?php endfor; ?>
                </div>
                <div id="starLabel" class="mt-2 fw-bold" style="color:var(--hcmue-gold);font-size:0.9rem;min-height:24px;"></div>
            </div>

            <!-- Nhận xét -->
            <div class="mb-4">
                <label class="form-label-hcmue">Nhận xét (không bắt buộc)</label>
                <textarea class="form-control form-control-hcmue" name="comment" rows="3"
                          placeholder="Sách đúng mô tả, người bán nhiệt tình, giao dịch nhanh..."></textarea>
            </div>

            <button type="submit" class="btn w-100 py-2 fw-bold rounded-3"
                    style="background:var(--hcmue-gold);color:var(--hcmue-blue);font-size:1rem;">
                <i class="fas fa-star me-2"></i>Gửi đánh giá
            </button>
        </form>
    </div>
    <div class="text-center mt-3">
        <a href="<?= site_url('orders') ?>" class="text-muted text-decoration-none" style="font-size:0.85rem;">
            <i class="fas fa-arrow-left me-1"></i>Bỏ qua, về Đơn hàng
        </a>
    </div>
</div>

<script>
const starLabels = ['', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt ⭐'];
document.querySelectorAll('.star-pick').forEach((lbl, idx, all) => {
    lbl.addEventListener('mouseenter', () => {
        all.forEach((l, i) => {
            l.querySelector('i').className = i <= idx ? 'fas fa-star' : 'far fa-star';
            l.querySelector('i').style.color = i <= idx ? '#F5A623' : '#CBD5E1';
        });
        document.getElementById('starLabel').textContent = starLabels[idx + 1];
    });
    lbl.addEventListener('mouseleave', () => {
        const checked = document.querySelector('.star-pick input:checked');
        const val = checked ? parseInt(checked.value) - 1 : -1;
        all.forEach((l, i) => {
            l.querySelector('i').className = i <= val ? 'fas fa-star' : 'far fa-star';
            l.querySelector('i').style.color = i <= val ? '#F5A623' : '#CBD5E1';
        });
        document.getElementById('starLabel').textContent = checked ? starLabels[val + 1] : '';
    });
    lbl.addEventListener('click', () => {
        all.forEach((l, i) => {
            l.querySelector('i').className = i <= idx ? 'fas fa-star' : 'far fa-star';
            l.querySelector('i').style.color = i <= idx ? '#F5A623' : '#CBD5E1';
        });
        document.getElementById('starLabel').textContent = starLabels[idx + 1];
    });
});
</script>
<?php endif; ?>
