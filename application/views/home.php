
<!-- Search & Filter Bar (API-driven) -->
<div class="search-section">
    <div class="container">
        <div class="d-flex gap-3 align-items-center flex-wrap">
            <!-- Ô tìm kiếm: id="apiSearchInput" để JS lắng nghe sự kiện -->
            <div class="search-input-wrap flex-grow-1" style="min-width:200px;max-width:400px;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="apiSearchInput"
                       value="<?= htmlspecialchars($keyword ?? '') ?>"
                       placeholder="Tìm sách, giáo trình...">
            </div>
            <!-- Nút lọc danh mục: Phân nhóm "Xem thêm" cho giao diện cực sang xịn -->
            <?php 
                $limit_visible = 6;
                $visible_cats  = array_slice($categories, 0, $limit_visible);
                $hidden_cats   = array_slice($categories, $limit_visible);
            ?>
            <div class="d-flex align-items-center gap-2 flex-grow-1" id="catFilterBar" style="min-width:0;">
                <!-- Thanh trượt ngang chứa các danh mục chính -->
                <div class="filter-scroll flex-grow-1 d-flex align-items-center">
                    <button type="button" class="cat-filter-btn active" data-cat-id="">
                        <i class="fas fa-th-large me-1"></i> Tất cả
                    </button>
                    <?php foreach($visible_cats as $cat): ?>
                        <button type="button" class="cat-filter-btn" data-cat-id="<?= $cat['id'] ?>">
                            <i class="<?= $cat['icon'] ?> me-1"></i> <?= $cat['category_name'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Dropdown NẰM NGOÀI filter-scroll để không bị kẹt/ẩn menu -->
                <?php if (!empty($hidden_cats)): ?>
                    <div class="dropdown flex-shrink-0">
                        <button type="button" class="cat-more-btn dropdown-toggle border-0" 
                                id="moreCatsDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,8">
                            Khác
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2 px-1" 
                            aria-labelledby="moreCatsDropdown"
                            style="min-width: 210px; border: 1px solid #EDF2F7 !important; z-index: 9999;">
                            <?php foreach($hidden_cats as $cat): ?>
                                <li>
                                    <button type="button" class="dropdown-item cat-filter-btn custom-dd-btn gap-2" 
                                            data-cat-id="<?= $cat['id'] ?>">
                                        <i class="<?= $cat['icon'] ?> text-center text-primary-mid" style="width: 18px; font-size:0.85rem;"></i>
                                        <span class="fw-bold"><?= $cat['category_name'] ?></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Nút xóa lọc -->
            <button type="button" id="clearFilterBtn" class="text-muted text-decoration-none btn btn-link p-0" style="font-size:0.82rem;white-space:nowrap;display:none;">
                <i class="fas fa-times me-1"></i>Xóa lọc
            </button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-4">

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-hcmue alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-hcmue alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Section Title -->
    <div class="d-flex align-items-center mb-4">
        <h2 class="section-title flex-grow-1">
            <i class="fas fa-book-open" style="font-size:1.1rem;"></i>
            <?php if($keyword): ?>
                Kết quả cho "<em><?= htmlspecialchars($keyword) ?></em>"
            <?php elseif($active_cat): ?>
                <?php $cur_cat = array_filter($categories, fn($c) => $c['id'] == $active_cat);
                      $cur_cat = reset($cur_cat); ?>
                <?= $cur_cat ? $cur_cat['category_name'] : 'Danh mục' ?>
            <?php else: ?>
                Tài liệu đang được trao đổi
            <?php endif; ?>
        </h2>
        <!-- id="resultCount" để JS cập nhật số lượng kết quả -->
        <span id="resultCount" class="ms-3 text-muted" style="font-size:0.83rem;white-space:nowrap;">
            <?= count($posts) ?> bài đăng
        </span>
    </div>

    <!-- Cards Grid — nội dung do API trả về sẽ được đổ vào đây qua Javascript -->
    <div id="book-list" class="row g-4"></div>

    <!-- Template thông báo không có kết quả (ẩn mặc định) -->
    <div id="empty-state" class="text-center py-5" style="display:none;">
        <i class="fas fa-box-open" style="font-size:3rem;color:#CBD5E1;"></i>
        <p class="mt-3 text-muted" style="font-size:0.95rem;">Không có bài đăng nào phù hợp.</p>
    </div>

    <!-- Skeleton loading — hiển thị khi đang chờ API trả dữ liệu -->
    <div id="loading-state" class="row g-4">
        <?php for($i=0;$i<4;$i++): ?>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 p-3" style="border-radius:var(--card-radius);box-shadow:var(--shadow-sm);">
                <div style="height:160px;background:#EFF6FF;border-radius:12px;animation:pulse 1.2s infinite;"></div>
                <div style="height:14px;background:#EFF6FF;border-radius:4px;margin-top:14px;animation:pulse 1.2s infinite;"></div>
                <div style="height:14px;background:#EFF6FF;border-radius:4px;margin-top:8px;width:60%;animation:pulse 1.2s infinite;"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

</div>

<style>
/* Skeleton loading animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.4; }
}

/* Nút "Xem thêm" danh mục */
.cat-more-btn {
    display        : inline-flex;
    align-items    : center;
    white-space    : nowrap;
    border         : 1.5px dashed var(--primary-light) !important;
    border-radius  : var(--radius-pill);
    padding        : 6px 16px;
    font-size      : 0.80rem;
    font-weight    : 600;
    font-family    : inherit;
    background     : #F8FAFC;
    color          : var(--primary-mid);
    transition     : var(--transition);
    cursor         : pointer;
}
.cat-more-btn:hover, .cat-more-btn:focus, .cat-more-btn[aria-expanded="true"] {
    background: var(--primary-pale);
    border-color: var(--primary-mid) !important;
}

/* Reset style cho các nút lọc danh mục nằm trong dropdown */
.cat-filter-btn.custom-dd-btn {
    display: flex !important;
    width: 100%;
    border: none !important;
    border-radius: 8px !important;
    background: transparent !important;
    box-shadow: none !important;
    color: #475569 !important;
    text-align: left !important;
    padding: 8px 16px !important;
    font-size: 0.82rem !important;
    white-space: nowrap !important;
}
.cat-filter-btn.custom-dd-btn:hover {
    background: #F1F5F9 !important;
    color: var(--primary-mid) !important;
}
.cat-filter-btn.custom-dd-btn.active {
    background: var(--primary-pale) !important;
    color: var(--primary) !important;
    font-weight: 700 !important;
}
</style>

<script>
(function () {
    // =========================================================
    // Biến trạng thái — lưu bộ lọc đang chọn
    // =========================================================
    let currentCat = '';    // ID danh mục đang lọc (rỗng = tất cả)
    let searchTimer = null; // Timer cho debounce

    // Lấy tham chiếu đến các phần tử HTML quan trọng
    const searchInput  = document.getElementById('apiSearchInput');
    const bookList     = document.getElementById('book-list');
    const emptyState   = document.getElementById('empty-state');
    const loadingState = document.getElementById('loading-state');
    const resultCount  = document.getElementById('resultCount');
    const clearBtn     = document.getElementById('clearFilterBtn');
    const catButtons   = document.querySelectorAll('#catFilterBar .cat-filter-btn');

    // URL gốc của API (dùng PHP để đảm bảo đúng domain)
    const API_URL = '<?= site_url("api/posts/search") ?>';
    const BASE_URL = '<?= base_url() ?>';
    const DETAIL_URL = '<?= site_url("trade/detail/") ?>';
    const EDIT_URL = '<?= site_url("trade/edit/") ?>';
    const MSG_URL = '<?= site_url("message/conversation/") ?>';
    const DEFAULT_IMG     let lastResultJson = '';

    // =========================================================
    // Hàm gọi API và render kết quả
    // =========================================================
    function renderBooksList(result) {
        if (result.status === 404 || !result.data || !result.data.length) {
            // Trường hợp không có kết quả
            emptyState.style.display  = 'block';
            bookList.style.display    = 'none';
            resultCount.textContent   = '0 bài đăng';
            return;
        }

        emptyState.style.display  = 'none';
        resultCount.textContent = result.total + ' bài đăng';

        // Vẽ HTML từ dữ liệu JSON trả về
        bookList.innerHTML = result.data.map(function (post) {
            const isSold  = post.status === 'sold';
            const imgSrc  = post.image_url ? BASE_URL + post.image_url : DEFAULT_IMG;
            const price   = Number(post.price).toLocaleString('vi-VN') + 'đ';
            const date    = new Date(post.created_at).toLocaleDateString('vi-VN');
            const rating  = parseFloat(post.avg_rating) > 0
                ? Array.from({length: 5}, function(_, i) {
                      return '<i class="' + (i < Math.round(post.avg_rating) ? 'fas' : 'far') + ' fa-star"></i>';
                  }).join('') + ' <span style="color:#6B7280">(' + post.total_ratings + ')</span>'
                : '<span class="no-rating">Chưa có đánh giá</span>';

            return '<div class="col-12 col-sm-6 col-lg-4 col-xl-3">' +
                '<div class="card trade-card d-flex flex-column ' + (isSold ? 'card-sold' : '') + '">' +
                    '<a href="' + DETAIL_URL + post.id + '" class="d-block card-img-link">' +
                        '<img src="' + imgSrc + '" class="post-img" alt="' + post.title + '" loading="lazy"' +
                             ' onerror="this.onerror=null;this.src=\'' + DEFAULT_IMG + '\'">' +
                    '</a>' +
                    '<div class="p-3 d-flex flex-column flex-grow-1">' +
                        '<div class="d-flex align-items-center justify-content-between mb-2 gap-1 flex-wrap">' +
                            '<span class="badge-cat"><i class="' + (post.cat_icon || 'fas fa-book') + '"></i> ' + (post.category_name || '') + '</span>' +
                            (isSold
                                ? '<span class="status-badge-sold"><i class="fas fa-lock" style="font-size:10px"></i> Đã Pass</span>'
                                : '<span class="status-badge-avail"><i class="fas fa-circle" style="font-size:6px"></i> Còn ' + post.quantity + ' cuốn</span>') +
                        '</div>' +
                        '<a href="' + DETAIL_URL + post.id + '" class="text-decoration-none text-dark fw-bold mb-1"' +
                           ' style="font-size:0.92rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4;">' +
                            post.title +
                        '</a>' +
                        '<div class="d-flex align-items-center gap-1 mb-2" style="font-size:0.78rem;color:#6B7280;">' +
                            '<i class="fas fa-user-circle" style="color:#2563EB;"></i>' +
                            '<span>' + (post.full_name || post.username) + '</span>' +
                            '<span class="mx-1">·</span>' +
                            '<span class="star-display">' + rating + '</span>' +
                        '</div>' +
                        '<p class="text-muted mb-2 flex-grow-1" style="font-size:0.8rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' +
                            (post.description || 'Không có mô tả') +
                        '</p>' +
                        '<hr class="my-2" style="border-color:#F1F5F9;">' +
                        '<div class="d-flex align-items-center justify-content-between gap-1 flex-wrap">' +
                            '<span class="price-tag">' + price + '</span>' +
                            '<div class="d-flex align-items-center gap-1">' +
                                ((IS_ADMIN || String(post.user_id) === CUR_UID)
                                    ? '<a href="' + EDIT_URL + post.id + '" class="btn btn-sm btn-outline-secondary rounded-3" style="font-size:0.72rem;padding:3px 7px;" title="Chỉnh sửa">' +
                                          '<i class="fas fa-pen"></i>' +
                                      '</a>'
                                    : '') +
                                '<a href="' + MSG_URL + post.user_id + '" class="btn btn-sm btn-primary-hcmue rounded-3" style="font-size:0.75rem;" title="Nhắn tin">' +
                                    '<i class="fas fa-comment"></i>' +
                                '</a>' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex gap-3 mt-2" style="font-size:0.74rem;color:#9CA3AF;">' +
                            '<span><i class="far fa-clock me-1"></i>' + date + '</span>' +
                            '<span><i class="far fa-comment me-1"></i>' + post.comment_count + ' bình luận</span>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '</div>';
        }).join('');

        bookList.style.display = '';
    }

    function fetchBooks() {
        const keyword = searchInput.value.trim();

        // Bước 1: Hiện skeleton, ẩn nội dung cũ
        loadingState.style.display = 'flex';
        bookList.style.display     = 'none';
        emptyState.style.display   = 'none';

        // Bước 2: Xây dựng URL với tham số lọc
        const params = new URLSearchParams();
        if (currentCat) params.append('cat', currentCat);
        if (keyword)    params.append('q', keyword);

        // Bước 3: Hiện/ẩn nút "Xóa lọc"
        clearBtn.style.display = (currentCat || keyword) ? 'inline-block' : 'none';

        // Bước 4: Gọi API bằng Fetch
        fetch(API_URL + '?' + params.toString())
            .then(function (response) { return response.json(); })
            .then(function (result) {
                // Ẩn skeleton sau khi nhận được dữ liệu
                loadingState.style.display = 'none';

                lastResultJson = JSON.stringify(result.data || []);
                renderBooksList(result);
            })
            .catch(function () {
                loadingState.style.display = 'none';
                bookList.innerHTML = '<div class="col-12 text-center text-danger py-4">Không thể kết nối API. Vui lòng thử lại.</div>';
                bookList.style.display = '';
            });
    }

    // Polling chạy ẩn để cập nhật bài đăng mới / đã duyệt realtime
    function pollBooks() {
        const keyword = searchInput.value.trim();
        const params = new URLSearchParams();
        if (currentCat) params.append('cat', currentCat);
        if (keyword)    params.append('q', keyword);

        fetch(API_URL + '?' + params.toString())
            .then(function (response) { return response.json(); })
            .then(function (result) {
                const currentJson = JSON.stringify(result.data || []);
                // Chỉ vẽ lại nếu danh sách bài đăng có sự thay đổi
                if (currentJson !== lastResultJson) {
                    lastResultJson = currentJson;
                    renderBooksList(result);
                }
            })
            .catch(function (err) {
                console.warn('Lỗi đồng bộ danh sách bài đăng:', err);
            });
    }

    // =========================================================
    // Gắn sự kiện lọc danh mục vào các nút
    // =========================================================
    catButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Bỏ active tất cả, bật active nút được bấm
            catButtons.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            currentCat = btn.dataset.catId; // Đọc ID danh mục từ data-cat-id
            fetchBooks();
        });
    });

    // =========================================================
    // Gắn sự kiện tìm kiếm với Debounce (chờ 400ms sau khi gõ)
    // =========================================================
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchBooks, 400);
    });

    // =========================================================
    // Nút "Xóa lọc"
    // =========================================================
    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        currentCat = '';
        catButtons.forEach(function (b) { b.classList.remove('active'); });
        catButtons[0].classList.add('active'); // Bật lại "Tất cả"
        fetchBooks();
    });

    // =========================================================
    // Tải danh sách ngay khi trang mở (Initial Load)
    // =========================================================
    fetchBooks();

    // Tự động kiểm tra cập nhật bài đăng mỗi 6 giây
    setInterval(pollBooks, 6000);
}());
</script>
