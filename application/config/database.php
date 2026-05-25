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
    'reconnect' => TRUE,
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
// DEBUG KẾT NỐI DATABASE
// ĐÃ XÓA debug_db endpoint vì lý do bảo mật.
// Dùng CLI hoặc kiểm tra application/logs/ để debug.
// ==========================================
