<div class="container py-4" style="max-width:780px;">
    <h2 class="section-title mb-4"><i class="fas fa-inbox"></i> Hộp thư</h2>

    <?php if (empty($conversations)): ?>
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center" id="emptyInboxCard">
            <i class="fas fa-comment-slash" style="font-size:2.5rem;color:#CBD5E1;"></i>
            <p class="mt-3 text-muted mb-0">Bạn chưa có hội thoại nào.</p>
            <a href="<?= site_url('trade') ?>" class="btn btn-primary-hcmue mt-3 px-4 d-inline-block" style="font-size:0.88rem;">
                <i class="fas fa-search me-1"></i> Tìm sách để nhắn tin
            </a>
        </div>
    <?php else: ?>
        <div class="card border-0 rounded-4 shadow-sm overflow-hidden" id="inboxListContainer">
            <?php foreach($conversations as $i => $conv): ?>
                <div class="d-flex align-items-center justify-content-between p-3 conv-item 
                            <?= $conv['unread_count'] > 0 ? 'unread' : '' ?> 
                            <?= $conv['is_pinned'] ? 'pinned' : '' ?>"
                     data-id="<?= $conv['other_user_id'] ?>"
                     data-timestamp="<?= strtotime($conv['created_at']) ?>"
                     style="<?= $i > 0 ? 'border-top:1px solid #F1F5F9;' : '' ?> transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative;">

                    <!-- Khu vực nội dung có thể click để vào đoạn chat -->
                    <a href="<?= site_url('message/conversation/' . $conv['other_user_id']) ?>" 
                       class="d-flex align-items-center gap-3 text-decoration-none text-dark flex-grow-1 overflow-hidden py-1">
                        
                        <!-- Avatar với chữ cái đầu của tên và gradient màu tương ứng -->
                        <div class="avatar-circle flex-shrink-0" style="width:48px;height:48px;background:<?= $conv['is_pinned'] ? 'linear-gradient(135deg, #F59E0B, #D97706)' : 'linear-gradient(135deg, #1E40AF, #3B82F6)' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.1rem;box-shadow: 0 2px 8px rgba(30,64,175,0.15); transition: background 0.3s;">
                            <?= strtoupper(substr($conv['full_name'] ?: $conv['username'], 0, 1)) ?>
                        </div>

                        <!-- Thông tin hội thoại -->
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold d-inline-flex align-items-center" style="font-size:0.92rem;">
                                    <?= htmlspecialchars($conv['full_name'] ?: $conv['username']) ?>
                                    
                                    <!-- Icon Ghim -->
                                    <i class="fas fa-thumbtack text-warning ms-2 pin-icon" style="font-size:0.75rem; <?= $conv['is_pinned'] ? '' : 'display:none;' ?>" title="Đã ghim"></i>
                                    
                                    <!-- Icon Tắt thông báo -->
                                    <i class="fas fa-bell-slash text-muted ms-2 mute-icon" style="font-size:0.75rem; <?= $conv['is_muted'] ? '' : 'display:none;' ?>" title="Đã tắt thông báo"></i>
                                </span>
                                <span class="text-muted" style="font-size:0.72rem;white-space:nowrap;">
                                    <?= date('d/m H:i', strtotime($conv['created_at'])) ?>
                                </span>
                            </div>
                            <?php if ($conv['post_title']): ?>
                                <div style="font-size:0.73rem;color:var(--hcmue-blue);margin-bottom:3px;font-weight:600;">
                                    <i class="fas fa-book me-1"></i><?= htmlspecialchars($conv['post_title']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted text-truncate msg-snippet" style="font-size:0.82rem; <?= $conv['unread_count'] > 0 ? 'font-weight:600; color:#1E293B !important;' : '' ?>">
                                <?= ($conv['sender_id'] == $this->session->userdata('user_id') ? 'Bạn: ' : '') . htmlspecialchars($conv['content']) ?>
                            </div>
                        </div>
                    </a>

                    <!-- Badge số tin nhắn & Menu Hành động ba chấm ở cạnh phải -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                        <!-- Badge chưa đọc -->
                        <span class="unread-badge badge-round flex-shrink-0" 
                              style="border-radius:50%;width:22px;height:22px;display:<?= $conv['unread_count'] > 0 ? 'flex' : 'none' ?>;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;
                                     <?= $conv['is_muted'] ? 'background:#94A3B8;color:#fff;' : 'background:var(--hcmue-blue);color:#fff;' ?>">
                            <?= $conv['unread_count'] ?>
                        </span>
                        
                        <i class="fas fa-chevron-right text-muted flex-shrink-0 chevron-indicator" style="font-size:0.7rem; <?= $conv['unread_count'] > 0 ? 'display:none;' : '' ?>"></i>

                        <!-- Menu ba chấm tinh tế -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0 border-0 btn-conv-actions" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:transparent; transition:all 0.2s;">
                                <i class="fas fa-ellipsis-v" style="font-size:0.85rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 py-2 px-1" style="min-width: 170px; font-size: 0.82rem; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.98); border: 1px solid rgba(0,0,0,0.05) !important;">
                                <li>
                                    <button class="dropdown-item py-2 px-3 rounded-2 action-mark-read" data-id="<?= $conv['other_user_id'] ?>" style="<?= $conv['unread_count'] > 0 ? '' : 'display:none;' ?>">
                                        <i class="fas fa-check-double text-success me-2" style="width:14px;"></i>Đánh dấu đã đọc
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item py-2 px-3 rounded-2 action-toggle-pin" data-id="<?= $conv['other_user_id'] ?>">
                                        <i class="fas fa-thumbtack text-warning me-2" style="width:14px;"></i>
                                        <span class="pin-text"><?= $conv['is_pinned'] ? 'Bỏ ghim' : 'Ghim đoạn chat' ?></span>
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item py-2 px-3 rounded-2 action-toggle-mute" data-id="<?= $conv['other_user_id'] ?>">
                                        <i class="fas <?= $conv['is_muted'] ? 'fa-bell text-primary' : 'fa-bell-slash text-muted' ?> me-2" style="width:14px;"></i>
                                        <span class="mute-text"><?= $conv['is_muted'] ? 'Bật thông báo' : 'Tắt thông báo' ?></span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <button class="dropdown-item py-2 px-3 rounded-2 action-delete text-danger" data-id="<?= $conv['other_user_id'] ?>">
                                        <i class="fas fa-trash-alt me-2" style="width:14px;"></i>Xóa đoạn chat
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Giao diện trống ẩn mặc định để dùng khi xóa hết hội thoại -->
        <div class="card border-0 rounded-4 shadow-sm p-5 text-center d-none" id="emptyInboxCard">
            <i class="fas fa-comment-slash" style="font-size:2.5rem;color:#CBD5E1;"></i>
            <p class="mt-3 text-muted mb-0">Bạn chưa có hội thoại nào.</p>
            <a href="<?= site_url('trade') ?>" class="btn btn-primary-hcmue mt-3 px-4 d-inline-block" style="font-size:0.88rem;">
                <i class="fas fa-search me-1"></i> Tìm sách để nhắn tin
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.conv-item { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.conv-item:hover { background: #F8FAFC; }
.conv-item.unread { background: #EEF5FF; }
.conv-item.unread:hover { background: #E3EEFF; }
.conv-item.pinned {
    background: #FFFDF5;
    border-left: 4px solid var(--accent) !important;
}
.conv-item.pinned:hover {
    background: #FFF9E6;
}
.btn-conv-actions:hover {
    background: rgba(0,0,0,0.05) !important;
    color: #1E293B !important;
}
.badge-round {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hàm sắp xếp lại danh sách hội thoại theo trạng thái Ghim & Thời gian mới nhất
    function resortInbox() {
        const container = document.getElementById('inboxListContainer');
        if (!container) return;

        const items = Array.from(container.querySelectorAll('.conv-item'));
        items.sort((a, b) => {
            const aPinned = a.classList.contains('pinned') ? 1 : 0;
            const bPinned = b.classList.contains('pinned') ? 1 : 0;

            if (aPinned !== bPinned) {
                return bPinned - aPinned; // Hội thoại đã ghim ưu tiên lên đầu
            }

            const aTime = parseInt(a.getAttribute('data-timestamp')) || 0;
            const bTime = parseInt(b.getAttribute('data-timestamp')) || 0;
            return bTime - aTime; // Cùng ghim hoặc cùng thường thì sắp xếp theo thời gian mới nhất
        });

        // Cập nhật đường phân cách viền
        items.forEach((item, index) => {
            if (index === 0) {
                item.style.borderTop = 'none';
            } else {
                item.style.borderTop = '1px solid #F1F5F9';
            }
            container.appendChild(item);
        });
    }

    // Cập nhật số badge ở thanh tiêu đề (Header)
    function triggerHeaderUpdate() {
        const badge = document.getElementById('inboxUnreadBadge');
        if (!badge) return;

        fetch('<?= site_url("message/total_unread") ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                const count = parseInt(data.count);
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(err => console.warn('Lỗi đồng bộ tin nhắn header:', err));
    }

    // 1. XỬ LÝ GHIM / BỎ GHIM (PIN CHAT)
    document.querySelectorAll('.action-toggle-pin').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const row = document.querySelector(`.conv-item[data-id="${id}"]`);
            const pinIcon = row.querySelector('.pin-icon');
            const pinText = this.querySelector('.pin-text');
            const avatar = row.querySelector('.avatar-circle');

            fetch(`<?= site_url('message/toggle_pin_ajax/') ?>${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    if (data.is_pinned == 1) {
                        row.classList.add('pinned');
                        pinIcon.style.display = '';
                        pinText.textContent = 'Bỏ ghim';
                        avatar.style.background = 'linear-gradient(135deg, #F59E0B, #D97706)';
                    } else {
                        row.classList.remove('pinned');
                        pinIcon.style.display = 'none';
                        pinText.textContent = 'Ghim đoạn chat';
                        avatar.style.background = 'linear-gradient(135deg, #1E40AF, #3B82F6)';
                    }
                    resortInbox();
                } else {
                    alert('Có lỗi xảy ra, vui lòng thử lại!');
                }
            })
            .catch(err => console.error(err));
        });
    });

    // 2. XỬ LÝ TẮT / BẬT THÔNG BÁO (MUTE CHAT)
    document.querySelectorAll('.action-toggle-mute').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const row = document.querySelector(`.conv-item[data-id="${id}"]`);
            const muteIcon = row.querySelector('.mute-icon');
            const muteText = this.querySelector('.mute-text');
            const muteBtnIcon = this.querySelector('i');
            const unreadBadge = row.querySelector('.unread-badge');

            fetch(`<?= site_url('message/toggle_mute_ajax/') ?>${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    if (data.is_muted == 1) {
                        muteIcon.style.display = '';
                        muteText.textContent = 'Bật thông báo';
                        muteBtnIcon.className = 'fas fa-bell text-primary me-2';
                        if (unreadBadge) {
                            unreadBadge.style.background = '#94A3B8'; // Đổi sang xám trầm
                        }
                    } else {
                        muteIcon.style.display = 'none';
                        muteText.textContent = 'Tắt thông báo';
                        muteBtnIcon.className = 'fas fa-bell-slash text-muted me-2';
                        if (unreadBadge) {
                            unreadBadge.style.background = 'var(--hcmue-blue)'; // Đổi sang xanh sáng
                        }
                    }
                    triggerHeaderUpdate();
                } else {
                    alert('Có lỗi xảy ra, vui lòng thử lại!');
                }
            })
            .catch(err => console.error(err));
        });
    });

    // 3. XỬ LÝ ĐÁNH DẤU ĐÃ ĐỌC TRỰC TIẾP
    document.querySelectorAll('.action-mark-read').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const row = document.querySelector(`.conv-item[data-id="${id}"]`);
            const unreadBadge = row.querySelector('.unread-badge');
            const chevron = row.querySelector('.chevron-indicator');
            const msgSnippet = row.querySelector('.msg-snippet');

            fetch(`<?= site_url('message/mark_read_ajax/') ?>${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    row.classList.remove('unread');
                    if (unreadBadge) unreadBadge.style.display = 'none';
                    if (chevron) chevron.style.display = '';
                    if (msgSnippet) {
                        msgSnippet.style.fontWeight = 'normal';
                        msgSnippet.style.color = '';
                    }
                    this.style.display = 'none'; // Ẩn tùy chọn này
                    triggerHeaderUpdate();
                } else {
                    alert('Có lỗi xảy ra, vui lòng thử lại!');
                }
            })
            .catch(err => console.error(err));
        });
    });

    // 4. XỬ LÝ XÓA ĐOẠN CHAT (SOFT DELETE CHAT)
    document.querySelectorAll('.action-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const row = document.querySelector(`.conv-item[data-id="${id}"]`);

            if (confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này? Toàn bộ tin nhắn cũ sẽ bị ẩn đối với bạn.')) {
                fetch(`<?= site_url('message/delete_chat_ajax/') ?>${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        // Hiệu ứng fade-out cực mượt
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            row.remove();
                            // Nếu xóa hết sạch hội thoại
                            const container = document.getElementById('inboxListContainer');
                            if (container && container.querySelectorAll('.conv-item').length === 0) {
                                container.remove();
                                const emptyCard = document.getElementById('emptyInboxCard');
                                if (emptyCard) {
                                    emptyCard.classList.remove('d-none');
                                }
                            }
                            triggerHeaderUpdate();
                        }, 300);
                    } else {
                        alert('Không thể thực hiện xóa. Vui lòng thử lại!');
                    }
                })
                .catch(err => console.error(err));
            }
        });
    });
});
</script>
