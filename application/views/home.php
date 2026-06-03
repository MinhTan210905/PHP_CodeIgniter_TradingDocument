
<!-- Search & Filter Bar (API-driven) -->
<div class="search-section">
    <div class="container">
        <div class="d-flex gap-3 align-items-center flex-wrap">
            <!-- Ô tìm kiếm: id="apiSearchInput" để JS lắng nghe sự kiện -->
            <div class="search-input-wrap flex-grow-1 position-relative" style="min-width:200px;max-width:400px;">
                <i class="fas fa-search search-icon" id="searchIconBtn" style="cursor: pointer; z-index: 10;"></i>
                <input type="text" id="apiSearchInput" autocomplete="off"
                       value="<?= htmlspecialchars($keyword ?? '') ?>"
                       placeholder="Tìm sách, giáo trình...">
                
                <!-- Dropdown Lịch sử & Gợi ý tìm kiếm -->
                <div id="searchSuggestionsDropdown" class="position-absolute bg-white rounded-3 shadow-lg border border-light p-2 mt-2 w-100 d-none" 
                     style="top: 100%; left: 0; z-index: 10000; max-height: 380px; overflow-y: auto;">
                    <!-- Lịch sử tìm kiếm -->
                    <div id="searchHistorySection">
                        <div class="d-flex align-items-center justify-content-between px-2 py-1 text-secondary fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <span>LỊCH SỬ TÌM KIẾM</span>
                            <button type="button" class="btn btn-link p-0 text-muted text-decoration-none" onclick="clearAllSearchHistory()" style="font-size: 0.7rem;">Xóa tất cả</button>
                        </div>
                        <div id="searchHistoryList" class="d-flex flex-column gap-1"></div>
                    </div>
                    
                    <!-- Từ khóa thịnh hành -->
                    <div id="searchTrendingSection" class="mt-2.5">
                        <div class="px-2 py-1 text-secondary fw-bold d-flex align-items-center gap-1.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="fas fa-fire text-danger" style="animation: bounce-gentle 1.5s infinite;"></i>
                            <span>TỪ KHÓA THỊNH HÀNH</span>
                        </div>
                        <div id="searchTrendingList" class="d-flex flex-wrap gap-2 p-2"></div>
                    </div>
                    
                    <!-- Gợi ý liên quan -->
                    <div id="searchSuggestionsSection" class="mt-2 d-none">
                        <div class="px-2 py-1 text-secondary fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            GỢI Ý LIÊN QUAN
                        </div>
                        <div id="searchSuggestionsList" class="d-flex flex-column gap-1"></div>
                    </div>
                </div>
            </div>
            
            <!-- Dòng thông báo chạy chữ (Marquee Banner) chiếm chỗ trống -->
            <?php
            $CI_ann =& get_instance();
            $CI_ann->load->model('Setting_model');
            $site_announcement = $CI_ann->Setting_model->get('site_announcement', 'Chào mừng đến với diễn đàn pass tài liệu của Trường Đại học Sư phạm thành phố Hồ Chí Minh');
            if (!empty(trim($site_announcement))):
            ?>
            <div class="search-announcement-marquee flex-grow-1 d-flex align-items-center" style="min-width: 250px; overflow: hidden; background: linear-gradient(90deg, #F0F9FF 0%, #E0F2FE 100%); border: 1.5px solid #BAE6FD; border-radius: 20px; height: 42px; padding: 0 16px 0 38px; position: relative;">
                <div class="d-flex align-items-center" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); z-index: 2; pointer-events: none;">
                    <i class="fas fa-bullhorn text-primary" style="font-size:0.9rem; animation: marquee-bell 1.5s infinite;"></i>
                </div>
                <div class="search-marquee-scroll-wrap" style="width: 100%; overflow: hidden; white-space: nowrap;">
                    <span class="search-marquee-text-content" style="display: inline-block; padding-left: 100%; animation: search-marquee-scroll-animation 28s linear infinite; font-size: 0.82rem; font-weight: 600; color: #0369A1; cursor: default;">
                        <?= htmlspecialchars($site_announcement) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

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

    <div class="row">
        <!-- Main Content (Full Width) -->
        <div class="col-12">
            <!-- Section Title & Sort -->
            <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-3 px-4 rounded-4 shadow-sm border border-light flex-wrap gap-3">
                <div class="d-flex align-items-center flex-grow-1">
                    <h2 class="section-title mb-0 fs-5 d-flex align-items-center">
                        <i class="fas fa-book-open text-primary-mid me-2" style="font-size:1.1rem;"></i>
                        <span id="pageTitleText">Tài liệu đang được trao đổi</span>
                    </h2>
                    <span id="resultCount" class="ms-3 badge bg-light text-secondary border px-2 py-1 fw-medium" style="font-size:0.85rem;">
                        0 kết quả
                    </span>
                </div>
                
                <div class="d-flex align-items-center gap-2 sort-bar">
                    <span class="text-secondary small fw-600 d-none d-sm-inline">Sắp xếp:</span>
                    <button class="btn btn-sm sort-btn active fw-semibold" data-sort="latest" onclick="setSort('latest')">Mới nhất</button>
                    <button class="btn btn-sm sort-btn fw-semibold bg-light text-secondary border-0" data-sort="popular" onclick="setSort('popular')">Phổ biến</button>
                    <button class="btn btn-sm sort-btn fw-semibold bg-light text-secondary border-0" data-sort="relevance" onclick="setSort('relevance')">Liên quan</button>
                    <select class="form-select form-select-sm sort-select border-0 shadow-none bg-light text-secondary fw-semibold" style="width: auto; cursor:pointer;" id="sort_select" onchange="setSort(this.value)">
                        <option value="">Giá</option>
                        <option value="price_asc">Giá: Thấp đến Cao</option>
                        <option value="price_desc">Giá: Cao đến Thấp</option>
                    </select>
                    
                    <!-- Nút Lọc thu gọn -->
                    <button type="button" class="btn btn-sm btn-primary-hcmue d-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold border-0 shadow-sm transition-all"
                            id="toggleFilterBtn"
                            data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas"
                            style="border-radius:20px !important;">
                        <i class="fas fa-filter" style="font-size:0.8rem;"></i>
                        <span>Bộ lọc</span>
                        <span id="filterActiveDot" class="bg-danger rounded-circle d-none" style="width: 6px; height: 6px; margin-left: 2px;"></span>
                    </button>
                </div>
            </div>

            <!-- Cards Grid — nội dung do API trả về sẽ được đổ vào đây qua Javascript -->
            <div id="book-list" class="row g-4"></div>

            <!-- Template thông báo không có kết quả (ẩn mặc định) -->
            <div id="empty-state" class="text-center py-5" style="display:none;">
                <i class="fas fa-box-open" style="font-size:3rem;color:#CBD5E1;"></i>
                <p class="mt-3 text-muted px-3" id="emptyStateText" style="font-size:0.95rem; max-width: 600px; margin: 0 auto;">Không có bài đăng nào phù hợp với bộ lọc.</p>
                <button type="button" class="btn btn-outline-primary btn-sm mt-3 rounded-pill px-4 fw-bold shadow-sm" id="emptyStateBtn" onclick="clearAllFilters()">Xóa tìm kiếm & bộ lọc</button>
            </div>

            <!-- Skeleton loading — hiển thị khi đang chờ API trả dữ liệu -->
            <div id="loading-state" class="row g-4">
                <?php for($i=0;$i<4;$i++): ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="card border-0 p-3" style="border-radius:var(--card-radius);box-shadow:var(--shadow-sm);">
                        <div style="height:180px;background:#EFF6FF;border-radius:12px;animation:pulse 1.2s infinite;"></div>
                        <div style="height:14px;background:#EFF6FF;border-radius:4px;margin-top:14px;animation:pulse 1.2s infinite;"></div>
                        <div style="height:14px;background:#EFF6FF;border-radius:4px;margin-top:8px;width:60%;animation:pulse 1.2s infinite;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

