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
                    <span>280 An Dương Vương, Phường Chợ Quán, TP.HCM</span>
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
                    
                    <form action="<?= current_url() ?>" method="POST" class="newsletter-form">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                        <input type="email" class="newsletter-input" 
                               placeholder="Email của bạn..." required>
                        <button type="submit" class="newsletter-btn">
                            Đăng ký
                        </button>
                    </form>
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

<?php /* FIX CSRF: Tự động thêm CSRF token vào tất cả form POST chưa có token */ ?>
<script>
(function() {
    var tokenName  = '<?= $this->security->get_csrf_token_name() ?>';
    var tokenValue = '<?= $this->security->get_csrf_hash() ?>';
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function(form) {
        if (!form.querySelector('input[name="' + tokenName + '"]')) {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = tokenName;
            input.value = tokenValue;
            form.appendChild(input);
        }
    });
})();
</script>

<?php if ($this->session->userdata('logged_in')): ?>
<!-- ========================================== -->
<!--     HCMUE BOOKSWAP FLOATING CHAT WIDGET    -->
<!-- ========================================== -->
<style>
/* CSS Reset & Variable fallback */
.floating-chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1050;
    font-family: 'Inter', sans-serif;
}
.floating-chat-trigger {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 4px 16px rgba(30,64,175,0.35);
    border: none;
    cursor: pointer;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}
.floating-chat-trigger:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 22px rgba(30,64,175,0.45);
}
.floating-chat-trigger .badge-count {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #EF4444;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.floating-chat-window {
    position: absolute;
    bottom: 76px;
    right: 0;
    width: 380px;
    height: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(15,23,42,0.15);
    display: none;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(20px) scale(0.95);
    opacity: 0;
    transform-origin: bottom right;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0,0,0,0.06);
}
.floating-chat-window.open {
    display: flex;
    transform: translateY(0) scale(1);
    opacity: 1;
}
.floating-chat-header {
    background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%);
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.floating-chat-header-user {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow: hidden;
}
.floating-chat-header-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    overflow: hidden;
    flex-shrink: 0;
}
.floating-chat-header-title {
    font-size: 0.95rem;
    font-weight: 700;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    letter-spacing: -0.2px;
}
.floating-chat-header-close, .floating-chat-header-back {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.85);
    font-size: 16px;
    cursor: pointer;
    outline: none;
    transition: color 0.2s;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.floating-chat-header-close:hover, .floating-chat-header-back:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
}
.floating-chat-body {
    flex-grow: 1;
    overflow-y: auto;
    background: #F8FAFC;
    padding: 12px;
    display: flex;
    flex-direction: column;
}
.floating-chat-inbox-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.floating-chat-inbox-item {
    background: #fff;
    border-radius: 12px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #F1F5F9;
}
.floating-chat-inbox-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.03);
    border-color: #E2E8F0;
    background: #FAFBFC;
}
.floating-chat-inbox-item.unread {
    background: #EFF6FF;
    border-color: #BFDBFE;
}
.floating-chat-inbox-item.pinned {
    background: #FFFDF5;
    border-left: 4px solid #F59E0B !important;
}
.floating-chat-inbox-item.pinned:hover {
    background: #FFF9E6;
}
.floating-chat-inbox-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1E40AF, #3B82F6);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.95rem;
    box-shadow: 0 2px 6px rgba(30,64,175,0.1);
    flex-shrink: 0;
    overflow: hidden;
}
.floating-chat-inbox-info {
    flex-grow: 1;
    overflow: hidden;
}
.floating-chat-inbox-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: #1E293B;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.floating-chat-inbox-time {
    font-size: 0.68rem;
    font-weight: 400;
    color: #94A3B8;
}
.floating-chat-inbox-snippet {
    font-size: 0.78rem;
    color: #64748B;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.floating-chat-inbox-item.unread .floating-chat-inbox-snippet {
    font-weight: 700;
    color: #1E293B;
}
.floating-chat-inbox-badge {
    background: #2563EB;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.floating-chat-messages-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-grow: 1;
    overflow-y: auto;
}
.floating-chat-msg-bubble {
    max-width: 80%;
    padding: 8px 12px;
    border-radius: 14px;
    font-size: 0.8rem;
    line-height: 1.4;
    word-break: break-word;
}
.floating-chat-msg-bubble.sent {
    align-self: flex-end;
    background: #2563EB;
    color: #fff;
    border-bottom-right-radius: 3px;
    box-shadow: 0 2px 6px rgba(37,99,235,0.15);
}
.floating-chat-msg-bubble.received {
    align-self: flex-start;
    background: #F1F5F9;
    color: #1E293B;
    border-bottom-left-radius: 3px;
    border: 1px solid #E2E8F0;
}
.floating-chat-input-container {
    padding: 10px 12px;
    border-top: 1px solid #F1F5F9;
    background: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.floating-chat-input {
    flex-grow: 1;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 0.83rem;
    outline: none;
    transition: border-color 0.2s;
}
.floating-chat-input:focus {
    border-color: #2563EB;
}
.floating-chat-send-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #2563EB;
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: background 0.2s, transform 0.2s;
    outline: none;
    flex-shrink: 0;
}
.floating-chat-send-btn:hover {
    background: #1D4ED8;
    transform: scale(1.05);
}
</style>

