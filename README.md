# QNU Student Management System

Hệ thống Quản lý Sinh viên — Trường Đại học Quy Nhơn  
Công nghệ: **PHP thuần + MySQL + HTML/CSS/JS**

---

## ⚡ Cài đặt nhanh

### 1. Yêu cầu
- XAMPP / Laragon / WAMP (PHP >= 7.4, MySQL 5.7+)
- Web server: Apache

### 2. Cấu hình
```text
Đặt thư mục dự án vào: C:/xampp/htdocs/qnu-student-management/
URL truy cập: http://localhost/qnu-student-management/
```

### 3. Khởi tạo CSDL
1. Mở phpMyAdmin → Import file `config/schema.sql` (hoặc `config/seed_qnu_data.sql` để có dữ liệu mẫu đầy đủ).
2. Hoặc chạy lệnh: `mysql -u root -p < config/schema.sql`

### 4. Cấu hình kết nối
Mở `config/database.php`, chỉnh sửa:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // mật khẩu MySQL của bạn (để trống nếu dùng XAMPP mặc định)
define('DB_NAME', 'qnu_sms');
```

Mở `config/constants.php`, chỉnh `BASE_URL`:
```php
define('BASE_URL', 'http://localhost/qnu-student-management');
```

### 5. Tài khoản demo
| Username | Mật khẩu | Vai trò |
|----------|----------|---------|
| sv001    | password | Sinh viên |
| sv002    | password | Sinh viên |
| admin    | password | Quản trị viên (Admin) |

---

## 📁 Cấu trúc dự án

```text
qnu-student-management/
├── admin/                  # [MỚI] DÀNH CHO QUẢN TRỊ VIÊN
│   ├── dashboard.php       # Tổng quan hệ thống
│   ├── sinh_vien/          # Quản lý hồ sơ sinh viên
│   ├── users/              # Quản lý tài khoản đăng nhập
│   ├── hoc_phan/           # Quản lý danh mục học phần
│   ├── thoi_khoa_bieu/     # Quản lý TKB và xếp lịch tự động
│   ├── tai_lieu/           # Quản lý tài liệu học tập
│   ├── hoc_phi/            # Quản lý và báo cáo học phí
│   └── data_sync/          # Sao lưu (Export) & Phục hồi (Import) CSDL
├── auth/                   # XỬ LÝ XÁC THỰC
│   ├── login.php           # Trang đăng nhập
│   ├── process_login.php   # Xử lý đăng nhập
│   └── logout.php          # Đăng xuất
├── config/                 # CẤU HÌNH
│   ├── database.php        # Kết nối DB
│   ├── constants.php       # Hằng số hệ thống
│   ├── schema.sql          # SQL tạo bảng + dữ liệu mẫu cơ bản
│   └── seed_qnu_data.sql   # Dữ liệu mẫu mở rộng
├── includes/               # THÀNH PHẦN GIAO DIỆN CHUNG
│   ├── admin/              # Giao diện của Admin (Sidebar dọc, Header, Footer)
│   ├── session.php         # Quản lý phiên + helper functions
│   ├── header.php          # HTML head chung cho Sinh viên
│   ├── navbar_student.php  # Navbar ngang cho Sinh viên
│   └── footer.php          # Footer chung
├── student/                # DÀNH CHO SINH VIÊN
│   ├── dashboard.php       # Tổng quan cá nhân
│   ├── ca_nhan/            # Xem & cập nhật thông tin, tiến độ
│   ├── hoc_tap/            # Điểm số, TKB, Chương trình đào tạo, Học phí
│   └── truc_tuyen/         # Đăng ký môn, Chia sẻ & tải tài liệu
├── assets/                 # TÀI NGUYÊN TĨNH
│   ├── css/                # style.css (chung), student.css (giao diện)
│   ├── js/                 # main.js (Xử lý UI, SweetAlert2), validation.js
│   └── img/                # Hình ảnh, avatar mặc định
├── uploads/                # THƯ MỤC LƯU TRỮ
│   └── avatars/            # Avatar người dùng
└── index.php               # Điều hướng tự động (Login / Dashboard)
```

---

## ✨ Tính năng nổi bật

### Dành cho Admin
- **Quản lý toàn diện:** Sinh viên, Tài khoản, Học phần, Tài liệu.
- **Tối ưu Thời khóa biểu:** Tự động xếp lịch học thông minh tránh trùng lặp.
- **Quản lý Học phí:** Theo dõi đóng học phí, in báo cáo thống kê.
- **Đồng bộ Dữ liệu:** Nhập/Xuất (Import/Export) cơ sở dữ liệu hệ thống chỉ với một click (sử dụng luồng xử lý lệnh an toàn trên Windows).
- **Giao diện hiện đại:** Sidebar dọc bên trái, thiết kế UI/UX theo xu hướng, Header cố định (Sticky).

### Dành cho Sinh viên
- **Cổng thông tin:** Xem thông tin cá nhân, điểm số, điểm rèn luyện, tiến độ tín chỉ.
- **Tương tác:** Đăng ký học phần trực tuyến, xem lịch học (TKB).
- **Tài liệu:** Chia sẻ và tải xuống tài liệu học tập với các bạn khác.

---

## 🎨 Thiết kế & Trải nghiệm (UI/UX)
- **Màu chính:** `#0056B3` (QNU Blue), kết hợp giao diện tối (`#1a1a2e`) cho Admin Sidebar.
- **Font chữ:** Noto Sans / Roboto (Google Fonts).
- **Thông báo (Alerts):** Tích hợp thư viện **SweetAlert2** thay thế toàn bộ các hộp thoại xác nhận mặc định của trình duyệt mang lại trải nghiệm chuyên nghiệp.
- **Responsive:** Tương thích hoàn toàn trên thiết bị di động và máy tính bảng.

## 🔒 Bảo mật
- **Mã hóa:** Password hash bằng `password_verify()` (chuẩn bcrypt).
- **Phiên (Session):** Session regenerate chống đánh cắp phiên (Session Hijacking).
- **Cơ sở dữ liệu:** Sử dụng Prepared statements (`mysqli`) chống SQL Injection 100%.
- **Bảo vệ XSS:** Hàm helper `e()` (sử dụng `htmlspecialchars()`) bảo vệ dữ liệu đầu ra khỏi các tấn công XSS.
