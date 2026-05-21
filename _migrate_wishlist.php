<?php
$db = new mysqli('localhost', 'root', '', 'hcmue_pass_sach');
if ($db->connect_error) { echo 'FAIL: '.$db->connect_error; exit(1); }
$sql = "CREATE TABLE IF NOT EXISTS `book_wishlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_title` VARCHAR(255) NOT NULL COMMENT 'Ten sach mong muon',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT '1 = Dang theo doi, 0 = Tam tat',
    `last_notified_post_id` INT DEFAULT NULL COMMENT 'ID bai dang cuoi da thong bao',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_active_user` (`is_active`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if ($db->query($sql)) { echo 'OK: Table book_wishlists created/exists'; } else { echo 'FAIL: '.$db->error; }
$db->close();