<div class="floating-chat-widget" id="floatingChatWidget">
    <!-- Cửa sổ Chat -->
    <div class="floating-chat-window" id="floatingChatWindow">
        <!-- Header -->
        <div class="floating-chat-header">
            <div class="floating-chat-header-user">
                <button class="floating-chat-header-back" id="floatingChatHeaderBack" style="display:none;" onclick="backToInbox()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="floating-chat-header-avatar" id="floatingChatHeaderAvatar" style="display:none;"></div>
                <div class="floating-chat-header-title" id="floatingChatTitle">Hộp thư</div>
            </div>
            <button class="floating-chat-header-close" onclick="toggleFloatingChat()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="floating-chat-body" id="floatingChatBody">
            <!-- View 1: Inbox list -->
            <div class="floating-chat-inbox-list" id="floatingChatInboxList"></div>
            
            <!-- View 2: Conversation detail -->
            <div class="floating-chat-messages-container" id="floatingChatMessages" style="display:none;"></div>
        </div>

        <!-- Input gửi tin nhắn (Chỉ hiện khi ở View 2) -->
        <div class="floating-chat-input-container" id="floatingChatInputContainer" style="display:none;">
            <input type="text" class="floating-chat-input" id="floatingChatInput" placeholder="Nhập tin nhắn..." onkeydown="handleChatInputKey(event)">
            <button class="floating-chat-send-btn" onclick="sendFloatingMessage()">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <!-- Bong bóng trigger -->
    <button class="floating-chat-trigger" id="floatingChatTriggerBtn" onclick="toggleFloatingChat()">
        <i class="fas fa-comment-dots"></i>
        <span class="badge-count" id="floatingChatUnreadBadge" style="display:none;">0</span>
    </button>
</div>

<script>
let floatingChatOpen = false;
let currentChatUserId = null;
let lastMessageId = 0;
let floatingPollInterval = null;
const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
let csrfHash = '<?= $this->security->get_csrf_hash() ?>';

// Bật/tắt mở khung chat
function toggleFloatingChat() {
    const windowEl = document.getElementById('floatingChatWindow');
    if (!windowEl) return;

    floatingChatOpen = !floatingChatOpen;
    if (floatingChatOpen) {
        // Tức thời ẩn thông báo chưa đọc khi mở hộp thoại
        const floatingBadge = document.getElementById('floatingChatUnreadBadge');
        if (floatingBadge) floatingBadge.style.display = 'none';

        windowEl.style.display = 'flex';
        // Kích hoạt animation
        setTimeout(() => {
            windowEl.classList.add('open');
        }, 10);
        
        // Nếu chưa ở trong phòng chat cụ thể, load inbox
        if (!currentChatUserId) {
            loadFloatingInbox();
        } else {
            // Nếu đang trong phòng chat, cuộn xuống dưới cùng
            scrollToFloatingChatBottom();
        }
    } else {
        windowEl.classList.remove('open');
        setTimeout(() => {
            windowEl.style.display = 'none';
        }, 300);
    }
}

