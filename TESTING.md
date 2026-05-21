# 🧪 Testing Guide - Admin Features

## 📋 Danh sách các test case

### Test Case 1: Đăng nhập Admin
**Steps:**
1. Truy cập: `http://localhost/qnu-student-management/auth/login.php`
2. Nhập username: `admin`
3. Nhập password: `password`
4. Nhấn login

**Expected:**
- ✅ Redirect tới `/admin/dashboard.php`
- ✅ Navbar admin visible
- ✅ Hiển thị stats (sinh viên, HP, TKB)

---

### Test Case 2: Xem Dashboard
**Steps:**
1. Sau khi đăng nhập, ở trang dashboard
2. Kiểm tra 4 stat cards

**Expected:**
- ✅ Hiển thị "Tổng sinh viên": 5
- ✅ Hiển thị "Học phần": 21
- ✅ Hiển thị "Thời khóa biểu": 4 (sau import dữ liệu)
- ✅ Hiển thị "Đã duyệt": 17 (đăng ký HP)

---

### Test Case 3: Thêm Học phần mới
**Steps:**
1. Dashboard → Quản lý Học phần
2. Nhấn "+ Thêm mới"
3. Điền form:
   - Mã HP: `CNTT025`
   - Tên: `Phát triển Game`
   - Tín chỉ: `3`
   - Loại: `Tự chọn`
   - HK: `6`
   - Niên khóa: `2021-2025`
4. Nhấn "Lưu"

**Expected:**
- ✅ Modal form hiện
- ✅ Flash message "Thêm mới thành công!"
- ✅ HP mới xuất hiện trong danh sách
- ✅ Có thể tìm kiếm được bằng "CNTT025"

---

### Test Case 4: Sửa Học phần
**Steps:**
1. Trong danh sách HP, tìm HP vừa tạo (`CNTT025`)
2. Nhấn "Sửa"
3. Sửa tên thành: `Phát triển Game 3D`
4. Nhấn "Lưu"

**Expected:**
- ✅ Modal form điền dữ liệu cũ
- ✅ Dữ liệu được cập nhật
- ✅ Flash message "Cập nhật thành công!"
- ✅ Danh sách hiển thị tên mới

---

### Test Case 5: Xóa Học phần
**Steps:**
1. Tìm HP `CNTT025` trong danh sách
2. Nhấn "Xóa"
3. Xác nhận dialog "Xác nhận xóa?"

**Expected:**
- ✅ Confirm dialog hiện
- ✅ HP bị xóa khỏi DB
- ✅ Flash message "Xóa thành công!"
- ✅ HP không còn trong danh sách

---

### Test Case 6: Tìm kiếm Học phần
**Steps:**
1. Quản lý Học phần
2. Nhập "CNTT001" vào search box
3. Nhấn "Tìm"

**Expected:**
- ✅ Danh sách chỉ hiển thị HP có mã hoặc tên chứa "CNTT001"
- ✅ Tìm kiếm được theo cả mã và tên
- ✅ Có thể clear search để xem tất cả

---

### Test Case 7: Thêm Lịch học
**Steps:**
1. Dashboard → Thời khóa biểu
2. Nhấn "+ Thêm mới"
3. Điền form:
   - Sinh viên: `3121410001 - Nguyễn Văn An`
   - Học phần: `CNTT016 - Phát triển ứng dụng Desktop`
   - Thứ: `Thứ 6`
   - Tiết bắt đầu: `7`
   - Số tiết: `2`
   - Phòng: `B205`
   - Giảng viên: `TS. Lê Văn Kiệm`
4. Nhấn "Lưu"

**Expected:**
- ✅ Lịch được tạo thành công
- ✅ Xuất hiện trong danh sách
- ✅ Hiển thị đúng thứ, tiết, phòng, giảng viên

---

### Test Case 8: Lọc Thời khóa biểu
**Steps:**
1. Thời khóa biểu
2. Thay đổi bộ lọc:
   - Học kỳ: `HK5`
   - Năm học: `2023-2024`
3. Nhấn "Lọc"

**Expected:**
- ✅ URL thay đổi (query params)
- ✅ Danh sách cập nhật theo filter
- ✅ Hiển thị tất cả lịch cho HK5/2023-2024

---

### Test Case 9: Tìm kiếm Thời khóa biểu
**Steps:**
1. Thời khóa biểu (HK5)
2. Nhập "Nguyễn Văn An" vào search
3. Nhấn "Tìm"

**Expected:**
- ✅ Danh sách hiển thị lịch của sinh viên đó
- ✅ Có thể tìm theo mã SV, tên SV, hoặc tên HP

---

