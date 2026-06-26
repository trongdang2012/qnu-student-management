<?php
/**
 * Cập nhật dữ liệu mẫu cho bảng giảng viên
 * - ALTER TABLE thêm cột hoc_vi, chuyen_nganh nếu chưa có
 * - Gán đúng khoa_id, học vị, chuyên ngành cho tất cả giảng viên
 */

require_once __DIR__ . '/../config/database.php';

set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');

echo "<pre>";
echo "=========================================================\n";
echo "CẬP NHẬT DỮ LIỆU MẪU GIẢNG VIÊN\n";
echo "=========================================================\n\n";

$conn = getDB();
if (!$conn) {
    die("LỖI: Không thể kết nối cơ sở dữ liệu.\n");
}

// 0. Kiểm tra và thêm cột hoc_vi, chuyen_nganh nếu chưa có
echo "0. Kiểm tra cấu trúc bảng giang_vien...\n";

$cols = [];
$result = $conn->query("DESCRIBE giang_vien");
while ($row = $result->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo " - Các cột hiện có: " . implode(', ', $cols) . "\n";

if (!in_array('hoc_vi', $cols)) {
    $conn->query("ALTER TABLE giang_vien ADD COLUMN hoc_vi VARCHAR(30) DEFAULT NULL AFTER ho_ten");
    echo " - ✓ Đã thêm cột: hoc_vi\n";
} else {
    echo " - Cột hoc_vi đã tồn tại.\n";
}

if (!in_array('chuyen_nganh', $cols)) {
    $conn->query("ALTER TABLE giang_vien ADD COLUMN chuyen_nganh VARCHAR(100) DEFAULT NULL AFTER hoc_vi");
    echo " - ✓ Đã thêm cột: chuyen_nganh\n";
} else {
    echo " - Cột chuyen_nganh đã tồn tại.\n";
}

// 1. Lấy danh sách khoa hiện có
echo "\n1. Đọc danh sách Khoa hiện có...\n";
$result = $conn->query("SELECT id, ten_khoa FROM khoa ORDER BY id");
$khoaMap = [];
while ($row = $result->fetch_assoc()) {
    $khoaMap[$row['ten_khoa']] = $row['id'];
    echo " - [{$row['id']}] {$row['ten_khoa']}\n";
}

if (empty($khoaMap)) {
    die("\nLỖI: Không tìm thấy Khoa nào trong CSDL. Hãy chạy seeder.php trước.\n");
}

// 2. Xóa dữ liệu giảng viên cũ
echo "\n2. Xóa dữ liệu giảng viên cũ...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Cập nhật bảng lop_hoc_phan để xóa tham chiếu giảng viên nếu có cột giang_vien_id
$colCheck = $conn->query("SHOW COLUMNS FROM lop_hoc_phan LIKE 'giang_vien_id'");
if ($colCheck && $colCheck->num_rows > 0) {
    $conn->query("UPDATE lop_hoc_phan SET giang_vien_id = NULL");
}

$conn->query("DELETE FROM giang_vien");
$conn->query("ALTER TABLE giang_vien AUTO_INCREMENT = 1");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo " - Đã xóa dữ liệu giảng viên cũ.\n";

// 3. Dữ liệu giảng viên mẫu - phân bổ theo từng khoa
echo "\n3. Tạo dữ liệu giảng viên mẫu...\n";

$giangVienData = [
    'Khoa Công nghệ thông tin' => [
        ['ma_gv' => 'GV1001', 'ho_ten' => 'Nguyễn Thanh Tùng',     'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Khoa học máy tính',           'email' => 'tungnt@qnu.edu.vn',     'sdt' => '0901234001'],
        ['ma_gv' => 'GV1002', 'ho_ten' => 'Trần Văn Hùng',         'hoc_vi' => 'TS',     'chuyen_nganh' => 'Kỹ thuật phần mềm',           'email' => 'hungtv@qnu.edu.vn',     'sdt' => '0901234002'],
        ['ma_gv' => 'GV1003', 'ho_ten' => 'Lê Thị Hồng Nhung',     'hoc_vi' => 'TS',     'chuyen_nganh' => 'Trí tuệ nhân tạo',            'email' => 'nhunglth@qnu.edu.vn',   'sdt' => '0901234003'],
        ['ma_gv' => 'GV1004', 'ho_ten' => 'Phạm Minh Đức',         'hoc_vi' => 'ThS',    'chuyen_nganh' => 'Mạng máy tính',                'email' => 'ducpm@qnu.edu.vn',      'sdt' => '0901234004'],
        ['ma_gv' => 'GV1005', 'ho_ten' => 'Vũ Thị Quỳnh',          'hoc_vi' => 'TS',     'chuyen_nganh' => 'Hệ thống thông tin',           'email' => 'quynhvt@qnu.edu.vn',    'sdt' => '0901234005'],
    ],
    'Khoa Sư phạm' => [
        ['ma_gv' => 'GV2001', 'ho_ten' => 'Đặng Văn Lâm',          'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Toán giải tích',               'email' => 'lamdv@qnu.edu.vn',      'sdt' => '0901234006'],
        ['ma_gv' => 'GV2002', 'ho_ten' => 'Hoàng Thị Mai',          'hoc_vi' => 'TS',     'chuyen_nganh' => 'Sư phạm Ngữ văn',             'email' => 'maiht@qnu.edu.vn',      'sdt' => '0901234007'],
        ['ma_gv' => 'GV2003', 'ho_ten' => 'Bùi Quốc Bảo',          'hoc_vi' => 'TS',     'chuyen_nganh' => 'Lý luận và PP dạy Toán',      'email' => 'baobq@qnu.edu.vn',      'sdt' => '0901234008'],
    ],
    'Khoa Khoa học tự nhiên' => [
        ['ma_gv' => 'GV3001', 'ho_ten' => 'Ngô Xuân Phong',        'hoc_vi' => 'GS.TS',  'chuyen_nganh' => 'Hóa hữu cơ',                  'email' => 'phongnx@qnu.edu.vn',    'sdt' => '0901234009'],
        ['ma_gv' => 'GV3002', 'ho_ten' => 'Trần Thị Bích Ngọc',    'hoc_vi' => 'TS',     'chuyen_nganh' => 'Sinh học phân tử',             'email' => 'ngocttb@qnu.edu.vn',    'sdt' => '0901234010'],
    ],
    'Khoa Khoa học xã hội & nhân văn' => [
        ['ma_gv' => 'GV4001', 'ho_ten' => 'Phan Văn Thành',        'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Văn học Việt Nam',              'email' => 'thanhpv@qnu.edu.vn',    'sdt' => '0901234011'],
        ['ma_gv' => 'GV4002', 'ho_ten' => 'Lý Thị Thu Hà',         'hoc_vi' => 'TS',     'chuyen_nganh' => 'Tâm lý học giáo dục',         'email' => 'haltt@qnu.edu.vn',      'sdt' => '0901234012'],
    ],
    'Khoa Ngoại ngữ' => [
        ['ma_gv' => 'GV5001', 'ho_ten' => 'Nguyễn Thị Lan Anh',    'hoc_vi' => 'TS',     'chuyen_nganh' => 'Ngôn ngữ Anh',                'email' => 'anhltn@qnu.edu.vn',     'sdt' => '0901234013'],
        ['ma_gv' => 'GV5002', 'ho_ten' => 'Trương Minh Quân',       'hoc_vi' => 'ThS',    'chuyen_nganh' => 'Ngôn ngữ Trung Quốc',         'email' => 'quantm@qnu.edu.vn',     'sdt' => '0901234014'],
    ],
    'Khoa Kỹ thuật & Công nghệ' => [
        ['ma_gv' => 'GV6001', 'ho_ten' => 'Đỗ Hoàng Nam',          'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Kỹ thuật điện',                'email' => 'namdh@qnu.edu.vn',      'sdt' => '0901234015'],
        ['ma_gv' => 'GV6002', 'ho_ten' => 'Võ Thị Thanh Trúc',     'hoc_vi' => 'TS',     'chuyen_nganh' => 'Công nghệ ô tô',              'email' => 'trucvtt@qnu.edu.vn',    'sdt' => '0901234016'],
    ],
    'Khoa Toán & Thống kê' => [
        ['ma_gv' => 'GV7001', 'ho_ten' => 'Lê Quang Vinh',         'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Toán ứng dụng',                'email' => 'vinhlq@qnu.edu.vn',     'sdt' => '0901234017'],
        ['ma_gv' => 'GV7002', 'ho_ten' => 'Nguyễn Thị Phương',     'hoc_vi' => 'TS',     'chuyen_nganh' => 'Khoa học dữ liệu',            'email' => 'phuongnt@qnu.edu.vn',   'sdt' => '0901234018'],
    ],
    'Khoa Kinh tế & Kế toán' => [
        ['ma_gv' => 'GV8001', 'ho_ten' => 'Huỳnh Văn Đạt',         'hoc_vi' => 'TS',     'chuyen_nganh' => 'Kinh tế học',                  'email' => 'dathv@qnu.edu.vn',      'sdt' => '0901234019'],
        ['ma_gv' => 'GV8002', 'ho_ten' => 'Trần Thị Kim Oanh',     'hoc_vi' => 'ThS',    'chuyen_nganh' => 'Kế toán - Kiểm toán',         'email' => 'oanhttk@qnu.edu.vn',    'sdt' => '0901234020'],
    ],
    'Khoa Tài chính - Ngân hàng & Quản trị kinh doanh' => [
        ['ma_gv' => 'GV9001', 'ho_ten' => 'Mai Thanh Phong',        'hoc_vi' => 'PGS.TS', 'chuyen_nganh' => 'Quản trị kinh doanh',          'email' => 'phongmt@qnu.edu.vn',    'sdt' => '0901234021'],
    ],
    'Khoa Giáo dục tiểu học & mầm non' => [
        ['ma_gv' => 'GV1101', 'ho_ten' => 'Đinh Thị Hạnh',         'hoc_vi' => 'TS',     'chuyen_nganh' => 'Giáo dục mầm non',             'email' => 'hanhdt@qnu.edu.vn',     'sdt' => '0901234022'],
    ],
    'Khoa Giáo dục Thể chất' => [
        ['ma_gv' => 'GV1201', 'ho_ten' => 'Lê Văn Cường',          'hoc_vi' => 'ThS',    'chuyen_nganh' => 'Giáo dục thể chất',            'email' => 'cuonglv@qnu.edu.vn',    'sdt' => '0901234023'],
    ],
    'Khoa Lý luận chính trị - Luật & Quản lý nhà nước' => [
        ['ma_gv' => 'GV1301', 'ho_ten' => 'Trần Đình Khoa',        'hoc_vi' => 'TS',     'chuyen_nganh' => 'Luật hành chính',              'email' => 'khoatd@qnu.edu.vn',     'sdt' => '0901234024'],
    ],
];

$stmt = $conn->prepare("INSERT INTO giang_vien (ma_gv, ho_ten, khoa_id, hoc_vi, chuyen_nganh, email, so_dien_thoai) VALUES (?, ?, ?, ?, ?, ?, ?)");

$totalInserted = 0;
$totalSkipped = 0;

foreach ($giangVienData as $tenKhoa => $dsGV) {
    $khoaId = $khoaMap[$tenKhoa] ?? null;
    
    if (!$khoaId) {
        echo " [!] Không tìm thấy khoa: $tenKhoa - Bỏ qua " . count($dsGV) . " giảng viên.\n";
        $totalSkipped += count($dsGV);
        continue;
    }

    echo "\n >> $tenKhoa (ID: $khoaId)\n";

    foreach ($dsGV as $gv) {
        $stmt->bind_param(
            "ssissss",
            $gv['ma_gv'],
            $gv['ho_ten'],
            $khoaId,
            $gv['hoc_vi'],
            $gv['chuyen_nganh'],
            $gv['email'],
            $gv['sdt']
        );
        
        if ($stmt->execute()) {
            $totalInserted++;
            echo "   ✓ {$gv['ma_gv']} - {$gv['hoc_vi']} {$gv['ho_ten']} ({$gv['chuyen_nganh']})\n";
        } else {
            echo "   ✗ Lỗi thêm {$gv['ma_gv']}: " . $conn->error . "\n";
        }
    }
}

$stmt->close();

echo "\n=========================================================\n";
echo "KẾT QUẢ:\n";
echo " - Tổng giảng viên đã thêm: $totalInserted\n";
echo " - Tổng bỏ qua (khoa không tồn tại): $totalSkipped\n";
echo "=========================================================\n";

// 4. Kiểm tra kết quả
echo "\n4. Kiểm tra kết quả...\n\n";
$result = $conn->query("
    SELECT gv.ma_gv, gv.ho_ten, gv.hoc_vi, gv.chuyen_nganh, k.ten_khoa, gv.email
    FROM giang_vien gv
    LEFT JOIN khoa k ON gv.khoa_id = k.id
    ORDER BY gv.id
");

echo str_pad("Ma GV", 10) . str_pad("Ho va ten", 28) . str_pad("Hoc vi", 10) . str_pad("Chuyen nganh", 30) . str_pad("Khoa", 40) . "Email\n";
echo str_repeat("-", 148) . "\n";

while ($row = $result->fetch_assoc()) {
    echo str_pad($row['ma_gv'], 10)
       . str_pad($row['ho_ten'], 28)
       . str_pad($row['hoc_vi'] ?? '', 10)
       . str_pad($row['chuyen_nganh'] ?? '', 30)
       . str_pad($row['ten_khoa'] ?? 'N/A', 40)
       . ($row['email'] ?? '') . "\n";
}

echo "\n>> HOÀN TẤT!\n";
echo "</pre>";
