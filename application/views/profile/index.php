<?php $cur_uid = $this->session->userdata('user_id'); ?>

<div class="container py-4" style="max-width:900px;">

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- === Profile Header === -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div style="height:120px;background:linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%);"></div>
        <div class="px-4 pb-4">
            <div class="d-flex align-items-end gap-4">
                <div style="width:90px;height:90px;background:var(--bg-card);border-radius:50%;border:4px solid #fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:-45px;box-shadow:var(--shadow-md);overflow:hidden;">
                    <?php if(!empty($user['avatar']) && file_exists(FCPATH . $user['avatar'])): ?>
                        <img src="<?= base_url($user['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="font-size:2.4rem;color:var(--primary);font-weight:800;"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="pt-3 flex-grow-1">
                    <h2 style="font-size:1.45rem;font-weight:800;color:var(--text-dark);margin:0;letter-spacing:-0.5px;">
                        <?= htmlspecialchars($user['full_name']) ?>
                    </h2>
                    <span class="text-muted" style="font-size:0.85rem;">@<?= $user['username'] ?></span>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="ms-2" style="background:var(--accent-pale);color:var(--accent-dark);font-size:0.72rem;font-weight:700;padding:2px 10px;border-radius:20px;">ADMIN</span>
                    <?php endif; ?>
                </div>
                <div class="text-center pb-1">
                    <div class="fw-bold" style="font-size:1.2rem;color:var(--primary);"><?= $avg_rating['avg'] ?: '—' ?></div>
                    <div class="star-display" style="font-size:0.85rem;">
                        <?php if ($avg_rating['avg'] > 0): ?>
                            <?php for($s=1;$s<=5;$s++): ?>
                                <i class="<?= $s<=round($avg_rating['avg'])?'fas':'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.78rem;">Chưa có đánh giá</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:0.72rem;"><?= $avg_rating['total'] ?> đánh giá</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- === Cài đặt Cá Nhân === -->
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:var(--primary);">
                    <i class="fas fa-user-edit me-2"></i>Thông tin cá nhân
                </h6>
                
                <form action="<?= site_url('profile/update_info') ?>" method="POST" enctype="multipart/form-data">
                    <!-- Sửa tên -->
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Họ và Tên</label>
                        <input type="text" class="form-control form-control-sm" name="full_name" 
                               value="<?= htmlspecialchars($user['full_name']) ?>" required 
                               style="border-radius:8px; border:1.5px solid #E5E9F2;">
                    </div>
                    
                    <!-- Đổi avatar -->
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Thay ảnh đại diện</label>
                        <input type="file" class="form-control form-control-sm" name="avatar" accept="image/*" 
                               style="border-radius:8px; border:1.5px solid #E5E9F2; font-size: 0.75rem;">
                    </div>

                    <!-- Cập nhật SĐT -->
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Số điện thoại</label>
                        <input type="tel" class="form-control form-control-sm" name="phone" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               placeholder="0912345678"
                               pattern="0[0-9]{9}" title="Số điện thoại phải có đúng 10 chữ số và bắt đầu bằng số 0"
                               style="border-radius:8px; border:1.5px solid #E5E9F2;">
                    </div>

                    <button type="submit" class="btn btn-primary-hcmue w-100 btn-sm py-2 mb-3" style="border-radius:8px;font-weight:600;">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </form>

                <hr class="my-3" style="border-color:#F1F5F9;">

                <!-- Toggle ẩn/hiện SĐT -->
                <label class="form-label text-muted small fw-bold mb-1">Chế độ hiển thị SĐT</label>
                <a href="<?= site_url('profile/toggle_phone') ?>"
                   class="btn w-100 py-2 fw-bold mb-4"
                   style="font-size:0.8rem;border-radius:10px;
                          background:<?= $user['phone_visible'] ? '#D1FAE5' : '#F3F4F6' ?>;
                          color:<?= $user['phone_visible'] ? '#065F46' : '#6B7280' ?>;border:none;">
                    <i class="fas fa-<?= $user['phone_visible'] ? 'eye' : 'eye-slash' ?> me-2"></i>
                    <?= $user['phone_visible'] ? 'Công khai' : 'Riêng tư' ?>
                </a>

                <hr class="my-3" style="border-color:#F1F5F9;">

                <!-- Đổi mật khẩu -->
                <h6 class="fw-bold mb-3" style="color:var(--primary); font-size: 0.95rem;">
                    <i class="fas fa-lock me-2"></i>Đổi mật khẩu
                </h6>
                <form action="<?= site_url('profile/change_password') ?>" method="POST">
                    <div class="mb-2">
                        <input type="password" class="form-control form-control-sm" name="old_password" 
                               placeholder="Mật khẩu cũ" required 
                               style="border-radius:8px; border:1.5px solid #E5E9F2; background:#f8fafc;">
                    </div>
                    <div class="mb-2">
                        <input type="password" class="form-control form-control-sm" name="new_password" 
                               placeholder="Mật khẩu mới" required 
                               style="border-radius:8px; border:1.5px solid #E5E9F2; background:#f8fafc;">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control form-control-sm" name="confirm_password" 
                               placeholder="Xác nhận mật khẩu mới" required 
                               style="border-radius:8px; border:1.5px solid #E5E9F2; background:#f8fafc;">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary w-100 btn-sm py-2" style="border-radius:8px;font-weight:600;">
                        Cập nhật mật khẩu
                    </button>
                </form>
            </div>
        </div>

        <!-- === Đánh giá nhận được === -->
        <div class="col-md-8">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:var(--primary);">
                    <i class="fas fa-star me-2" style="color:var(--accent);"></i>Đánh giá từ người mua
                </h6>
                <?php if (empty($my_ratings)): ?>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">Bạn chưa nhận được đánh giá nào.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3" style="max-height:220px;overflow-y:auto;">
                        <?php foreach($my_ratings as $r): ?>
                            <div class="d-flex gap-3">
                                <div style="width:34px;height:34px;background:#E8F0FD;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;font-size:0.8rem;flex-shrink:0; overflow:hidden;">
                                    <?php if (!empty($r['avatar']) && file_exists(FCPATH . $r['avatar'])): ?>
                                        <img src="<?= base_url($r['avatar']) ?>" alt="Avt" style="width:100%;height:100%;object-fit:cover;">
                                    <?php else: ?>
                                        <?= strtoupper(substr($r['full_name'] ?: $r['username'], 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span style="font-size:0.82rem;font-weight:600;"><?= htmlspecialchars($r['full_name'] ?: $r['username']) ?></span>
                                        <span class="star-display" style="font-size:0.78rem;">
                                            <?php for($s=1;$s<=5;$s++): ?>
                                                <i class="<?= $s<=$r['stars']?'fas':'far' ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php if ($r['comment']): ?>
                                        <p style="font-size:0.82rem;color:#4B5563;margin:0;"><?= htmlspecialchars($r['comment']) ?></p>
                                    <?php endif; ?>
                                    <div class="text-muted" style="font-size:0.72rem;">
                                        Về: <?= htmlspecialchars($r['post_title']) ?> · <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- === Bài đăng của tôi === -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="color:var(--primary);">
                        <i class="fas fa-clipboard-list me-2"></i>Bài đăng của tôi (<?= count($my_posts) ?>)
                    </h6>
                    <button class="btn-dang-bai" data-bs-toggle="modal" data-bs-target="#createPostModal" style="font-size:0.8rem;padding:6px 14px;">
                        <i class="fas fa-plus"></i> Đăng mới
                    </button>
                </div>
                <?php if (empty($my_posts)): ?>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">Bạn chưa đăng bài nào.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                            <thead style="background:#F8FAFC;">
                                <tr class="text-muted" style="font-size:0.78rem;">
                                    <th>Tên sách</th>
                                    <th>Danh mục</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <th>Bình luận</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($my_posts as $p): ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('trade/detail/'.$p['id']) ?>"
                                           class="text-decoration-none fw-bold text-dark"
                                           style="font-size:0.85rem;">
                                            <?= htmlspecialchars(mb_strimwidth($p['title'], 0, 50, '...')) ?>
                                        </a>
                                    </td>
                                    <td class="text-muted"><?= $p['category_name'] ?></td>
                                    <td class="text-danger fw-bold"><?= number_format($p['price'],0,',','.') ?>đ</td>
                                    <td>
                                        <?php if ($p['status']==='available'): ?>
                                            <span class="status-badge-avail">Còn sách</span>
                                        <?php elseif ($p['status']==='sold'): ?>
                                            <span class="status-badge-sold">Đã Pass</span>
                                        <?php elseif ($p['status']==='rejected'): ?>
                                            <span class="status-badge-rejected" style="background:#FEE2E2; color:#991B1B; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:4px; border:1px solid #FCA5A5;">
                                                <i class="fas fa-times-circle" style="font-size:0.75rem;"></i>
                                                Bị từ chối
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge-pending">
                                                <i class="fas fa-hourglass-half" style="font-size:0.65rem;"></i>
                                                Chờ duyệt
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted"><i class="far fa-comment me-1"></i><?= $p['comment_count'] ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= site_url('trade/edit/'.$p['id']) ?>"
                                               class="btn btn-sm btn-outline-secondary rounded-2"
                                               title="Sửa bài" style="font-size:0.75rem;padding:3px 8px;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($p['status']==='available'): ?>
                                                <a href="<?= site_url('trade/update_status/'.$p['id'].'/sold') ?>"
                                                   class="btn btn-sm btn-outline-success rounded-2"
                                                   onclick="return confirm('Đánh dấu Đã Pass?');"
                                                   title="Đã Pass" style="font-size:0.75rem;padding:3px 8px;">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php elseif ($p['status']==='sold'): ?>
                                                <a href="<?= site_url('trade/update_status/'.$p['id'].'/available') ?>"
                                                   class="btn btn-sm btn-outline-primary rounded-2"
                                                   onclick="return confirm('Khôi phục trạng thái Còn sách để bán tiếp?');"
                                                   title="Bán tiếp" style="font-size:0.75rem;padding:3px 8px;">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= site_url('trade/delete/'.$p['id']) ?>"
                                               class="btn btn-sm btn-outline-danger rounded-2"
                                               onclick="return confirm('Xóa bài này?');"
                                               title="Xóa" style="font-size:0.75rem;padding:3px 8px;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- === Vùng nguy hiểm: Xóa tài khoản === -->
    <div class="card border-0 rounded-4 shadow-sm p-4 mt-4" style="border: 1.5px solid #FEE2E2 !important;">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6 class="fw-bold mb-1" style="color:#DC2626;"><i class="fas fa-exclamation-triangle me-2"></i>Vùng nguy hiểm</h6>
                <p class="text-muted mb-0" style="font-size:0.82rem;">Xóa tài khoản sẽ xóa vĩnh viễn toàn bộ dữ liệu của bạn (bài đăng, tin nhắn...) và không thể hoàn tác.</p>
            </div>
            <button class="btn btn-danger rounded-3 fw-bold ms-4 flex-shrink-0" style="font-size:0.85rem;padding:8px 18px;"
                    data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                <i class="fas fa-trash me-2"></i>Xóa tài khoản
            </button>
        </div>
    </div>

</div>

<!-- Modal xác nhận xóa tài khoản -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color:#DC2626;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa tài khoản
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('profile/delete_account') ?>" method="POST">
                <div class="modal-body">
                    <div class="alert alert-danger border-0 rounded-3" style="font-size:0.85rem;">
                        <strong>Cảnh báo!</strong> Hành động này sẽ xóa vĩnh viễn tài khoản và toàn bộ dữ liệu của bạn. Bạn sẽ không thể đăng nhập lại.
                    </div>
                    <label class="form-label fw-semibold" style="font-size:0.85rem;">Nhập mật khẩu để xác nhận:</label>
                    <input type="password" class="form-control rounded-3" name="confirm_delete_password" required placeholder="Mật khẩu hiện tại của bạn">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 fw-semibold" data-bs-dismiss="modal">Huỷ bỏ</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold">
                        <i class="fas fa-trash me-2"></i>Xóa vĩnh viễn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