### Test Case 10: Tối ưu TKB Tự động
**Steps:**
1. Dashboard → "⚡ Tối ưu TKB"
2. Kiểm tra danh sách sinh viên cần tạo lịch
3. Nhấn "⚡ Tối ưu ngay"

**Expected:**
- ✅ Lịch được tạo tự động
- ✅ Flash message: "Tối ưu thành công! Tạo X lịch"
- ✅ Redirect tới danh sách TKB
- ✅ Có thể xem lịch vừa tạo (lọc HK, năm học)

---

### Test Case 11: Kiểm tra Thuật toán Tối ưu
**Steps:**
1. Tối ưu TKB cho HK5/2023-2024
2. Sau khi tối ưu, vào TKB để xem chi tiết
3. Kiểm tra lịch của từng sinh viên

**Expected (Logic kiểm tra):**
- ✅ Mỗi sinh viên có 3-4 lịch (tương ứng HP đăng ký)
- ✅ Không có xung đột thời gian (cùng thứ, tiết)
- ✅ Phân bố trên các thứ khác nhau
- ✅ Ưu tiên sáng hơn chiều (tiết 1-5 trước 6-10)
- ✅ Tránh cuối tuần nếu có thể

---

### Test Case 12: Responsive Design
**Steps:**
1. Admin Dashboard
2. Resize browser window:
   - Desktop (1200px+)
   - Tablet (768px-1024px)
   - Mobile (320px-480px)

**Expected:**
- ✅ Layout thích ứng trên tất cả kích thước
- ✅ Tables vẫn readable
- ✅ Buttons vẫn clickable
- ✅ Modal forms vẫn hiển thị đúng

---

### Test Case 13: Flash Messages
**Steps:**
1. Thêm/sửa/xóa dữ liệu
2. Kiểm tra thông báo

**Expected:**
- ✅ Success: "... thành công!" (xanh)
- ✅ Danger: "... lỗi!" (đỏ)
- ✅ Flash messages tự động biến mất khi reload

---

### Test Case 14: Modal Form Validation
**Steps:**
1. Quản lý Học phần → + Thêm mới
2. Không điền gì, nhấn "Lưu"

**Expected:**
- ✅ Browser validate: "Vui lòng điền trường này" (HTML5)
- ✅ Hoặc PHP validate: Flash message error

---

### Test Case 15: Data Integrity
**Steps:**
1. Thêm HP mã "CNTT001" (đã tồn tại)
2. Hoặc xóa HP rồi kiểm tra TKB

**Expected:**
- ✅ Không thể thêm trùng mã HP (unique constraint)
- ✅ Khi xóa HP, TKB referencing cũng bị xóa (cascade)
- ✅ DB maintains data consistency

---

## 📊 Test Data Summary

**Sau khi import `seed_qnu_data.sql`:**
```
Sinh viên:       5
Học phần:        21
Đăng ký HP (HK5): 17
TKB sẵn có:      4 (SV#1 cho HK5)
```

**Để test tối ưu TKB:**
- HK5/2023-2024 có 5 sinh viên chưa có lịch
- Mỗi SV đăng ký 3-4 HP
- Tối ưu sẽ tạo 15-20 lịch mới

---

## 🚀 Quick Test Checklist

- [ ] Import dữ liệu QNU
- [ ] Đăng nhập admin
- [ ] Xem dashboard (check stats)
- [ ] Thêm 1 HP mới
- [ ] Sửa HP vừa tạo
- [ ] Xóa HP đó
- [ ] Tìm kiếm HP
- [ ] Xem danh sách TKB
- [ ] Lọc TKB theo HK/năm
- [ ] Tìm kiếm TKB
- [ ] Tối ưu TKB tự động
- [ ] Kiểm tra lịch vừa tạo
- [ ] Test trên mobile (responsive)
- [ ] Test flash messages
- [ ] Kiểm tra DB data consistency

---

## 🐛 Debug Tips

**Nếu gặp lỗi:**

1. **Database error:**
   - Kiểm tra MySQL chạy chưa?
   - Kiểm tra database `qnu_sms` có tồn tại?
   - Chạy `seed_qnu_data.sql` chưa?

2. **Permission denied:**
   - Kiểm tra tài khoản là admin?
   - Session có active không?
   - Cookies có enable không?

3. **Modal form không show:**
   - Mở browser DevTools (F12)
   - Kiểm tra JavaScript error?
   - Kiểm tra class="active" được add không?

4. **TKB không tạo lịch:**
   - Sinh viên có đăng ký HP không?
   - Status đăng ký phải là "Đã duyệt"
   - Kiểm tra log console

---

## 📈 Performance

**Expected response time:**
- Dashboard: < 500ms
- CRUD operations: < 300ms
- Search: < 500ms
- Optimize TKB: 2-3 seconds (5 sinh viên)

---

**Good luck with testing!** 🎉