// Quay lại hòm thư
function backToInbox() {
    currentChatUserId = null;
    if (floatingPollInterval) {
        clearInterval(floatingPollInterval);
        floatingPollInterval = null;
    }
    
    document.getElementById('floatingChatTitle').innerText = 'Hộp thư';
    document.getElementById('floatingChatHeaderBack').style.display = 'none';
    document.getElementById('floatingChatHeaderAvatar').style.display = 'none';
    document.getElementById('floatingChatInboxList').style.display = 'flex';
    document.getElementById('floatingChatMessages').style.display = 'none';
    document.getElementById('floatingChatInputContainer').style.display = 'none';
    
    loadFloatingInbox();
}

// Tải danh sách hội thoại
function loadFloatingInbox() {
    const listBody = document.getElementById('floatingChatInboxList');
    if (!listBody) return;
    
    listBody.innerHTML = `
        <div style="text-align:center; padding: 40px 0; color:#94A3B8;">
            <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary" style="color:#2563EB !important;"></i>
            <p class="small mb-0">Đang tải cuộc hội thoại...</p>
        </div>
    `;

    fetch('<?= site_url("message/get_conversations_ajax") ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            renderFloatingInbox(data.conversations);
        } else {
            listBody.innerHTML = `<div style="text-align:center; padding: 40px 0; color:#EF4444; font-size:0.8rem;">Không thể tải hội thoại!</div>`;
        }
    })
    .catch(err => {
        listBody.innerHTML = `<div style="text-align:center; padding: 40px 0; color:#EF4444; font-size:0.8rem;">Lỗi kết nối mạng!</div>`;
    });
}

