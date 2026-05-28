# QNU Student Management System (Hệ thống Quản lý Sinh viên - Đại học Quy Nhơn)

Hệ thống quản lý học tập, đăng ký học phần, xếp lịch tự động và quản trị sinh viên toàn diện theo mô hình kiến trúc **MVC (Model-View-Controller) thuần bằng PHP**. Hệ thống tích hợp các công nghệ bảo mật cao cấp (2FA/OTP, PDO Prepared Statements) và thuật toán phân bổ thời khóa biểu thông minh.

---

## 🚀 Tính năng nổi bật

### 🔒 Phân hệ Bảo mật & Xác thực cao cấp
- **Xác thực 2 lớp (2FA/OTP):** Hệ thống tự động tạo mã OTP bảo mật và gửi trực tiếp qua Email của sinh viên/admin khi thực hiện đăng nhập.
- **Quản lý Session thông minh:** Chống tấn công chiếm quyền điều khiển phiên (**Session Hijacking**) bằng cơ chế nạp lại ID phiên (`session_regenerate_id(true)`) ngay sau khi hoàn tất xác thực OTP. Khắc phục hoàn toàn lỗi **Session Locking** trên Windows/XAMPP giúp hệ thống hoạt động trơn tru, phản hồi tức thì.
- **Bảo mật dữ liệu tuyệt đối:** Sử dụng cơ chế kết nối **PDO Prepared Statements** giúp ngăn chặn triệt để tấn công **SQL Injection**. Dữ liệu đầu ra được xử lý qua hàm helper `e()` (`htmlspecialchars`) để phòng chống tấn công **XSS**.
- **Mã hóa mật khẩu mật độ cao:** Áp dụng thuật toán **Bcrypt** tiêu chuẩn công nghiệp thông qua `password_hash()` và `password_verify()`.

### 🎓 Phân hệ Sinh viên
- **Cổng thông tin cá nhân:** Xem thông tin hồ sơ chi tiết, cập nhật thông tin liên lạc và tải lên ảnh đại diện cá nhân.
- **Đăng ký học phần trực tuyến:** Đăng ký môn học thời gian thực. Hệ thống tự động kiểm tra ràng buộc điều kiện môn học, số tín chỉ tối đa/tối thiểu được đăng ký trong học kỳ.
- **Xem Lịch học & Thời khóa biểu:** Giao diện trực quan hiển thị thời khóa biểu chi tiết theo tuần/học kỳ. Hệ thống tự động kiểm tra trùng lịch (xung đột thời gian) khi đăng ký.
- **Quản lý học tập chuyên sâu:** Tra cứu điểm thi, điểm trung bình tích lũy (GPA) theo thang điểm 4 và thang điểm 10, theo dõi biểu đồ tiến độ tích lũy tín chỉ trực quan, xem điểm rèn luyện từng kỳ.
- **Học phí trực tuyến:** Theo dõi chi tiết các khoản học phí, trạng thái đóng học phí (đã hoàn thành / còn nợ) của từng học kỳ.
- **Kho tài liệu dùng chung:** Sinh viên có thể đăng tải tài liệu học tập của mình lên hệ thống và tải xuống các tài liệu học tập hữu ích do giảng viên hoặc các sinh viên khác chia sẻ.

### 💼 Phân hệ Quản trị viên (Admin)
- **Bảng điều khiển (Dashboard) thông minh:** Biểu đồ thống kê thời gian thực về số lượng sinh viên, học phần, tỷ lệ đóng học phí, và các thông tin hệ thống quan trọng.
- **Quản lý danh mục toàn diện (CRUD):** Quản lý hồ sơ sinh viên, lớp học, tài khoản người dùng, danh mục học phần, tài liệu học tập.
- **Quản lý Điểm & Học tập:** Nhập điểm, sửa đổi và theo dõi kết quả học tập của từng lớp học, từng sinh viên cụ thể.
- **Xếp Thời khóa biểu tự động (TKB Optimizer):** Thuật toán tối ưu hóa tự động xếp lịch dựa trên số lượng sinh viên đăng ký môn học đã duyệt. Tự động tránh trùng lịch phòng, giảng viên và cân đối số tiết học giữa các ngày trong tuần (ưu tiên sáng thứ 2-6).
- **Sao lưu & Phục hồi CSDL (Data Sync):** Công cụ Export/Import cơ sở dữ liệu bằng một click chuột, xử lý luồng chạy ngầm an toàn và trực quan.
- **Thông báo hệ thống:** Soạn thảo và gửi thông báo chung đến toàn thể sinh viên hoặc thông báo riêng cho từng đối tượng cụ thể.

