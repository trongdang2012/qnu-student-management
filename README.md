# QNU Student Management System

Hệ thống Quản lý Sinh viên — Trường Đại học Quy Nhơn  
Công nghệ: **PHP thuần + MySQL + HTML/CSS/JS**

---

## ⚡ Cài đặt nhanh

### 1. Yêu cầu
- XAMPP / Laragon / WAMP (PHP >= 7.4, MySQL 5.7+)
- Web server: Apache

### 2. Cấu hình
```
Đặt thư mục dự án vào: C:/xampp/htdocs/qnu-student-management/
URL truy cập: http://localhost/qnu-student-management/
```

### 3. Khởi tạo CSDL
1. Mở phpMyAdmin → Import file `config/schema.sql`
2. Hoặc chạy: `mysql -u root -p < config/schema.sql`

### 4. Cấu hình kết nối
Mở `config/database.php`, chỉnh:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // mật khẩu MySQL của bạn
define('DB_NAME', 'qnu_sms');
```

Mở `config/constants.php`, chỉnh BASE_URL:
```php
define('BASE_URL', 'http://localhost/qnu-student-management');
```

### 5. Tài khoản demo
| Username | Mật khẩu | Vai trò |
|----------|----------|---------|
| sv001    | password | Sinh viên |
| sv002    | password | Sinh viên |
| admin    | password | Admin |

---

## 📁 Cấu trúc dự án

```
qnu-student-management/
├── config/
│   ├── database.php        # Kết nối DB
│   ├── constants.php       # Hằng số hệ thống
│   └── schema.sql          # SQL tạo bảng + dữ liệu mẫu
├── includes/
│   ├── session.php         # Quản lý phiên + helper functions
│   ├── header.php          # HTML head chung
│   ├── navbar_student.php  # Top navbar sinh viên
│   └── footer.php          # Footer + scripts
├── auth/
│   ├── login.php           # Trang đăng nhập
│   ├── process_login.php   # Xử lý đăng nhập
│   └── logout.php          # Đăng xuất
├── student/
│   ├── dashboard.php       # Tổng quan
│   ├── ca_nhan/
│   │   ├── thong_tin.php   # UC1: Xem thông tin
│   │   ├── cap_nhat.php    # UC2: Sửa SĐT, Email
│   │   └── tien_do.php     # UC3: Tiến độ tín chỉ
│   ├── hoc_tap/
│   │   ├── chuong_trinh.php    # UC4: CTDT
│   │   ├── thoi_khoa_bieu.php  # UC5: TKB Grid
│   │   ├── diem_hoc_tap.php    # UC6: Điểm + CPA
│   │   ├── diem_ren_luyen.php  # UC7: Điểm rèn luyện
│   │   └── hoc_phi.php         # UC8: Học phí
│   └── truc_tuyen/
│       ├── dang_ky.php         # UC9: Đăng ký HP
│       ├── chia_se_tl.php      # UC10: Tài liệu chia sẻ
│       └── download.php        # Tải xuống file
├── assets/
│   ├── css/
│   │   ├── style.css       # CSS toàn hệ thống
│   │   └── student.css     # CSS giao diện sinh viên
│   ├── js/
│   │   ├── main.js         # JS chính (nav, tabs, upload...)
│   │   └── validation.js   # Validate form client-side
│   └── img/
│       └── default-avatar.png
├── uploads/                # Thư mục chứa file tải lên
└── index.php               # Redirect tự động
```

---

## 🎨 Thiết kế

- **Màu chính:** `#0056B3` (QNU Blue)
- **Font:** Roboto (Google Fonts)
- **Layout sinh viên:** Top Navbar + max-width 1200px
- **Responsive:** Mobile-friendly

## 🔒 Bảo mật

- Password hash: `password_verify()` (bcrypt)
- Session regenerate sau login
- Prepared statements chống SQL Injection
- `htmlspecialchars()` chống XSS