// Render danh sách hội thoại
function renderFloatingInbox(conversations) {
    const listBody = document.getElementById('floatingChatInboxList');
    if (!listBody) return;

    if (conversations.length === 0) {
        listBody.innerHTML = `
            <div style="text-align:center; padding: 50px 20px; color:#94A3B8;">
                <i class="fas fa-comment-slash fa-2x mb-2" style="color:#CBD5E1;"></i>
                <p class="small mb-0">Chưa có cuộc hội thoại nào.</p>
            </div>
        `;
        return;
    }

    let html = '';
    // Sắp xếp các cuộc trò chuyện đã ghim lên đầu tiên
    conversations.sort((a, b) => {
        const aPinned = a.is_pinned == 1 ? 1 : 0;
        const bPinned = b.is_pinned == 1 ? 1 : 0;
        if (aPinned !== bPinned) return bPinned - aPinned;
        return new Date(b.created_at) - new Date(a.created_at);
    });

    conversations.forEach(c => {
        const unreadClass = c.unread_count > 0 ? 'unread' : '';
        const pinnedClass = c.is_pinned == 1 ? 'pinned' : '';
        const unreadBadge = c.unread_count > 0 ? `<span class="floating-chat-inbox-badge">${c.unread_count}</span>` : '';
        const avatar = c.avatar_url 
            ? `<img src="${c.avatar_url}" style="width:100%;height:100%;object-fit:cover;">` 
            : c.initial;

        html += `
            <div class="floating-chat-inbox-item ${unreadClass} ${pinnedClass} position-relative d-flex align-items-center justify-content-between py-2.5" 
                 data-id="${c.other_user_id}"
                 onclick="handleFloatingInboxClick(event, ${c.other_user_id}, '${c.full_name_escaped}', '${c.avatar_url}')">
                
                <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1" style="min-width:0;">
                    <div class="floating-chat-inbox-avatar" style="${c.is_pinned == 1 ? 'background:linear-gradient(135deg, #F59E0B, #D97706);' : ''}">${avatar}</div>
                    <div class="floating-chat-inbox-info overflow-hidden">
                        <div class="floating-chat-inbox-name">
                            <span class="d-inline-flex align-items-center gap-1 overflow-hidden text-truncate fw-bold">
                                ${c.full_name_escaped}
                                ${c.is_pinned == 1 ? '<i class="fas fa-thumbtack text-warning" style="font-size:0.68rem;" title="Đã ghim"></i>' : ''}
                                ${c.is_muted == 1 ? '<i class="fas fa-bell-slash text-muted" style="font-size:0.68rem;" title="Đã tắt tiếng"></i>' : ''}
                            </span>
                            <span class="floating-chat-inbox-time">${c.time_str}</span>
                        </div>
                        <div class="floating-chat-inbox-snippet">
                            ${c.sender_id == <?= $this->session->userdata('user_id') ?> ? 'Bạn: ' : ''}${c.content_escaped}
                        </div>
                    </div>
                </div>

                <!-- Dropdown Hành động Ba chấm -->
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0 ms-2" onclick="event.stopPropagation()">
                    ${unreadBadge}
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" 
                                style="border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; background:transparent;">
                            <i class="fas fa-ellipsis-v" style="font-size:0.75rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 px-1 mt-1" style="min-width: 140px; font-size: 0.76rem; z-index: 1060;">
                            ${c.unread_count > 0 ? `
                            <li>
                                <button class="dropdown-item py-1.5 px-3 rounded-2" onclick="floatingMarkAsRead(event, ${c.other_user_id})">
                                    <i class="fas fa-check-double text-success me-1.5" style="width:12px;"></i>Đã đọc
                                </button>
                            </li>` : ''}
                            <li>
                                <button class="dropdown-item py-1.5 px-3 rounded-2" onclick="floatingTogglePin(event, ${c.other_user_id})">
                                    <i class="fas fa-thumbtack text-warning me-1.5" style="width:12px;"></i>
                                    <span>${c.is_pinned == 1 ? 'Bỏ ghim' : 'Ghim'}</span>
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item py-1.5 px-3 rounded-2" onclick="floatingToggleMute(event, ${c.other_user_id})">
                                    <i class="fas ${c.is_muted == 1 ? 'fa-bell text-primary' : 'fa-bell-slash text-muted'} me-1.5" style="width:12px;"></i>
                                    <span>${c.is_muted == 1 ? 'Bật thông báo' : 'Tắt thông báo'}</span>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <button class="dropdown-item py-1.5 px-3 rounded-2 text-danger" onclick="floatingDeleteConversation(event, ${c.other_user_id})">
                                    <i class="fas fa-trash-alt me-1.5" style="width:12px;"></i>Xóa trò chuyện
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
    });

    listBody.innerHTML = html;
}

// Mở phòng chat cụ thể
function openFloatingConversation(otherId, fullName, avatarUrl) {
    currentChatUserId = otherId;
    lastMessageId = 0;
    
    // Cập nhật Header
    document.getElementById('floatingChatTitle').innerText = fullName;
    document.getElementById('floatingChatHeaderBack').style.display = 'flex';
    
    const headerAvatar = document.getElementById('floatingChatHeaderAvatar');
    headerAvatar.style.display = 'flex';
    if (avatarUrl) {
        headerAvatar.innerHTML = `<img src="${avatarUrl}" style="width:100%;height:100%;object-fit:cover;">`;
    } else {
        headerAvatar.innerHTML = fullName.charAt(0).toUpperCase();
    }
    
    // Chuyển View
    document.getElementById('floatingChatInboxList').style.display = 'none';
    document.getElementById('floatingChatMessages').style.display = 'flex';
    document.getElementById('floatingChatInputContainer').style.display = 'flex';
    
    const messagesBody = document.getElementById('floatingChatMessages');
    messagesBody.innerHTML = `
        <div style="text-align:center; padding: 60px 0; color:#94A3B8; margin: auto;">
            <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary" style="color:#2563EB !important;"></i>
            <p class="small mb-0">Đang tải tin nhắn...</p>
        </div>
    `;

    fetch(`<?= site_url("message/get_messages_ajax/") ?>${otherId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            renderFloatingMessages(data.messages);
            scrollToFloatingChatBottom();
            
            // Thiết lập Polling tin nhắn mỗi 3s
            if (floatingPollInterval) clearInterval(floatingPollInterval);
            floatingPollInterval = setInterval(pollFloatingNewMessages, 3000);
            
            // Đồng bộ lại Badge unread
            syncUnreadCount();
        } else {
            messagesBody.innerHTML = `<div style="text-align:center; padding: 40px 0; color:#EF4444; font-size:0.8rem; margin:auto;">Không thể tải tin nhắn!</div>`;
        }
    })
    .catch(err => {
        messagesBody.innerHTML = `<div style="text-align:center; padding: 40px 0; color:#EF4444; font-size:0.8rem; margin:auto;">Lỗi kết nối mạng!</div>`;
    });
}

