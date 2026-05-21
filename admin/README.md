# Admin Panel - Hệ thống Quản lý Sinh viên QNU

## 📋 Tính năng Admin

### 1. **Dashboard Admin** (`/admin/dashboard.php`)
- Thống kê tổng quan: Sinh viên, Học phần, Lịch học, Đã duyệt
- Hành động nhanh truy cập các tính năng chính
- Thông tin hệ thống

### 2. **Quản lý Học phần** (`/admin/hoc_phan/`)

#### Chức năng CRUD:
- **Thêm mới**: Tạo học phần mới
- **Sửa**: Cập nhật thông tin học phần
- **Xóa**: Xóa học phần khỏi hệ thống
- **Tìm kiếm**: Tìm kiếm theo mã hoặc tên học phần

#### Thông tin học phần:
- Mã HP (unique)
- Tên học phần
- Số tín chỉ (1-5)
- Loại: Bắt buộc / Tự chọn / Đại cương
- Học kỳ (1-8)
- Niên khóa

### 3. **Quản lý Thời khóa biểu** (`/admin/thoi_khoa_bieu/`)

#### Chức năng CRUD:
- **Thêm mới**: Thêm lịch học cho sinh viên
- **Sửa**: Cập nhật lịch học
- **Xóa**: Xóa lịch học
- **Tìm kiếm**: Tìm theo sinh viên hoặc học phần
- **Lọc**: Theo học kỳ và năm học

#### Thông tin lịch học:
- Sinh viên
- Học phần
- Thứ (Thứ 2 - Chủ nhật)
- Tiết bắt đầu (1-10)
- Số tiết (1-5)
- Phòng học
- Giảng viên
- Học kỳ
- Năm học

### 4. **Tối ưu Thời khóa biểu Tự động** (`/admin/thoi_khoa_bieu/optimize.php`)

#### Thuật toán tối ưu:
```
1. Tìm tất cả sinh viên chưa có TKB
2. Lấy danh sách HP đã đăng ký (trạng thái: Đã duyệt)
3. Phân bố vào các tiết tránh xung đột
4. Ưu tiên Thứ 2-6, giảm dần ưu tiên Thứ 7-CN
5. Ưu tiên các tiết sáng (1-5) trước tiết chiều
6. Cân bằng số tiết mỗi ngày
```

#### Đặc điểm:
- Tự động gán phòng học theo thứ và tiết
- Tự động gán giảng viên (hoặc sử dụng giáo viên có sẵn)
- Tránh xung đột thời gian
- Kiểm tra giới hạn tiết mỗi buổi học

---

## 🔐 Yêu cầu đăng nhập

### Tài khoản Admin mặc định:
- **Username**: `admin`
- **Password**: `password` (hash bcrypt)

### Cách đăng nhập:
1. Truy cập `/auth/login.php`
2. Nhập username: `admin`
3. Nhập password: `password`
4. Chọn **Admin Panel** từ dashboard

---

## 📊 Dữ liệu mẫu

### Dữ liệu từ Đại học Quy Nhơn:

#### Sinh viên (Ngành CNTT):
- 3121410001 - Nguyễn Văn An
- 3121410002 - Trần Thị Bình
- 3121410003 - Phạm Văn Cường
- 3121410004 - Lê Thị Dương
- 3121410005 - Hoàng Văn Em

#### Chương trình CNTT (8 học kỳ):
- **HK1-4**: Học phần bắt buộc
- **HK5-6**: Học phần tự chọn
- **HK7**: Thực tập
- **HK8**: Đồ án tốt nghiệp

---

## 💾 Cài đặt dữ liệu mẫu

Chạy file SQL để nạp dữ liệu:

```bash
mysql -u root qnu_sms < config/seed_qnu_data.sql
```

Hoặc từ phpMyAdmin:
1. Import file `config/seed_qnu_data.sql`
2. Chọn database `qnu_sms`
3. Thực thi

---

## 🎨 Giao diện

### Admin Interface Features:
- ✅ Responsive design (desktop + tablet)
- ✅ Modal forms cho CRUD operations
- ✅ Flash messages (success/danger/info)
- ✅ Real-time search & filter
- ✅ Table with actions (Edit/Delete)
- ✅ Status badges
- ✅ Icon-based UI (Font Awesome)

### CSS Classes:
```css
.admin-wrapper       /* Container chính */
.admin-container     /* Content area */
.stat-card          /* Thẻ thống kê */
.modal              /* Dialog form */
.alert              /* Thông báo */
.table-wrap         /* Bảng */
.action-bar         /* Thanh công cụ */
```

---

## 📝 Hướng dẫn sử dụng

### Thêm Học phần mới:
1. Vào **Quản lý Học phần**
2. Nhấn **+ Thêm mới**
3. Điền thông tin:
   - Mã HP (vd: CNTT001)
   - Tên HP
   - Số tín chỉ
   - Loại (Bắt buộc/Tự chọn/Đại cương)
4. Nhấn **Lưu**

### Tạo Lịch học Tự động:
1. Vào **Thời khóa biểu**
2. Nhấn **⚡ Tối ưu TKB**
3. Kiểm tra sinh viên cần tạo lịch
4. Nhấn **⚡ Tối ưu ngay**
5. Hệ thống sẽ tự động phân bố lịch học

### Sửa Lịch học Thủ công:
1. Vào **Thời khóa biểu**
2. Nhấn icon **Edit** trên hàng cần sửa
3. Cập nhật thông tin
4. Nhấn **Lưu**

---

## 🔧 API Endpoints

> Hiện tại API được tích hợp trong các trang PHP. 
> Có thể mở rộng với REST API endpoints nếu cần.

---

## ⚠️ Lưu ý quan trọng

1. **Backup dữ liệu** trước khi xóa
2. **Kiểm tra xung đột** trước khi tối ưu lịch
3. **Xác nhận lại** khi xóa học phần/lịch
4. **Sử dụng filter** để xem dữ liệu rõ ràng
5. **Quyền admin** cần đặt password mạnh

---

## 📱 Responsive Design

Hỗ trợ tốt trên:
- ✅ Desktop (1024px+)
- ✅ Tablet (768px-1024px)
- ✅ Mobile (dưới 768px)

---

## 🚀 Tính năng tiếp theo (Future)

- [ ] Xuất Excel/PDF lịch học
- [ ] Gửi email thông báo sinh viên
- [ ] Quản lý giảng viên và phòng học
- [ ] Báo cáo thống kê chi tiết
- [ ] Dark mode
- [ ] Audit log cho các thao tác

---

**Phiên bản**: 1.0.0  
**Cập nhật lần cuối**: 2024
