<?php $cur_uid = $this->session->userdata('user_id'); ?>

<div class="container py-4" style="max-width:700px;">

    <!-- Header hội thoại -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= site_url('message/inbox') ?>" class="btn btn-light rounded-3 px-3 py-2" style="font-size:0.88rem;">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
        <a href="<?= site_url('seller/' . $other_user['id']) ?>" class="d-flex align-items-center gap-3 text-decoration-none text-dark" style="transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1">
            <div style="width:42px;height:42px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:800;font-size:1rem;flex-shrink:0;">
                <?= strtoupper(substr($other_user['full_name'] ?: $other_user['username'], 0, 1)) ?>
            </div>
            <div>
                <div class="fw-bold" style="font-size:0.95rem;"><?= htmlspecialchars($other_user['full_name'] ?: $other_user['username']) ?></div>
                <div class="text-muted" style="font-size:0.75rem;">@<?= $other_user['username'] ?></div>
            </div>
        </a>
    </div>

    <!-- Messages container -->
    <div class="chat-box card border-0 rounded-4 shadow-sm p-3 mb-3" id="chatBox"
         style="height:440px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:#F8FAFC;">

        <?php if (empty($messages)): ?>
            <div id="noMessagesPlaceholder" style="display:flex;align-items:center;justify-content:center;height:100%;color:#9CA3AF;font-size:0.87rem;">
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
                <?php if ($msg['message_type'] == 'meetup'): ?>
                    <?php
                        $status_bg = '#f8f9fa'; $status_color = '#6c757d'; $status_icon = 'fa-clock'; $status_text = 'Đang chờ xác nhận';
                        if ($msg['meetup_status'] == 'accepted') { $status_bg = '#d1e7dd'; $status_color = '#0f5132'; $status_icon = 'fa-check-circle'; $status_text = 'Đã chấp nhận'; }
                        elseif ($msg['meetup_status'] == 'rejected') { $status_bg = '#f8d7da'; $status_color = '#842029'; $status_icon = 'fa-times-circle'; $status_text = 'Đã từ chối'; }
                    ?>
                    <div class="message-item" data-id="<?= $msg['id'] ?>" style="display:flex;flex-direction:column;align-items:<?= $is_mine ? 'flex-end' : 'flex-start' ?>;">
                        <div class="meetup-card" style="width:270px; background:#fff; border:1px solid #e0e0e0; border-radius:14px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom:4px;">
                            <div style="background:linear-gradient(135deg, var(--hcmue-blue), #1e5ba3); color:#fff; padding:12px 15px; font-weight:bold; font-size:0.9rem; display:flex; align-items:center; gap:8px;">
                                <i class="fas fa-calendar-alt"></i> Hẹn Gặp Giao Sách
                            </div>
                            <div style="padding:15px; font-size:0.85rem; color:#333;">
                                <div class="mb-2 d-flex align-items-start">
                                    <i class="fas fa-map-marker-alt text-danger me-2 mt-1" style="width:16px;"></i> 
                                    <div><strong>Địa điểm:</strong><br><?= htmlspecialchars($msg['meetup_location']) ?></div>
                                </div>
                                <div class="mb-3 d-flex align-items-center">
                                    <i class="fas fa-clock text-primary me-2" style="width:16px;"></i> 
                                    <div><strong>Thời gian:</strong> <?= date('H:i d/m/Y', strtotime($msg['meetup_time'])) ?></div>
                                </div>
                                
                                <div style="background:<?= $status_bg ?>; color:<?= $status_color ?>; padding:6px 10px; border-radius:8px; text-align:center; font-weight:bold; font-size:0.8rem; margin-bottom: <?= (!$is_mine && $msg['meetup_status'] == 'pending') ? '12px' : '0' ?>;">
                                    <i class="fas <?= $status_icon ?> me-1"></i> <?= $status_text ?>
                                </div>

                                <?php if (!$is_mine && $msg['meetup_status'] == 'pending'): ?>
                                    <div class="d-flex gap-2 mt-2">
                                        <button class="btn btn-sm w-50 btn-success fw-bold rounded-3 py-2" onclick="respondMeetup(<?= $msg['id'] ?>, 'accepted')" style="font-size:0.8rem; box-shadow:0 2px 4px rgba(25,135,84,0.3);">
                                            <i class="fas fa-check me-1"></i> Chấp nhận
                                        </button>
                                        <button class="btn btn-sm w-50 btn-danger fw-bold rounded-3 py-2" onclick="respondMeetup(<?= $msg['id'] ?>, 'rejected')" style="font-size:0.8rem; box-shadow:0 2px 4px rgba(220,53,69,0.3);">
                                            <i class="fas fa-times me-1"></i> Từ chối
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span style="font-size:0.68rem;color:#9CA3AF;margin-top:3px;">
                            <?= date('H:i d/m', strtotime($msg['created_at'])) ?>
                            <?php if ($is_mine): ?>
                                <i class="fas fa-<?= $msg['is_read'] ? 'check-double' : 'check' ?> ms-1" style="<?= $msg['is_read'] ? 'color:var(--hcmue-blue)' : '' ?>"></i>
                            <?php endif; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <div class="message-item" data-id="<?= $msg['id'] ?>" style="display:flex;flex-direction:column;align-items:<?= $is_mine ? 'flex-end' : 'flex-start' ?>;">
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
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Form gửi tin nhắn -->
    <form action="<?= site_url('message/send') ?>" method="POST" id="chatForm" class="d-flex gap-2 align-items-end">
        <input type="hidden" name="receiver_id" value="<?= $other_user['id'] ?>">
        <input type="hidden" name="post_id" value="<?= $this->input->get('post_id') ?>">
        
        <button type="button" class="btn btn-light px-3 py-3" style="border-radius:16px; border:1.5px solid #E5E9F2; color:var(--hcmue-blue); flex-shrink:0;" data-bs-toggle="modal" data-bs-target="#meetupModal" title="Lên lịch hẹn gặp">
            <i class="fas fa-calendar-alt"></i>
        </button>

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

