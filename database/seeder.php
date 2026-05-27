<?php
/**
 * Database Seeder Script - QNU Student Management System
 * Tự động sinh dữ liệu mẫu chất lượng cao, tối thiểu 100 bản ghi cho mỗi bảng.
 * Tuyệt đối KHÔNG xóa dữ liệu cũ, chỉ chèn thêm dữ liệu mới.
 * Mật khẩu sinh viên mới: Student@123 (Đáp ứng điều kiện: viết hoa, số, ký tự đặc biệt).
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';

// Autoloader cơ bản cho chuẩn PSR-4 đề phòng có dùng class hệ thống
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Thiết lập chạy không giới hạn thời gian (trong trường hợp chèn nhiều)
set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

echo "=========================================================\n";
echo "BẮT ĐẦU CHẠY DATABASE SEEDER CHO HỆ THỐNG QNU SMS\n";
echo "=========================================================\n\n";

$conn = getDB();
if (!$conn) {
    die("LỖI: Không thể kết nối cơ sở dữ liệu.\n");
}

// Ràng buộc mật khẩu chuẩn Bcrypt
$defaultPassword = 'Student@123';
$hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);
echo "Mật khẩu sinh viên mẫu mặc định: '$defaultPassword' (Bcrypt hashed)\n\n";

// Định nghĩa 5 Khoa, 5 Ngành và 5 Lớp tương ứng
$faculties = [
    'Công nghệ thông tin' => [
        'khoa' => 'Kỹ thuật - Công nghệ',
        'nganh' => 'Công nghệ thông tin',
        'lop' => 'CNTT47A',
        'prefix_hp' => 'CNTT'
    ],
    'Kinh tế - Quản trị kinh doanh' => [
        'khoa' => 'Kinh tế - Luật',
        'nganh' => 'Quản trị kinh doanh',
        'lop' => 'QTKD47A',
        'prefix_hp' => 'QTKD'
    ],
    'Ngoại ngữ' => [
        'khoa' => 'Ngoại ngữ',
        'nganh' => 'Ngôn ngữ Anh',
        'lop' => 'NNA47A',
        'prefix_hp' => 'NNA'
    ],
    'Sư phạm' => [
        'khoa' => 'Khoa học Tự nhiên',
        'nganh' => 'Sư phạm Toán',
        'lop' => 'SPT47A',
        'prefix_hp' => 'SPT'
    ],
    'Kỹ thuật và Công nghệ' => [
        'khoa' => 'Kỹ thuật - Công nghệ',
        'nganh' => 'Kỹ thuật điện',
        'lop' => 'KTD47A',
        'prefix_hp' => 'KTD'
    ]
];

// Môn học mẫu cụ thể cho từng ngành
$subjectsTemplate = [
    'CNTT' => [
        ['Lập trình căn bản', 4, 'Bắt buộc'],
        ['Cấu trúc dữ liệu và giải thuật', 3, 'Bắt buộc'],
        ['Lập trình hướng đối tượng', 3, 'Bắt buộc'],
        ['Cơ sở dữ liệu', 3, 'Bắt buộc'],
        ['Mạng máy tính', 3, 'Bắt buộc'],
        ['Lập trình Web', 3, 'Bắt buộc'],
        ['Hệ điều hành', 3, 'Bắt buộc'],
        ['Kiến trúc máy tính', 3, 'Đại cương'],
        ['Trí tuệ nhân tạo', 3, 'Tự chọn'],
        ['An toàn thông tin', 3, 'Tự chọn'],
        ['Phát triển ứng dụng Mobile', 3, 'Tự chọn'],
        ['Phân tích thiết kế hệ thống', 3, 'Bắt buộc'],
        ['Quản trị dự án phần mềm', 3, 'Tự chọn'],
        ['Điện toán đám mây', 3, 'Tự chọn'],
        ['Đồ án ngành Công nghệ thông tin', 2, 'Bắt buộc'],
        ['Thực tập tốt nghiệp', 5, 'Bắt buộc'],
        ['Đồ án tốt nghiệp', 7, 'Bắt buộc']
    ],
    'QTKD' => [
        ['Quản trị học', 3, 'Bắt buộc'],
        ['Kinh tế vĩ mô', 3, 'Đại cương'],
        ['Kinh tế vi mô', 3, 'Đại cương'],
        ['Nguyên lý kế toán', 3, 'Bắt buộc'],
        ['Marketing căn bản', 3, 'Bắt buộc'],
        ['Quản trị tài chính', 3, 'Bắt buộc'],
        ['Quản trị nhân lực', 3, 'Bắt buộc'],
        ['Quản trị chiến lược', 3, 'Bắt buộc'],
        ['Hành vi tổ chức', 3, 'Tự chọn'],
        ['Thương mại quốc tế', 3, 'Tự chọn'],
        ['Quản trị chất lượng', 3, 'Tự chọn'],
        ['Khởi nghiệp kinh doanh', 3, 'Tự chọn'],
        ['Logistics và chuỗi cung ứng', 3, 'Tự chọn'],
        ['Nghiên cứu marketing', 3, 'Bắt buộc'],
        ['Đồ án ngành Quản trị kinh doanh', 2, 'Bắt buộc'],
        ['Thực tập tốt nghiệp QTKD', 5, 'Bắt buộc'],
        ['Khóa luận tốt nghiệp QTKD', 7, 'Bắt buộc']
    ],
    'NNA' => [
        ['Tiếng Anh giao tiếp', 3, 'Đại cương'],
        ['Ngữ pháp tiếng Anh', 3, 'Bắt buộc'],
        ['Đọc hiểu tiếng Anh 1', 3, 'Bắt buộc'],
        ['Viết tiếng Anh 1', 3, 'Bắt buộc'],
        ['Nghe nói tiếng Anh 1', 3, 'Bắt buộc'],
        ['Biên dịch tiếng Anh', 3, 'Bắt buộc'],
        ['Phiên dịch tiếng Anh', 3, 'Bắt buộc'],
        ['Văn hóa Anh Mỹ', 3, 'Tự chọn'],
        ['Văn học Anh Mỹ', 3, 'Tự chọn'],
        ['Tiếng Anh thương mại', 3, 'Tự chọn'],
        ['Tiếng Anh du lịch', 3, 'Tự chọn'],
        ['Phương pháp giảng dạy Tiếng Anh', 3, 'Tự chọn'],
        ['Từ vựng học tiếng Anh', 2, 'Bắt buộc'],
        ['Ngữ âm học tiếng Anh', 2, 'Bắt buộc'],
        ['Đồ án ngành Ngôn ngữ Anh', 2, 'Bắt buộc'],
        ['Thực tập tốt nghiệp NNA', 5, 'Bắt buộc'],
        ['Khóa luận tốt nghiệp NNA', 7, 'Bắt buộc']
    ],
    'SPT' => [
        ['Giải tích 1', 4, 'Đại cương'],
        ['Đại số tuyến tính', 4, 'Đại cương'],
        ['Hình học cổ điển', 3, 'Bắt buộc'],
        ['Lý thuyết số', 3, 'Bắt buộc'],
        ['Phương pháp dạy học Toán', 3, 'Bắt buộc'],
        ['Hình học giải tích', 3, 'Bắt buộc'],
        ['Giải tích phức', 3, 'Tự chọn'],
        ['Đại số đại cương', 3, 'Bắt buộc'],
        ['Giải tích thực', 3, 'Tự chọn'],
        ['Hình học vi phân', 3, 'Tự chọn'],
        ['Phương trình vi phân', 3, 'Bắt buộc'],
        ['Xác suất thống kê Toán', 3, 'Bắt buộc'],
        ['Tâm lý học sư phạm', 2, 'Bắt buộc'],
        ['Giáo dục học đại cương', 2, 'Bắt buộc'],
        ['Thực tập sư phạm 1', 2, 'Bắt buộc'],
        ['Thực tập sư phạm tốt nghiệp', 5, 'Bắt buộc'],
        ['Khóa luận tốt nghiệp Sư phạm Toán', 7, 'Bắt buộc']
    ],
    'KTD' => [
        ['Mạch điện 1', 3, 'Bắt buộc'],
        ['Kỹ thuật điện tử', 3, 'Bắt buộc'],
        ['Máy điện', 3, 'Bắt buộc'],
        ['Hệ thống cung cấp điện', 3, 'Bắt buộc'],
        ['Đo lường điện', 3, 'Bắt buộc'],
        ['Điều khiển tự động', 3, 'Bắt buộc'],
        ['Điện tử công suất', 3, 'Bắt buộc'],
        ['An toàn điện', 2, 'Đại cương'],
        ['Kỹ thuật lập trình vi điều khiển', 3, 'Tự chọn'],
        ['Năng lượng tái tạo', 3, 'Tự chọn'],
        ['Truyền động điện', 3, 'Tự chọn'],
        ['Thiết kế hệ thống điện', 3, 'Tự chọn'],
        ['Mạng điện và trạm biến áp', 3, 'Bắt buộc'],
        ['Đồ án chuyên ngành Kỹ thuật điện', 2, 'Bắt buộc'],
        ['Thực tập tốt nghiệp Kỹ thuật điện', 5, 'Bắt buộc'],
        ['Khóa luận tốt nghiệp Kỹ thuật điện', 7, 'Bắt buộc']
    ]
];

// -----------------------------------------------------------------------------
// BƯỚC 1: SINH DỮ LIỆU BẢNG HỌC PHẦN (HOC_PHAN) VÀ CTDT_CHI_TIET
// -----------------------------------------------------------------------------
echo "1. Đang kiểm tra bảng `hoc_phan`...\n";
$resHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phan");
$countHp = $resHp->fetch_assoc()['total'];
echo "Số học phần hiện có: $countHp\n";

// Lấy các mã học phần hiện tại để tránh trùng lặp
$existingMaHp = [];
$resExisting = $conn->query("SELECT ma_hp FROM hoc_phan");
while ($row = $resExisting->fetch_assoc()) {
    $existingMaHp[] = $row['ma_hp'];
}

$insertedHpCount = 0;
$totalHpNeeded = 105; // Sinh cho dư giả (> 100)

if ($countHp < $totalHpNeeded) {
    $needed = $totalHpNeeded - $countHp;
    echo "Cần chèn thêm ít nhất $needed học phần...\n";

    // Danh sách giảng viên ngẫu nhiên
    $giangViens = [
        'TS. Nguyễn Văn Hùng', 'ThS. Trần Thị Lan', 'TS. Lê Văn Minh', 'ThS. Hoàng Văn E', 
        'ThS. Phạm Thị Hoa', 'TS. Hoàng Quang Trung', 'ThS. Nguyễn Thị F', 'TS. Trần Văn G', 
        'TS. Lê Văn H', 'TS. Lý Văn I', 'ThS. Nguyễn Văn J', 'Cô Đỗ Thị K', 'Thầy Vũ Văn L',
        'TS. Phạm Minh Tuấn', 'TS. Đặng Thanh Sơn', 'ThS. Lê Hoàng Nam', 'ThS. Nguyễn Thị Mai',
        'TS. Bùi Quốc Bảo', 'ThS. Huỳnh Tấn Đạt', 'TS. Phan Anh Tuấn', 'TS. Vũ Thị Quỳnh'
    ];
    $phongs = ['A101', 'A102', 'A201', 'A301', 'B101', 'B201', 'B202', 'B302', 'B303', 'B304', 'B305', 'Lab IT', 'Lab Điện'];

    // Đầu tiên chèn các môn học từ template
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
            if (in_array($maHp, $existingMaHp)) {
                continue;
            }

            $tenHp = $sub[0];
            $soTc = $sub[1];
            $loai = $sub[2];
            $hocKy = rand(1, 8);
            $thu = rand(2, 7);
            $tietBd = rand(1, 7);
            $soTiet = rand(2, 4);
            $phong = $phongs[array_rand($phongs)];
            $gv = $giangViens[array_rand($giangViens)];
            $siSoMax = rand(50, 100);

            // Insert môn học
            $stmt = $conn->prepare("INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, si_so_toi_da, si_so_hien_tai) VALUES (?, ?, ?, ?, ?, '2021-2025', ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("ssisiisissi", $maHp, $tenHp, $soTc, $loai, $hocKy, $thu, $tietBd, $soTiet, $phong, $gv, $siSoMax);
            if ($stmt->execute()) {
                $hpId = $conn->insert_id;
                $existingMaHp[] = $maHp;
                $insertedHpCount++;

                // Chèn vào CTDT cho ngành này
                $stmtCtdt = $conn->prepare("INSERT INTO ctdt_chi_tiet (nganh, hoc_phan_id, hoc_ky) VALUES (?, ?, ?)");
                $stmtCtdt->bind_param("sii", $facultyData['nganh'], $hpId, $hocKy);
                $stmtCtdt->execute();
                $stmtCtdt->close();
            }
            $stmt->close();
        }
    }

    // Nếu vẫn chưa đủ 100, tiếp tục sinh ngẫu nhiên môn học bổ sung
    $subIndex = 100;
    while (count($existingMaHp) < $totalHpNeeded) {
        $prefix = array_keys($subjectsTemplate)[rand(0, 4)];
        $maHp = $prefix . $subIndex++;
        if (in_array($maHp, $existingMaHp)) {
            continue;
        }

        $tenHp = "Môn học chuyên đề " . $maHp;
        $soTc = rand(2, 4);
        $loai = rand(0, 10) > 7 ? 'Tự chọn' : 'Bắt buộc';
        $hocKy = rand(1, 8);
        $thu = rand(2, 7);
        $tietBd = rand(1, 7);
        $soTiet = rand(2, 4);
        $phong = $phongs[array_rand($phongs)];
        $gv = $giangViens[array_rand($giangViens)];
        $siSoMax = rand(50, 100);

        $stmt = $conn->prepare("INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, si_so_toi_da, si_so_hien_tai) VALUES (?, ?, ?, ?, ?, '2021-2025', ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssisiisissi", $maHp, $tenHp, $soTc, $loai, $hocKy, $thu, $tietBd, $soTiet, $phong, $gv, $siSoMax);
        if ($stmt->execute()) {
            $hpId = $conn->insert_id;
            $existingMaHp[] = $maHp;
            $insertedHpCount++;

            // Lấy ngành tương ứng với prefix
            $nganhTarget = '';
            foreach ($faculties as $key => $val) {
                if ($val['prefix_hp'] === $prefix) {
                    $nganhTarget = $val['nganh'];
                    break;
                }
            }

            // Chèn vào CTDT
            $stmtCtdt = $conn->prepare("INSERT INTO ctdt_chi_tiet (nganh, hoc_phan_id, hoc_ky) VALUES (?, ?, ?)");
            $stmtCtdt->bind_param("sii", $nganhTarget, $hpId, $hocKy);
            $stmtCtdt->execute();
            $stmtCtdt->close();
        }
        $stmt->close();
    }
}

$resHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phan");
$finalCountHp = $resHp->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `hoc_phan`. Số lượng bản ghi hiện tại: $finalCountHp (Đã thêm: $insertedHpCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 2: SINH DỮ LIỆU BẢNG USERS VÀ SINH_VIEN
// -----------------------------------------------------------------------------
echo "2. Đang kiểm tra bảng `sinh_vien`...\n";
$resSv = $conn->query("SELECT COUNT(*) as total FROM sinh_vien");
$countSv = $resSv->fetch_assoc()['total'];
echo "Số sinh viên hiện có: $countSv\n";

$existingMaSv = [];
$resExistingSv = $conn->query("SELECT ma_sv FROM sinh_vien");
while ($row = $resExistingSv->fetch_assoc()) {
    $existingMaSv[] = $row['ma_sv'];
}

$insertedSvCount = 0;
$totalSvNeeded = 105; // Sinh cho dư giả (> 100)

// Họ đệm và tên tiếng Việt ngẫu nhiên để sinh dữ liệu thật
$hos = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Huỳnh', 'Hoàng', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];
$demsNam = ['Văn', 'Hữu', 'Minh', 'Đức', 'Quang', 'Thành', 'Xuân', 'Anh', 'Quốc', 'Tuấn'];
$demsNu = ['Thị', 'Thanh', 'Diệu', 'Ngọc', 'Quỳnh', 'Phương', 'Tuyết', 'Hồng', 'Minh'];
$tensNam = ['An', 'Bình', 'Cường', 'Dũng', 'Em', 'Hải', 'Hùng', 'Huy', 'Khánh', 'Lộc', 'Minh', 'Nam', 'Nhân', 'Phong', 'Phúc', 'Quân', 'Sơn', 'Tài', 'Tâm', 'Tùng', 'Tuấn', 'Vinh', 'Khải', 'Thịnh'];
$tensNu = ['Anh', 'Bình', 'Chi', 'Diệp', 'Dung', 'Giang', 'Hà', 'Hoa', 'Hương', 'Lan', 'Linh', 'Mai', 'Nga', 'Oanh', 'Phương', 'Quỳnh', 'Thảo', 'Trang', 'Tuyết', 'Vân', 'Yến', 'Trúc', 'Hân', 'Vy'];

$diaChis = [
    '123 Lê Lợi, Quy Nhơn, Bình Định', '45 Nguyễn Huệ, Quy Nhơn, Bình Định', '78 Trần Hưng Đạo, Quy Nhơn, Bình Định',
    '12 Ngô Mây, Quy Nhơn, Bình Định', '56 Nguyễn Thái Học, Quy Nhơn, Bình Định', '89 An Dương Vương, Quy Nhơn, Bình Định',
    '34 Tây Sơn, Quy Nhơn, Bình Định', '67 Hùng Vương, Quy Nhơn, Bình Định', '90 Xuân Diệu, Quy Nhơn, Bình Định',
    'Phú Tài, Quy Nhơn, Bình Định', 'Tuy Phước, Bình Định', 'An Nhơn, Bình Định', 'Hoài Nhơn, Bình Định',
    'Tây Sơn, Bình Định', 'Phù Cát, Bình Định', 'Phù Mỹ, Bình Định', 'Sông Cầu, Phú Yên', 'Tuy Hòa, Phú Yên',
    'Quảng Ngãi', 'Gia Lai', 'Kon Tum', 'Đắk Lắk'
];

if ($countSv < $totalSvNeeded) {
    $needed = $totalSvNeeded - $countSv;
    echo "Cần chèn thêm ít nhất $needed sinh viên mới...\n";

    // Bắt đầu mã sinh viên từ 3121410003 (để tránh trùng với sv001, sv002 và sv hiện tại)
    $svIndex = 10003;
    while (count($existingMaSv) < $totalSvNeeded) {
        $maSv = "312141" . sprintf("%04d", $svIndex++);
        if (in_array($maSv, $existingMaSv)) {
            continue;
        }

        // Sinh tên ngẫu nhiên
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

        // Sinh các thông tin khác
        $ngaySinh = sprintf("2003-%02d-%02d", rand(1, 12), rand(1, 28));
        $diaChi = $diaChis[array_rand($diaChis)];
        $username = $maSv;
        $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ten . $dem . $ho)) . $maSv . "@student.qnu.edu.vn";
        $sdt = "0" . rand(3, 9) . rand(10000000, 99999999);

        // Lấy ngành, khoa, lớp ngẫu nhiên trong 5 ngành
        $facultyKey = array_keys($faculties)[rand(0, 4)];
        $faculty = $faculties[$facultyKey];
        $nganh = $faculty['nganh'];
        $khoa = $faculty['khoa'];
        $lop = $faculty['lop'];

        // 1. Chèn vào bảng users
        $stmtUser = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'student', ?)");
        $stmtUser->bind_param("sss", $username, $hashedPassword, $email);
        if ($stmtUser->execute()) {
            $userId = $conn->insert_id;

            // 2. Chèn vào bảng sinh_vien
            $stmtSv = $conn->prepare("INSERT INTO sinh_vien (user_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, dia_chi, email, so_dien_thoai, nganh, khoa, lop, nien_khoa, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2021-2025', 'Đang học')");
            $stmtSv->bind_param("issssssssss", $userId, $maSv, $hoTen, $ngaySinh, $gioiTinh, $diaChi, $email, $sdt, $nganh, $khoa, $lop);
            if ($stmtSv->execute()) {
                $existingMaSv[] = $maSv;
                $insertedSvCount++;
            }
            $stmtSv->close();
        }
        $stmtUser->close();
    }
}

$resSv = $conn->query("SELECT COUNT(*) as total FROM sinh_vien");
$finalCountSv = $resSv->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `sinh_vien` & `users`. Số lượng sinh viên hiện tại: $finalCountSv (Đã thêm: $insertedSvCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 3: SINH ĐĂNG KÝ HỌC PHẦN (DANG_KY_HP) VÀ THỜI KHÓA BIỂU (THOI_KHOA_BIEU)
// -----------------------------------------------------------------------------
echo "3. Đang kiểm tra bảng `dang_ky_hp` & `thoi_khoa_bieu` cho học kỳ hiện tại...\n";

// Lấy danh sách toàn bộ sinh viên
$students = [];
$resSvs = $conn->query("SELECT id, nganh FROM sinh_vien");
while ($row = $resSvs->fetch_assoc()) {
    $students[] = $row;
}

// Học kỳ và năm học hiện tại
$hkHienTai = HOC_KY_HIEN_TAI;
$nhHienTai = NAM_HOC_HIEN_TAI;

$insertedDkCount = 0;
$insertedTkbCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];
    $svNganh = $sv['nganh'];

    // Kiểm tra xem sinh viên này đã đăng ký học phần nào ở HK hiện tại chưa
    $resCheck = $conn->prepare("SELECT COUNT(*) as total FROM dang_ky_hp WHERE sinh_vien_id = ? AND hoc_ky = ? AND nam_hoc = ?");
    $hkHienTaiStr = (string)$hkHienTai;
    $resCheck->bind_param("iss", $svId, $hkHienTaiStr, $nhHienTai);
    $resCheck->execute();
    $countDk = $resCheck->get_result()->fetch_assoc()['total'];
    $resCheck->close();

    if ($countDk == 0) {
        // Sinh viên chưa đăng ký môn học nào ở HK hiện tại, hãy đăng ký cho họ
        // Lấy danh sách các học phần trong chương trình đào tạo của ngành này ở học kỳ 5 hoặc lân cận
        $stmtHps = $conn->prepare("
            SELECT hp.* FROM ctdt_chi_tiet c 
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id 
            WHERE c.nganh = ? AND c.hoc_ky IN (4, 5, 6)
            LIMIT 5
        ");
        $stmtHps->bind_param("s", $svNganh);
        $stmtHps->execute();
        $hpList = $stmtHps->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtHps->close();

        if (empty($hpList)) {
            // Fallback lấy ngẫu nhiên 4 môn bất kỳ
            $resFallback = $conn->query("SELECT * FROM hoc_phan LIMIT 4");
            $hpList = $resFallback->fetch_all(MYSQLI_ASSOC);
        }

        foreach ($hpList as $hp) {
            $hpId = $hp['id'];

            // 1. Chèn vào dang_ky_hp
            $stmtDk = $conn->prepare("INSERT IGNORE INTO dang_ky_hp (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, trang_thai) VALUES (?, ?, ?, ?, 'Đã duyệt')");
            $stmtDk->bind_param("iiss", $svId, $hpId, $hkHienTaiStr, $nhHienTai);
            if ($stmtDk->execute() && $conn->affected_rows > 0) {
                $insertedDkCount++;

                // Tăng sĩ số hiện tại của môn học
                $conn->query("UPDATE hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = $hpId");

                // 2. Chèn vào thoi_khoa_bieu
                $thu = $hp['thu'] ?? rand(2, 7);
                $tietBd = $hp['tiet_bat_dau'] ?? rand(1, 7);
                $soTiet = $hp['so_tiet'] ?? 3;
                $phong = $hp['phong_hoc'] ?? 'A101';
                $gv = $hp['giang_vien'] ?? 'TS. Nguyễn Văn Hùng';

                $stmtTkb = $conn->prepare("INSERT INTO thoi_khoa_bieu (sinh_vien_id, hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtTkb->bind_param("iiiiissss", $svId, $hpId, $thu, $tietBd, $soTiet, $phong, $gv, $hkHienTai, $nhHienTai);
                if ($stmtTkb->execute()) {
                    $insertedTkbCount++;
                }
                $stmtTkb->close();
            }
            $stmtDk->close();
        }
    }
}

$resDk = $conn->query("SELECT COUNT(*) as total FROM dang_ky_hp");
$finalCountDk = $resDk->fetch_assoc()['total'];
$resTkb = $conn->query("SELECT COUNT(*) as total FROM thoi_khoa_bieu");
$finalCountTkb = $resTkb->fetch_assoc()['total'];

echo "-> Hoàn thành bảng `dang_ky_hp` & `thoi_khoa_bieu`.\n";
echo "   Tổng số đăng ký: $finalCountDk (Đã thêm: $insertedDkCount)\n";
echo "   Tổng số thời khóa biểu: $finalCountTkb (Đã thêm: $insertedTkbCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 4: SINH ĐIỂM HỌC TẬP (DIEM_HOC_TAP) CHO CÁC KỲ TRƯỚC
// -----------------------------------------------------------------------------
echo "4. Đang kiểm tra bảng `diem_hoc_tap` cho các học kỳ trước (1, 2, 3, 4)...\n";
$resDiem = $conn->query("SELECT COUNT(*) as total FROM diem_hoc_tap");
$countDiem = $resDiem->fetch_assoc()['total'];
echo "Số lượng điểm hiện có: $countDiem\n";

$insertedDiemCount = 0;

// Chỉ sinh điểm cho sinh viên chưa có điểm ở các kỳ trước
foreach ($students as $sv) {
    $svId = $sv['id'];
    $svNganh = $sv['nganh'];

    $resCheckDiem = $conn->query("SELECT COUNT(*) as total FROM diem_hoc_tap WHERE sinh_vien_id = $svId");
    $hasDiem = $resCheckDiem->fetch_assoc()['total'];

    if ($hasDiem < 5) {
        // Sinh viên này thiếu điểm học tập, sinh điểm cho họ ở kỳ 1, 2, 3, 4
        // Lấy danh sách môn học thuộc CTDT của ngành ở kỳ 1, 2, 3, 4
        $stmtHps = $conn->prepare("
            SELECT hp.*, c.hoc_ky as hp_hk FROM ctdt_chi_tiet c 
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id 
            WHERE c.nganh = ? AND c.hoc_ky IN (1, 2, 3, 4)
            LIMIT 12
        ");
        $stmtHps->bind_param("s", $svNganh);
        $stmtHps->execute();
        $hpList = $stmtHps->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtHps->close();

        if (empty($hpList)) {
            $resFallback = $conn->query("SELECT *, 1 as hp_hk FROM hoc_phan LIMIT 8");
            $hpList = $resFallback->fetch_all(MYSQLI_ASSOC);
        }

        foreach ($hpList as $hp) {
            $hpId = $hp['id'];
            $hpHk = $hp['hp_hk'];
            
            // Xác định năm học dựa trên học kỳ của môn học
            $namHoc = '2021-2022';
            if ($hpHk == 3 || $hpHk == 4) {
                $namHoc = '2022-2023';
            }

            // Sinh điểm ngẫu nhiên
            // Tạo 10% tỷ lệ bị điểm F để kiểm tra bộ lọc cảnh báo học tập
            $isFail = rand(1, 100) <= 8; // 8% trượt môn

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

            // Tính điểm hệ 4 và điểm chữ
            $diemHe4 = 0.0;
            $diemChu = 'F';

            if ($diemTong >= 9.0) {
                $diemChu = 'A+'; $diemHe4 = 4.0;
            } elseif ($diemTong >= 8.5) {
                $diemChu = 'A';  $diemHe4 = 3.7;
            } elseif ($diemTong >= 8.0) {
                $diemChu = 'B+'; $diemHe4 = 3.5;
            } elseif ($diemTong >= 7.0) {
                $diemChu = 'B';  $diemHe4 = 3.0;
            } elseif ($diemTong >= 6.5) {
                $diemChu = 'C+'; $diemHe4 = 2.5;
            } elseif ($diemTong >= 5.5) {
                $diemChu = 'C';  $diemHe4 = 2.0;
            } elseif ($diemTong >= 5.0) {
                $diemChu = 'D+'; $diemHe4 = 1.5;
            } elseif ($diemTong >= 4.0) {
                $diemChu = 'D';  $diemHe4 = 1.0;
            }

            $stmtDiem = $conn->prepare("INSERT IGNORE INTO diem_hoc_tap (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, diem_cc, diem_gk, diem_ck, diem_tong, diem_chu, diem_he4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtDiem->bind_param("iiisddddsd", $svId, $hpId, $hpHk, $namHoc, $diemCc, $diemGk, $diemCk, $diemTong, $diemChu, $diemHe4);
            if ($stmtDiem->execute() && $conn->affected_rows > 0) {
                $insertedDiemCount++;
            }
            $stmtDiem->close();
        }
    }
}

$resDiem = $conn->query("SELECT COUNT(*) as total FROM diem_hoc_tap");
$finalCountDiem = $resDiem->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `diem_hoc_tap`. Tổng số bản ghi điểm học tập: $finalCountDiem (Đã thêm: $insertedDiemCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 5: SINH ĐIỂM RÈN LUYỆN (DIEM_REN_LUYEN)
// -----------------------------------------------------------------------------
echo "5. Đang kiểm tra bảng `diem_ren_luyen`...\n";
$resDrl = $conn->query("SELECT COUNT(*) as total FROM diem_ren_luyen");
$countDrl = $resDrl->fetch_assoc()['total'];
echo "Số lượng điểm rèn luyện hiện có: $countDrl\n";

$insertedDrlCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];

    $resCheckDrl = $conn->query("SELECT COUNT(*) as total FROM diem_ren_luyen WHERE sinh_vien_id = $svId");
    $hasDrl = $resCheckDrl->fetch_assoc()['total'];

    if ($hasDrl < 4) {
        // Sinh điểm rèn luyện cho các kỳ 1, 2, 3, 4
        $semesters = [
            [1, '2021-2022'],
            [2, '2021-2022'],
            [3, '2022-2023'],
            [4, '2022-2023']
        ];

        foreach ($semesters as $sem) {
            $hk = $sem[0];
            $nh = $sem[1];
            $diem = rand(55, 95);

            $xepLoai = 'Khá';
            if ($diem >= 90) $xepLoai = 'Xuất sắc';
            elseif ($diem >= 80) $xepLoai = 'Tốt';
            elseif ($diem >= 65) $xepLoai = 'Khá';
            elseif ($diem >= 50) $xepLoai = 'Trung bình';
            else $xepLoai = 'Yếu';

            $ghiChu = "Tham gia đầy đủ các hoạt động đoàn hội và ngoại khóa.";

            $stmtDrl = $conn->prepare("INSERT IGNORE INTO diem_ren_luyen (sinh_vien_id, hoc_ky, nam_hoc, diem, xep_loai, ghi_chu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtDrl->bind_param("iiisss", $svId, $hk, $nh, $diem, $xepLoai, $ghiChu);
            if ($stmtDrl->execute() && $conn->affected_rows > 0) {
                $insertedDrlCount++;
            }
            $stmtDrl->close();
        }
    }
}

$resDrl = $conn->query("SELECT COUNT(*) as total FROM diem_ren_luyen");
$finalCountDrl = $resDrl->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `diem_ren_luyen`. Tổng số bản ghi rèn luyện: $finalCountDrl (Đã thêm: $insertedDrlCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 6: SINH DỮ LIỆU HỌC PHÍ (HOC_PHI)
// -----------------------------------------------------------------------------
echo "6. Đang kiểm tra bảng `hoc_phi`...\n";
$resHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phi");
$countHpTable = $resHp->fetch_assoc()['total'];
echo "Số học phí hiện có: $countHpTable\n";

$insertedHpTableCount = 0;

foreach ($students as $sv) {
    $svId = $sv['id'];

    $resCheckHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phi WHERE sinh_vien_id = $svId");
    $hasHp = $resCheckHp->fetch_assoc()['total'];

    if ($hasHp < 5) {
        // Sinh học phí cho kỳ 1, 2, 3, 4, 5
        $semesters = [
            [1, '2021-2022', '2021-10-15', 'Đã nộp'],
            [2, '2021-2022', '2022-03-15', 'Đã nộp'],
            [3, '2022-2023', '2022-10-15', 'Đã nộp'],
            [4, '2022-2023', '2023-03-15', null], // Kỳ 4: 95% đã nộp, 5% nợ
            [5, '2023-2024', '2023-10-15', null]  // Kỳ 5 (Hiện tại): 30% đã nộp, 40% chưa nộp, 30% nợ
        ];

        foreach ($semesters as $sem) {
            $hk = $sem[0];
            $nh = $sem[1];
            $hanNop = $sem[2];
            $trangThaiDef = $sem[3];

            $soTien = rand(80, 110) * 100000; // 8.000.000đ - 11.000.000đ

            if ($trangThaiDef !== null) {
                $trangThai = $trangThaiDef;
                $daNop = $soTien;
            } else {
                if ($hk == 4) {
                    $rand = rand(1, 100);
                    if ($rand <= 95) {
                        $trangThai = 'Đã nộp';
                        $daNop = $soTien;
                    } else {
                        $trangThai = 'Nợ';
                        $daNop = rand(3, 7) * 1000000;
                    }
                } else {
                    // Học kỳ 5 hiện tại
                    $rand = rand(1, 100);
                    if ($rand <= 30) {
                        $trangThai = 'Đã nộp';
                        $daNop = $soTien;
                    } elseif ($rand <= 70) {
                        $trangThai = 'Chưa nộp';
                        $daNop = 0;
                    } else {
                        $trangThai = 'Nợ';
                        $daNop = rand(1, 5) * 1000000;
                    }
                }
            }

            $stmtHp = $conn->prepare("INSERT IGNORE INTO hoc_phi (sinh_vien_id, hoc_ky, nam_hoc, so_tien, da_nop, han_nop, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtHp->bind_param("iissdss", $svId, $hk, $nh, $soTien, $daNop, $hanNop, $trangThai);
            if ($stmtHp->execute() && $conn->affected_rows > 0) {
                $insertedHpTableCount++;
            }
            $stmtHp->close();
        }
    }
}

$resHp = $conn->query("SELECT COUNT(*) as total FROM hoc_phi");
$finalCountHpTable = $resHp->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `hoc_phi`. Tổng số bản ghi học phí: $finalCountHpTable (Đã thêm: $insertedHpTableCount)\n\n";


// -----------------------------------------------------------------------------
// BƯỚC 7: SINH TÀI LIỆU CHIA SẺ (TAI_LIEU) VÀ TẠO FILE VẬT LÝ
// -----------------------------------------------------------------------------
echo "7. Đang kiểm tra bảng `tai_lieu`...\n";
$resTl = $conn->query("SELECT COUNT(*) as total FROM tai_lieu");
$countTl = $resTl->fetch_assoc()['total'];
echo "Số tài liệu chia sẻ hiện có: $countTl\n";

$insertedTlCount = 0;

$documentTemplates = [
    'Đề cương ôn tập giữa kỳ Toán Cao Cấp A1',
    'Tổng hợp ngữ pháp Tiếng Anh 1 và 2',
    'Slide bài giảng Cấu trúc dữ liệu và Giải thuật',
    'Tài liệu thực hành Lập trình căn bản C/C++',
    'Hướng dẫn cài đặt môi trường Lập trình hướng đối tượng Java',
    'Bộ câu hỏi trắc nghiệm Hệ điều hành có đáp án',
    'Tóm tắt kiến thức cốt lõi môn Triết học Mác Lênin',
    'Bài tập lớn thiết kế Cơ sở dữ liệu mẫu',
    'Slide tóm tắt kiến thức Mạng máy tính',
    'Hướng dẫn lập trình Web HTML CSS JS cơ bản',
    'Đề ôn thi lý thuyết An toàn thông tin QNU',
    'Bài tập thực hành phát triển ứng dụng di động Android',
    'Nguyên lý cơ bản của kỹ thuật điện tử và mạch điện',
    'Bài giảng Quản trị học đại cương',
    'Tóm tắt các chỉ số Kinh tế vĩ mô cần nhớ',
    'Bài tập Nguyên lý kế toán kèm lời giải',
    'Đề cương ôn tập Ngữ pháp tiếng Anh nâng cao',
    'Tài liệu tự học Biên dịch và Phiên dịch tiếng Anh',
    'Các phương pháp giải nhanh Giải tích 1',
    'Đại số tuyến tính - Ma trận và định thức',
    'Phương trình vi phân và các dạng bài tập điển hình'
];

$fileTypes = ['pdf', 'docx', 'zip', 'xlsx', 'pptx'];

$uploadDir = ROOT . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($countTl < 105) {
    $needed = 105 - $countTl;
    echo "Cần sinh thêm ít nhất $needed tài liệu chia sẻ...\n";

    // Lấy danh sách tất cả học phần đang có
    $allHps = [];
    $resAllHps = $conn->query("SELECT id FROM hoc_phan");
    while ($h = $resAllHps->fetch_assoc()) {
        $allHps[] = $h['id'];
    }

    for ($i = 0; $i < $needed; $i++) {
        $sv = $students[array_rand($students)];
        $svId = $sv['id'];
        
        $hpId = !empty($allHps) ? $allHps[array_rand($allHps)] : null;
        $tieuDe = $documentTemplates[array_rand($documentTemplates)] . " - Phần " . rand(1, 5);
        $moTa = "Tài liệu học tập bổ ích được đóng góp bởi sinh viên hỗ trợ học tập tốt hơn.";
        
        $ext = $fileTypes[array_rand($fileTypes)];
        $tenFile = str_replace(' ', '_', strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $tieuDe))) . '.' . $ext;
        
        // Tạo đường dẫn file giả lập
        $newFilename = time() . '_' . $svId . '_' . rand(1000, 9999) . '_' . $tenFile;
        $destFilepath = $uploadDir . $newFilename;
        
        // Tạo file vật lý rỗng để đảm bảo tính năng tải file không bị lỗi
        file_put_contents($destFilepath, "Dữ liệu giả lập tài liệu chia sẻ môn học của sinh viên QNU: " . $tieuDe);
        
        $kichThuoc = filesize($destFilepath);
        $loaiFile = strtoupper($ext);
        $luotTai = rand(0, 50);

        $stmtTl = $conn->prepare("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file, luot_tai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtTl->bind_param("iissssisi", $svId, $hpId, $tieuDe, $moTa, $tenFile, $newFilename, $kichThuoc, $loaiFile, $luotTai);
        if ($stmtTl->execute()) {
            $insertedTlCount++;
        }
        $stmtTl->close();
    }
}

$resTl = $conn->query("SELECT COUNT(*) as total FROM tai_lieu");
$finalCountTl = $resTl->fetch_assoc()['total'];
echo "-> Hoàn thành bảng `tai_lieu`. Tổng số tài liệu chia sẻ: $finalCountTl (Đã thêm: $insertedTlCount, và đã sinh file vật lý rỗng trong thư mục /uploads/)\n\n";

echo "=========================================================\n";
echo "DATABASE SEEDER ĐÃ THỰC THI THÀNH CÔNG RỰC RỠ!\n";
echo "=========================================================\n";
?>
