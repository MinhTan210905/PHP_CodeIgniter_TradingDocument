# HCMUE Pass Sách 📚

> **Nền tảng trao đổi, mua bán sách & tài liệu nội bộ dành riêng cho sinh viên Trường Đại học Sư phạm TP.HCM.**

**HCMUE Pass Sách** là một ứng dụng Web chuyên nghiệp giúp sinh viên dễ dàng chia sẻ, thanh lý giáo trình, tài liệu học tập trong khuôn khổ cộng đồng an toàn, minh bạch và tiện lợi. 

![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=for-the-badge)
![Framework](https://img.shields.io/badge/Framework-CodeIgniter%203-EE4323.svg?style=for-the-badge)
![MySQL](https://img.shields.io/badge/MySQL-Database-00758F.svg?style=for-the-badge)

---

## 🚀 Những Điểm Nổi Bật Gần Đây (Recent Upgrades)

Chúng tôi vừa nâng cấp hệ thống lên một tầm cao mới với loạt tính năng UI/UX đỉnh cao:
- 🖼️ **Hệ thống Đa Ảnh (Multi-Image Upload)**: Cho phép đăng tải ảnh bìa kèm 5 ảnh chi tiết thực tế cùng lúc.
- 🛍️ **Trình xem ảnh Shopee-Style**: Giao diện trang chi tiết tích hợp thanh cuộn Thumbnail linh hoạt, tự động đổi ảnh lớn với hiệu ứng chuyển mờ (Fade-in) êm ái như trên sàn TMĐT thực thụ!
- 🔍 **Thanh Lọc "Khác" Thông Minh**: Tự động gom nhóm gọn gàng các danh mục dài dòng vào một Dropdown bay tự do, giải phóng không gian màn hình cực kì tinh tế và thanh lịch.
- 🔑 **Cơ chế Tự Phục Hồi (Self-Healing Header)**: Loại bỏ hoàn toàn lỗi mất dữ liệu danh mục khi đổi trang hoặc thao tác Đăng xuất.

---

## ✨ Tính Năng Cốt Lõi (Key Features)

*   🔐 **Đăng ký Bằng Mail Trường**: Chỉ chấp nhận tài khoản có đuôi `@student.hcmue.edu.vn`.
*   📧 **Xác thực OTP Qua Gmail**: Bảo vệ tài khoản tuyệt đối thông qua việc gửi mã OTP 6 số qua email bằng SMTP chuẩn Google.
*   📖 **Quản Lý Bài Đăng Đa Năng**: Người dùng có thể tạo, chỉnh sửa bài viết, bổ sung ảnh phụ, cập nhật số lượng, hoặc ẩn số điện thoại riêng tư.
*   🛍️ **Hệ Thống Đơn Hàng & Phê Duyệt**: Quy trình yêu cầu mua (Escrow Flow) chuyên nghiệp giúp Người bán kiểm duyệt yêu cầu trước khi giao dịch.
*   🛡️ **Bảng Điều Khiển Của Admin**: Dành cho quản trị viên kiểm duyệt bài đăng mới, chặn tài khoản vi phạm, tùy chỉnh cấu hình Duyệt Tự Động.
*   💬 **Chat Trực Tuyến**: Hệ thống nhắn tin tích hợp hiển thị thông báo tự động và đường dẫn tắt (CTA Link) đến chi tiết đơn hàng.
*   🌟 **Đánh Giá & Xếp Hạng**: Tích lũy sao uy tín cho chủ tiệm dựa trên trải nghiệm thực tế.

---

## 🛠️ Cài đặt Nhanh (Quick Installation Guide)

### Bước 1: Tải mã nguồn
```bash
git clone https://github.com/nmdai679/PHP_CodeIgniter_TradingDocument.git
```

### Bước 2: Thiết lập Cơ sở dữ liệu (MySQL)
1. Mở **XAMPP / phpMyAdmin** và tạo một database mới có tên: `hcmue_pass_sach`
2. Chọn database vừa tạo, click **Import** và dẫn file `database.sql` (nằm ở thư mục gốc dự án) vào để khởi tạo toàn bộ cấu trúc bảng.

### Bước 3: Khởi tạo dữ liệu Ví điện tử HCMUEPay
Mở Terminal/Command Prompt tại thư mục dự án và chạy lệnh sau để tự động tạo các bảng Ví điện tử và cấp phát ví cho người dùng hiện tại:
```bash
php migrate_wallet.php
```

### Bước 4: Thiết lập OTP Email & PayOS (Vô cùng quan trọng)
Hệ thống sử dụng SMTP để gửi thư OTP xác thực:
1. Tại thư mục gốc, tìm file `.env.example`.
2. Sao chép và đổi tên thành `.env`.
3. Mở `.env` lên và điền tài khoản Gmail cùng Mật khẩu ứng dụng (App Password 16 ký tự) của bạn:
```env
SMTP_USER="dia_chi_gmail_cua_ban@gmail.com"
SMTP_PASS="xxxx xxxx xxxx xxxx"
```
*(Mẹo: Bạn cần bật 2FA trong tài khoản Google -> Bảo mật -> Tạo Mật khẩu ứng dụng).*

### Bước 4: Cấu hình Đường dẫn (Base URL)
Mở file `application/config/config.php` và tinh chỉnh lại dòng:
```php
$config['base_url'] = 'http://localhost/PHP_CodeIgniter_TradingDocument/';
```
*(Đảm bảo tên thư mục khớp chính xác với tên thư mục bạn đặt trong htdocs)*

### Bước 5: Khởi chạy
Mở trình duyệt bất kì và truy cập:
👉 `http://localhost/PHP_CodeIgniter_TradingDocument`

---

## 👤 Tài Khoản Mẫu Đăng Nhập (Demo Accounts)

| Vai trò | Email Đăng Nhập | Mật khẩu |
| :--- | :--- | :--- |
| **Quản Trị Viên (Admin)** | `admin@hcmue.edu.vn` | `password` |
| **Người Dùng Thường** | `nva@student.hcmue.edu.vn` | `password` |

---

## 🛠️ Stack Công Nghệ Sử Dụng

- **Backend Core**: PHP 7.4 ~ 8.2 với Framework **CodeIgniter 3** (Mô hình MVC).
- **Database Engine**: MySQL / MariaDB 10.4+.
- **Frontend Lib**: Bootstrap 5.3, FontAwesome 6.4, Google Inter Webfonts.
- **Styling**: Custom Vanilla CSS3 kết hợp với Bảng màu Nhận Diện Thương Hiệu Trường ĐHSP TP.HCM.

---

## 📜 Giấy phép & Bản quyền

Dự án được phát hành dưới giấy phép MIT License. 

✨ **Được phát triển bằng cả ❤️ dành cho cộng đồng sinh viên Sư Phạm HCMUE.**