// Render tin nhắn trong phòng chat
function renderFloatingMessages(messages) {
    const messagesBody = document.getElementById('floatingChatMessages');
    if (!messagesBody) return;

    if (messages.length === 0) {
        messagesBody.innerHTML = `
            <div style="text-align:center; padding: 40px 10px; color:#94A3B8; margin: auto; font-size: 0.8rem;">
                Hãy gửi tin nhắn để bắt đầu cuộc trò chuyện.
            </div>
        `;
        return;
    }

    let html = '';
    messages.forEach(m => {
        const sideClass = m.sender_id == <?= $this->session->userdata('user_id') ?> ? 'sent' : 'received';
        html += `
            <div class="floating-chat-msg-bubble ${sideClass}" data-msg-id="${m.id}">
                ${m.content_escaped || escapeHtml(m.content)}
            </div>
        `;
        lastMessageId = Math.max(lastMessageId, parseInt(m.id));
    });

    messagesBody.innerHTML = html;
}

// Nhận tin nhắn mới ngầm (realtime polling)
function pollFloatingNewMessages() {
    if (!currentChatUserId || !floatingChatOpen) return;

    fetch(`<?= site_url("message/poll_messages/") ?>${currentChatUserId}?after_id=${lastMessageId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok' && data.messages.length > 0) {
            appendFloatingNewMessages(data.messages);
            scrollToFloatingChatBottom();
            syncUnreadCount();
        }
    });
}

// Nối tiếp tin nhắn mới
function appendFloatingNewMessages(messages) {
    const messagesBody = document.getElementById('floatingChatMessages');
    if (!messagesBody) return;

    // Xoá dòng chữ mặc định nếu có
    if (messagesBody.innerText.includes('Hãy gửi tin nhắn')) {
        messagesBody.innerHTML = '';
    }

    messages.forEach(m => {
        // Tránh trùng tin nhắn
        if (document.querySelector(`[data-msg-id="${m.id}"]`)) return;

        const sideClass = m.sender_id == <?= $this->session->userdata('user_id') ?> ? 'sent' : 'received';
        const bubble = document.createElement('div');
        bubble.className = `floating-chat-msg-bubble ${sideClass}`;
        bubble.setAttribute('data-msg-id', m.id);
        bubble.innerText = m.content;
        messagesBody.appendChild(bubble);

        lastMessageId = Math.max(lastMessageId, parseInt(m.id));
    });
}

// Gửi tin nhắn mới
function sendFloatingMessage() {
    const input = document.getElementById('floatingChatInput');
    const content = input.value.trim();
    if (!content || !currentChatUserId) return;

    input.value = '';

    const formData = new FormData();
    formData.append('receiver_id', currentChatUserId);
    formData.append('content', content);
    formData.append(csrfName, csrfHash);

    // Hiển thị tin nhắn tạm thời lên màn hình ngay lập tức để có cảm giác mượt mà (Optimistic UI)
    const messagesBody = document.getElementById('floatingChatMessages');
    if (messagesBody.innerText.includes('Hãy gửi tin nhắn')) messagesBody.innerHTML = '';
    
    const tempBubble = document.createElement('div');
    tempBubble.className = 'floating-chat-msg-bubble sent';
    tempBubble.style.opacity = '0.6';
    tempBubble.innerText = content;
    messagesBody.appendChild(tempBubble);
    scrollToFloatingChatBottom();

    fetch('<?= site_url("message/send_ajax") ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            // Xoá bubble tạm thời và lấy tin nhắn thật
            tempBubble.remove();
            pollFloatingNewMessages();
        } else {
            tempBubble.remove();
            alert(data.message || 'Lỗi gửi tin nhắn!');
        }
    })
    .catch(err => {
        tempBubble.remove();
        alert('Lỗi kết nối mạng khi gửi tin nhắn!');
    });
}

// Xử lý phím Enter trong ô input
function handleChatInputKey(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        sendFloatingMessage();
    }
}

// Cuộn tin nhắn xuống cuối cùng
function scrollToFloatingChatBottom() {
    const messagesBody = document.getElementById('floatingChatMessages');
    if (messagesBody) {
        messagesBody.scrollTop = messagesBody.scrollHeight;
    }
}

// Đồng bộ tổng số lượng tin nhắn chưa đọc
function syncUnreadCount() {
    fetch('<?= site_url("message/total_unread") ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            const count = data.count;
            const badge = document.getElementById('inboxUnreadBadge');
            const floatingBadge = document.getElementById('floatingChatUnreadBadge');
            
            if (badge) {
                if (count > 0) {
                    badge.innerText = count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
            if (floatingBadge) {
                if (count > 0) {
                    floatingBadge.innerText = count;
                    floatingBadge.style.display = 'flex';
                } else {
                    floatingBadge.style.display = 'none';
                }
            }
        }
    });
}

window.floatingMarkAsRead = function(event, otherId) {
    event.stopPropagation();
    fetch(`<?= site_url('message/mark_read_ajax/') ?>${otherId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            loadFloatingInbox();
            syncUnreadCount();
        }
    });
};