</div>

<!-- Offcanvas Filter (Right-aligned) -->
<div class="offcanvas offcanvas-end rounded-start-4" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="width: 380px; border-left: none; z-index: 10050;">
    <div class="offcanvas-header border-bottom py-3 px-4">
        <h5 class="offcanvas-title fw-bold text-primary-mid d-flex align-items-center" id="filterOffcanvasLabel">
            <i class="fas fa-filter me-2" style="font-size: 1.05rem;"></i>Bộ Lọc Tìm Kiếm
        </h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        
        <!-- Tình trạng -->
        <div class="mb-4">
            <div class="fw-bold small text-secondary mb-2 text-uppercase" style="letter-spacing:0.5px; font-size:0.75rem;">Tình trạng</div>
            <div class="form-check mb-2">
                <input class="form-check-input filter-radio shadow-none" type="radio" name="condition" value="" id="cond_all" checked onchange="fetchBooks()">
                <label class="form-check-label small fw-semibold text-dark" for="cond_all">Tất cả tình trạng</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input filter-radio shadow-none" type="radio" name="condition" value="new" id="cond_new" onchange="fetchBooks()">
                <label class="form-check-label small fw-semibold text-dark" for="cond_new">Sách mới</label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input filter-radio shadow-none" type="radio" name="condition" value="used" id="cond_used" onchange="fetchBooks()">
                <label class="form-check-label small fw-semibold text-dark" for="cond_used">Sách đã sử dụng</label>
            </div>
        </div>

        <!-- Đánh giá -->
        <div class="mb-4">
            <div class="fw-bold small text-secondary mb-2 text-uppercase" style="letter-spacing:0.5px; font-size:0.75rem;">Đánh giá người bán</div>
            <div class="d-flex flex-column gap-2">
                <!-- 5 sao -->
                <div class="form-check">
                    <input class="form-check-input filter-radio shadow-none" type="radio" name="rating" value="5" id="rate_5" onchange="fetchBooks()">
                    <label class="form-check-label small fw-semibold text-dark d-flex align-items-center gap-1" for="rate_5">
                        <span>5</span><i class="fas fa-star text-warning" style="font-size:0.8rem;"></i><span>sao</span>
                    </label>
                </div>
                <!-- > 4 sao -->
                <div class="form-check">
                    <input class="form-check-input filter-radio shadow-none" type="radio" name="rating" value="4" id="rate_4" onchange="fetchBooks()">
                    <label class="form-check-label small fw-semibold text-dark d-flex align-items-center gap-1" for="rate_4">
                        <span>&gt; 4</span><i class="fas fa-star text-warning" style="font-size:0.8rem;"></i><span>sao</span>
                    </label>
                </div>
                <!-- > 3 sao -->
                <div class="form-check">
                    <input class="form-check-input filter-radio shadow-none" type="radio" name="rating" value="3" id="rate_3" onchange="fetchBooks()">
                    <label class="form-check-label small fw-semibold text-dark d-flex align-items-center gap-1" for="rate_3">
                        <span>&gt; 3</span><i class="fas fa-star text-warning" style="font-size:0.8rem;"></i><span>sao</span>
                    </label>
                </div>
                <!-- > 2 sao -->
                <div class="form-check">
                    <input class="form-check-input filter-radio shadow-none" type="radio" name="rating" value="2" id="rate_2" onchange="fetchBooks()">
                    <label class="form-check-label small fw-semibold text-dark d-flex align-items-center gap-1" for="rate_2">
                        <span>&gt; 2</span><i class="fas fa-star text-warning" style="font-size:0.8rem;"></i><span>sao</span>
                    </label>
                </div>
                <!-- > 1 sao -->
                <div class="form-check">
                    <input class="form-check-input filter-radio shadow-none" type="radio" name="rating" value="1" id="rate_1" onchange="fetchBooks()">
                    <label class="form-check-label small fw-semibold text-dark d-flex align-items-center gap-1" for="rate_1">
                        <span>&gt; 1</span><i class="fas fa-star text-warning" style="font-size:0.8rem;"></i><span>sao</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Khoảng giá -->
        <div class="mb-4">
            <div class="fw-bold small text-secondary mb-2 text-uppercase" style="letter-spacing:0.5px; font-size:0.75rem;">Khoảng giá (đ)</div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="number" class="form-control form-control-sm text-center bg-light border-0 shadow-none py-2" id="filter_min_price" placeholder="₫ TỪ" style="border-radius: 8px;">
                <span class="text-muted">-</span>
                <input type="number" class="form-control form-control-sm text-center bg-light border-0 shadow-none py-2" id="filter_max_price" placeholder="₫ ĐẾN" style="border-radius: 8px;">
            </div>
            <button type="button" class="btn btn-primary-hcmue btn-sm w-100 fw-bold rounded-3 mt-1 py-2" onclick="fetchBooks()">Áp dụng</button>
        </div>

        
    </div>
    <div class="offcanvas-footer border-top p-3 bg-light d-flex gap-2">
        <button type="button" class="btn btn-light btn-sm flex-grow-1 border text-secondary rounded-3 py-2 fw-bold" onclick="clearAdvancedFilters()">Xóa tất cả</button>
        <button type="button" class="btn btn-primary-hcmue btn-sm flex-grow-1 rounded-3 py-2 fw-bold" data-bs-dismiss="offcanvas">Hoàn tất</button>
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

