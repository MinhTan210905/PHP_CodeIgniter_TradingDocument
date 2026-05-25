<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 | -------------------------------------------------------------------
 |  EMAIL SETTINGS
 | -------------------------------------------------------------------
 | Cấu hình gửi mail qua SMTP của Gmail.
 | HƯỚNG DẪN:
 | 1. smtp_user: Điền địa chỉ Gmail của bạn (VD: taikhoan@gmail.com)
 | 2. smtp_pass: Điền Mật khẩu Ứng dụng (App Password) gồm 16 chữ cái.
 |    - KHÔNG phải mật khẩu đăng nhập Gmail bình thường!
 |    - Cách tạo: Vào Tài khoản Google > Bảo mật > Xác minh 2 bước > Mật khẩu ứng dụng.
 */

$config['protocol']    = 'smtp';
$config['smtp_host']   = 'ssl://smtp.gmail.com';
$config['smtp_port']   = 465;
$config['smtp_user']   = isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : (isset($_SERVER['SMTP_USER']) ? $_SERVER['SMTP_USER'] : (getenv('SMTP_USER') ?: ''));
$config['smtp_pass']   = isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : (isset($_SERVER['SMTP_PASS']) ? $_SERVER['SMTP_PASS'] : (getenv('SMTP_PASS') ?: ''));
$config['mailtype']    = 'html'; // Gửi dạng HTML để hiển thị mẫu email đẹp
$config['charset']     = 'utf-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