---

## 📁 Cấu trúc thư mục dự án (Chuẩn MVC)

Dự án được xây dựng theo mô hình **MVC** hiện đại, tách biệt hoàn toàn Logic nghiệp vụ, Dữ liệu và Giao diện qua Front Controller duy nhất:

```text
qnu-student-management/
├── app/                      # THƯ MỤC SOURCE CODE CHÍNH (MVC)
│   ├── Controllers/          # Bộ điều khiển (Controllers)
│   │   ├── Admin/            # Các Controllers quản trị (Grade, Course, Schedule, Tuition, DataSync...)
│   │   ├── AuthController.php# Xử lý xác thực đăng nhập, quên mật khẩu, 2FA/OTP Email
│   │   ├── CourseController.php # Xử lý đăng ký môn học và thời khóa biểu sinh viên
│   │   ├── DocumentController.php # Quản lý tài liệu của sinh viên
│   │   └── StudentController.php  # Quản lý hồ sơ, điểm số, học phí, tiến độ sinh viên
│   ├── Core/                 # Các lớp cốt lõi của Framework tự dựng
│   │   ├── Controller.php    # Base Controller quản lý việc render View và truyền dữ liệu
│   │   ├── Database.php      # Lớp kết nối CSDL sử dụng PDO kết hợp Prepared Statements
│   │   └── Router.php        # Bộ định tuyến URL thân thiện (Custom Routing Engine)
│   ├── Models/               # Lớp truy vấn dữ liệu và tương tác CSDL (Models)
│   │   ├── AdminCourseModel.php
│   │   ├── AdminDocumentModel.php
│   │   ├── AdminGradeModel.php
│   │   ├── AdminModel.php
│   │   ├── AdminScheduleModel.php
│   │   ├── AdminStudentModel.php
│   │   ├── AdminTuitionModel.php
│   │   ├── AdminUserModel.php
│   │   ├── CourseModel.php
│   │   ├── DocumentModel.php
│   │   ├── NotificationModel.php
│   │   ├── StudentModel.php
│   │   └── UserModel.php
│   └── Views/                # Giao diện hiển thị (Views)
│       ├── admin/            # Giao diện quản lý của Admin (Lớp, môn, điểm, TKB, học phí, đồng bộ...)
│       ├── auth/             # Giao diện đăng nhập, nạp mã OTP, đổi mật khẩu
│       └── student/          # Giao diện chức năng dành cho Sinh viên (Hồ sơ, TKB, đăng ký, tài liệu...)
├── assets/                   # Tài nguyên tĩnh (CSS, JS, Images)
│   ├── css/                  # style.css (theme chính), student.css, admin.css
│   ├── js/                   # main.js (Tích hợp SweetAlert2, AJAX), validation.js (Kiểm tra form)
│   └── img/                  # Logo, hình ảnh minh họa, ảnh đại diện mặc định
├── config/                   # Cấu hình hệ thống và dữ liệu
│   ├── database.php          # Cấu hình kết nối CSDL PDO
│   ├── constants.php         # Các hằng số định nghĩa đường dẫn toàn cục
│   ├── schema.sql            # Cấu trúc CSDL chuẩn của hệ thống
│   └── seed_qnu_data.sql     # Dữ liệu mẫu cực kỳ chi tiết từ Đại học Quy Nhơn
├── includes/                 # Giao diện và helper dùng chung
│   ├── header.php            # Thẻ mở head và CSS chung
│   ├── footer.php            # Thẻ đóng body và JS chung
│   └── session.php           # Quản lý quyền truy cập (Role-based access control)
├── storage/                  # Thư mục lưu trữ tạm thời, log hệ thống
├── uploads/                  # Thư mục chứa file tải lên (Ảnh đại diện, tài liệu học tập)
├── .gitignore                # Loại trừ các file rác, file tạm và upload cá nhân khi đẩy lên Git
├── .htaccess                 # Cấu hình Apache Rewrite Engine phục vụ Friendly URL của Router
└── index.php                 # Front Controller duy nhất tiếp nhận mọi Request hệ thống
```

