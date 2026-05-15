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
$config['smtp_user']   = getenv('SMTP_USER') ?: '';
$config['smtp_pass']   = getenv('SMTP_PASS') ?: '';
$config['mailtype']    = 'text'; // Gửi dạng text thường
$config['charset']     = 'utf-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
