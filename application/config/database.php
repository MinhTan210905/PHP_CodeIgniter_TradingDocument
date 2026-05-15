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

$db['default'] = array(
    'dsn'      => '',
    'hostname' => getenv('DB_HOSTNAME') ?: 'localhost',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'database' => getenv('DB_NAME') ?: 'hcmue_pass_sach',
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
