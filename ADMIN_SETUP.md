# 🎓 QNU Student Management System - Admin Feature Setup

## ⚡ Quick Start Guide

### 1. Cài đặt dữ liệu mẫu (Đặc biệt quan trọng!)

```bash
# Truy cập phpmyadmin hoặc dòng lệnh MySQL
mysql -u root -p qnu_sms < config/seed_qnu_data.sql

# Hoặc dùng phpmyadmin:
# 1. Chọn database "qnu_sms"
# 2. Tab "Import"
# 3. Chọn file "config/seed_qnu_data.sql"
# 4. Nhấn "Go"
```

### 2. Đăng nhập Admin

**Tài khoản mặc định:**
```
Username: admin
Password: password
```

**Bước:**
1. Truy cập: `http://localhost/qnu-student-management/auth/login.php`
2. Nhập tài khoản admin
3. Bạn sẽ được redirect tới `/admin/dashboard.php`

### 3. Tính năng có sẵn

#### Dashboard Admin
📊 Xem thống kê: Sinh viên, Học phần, Lịch học  
🔗 Truy cập nhanh: Thêm HP, Quản lý TKB, Tối ưu TKB

#### Quản lý Học phần
✅ Thêm / Sửa / Xóa / Tìm kiếm  
🔍 Bộ lọc theo loại và niên khóa

#### Quản lý Thời khóa biểu
✅ Thêm / Sửa / Xóa / Tìm kiếm lịch học  
🔍 Lọc theo học kỳ và năm học  
⚡ **Tối ưu tự động**: Phân bố lịch cho sinh viên dựa trên các HP đã đăng ký

---

## 🤖 Tính năng Tối ưu Thời khóa biểu

### Cách hoạt động:

1. **Bước 1**: Sinh viên đăng ký học phần  
   - Status: "Đã duyệt"
   - Hệ thống sẽ quản lý ở bảng `dang_ky_hp`

2. **Bước 2**: Admin vào `/admin/thoi_khoa_bieu/optimize.php`
   - Hệ thống liệt kê sinh viên chưa có TKB

3. **Bước 3**: Nhấn "⚡ Tối ưu ngay"
   - **Thuật toán chạy:**
     - Tìm các HP đã đăng ký của mỗi SV
     - Phân bố vào các tiết trong tuần
     - Tránh xung đột thời gian
     - Ưu tiên Thứ 2-6 (công tác)
     - Tránh Thứ 7-CN
     - Cân bằng tải mỗi ngày

4. **Bước 4**: Kết quả
   - ✅ Lịch được tạo tự động
   - 📝 Log chi tiết từng sinh viên
   - ⚠️ Hiển thị xung đột (nếu có)

### Chi tiết thuật toán:

```
for each student NOT having schedule:
  for each registered course (status="Đã duyệt"):
    for priority in [weekday-morning, weekday-afternoon, weekend]:
      for each timeslot (không xung đột):
        place course
        mark slot occupied
        break
  if not placed:
    log conflict
```

---

## 📁 Cấu trúc thư mục

```
admin/
├── dashboard.php           # Trang chủ admin
├── README.md              # Tài liệu tính năng
├── hoc_phan/
│   └── index.php          # CRUD học phần
├── thoi_khoa_bieu/
│   ├── index.php          # CRUD lịch học
│   └── optimize.php       # Tối ưu tự động
└── api/                   # (Có thể mở rộng)

includes/admin/
├── header_admin.php       # Header page
├── navbar_admin.php       # Navigation
└── footer_admin.php       # Footer
```

---

## 🎯 Ví dụ sử dụng

### Ví dụ 1: Thêm học phần

```
Admin Dashboard → Quản lý Học phần → + Thêm mới
├─ Mã HP: CNTT022
├─ Tên: Phát triển Ứng dụng Web
├─ Số TC: 3
├─ Loại: Bắt buộc
├─ HK: 4
└─ Niên khóa: 2021-2025
```

### Ví dụ 2: Tối ưu TKB cho HK5

```
1. Sinh viên đăng ký các HP cho HK5
   (Bảng: dang_ky_hp, status="Đã duyệt")

2. Admin vào: Admin Panel → Thời khóa biểu → ⚡ Tối ưu TKB
   ├─ Hiển thị: 5 sinh viên cần tạo lịch
   ├─ Mỗi SV có 3-4 HP đăng ký
   └─ Nhấn "Tối ưu ngay"

3. Hệ thống tạo lịch:
   ├─ SV#2: 4 HP → Thứ 2,3,4,5 (sáng)
   ├─ SV#3: 3 HP → Thứ 3,4,5 (sáng)
   ├─ SV#4: 3 HP → Thứ 2,4,6 (sáng)
   └─ Thành công!

4. Xem kết quả:
   Admin Panel → Thời khóa biểu
   └─ Lọc HK 5, năm học 2023-2024
   └─ Xem danh sách lịch vừa tạo
```

---

## 🔐 Bảo mật

✅ **Kiểm tra quyền admin** (`requireAdmin()`)  
✅ **Escape output** để tránh XSS  
✅ **Prepared statements** cho DB queries  
✅ **Password hash** với bcrypt  
✅ **Session management**

---

## 🐛 Troubleshooting

### Lỗi: "No permission"
→ Kiểm tra xem đã đăng nhập với tài khoản admin?

### Lỗi: Tối ưu TKB không tạo lịch
→ Kiểm tra:
- Sinh viên có đăng ký HP cho HK đó không? (status="Đã duyệt")
- Có slot trống trong lịch không?

### Lỗi: Database connection failed
→ Kiểm tra `config/database.php`:
- DB host, user, password, tên DB
- MySQL service có chạy không?

### Modal form không hiển thị
→ Kiểm tra browser console (F12)  
→ Có JavaScript error không?

---

## 📊 Test Data (QNU)

**Dữ liệu sau khi import:**
- 5 sinh viên (CNTT47A, CNTT47B)
- 20+ học phần (CNTT, GD)
- 20+ đăng ký HP (HK5)
- 4 lịch học mẫu (SV#1)

---

## 🚀 Mở rộng trong tương lai

- [ ] REST API endpoints
- [ ] Export to Excel/PDF
- [ ] Email notifications
- [ ] Manage lecturers & rooms
- [ ] Conflict detection
- [ ] Schedule visualization (calendar)
- [ ] Mobile app
- [ ] Dark theme

---

## 📞 Support

Xem chi tiết tại: `/admin/README.md`

---

**Happy coding! 🎉**