<!-- Modal Hẹn Gặp -->
<div class="modal fade" id="meetupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 rounded-top-4" style="background:linear-gradient(135deg, var(--hcmue-blue), #1e5ba3); color:#fff;">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-alt me-2"></i> Lên lịch Hẹn Gặp</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="meetupForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="receiver_id" value="<?= $other_user['id'] ?>">
                    <input type="hidden" name="post_id" value="<?= $this->input->get('post_id') ?>">
                    
                    <div class="alert alert-info rounded-3" style="font-size:0.85rem; background:#E8F0FD; border:none; color:var(--hcmue-blue);">
                        <i class="fas fa-info-circle me-1"></i> Tính năng này giúp hai bên thống nhất thời gian và địa điểm giao nhận sách trong khuôn viên trường.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:0.9rem; color:#333;">Cơ sở / Khu vực giao nhận</label>
                        <select name="location" class="form-select rounded-3 border-2" style="padding:10px 14px;" required>
                            <option value="">-- Chọn địa điểm --</option>
                            <optgroup label="Cơ sở chính (280 An Dương Vương)">
                                <option value="Cơ sở chính - Thư viện">Thư viện</option>
                                <option value="Cơ sở chính - Căn tin">Căn tin</option>
                                <option value="Cơ sở chính - Khu tự học">Khu tự học</option>
                                <option value="Cơ sở chính - Sảnh nhà A">Sảnh nhà A</option>
                                <option value="Cơ sở chính - Bãi xe">Bãi xe</option>
                            </optgroup>
                            <optgroup label="Cơ sở 2 (Lê Văn Sỹ)">
                                <option value="Cơ sở 2 - Sảnh chính">Sảnh chính</option>
                                <option value="Cơ sở 2 - Căn tin">Căn tin</option>
                            </optgroup>
                            <option value="custom">-- Địa điểm khác (Tự nhập thủ công) --</option>
                        </select>
                    </div>

                    <!-- Trường nhập địa điểm tự do -->
                    <div class="mb-3 animate-fade-in" id="customLocationContainer" style="display:none; transition: all 0.3s ease-in-out;">
                        <label class="form-label fw-bold" style="font-size:0.9rem; color:#1e5ba3;"><i class="fas fa-edit me-1"></i> Nhập địa điểm cụ thể khác *</label>
                        <input type="text" id="customLocationInput" class="form-control rounded-3 border-2" style="padding:10px 14px;" placeholder="VD: Trước cổng trường, Ghế đá dãy B, Căn tin lầu 2...">
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" style="font-size:0.9rem; color:#333;">Ngày hẹn</label>
                            <input type="date" name="date" class="form-control rounded-3 border-2" style="padding:10px 14px;" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold" style="font-size:0.9rem; color:#333;">Giờ hẹn</label>
                            <input type="time" name="time" class="form-control rounded-3 border-2" style="padding:10px 14px;" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-hcmue rounded-3 px-4 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Gửi Lịch Hẹn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const currentUserId = <?= json_encode($cur_uid) ?>;
