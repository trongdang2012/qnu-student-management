-- ============================================================
-- QNU Student Management System - Du lieu mau Dai hoc Quy Nhon
-- Chay sau config/schema.sql
-- Tai khoan mau: admin / password, sv001..sv005 / password
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET COLLATION_CONNECTION = utf8mb4_unicode_ci;

USE `qnu_sms`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `tai_lieu`;
TRUNCATE TABLE `thoi_khoa_bieu`;
TRUNCATE TABLE `hoc_phi`;
TRUNCATE TABLE `diem_ren_luyen`;
TRUNCATE TABLE `diem_hoc_tap`;
TRUNCATE TABLE `dang_ky_hp`;
TRUNCATE TABLE `ctdt_chi_tiet`;
TRUNCATE TABLE `hoc_phan`;
TRUNCATE TABLE `sinh_vien`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Tai khoan
-- Mat khau hash bcrypt ben duoi la "password"
-- ============================================================

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'sv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(3, 'sv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(4, 'sv003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(5, 'sv004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(6, 'sv005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- ============================================================
-- Sinh vien mau theo boi canh Dai hoc Quy Nhon
-- ============================================================

INSERT INTO `sinh_vien`
(`id`, `user_id`, `ma_sv`, `ho_ten`, `ngay_sinh`, `gioi_tinh`, `dia_chi`, `email`, `so_dien_thoai`, `nganh`, `khoa`, `lop`, `nien_khoa`, `trang_thai`) VALUES
(1, 2, '3121410001', 'Nguyễn Văn An', '2003-05-15', 'Nam', '170 An Dương Vương, Quy Nhơn, Bình Định', 'an.nv3121@student.qnu.edu.vn', '0912345678', 'Công nghệ thông tin', 'Kỹ thuật - Công nghệ', 'CNTT47A', '2021-2025', 'Đang học'),
(2, 3, '3121410002', 'Trần Thị Bình', '2003-08-22', 'Nữ', '45 Nguyễn Huệ, Quy Nhơn, Bình Định', 'binh.tt3121@student.qnu.edu.vn', '0923456789', 'Công nghệ thông tin', 'Kỹ thuật - Công nghệ', 'CNTT47A', '2021-2025', 'Đang học'),
(3, 4, '3121410003', 'Phạm Văn Cường', '2003-03-10', 'Nam', '67 Hùng Vương, Quy Nhơn, Bình Định', 'cuong.pv3121@student.qnu.edu.vn', '0934567890', 'Công nghệ thông tin', 'Kỹ thuật - Công nghệ', 'CNTT47A', '2021-2025', 'Đang học'),
(4, 5, '3121410004', 'Lê Thị Dương', '2003-12-08', 'Nữ', '89 Trần Phú, Quy Nhơn, Bình Định', 'duong.lt3121@student.qnu.edu.vn', '0945678901', 'Công nghệ thông tin', 'Kỹ thuật - Công nghệ', 'CNTT47B', '2021-2025', 'Đang học'),
(5, 6, '3121410005', 'Hoàng Văn Em', '2003-06-20', 'Nam', '12 Lý Tự Trọng, Quy Nhơn, Bình Định', 'em.hv3121@student.qnu.edu.vn', '0956789012', 'Công nghệ thông tin', 'Kỹ thuật - Công nghệ', 'CNTT47B', '2021-2025', 'Đang học');

-- ============================================================
-- Hoc phan mau nganh Cong nghe thong tin
-- ============================================================

INSERT INTO `hoc_phan` (`id`, `ma_hp`, `ten_hp`, `so_tin_chi`, `loai`, `hoc_ky`, `nien_khoa`) VALUES
(1, 'CNTT001', 'Nhập môn Công nghệ thông tin', 3, 'Đại cương', 1, '2021-2025'),
(2, 'CNTT002', 'Lập trình căn bản', 4, 'Bắt buộc', 1, '2021-2025'),
(3, 'GD001', 'Triết học Mác - Lênin', 3, 'Đại cương', 1, '2021-2025'),
(4, 'GD002', 'Toán cao cấp A1', 4, 'Đại cương', 1, '2021-2025'),
(5, 'GD003', 'Tiếng Anh 1', 3, 'Đại cương', 1, '2021-2025'),
(6, 'GD005', 'Giáo dục thể chất 1', 1, 'Đại cương', 1, '2021-2025'),

(7, 'CNTT003', 'Cấu trúc dữ liệu', 3, 'Bắt buộc', 2, '2021-2025'),
(8, 'CNTT004', 'Giải thuật', 3, 'Bắt buộc', 2, '2021-2025'),
(9, 'CNTT008', 'Kiến trúc máy tính', 3, 'Đại cương', 2, '2021-2025'),
(10, 'GD004', 'Tiếng Anh 2', 3, 'Đại cương', 2, '2021-2025'),
(11, 'GD006', 'Toán cao cấp A2', 4, 'Đại cương', 2, '2021-2025'),

(12, 'CNTT005', 'Lập trình hướng đối tượng', 3, 'Bắt buộc', 3, '2021-2025'),
(13, 'CNTT006', 'Cơ sở dữ liệu', 3, 'Bắt buộc', 3, '2021-2025'),
(14, 'CNTT009', 'Kỹ năng lập trình nâng cao', 3, 'Bắt buộc', 3, '2021-2025'),
(15, 'GD007', 'Toán rời rạc', 3, 'Đại cương', 3, '2021-2025'),

(16, 'CNTT010', 'Lập trình Web', 3, 'Bắt buộc', 4, '2021-2025'),
(17, 'CNTT011', 'Mạng máy tính', 3, 'Bắt buộc', 4, '2021-2025'),
(18, 'CNTT012', 'Hệ điều hành', 3, 'Bắt buộc', 4, '2021-2025'),
(19, 'GD008', 'Lý thuyết đồ thị', 3, 'Đại cương', 4, '2021-2025'),

(20, 'CNTT013', 'An toàn thông tin', 3, 'Tự chọn', 5, '2021-2025'),
(21, 'CNTT014', 'Trí tuệ nhân tạo', 3, 'Tự chọn', 5, '2021-2025'),
(22, 'CNTT015', 'Lập trình ứng dụng Mobile', 3, 'Tự chọn', 5, '2021-2025'),
(23, 'CNTT016', 'Phát triển ứng dụng Desktop', 3, 'Tự chọn', 5, '2021-2025'),

(24, 'CNTT017', 'Công nghệ Cloud Computing', 3, 'Tự chọn', 6, '2021-2025'),
(25, 'CNTT018', 'Big Data & Analytics', 3, 'Tự chọn', 6, '2021-2025'),
(26, 'CNTT019', 'Blockchain', 3, 'Tự chọn', 6, '2021-2025'),
(27, 'CNTT020', 'Thực tập tốt nghiệp', 5, 'Bắt buộc', 7, '2021-2025'),
(28, 'CNTT021', 'Đồ án tốt nghiệp', 7, 'Bắt buộc', 8, '2021-2025');

-- Chuong trinh dao tao nganh CNTT
INSERT INTO `ctdt_chi_tiet` (`nganh`, `hoc_phan_id`, `hoc_ky`)
SELECT 'Công nghệ thông tin', id, hoc_ky FROM `hoc_phan`;

-- ============================================================
-- Dang ky hoc phan HK5 nam hoc 2023-2024
-- ============================================================

INSERT INTO `dang_ky_hp` (`sinh_vien_id`, `hoc_phan_id`, `hoc_ky`, `nam_hoc`, `trang_thai`) VALUES
(1, 20, '5', '2023-2024', 'Đã duyệt'),
(1, 21, '5', '2023-2024', 'Đã duyệt'),
(1, 22, '5', '2023-2024', 'Đã duyệt'),
(1, 23, '5', '2023-2024', 'Đã duyệt'),

(2, 21, '5', '2023-2024', 'Đã duyệt'), 
(2, 22, '5', '2023-2024', 'Đã duyệt'),
(2, 24, '5', '2023-2024', 'Đã duyệt'),

(3, 20, '5', '2023-2024', 'Đã duyệt'),
(3, 23, '5', '2023-2024', 'Đã duyệt'),
(3, 25, '5', '2023-2024', 'Đã duyệt'),

(4, 20, '5', '2023-2024', 'Đã duyệt'),
(4, 21, '5', '2023-2024', 'Đã duyệt'),
(4, 22, '5', '2023-2024', 'Đã duyệt'),
(4, 24, '5', '2023-2024', 'Đã duyệt'),

(5, 22, '5', '2023-2024', 'Đã duyệt'),
(5, 23, '5', '2023-2024', 'Đã duyệt'),
(5, 25, '5', '2023-2024', 'Đã duyệt');

-- Lich mau cho mot sinh vien, admin co the xep lai bang chuc nang tu dong
INSERT INTO `thoi_khoa_bieu`
(`sinh_vien_id`, `hoc_phan_id`, `thu`, `tiet_bat_dau`, `so_tiet`, `phong_hoc`, `giang_vien`, `hoc_ky`, `nam_hoc`) VALUES
(1, 20, 2, 1, 3, 'A101', 'TS. Nguyễn Văn Hùng', 5, '2023-2024'),
(1, 21, 3, 1, 3, 'A102', 'ThS. Trần Thị Lan', 5, '2023-2024'),
(1, 22, 4, 1, 3, 'Lab IT', 'TS. Lê Văn Minh', 5, '2023-2024'),
(1, 23, 5, 1, 3, 'A201', 'ThS. Phạm Thị Hoa', 5, '2023-2024');

-- Diem va hoc phi mau de cac trang sinh vien van co du lieu
INSERT INTO `diem_hoc_tap`
(`sinh_vien_id`, `hoc_phan_id`, `hoc_ky`, `nam_hoc`, `diem_cc`, `diem_gk`, `diem_ck`, `diem_tong`, `diem_chu`, `diem_he4`) VALUES
(1, 1, 1, '2021-2022', 9, 7.5, 8, 8.1, 'A', 3.7),
(1, 2, 1, '2021-2022', 8, 8, 8.5, 8.25, 'B+', 3.5),
(1, 7, 2, '2021-2022', 8, 8, 7.5, 7.7, 'B+', 3.5),
(1, 12, 3, '2022-2023', 8, 7, 8, 7.8, 'B+', 3.5),
(1, 16, 4, '2022-2023', 9, 8, 8.5, 8.45, 'A', 3.7);

INSERT INTO `diem_ren_luyen` (`sinh_vien_id`, `hoc_ky`, `nam_hoc`, `diem`, `xep_loai`) VALUES
(1, 1, '2021-2022', 85, 'Tốt'),
(1, 2, '2021-2022', 80, 'Tốt'),
(1, 3, '2022-2023', 78, 'Khá'),
(1, 4, '2022-2023', 82, 'Tốt');

INSERT INTO `hoc_phi` (`sinh_vien_id`, `hoc_ky`, `nam_hoc`, `so_tien`, `da_nop`, `han_nop`, `trang_thai`) VALUES
(1, 1, '2021-2022', 8500000, 8500000, '2021-10-15', 'Đã nộp'),
(1, 2, '2021-2022', 8500000, 8500000, '2022-03-15', 'Đã nộp'),
(1, 3, '2022-2023', 9000000, 9000000, '2022-10-15', 'Đã nộp'),
(1, 4, '2022-2023', 9000000, 4500000, '2023-03-15', 'Nợ'),
(1, 5, '2023-2024', 9500000, 0, '2023-10-15', 'Chưa nộp');

COMMIT;
