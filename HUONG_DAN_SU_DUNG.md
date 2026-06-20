# 🎓 HƯỚNG DẪN CÀI ĐẶT & TÀI KHOẢN TRẢI NGHIỆM HỆ THỐNG
## QNU Student Management System (Hệ thống Quản lý Sinh viên - Đại học Quy Nhơn)

---

## 🔑 1. DANH SÁCH TÀI KHOẢN MẪU THỬ NGHIỆM

Hệ thống đã được cấu hình sẵn các tài khoản demo (sau khi chạy file Seeder) để phục vụ việc kiểm tra và đánh giá toàn diện các chức năng:

### 💼 Phân hệ Quản trị viên (Admin)
Admin có toàn quyền quản lý hồ sơ sinh viên, lớp sinh hoạt, môn học, lớp học phần, nhập điểm, tự động xếp thời khóa biểu, gửi thông báo và sao lưu CSDL.

*   **Danh sách tài khoản Admin:** `admin`, `phi`, `chi`, `khai`, `huy`
*   **Mật khẩu đăng nhập:** `password`
*   **Ví dụ đăng nhập:**
    *   Tên đăng nhập: `admin`
    *   Mật khẩu: `password`

---

### 🎓 Phân hệ Sinh viên
Sinh viên có quyền xem hồ sơ cá nhân, thực hiện đăng ký học phần, xem thời khóa biểu, tra cứu bảng điểm học tập chi tiết (CPA, tín chỉ tích lũy), xem điểm rèn luyện, xem học phí và chia sẻ tài liệu học tập.

*   **Các tài khoản Sinh viên lớp chuyên ngành (KTPM K47):**
    *   Mã sinh viên (Username): Từ `4751190001` đến `4751190060` (Bỏ qua số 0016, 0035, 0048 do không có trong danh sách gốc). Các tài khoản này đã được cập nhật đầy đủ thông tin hồ sơ, đăng ký học phần, lịch học và bảng điểm học tập chi tiết qua các kỳ học.
    *   Mật khẩu đăng nhập: `Student@123`
    *   Ví dụ:
        *   Tên đăng nhập: `4751190001`
        *   Mật khẩu: `Student@123`

*   **Các tài khoản Sinh viên của 50 ngành học khác:**
    *   Mã sinh viên (Username): Bắt đầu từ `4751200001` đến `4751200500`
    *   Mật khẩu đăng nhập: `Student@123`

---

## 🛠️ 2. HƯỚNG DẪN CÀI ĐẶT & CHẠY CHƯƠNG TRÌNH

### 📋 Yêu cầu hệ thống tối thiểu
*   **Web Server:** Apache (tích hợp sẵn trong XAMPP, Laragon, WAMP).
*   **Phiên bản PHP:** `>= 7.4` (Đã kích hoạt extension `PDO MySQL` và `mysqli`).
*   **Cơ sở dữ liệu:** MySQL `>= 5.7` hoặc MariaDB `>= 10.3`.

---

### 💻 Quy trình thiết lập từng bước

#### **Bước 1: Sao chép mã nguồn**
Di chuyển thư mục dự án vào thư mục gốc của Web Server của bạn. 
*   Đối với XAMPP: Sao chép thư mục vào `C:\xampp\htdocs\qnu-student-management\`

#### **Bước 2: Cấu hình kết nối Cơ sở dữ liệu**
1.  Mở tệp tin `config/database.php` và cập nhật thông tin kết nối MySQL:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root'); // Username MySQL của bạn (mặc định là root)
    define('DB_PASS', '');     // Mật khẩu MySQL của bạn (mặc định trong XAMPP là để trống)
    define('DB_NAME', 'qnu_sms');
    ```
2.  Mở tệp tin `config/constants.php` và cập nhật URL chạy dự án:
    ```php
    define('BASE_URL', 'http://localhost/qnu-student-management');
    ```

#### **Bước 3: Tạo Cơ sở dữ liệu trống**
1.  Truy cập vào công cụ quản trị cơ sở dữ liệu (ví dụ: `http://localhost/phpmyadmin/`).
2.  Tạo một cơ sở dữ liệu mới có tên là `qnu_sms` với bảng mã (collation) là `utf8mb4_unicode_ci`.

#### **Bước 4: Nhập cấu trúc bảng CSDL (Schema)**
1.  Chọn cơ sở dữ liệu `qnu_sms` vừa tạo.
2.  Nhấp vào tab **Import** (Nhập).
3.  Chọn tệp tin cấu trúc bảng tại `config/schema.sql`.
4.  Nhấp nút **Go** (Thực hiện) để tạo các bảng CSDL cần thiết.

#### **Bước 5: Khởi tạo dữ liệu học vụ & bảng điểm**
Bạn có thể lựa chọn 1 trong 2 cách sau để nạp dữ liệu mẫu (bao gồm các ngành học, môn học và bảng điểm học tập mẫu của sinh viên):

*   **Cách 1: Nhập dữ liệu trực tiếp trên giao diện Web (Khuyên dùng - Đơn giản nhất)**
    1.  Mở trình duyệt, truy cập vào trang đăng nhập: `http://localhost/qnu-student-management/auth/login`
    2.  Đăng nhập bằng tài khoản Admin mặc định:
        *   Tên đăng nhập: `admin`
        *   Mật khẩu: `password`
    3.  Trên thanh Menu của Admin, tìm và truy cập vào mục **Đồng bộ CSDL** (hoặc **Backup / Restore**).
    4.  Nhấp chọn nút **Nhập file SQL**, tìm đến tệp tin dữ liệu mẫu tại `config/seed_qnu_data.sql` (hoặc file backup `.sql` bất kỳ mà bạn đã export trước đó) rồi nhấn xác nhận để hệ thống tự động phục hồi dữ liệu.

*   **Cách 2: Chạy script Seeder bằng dòng lệnh (Terminal)**
    1.  Mở Terminal hoặc Command Prompt (cmd) trên máy tính.
    2.  Chạy lệnh sau bằng đường dẫn PHP của bạn:
        ```bash
        C:\xampp\php\php.exe database/seeder.php
        ```
    3.  Đợi cho đến khi màn hình hiển thị thông báo chạy thành công.

#### **Bước 6: Khởi chạy và Trải nghiệm**
1.  Khởi động **Apache** và **MySQL** trên bảng điều khiển XAMPP Control Panel.
2.  Mở trình duyệt web và truy cập đường dẫn:
    `http://localhost/qnu-student-management/`
3.  Sử dụng các tài khoản mẫu ở phần 1 để bắt đầu kiểm tra và trải nghiệm các tính năng của hệ thống.