const otherUserId   = <?= json_encode($other_user['id']) ?>;
const siteUrl       = <?= json_encode(site_url()) ?>;
const chatBox       = document.getElementById('chatBox');
const msgInput      = document.getElementById('msgInput');
const chatForm      = document.getElementById('chatForm');

let maxMsgId = 0;

// Tìm ID tin nhắn lớn nhất hiện có
function updateMaxMsgId() {
    document.querySelectorAll('.message-item').forEach(el => {
        const id = parseInt(el.getAttribute('data-id'));
        if (id > maxMsgId) maxMsgId = id;
    });
}
updateMaxMsgId();

// Tự động cuộn xuống cuối
function scrollToBottom() {
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}
scrollToBottom();

// Hàm XSS Escape
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Hàm phân tích định dạng tin nhắn (lọc link Đơn hàng giống PHP)
function formatMessageContent(content) {
    const orderLinkRegex = /https?:\/\/[^\s]+orders\/(detail|rate)\/(\d+)/i;
    const matches = content.match(orderLinkRegex);
    let orderActionType = null;
    let orderId = null;
    
    if (matches) {
        orderActionType = matches[1].toLowerCase();
        orderId = matches[2];
        
        content = content.replace(/https?:\/\/[^\s]+/gi, '');
        content = content.replace('Vào trang Đơn hàng để xác nhận:', '');
        content = content.replace('Hãy liên hệ để hẹn giao nhận sách nhé! Xem chi tiết:', '');
        content = content.replace('Xem chi tiết:', '');
        content = content.replace('Hãy để lại đánh giá cho người bán nhé:', '');
        content = content.trim();
    }
    
    return {
        text: content,
        orderId: orderId,
        orderActionType: orderActionType
    };
}

