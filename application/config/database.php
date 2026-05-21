<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 | -------------------------------------------------------------------
 |  DATABASE CONNECTIVITY SETTINGS
 | -------------------------------------------------------------------
 | Chỉnh sửa đúng thông tin kết nối MySQL của bạn tại đây.
 */

$active_group = 'default';
$query_builder = TRUE;

$db_host = isset($_ENV['DB_HOSTNAME']) ? $_ENV['DB_HOSTNAME'] : (isset($_SERVER['DB_HOSTNAME']) ? $_SERVER['DB_HOSTNAME'] : (getenv('DB_HOSTNAME') ?: 'localhost'));
$db_user = isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : (isset($_SERVER['DB_USERNAME']) ? $_SERVER['DB_USERNAME'] : (getenv('DB_USERNAME') ?: 'root'));
$db_pass = isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : (isset($_SERVER['DB_PASSWORD']) ? $_SERVER['DB_PASSWORD'] : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ''));
$db_name = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : (isset($_SERVER['DB_NAME']) ? $_SERVER['DB_NAME'] : (getenv('DB_NAME') ?: 'hcmue_pass_sach'));

$db['default'] = array(
    'dsn'      => '',
    'hostname' => $db_host,
    'username' => $db_user,
    'password' => $db_pass,
    'database' => $db_name,
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE,
);

// ==========================================
// ĐOẠN CODE DEBUG KẾT NỐI DATABASE (Rất hữu ích cho hosting)
// Truy cập: tên-miền-của-bạn/?debug_db=1 để kiểm tra trực tiếp
// ==========================================
if (isset($_GET['debug_db'])) {
    $test_conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (!$test_conn) {
        echo "<h3>[DEBUG] Kết nối Database thất bại:</h3>";
        echo "<b>Host:</b> " . htmlspecialchars($db_host) . "<br>";
        echo "<b>User:</b> " . htmlspecialchars($db_user) . "<br>";
        echo "<b>Database:</b> " . htmlspecialchars($db_name) . "<br>";
        echo "<b>Mật khẩu dài:</b> " . strlen($db_pass) . " ký tự<br>";
        echo "<b>Lỗi chi tiết từ MySQL:</b> " . htmlspecialchars(mysqli_connect_error()) . "<br>";
        die("<hr>Vui lòng kiểm tra lại file .env đã upload hoặc cấu hình database trên hosting.");
    } else {
        mysqli_close($test_conn);
        die("<h3>[DEBUG] Kết nối Database THÀNH CÔNG!</h3>");
    }
}

