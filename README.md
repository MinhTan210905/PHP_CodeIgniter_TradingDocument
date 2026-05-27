# HCMUE BookSwap 📚

> **Nền tảng trao đổi, mua bán sách & tài liệu nội bộ dành riêng cho sinh viên Trường Đại học Sư phạm TP.HCM.**

**HCMUE BookSwap** là một ứng dụng Web chuyên nghiệp giúp sinh viên dễ dàng chia sẻ, thanh lý giáo trình, tài liệu học tập trong khuôn khổ cộng đồng an toàn, minh bạch và tiện lợi. 

![License](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?style=for-the-badge)
![Framework](https://img.shields.io/badge/Framework-CodeIgniter%203-EE4323.svg?style=for-the-badge)
![MySQL](https://img.shields.io/badge/MySQL-Database-00758F.svg?style=for-the-badge)

---

## 🚀 Những Điểm Nổi Bật Mới Nhất (Recent Upgrades)

Chúng tôi vừa nâng cấp hệ thống lên một tầm cao mới với các tính năng hiện đại:
- 🤖 **AI Kiểm Duyệt Ngôn Từ Độc Hại (Google Gemini 3.5 Flash)**: Tích hợp mô hình trí tuệ nhân tạo Google Gemini 3.5 Flash siêu nhanh làm hệ thống kiểm duyệt tin bài công cộng. Hệ thống bao gồm 2 lớp phòng vệ: Lớp 1 (Local Regex - 0ms) để chặn ngay lập tức từ tục tĩu phổ biến giúp tiết kiệm tối đa API Key, Lớp 2 (Gemini 3.5 Flash) phân tích sâu sắc từ lóng, toxic Gen Z Việt Nam trong đăng bán sách (`Trade`) và bình luận (`Comment`).
- 🖼️ **Hệ thống Đa Ảnh (Multi-Image Upload)**: Cho phép đăng tải ảnh bìa kèm 5 ảnh chi tiết thực tế cùng lúc với giao diện xem ảnh mượt mà có tích hợp nút chuyển ảnh nhanh (Carousel) trái phải.
- 🔔 **Mong muốn sách & Email HTML chuyên nghiệp (Wishlist)**: Cho phép sinh viên đăng ký theo dõi sách cần tìm. Hệ thống tự động so khớp thông minh (không dấu tiếng Việt, trùng >70%) và lập tức gửi **Email thông báo dạng HTML tuyệt đẹp** trực tiếp vào hòm thư.
- 📄 **Tải Lên & Xem Trước PDF (PDF Reader)**: Hỗ trợ người dùng đính kèm file PDF tài liệu học tập, giáo trình trực quan khi đăng bài. Người mua có thể đọc thử ngay trên trình duyệt thông qua khung iframe lớn.
- 🏷️ **Lọc & Cấu hình Tình Trạng Sách**: Phân chia chi tiết tài liệu theo trạng thái "Mới" (New) hoặc "Đã sử dụng" (Used) đi kèm bộ lọc nâng cao trên thanh header.
- 💬 **Hội thoại Realtime 100% & Popup Chat thông minh**: Đồng bộ kép real-time thời gian thực (Pusher Channels + Polling 3 giây dự phòng) đảm bảo nhắn tin không trễ trên localhost. Bóng bóng Chat popup góc phải màn hình hiển thị đầy đủ thời gian nhận tin (`HH:MM DD/MM`) và tích xanh trạng thái cực kỳ chuyên nghiệp.

---

## ✨ Tính Năng Cốt Lõi (Key Features)

*   🔐 **Đăng ký Bằng Mail Trường**: Chỉ chấp nhận tài khoản có đuôi `@student.hcmue.edu.vn`.
*   📧 **Xác thực OTP Qua Gmail**: Bảo vệ tài khoản tuyệt đối thông qua việc gửi mã OTP 6 số qua email bằng SMTP chuẩn Google.
*   📖 **Quản Lý Bài Đăng Đa Năng**: Người dùng có thể tạo, chỉnh sửa bài viết, bổ sung ảnh phụ, tải lên tệp PDF đính kèm, cấu hình tình trạng sách (mới/cũ), cập nhật số lượng, hoặc ẩn số điện thoại riêng tư.
*   🛍️ **Hệ Thống Đơn Hàng & Phê Duyệt**: Quy trình yêu cầu mua (Escrow Flow) chuyên nghiệp giúp Người bán kiểm duyệt yêu cầu trước khi giao dịch.
*   🛡️ **Bảng Điều Khiển Của Admin**: Dành cho quản trị viên kiểm duyệt bài đăng, chặn tài khoản vi phạm, duyệt yêu cầu rút tiền.
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
2. Chọn database vừa tạo, click **Import** và dẫn file `database.sql` (nằm ở thư mục gốc dự án) vào để khởi tạo toàn bộ cấu trúc bảng và dữ liệu mẫu.

*(Lưu ý: Không cần chạy các file `migrate_...php` nữa vì `database.sql` đã bao gồm toàn bộ các bảng Ví điện tử, Wishlist, và AI).*

### Bước 3: Thiết lập Môi trường (.env)
Hệ thống sử dụng các dịch vụ bên thứ 3 (Gửi Email, AI, Thanh toán):
1. Tại thư mục gốc, copy file `.env.example` thành file `.env`.
2. Mở `.env` lên và điền các API Key tương ứng:
```env
# 1. Cấu hình Email SMTP (Bắt buộc để đăng ký tài khoản)
SMTP_USER="dia_chi_gmail_cua_ban@gmail.com"
SMTP_PASS="xxxx xxxx xxxx xxxx" (Mật khẩu ứng dụng Google 16 ký tự)

# 2. Cấu hình Google Gemini API Key (Phát hiện ngôn từ độc hại công cộng)
GEMINI_API_KEY="your_google_gemini_api_key_here"

# 3. Cấu hình PayOS (Cổng nạp tiền tự động)
PAYOS_CLIENT_ID="your_client_id"
PAYOS_API_KEY="your_api_key"
PAYOS_CHECKSUM_KEY="your_checksum_key"
```

### Bước 4: Cấu hình Đường dẫn (Base URL)
Mở file `application/config/config.php` và tinh chỉnh lại dòng:
```php
$config['base_url'] = 'http://localhost/PHP_CodeIgniter_TradingDocument/';
```
*(Đảm bảo tên thư mục khớp chính xác với tên thư mục bạn đặt trong htdocs)*

### Bước 5: Khởi chạy
Mở trình duyệt bất kì và truy cập: 👉 `http://localhost/PHP_CodeIgniter_TradingDocument`

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
- **Frontend Lib**: Bootstrap 5.3, FontAwesome 6.4, Google Inter / Plus Jakarta Sans Webfonts.
- **AI & Payment**: Google Gemini 3.5 Flash API, PayOS VietQR API, Pusher Channels WebSocket.

---

## 📜 Giấy phép & Bản quyền

Dự án được phát hành dưới giấy phép MIT License. 

✨ **Được phát triển bằng cả ❤️ dành cho cộng đồng sinh viên Sư Phạm HCMUE.**