// Render HTML cho tin nhắn mới nhận được
function renderMessageHTML(msg, isMine) {
    // Xử lý tạo ngày giờ tin nhắn (HH:MM DD/MM)
    let formattedTime = '';
    try {
        const dateObj = new Date(msg.created_at.replace(/-/g, '/')); 
        let hours = dateObj.getHours().toString().padStart(2, '0');
        let minutes = dateObj.getMinutes().toString().padStart(2, '0');
        let day = dateObj.getDate().toString().padStart(2, '0');
        let month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
        formattedTime = `${hours}:${minutes} ${day}/${month}`;
    } catch (e) {
        formattedTime = 'Vừa xong';
    }
    
    const checkIcon = isMine 
        ? `<i class="fas fa-${msg.is_read == 1 ? 'check-double' : 'check'} ms-1" style="${msg.is_read == 1 ? 'color:var(--hcmue-blue)' : ''}"></i>`
        : '';
    const align = isMine ? 'flex-end' : 'flex-start';

    // RENDER: Lịch hẹn
    if (msg.message_type === 'meetup') {
        let statusBg = '#f8f9fa', statusColor = '#6c757d', statusIcon = 'fa-clock', statusText = 'Đang chờ xác nhận';
        if (msg.meetup_status === 'accepted') { statusBg = '#d1e7dd'; statusColor = '#0f5132'; statusIcon = 'fa-check-circle'; statusText = 'Đã chấp nhận'; }
        else if (msg.meetup_status === 'rejected') { statusBg = '#f8d7da'; statusColor = '#842029'; statusIcon = 'fa-times-circle'; statusText = 'Đã từ chối'; }

        let meetupTimeStr = msg.meetup_time; 
        try {
            const mDateObj = new Date(msg.meetup_time.replace(/-/g, '/'));
            let h = mDateObj.getHours().toString().padStart(2, '0');
            let m = mDateObj.getMinutes().toString().padStart(2, '0');
            let d = mDateObj.getDate().toString().padStart(2, '0');
            let mo = (mDateObj.getMonth() + 1).toString().padStart(2, '0');
            let y = mDateObj.getFullYear();
            meetupTimeStr = `${h}:${m} ${d}/${mo}/${y}`;
        } catch(e) {}

        let buttonsHtml = '';
        if (!isMine && msg.meetup_status === 'pending') {
            buttonsHtml = `
                <div class="d-flex gap-2 mt-2">
                    <button class="btn btn-sm w-50 btn-success fw-bold rounded-3 py-2" onclick="respondMeetup(${msg.id}, 'accepted')" style="font-size:0.8rem; box-shadow:0 2px 4px rgba(25,135,84,0.3);">
                        <i class="fas fa-check me-1"></i> Chấp nhận
                    </button>
                    <button class="btn btn-sm w-50 btn-danger fw-bold rounded-3 py-2" onclick="respondMeetup(${msg.id}, 'rejected')" style="font-size:0.8rem; box-shadow:0 2px 4px rgba(220,53,69,0.3);">
                        <i class="fas fa-times me-1"></i> Từ chối
                    </button>
                </div>
            `;
        }
        const bottomMargin = (!isMine && msg.meetup_status === 'pending') ? '12px' : '0';

        return `
        <div class="message-item" data-id="${msg.id}" style="display:flex;flex-direction:column;align-items:${align};">
            <div class="meetup-card" style="width:270px; background:#fff; border:1px solid #e0e0e0; border-radius:14px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.05); margin-bottom:4px;">
                <div style="background:linear-gradient(135deg, var(--hcmue-blue), #1e5ba3); color:#fff; padding:12px 15px; font-weight:bold; font-size:0.9rem; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-calendar-alt"></i> Hẹn Gặp Giao Sách
                </div>
                <div style="padding:15px; font-size:0.85rem; color:#333;">
                    <div class="mb-2 d-flex align-items-start">
                        <i class="fas fa-map-marker-alt text-danger me-2 mt-1" style="width:16px;"></i> 
                        <div><strong>Địa điểm:</strong><br>${escapeHtml(msg.meetup_location)}</div>
                    </div>
                    <div class="mb-3 d-flex align-items-center">
                        <i class="fas fa-clock text-primary me-2" style="width:16px;"></i> 
                        <div><strong>Thời gian:</strong> ${meetupTimeStr}</div>
                    </div>
                    
                    <div style="background:${statusBg}; color:${statusColor}; padding:6px 10px; border-radius:8px; text-align:center; font-weight:bold; font-size:0.8rem; margin-bottom:${bottomMargin};">
                        <i class="fas ${statusIcon} me-1"></i> ${statusText}
                    </div>
                    ${buttonsHtml}
                </div>
            </div>
            <span style="font-size:0.68rem;color:#9CA3AF;margin-top:3px;">
                ${formattedTime} ${checkIcon}
            </span>
        </div>`;
    }

    // RENDER: Text message
    const parsed = formatMessageContent(msg.content);
    const escapedContent = escapeHtml(parsed.text).replace(/\n/g, '<br>');
    
    let orderButtonHtml = '';
    if (parsed.orderId) {
        if (parsed.orderActionType === 'rate') {
            orderButtonHtml = `
                <div class="mt-2 pt-2 border-top" style="border-color:rgba(255,255,255,0.2) !important;">
                    <a href="${siteUrl}orders/rate/${parsed.orderId}" 
                       class="btn btn-sm w-100 rounded-3 fw-bold py-1.5 text-white"
                       style="background: linear-gradient(135deg, #F59E0B, #D97706); 
                              font-size:0.78rem; 
                              border: none;
                              box-shadow: var(--shadow-sm);">
                        <i class="fas fa-star me-1"></i> Đánh giá người bán ngay
                    </a>
                </div>`;
        } else {
            orderButtonHtml = `
                <div class="mt-2 pt-2 border-top" style="border-color:rgba(255,255,255,0.2) !important;">
                    <a href="${siteUrl}orders/detail/${parsed.orderId}" 
                       class="btn btn-sm w-100 rounded-3 fw-bold py-1"
                       style="background:${isMine ? '#fff' : 'var(--hcmue-blue)'}; 
                              color:${isMine ? 'var(--hcmue-blue)' : '#fff'}; 
                              font-size:0.78rem; 
                              border:1px solid rgba(0,0,0,0.05);">
                        <i class="fas fa-shopping-bag me-1"></i> Xem chi tiết Đơn hàng
                    </a>
                </div>`;
        }
    }
    
    const bg = isMine ? 'var(--hcmue-blue)' : '#fff';
    const color = isMine ? '#fff' : '#1A1A2E';
    const radius = isMine ? '18px 18px 6px 18px' : '18px 18px 18px 6px';
    
    return `
        <div class="message-item" data-id="${msg.id}" style="display:flex;flex-direction:column;align-items:${align};">
             <div style="
                max-width:72%;
                background:${bg};
                color:${color};
                border-radius:${radius};
                padding:10px 14px;
                font-size:0.87rem;
                line-height:1.5;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
                word-break:break-word;
            ">
                ${escapedContent}
                ${orderButtonHtml}
            </div>
            <span style="font-size:0.68rem;color:#9CA3AF;margin-top:3px;">
                ${formattedTime}
                ${checkIcon}
            </span>
        </div>`;
}