/* Gợi ý tìm kiếm */
.suggestion-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.82rem;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}
.suggestion-item:hover {
    background: #F1F5F9;
    color: var(--primary-mid);
}

/* Trending keyword chips */
.trending-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 0.8rem;
    font-weight: 550;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.trending-chip:hover {
    background: var(--primary-pale);
    border-color: var(--primary-mid);
    color: var(--primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.08);
}
.trending-chip .hot-badge {
    background: linear-gradient(135deg, #EF4444, #F59E0B);
    color: #fff;
    font-size: 0.62rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    text-transform: uppercase;
    line-height: 1;
}
@keyframes bounce-gentle {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-2px); }
}
/* CSS cho chạy chữ thông báo trang chủ */
.search-marquee-text-content {
    animation-play-state: running;
}
.search-announcement-marquee:hover .search-marquee-text-content {
    animation-play-state: paused;
}
@keyframes search-marquee-scroll-animation {
    0% { transform: translate3d(0, 0, 0); }
    100% { transform: translate3d(-100%, 0, 0); }
}
@keyframes marquee-bell {
    0%, 100% { transform: rotate(0); }
    15% { transform: rotate(-15deg); }
    30% { transform: rotate(15deg); }
    45% { transform: rotate(-10deg); }
    60% { transform: rotate(10deg); }
    75% { transform: rotate(-5deg); }
    85% { transform: rotate(5deg); }
}
</style>

