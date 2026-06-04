<?php
/**
 * Migration: Chuyển đổi dữ liệu và chuẩn hóa cấu trúc Khoa - Ngành - Lớp sinh hoạt (Không dùng Transaction để tránh lỗi implicit commit trên DDL)
 */

require_once __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    echo "Bắt đầu quá trình Migration...\n";

    // 1. Tạo các bảng mới
    echo "1. Tạo các bảng danh mục mới...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `khoa` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ten_khoa` VARCHAR(100) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `nganh` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ten_nganh` VARCHAR(100) NOT NULL,
            `khoa_id` INT NOT NULL,
            UNIQUE KEY `unique_nganh_khoa` (`ten_nganh`, `khoa_id`),
            FOREIGN KEY (`khoa_id`) REFERENCES `khoa`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `lop_sinh_hoat` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ten_lop` VARCHAR(50) NOT NULL UNIQUE,
            `nganh_id` INT NOT NULL,
            FOREIGN KEY (`nganh_id`) REFERENCES `nganh`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Di chuyển dữ liệu Khoa
    echo "2. Trích xuất và chuyển đổi dữ liệu Khoa...\n";
    $faculties = $pdo->query("SELECT DISTINCT khoa FROM sinh_vien WHERE khoa IS NOT NULL AND khoa != ''")->fetchAll(PDO::FETCH_COLUMN);
    $stmtInsertFaculty = $pdo->prepare("INSERT IGNORE INTO khoa (ten_khoa) VALUES (:ten)");
    foreach ($faculties as $f) {
        $stmtInsertFaculty->execute(['ten' => trim($f)]);
        echo " - Đã thêm khoa: " . $f . "\n";
    }

    // 3. Di chuyển dữ liệu Ngành
    echo "3. Trích xuất và chuyển đổi dữ liệu Ngành...\n";
    $majors = $pdo->query("SELECT DISTINCT khoa, nganh FROM sinh_vien WHERE nganh IS NOT NULL AND nganh != ''")->fetchAll();
    $stmtInsertMajor = $pdo->prepare("INSERT IGNORE INTO nganh (ten_nganh, khoa_id) VALUES (:ten, :khoa_id)");
    
    // Tạo map khoa -> id
    $facMap = [];
    $facRows = $pdo->query("SELECT id, ten_khoa FROM khoa")->fetchAll();
    foreach ($facRows as $row) {
        $facMap[$row['ten_khoa']] = $row['id'];
    }

    foreach ($majors as $m) {
        $ten_khoa = trim($m['khoa']);
        $ten_nganh = trim($m['nganh']);
        $khoa_id = $facMap[$ten_khoa] ?? null;
        if ($khoa_id) {
            $stmtInsertMajor->execute(['ten' => $ten_nganh, 'khoa_id' => $khoa_id]);
            echo " - Đã thêm ngành: " . $ten_nganh . " (Khoa ID: $khoa_id)\n";
        } else {
            echo " - Cảnh báo: Không tìm thấy khoa '$ten_khoa' cho ngành '$ten_nganh'\n";
        }
    }

    // 4. Di chuyển dữ liệu Lớp sinh hoạt
    echo "4. Trích xuất và chuyển đổi dữ liệu Lớp sinh hoạt...\n";
    $classes = $pdo->query("SELECT DISTINCT khoa, nganh, lop FROM sinh_vien WHERE lop IS NOT NULL AND lop != ''")->fetchAll();
    $stmtInsertClass = $pdo->prepare("INSERT IGNORE INTO lop_sinh_hoat (ten_lop, nganh_id) VALUES (:ten, :nganh_id)");

    // Tạo map nganh_ten + khoa_id -> id
    $majMap = [];
    $majRows = $pdo->query("SELECT id, ten_nganh, khoa_id FROM nganh")->fetchAll();
    foreach ($majRows as $row) {
        $majMap[$row['ten_nganh'] . '_' . $row['khoa_id']] = $row['id'];
    }

    foreach ($classes as $c) {
        $ten_khoa = trim($c['khoa']);
        $ten_nganh = trim($c['nganh']);
        $ten_lop = trim($c['lop']);
        
        $khoa_id = $facMap[$ten_khoa] ?? null;
        $nganh_id = $khoa_id ? ($majMap[$ten_nganh . '_' . $khoa_id] ?? null) : null;

        if ($nganh_id) {
            $stmtInsertClass->execute(['ten' => $ten_lop, 'nganh_id' => $nganh_id]);
            echo " - Đã thêm lớp sinh hoạt: " . $ten_lop . " (Ngành ID: $nganh_id)\n";
        } else {
            echo " - Cảnh báo: Không tìm thấy ngành '$ten_nganh' cho lớp '$ten_lop'\n";
        }
    }

    // Tạo map lop_ten -> id
    $classMap = [];
    $classRows = $pdo->query("SELECT id, ten_lop FROM lop_sinh_hoat")->fetchAll();
    foreach ($classRows as $row) {
        $classMap[$row['ten_lop']] = $row['id'];
    }

    // 5. Thêm cột lop_sinh_hoat_id vào bảng sinh_vien
    echo "5. Cấu trúc lại bảng sinh_vien và gán lớp mới...\n";
    // Kiểm tra xem cột đã tồn tại chưa
    $cols = $pdo->query("SHOW COLUMNS FROM sinh_vien LIKE 'lop_sinh_hoat_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE sinh_vien ADD COLUMN lop_sinh_hoat_id INT DEFAULT NULL AFTER user_id");
    }

    // Cập nhật lớp cho sinh viên
    $students = $pdo->query("SELECT id, lop FROM sinh_vien WHERE lop IS NOT NULL AND lop != ''")->fetchAll();
    $stmtUpdateStudentClass = $pdo->prepare("UPDATE sinh_vien SET lop_sinh_hoat_id = :class_id WHERE id = :sid");
    foreach ($students as $sv) {
        $ten_lop = trim($sv['lop']);
        $class_id = $classMap[$ten_lop] ?? null;
        if ($class_id) {
            $stmtUpdateStudentClass->execute(['class_id' => $class_id, 'sid' => $sv['id']]);
        }
    }
    echo " - Đã cập nhật lớp sinh hoạt mới cho " . count($students) . " sinh viên.\n";

    // 6. Cấu trúc lại bảng ctdt_chi_tiet
    echo "6. Cấu trúc lại bảng ctdt_chi_tiet (Chương trình đào tạo)...\n";
    // Thêm cột nganh_id
    $colsCtdt = $pdo->query("SHOW COLUMNS FROM ctdt_chi_tiet LIKE 'nganh_id'")->fetchAll();
    if (empty($colsCtdt)) {
        $pdo->exec("ALTER TABLE ctdt_chi_tiet ADD COLUMN nganh_id INT DEFAULT NULL AFTER nganh");
    }

    // Tạo map nganh_ten -> id (nếu trùng tên lấy ID đầu tiên)
    $nganhNameMap = [];
    foreach ($majRows as $row) {
        if (!isset($nganhNameMap[$row['ten_nganh']])) {
            $nganhNameMap[$row['ten_nganh']] = $row['id'];
        }
    }

    // Cập nhật ngành cho CTĐT
    $ctdts = $pdo->query("SELECT id, nganh FROM ctdt_chi_tiet")->fetchAll();
    $stmtUpdateCtdt = $pdo->prepare("UPDATE ctdt_chi_tiet SET nganh_id = :nganh_id WHERE id = :id");
    foreach ($ctdts as $ct) {
        $ten_ng = trim($ct['nganh']);
        $ng_id = $nganhNameMap[$ten_ng] ?? null;
        if ($ng_id) {
            $stmtUpdateCtdt->execute(['nganh_id' => $ng_id, 'id' => $ct['id']]);
        }
    }
    echo " - Đã cập nhật ngành mới cho " . count($ctdts) . " chương trình đào tạo chi tiết.\n";

    // 7. Xóa các cột cũ và thiết lập khóa ngoại
    echo "7. Xóa bỏ các cột văn bản cũ và thêm ràng buộc khóa ngoại...\n";
    
    // Kiểm tra xem đã thêm khóa ngoại sinh_vien -> lop_sinh_hoat chưa
    $foreignKeysSv = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND TABLE_NAME = 'sinh_vien' 
          AND CONSTRAINT_NAME = 'fk_sv_lop_sh'
    ")->fetchAll();

    // Bảng sinh_vien
    $colsOldSv = $pdo->query("SHOW COLUMNS FROM sinh_vien")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('lop', $colsOldSv)) {
        $pdo->exec("ALTER TABLE sinh_vien DROP COLUMN lop");
    }
    if (in_array('nganh', $colsOldSv)) {
        $pdo->exec("ALTER TABLE sinh_vien DROP COLUMN nganh");
    }
    if (in_array('khoa', $colsOldSv)) {
        $pdo->exec("ALTER TABLE sinh_vien DROP COLUMN khoa");
    }

    // Bảng ctdt_chi_tiet
    $colsOldCtdt = $pdo->query("SHOW COLUMNS FROM ctdt_chi_tiet")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('nganh', $colsOldCtdt)) {
        $pdo->exec("ALTER TABLE ctdt_chi_tiet DROP COLUMN nganh");
    }

    // Thêm ràng buộc khóa ngoại nếu chưa có
    if (empty($foreignKeysSv)) {
        $pdo->exec("ALTER TABLE sinh_vien ADD CONSTRAINT fk_sv_lop_sh FOREIGN KEY (lop_sinh_hoat_id) REFERENCES lop_sinh_hoat(id) ON DELETE SET NULL");
    }

    $foreignKeysCtdt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
          AND TABLE_NAME = 'ctdt_chi_tiet' 
          AND CONSTRAINT_NAME = 'fk_ctdt_nganh'
    ")->fetchAll();
    
    if (empty($foreignKeysCtdt)) {
        $pdo->exec("ALTER TABLE ctdt_chi_tiet ADD CONSTRAINT fk_ctdt_nganh FOREIGN KEY (nganh_id) REFERENCES nganh(id) ON DELETE CASCADE");
    }

    echo "<strong>Migration hoàn tất thành công!</strong>\n";

} catch (Exception $e) {
    echo "Lỗi Migration: " . $e->getMessage() . "\n";
}
