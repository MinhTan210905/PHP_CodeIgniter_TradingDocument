<div class="container py-4">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h2 class="section-title flex-grow-1"><i class="fas fa-users"></i> Quản lý Người dùng
            <span class="badge bg-secondary ms-2" style="font-size:0.75rem;font-weight:600;"><?= count($users) ?></span>
        </h2>
        <div class="d-flex gap-2 align-items-center">
            <div class="btn-group" role="group" id="userStatusFilter">
                <button type="button" class="btn btn-sm btn-outline-success rounded-start-3 fw-bold active" data-filter="active" style="font-size:0.8rem;padding:5px 16px;">
                    <i class="fas fa-check-circle me-1" style="font-size:0.7rem;"></i>Đang hoạt động
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-end-3 fw-bold" data-filter="banned" style="font-size:0.8rem;padding:5px 16px;">
                    <i class="fas fa-ban me-1" style="font-size:0.7rem;"></i>Bị chặn
                </button>
            </div>
            <a href="<?= site_url('admin') ?>" class="btn btn-light rounded-3 px-3" style="font-size:0.85rem;">
                <i class="fas fa-arrow-left me-1"></i> Về Dashboard
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                <thead style="background:#F8FAFC;">
                    <tr class="text-muted" style="font-size:0.77rem;">
                        <th style="padding:12px 16px;">ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Quyền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th style="text-align:right;padding-right:16px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach($users as $u): ?>
                    <tr data-banned="<?= !empty($u['is_banned']) ? '1' : '0' ?>">
                        <td style="padding:10px 16px;color:#9CA3AF;">#<?= $u['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:var(--hcmue-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#F5A623;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                                    <?= strtoupper(mb_substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></span>
                            </div>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= $u['phone'] ?: '<span class="text-muted">—</span>' ?></td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span style="background:#FEF3C7;color:#D97706;font-size:0.75rem;font-weight:700;padding:2px 10px;border-radius:20px;">Admin</span>
                            <?php else: ?>
                                <span style="background:#E8F0FD;color:var(--hcmue-blue);font-size:0.75rem;font-weight:600;padding:2px 10px;border-radius:20px;">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($u['is_banned'])): ?>
                                <span style="background:#FEE2E2;color:#DC2626;font-size:0.75rem;font-weight:700;padding:2px 10px;border-radius:20px;"><i class="fas fa-ban me-1" style="font-size:0.65rem;"></i>Bị chặn</span>
                            <?php else: ?>
                                <span style="background:#D1FAE5;color:#059669;font-size:0.75rem;font-weight:600;padding:2px 10px;border-radius:20px;"><i class="fas fa-check me-1" style="font-size:0.65rem;"></i>Hoạt động</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted" style="font-size:0.77rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td style="text-align:right;padding-right:16px;">
                            <?php if ($u['id'] != $this->session->userdata('user_id')): ?>
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Sửa thông tin -->
                                <button class="btn btn-sm btn-outline-primary rounded-2" style="font-size:0.72rem;padding:4px 9px;"
                                    data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id'] ?>">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <!-- Ban / Unban -->
                                <?php if (!empty($u['is_banned'])): ?>
                                <a href="<?= site_url('admin/unban_user/'.$u['id']) ?>"
                                   class="btn btn-sm btn-outline-success rounded-2" style="font-size:0.72rem;padding:4px 9px;"
                                   onclick="return confirm('Bỏ chặn tài khoản này?');" title="Bỏ chặn">
                                    <i class="fas fa-check-circle"></i>
                                </a>
                                <?php else: ?>
                                <a href="<?= site_url('admin/ban_user/'.$u['id']) ?>"
                                   class="btn btn-sm btn-outline-warning rounded-2" style="font-size:0.72rem;padding:4px 9px;"
                                   onclick="return confirm('Chặn tài khoản người dùng này vào Danh sách đen?');" title="Chặn tài khoản">
                                    <i class="fas fa-ban"></i>
                                </a>
                                <?php endif; ?>

                                <!-- Xóa tài khoản -->
                                <a href="<?= site_url('admin/delete_user/'.$u['id']) ?>"
                                   class="btn btn-sm btn-outline-danger rounded-2" style="font-size:0.72rem;padding:4px 9px;"
                                   onclick="return confirm('⚠️ XÓA VĨNH VIỄN tài khoản <?= addslashes(htmlspecialchars($u['full_name'])) ?>? Hành động này không thể hoàn tác!');"
                                   title="Xóa tài khoản">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.77rem;">(Bạn)</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal Sửa User -->
                    <?php if ($u['id'] != $this->session->userdata('user_id')): ?>
                    <div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" style="color:var(--hcmue-blue);">
                                        <i class="fas fa-user-edit me-2"></i>Sửa thông tin: <?= htmlspecialchars($u['full_name']) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="<?= site_url('admin/edit_user_post/'.$u['id']) ?>" method="POST">
                                    <div class="modal-body pt-3">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Họ và Tên</label>
                                                <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($u['full_name']) ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Tên đăng nhập</label>
                                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($u['username']) ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Số điện thoại</label>
                                                <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($u['phone'] ?? '') ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Email</label>
                                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Quyền</label>
                                                <select class="form-select" name="role">
                                                    <option value="user" <?= $u['role']==='user' ? 'selected' : '' ?>>User</option>
                                                    <option value="admin" <?= $u['role']==='admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.82rem;">Đặt lại mật khẩu <span class="text-muted">(tuỳ chọn)</span></label>
                                                <input type="password" class="form-control" name="new_password" placeholder="Để trống nếu không đổi">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Huỷ</button>
                                        <button type="submit" class="btn rounded-3 text-white fw-bold px-4"
                                            style="background:linear-gradient(135deg,#003F8A,#0052B4);">
                                            <i class="fas fa-save me-1"></i> Lưu thay đổi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Tab filter Hoạt động / Bị chặn -->
    <script>
    (function() {
        function filterUsers(filter) {
            document.querySelectorAll('#usersTableBody > tr').forEach(row => {
                const isBanned = row.dataset.banned === '1';
                if (filter === 'active') {
                    row.style.display = isBanned ? 'none' : '';
                } else {
                    row.style.display = isBanned ? '' : 'none';
                }
            });
        }

        document.querySelectorAll('#userStatusFilter button').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#userStatusFilter button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                filterUsers(this.dataset.filter);
            });
        });

        // Mặc định: Chỉ hiện người đang hoạt động
        filterUsers('active');
    })();
    </script>
</div>
