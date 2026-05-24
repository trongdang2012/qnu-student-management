-- ============================================================
-- QNU Student Management System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `qnu_sms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qnu_sms`;

-- Bảng tài khoản đăng nhập
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') NOT NULL DEFAULT 'student',
  `email` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng thông tin sinh viên
CREATE TABLE IF NOT EXISTS `sinh_vien` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ma_sv` VARCHAR(20) NOT NULL UNIQUE,
  `ho_ten` VARCHAR(100) NOT NULL,
  `ngay_sinh` DATE,
  `gioi_tinh` ENUM('Nam','Nữ','Khác') DEFAULT 'Nam',
  `dia_chi` TEXT,
  `email` VARCHAR(100),
  `so_dien_thoai` VARCHAR(15),
  `nganh` VARCHAR(100),
  `khoa` VARCHAR(100),
  `lop` VARCHAR(50),
  `nien_khoa` VARCHAR(20),
  `trang_thai` ENUM('Đang học','Tạm dừng','Tốt nghiệp','Thôi học') DEFAULT 'Đang học',
  `anh_dai_dien` VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng môn học (học phần)
CREATE TABLE IF NOT EXISTS `hoc_phan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ma_hp` VARCHAR(20) NOT NULL UNIQUE,
  `ten_hp` VARCHAR(150) NOT NULL,
  `so_tin_chi` INT NOT NULL DEFAULT 3,
  `loai` ENUM('Bắt buộc','Tự chọn','Đại cương') DEFAULT 'Bắt buộc',
  `hoc_ky` INT NOT NULL DEFAULT 1,
  `nien_khoa` VARCHAR(20),
  `thu` TINYINT DEFAULT NULL COMMENT '2=Thứ 2, ..., 8=Chủ nhật',
  `tiet_bat_dau` TINYINT DEFAULT NULL,
  `so_tiet` TINYINT DEFAULT NULL,
  `phong_hoc` VARCHAR(50) DEFAULT NULL,
  `giang_vien` VARCHAR(100) DEFAULT NULL,
  `si_so_toi_da` INT DEFAULT 50,
  `si_so_hien_tai` INT DEFAULT 0,
  `ma_hp_tien_quyet` VARCHAR(20) DEFAULT NULL
) ENGINE=InnoDB;

-- Bảng chương trình đào tạo (các HP sinh viên cần học)
CREATE TABLE IF NOT EXISTS `ctdt_chi_tiet` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nganh` VARCHAR(100) NOT NULL,
  `hoc_phan_id` INT NOT NULL,
  `hoc_ky` INT NOT NULL,
  FOREIGN KEY (`hoc_phan_id`) REFERENCES `hoc_phan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng đăng ký học phần của sinh viên
CREATE TABLE IF NOT EXISTS `dang_ky_hp` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_phan_id` INT NOT NULL,
  `hoc_ky` VARCHAR(20) NOT NULL,
  `nam_hoc` VARCHAR(20) NOT NULL,
  `trang_thai` ENUM('Chờ duyệt','Đã duyệt','Từ chối','Đã hủy') DEFAULT 'Chờ duyệt',
  `ngay_dang_ky` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_dk` (`sinh_vien_id`,`hoc_phan_id`,`hoc_ky`,`nam_hoc`),
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hoc_phan_id`) REFERENCES `hoc_phan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng điểm học tập
CREATE TABLE IF NOT EXISTS `diem_hoc_tap` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_phan_id` INT NOT NULL,
  `hoc_ky` INT NOT NULL,
  `nam_hoc` VARCHAR(20) NOT NULL,
  `diem_cc` FLOAT DEFAULT NULL COMMENT 'Điểm chuyên cần (10%)',
  `diem_gk` FLOAT DEFAULT NULL COMMENT 'Điểm giữa kỳ (30%)',
  `diem_ck` FLOAT DEFAULT NULL COMMENT 'Điểm cuối kỳ (60%)',
  `diem_tong` FLOAT DEFAULT NULL COMMENT 'Điểm tổng kết (thang 10)',
  `diem_chu` VARCHAR(5) DEFAULT NULL COMMENT 'A+, A, B+, B, ...',
  `diem_he4` FLOAT DEFAULT NULL COMMENT 'Điểm hệ 4',
  UNIQUE KEY `unique_diem` (`sinh_vien_id`,`hoc_phan_id`,`hoc_ky`,`nam_hoc`),
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hoc_phan_id`) REFERENCES `hoc_phan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng điểm rèn luyện
CREATE TABLE IF NOT EXISTS `diem_ren_luyen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_ky` INT NOT NULL,
  `nam_hoc` VARCHAR(20) NOT NULL,
  `diem` INT DEFAULT 0,
  `xep_loai` VARCHAR(30) DEFAULT NULL,
  `ghi_chu` TEXT,
  UNIQUE KEY `unique_rl` (`sinh_vien_id`,`hoc_ky`,`nam_hoc`),
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng học phí
CREATE TABLE IF NOT EXISTS `hoc_phi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_ky` INT NOT NULL,
  `nam_hoc` VARCHAR(20) NOT NULL,
  `so_tien` DECIMAL(15,0) NOT NULL DEFAULT 0,
  `da_nop` DECIMAL(15,0) NOT NULL DEFAULT 0,
  `han_nop` DATE DEFAULT NULL,
  `trang_thai` ENUM('Chưa nộp','Đã nộp','Nợ') DEFAULT 'Chưa nộp',
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng thời khóa biểu
CREATE TABLE IF NOT EXISTS `thoi_khoa_bieu` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_phan_id` INT NOT NULL,
  `thu` TINYINT NOT NULL COMMENT '2=Thứ 2, 3=Thứ 3, ..., 8=Chủ nhật',
  `tiet_bat_dau` TINYINT NOT NULL,
  `so_tiet` TINYINT NOT NULL DEFAULT 3,
  `phong_hoc` VARCHAR(20),
  `giang_vien` VARCHAR(100),
  `hoc_ky` INT NOT NULL,
  `nam_hoc` VARCHAR(20) NOT NULL,
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hoc_phan_id`) REFERENCES `hoc_phan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng tài liệu chia sẻ
CREATE TABLE IF NOT EXISTS `tai_lieu` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sinh_vien_id` INT NOT NULL,
  `hoc_phan_id` INT DEFAULT NULL,
  `tieu_de` VARCHAR(200) NOT NULL,
  `mo_ta` TEXT,
  `ten_file` VARCHAR(255),
  `duong_dan` VARCHAR(500),
  `kich_thuoc` INT DEFAULT 0,
  `loai_file` VARCHAR(50),
  `luot_tai` INT DEFAULT 0,
  `ngay_dang` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hoc_phan_id`) REFERENCES `hoc_phan`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- Dữ liệu mẫu (Sample Data)
-- ============================================================

-- Tài khoản (password = 'password123' dạng hash sha256 đơn giản, thực tế dùng bcrypt)
INSERT INTO `users` (`username`, `password`, `role`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@qnu.edu.vn'),
('sv001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'nguyenvanan@gmail.com'),
('sv002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'tranthihinh@gmail.com');
-- Password mặc định: password

-- Thông tin sinh viên
INSERT INTO `sinh_vien` (`user_id`,`ma_sv`,`ho_ten`,`ngay_sinh`,`gioi_tinh`,`dia_chi`,`email`,`so_dien_thoai`,`nganh`,`khoa`,`lop`,`nien_khoa`,`trang_thai`) VALUES
(2,'3121410001','Nguyễn Văn An','2003-05-15','Nam','123 Đường Lê Lợi, Quy Nhơn, Bình Định','nguyenvanan@gmail.com','0912345678','Công nghệ thông tin','Kỹ thuật - Công nghệ','CNTT47A','2021-2025','Đang học'),
(3,'3121410002','Trần Thị Bình','2003-08-22','Nữ','45 Đường Nguyễn Huệ, Quy Nhơn, Bình Định','tranthihinh@gmail.com','0923456789','Công nghệ thông tin','Kỹ thuật - Công nghệ','CNTT47A','2021-2025','Đang học');

-- Học phần
INSERT INTO `hoc_phan` (`ma_hp`,`ten_hp`,`so_tin_chi`,`loai`,`hoc_ky`,`nien_khoa`,`thu`,`tiet_bat_dau`,`so_tiet`,`phong_hoc`,`giang_vien`,`si_so_toi_da`,`si_so_hien_tai`,`ma_hp_tien_quyet`) VALUES
('CNTT001','Lập trình căn bản',4,'Bắt buộc',1,'2021-2025',2,1,4,'A101','TS. Nguyễn Văn A',80,0,NULL),
('CNTT002','Cấu trúc dữ liệu và giải thuật',3,'Bắt buộc',2,'2021-2025',3,1,3,'B201','ThS. Lê Văn B',60,0,'CNTT001'),
('CNTT003','Lập trình hướng đối tượng',3,'Bắt buộc',3,'2021-2025',4,1,3,'A102','TS. Phạm Văn C',70,0,'CNTT001'),
('CNTT004','Cơ sở dữ liệu',3,'Bắt buộc',3,'2021-2025',5,1,3,'B202','TS. Trần Văn D',75,0,'CNTT001'),
('CNTT005','Mạng máy tính',3,'Bắt buộc',4,'2021-2025',2,1,3,'A201','TS. Nguyễn Văn Hùng',50,0,NULL),
('CNTT006','Lập trình Web',3,'Bắt buộc',4,'2021-2025',3,4,3,'B305','ThS. Trần Thị Lan',50,0,'CNTT001'),
('CNTT007','Hệ điều hành',3,'Bắt buộc',4,'2021-2025',4,1,3,'A101','TS. Lê Văn Minh',50,0,NULL),
('CNTT008','Kiến trúc máy tính',3,'Đại cương',2,'2021-2025',5,7,3,'B101','ThS. Hoàng Văn E',60,0,NULL),
('CNTT009','Trí tuệ nhân tạo',3,'Tự chọn',5,'2021-2025',5,4,3,'Lab IT', 'ThS. Phạm Thị Hoa',50,0,'CNTT002'),
('CNTT010','An toàn thông tin',3,'Tự chọn',5,'2021-2025',6,1,3,'B202','TS. Hoàng Quang Trung',1,0,'CNTT005'),
('CNTT011','Phát triển ứng dụng Mobile',3,'Tự chọn',6,'2021-2025',3,7,3,'Lab IT','ThS. Nguyễn Thị F',40,0,'CNTT006'),
('CNTT012','Thực tập tốt nghiệp',5,'Bắt buộc',7,'2021-2025',7,1,5,'Ngoài trường','TS. Trần Văn G',100,0,NULL),
('CNTT013','Đồ án tốt nghiệp',7,'Bắt buộc',8,'2021-2025',7,6,5,'Ngoài trường','TS. Lê Văn H',100,0,NULL),
('GD001','Triết học Mác-Lênin',3,'Đại cương',1,'2021-2025',2,7,3,'A301','TS. Lý Văn I',120,0,NULL),
('GD002','Toán cao cấp A1',4,'Đại cương',1,'2021-2025',3,7,4,'B302','ThS. Nguyễn Văn J',100,0,NULL),
('GD003','Tiếng Anh 1',3,'Đại cương',1,'2021-2025',4,7,3,'B303','Cô Đỗ Thị K',40,0,NULL),
('GD004','Tiếng Anh 2',3,'Đại cương',2,'2021-2025',5,7,3,'B304','Cô Đỗ Thị K',45,0,'GD003'),
('GD005','Giáo dục thể chất 1',1,'Đại cương',1,'2021-2025',6,7,2,'Sân thể chất','Thầy Vũ Văn L',50,0,NULL);

-- Điểm học tập sinh viên 1
INSERT INTO `diem_hoc_tap` (`sinh_vien_id`,`hoc_phan_id`,`hoc_ky`,`nam_hoc`,`diem_cc`,`diem_gk`,`diem_ck`,`diem_tong`,`diem_chu`,`diem_he4`) VALUES
(1,1,1,'2021-2022',9,7.5,8,8.1,'A',3.7),
(1,14,1,'2021-2022',8,6.5,7,7.15,'B+',3.2),
(1,15,1,'2021-2022',10,8,8.5,8.65,'A',3.7),
(1,16,1,'2021-2022',8,7,7.5,7.55,'B+',3.2),
(1,18,1,'2021-2022',9,NULL,NULL,NULL,NULL,NULL),
(1,2,2,'2021-2022',8,8,7.5,7.7,'B+',3.2),
(1,8,2,'2021-2022',9,8.5,9,8.85,'A',3.7),
(1,17,2,'2021-2022',7,6,6.5,6.45,'C+',2.2),
(1,3,3,'2022-2023',8,7,8,7.8,'B+',3.2),
(1,4,3,'2022-2023',9,8,8.5,8.45,'A',3.7);

-- Điểm rèn luyện sinh viên 1
INSERT INTO `diem_ren_luyen` (`sinh_vien_id`,`hoc_ky`,`nam_hoc`,`diem`,`xep_loai`) VALUES
(1,1,'2021-2022',85,'Tốt'),
(1,2,'2021-2022',80,'Tốt'),
(1,3,'2022-2023',78,'Khá'),
(1,4,'2022-2023',82,'Tốt');

-- Học phí sinh viên 1
INSERT INTO `hoc_phi` (`sinh_vien_id`,`hoc_ky`,`nam_hoc`,`so_tien`,`da_nop`,`han_nop`,`trang_thai`) VALUES
(1,1,'2021-2022',8500000,8500000,'2021-10-15','Đã nộp'),
(1,2,'2021-2022',8500000,8500000,'2022-03-15','Đã nộp'),
(1,3,'2022-2023',9000000,9000000,'2022-10-15','Đã nộp'),
(1,4,'2022-2023',9000000,4500000,'2023-03-15','Nợ'),
(1,5,'2023-2024',9500000,0,'2023-10-15','Chưa nộp');

-- Thời khóa biểu sinh viên 1 (kỳ 5)
INSERT INTO `thoi_khoa_bieu` (`sinh_vien_id`,`hoc_phan_id`,`thu`,`tiet_bat_dau`,`so_tiet`,`phong_hoc`,`giang_vien`,`hoc_ky`,`nam_hoc`) VALUES
(1,5,2,1,3,'A201','TS. Nguyễn Văn Hùng',5,'2023-2024'),
(1,6,3,4,3,'B305','ThS. Trần Thị Lan',5,'2023-2024'),
(1,7,4,1,3,'A101','TS. Lê Văn Minh',5,'2023-2024'),
(1,9,5,4,3,'Lab IT','ThS. Phạm Thị Hoa',5,'2023-2024'),
(1,5,6,1,3,'A201','TS. Nguyễn Văn Hùng',5,'2023-2024');

-- Đăng ký học phần sinh viên 1
INSERT INTO `dang_ky_hp` (`sinh_vien_id`,`hoc_phan_id`,`hoc_ky`,`nam_hoc`,`trang_thai`) VALUES
(1,5,'5','2023-2024','Đã duyệt'),
(1,6,'5','2023-2024','Đã duyệt'),
(1,7,'5','2023-2024','Đã duyệt'),
(1,9,'5','2023-2024','Đã duyệt');

-- Chương trình đào tạo ngành CNTT
INSERT INTO `ctdt_chi_tiet` (`nganh`,`hoc_phan_id`,`hoc_ky`) VALUES
('Công nghệ thông tin',14,1),('Công nghệ thông tin',15,1),('Công nghệ thông tin',16,1),('Công nghệ thông tin',18,1),('Công nghệ thông tin',1,1),
('Công nghệ thông tin',2,2),('Công nghệ thông tin',8,2),('Công nghệ thông tin',17,2),
('Công nghệ thông tin',3,3),('Công nghệ thông tin',4,3),
('Công nghệ thông tin',5,4),('Công nghệ thông tin',6,4),('Công nghệ thông tin',7,4),
('Công nghệ thông tin',9,5),('Công nghệ thông tin',10,5),
('Công nghệ thông tin',11,6),
('Công nghệ thông tin',12,7),
('Công nghệ thông tin',13,8);
