# HCMUE Pass Sách 📚

> **Internal Book & Document Trading Platform for HCMUE Students.**

**HCMUE Pass Sách** is a professional web-based platform designed specifically for students at **Ho Chi Minh City University of Education (HCMUE)**. It facilitates the exchange, buying, and selling of textbooks and study materials within the student community.

![License](https://img.shields.io/badge/License-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg)
![Framework](https://img.shields.io/badge/Framework-CodeIgniter%203-EE4323.svg)

---

## ✨ Features

- 🔐 **Secure Authentication**: Student-only registration using university emails.
- 📖 **Book Listings**: Easy upload of books with images, categories, and condition descriptions.
- 🛡️ **Admin Approval**: Integrated moderation system to ensure quality and community standards.
- 💬 **Real-time Messaging**: Private chat system between buyers and sellers.
- 🌟 **Rating & Reviews**: Trust-based rating system for sellers.
- 📱 **Modern UI**: Professional HCMUE Branding with sticky footer and responsive design.
- 📞 **Phone Privacy**: Toggle phone number visibility for privacy protection.

---

## 🚀 Tech Stack

- **Backend**: PHP 7.4+ with **CodeIgniter 3** (MVC Architecture).
- **Database**: MySQL / MariaDB.
- **Frontend**: Bootstrap 5, FontAwesome 6, Google Fonts (Inter).
- **Styling**: Custom Vanilla CSS with HCMUE Branding Colors.

---

## 🛠️ Hướng dẫn Cài đặt / Installation Guide

1. **Clone the repository**:
   ```bash
   git clone https://github.com/nmdai679/PHP_CodeIgniter_TradingDocument.git
   ```

2. **Database Setup**:
   - Tạo database tên `hcmue_pass_sach` trong XAMPP/phpMyAdmin.
   - Import file `database.sql` vào database vừa tạo.

3. **Cấu hình Môi trường (QUAN TRỌNG ĐỂ CHẠY OTP)**:
   - Bạn sẽ thấy file `.env.example` ở thư mục gốc.
   - Hãy **Copy và Đổi tên** nó thành file tên là `.env`.
   - Mở file `.env` lên và điền thông tin của bạn vào:
     ```env
     SMTP_USER="gmail_cua_ban@gmail.com"
     SMTP_PASS="mat_khau_ung_dung_16_ki_tu"
     ```
     *(Lưu ý: `.env` là file bí mật, không được chia sẻ cho người khác).*

4. **Base URL Config**:
   - Vào `application/config/config.php` và đảm bảo `$config['base_url']` đã trỏ đúng về folder dự án của bạn (VD: `http://localhost/PHP_CodeIgniter_TradingDocument/`).

5. **Run**:
   - Mở trình duyệt và truy cập: `http://localhost/PHP_CodeIgniter_TradingDocument`.


---

## 👤 Sample Accounts

- **Admin**: `admin@hcmue.edu.vn` / password: `password`
- **User**: `nva@student.hcmue.edu.vn` / password: `password`

---

## 📜 License

Distributed under the MIT License. See `LICENSE` for more information.

---

**Developed with ❤️ for the HCMUE community.**
