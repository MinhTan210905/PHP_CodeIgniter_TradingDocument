<?php $cur_uid = $this->session->userdata('user_id'); ?>

<div class="container py-4" style="max-width:700px;">

    <!-- Header hội thoại -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= site_url('message/inbox') ?>" class="btn btn-light rounded-3 px-3 py-2" style="font-size:0.88rem;">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
        <div style="width:42px;height:42px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:800;font-size:1rem;flex-shrink:0;">
            <?= strtoupper(substr($other_user['full_name'] ?: $other_user['username'], 0, 1)) ?>
        </div>
        <div>
            <div class="fw-bold" style="font-size:0.95rem;"><?= htmlspecialchars($other_user['full_name'] ?: $other_user['username']) ?></div>
            <div class="text-muted" style="font-size:0.75rem;">@<?= $other_user['username'] ?></div>
        </div>
    </div>

    <!-- Messages container -->
    <div class="chat-box card border-0 rounded-4 shadow-sm p-3 mb-3" id="chatBox"
         style="height:440px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:#F8FAFC;">

        <?php if (empty($messages)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#9CA3AF;font-size:0.87rem;">
                <div class="text-center">
                    <i class="fas fa-comment-dots" style="font-size:2rem;margin-bottom:10px;display:block;"></i>
                    Hãy bắt đầu cuộc trò chuyện!
                </div>
            </div>
        <?php else: ?>
            <?php $prev_post = null; ?>
            <?php foreach($messages as $msg): ?>

                <!-- Thông tin bài đăng liên quan (nếu có, chỉ hiện 1 lần) -->
                <?php if ($msg['post_id_ref'] && $msg['post_title'] && $msg['post_id_ref'] !== $prev_post): ?>
                    <?php $prev_post = $msg['post_id_ref']; ?>
                    <div style="text-align:center;margin:6px 0;">
                        <span style="background:#E8F0FD;color:var(--hcmue-blue);font-size:0.75rem;font-weight:600;padding:4px 14px;border-radius:20px;">
                            <i class="fas fa-book me-1"></i>
                            <a href="<?= site_url('trade/detail/'.$msg['post_id_ref']) ?>" style="color:var(--hcmue-blue);text-decoration:none;">
                                <?= htmlspecialchars($msg['post_title']) ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php 
                $is_mine = ($msg['sender_id'] == $cur_uid); 
                
                // PARSE TỰ ĐỘNG: Lọc link Đơn hàng/Đánh giá để làm giao diện gọn đẹp
                $content = $msg['content'];
                $has_order_link = preg_match('/https?:\/\/[^\s]+orders\/(detail|rate)\/(\d+)/i', $content, $matches);
                
                $order_action_type = null;
                $order_id = null;
                if ($has_order_link) {
                    $order_action_type = strtolower($matches[1]); // 'detail' hoặc 'rate'
                    $order_id = $matches[2];
                    
                    // Xoá bỏ hoàn toàn chuỗi URL thô để giao diện sạch đẹp
                    $content = preg_replace('/https?:\/\/[^\s]+/i', '', $content);
                    // Làm sạch các tiền tố thừa thãi
                    $content = str_replace('Vào trang Đơn hàng để xác nhận:', '', $content);
                    $content = str_replace('Vui lòng liên hệ để thỏa thuận thời gian và địa điểm giao nhận sách. Xem chi tiết:', '', $content);
                    $content = str_replace('Xem chi tiết:', '', $content);
                    $content = str_replace('Hãy để lại đánh giá cho người bán tại đây:', '', $content);
                    $content = trim($content);
                }
                ?>
                <div style="display:flex;flex-direction:column;align-items:<?= $is_mine ? 'flex-end' : 'flex-start' ?>;">
                     <div style="
                        max-width:72%;
                        background:<?= $is_mine ? 'var(--hcmue-blue)' : '#fff' ?>;
                        color:<?= $is_mine ? '#fff' : '#1A1A2E' ?>;
                        border-radius:<?= $is_mine ? '18px 18px 6px 18px' : '18px 18px 18px 6px' ?>;
                        padding:10px 14px;
                        font-size:0.87rem;
                        line-height:1.5;
                        box-shadow:0 2px 8px rgba(0,0,0,0.06);
                        word-break:break-word;
                    ">
                        <?= nl2br(htmlspecialchars($content)) ?>
                        
                        <!-- Nếu có mã Đơn hàng đi kèm, vẽ Nút hành động cực đẹp -->
                        <?php if ($order_id): ?>
                            <div class="mt-2 pt-2 border-top" style="border-color:rgba(255,255,255,0.2) !important;">
                                <?php if ($order_action_type === 'rate'): ?>
                                    <a href="<?= site_url('orders/rate/' . $order_id) ?>" 
                                       class="btn btn-sm w-100 rounded-3 fw-bold py-1.5 text-white"
                                       style="background: linear-gradient(135deg, #F59E0B, #D97706); 
                                              font-size:0.78rem; 
                                              border: none;
                                              box-shadow: var(--shadow-sm);">
                                        <i class="fas fa-star me-1"></i> Đánh giá người bán ngay
                                    </a>
                                <?php else: ?>
                                    <a href="<?= site_url('orders/detail/' . $order_id) ?>" 
                                       class="btn btn-sm w-100 rounded-3 fw-bold py-1"
                                       style="background:<?= $is_mine ? '#fff' : 'var(--hcmue-blue)' ?>; 
                                              color:<?= $is_mine ? 'var(--hcmue-blue)' : '#fff' ?>; 
                                              font-size:0.78rem; 
                                              border:1px solid rgba(0,0,0,0.05);">
                                        <i class="fas fa-shopping-bag me-1"></i> Xem chi tiết Đơn hàng
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:0.68rem;color:#9CA3AF;margin-top:3px;">
                        <?= date('H:i d/m', strtotime($msg['created_at'])) ?>
                        <?php if ($is_mine): ?>
                            <i class="fas fa-<?= $msg['is_read'] ? 'check-double' : 'check' ?> ms-1" style="<?= $msg['is_read'] ? 'color:var(--hcmue-blue)' : '' ?>"></i>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Form gửi tin nhắn -->
    <form action="<?= site_url('message/send') ?>" method="POST" class="d-flex gap-2 align-items-end">
        <input type="hidden" name="receiver_id" value="<?= $other_user['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $this->input->get('post_id') ?>">
        <div class="flex-grow-1">
            <textarea class="form-control" name="content" rows="2" id="msgInput"
                      placeholder="Nhập tin nhắn..." required
                      style="border:1.5px solid #E5E9F2;border-radius:16px;resize:none;font-size:0.9rem;padding:12px 16px;line-height:1.5;"></textarea>
        </div>
        <button type="submit" class="btn btn-primary-hcmue px-3 py-3" style="border-radius:16px;flex-shrink:0;" title="Gửi">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<script>
// Auto scroll xuống cuối
const chatBox = document.getElementById('chatBox');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

// Nhấn Enter để gửi (trừ khi giữ Shift thì xuống dòng)
document.getElementById('msgInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault(); // Ngăn xuống dòng
        if (this.value.trim() !== '') {
            this.form.submit();
        }
    }
});
</script>

<style>
.form-control:focus { border-color:var(--hcmue-blue-light) !important; box-shadow:0 0 0 3px rgba(0,63,138,0.1) !important; outline:none; }
</style>