<script>
(function () {
    // =========================================================
    // Biến trạng thái — lưu bộ lọc đang chọn
    // =========================================================
    let currentCat = '';    // ID danh mục đang lọc (rỗng = tất cả)
    let searchTimer = null; // Timer cho debounce

    const trendingKeywords = [
        { text: 'Tâm lý học sư phạm', hot: true },
        { text: 'Giáo trình Triết học', hot: true },
        { text: 'Lập trình C++', hot: false },
        { text: 'Tiếng Anh đại học', hot: false },
        { text: 'Phương pháp dạy học', hot: true },
        { text: 'Rèn luyện nghiệp vụ', hot: false },
        { text: 'Đại số tuyến tính', hot: false },
        { text: 'Giáo dục học', hot: false }
    ];

    function renderTrendingKeywords() {
        const listEl = document.getElementById('searchTrendingList');
        if (!listEl) return;
        listEl.innerHTML = trendingKeywords.map(k => `
            <div class="trending-chip" onclick="selectSuggestion('${k.text.replace(/'/g, "\\'")}')">
                <span>${escapeHtml(k.text)}</span>
                ${k.hot ? '<span class="hot-badge">Hot</span>' : ''}
            </div>
        `).join('');
    }

    // Lấy tham chiếu đến các phần tử HTML quan trọng
    const searchInput  = document.getElementById('apiSearchInput');
    const bookList     = document.getElementById('book-list');
    const emptyState   = document.getElementById('empty-state');
    const loadingState = document.getElementById('loading-state');
    const resultCount  = document.getElementById('resultCount');
    const clearBtn     = document.getElementById('clearFilterBtn');
    const catButtons   = document.querySelectorAll('.cat-filter-btn');

    // Các hằng số phân quyền & định danh người dùng dùng trong Javascript
    const IS_ADMIN = <?= json_encode($this->session->userdata('role') === 'admin') ?>;
    const CUR_UID = <?= json_encode((string)$this->session->userdata('user_id')) ?>;

    // URL gốc của API (dùng PHP để đảm bảo đúng domain)
    const API_URL = '<?= site_url("api/posts/search") ?>';
    const BASE_URL = '<?= base_url() ?>';
    const DETAIL_URL = '<?= site_url("trade/detail/") ?>';
    const EDIT_URL = '<?= site_url("trade/edit/") ?>';
    const MSG_URL = '<?= site_url("message/conversation/") ?>';
    const SELLER_URL = '<?= site_url("seller/") ?>';
    const DEFAULT_IMG = BASE_URL + 'assets/images/default_book.jpg';
    let lastResultJson = '';

    // =========================================================
    // Hàm gọi API và render kết quả
    // =========================================================
    function renderBooksList(result) {
        if (result.status === 404 || !result.data || !result.data.length) {
            // Trường hợp không có kết quả
            const keyword = searchInput.value.trim();
            const condChecked = document.querySelector('input[name="condition"]:checked');
            const rateChecked = document.querySelector('input[name="rating"]:checked');
            const minP = document.getElementById('filter_min_price').value;
            const maxP = document.getElementById('filter_max_price').value;
            const hasActiveFilters = (condChecked && condChecked.value !== '') ||
                                     (rateChecked !== null) ||
                                     (minP !== '') ||
                                     (maxP !== '');

            const emptyStateText = document.getElementById('emptyStateText');
            const emptyStateBtn = document.getElementById('emptyStateBtn');

            if (keyword && (hasActiveFilters || currentCat)) {
                emptyStateText.innerHTML = 'Không tìm thấy kết quả nào phù hợp với từ khóa <strong>"' + escapeHtml(keyword) + '"</strong> và bộ lọc đang chọn.';
                emptyStateBtn.textContent = 'Xóa tìm kiếm & bộ lọc';
                emptyStateBtn.style.display = 'inline-block';
            } else if (keyword) {
                emptyStateText.innerHTML = 'Không tìm thấy tài liệu nào phù hợp với từ khóa <strong>"' + escapeHtml(keyword) + '"</strong>.';
                emptyStateBtn.textContent = 'Xóa từ khóa tìm kiếm';
                emptyStateBtn.style.display = 'inline-block';
            } else if (hasActiveFilters || currentCat) {
                emptyStateText.innerHTML = 'Không có tài liệu nào phù hợp với bộ lọc đang chọn.';
                emptyStateBtn.textContent = 'Xóa tất cả bộ lọc';
                emptyStateBtn.style.display = 'inline-block';
            } else {
                emptyStateText.innerHTML = 'Hệ thống hiện tại chưa có tài liệu nào được đăng tải.';
                emptyStateBtn.style.display = 'none';
            }

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
                            '<a href="' + SELLER_URL + post.user_id + '" class="d-inline-flex align-items-center gap-1 text-decoration-none" style="color:#475569 !important; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.75" onmouseout="this.style.opacity=1">' +
                                '<i class="fas fa-user-circle" style="color:#2563EB;"></i>' +
                                '<span>' + (post.full_name || post.username) + '</span>' +
                            '</a>' +
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
                                '<a href="javascript:void(0)" onclick="window.openDirectChat(' + post.user_id + ', \'' + (post.full_name || post.username || '').replace(/'/g, "\\'") + '\', \'' + (post.avatar ? (post.avatar.startsWith('http') ? post.avatar : BASE_URL + post.avatar) : '') + '\')" class="btn btn-sm btn-primary-hcmue rounded-3" style="font-size:0.75rem;" title="Nhắn tin">' +
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

    function getFilterParams() {
        const params = new URLSearchParams();
        const keyword = searchInput.value.trim();
        
        if (currentCat) params.append('cat', currentCat);
        if (keyword)    params.append('q', keyword);
        
        // condition
        const condition = document.querySelector('input[name="condition"]:checked');
        if (condition && condition.value) params.append('condition', condition.value);
        
        // rating
        const rating = document.querySelector('input[name="rating"]:checked');
        if (rating && rating.value) params.append('rating', rating.value);
        
        // price
        const minPrice = document.getElementById('filter_min_price').value;
        const maxPrice = document.getElementById('filter_max_price').value;
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        

        
        // sort
        const sortBtn = document.querySelector('.sort-btn.active');
        const sortSelect = document.getElementById('sort_select');
        let sort_by = '';
        if (sortBtn) {
            sort_by = sortBtn.dataset.sort;
        } else if (sortSelect && sortSelect.value) {
            sort_by = sortSelect.value;
        }
        if (sort_by) params.append('sort_by', sort_by);
        
        return params;
    }

    // Expose fetchBooks globally for HTML onclick handlers
    window.fetchBooks = function() {
        const keyword = searchInput.value.trim();

        // Bước 1: Hiện skeleton, ẩn nội dung cũ
        loadingState.style.display = 'flex';
        bookList.style.display     = 'none';
        emptyState.style.display   = 'none';

        // Bước 2: Xây dựng URL với tham số lọc
        const params = getFilterParams();

        // Bước 3: Hiện/ẩn nút "Xóa lọc" trên thanh tìm kiếm
        clearBtn.style.display = (currentCat || keyword) ? 'inline-block' : 'none';

        // Bước 3.5: Cập nhật chỉ báo hoạt động của bộ lọc trên nút Bộ lọc
        const condChecked = document.querySelector('input[name="condition"]:checked');
        const rateChecked = document.querySelector('input[name="rating"]:checked');
        const minP = document.getElementById('filter_min_price').value;
        const maxP = document.getElementById('filter_max_price').value;
        const hasActiveFilters = (condChecked && condChecked.value !== '') ||
                                 (rateChecked !== null) ||
                                 (minP !== '') ||
                                 (maxP !== '');

        const activeDot = document.getElementById('filterActiveDot');
        const toggleBtn = document.getElementById('toggleFilterBtn');
        if (activeDot && toggleBtn) {
            if (hasActiveFilters) {
                activeDot.classList.remove('d-none');
                toggleBtn.classList.remove('btn-primary-hcmue');
                toggleBtn.classList.add('btn-danger'); // Thay đổi sang màu đỏ để nổi bật khi bộ lọc đang hoạt động
            } else {
                activeDot.classList.add('d-none');
                toggleBtn.classList.add('btn-primary-hcmue');
                toggleBtn.classList.remove('btn-danger');
            }
        }

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

    window.setSort = function(sortValue) {
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.add('bg-light', 'text-secondary', 'border-0');
        });
        document.getElementById('sort_select').value = '';
        
        if (sortValue === 'latest' || sortValue === 'popular' || sortValue === 'relevance') {
            const btn = document.querySelector(`.sort-btn[data-sort="${sortValue}"]`);
            if (btn) {
                btn.classList.add('active');
                btn.classList.remove('bg-light', 'text-secondary', 'border-0');
            }
        } else if (sortValue) {
            document.getElementById('sort_select').value = sortValue;
        }
        
        fetchBooks();
    }

    window.clearAdvancedFilters = function() {
        document.getElementById('cond_all').checked = true;
        document.querySelectorAll('input[name="rating"]').forEach(el => el.checked = false);
        document.getElementById('filter_min_price').value = '';
        document.getElementById('filter_max_price').value = '';

        fetchBooks();
    }

    window.clearAllFilters = function() {
        searchInput.value = '';
        currentCat = '';
        catButtons.forEach(function (b) { b.classList.remove('active'); });
        if (catButtons[0]) catButtons[0].classList.add('active');

        document.getElementById('cond_all').checked = true;
        document.querySelectorAll('input[name="rating"]').forEach(el => el.checked = false);
        document.getElementById('filter_min_price').value = '';
        document.getElementById('filter_max_price').value = '';

        fetchBooks();
    }

    window.applyPriceFilter = function() {
        fetchBooks();
    }

    // Polling chạy ẩn để cập nhật bài đăng mới / đã duyệt realtime
    function pollBooks() {
        const params = getFilterParams();

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
    // Quản lý Lịch sử tìm kiếm & Gợi ý tìm kiếm
    // =========================================================
    const suggestionsDropdown = document.getElementById('searchSuggestionsDropdown');
    const historyListEl = document.getElementById('searchHistoryList');
    const suggestionsListEl = document.getElementById('searchSuggestionsList');
    const historySection = document.getElementById('searchHistorySection');
    const suggestionsSection = document.getElementById('searchSuggestionsSection');

    // Lưu từ khóa tìm kiếm khi thực hiện tìm kiếm thực tế
    function saveSearchHistory(query) {
        if (!query) return;
        let history = JSON.parse(localStorage.getItem('hcmue_search_history') || '[]');
        // Xóa trùng lặp
        history = history.filter(item => item !== query);
        // Thêm vào đầu
        history.unshift(query);
        // Giới hạn 5 từ khóa gần nhất
        history = history.slice(0, 5);
        localStorage.setItem('hcmue_search_history', JSON.stringify(history));
    }

    window.clearAllSearchHistory = function() {
        localStorage.removeItem('hcmue_search_history');
        renderSearchHistory();
    };

    window.deleteHistoryItem = function(event, item) {
        event.stopPropagation(); // Ngăn kích hoạt tìm kiếm khi click nút xóa
        let history = JSON.parse(localStorage.getItem('hcmue_search_history') || '[]');
        history = history.filter(q => q !== item);
        localStorage.setItem('hcmue_search_history', JSON.stringify(history));
        renderSearchHistory();
    };

    function renderSearchHistory() {
        const history = JSON.parse(localStorage.getItem('hcmue_search_history') || '[]');
        
        // Show the dropdown and render trending keywords
        suggestionsDropdown.classList.remove('d-none');
        const trendingSec = document.getElementById('searchTrendingSection');
        if (trendingSec) trendingSec.classList.remove('d-none');
        renderTrendingKeywords();

        if (history.length === 0) {
            historySection.classList.add('d-none');
            return;
        }

        historySection.classList.remove('d-none');

        historyListEl.innerHTML = history.map(item => `
            <div class="suggestion-item d-flex align-items-center justify-content-between py-2 px-3 rounded-2" onclick="selectSuggestion('${item.replace(/'/g, "\\'")}')">
                <div class="d-flex align-items-center gap-2">
                    <i class="far fa-clock text-muted" style="font-size:0.75rem;"></i>
                    <span>${escapeHtml(item)}</span>
                </div>
                <button type="button" class="btn btn-link p-0 text-muted" onclick="deleteHistoryItem(event, '${item.replace(/'/g, "\\'")}')">
                    <i class="fas fa-times" style="font-size:0.75rem;"></i>
                </button>
            </div>
        `).join('');
    }

    window.selectSuggestion = function(item) {
        searchInput.value = item;
        saveSearchHistory(item);
        suggestionsDropdown.classList.add('d-none');
        fetchBooks();
    };

    function renderSuggestions(query) {
        if (!query) {
            suggestionsSection.classList.add('d-none');
            renderSearchHistory();
            return;
        }

        // Lấy sách khớp từ lastResultJson làm gợi ý liên quan
        let books = [];
        try {
            books = JSON.parse(lastResultJson || '[]');
        } catch(e) {}

        const matched = books.filter(b => b.title.toLowerCase().includes(query.toLowerCase())).slice(0, 5);

        const trendingSec = document.getElementById('searchTrendingSection');

        if (matched.length === 0) {
            suggestionsSection.classList.add('d-none');
            if (historySection.classList.contains('d-none')) {
                if (trendingSec) trendingSec.classList.add('d-none');
                suggestionsDropdown.classList.add('d-none');
            }
            return;
        }

        historySection.classList.add('d-none'); // Ẩn lịch sử khi đang gõ
        if (trendingSec) trendingSec.classList.add('d-none'); // Ẩn từ khóa thịnh hành khi đang gõ
        suggestionsSection.classList.remove('d-none');
        suggestionsDropdown.classList.remove('d-none');

        suggestionsListEl.innerHTML = matched.map(b => `
            <div class="suggestion-item d-flex align-items-center gap-2 py-2 px-3 rounded-2" onclick="selectSuggestion('${b.title.replace(/'/g, "\\'")}')">
                <i class="fas fa-search text-primary-mid" style="font-size:0.75rem;"></i>
                <div class="text-truncate fw-medium" style="max-width: 90%;">${escapeHtml(b.title)}</div>
            </div>
        `).join('');
    }

    // Sự kiện focus ô tìm kiếm
    searchInput.addEventListener('focus', function() {
        const query = searchInput.value.trim();
        if (query) {
            renderSuggestions(query);
        } else {
            renderSearchHistory();
        }
    });

    // Ẩn dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDropdown.contains(e.target)) {
            suggestionsDropdown.classList.add('d-none');
        }
    });

    // =========================================================
    // Gắn sự kiện tìm kiếm với Debounce (chờ 400ms sau khi gõ)
    // =========================================================
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();
        renderSuggestions(query);
    });

    // Chỉ lưu lịch sử tìm kiếm khi bấm phím Enter
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (query) {
                saveSearchHistory(query);
            }
            suggestionsDropdown.classList.add('d-none');
            fetchBooks();
        }
    });

    // Hỗ trợ click vào icon kính lúp để tìm kiếm và lưu lịch sử
    const searchIconBtn = document.getElementById('searchIconBtn');
    if (searchIconBtn) {
        searchIconBtn.addEventListener('click', function () {
            const query = searchInput.value.trim();
            if (query) {
                saveSearchHistory(query);
            }
            suggestionsDropdown.classList.add('d-none');
            fetchBooks();
        });
    }

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
