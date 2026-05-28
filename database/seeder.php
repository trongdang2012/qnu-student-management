<?php
/**
 * Database Seeder Script - QNU Student Management System (Tín chỉ mới)
 * Tự động sinh dữ liệu mẫu chất lượng cao, phân tách Học phần và Lớp học phần.
 * Cập nhật thời gian học vụ hiện tại: Học kỳ 2, năm học 2025-2026.
 * Mật khẩu sinh viên mới: Student@123
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

echo "=========================================================\n";
echo "BẮT ĐẦU CHẠY DATABASE SEEDER PHÂN TÁCH HỌC PHẦN - LỚP HỌC PHẦN (2025-2026)\n";
echo "=========================================================\n\n";

$conn = getDB();
if (!$conn) {
    die("LỖI: Không thể kết nối cơ sở dữ liệu.\n");
}

$defaultPassword = 'Student@123';
$hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

// Định nghĩa 5 Khoa, 5 Ngành
$faculties = [
    'Công nghệ thông tin' => [
        'khoa' => 'Kỹ thuật - Công nghệ',
        'nganh' => 'Công nghệ thông tin',
        'prefix_hp' => 'CNTT'
    ],
    'Kinh tế - Quản trị kinh doanh' => [
        'khoa' => 'Kinh tế - Luật',
        'nganh' => 'Quản trị kinh doanh',
        'prefix_hp' => 'QTKD'
    ],
    'Ngoại ngữ' => [
        'khoa' => 'Ngoại ngữ',
        'nganh' => 'Ngôn ngữ Anh',
        'prefix_hp' => 'NNA'
    ],
    'Sư phạm' => [
        'khoa' => 'Khoa học Tự nhiên',
        'nganh' => 'Sư phạm Toán',
        'prefix_hp' => 'SPT'
    ],
    'Kỹ thuật và Công nghệ' => [
        'khoa' => 'Kỹ thuật - Công nghệ',
        'nganh' => 'Kỹ thuật điện',
        'prefix_hp' => 'KTD'
    ]
];

// Môn học mẫu cụ thể cho từng ngành
$subjectsTemplate = [
    'CNTT' => [
        ['Lập trình căn bản', 4, 'Bắt buộc', 45, 30],
        ['Cấu trúc dữ liệu và giải thuật', 3, 'Bắt buộc', 30, 30],
        ['Lập trình hướng đối tượng', 3, 'Bắt buộc', 30, 30],
        ['Cơ sở dữ liệu', 3, 'Bắt buộc', 30, 30],
        ['Mạng máy tính', 3, 'Bắt buộc', 30, 30],
        ['Lập trình Web', 3, 'Bắt buộc', 30, 30],
        ['Hệ điều hành', 3, 'Bắt buộc', 30, 30],
        ['Kiến trúc máy tính', 3, 'Đại cương', 45, 0],
        ['Trí tuệ nhân tạo', 3, 'Tự chọn', 30, 30],
        ['An toàn thông tin', 3, 'Tự chọn', 30, 30],
        ['Phát triển ứng dụng Mobile', 3, 'Tự chọn', 15, 60],
        ['Phân tích thiết kế hệ thống', 3, 'Bắt buộc', 30, 30],
        ['Quản trị dự án phần mềm', 3, 'Tự chọn', 45, 0],
        ['Điện toán đám mây', 3, 'Tự chọn', 30, 30],
        ['Đồ án ngành Công nghệ thông tin', 2, 'Bắt buộc', 0, 60],
        ['Thực tập tốt nghiệp', 5, 'Bắt buộc', 0, 150],
        ['Đồ án tốt nghiệp', 7, 'Bắt buộc', 0, 210]
    ],
    'QTKD' => [
        ['Quản trị học', 3, 'Bắt buộc', 45, 0],
        ['Kinh tế vĩ mô', 3, 'Đại cương', 45, 0],
        ['Kinh tế vi mô', 3, 'Đại cương', 45, 0],
        ['Nguyên lý kế toán', 3, 'Bắt buộc', 30, 30],
        ['Marketing căn bản', 3, 'Bắt buộc', 45, 0],
        ['Quản trị tài chính', 3, 'Bắt buộc', 45, 0],
        ['Quản trị nhân lực', 3, 'Bắt buộc', 45, 0],
        ['Quản trị chiến lược', 3, 'Bắt buộc', 45, 0],
        ['Hành vi tổ chức', 3, 'Tự chọn', 45, 0],
        ['Thương mại quốc tế', 3, 'Tự chọn', 45, 0],
        ['Quản trị chất lượng', 3, 'Tự chọn', 45, 0],
        ['Khởi nghiệp kinh doanh', 3, 'Tự chọn', 30, 30],
        ['Logistics và chuỗi cung ứng', 3, 'Tự chọn', 45, 0],
        ['Nghiên cứu marketing', 3, 'Bắt buộc', 30, 30],
        ['Đồ án ngành Quản trị kinh doanh', 2, 'Bắt buộc', 0, 60],
        ['Thực tập tốt nghiệp QTKD', 5, 'Bắt buộc', 0, 150],
        ['Khóa luận tốt nghiệp QTKD', 7, 'Bắt buộc', 0, 210]
    ],
    'NNA' => [
        ['Tiếng Anh giao tiếp', 3, 'Đại cương', 45, 0],
        ['Ngữ pháp tiếng Anh', 3, 'Bắt buộc', 45, 0],
        ['Đọc hiểu tiếng Anh 1', 3, 'Bắt buộc', 45, 0],
        ['Viết tiếng Anh 1', 3, 'Bắt buộc', 30, 30],
        ['Nghe nói tiếng Anh 1', 3, 'Bắt buộc', 15, 60],
        ['Biên dịch tiếng Anh', 3, 'Bắt buộc', 30, 30],
        ['Phiên dịch tiếng Anh', 3, 'Bắt buộc', 15, 60],
        ['Văn hóa Anh Mỹ', 3, 'Tự chọn', 45, 0],
        ['Văn học Anh Mỹ', 3, 'Tự chọn', 45, 0],
        ['Tiếng Anh thương mại', 3, 'Tự chọn', 30, 30],
        ['Tiếng Anh du lịch', 3, 'Tự chọn', 30, 30],
        ['Phương pháp giảng dạy Tiếng Anh', 3, 'Tự chọn', 45, 0],
        ['Từ vựng học tiếng Anh', 2, 'Bắt buộc', 30, 0],
        ['Ngữ âm học tiếng Anh', 2, 'Bắt buộc', 30, 0],
        ['Đồ án ngành Ngôn ngữ Anh', 2, 'Bắt buộc', 0, 60],
        ['Thực tập tốt nghiệp NNA', 5, 'Bắt buộc', 0, 150],
        ['Khóa luận tốt nghiệp NNA', 7, 'Bắt buộc', 0, 210]
    ],
    'SPT' => [
        ['Giải tích 1', 4, 'Đại cương', 60, 0],
        ['Đại số tuyến tính', 4, 'Đại cương', 60, 0],
        ['Hình học cổ điển', 3, 'Bắt buộc', 45, 0],
        ['Lý thuyết số', 3, 'Bắt buộc', 45, 0],
        ['Phương pháp dạy học Toán', 3, 'Bắt buộc', 30, 30],
        ['Hình học giải tích', 3, 'Bắt buộc', 45, 0],
        ['Giải tích phức', 3, 'Tự chọn', 45, 0],
        ['Đại số đại cương', 3, 'Bắt buộc', 45, 0],
        ['Giải tích thực', 3, 'Tự chọn', 45, 0],
        ['Hình học vi phân', 3, 'Tự chọn', 45, 0],
        ['Phương trình vi phân', 3, 'Bắt buộc', 45, 0],
        ['Xác suất thống kê Toán', 3, 'Bắt buộc', 45, 0],
        ['Tâm lý học sư phạm', 2, 'Bắt buộc', 30, 0],
        ['Giáo dục học đại cương', 2, 'Bắt buộc', 30, 0],
        ['Thực tập sư phạm 1', 2, 'Bắt buộc', 0, 60],
        ['Thực tập sư phạm tốt nghiệp', 5, 'Bắt buộc', 0, 150],
        ['Khóa luận tốt nghiệp Sư phạm Toán', 7, 'Bắt buộc', 0, 210]
    ],
    'KTD' => [
        ['Mạch điện 1', 3, 'Bắt buộc', 30, 30],
        ['Kỹ thuật điện tử', 3, 'Bắt buộc', 30, 30],
        ['Máy điện', 3, 'Bắt buộc', 30, 30],
        ['Hệ thống cung cấp điện', 3, 'Bắt buộc', 45, 0],
        ['Đo lường điện', 3, 'Bắt buộc', 30, 30],
        ['Điều khiển tự động', 3, 'Bắt buộc', 30, 30],
        ['Điện tử công suất', 3, 'Bắt buộc', 30, 30],
        ['An toàn điện', 2, 'Đại cương', 30, 0],
        ['Kỹ thuật lập trình vi điều khiển', 3, 'Tự chọn', 15, 60],
        ['Năng lượng tái tạo', 3, 'Tự chọn', 45, 0],
        ['Truyền động điện', 3, 'Tự chọn', 30, 30],
        ['Thiết kế hệ thống điện', 3, 'Tự chọn', 15, 60],
        ['Mạng điện và trạm biến áp', 3, 'Bắt buộc', 45, 0],
        ['Đồ án chuyên ngành Kỹ thuật điện', 2, 'Bắt buộc', 0, 60],
        ['Thực tập tốt nghiệp Kỹ thuật điện', 5, 'Bắt buộc', 0, 150],
        ['Khóa luận tốt nghiệp Kỹ thuật điện', 7, 'Bắt buộc', 0, 210]
    ]
];

// -----------------------------------------------------------------------------
// BƯỚC 1: SINH HỌC PHẦN (HOC_PHAN) VÀ CTDT_CHI_TIET
// -----------------------------------------------------------------------------
echo "1. Đang kiểm tra bảng `hoc_phan`...\n";
$resHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phan");
$countHp = $resHp->fetch_assoc()['total'];
echo "Số học phần hiện có: $countHp\n";

$insertedHpCount = 0;
$totalHpNeeded = 105;

if ($countHp < $totalHpNeeded) {
    foreach ($subjectsTemplate as $prefix => $subjects) {
        $facultyData = null;
        foreach ($faculties as $key => $val) {
            if ($val['prefix_hp'] === $prefix) {
                $facultyData = $val;
                break;
            }
        }

        $idx = 1;
        foreach ($subjects as $sub) {
            $maHp = sprintf("%s%03d", $prefix, $idx++);
            $tenHp = $sub[0];
            $soTc = $sub[1];
            $loai = $sub[2];
            $lt = $sub[3];
            $th = $sub[4];
            $hocKy = rand(1, 8);
            $khoa = $facultyData['khoa'];
            $mota = "Học phần trang bị kiến thức nền tảng và chuyên sâu về môn học " . $tenHp . " dành cho sinh viên ngành " . $facultyData['nganh'] . ".";

            $stmt = $conn->prepare("INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, so_tiet_ly_thuyet, so_tiet_thuc_hanh, khoa_phu_trach, mo_ta, trang_thai_hoat_dong) VALUES (?, ?, ?, ?, ?, '2022-2026', ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssisiiiss", $maHp, $tenHp, $soTc, $loai, $hocKy, $lt, $th, $khoa, $mota);
            if ($stmt->execute()) {
                $hpId = $conn->insert_id;
                $insertedHpCount++;

                $stmtCtdt = $conn->prepare("INSERT INTO ctdt_chi_tiet (nganh, hoc_phan_id, hoc_ky) VALUES (?, ?, ?)");
                $stmtCtdt->bind_param("sii", $facultyData['nganh'], $hpId, $hocKy);
                $stmtCtdt->execute();
                $stmtCtdt->close();
            }
            $stmt->close();
        }
    }

    $subIndex = 100;
    while ($insertedHpCount < $totalHpNeeded) {
        $prefix = array_keys($subjectsTemplate)[rand(0, 4)];
        $maHp = $prefix . $subIndex++;
        
        $tenHp = "Môn học chuyên đề " . $maHp;
        $soTc = rand(2, 4);
        $loai = rand(0, 10) > 7 ? 'Tự chọn' : 'Bắt buộc';
        $lt = 30;
        $th = 15;
        $hocKy = rand(1, 8);
        
        $nganhTarget = '';
        $khoaTarget = '';
        foreach ($faculties as $key => $val) {
            if ($val['prefix_hp'] === $prefix) {
                $nganhTarget = $val['nganh'];
                $khoaTarget = $val['khoa'];
                break;
            }
        }
        $mota = "Chuyên đề nâng cao ngành " . $nganhTarget;

        $stmt = $conn->prepare("INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, so_tiet_ly_thuyet, so_tiet_thuc_hanh, khoa_phu_trach, mo_ta, trang_thai_hoat_dong) VALUES (?, ?, ?, ?, ?, '2022-2026', ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssisiiiss", $maHp, $tenHp, $soTc, $loai, $hocKy, $lt, $th, $khoaTarget, $mota);
        if ($stmt->execute()) {
            $hpId = $conn->insert_id;
            $insertedHpCount++;

            $stmtCtdt = $conn->prepare("INSERT INTO ctdt_chi_tiet (nganh, hoc_phan_id, hoc_ky) VALUES (?, ?, ?)");
            $stmtCtdt->bind_param("sii", $nganhTarget, $hpId, $hocKy);
            $stmtCtdt->execute();
            $stmtCtdt->close();
        }
        $stmt->close();
    }
}

$resHp = $conn->query("SELECT id, ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, khoa_phu_trach FROM hoc_phan");
$hocPhans = $resHp->fetch_all(MYSQLI_ASSOC);
echo "-> Hoàn thành bảng `hoc_phan`. Số lượng: " . count($hocPhans) . "\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 2: TẠO LỚP HỌC PHẦN (LOP_HOC_PHAN)
// -----------------------------------------------------------------------------
echo "2. Đang tạo các lớp học phần cho Học kỳ 2, năm học 2025-2026 và các kỳ trước...\n";

$giangViens = [
    'TS. Nguyễn Văn Hùng', 'ThS. Trần Thị Lan', 'TS. Lê Văn Minh', 'ThS. Hoàng Văn E', 
    'ThS. Phạm Thị Hoa', 'TS. Hoàng Quang Trung', 'ThS. Nguyễn Thị F', 'TS. Trần Văn G', 
    'TS. Lê Văn H', 'TS. Lý Văn I', 'ThS. Nguyễn Văn J', 'Cô Đỗ Thị K', 'Thầy Vũ Văn L',
    'TS. Phạm Minh Tuấn', 'TS. Đặng Thanh Sơn', 'ThS. Lê Hoàng Nam', 'ThS. Nguyễn Thị Mai',
    'TS. Bùi Quốc Bảo', 'ThS. Huỳnh Tấn Đạt', 'TS. Phan Anh Tuấn', 'TS. Vũ Thị Quỳnh'
];

$insertedLhpCount = 0;
// Lớp học phần được mở cho học kỳ hiện tại (Học kỳ 2, năm học 2025-2026)
$hkHienTai = HOC_KY_HIEN_TAI;
$nhHienTai = NAM_HOC_HIEN_TAI;

// Duyệt qua tất cả học phần và tạo lớp cho học kỳ hiện tại + các học kỳ trước
foreach ($hocPhans as $hp) {
    $hpId = $hp['id'];
    $maHp = $hp['ma_hp'];
    $hpHkCtdt = (int)$hp['hoc_ky'];

    // 1. Tạo lớp học phần ở Học kỳ 2, năm học 2025-2026
    $maLopHp1 = $maHp . '-L01';
    $gv1 = $giangViens[array_rand($giangViens)];
    $maxSiso = rand(60, 90);
    $ngayBd = '2026-01-15';
    $ngayKt = '2026-05-30';

    $stmtLhp = $conn->prepare("INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 'Đang mở')");
    $stmtLhp->bind_param("sisssiss", $maLopHp1, $hpId, $gv1, $hkHienTai, $nhHienTai, $maxSiso, $ngayBd, $ngayKt);
    if ($stmtLhp->execute()) {
        $insertedLhpCount++;
    }
    $stmtLhp->close();

    // 2. Tạo các lớp học phần cho các kỳ trước (để làm dữ liệu điểm và rèn luyện lịch sử)
    // Mỗi học phần gợi ý K của CTDT sẽ được mở lớp ở kỳ tương ứng
    $n = (int)ceil($hpHkCtdt / 2); // Năm thứ mấy
    $dbHk = ($hpHkCtdt % 2 == 0) ? 2 : 1; 
    // Giả sử mốc bắt đầu là 2022
    $dbYearStart = 2022 + $n - 1;
    $dbNamHoc = $dbYearStart . '-' . ($dbYearStart + 1);

    // Không mở lớp kỳ trước nếu trùng với kỳ hiện tại
    if (!($dbNamHoc === $nhHienTai && $dbHk == $hkHienTai)) {
        $maLopHp2 = $maHp . sprintf("-L%02d", $hpHkCtdt);
        $gv2 = $giangViens[array_rand($giangViens)];
        $ngayBd2 = ($dbYearStart + ($dbHk == 1 ? 0 : 1)) . ($dbHk == 1 ? '-09-05' : '-01-15');
        $ngayKt2 = ($dbYearStart + ($dbHk == 1 ? 0 : 1)) . ($dbHk == 1 ? '-01-15' : '-05-30');

        $stmtLhp2 = $conn->prepare("INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 'Đang mở')");
        $stmtLhp2->bind_param("sisssiss", $maLopHp2, $hpId, $gv2, $dbHk, $dbNamHoc, $maxSiso, $ngayBd2, $ngayKt2);
        if ($stmtLhp2->execute()) {
            $insertedLhpCount++;
        }
        $stmtLhp2->close();
    }
}

$resLhp = $conn->query("SELECT * FROM lop_hoc_phan");
$lopHocPhans = $resLhp->fetch_all(MYSQLI_ASSOC);
echo "-> Hoàn thành bảng `lop_hoc_phan`. Số lượng lớp: " . count($lopHocPhans) . "\n\n";

// Map lớp học phần để truy vấn nhanh
$lhpMap = [];
foreach ($lopHocPhans as $lhp) {
    $key = $lhp['hoc_phan_id'] . '_' . $lhp['hoc_ky'] . '_' . $lhp['nam_hoc'];
    $lhpMap[$key] = $lhp['id'];
}

// -----------------------------------------------------------------------------
// BƯỚC 3: TẠO THỜI KHÓA BIỂU CHO LỚP HỌC PHẦN (THOI_KHOA_BIEU)
// -----------------------------------------------------------------------------
echo "3. Đang tạo thời khóa biểu cho các lớp học phần...\n";
$phongs = ['A101', 'A102', 'A201', 'A301', 'B101', 'B201', 'B202', 'B302', 'B303', 'B304', 'B305', 'Lab IT', 'Lab Điện'];
$insertedTkbCount = 0;

foreach ($lopHocPhans as $lhp) {
    $lhpId = $lhp['id'];
    $thu = rand(2, 7);
    $tietBd = rand(1, 7);
    $soTiet = rand(2, 4);
    $phong = $phongs[array_rand($phongs)];
    $gv = $lhp['giang_vien'];
    $hk = $lhp['hoc_ky'];
    $nh = $lhp['nam_hoc'];
    $ngayBd = $lhp['ngay_bat_dau'];
    $ngayKt = $lhp['ngay_ket_thuc'];

    $stmtTkb = $conn->prepare("INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtTkb->bind_param("iiiiisssss", $lhpId, $thu, $tietBd, $soTiet, $phong, $gv, $hk, $nh, $ngayBd, $ngayKt);
    if ($stmtTkb->execute()) {
        $insertedTkbCount++;
    }
    $stmtTkb->close();
}

echo "-> Hoàn thành bảng `thoi_khoa_bieu` cho Lớp HP. Số bản ghi TKB: $insertedTkbCount\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 4: SINH USERS VÀ SINH_VIEN (PHÂN BỔ 4 KHÓA)
// -----------------------------------------------------------------------------
$hos = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
$demsNam = ['Văn', 'Hữu', 'Đức', 'Quốc', 'Minh', 'Thành', 'Hoàng', 'Tuấn', 'Thế', 'Xuân', 'Gia', 'Anh'];
$tensNam = ['Hùng', 'Hải', 'Nam', 'Sơn', 'Tùng', 'Đạt', 'Lộc', 'Duy', 'Phong', 'Bảo', 'Minh', 'Khang', 'Thịnh', 'Phúc', 'An'];
$demsNu = ['Thị', 'Quỳnh', 'Mỹ', 'Ngọc', 'Thu', 'Thanh', 'Kiều', 'Trúc', 'Phương', 'Như', 'Khánh', 'Minh'];
$tensNu = ['Hoa', 'Lan', 'Mai', 'Trang', 'Vy', 'Linh', 'Anh', 'Chi', 'Hà', 'Yến', 'Hương', 'Thảo', 'Trinh', 'Dung', 'Nhi'];
$diaChis = ['Quy Nhơn, Bình Định', 'An Nhơn, Bình Định', 'Tuy Phước, Bình Định', 'Phù Cát, Bình Định', 'Hoài Nhơn, Bình Định', 'Tuy Hòa, Phú Yên', 'Sông Cầu, Phú Yên', 'Quảng Ngãi', 'Gia Lai', 'Kon Tum', 'Đắk Lắk', 'Nha Trang, Khánh Hòa'];

$documentTemplates = [
    'Đề thi thử học kỳ', 'Tóm tắt lý thuyết cốt lõi', 'Bài tập lớn tham khảo', 
    'Sơ đồ tư duy môn học', 'Hướng dẫn thực hành chi tiết', 'Tài liệu ôn tập cuối kỳ'
];
$fileTypes = ['pdf', 'docx', 'pptx', 'zip'];
$uploadDir = ROOT . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

echo "4. Đang kiểm tra bảng `sinh_vien`...\n";
$resSv = $conn->query("SELECT COUNT(*) as total FROM sinh_vien");
$countSv = $resSv->fetch_assoc()['total'];
echo "Số sinh viên hiện có: $countSv\n";

$existingMaSv = [];
$resExistingSv = $conn->query("SELECT ma_sv FROM sinh_vien");
while ($row = $resExistingSv->fetch_assoc()) {
    $existingMaSv[] = $row['ma_sv'];
}

$insertedSvCount = 0;
$totalSvNeeded = 105;

$classMap = [
    2022 => ['nien_khoa' => '2022-2026', 'sv_prefix' => '312241', 'lop_suffix' => 'A'],
    2023 => ['nien_khoa' => '2023-2027', 'sv_prefix' => '312341', 'lop_suffix' => 'B'],
    2024 => ['nien_khoa' => '2024-2028', 'sv_prefix' => '312441', 'lop_suffix' => 'C'],
    2025 => ['nien_khoa' => '2025-2029', 'sv_prefix' => '312541', 'lop_suffix' => 'D']
];

if ($countSv < $totalSvNeeded) {
    $svIndex = 10003;
    while (count($existingMaSv) < $totalSvNeeded) {
        $k_year = array_keys($classMap)[rand(0, 3)];
        $c_info = $classMap[$k_year];
        
        $maSv = $c_info['sv_prefix'] . sprintf("%04d", $svIndex++);
        if (in_array($maSv, $existingMaSv)) continue;

        $gioiTinh = rand(0, 1) === 0 ? 'Nam' : 'Nữ';
        $ho = $hos[array_rand($hos)];
        if ($gioiTinh === 'Nam') {
            $dem = $demsNam[array_rand($demsNam)];
            $ten = $tensNam[array_rand($tensNam)];
        } else {
            $dem = $demsNu[array_rand($demsNu)];
            $ten = $tensNu[array_rand($tensNu)];
        }
        $hoTen = "$ho $dem $ten";

        $ngaySinh = sprintf("%d-%02d-%02d", $k_year - 18, rand(1, 12), rand(1, 28));
        $diaChi = $diaChis[array_rand($diaChis)];
        $username = $maSv;
        $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ten . $dem . $ho)) . $maSv . "@student.qnu.edu.vn";
        $sdt = "0" . rand(3, 9) . rand(10000000, 99999999);

        $facultyKey = array_keys($faculties)[rand(0, 4)];
        $faculty = $faculties[$facultyKey];
        $nganh = $faculty['nganh'];
        $khoa = $faculty['khoa'];
        $lop = $faculty['prefix_hp'] . (47 + ($k_year - 2022)) . $c_info['lop_suffix'];

        $stmtUser = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'student', ?)");
        $stmtUser->bind_param("sss", $username, $hashedPassword, $email);
        if ($stmtUser->execute()) {
            $userId = $conn->insert_id;

            $stmtSv = $conn->prepare("INSERT INTO sinh_vien (user_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, dia_chi, email, so_dien_thoai, nganh, khoa, lop, nien_khoa, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Đang học')");
            $stmtSv->bind_param("isssssssssss", $userId, $maSv, $hoTen, $ngaySinh, $gioiTinh, $diaChi, $email, $sdt, $nganh, $khoa, $lop, $c_info['nien_khoa']);
            if ($stmtSv->execute()) {
                $existingMaSv[] = $maSv;
                $insertedSvCount++;
            }
            $stmtSv->close();
        }
        $stmtUser->close();
    }
}

$resSv = $conn->query("SELECT id, user_id, ma_sv, nganh, nien_khoa FROM sinh_vien");
$students = $resSv->fetch_all(MYSQLI_ASSOC);
echo "-> Hoàn thành bảng `sinh_vien` & `users`. Số lượng sinh viên: " . count($students) . "\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 5: SINH ĐĂNG KÝ HỌC PHẦN (DANG_KY_HP) CHO KỲ 2 NĂM HỌC 2025-2026
// -----------------------------------------------------------------------------
echo "5. Đang sinh các đăng ký lớp học phần cho Học kỳ 2, năm học 2025-2026...\n";

$insertedDkCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];
    $svNganh = $sv['nganh'];
    $nien_khoa = $sv['nien_khoa'];
    
    $startYear = (int)explode('-', $nien_khoa)[0];
    $svYearOfStudy = 2025 - $startYear + 1; // năm thứ mấy
    $targetHpCtdtHk = $svYearOfStudy * 2; 

    // Lấy các học phần gợi ý
    $stmtHps = $conn->prepare("
        SELECT hp.* FROM ctdt_chi_tiet c 
        JOIN hoc_phan hp ON hp.id = c.hoc_phan_id 
        WHERE c.nganh = ? AND c.hoc_ky = ?
        LIMIT 5
    ");
    $stmtHps->bind_param("si", $svNganh, $targetHpCtdtHk);
    $stmtHps->execute();
    $hpList = $stmtHps->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtHps->close();

    foreach ($hpList as $hp) {
        $hpId = $hp['id'];
        
        // Tìm lớp học phần đang mở của học phần này ở Học kỳ 2, năm học 2025-2026
        $key = $hpId . '_' . $hkHienTai . '_' . $nhHienTai;
        if (isset($lhpMap[$key])) {
            $lhpId = $lhpMap[$key];

            $stmtDk = $conn->prepare("INSERT IGNORE INTO dang_ky_hp (sinh_vien_id, lop_hoc_phan_id, hoc_ky, nam_hoc, trang_thai) VALUES (?, ?, ?, ?, 'Đã duyệt')");
            $hkHienTaiStr = (string)$hkHienTai;
            $stmtDk->bind_param("iiss", $svId, $lhpId, $hkHienTaiStr, $nhHienTai);
            if ($stmtDk->execute() && $conn->affected_rows > 0) {
                $insertedDkCount++;
                // Tăng sĩ số hiện tại của lớp
                $conn->query("UPDATE lop_hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = $lhpId");
            }
            $stmtDk->close();
        }
    }
}

echo "-> Hoàn thành bảng `dang_ky_hp` liên kết lớp học phần. Số bản ghi đăng ký: $insertedDkCount\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 6: SINH ĐIỂM HỌC TẬP (DIEM_HOC_TAP) CHO MÔN HỌC (HOC_PHAN)
// -----------------------------------------------------------------------------
echo "6. Đang kiểm tra và sinh điểm học tập cho sinh viên...\n";

$insertedDiemCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];
    $svNganh = $sv['nganh'];
    $nien_khoa = $sv['nien_khoa'];
    
    $startYear = (int)explode('-', $nien_khoa)[0];
    $svYearOfStudy = 2025 - $startYear + 1;
    $maxPassedHkCtdt = $svYearOfStudy * 2 - 1; 
    if ($maxPassedHkCtdt <= 0) continue;

    $resCheckDiem = $conn->query("SELECT COUNT(*) as total FROM diem_hoc_tap WHERE sinh_vien_id = $svId");
    $hasDiem = $resCheckDiem->fetch_assoc()['total'];

    if ($hasDiem < 3) {
        $stmtHps = $conn->prepare("
            SELECT hp.*, c.hoc_ky as hp_hk 
            FROM ctdt_chi_tiet c 
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id 
            WHERE c.nganh = ? AND c.hoc_ky <= ?
            ORDER BY c.hoc_ky
        ");
        $stmtHps->bind_param("si", $svNganh, $maxPassedHkCtdt);
        $stmtHps->execute();
        $hpList = $stmtHps->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtHps->close();

        foreach ($hpList as $hp) {
            $hpId = $hp['id'];
            $hpHkCtdt = (int)$hp['hp_hk'];
            
            $n = (int)ceil($hpHkCtdt / 2);
            $dbHk = ($hpHkCtdt % 2 == 0) ? 2 : 1; 
            $dbYearStart = $startYear + $n - 1;
            $dbNamHoc = $dbYearStart . '-' . ($dbYearStart + 1);

            $chkDiem = $conn->query("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id=$svId AND hoc_phan_id=$hpId");
            if ($chkDiem->num_rows > 0) continue;

            $isFail = rand(1, 100) <= 6;
            if ($isFail) {
                $diemCc = rand(5, 8);
                $diemGk = rand(2, 4);
                $diemCk = rand(1, 3);
            } else {
                $diemCc = rand(80, 100) / 10.0;
                $diemGk = rand(60, 95) / 10.0;
                $diemCk = rand(55, 95) / 10.0;
            }

            $diemTong = round($diemCc * 0.1 + $diemGk * 0.3 + $diemCk * 0.6, 2);

            $diemHe4 = 0.0;
            $diemChu = 'F';
            if ($diemTong >= 9.0) { $diemChu = 'A+'; $diemHe4 = 4.0; }
            elseif ($diemTong >= 8.5) { $diemChu = 'A';  $diemHe4 = 3.7; }
            elseif ($diemTong >= 8.0) { $diemChu = 'B+'; $diemHe4 = 3.5; }
            elseif ($diemTong >= 7.0) { $diemChu = 'B';  $diemHe4 = 3.0; }
            elseif ($diemTong >= 6.5) { $diemChu = 'C+'; $diemHe4 = 2.5; }
            elseif ($diemTong >= 5.5) { $diemChu = 'C';  $diemHe4 = 2.0; }
            elseif ($diemTong >= 5.0) { $diemChu = 'D+'; $diemHe4 = 1.5; }
            elseif ($diemTong >= 4.0) { $diemChu = 'D';  $diemHe4 = 1.0; }

            $stmtDiem = $conn->prepare("INSERT INTO diem_hoc_tap (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, diem_cc, diem_gk, diem_ck, diem_tong, diem_chu, diem_he4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtDiem->bind_param("iiisddddsd", $svId, $hpId, $dbHk, $dbNamHoc, $diemCc, $diemGk, $diemCk, $diemTong, $diemChu, $diemHe4);
            if ($stmtDiem->execute()) {
                $insertedDiemCount++;
            }
            $stmtDiem->close();
        }
    }
}

$resDiemCount = $conn->query("SELECT COUNT(*) as total FROM diem_hoc_tap");
$finalCountDiem = $resDiemCount->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `diem_hoc_tap`. Số lượng điểm: $finalCountDiem (Đã thêm: $insertedDiemCount)\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 7: SINH ĐIỂM RÈN LUYỆN (DIEM_REN_LUYEN)
// -----------------------------------------------------------------------------
echo "7. Đang sinh điểm rèn luyện...\n";
$insertedDrlCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];
    $nien_khoa = $sv['nien_khoa'];
    
    $startYear = (int)explode('-', $nien_khoa)[0];
    $svYearOfStudy = 2025 - $startYear + 1; 

    $resCheckDrl = $conn->query("SELECT COUNT(*) as total FROM diem_ren_luyen WHERE sinh_vien_id = $svId");
    $hasDrl = $resCheckDrl->fetch_assoc()['total'];

    $targetDrlCount = ($svYearOfStudy - 1) * 2 + 1; 
    if ($targetDrlCount <= 0) continue;

    if ($hasDrl < $targetDrlCount) {
        for ($n = 1; $n <= $svYearOfStudy; $n++) {
            $dbYearStart = $startYear + $n - 1;
            $dbNamHoc = $dbYearStart . '-' . ($dbYearStart + 1);

            for ($dbHk = 1; $dbHk <= 2; $dbHk++) {
                if ($n == $svYearOfStudy && $dbHk == 2) break;

                $diem = rand(60, 95);
                $xepLoai = 'Khá';
                if ($diem >= 90) $xepLoai = 'Xuất sắc';
                elseif ($diem >= 80) $xepLoai = 'Tốt';
                elseif ($diem >= 65) $xepLoai = 'Khá';
                elseif ($diem >= 50) $xepLoai = 'Trung bình';
                else $xepLoai = 'Yếu';

                $ghiChu = "Tham gia đầy đủ các hoạt động học tập, phong trào ngoại khóa.";

                $stmtDrl = $conn->prepare("INSERT IGNORE INTO diem_ren_luyen (sinh_vien_id, hoc_ky, nam_hoc, diem, xep_loai, ghi_chu) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtDrl->bind_param("iiisss", $svId, $dbHk, $dbNamHoc, $diem, $xepLoai, $ghiChu);
                if ($stmtDrl->execute()) {
                    $insertedDrlCount++;
                }
                $stmtDrl->close();
            }
        }
    }
}

$resDrlCount = $conn->query("SELECT COUNT(*) as total FROM diem_ren_luyen");
$finalCountDrl = $resDrlCount->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `diem_ren_luyen`. Số bản ghi: $finalCountDrl (Đã thêm: $insertedDrlCount)\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 8: SINH HỌC PHÍ (HOC_PHI)
// -----------------------------------------------------------------------------
echo "8. Đang sinh học phí...\n";
$insertedHpTableCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];
    $nien_khoa = $sv['nien_khoa'];
    
    $startYear = (int)explode('-', $nien_khoa)[0];
    $svYearOfStudy = 2025 - $startYear + 1;

    $resCheckHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phi WHERE sinh_vien_id = $svId");
    $hasHp = $resCheckHp->fetch_assoc()['total'];

    $targetHpCount = ($svYearOfStudy - 1) * 2 + 2; 

    if ($hasHp < $targetHpCount) {
        for ($n = 1; $n <= $svYearOfStudy; $n++) {
            $dbYearStart = $startYear + $n - 1;
            $dbNamHoc = $dbYearStart . '-' . ($dbYearStart + 1);

            for ($dbHk = 1; $dbHk <= 2; $dbHk++) {
                $soTien = rand(85, 115) * 100000;
                $hanNop = sprintf("%d-%02d-15", $dbYearStart + ($dbHk == 1 ? 0 : 1), $dbHk == 1 ? 10 : 3);

                if ($n == $svYearOfStudy && $dbHk == 2) {
                    $rand = rand(1, 100);
                    if ($rand <= 40) {
                        $trangThai = 'Đã nộp';
                        $daNop = $soTien;
                    } elseif ($rand <= 75) {
                        $trangThai = 'Chưa nộp';
                        $daNop = 0;
                    } else {
                        $trangThai = 'Nợ';
                        $daNop = rand(2, 5) * 1000000;
                    }
                } else {
                    $trangThai = 'Đã nộp';
                    $daNop = $soTien;
                }

                $stmtHp = $conn->prepare("INSERT IGNORE INTO hoc_phi (sinh_vien_id, hoc_ky, nam_hoc, so_tien, da_nop, han_nop, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtHp->bind_param("iissdss", $svId, $dbHk, $dbNamHoc, $soTien, $daNop, $hanNop, $trangThai);
                if ($stmtHp->execute()) {
                    $insertedHpTableCount++;
                }
                $stmtHp->close();
            }
        }
    }
}

$resHpCount = $conn->query("SELECT COUNT(*) as total FROM hoc_phi");
$finalCountHpTable = $resHpCount->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `hoc_phi`. Số bản ghi: $finalCountHpTable\n\n";

// -----------------------------------------------------------------------------
// BƯỚC 9: SINH TÀI LIỆU CHIA SẺ
// -----------------------------------------------------------------------------
echo "9. Đang chèn tài liệu chia sẻ...\n";
$resTl = $conn->query("SELECT COUNT(*) as total FROM tai_lieu");
$countTl = $resTl->fetch_assoc()['total'];

if ($countTl < 105) {
    $needed = 105 - $countTl;
    $allHps = [];
    foreach ($hocPhans as $hp) {
        $allHps[] = $hp['id'];
    }

    for ($i = 0; $i < $needed; $i++) {
        $sv = $students[array_rand($students)];
        $svId = $sv['id'];
        
        $hpId = !empty($allHps) ? $allHps[array_rand($allHps)] : null;
        $tieuDe = $documentTemplates[array_rand($documentTemplates)] . " - Lớp HP Phần " . rand(1, 5);
        $moTa = "Tài liệu tự học, bài tập lớn và đề thi thử lớp học phần hỗ trợ học tốt môn học.";
        
        $ext = $fileTypes[array_rand($fileTypes)];
        $tenFile = str_replace(' ', '_', strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $tieuDe))) . '.' . $ext;
        
        $newFilename = time() . '_' . $svId . '_' . rand(1000, 9999) . '_' . $tenFile;
        $destFilepath = $uploadDir . $newFilename;
        
        file_put_contents($destFilepath, "Tài liệu lớp học phần QNU SMS: " . $tieuDe);
        
        $kichThuoc = filesize($destFilepath);
        $loaiFile = strtoupper($ext);
        $luotTai = rand(0, 50);

        $stmtTl = $conn->prepare("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file, luot_tai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtTl->bind_param("iissssisi", $svId, $hpId, $tieuDe, $moTa, $tenFile, $newFilename, $kichThuoc, $loaiFile, $luotTai);
        $stmtTl->execute();
        $stmtTl->close();
    }
}

echo "-> Hoàn thành bảng `tai_lieu` chia sẻ.\n\n";
echo "=========================================================\n";
echo "DATABASE SEEDER TÍN CHỈ MỚI ĐÃ THỰC THI THÀNH CÔNG RỰC RỠ!\n";
echo "=========================================================\n";
?>