// Gửi tin nhắn qua AJAX
chatForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const content = msgInput.value.trim();
    if (!content) return;
    
    const formData = new FormData(chatForm);
    
    // Xóa input ngay lập tức để tạo cảm giác phản hồi nhanh
    msgInput.value = '';
    
    fetch(`${siteUrl}message/send_ajax`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            // Gửi thành công -> kích hoạt poll ngay lập tức để lấy tin nhắn vừa gửi
            pollMessages();
        } else {
            alert(data.message || 'Lỗi gửi tin nhắn.');
            msgInput.value = content;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối mạng, vui lòng thử lại.');
        msgInput.value = content;
    });
});

// Lấy tin nhắn mới qua AJAX Polling (sử dụng khi gửi tin nhắn hoặc làm fallback)
function pollMessages() {
    if (document.hidden) return;
    fetch(`${siteUrl}message/poll/${otherUserId}?after_id=${maxMsgId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok' && data.messages && data.messages.length > 0) {
            // Xóa placeholder trống nếu có
            const placeholder = document.getElementById('noMessagesPlaceholder');
            if (placeholder) placeholder.remove();
            
            data.messages.forEach(msg => {
                // Kiểm tra xem tin nhắn đã hiển thị chưa
                if (!document.querySelector(`.message-item[data-id="${msg.id}"]`)) {
                    const isMine = parseInt(msg.sender_id) === parseInt(currentUserId);
                    const html = renderMessageHTML(msg, isMine);
                    chatBox.insertAdjacentHTML('beforeend', html);
                }
            });
            
            updateMaxMsgId();
            scrollToBottom();
        }
    })
    .catch(err => console.warn('Lỗi polling tin nhắn:', err));
}

// KHỞI TẠO PUSHER CHANNELS (REAL-TIME CHAT)
try {
    if (typeof globalPusher !== 'undefined' && globalPusher !== null) {
        const pusher = globalPusher;

    // Đăng ký lắng nghe kênh chat riêng của User hiện tại
    const channel = pusher.subscribe('chat-channel-' + currentUserId);

    channel.bind('new-message', function(data) {
        if (data && data.message) {
            const msg = data.message;
            // Nếu tin nhắn gửi từ đối phương đang trò chuyện cùng
            if (parseInt(msg.sender_id) === parseInt(otherUserId)) {
                // Xóa placeholder trống nếu có
                const placeholder = document.getElementById('noMessagesPlaceholder');
                if (placeholder) placeholder.remove();

                // Kiểm tra xem tin nhắn đã hiển thị chưa để tránh trùng lặp
                if (!document.querySelector(`.message-item[data-id="${msg.id}"]`)) {
                    const html = renderMessageHTML(msg, false);
                    chatBox.insertAdjacentHTML('beforeend', html);
                    updateMaxMsgId();
                    scrollToBottom();
                }

                // Tự động đánh dấu đã đọc ngầm bằng AJAX
                fetch(`${siteUrl}message/mark_read_ajax/${otherUserId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).catch(err => console.warn('Lỗi đánh dấu đã đọc:', err));
            }
        }
    });

    channel.bind('update-message', function(data) {
        if (data && data.message) {
            const msg = data.message;
            const msgItem = document.querySelector(`.message-item[data-id="${msg.id}"]`);
            if (msgItem) {
                const isMine = parseInt(msg.sender_id) === parseInt(currentUserId);
                const html = renderMessageHTML(msg, isMine);
                msgItem.outerHTML = html;
            }
        }
    });
    console.log('Pusher Channels đã kết nối và sẵn sàng nhận tin nhắn thời gian thực!');
} catch (e) {
    console.warn('Lỗi kết nối Pusher WebSocket:', e);
}

