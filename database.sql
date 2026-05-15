-- ============================================================
-- HCMUE PASS SÁCH - Database Schema v2.0
-- Trường Đại học Sư phạm TP.HCM
-- ============================================================

CREATE DATABASE IF NOT EXISTS hcmue_pass_sach DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hcmue_pass_sach;

-- ============================================================
-- BẢNG USERS (Người dùng)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    phone_visible TINYINT(1) DEFAULT 0 COMMENT '0=Ẩn, 1=Hiển thị',
    role ENUM('admin', 'user') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- BẢNG CATEGORIES (Danh mục môn học)
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-book'
);

-- ============================================================
-- BẢNG POSTS (Bài đăng pass sách)
-- ============================================================
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) DEFAULT 'assets/uploads/default.png',
    status ENUM('pending', 'available', 'sold') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ============================================================
-- BẢNG COMMENTS (Bình luận trên bài đăng)
-- ============================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- BẢNG RATINGS (Đánh giá người bán 1-5 sao)
-- ============================================================
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL COMMENT 'Người đánh giá',
    seller_id INT NOT NULL COMMENT 'Người bán được đánh giá',
    post_id INT NOT NULL COMMENT 'Bài đăng liên quan',
    stars TINYINT NOT NULL CHECK (stars BETWEEN 1 AND 5),
    comment TEXT DEFAULT NULL COMMENT 'Nhận xét kèm theo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (reviewer_id, post_id) COMMENT 'Mỗi người chỉ đánh giá 1 lần / bài',
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- ============================================================
-- BẢNG MESSAGES (Chat riêng giữa 2 người)
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    post_id INT DEFAULT NULL COMMENT 'Bài đăng liên quan (nếu có)',
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0 COMMENT '0=Chưa đọc, 1=Đã đọc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE SET NULL
);

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- Admin + User mẫu (password: 'admin123' và 'user123' dùng password_hash)
INSERT INTO users (full_name, username, email, password, phone, phone_visible, role) VALUES
('Nguyễn Văn Admin', 'admin', 'admin@hcmue.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 1, 'admin'),
('Nguyễn Văn A', 'nguyenvana', 'nva@student.hcmue.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 1, 'user'),
('Lê Thị B', 'lethib', 'ltb@student.hcmue.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 0, 'user');

-- Ghi chú: password mẫu trên đều là 'password' (Laravel's default hash for testing)
-- Trong thực tế, dùng: password_hash('yourpassword', PASSWORD_DEFAULT)

INSERT INTO categories (category_name, icon) VALUES
('Các môn Đại cương', 'fas fa-book'),
('Công nghệ Thông tin', 'fas fa-laptop-code'),
('Tâm lý học - Giáo dục', 'fas fa-brain'),
('Toán học & Ứng dụng', 'fas fa-calculator'),
('Ngữ văn & Ngôn ngữ', 'fas fa-feather-alt'),
('Sư phạm tiếng Anh', 'fas fa-language'),
('Sư phạm tiếng Trung/Nhật/Hàn', 'fas fa-globe-asia'),
('Khoa học Tự nhiên (Lý, Hóa, Sinh)', 'fas fa-flask'),
('Khoa học Xã hội (Sử, Địa, GDCD)', 'fas fa-globe'),
('Mầm non & Tiểu học', 'fas fa-child'),
('Nghệ thuật & Thể chất', 'fas fa-palette'),
('Giáo trình Ngoại ngữ (IELTS, TOEIC...)', 'fas fa-passport'),
('Tài liệu ôn thi / Trắc nghiệm', 'fas fa-file-alt'),
('Khác / Tổng hợp', 'fas fa-folder-plus');

INSERT INTO posts (user_id, category_id, title, description, price, image_url, status) VALUES
(2, 2, 'Giáo trình C++ và Lập trình Hướng đối tượng', 'Sách còn mới 90%, không ghi chú, giá rẻ cho ae khóa dưới.', 85000, 'assets/uploads/default.png', 'available'),
(3, 1, 'Triết học Mác - Lênin (Bản chuẩn)', 'Sách có highlight nhẹ vài chương đầu, đọc kỹ bao qua môn.', 40000, 'assets/uploads/default.png', 'available'),
(2, 3, 'Combo IELTS Cambridge 15-18', 'Sạch sẽ, tặng kèm file nghe PDF và flashcard từ vựng.', 350000, 'assets/uploads/default.png', 'sold');

INSERT INTO ratings (reviewer_id, seller_id, post_id, stars, comment) VALUES
(3, 2, 1, 5, 'Sách mới y như mô tả, giao dịch nhanh, tin tưởng được!'),
(2, 3, 2, 4, 'Sách ok, người bán dễ chịu. Sẽ mua lại.');

INSERT INTO comments (post_id, user_id, content) VALUES
(1, 3, 'Bạn ơi sách này còn bán không? Mình cần gấp.'),
(1, 2, 'Còn bạn nhé, nhắn tin mình để hỏi thêm nhé!'),
(2, 2, 'Sách ở khoa nào vậy bạn?');

-- ============================================================
-- NÂNG CẤP v3.0 — Shopee-style Order System
-- ============================================================

-- Thêm số lượng vào bài đăng
ALTER TABLE posts ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER price;

-- ============================================================
-- BẢNG ORDERS (Đơn hàng — luồng mua bán)
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    post_id       INT NOT NULL,
    seller_id     INT NOT NULL,
    buyer_id      INT NOT NULL,
    quantity      INT NOT NULL DEFAULT 1,
    note          TEXT COMMENT 'Ghi chú của người mua',
    status        ENUM('pending','confirmed','completed','disputed','rejected','cancelled')
                  NOT NULL DEFAULT 'pending',
    reject_reason TEXT COMMENT 'Lý do từ chối / tranh chấp',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id)   REFERENCES posts(id)  ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (buyer_id)  REFERENCES users(id)  ON DELETE CASCADE
);

-- Thêm order_id vào ratings (chỉ đánh giá khi có đơn completed)
ALTER TABLE ratings ADD COLUMN order_id INT NULL AFTER post_id,
    ADD FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- Thêm cột show_sold_history vào users (quyền hiện/ẩn lịch sử trên sàn cá nhân)
ALTER TABLE users ADD COLUMN show_sold_history TINYINT(1) DEFAULT 1 AFTER phone_visible;

-- Thêm cột is_banned vào users (quyền khoá tài khoản)
ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0;

-- ============================================================
-- BẢNG SETTINGS (Cài đặt hệ thống cấu hình động)
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    skey VARCHAR(50) PRIMARY KEY,
    svalue TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (skey, svalue) VALUES ('auto_approve_new', '0'), ('auto_approve_edit', '0');

-- ============================================================
-- BẢNG POST_IMAGES (Lưu trữ nhiều hình ảnh chi tiết của Sách)
-- ============================================================
CREATE TABLE IF NOT EXISTS post_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
