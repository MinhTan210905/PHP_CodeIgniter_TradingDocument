<?php
$stats     = $stats ?? [];
$active_tab= $active_tab ?? 'active';
$user_id   = $this->session->userdata('user_id');
?>
<style>
.seller-tab-btn { border:none;background:transparent;padding:10px 20px;font-weight:600;font-size:0.88rem;color:var(--text-muted);border-bottom:2.5px solid transparent;transition:all 0.2s;cursor:pointer; }
.seller-tab-btn.active { color:var(--primary);border-bottom-color:var(--primary); }
.seller-stat-box { text-align:center;padding:12px 20px; }
.seller-stat-box .num { font-size:1.5rem;font-weight:800;color:var(--primary); }
.seller-stat-box .lbl { font-size:0.75rem;color:var(--text-muted); }
</style>

<div class="container py-4" style="max-width:900px;">

    <!-- Seller Header Card -->
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div style="height:120px;background:linear-gradient(145deg, #1E3A8A 0%, #1D4ED8 60%, #3B82F6 100%);"></div>
        <div class="px-4 pb-4">
            <div class="d-flex align-items-end gap-4">
                <div style="width:90px;height:90px;background:var(--bg-card);border-radius:50%;border:4px solid #fff;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:-45px;
                            box-shadow:var(--shadow-md); overflow:hidden;">
                    <?php if(!empty($seller['avatar']) && file_exists(FCPATH . $seller['avatar'])): ?>
                        <img src="<?= base_url($seller['avatar']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="font-size:2.4rem;color:var(--primary);font-weight:800;"><?= strtoupper(substr($seller['full_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="pt-3 flex-grow-1">
                    <h1 style="font-size:1.4rem;font-weight:800;color:var(--text-dark);margin:0;letter-spacing:-0.5px;">
                        <?= htmlspecialchars($seller['full_name']) ?>
                    </h1>
                    <span class="text-muted" style="font-size:0.85rem;">@<?= $seller['username'] ?></span>
                    <?php if ($seller['role'] === 'admin'): ?>
                        <span class="ms-2" style="background:var(--accent-pale);color:var(--accent-dark);font-size:0.72rem;font-weight:700;padding:2px 10px;border-radius:20px;">ADMIN</span>
                    <?php endif; ?>
                </div>
                <!-- Actions -->
                <div class="pt-3 d-flex gap-2">
                    <?php if (!$this->session->userdata('logged_in') || $this->session->userdata('user_id') != $seller['id']): ?>
                        <?php 
                        $seller_avatar = '';
                        if (!empty($seller['avatar']) && file_exists(FCPATH . $seller['avatar'])) {
                            $seller_avatar = base_url($seller['avatar']);
                        }
                        ?>
                        <a href="javascript:void(0)" onclick="window.openDirectChat(<?= $seller['id'] ?>, '<?= htmlspecialchars($seller['full_name'], ENT_QUOTES) ?>', '<?= $seller_avatar ?>')"
                           class="btn btn-primary-hcmue rounded-3 px-3 py-2" style="font-size:0.85rem;">
                            <i class="fas fa-comment-dots me-1"></i>Nhắn tin
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="row g-0 mt-3 border-top pt-3">
                <div class="col-4 seller-stat-box border-end">
                    <div class="num"><?= $stats['active_posts'] ?? 0 ?></div>
                    <div class="lbl">Đang bán</div>
                </div>
                <div class="col-4 seller-stat-box border-end">
                    <div class="num"><?= $stats['sold_posts'] ?? 0 ?></div>
                    <div class="lbl">Đã Pass</div>
                </div>
                <div class="col-4 seller-stat-box">
                    <div class="num" style="color:var(--accent);">
                        <?= $stats['avg_rating'] ? $stats['avg_rating'] . '★' : '—' ?>
                    </div>
                    <div class="lbl"><?= $stats['total_ratings'] ?? 0 ?> đánh giá</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="d-flex border-bottom mb-4">
        <button class="seller-tab-btn <?= $active_tab === 'active' ? 'active' : '' ?>" onclick="sellerTab('active')">
            <i class="fas fa-store me-1"></i>Đang bán (<?= count($active_posts) ?>)
        </button>
        <button class="seller-tab-btn <?= $active_tab === 'sold' ? 'active' : '' ?>" onclick="sellerTab('sold')">
            <i class="fas fa-archive me-1"></i>Đã Pass (<?= count($sold_posts) ?>)
        </button>
        <button class="seller-tab-btn <?= $active_tab === 'ratings' ? 'active' : '' ?>" onclick="sellerTab('ratings')">
            <i class="fas fa-star me-1"></i>Đánh giá (<?= count($ratings) ?>)
        </button>
    </div>

    <!-- Tab Đang bán -->
    <div id="tab-active" class="<?= $active_tab !== 'active' ? 'd-none' : '' ?>">
        <?php if (empty($active_posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-store" style="font-size:2.5rem;color:#CBD5E1;"></i>
                <p class="mt-3">Người bán chưa có sách nào đang bán.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($active_posts as $p): ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card trade-card h-100">
                        <?php
                            $img_src = (!empty($p['image_url']) && file_exists(FCPATH . $p['image_url']))
                                       ? base_url($p['image_url'])
                                       : base_url('assets/images/default_book.jpg');
                        ?>
                        <a href="<?= site_url('trade/detail/' . $p['id']) ?>" class="d-block card-img-link">
                            <img src="<?= $img_src ?>" class="post-img" alt="<?= htmlspecialchars($p['title']) ?>"
                                 onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">
                        </a>
                        <div class="p-3">
                            <span class="badge-cat mb-2 d-inline-block"><i class="<?= $p['cat_icon'] ?? 'fas fa-book' ?>"></i> <?= $p['category_name'] ?></span>
                            <a href="<?= site_url('trade/detail/' . $p['id']) ?>"
                               class="d-block fw-bold text-dark text-decoration-none mb-1"
                               style="font-size:0.9rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                <?= htmlspecialchars($p['title']) ?>
                            </a>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="price-tag"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                <?php if ($p['quantity'] > 0): ?>
                                    <span style="font-size:0.75rem;color:#6B7280;">Còn <?= $p['quantity'] ?> cuốn</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab Đã Pass -->
    <div id="tab-sold" class="<?= $active_tab !== 'sold' ? 'd-none' : '' ?>">
        <?php if (empty($sold_posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-archive" style="font-size:2.5rem;color:#CBD5E1;"></i>
                <p class="mt-3">Chưa có sách nào được pass.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($sold_posts as $p): ?>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card trade-card card-sold h-100">
                        <?php
                            $img_src = (!empty($p['image_url']) && file_exists(FCPATH . $p['image_url']))
                                       ? base_url($p['image_url'])
                                       : base_url('assets/images/default_book.jpg');
                        ?>
                        <a href="<?= site_url('trade/detail/' . $p['id']) ?>" class="d-block card-img-link">
                            <img src="<?= $img_src ?>" class="post-img" alt="<?= htmlspecialchars($p['title']) ?>"
                                 onerror="this.src='<?= base_url('assets/images/default_book.jpg') ?>';">
                        </a>
                        <div class="p-3">
                            <span class="status-badge-sold mb-2 d-inline-flex"><i class="fas fa-lock" style="font-size:10px;"></i> Đã Pass</span>
                            <div class="fw-bold text-dark mb-1" style="font-size:0.88rem;"><?= htmlspecialchars(mb_strimwidth($p['title'], 0, 60, '...')) ?></div>
                            <div class="text-muted" style="font-size:0.78rem;"><?= $p['category_name'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab Đánh giá -->
    <div id="tab-ratings" class="<?= $active_tab !== 'ratings' ? 'd-none' : '' ?>">
        <?php if (empty($ratings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-star" style="font-size:2.5rem;color:#CBD5E1;"></i>
                <p class="mt-3">Người bán chưa có đánh giá nào.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($ratings as $r): ?>
                <div class="card border-0 rounded-4 shadow-sm p-3">
                    <div class="d-flex gap-3">
                        <div style="width:40px;height:40px;background:#E8F0FD;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--primary);font-weight:700;font-size:0.9rem;flex-shrink:0; overflow:hidden;">
                            <?php if (!empty($r['buyer_avatar']) && file_exists(FCPATH . $r['buyer_avatar'])): ?>
                                <img src="<?= base_url($r['buyer_avatar']) ?>" alt="Avt" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($r['buyer_name'] ?: $r['buyer_username'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                <span class="fw-bold" style="font-size:0.88rem;"><?= htmlspecialchars($r['buyer_name'] ?: $r['buyer_username']) ?></span>
                                <span class="star-display" style="font-size:0.88rem;">
                                    <?php for($s=1;$s<=5;$s++): ?>
                                        <i class="<?= $s<=$r['stars']?'fas':'far' ?> fa-star"></i>
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <!-- Tên sách đã giao dịch — tăng uy tín -->
                            <div style="font-size:0.78rem;color:var(--primary);margin:4px 0;">
                                <i class="fas fa-book me-1"></i>
                                <a href="<?= site_url('trade/detail/' . $r['post_id'] ?? '#') ?>"
                                   class="text-decoration-none" style="color:var(--primary);">
                                    <?= htmlspecialchars($r['post_title'] ?? 'Sách đã bán') ?>
                                </a>
                            </div>
                            <?php if ($r['comment']): ?>
                                <p style="font-size:0.85rem;color:#4B5563;margin:6px 0 0;"><?= htmlspecialchars($r['comment']) ?></p>
                            <?php endif; ?>
                            <div class="text-muted mt-1" style="font-size:0.72rem;">
                                <i class="far fa-clock me-1"></i><?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function sellerTab(tab) {
    ['active','sold','ratings'].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('d-none', t !== tab);
    });
    document.querySelectorAll('.seller-tab-btn').forEach((btn, i) => {
        btn.classList.toggle('active', ['active','sold','ratings'][i] === tab);
    });
    history.replaceState(null, '', '?tab=' + tab);
}
</script>