// Bật chế độ Polling đồng bộ tự động mỗi 10 giây song song làm fallback dự phòng cho Pusher
setInterval(pollMessages, 10000);

// Xử lý ẩn hiện ô nhập địa điểm tự do
const selectLoc = document.querySelector('#meetupForm select[name="location"]');
const customLocContainer = document.getElementById('customLocationContainer');
const customLocInput = document.getElementById('customLocationInput');

if (selectLoc && customLocContainer && customLocInput) {
    selectLoc.addEventListener('change', function() {
        if (this.value === 'custom') {
            customLocContainer.style.display = 'block';
            customLocInput.required = true;
            customLocInput.focus();
        } else {
            customLocContainer.style.display = 'none';
            customLocInput.required = false;
        }
    });
}

// Xử lý gửi Form Hẹn Gặp
const meetupForm = document.getElementById('meetupForm');
if (meetupForm) {
    meetupForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(meetupForm);
        
        // Nếu chọn địa điểm khác, ghi đè giá trị của custom input vào formData location
        const selectLocation = meetupForm.querySelector('select[name="location"]').value;
        if (selectLocation === 'custom') {
            const customVal = customLocInput.value.trim();
            if (!customVal) {
                alert('Vui lòng nhập địa điểm cụ thể!');
                return;
            }
            formData.set('location', customVal);
        }

        const submitBtn = meetupForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';

        fetch(`${siteUrl}message/send_meetup_ajax`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('meetupModal'));
                if (modal) modal.hide();
                meetupForm.reset();
                if (customLocContainer) {
                    customLocContainer.style.display = 'none';
                    customLocInput.required = false;
                }
                pollMessages(); // Lấy ngay tin nhắn vừa tạo
            } else {
                alert(data.message || 'Lỗi gửi lịch hẹn.');
            }
        })
        .catch(err => alert('Lỗi kết nối mạng.'))
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i> Gửi Lịch Hẹn';
        });
    });
}

// Xử lý nút Chấp nhận/Từ chối Hẹn Gặp
window.respondMeetup = function(msgId, action) {
    // Ẩn nút đi ngay để tránh spam click
    const card = document.querySelector(`.message-item[data-id="${msgId}"]`);
    if (card) {
        const buttonsContainer = card.querySelector('.d-flex.gap-2.mt-2');
        if (buttonsContainer) buttonsContainer.style.opacity = '0.5';
    }

    const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    const csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    
    fetch(`${siteUrl}message/respond_meetup_ajax`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: `message_id=${msgId}&action=${action}&${csrfName}=${csrfHash}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'ok') {
            alert(data.message || 'Có lỗi xảy ra.');
            if (card) {
                const buttonsContainer = card.querySelector('.d-flex.gap-2.mt-2');
                if (buttonsContainer) buttonsContainer.style.opacity = '1';
            }
        } else {
            // Không cần làm gì thêm vì Pusher 'update-message' sẽ trigger và cập nhật UI,
            // cộng với 'new-message' báo notification!
            // Nhỡ mạng lag, gọi poll để chắc ăn:
            pollMessages();
        }
    })
    .catch(err => {
        alert('Lỗi mạng khi phản hồi.');
    });
}

// Nhấn Enter để gửi (trừ khi giữ Shift thì xuống dòng)
msgInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.dispatchEvent(new Event('submit'));
    }
});
</script>

<style>
.form-control:focus { border-color:var(--hcmue-blue-light) !important; box-shadow:0 0 0 3px rgba(0,63,138,0.1) !important; outline:none; }
</style>

