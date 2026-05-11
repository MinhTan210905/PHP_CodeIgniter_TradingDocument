<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 | -------------------------------------------------------------------
 |  EMAIL SETTINGS
 | -------------------------------------------------------------------
 | Cấu hình gửi mail qua SMTP của Gmail.
 | HƯỚNG DẪN:
 | 1. smtp_user: Điền địa chỉ Gmail của bạn
 | 2. smtp_pass: Điền Mật khẩu Ứng dụng (App Password)
 */

$config['protocol']    = 'smtp';
$config['smtp_host']   = 'ssl://smtp.gmail.com';
$config['smtp_port']   = 465;
$config['smtp_user']   = 'YOUR_EMAIL_HERE@gmail.com';
$config['smtp_pass']   = 'YOUR_APP_PASSWORD_HERE';
$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
