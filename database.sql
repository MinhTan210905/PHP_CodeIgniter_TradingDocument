CREATE DATABASE IF NOT EXISTS hcmue_pass_sach DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hcmue_pass_sach;

SET FOREIGN_KEY_CHECKS = 0;


-- ============================================================
-- BẢNG USERS (Người dùng)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`         VARCHAR(150) NOT NULL,
    `username`          VARCHAR(100) NOT NULL UNIQUE,
    `email`             VARCHAR(100) NOT NULL UNIQUE,
    `password`          VARCHAR(255) NOT NULL,
    `phone`             VARCHAR(15) DEFAULT NULL,
    `phone_visible`     TINYINT(1) DEFAULT 0 COMMENT '0=Ẩn, 1=Hiển thị',
    `show_sold_history` TINYINT(1) DEFAULT 1,
    `role`              ENUM('admin','user') DEFAULT 'user',
    `avatar`            VARCHAR(255) DEFAULT NULL,
    `is_banned`         TINYINT(1) DEFAULT 0,
    `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG CATEGORIES (Danh mục môn học)
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL,
    `icon`          VARCHAR(50) DEFAULT 'fas fa-book'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG POSTS (Bài bán sách BookSwap)
-- ============================================================
CREATE TABLE IF NOT EXISTS `posts` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NOT NULL,
    `category_id` INT NOT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price`       DECIMAL(10,2) NOT NULL,
    `quantity`    INT NOT NULL DEFAULT 1,
    `image_url`   VARCHAR(255) DEFAULT 'assets/uploads/default.png',
    `status`      ENUM('pending','available','sold') DEFAULT 'pending',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)      ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG POST_IMAGES (Nhiều ảnh chi tiết của Sách)
-- ============================================================
CREATE TABLE IF NOT EXISTS `post_images` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `post_id`    INT NOT NULL,
    `image_url`  VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG COMMENTS (Bình luận trên bài đăng)
-- ============================================================
CREATE TABLE IF NOT EXISTS `comments` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `post_id`    INT NOT NULL,
    `user_id`    INT NOT NULL,
    `content`    TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG ORDERS (Đơn hàng — Shopee-style)
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `post_id`        INT NOT NULL,
    `seller_id`      INT NOT NULL,
    `buyer_id`       INT NOT NULL,
    `quantity`       INT NOT NULL DEFAULT 1,
    `note`           TEXT COMMENT 'Ghi chú của người mua',
    `status`         ENUM('pending','confirmed','processing','delivering','completed','disputed','rejected','cancelled') NOT NULL DEFAULT 'pending',
    `reject_reason`  TEXT COMMENT 'Lý do từ chối / tranh chấp',
    `delivery_proof` VARCHAR(255) DEFAULT NULL COMMENT 'Ảnh minh chứng giao hàng',
    `payment_method` ENUM('cod','wallet') DEFAULT 'cod' COMMENT 'Phương thức thanh toán',
    `payment_status` ENUM('unpaid','paid','refunded') DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán',
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`)   REFERENCES `posts`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`)  ON DELETE CASCADE,
    FOREIGN KEY (`buyer_id`)  REFERENCES `users`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG RATINGS (Đánh giá người bán 1-5 sao)
-- ============================================================
CREATE TABLE IF NOT EXISTS `ratings` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `reviewer_id` INT NOT NULL COMMENT 'Người đánh giá',
    `seller_id`   INT NOT NULL COMMENT 'Người bán được đánh giá',
    `post_id`     INT NOT NULL COMMENT 'Bài đăng liên quan',
    `order_id`    INT NULL COMMENT 'Đơn hàng liên quan',
    `stars`       TINYINT NOT NULL,
    `comment`     TEXT DEFAULT NULL COMMENT 'Nhận xét kèm theo',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_rating` (`reviewer_id`, `post_id`) COMMENT 'Mỗi người chỉ đánh giá 1 lần / bài',
    FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`seller_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`post_id`)     REFERENCES `posts`(`id`)   ON DELETE CASCADE,
    FOREIGN KEY (`order_id`)    REFERENCES `orders`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG MESSAGES (Chat riêng giữa 2 người)
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id`   INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `post_id`     INT DEFAULT NULL COMMENT 'Bài đăng liên quan (nếu có)',
    `content`     TEXT NOT NULL,
    `is_read`     TINYINT(1) DEFAULT 0 COMMENT '0=Chưa đọc, 1=Đã đọc',
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`)   REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`post_id`)     REFERENCES `posts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BẢNG USER_CONVERSATION_META (Cài đặt nâng cao cuộc trò chuyện)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_conversation_meta` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL,
    `other_user_id` INT NOT NULL,
    `is_pinned`     TINYINT(1) DEFAULT 0,
    `is_muted`      TINYINT(1) DEFAULT 0,
    `deleted_at`    DATETIME DEFAULT NULL,
    `updated_at`    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_conv` (`user_id`, `other_user_id`),
    FOREIGN KEY (`user_id`)       REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`other_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- BẢNG SETTINGS (Cài đặt hệ thống)
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `skey`   VARCHAR(50) PRIMARY KEY,
    `svalue` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VÍ ĐIỆN TỬ HCMUEPAY
-- ============================================================
CREATE TABLE IF NOT EXISTS `hcmuepay_wallets` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT NOT NULL UNIQUE,
    `balance`         DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Số dư khả dụng',
    `holding_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Số dư tạm giữ Escrow',
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LỊCH SỬ GIAO DỊCH VÍ
-- ============================================================
CREATE TABLE IF NOT EXISTS `hcmuepay_transactions` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `wallet_id`        INT NOT NULL,
    `order_id`         INT NULL COMMENT 'Liên kết đơn hàng (nếu có)',
    `amount`           DECIMAL(15,2) NOT NULL COMMENT 'Số tiền (+nạp/-chi)',
    `type`             ENUM('deposit','payment','receive','withdraw','refund') NOT NULL,
    `status`           ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    `description`      TEXT,
    `payos_reference`  VARCHAR(100) NULL COMMENT 'Mã tham chiếu PayOS',
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`wallet_id`) REFERENCES `hcmuepay_wallets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`)  REFERENCES `orders`(`id`)            ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- YÊU CẦU RÚT TIỀN
-- ============================================================
CREATE TABLE IF NOT EXISTS `hcmuepay_withdraw_requests` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`        INT NOT NULL,
    `amount`         DECIMAL(15,2) NOT NULL,
    `bank_name`      VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(50)  NOT NULL,
    `account_name`   VARCHAR(100) NOT NULL,
    `status`         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `admin_note`     TEXT NULL,
    `processed_at`   DATETIME NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DANH SÁCH MONG MUỐN (Wishlist nhận thông báo sách mới)
-- ============================================================
CREATE TABLE IF NOT EXISTS `book_wishlists` (
    `id`                    INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`               INT NOT NULL,
    `book_title`            VARCHAR(255) NOT NULL COMMENT 'Tên sách mong muốn',
    `is_active`             TINYINT(1) DEFAULT 1 COMMENT '1 = Đang theo dõi, 0 = Tạm tắt',
    `last_notified_post_id` INT DEFAULT NULL COMMENT 'ID bài đăng cuối đã thông báo',
    `created_at`            DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_active_user` (`is_active`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- LỊCH SỬ KIỂM DUYỆT AI (Hugging Face)
-- ============================================================
CREATE TABLE IF NOT EXISTS `ai_moderation_logs` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `content_type`     VARCHAR(50) NOT NULL COMMENT 'message, comment, post_description',
    `content_id`       INT DEFAULT NULL,
    `user_id`          INT NOT NULL,
    `raw_text`         TEXT NOT NULL,
    `label_0_score`    DECIMAL(5,4) NOT NULL,
    `label_1_score`    DECIMAL(5,4) NOT NULL,
    `label_2_score`    DECIMAL(5,4) NOT NULL,
    `prediction_label` INT NOT NULL,
    `action_taken`     VARCHAR(20) NOT NULL COMMENT 'allow, block',
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- Cài đặt mặc định
INSERT IGNORE INTO `settings` (`skey`, `svalue`) VALUES
('auto_approve_new',  '0'),
('auto_approve_edit', '0');

-- Admin + User mẫu (password: 'password')
INSERT IGNORE INTO `users` (`full_name`, `username`, `email`, `password`, `phone`, `phone_visible`, `role`) VALUES
('Nguyễn Văn Admin', 'admin',      'admin@hcmue.edu.vn',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 1, 'admin'),
('Nguyễn Văn A',     'nguyenvana', 'nva@student.hcmue.edu.vn',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 1, 'user'),
('Lê Thị B',         'lethib',     'ltb@student.hcmue.edu.vn',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 0, 'user');

-- Danh mục môn học
INSERT IGNORE INTO `categories` (`category_name`, `icon`) VALUES
('Các môn Đại cương',                   'fas fa-book'),
('Công nghệ Thông tin',                 'fas fa-laptop-code'),
('Tâm lý học - Giáo dục',              'fas fa-brain'),
('Toán học & Ứng dụng',               'fas fa-calculator'),
('Ngữ văn & Ngôn ngữ',                'fas fa-feather-alt'),
('Sư phạm tiếng Anh',                  'fas fa-language'),
('Sư phạm tiếng Trung/Nhật/Hàn',      'fas fa-globe-asia'),
('Khoa học Tự nhiên (Lý, Hóa, Sinh)', 'fas fa-flask'),
('Khoa học Xã hội (Sử, Địa, GDCD)',   'fas fa-globe'),
('Mầm non & Tiểu học',                'fas fa-child'),
('Nghệ thuật & Thể chất',             'fas fa-palette'),
('Giáo trình Ngoại ngữ (IELTS, TOEIC...)', 'fas fa-passport'),
('Tài liệu ôn thi / Trắc nghiệm',     'fas fa-file-alt'),
('Khác / Tổng hợp',                    'fas fa-folder-plus');

-- Bài đăng mẫu
INSERT IGNORE INTO `posts` (`user_id`, `category_id`, `title`, `description`, `price`, `quantity`, `image_url`, `status`) VALUES
(2, 2, 'Giáo trình C++ và Lập trình Hướng đối tượng', 'Sách còn mới 90%, không ghi chú, giá rẻ cho ae khóa dưới.', 85000, 2, 'assets/uploads/default.png', 'available'),
(3, 1, 'Triết học Mác - Lênin (Bản chuẩn)',            'Sách có highlight nhẹ vài chương đầu, đọc kỹ bao qua môn.',  40000, 1, 'assets/uploads/default.png', 'available');

-- Tạo ví HCMUEPay mẫu cho các user
INSERT IGNORE INTO `hcmuepay_wallets` (`user_id`, `balance`, `holding_balance`) VALUES
(1, 0.00, 0.00),
(2, 0.00, 0.00),
(3, 0.00, 0.00);