window.floatingTogglePin = function(event, otherId) {
    event.stopPropagation();
    fetch(`<?= site_url('message/toggle_pin_ajax/') ?>${otherId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            loadFloatingInbox();
        }
    });
};

window.floatingToggleMute = function(event, otherId) {
    event.stopPropagation();
    fetch(`<?= site_url('message/toggle_mute_ajax/') ?>${otherId}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'ok') {
            loadFloatingInbox();
            syncUnreadCount();
        }
    });
};

window.floatingDeleteConversation = function(event, otherId) {
    event.stopPropagation();
    if (confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này? Toàn bộ tin nhắn cũ sẽ bị ẩn đối với bạn.')) {
        fetch(`<?= site_url('message/delete_chat_ajax/') ?>${otherId}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                loadFloatingInbox();
                syncUnreadCount();
            }
        });
    }
};

window.handleFloatingInboxClick = function(event, otherId, fullName, avatarUrl) {
    // Ngăn chặn sự kiện mở chat nếu click trúng dropdown ba chấm
    if (event.target.closest('.dropdown')) return;
    openFloatingConversation(otherId, fullName, avatarUrl);
};

// Helper tránh lỗi XSS
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Khởi chạy khi tài liệu sẵn sàng
document.addEventListener('DOMContentLoaded', function() {
    // Sửa nút Hộp thư trên Header để khi click sẽ mở chat widget
    const headerTrigger = document.getElementById('headerInboxTrigger');
    if (headerTrigger) {
        headerTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            // Mở khung chat
            if (!floatingChatOpen) {
                toggleFloatingChat();
            } else if (currentChatUserId) {
                // Nếu đang mở mà ở trong phòng chat, bấm lần nữa để về inbox list
                backToInbox();
            } else {
                // Đóng khung chat
                toggleFloatingChat();
            }
        });
    }

    // Tự động kiểm tra unread badge định kỳ mỗi 15 giây
    syncUnreadCount();
    setInterval(syncUnreadCount, 15000);
});
</script>
<?php endif; ?>

