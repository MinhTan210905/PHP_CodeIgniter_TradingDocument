<?php
/**
 * Script tạo bảng HCMUEPay Wallet trực tiếp trên MySQL
 * Chạy 1 lần duy nhất: php migrate_wallet.php
 */
$db = new mysqli('localhost', 'root', '', 'hcmue_pass_sach');
if ($db->connect_error) {
    die("❌ Kết nối database thất bại: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

$queries = [
    // 1. Ví điện tử sinh viên
    "CREATE TABLE IF NOT EXISTS hcmuepay_wallets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL UNIQUE,
        balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Số dư khả dụng',
        holding_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Số dư tạm giữ (Escrow)',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. Lịch sử giao dịch
    "CREATE TABLE IF NOT EXISTS hcmuepay_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        wallet_id INT NOT NULL,
        order_id INT NULL COMMENT 'Liên kết đơn hàng (nếu có)',
        amount DECIMAL(15,2) NOT NULL COMMENT 'Số tiền (+nạp/-chi)',
        type ENUM('deposit','payment','receive','withdraw','refund') NOT NULL COMMENT 'Loại giao dịch',
        status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
        description TEXT COMMENT 'Nội dung giao dịch',
        payos_reference VARCHAR(100) NULL COMMENT 'Mã tham chiếu PayOS',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (wallet_id) REFERENCES hcmuepay_wallets(id) ON DELETE CASCADE,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. Yêu cầu rút tiền
    "CREATE TABLE IF NOT EXISTS hcmuepay_withdraw_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL COMMENT 'Số tiền muốn rút',
        bank_name VARCHAR(100) NOT NULL COMMENT 'Tên ngân hàng',
        account_number VARCHAR(50) NOT NULL COMMENT 'Số tài khoản',
        account_name VARCHAR(100) NOT NULL COMMENT 'Tên chủ tài khoản',
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        admin_note TEXT NULL COMMENT 'Ghi chú Admin',
        processed_at TIMESTAMP NULL COMMENT 'Thời điểm duyệt/từ chối',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. Thêm cột payment_method và payment_status vào bảng orders
    "ALTER TABLE orders ADD COLUMN payment_method ENUM('cod','wallet') DEFAULT 'cod' COMMENT 'Phương thức thanh toán'",
    "ALTER TABLE orders ADD COLUMN payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán'",
];

echo "🚀 Bắt đầu tạo bảng HCMUEPay...\n\n";

foreach ($queries as $i => $sql) {
    if ($db->query($sql)) {
        echo "✅ Query " . ($i + 1) . " thành công!\n";
    } else {
        // Bỏ qua lỗi "Duplicate column" nếu đã tồn tại
        if (strpos($db->error, 'Duplicate column') !== false) {
            echo "⚠️  Query " . ($i + 1) . " bỏ qua (cột/bảng đã tồn tại).\n";
        } else {
            echo "❌ Query " . ($i + 1) . " lỗi: " . $db->error . "\n";
        }
    }
}

// 5. Tự động tạo ví cho tất cả user hiện có (nếu chưa có)
$result = $db->query("SELECT id FROM users WHERE id NOT IN (SELECT user_id FROM hcmuepay_wallets)");
$count = 0;
while ($row = $result->fetch_assoc()) {
    $db->query("INSERT INTO hcmuepay_wallets (user_id) VALUES ({$row['id']})");
    $count++;
}
echo "\n🎒 Đã tạo ví HCMUEPay cho $count user hiện có.\n";

$db->close();
echo "\n🎉 HOÀN TẤT! Hệ thống HCMUEPay đã sẵn sàng!\n";