---

## 🎨 Thiết kế giao diện (UI/UX)
- Giao diện được thiết kế hiện đại, responsive hoàn hảo trên mọi thiết bị (Desktop, Tablet, Mobile).
- Tông màu chủ đạo là **QNU Blue (`#0056B3`)** thanh lịch kết hợp với **Sleek Dark Theme Sidebar** chuyên nghiệp ở phân hệ Admin.
- Tương tác sống động bằng thư viện **SweetAlert2** thay thế toàn bộ hộp thoại thông báo thô sơ của trình duyệt bằng những hiệu ứng mượt mà và trực quan.

---

## 🛠️ Hướng dẫn cài đặt nhanh

### 1. Yêu cầu hệ thống
- Máy chủ web **Apache** (XAMPP, Laragon, WAMP hoặc tương đương).
- Phiên bản **PHP >= 7.4** (đã bật tiện ích mở rộng PDO MySQL).
- Cơ sở dữ liệu **MySQL >= 5.7** hoặc **MariaDB >= 10.3**.

### 2. Cài đặt mã nguồn
Đưa thư mục dự án vào thư mục gốc của web server (ví dụ: `C:/xampp/htdocs/qnu-student-management/`).

### 3. Cấu hình hệ thống
- Mở file `config/database.php` và cập nhật thông tin kết nối CSDL:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', ''); // Mật khẩu CSDL của bạn
  define('DB_NAME', 'qnu_sms');
  ```
- Mở file `config/constants.php` để định nghĩa URL chạy dự án:
  ```php
  define('BASE_URL', 'http://localhost/qnu-student-management');
  ```

### 4. Khởi tạo Cơ sở dữ liệu
1. Tạo một cơ sở dữ liệu mới trong phpMyAdmin với tên `qnu_sms` và bảng mã collation `utf8mb4_unicode_ci`.
2. Import file cấu trúc CSDL tại `config/schema.sql`.
3. Import file dữ liệu mẫu tại `config/seed_qnu_data.sql` để hệ thống có đầy đủ dữ liệu mẫu mở rộng phục vụ việc thử nghiệm.

---

## 🔑 Tài khoản thử nghiệm (Demo Accounts)

Hệ thống đã cấu hình sẵn các tài khoản demo tương ứng với các vai trò để bạn trải nghiệm:

| Tên đăng nhập | Mật khẩu | Vai trò | Mô tả |
| :--- | :--- | :--- | :--- |
| **admin** | `password` | **Quản trị viên (Admin)** | Toàn quyền kiểm soát và vận hành hệ thống |
| **sv001** | `password` | **Sinh viên** | Nguyễn Văn An - Sinh viên ngành CNTT |
| **sv002** | `password` | **Sinh viên** | Trần Thị Bình - Sinh viên ngành CNTT |

---

## 🛠️ Quy trình Đóng góp & Phát triển

1. Fork dự án này về tài khoản GitHub của bạn.
2. Clone repository về local và cấu hình theo hướng dẫn cài đặt.
3. Tạo một branch mới để phát triển tính năng: `git checkout -b feature/tinh-nang-moi`.
4. Commit thay đổi: `git commit -m "Thêm tính năng mới"`.
5. Đẩy nhánh lên remote: `git push origin feature/tinh-nang-moi`.
6. Tạo một Pull Request trên GitHub để rà soát chất lượng mã nguồn.

---

**Phiên bản:** `2.0.0` (Nâng cấp kiến trúc MVC thuần, PDO, OTP/2FA Email Security và tối ưu hoá luồng đăng ký học tập).  
**Bảo quyền:** Đại học Quy Nhơn (QNU) — Khoa Công nghệ Thông tin.
