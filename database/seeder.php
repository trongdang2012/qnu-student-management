<?php
/**
 * Database Seeder Script - QNU Student Management System (Tích Hợp CTDT Thực Tế)
 * Cập nhật thời gian học vụ hiện tại: Học kỳ 2, năm học 2025-2026.
 * Mật khẩu sinh viên mới: Student@123
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

echo "=========================================================\n";
echo "BẮT ĐẦU CHẠY DATABASE SEEDER TÍCH HỢP CTDT THỰC TẾ QNU\n";
echo "=========================================================\n\n";

$conn = getDB();
if (!$conn) {
    die("LỖI: Không thể kết nối cơ sở dữ liệu.\n");
}

// 1. LÀM SẠCH DỮ LIỆU CŨ (Chỉ giữ lại 5 tài khoản admin)
echo "1. Đang làm sạch dữ liệu cũ...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

$tablesToTruncate = [
    'thoi_khoa_bieu',
    'dang_ky_hp',
    'diem_hoc_tap',
    'diem_ren_luyen',
    'hoc_phi',
    'tai_lieu',
    'thong_bao_sinh_vien',
    'thong_bao',
    'sinh_vien',
    'lop_sinh_hoat',
    'nganh',
    'khoa',
    'ctdt_chi_tiet',
    'lop_hoc_phan',
    'hoc_phan'
];

foreach ($tablesToTruncate as $tbl) {
    if ($conn->query("TRUNCATE TABLE `$tbl`")) {
        echo " - Đã làm sạch bảng: $tbl\n";
    } else {
        echo " - [!] Lỗi làm sạch bảng $tbl: " . $conn->error . "\n";
    }
}

// Xóa các user không phải là admin
// 5 tài khoản admin cần giữ lại có username là: admin, phi, chi, khai, huy
$conn->query("DELETE FROM users WHERE role != 'admin' AND username NOT IN ('admin', 'phi', 'chi', 'khai', 'huy')");
echo " - Đã xóa các tài khoản sinh viên cũ trong bảng users.\n";

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "-> Hoàn thành làm sạch dữ liệu.\n\n";

// 2. KHỞI TẠO 12 KHOA VÀ 51 NGÀNH
echo "2. Đang tạo danh mục Khoa và Ngành học...\n";
$khoa_nganh = [
    'Khoa Lý luận chính trị - Luật & Quản lý nhà nước' => [
        'Luật',
        'Quản lý nhà nước'
    ],
    'Khoa Khoa học tự nhiên' => [
        'Hóa học',
        'Nông học',
        'Công nghệ thực phẩm',
        'Quản lý tài nguyên và môi trường',
        'Quản lý đất đai'
    ],
    'Khoa Khoa học xã hội & nhân văn' => [
        'Văn học',
        'Tâm lý học giáo dục',
        'Đông phương học',
        'Việt Nam học',
        'Công tác xã hội',
        'Quản trị dịch vụ du lịch và lữ hành',
        'Quản trị khách sạn'
    ],
    'Khoa Ngoại ngữ' => [
        'Ngôn ngữ Anh',
        'Ngôn ngữ Trung Quốc'
    ],
    'Khoa Giáo dục tiểu học & mầm non' => [
        'Giáo dục mầm non',
        'Giáo dục Tiểu học'
    ],
    'Khoa Công nghệ thông tin' => [
        'Kỹ thuật phần mềm',
        'Trí tuệ nhân tạo',
        'Công nghệ thông tin'
    ],
    'Khoa Giáo dục Thể chất' => [
        'Giáo dục thể chất'
    ],
    'Khoa Sư phạm' => [
        'Quản lý Giáo dục',
        'Giáo dục chính trị',
        'Sư phạm Toán học',
        'Sư phạm Tin học',
        'Sư phạm Vật lý',
        'Sư phạm Hoá học',
        'Sư phạm Sinh học',
        'Sư phạm Ngữ văn',
        'Sư phạm Lịch sử',
        'Sư phạm Địa lý',
        'Sư phạm Tiếng Anh',
        'Sư phạm Khoa học tự nhiên',
        'Sư phạm Lịch sử Địa lý'
    ],
    'Khoa Kỹ thuật & Công nghệ' => [
        'Công nghệ kỹ thuật ô tô',
        'Công nghệ kỹ thuật hoá học',
        'Kỹ thuật cơ khí động lực',
        'Kỹ thuật điện',
        'Kỹ thuật điện tử - viễn thông',
        'Kỹ thuật điều khiển và Tự động hóa',
        'Vật lý kỹ thuật',
        'Kỹ thuật xây dựng'
    ],
    'Khoa Toán & Thống kê' => [
        'Khoa học dữ liệu',
        'Toán ứng dụng'
    ],
    'Khoa Kinh tế & Kế toán' => [
        'Kinh tế',
        'Kế toán',
        'Kiểm toán'
    ],
    'Khoa Tài chính - Ngân hàng & Quản trị kinh doanh' => [
        'Quản trị kinh doanh',
        'Tài chính - Ngân hàng',
        'Logistics và Quản lý chuỗi cung ứng'
    ]
];

$khoaMap = []; // ten_khoa => id
$nganhMap = []; // ten_nganh => id

$stmtKhoa = $conn->prepare("INSERT INTO khoa (ten_khoa) VALUES (?)");
$stmtNganh = $conn->prepare("INSERT INTO nganh (ten_nganh, khoa_id) VALUES (?, ?)");

foreach ($khoa_nganh as $tenKhoa => $dsNganh) {
    $stmtKhoa->bind_param("s", $tenKhoa);
    if ($stmtKhoa->execute()) {
        $khoaId = $conn->insert_id;
        $khoaMap[$tenKhoa] = $khoaId;
        
        foreach ($dsNganh as $tenNganh) {
            $stmtNganh->bind_param("si", $tenNganh, $khoaId);
            if ($stmtNganh->execute()) {
                $nganhId = $conn->insert_id;
                $nganhMap[$tenNganh] = $nganhId;
            }
        }
    }
}
$stmtKhoa->close();
$stmtNganh->close();
echo " - Đã tạo xong 12 Khoa và 51 Ngành học.\n\n";

// 3. TẠO LỚP SINH HOẠT
echo "3. Đang tạo các lớp sinh hoạt khóa K47 (Niên khóa 2024-2028)...\n";
$lopMap = []; // ten_lop => id

$stmtLop = $conn->prepare("INSERT INTO lop_sinh_hoat (ten_lop, nganh_id) VALUES (?, ?)");

$nganhVietTat = [
    'Luật' => 'LUAT', 'Quản lý nhà nước' => 'QLNN', 'Hóa học' => 'HOAHOC', 'Nông học' => 'NONGHOC',
    'Công nghệ thực phẩm' => 'CNTP', 'Quản lý tài nguyên và môi trường' => 'QLTNMT', 'Quản lý đất đai' => 'QLDD',
    'Văn học' => 'VANHOC', 'Tâm lý học giáo dục' => 'TLHGD', 'Đông phương học' => 'DPH',
    'Việt Nam học' => 'VNH', 'Công tác xã hội' => 'CTXH', 'Quản trị dịch vụ du lịch và lữ hành' => 'QTDVDL',
    'Quản trị khách sạn' => 'QTKS', 'Ngôn ngữ Anh' => 'NNA', 'Ngôn ngữ Trung Quốc' => 'NNTQ',
    'Giáo dục mầm non' => 'GDMN', 'Giáo dục Tiểu học' => 'GDTH', 'Kỹ thuật phần mềm' => 'KTPM',
    'Trí tuệ nhân tạo' => 'TTNT', 'Công nghệ thông tin' => 'CNTT', 'Giáo dục thể chất' => 'GDTC',
    'Quản lý Giáo dục' => 'QLGD', 'Giáo dục chính trị' => 'GDCT', 'Sư phạm Toán học' => 'SPTOAN',
    'Sư phạm Tin học' => 'SPTIN', 'Sư phạm Vật lý' => 'SPVALY', 'Sư phạm Hoá học' => 'SPHOA',
    'Sư phạm Sinh học' => 'SPSINH', 'Sư phạm Ngữ văn' => 'SPVAN', 'Sư phạm Lịch sử' => 'SPSU',
    'Sư phạm Địa lý' => 'SPDIA', 'Sư phạm Tiếng Anh' => 'SPANH', 'Sư phạm Khoa học tự nhiên' => 'SPKHTN',
    'Sư phạm Lịch sử Địa lý' => 'SPSUDIA', 'Công nghệ kỹ thuật ô tô' => 'CNKTO',
    'Công nghệ kỹ thuật hoá học' => 'CNKTHH', 'Kỹ thuật cơ khí động lực' => 'KTCKDL',
    'Kỹ thuật điện' => 'KTD', 'Kỹ thuật điện tử - viễn thông' => 'KTDTVT',
    'Kỹ thuật điều khiển và Tự động hóa' => 'KTDKTDH', 'Vật lý kỹ thuật' => 'VLKT',
    'Kỹ thuật xây dựng' => 'KTXD', 'Khoa học dữ liệu' => 'KHDL', 'Toán ứng dụng' => 'TOANUD',
    'Kinh tế' => 'KINHTE', 'Kế toán' => 'KETOAN', 'Kiểm toán' => 'KIEMTOAN', 'Quản trị kinh doanh' => 'QTKD',
    'Tài chính - Ngân hàng' => 'TCNH', 'Logistics và Quản lý chuỗi cung ứng' => 'LOGISTICS'
];

foreach ($nganhMap as $tenNganh => $nganhId) {
    $vt = isset($nganhVietTat[$tenNganh]) ? $nganhVietTat[$tenNganh] : 'NGANH';
    $tenLop = $vt . " K47";
    $stmtLop->bind_param("si", $tenLop, $nganhId);
    if ($stmtLop->execute()) {
        $lopMap[$tenLop] = $conn->insert_id;
    }
}
$stmtLop->close();
echo " - Đã tạo xong 51 lớp sinh hoạt khóa K47.\n\n";

// 4. KHỞI TẠO SINH VIÊN VÀ USER
echo "4. Đang tạo dữ liệu Sinh viên và tài khoản đăng nhập...\n";
$defaultPassword = 'Student@123';
$hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

// A. 50 sinh viên KTPM K47 chính xác từ hình ảnh
$ktpm_k47_students = [
    ['ma_sv' => '4751190001', 'ho_ten' => 'Bùi Quốc Bảo', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190002', 'ho_ten' => 'Võ Nam Bằng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190003', 'ho_ten' => 'Lê Thanh Bình', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190004', 'ho_ten' => 'Bùi Long Chí', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190005', 'ho_ten' => 'Nguyễn Quốc Cương', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190006', 'ho_ten' => 'Trần Ngọc Cường', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190007', 'ho_ten' => 'Nguyễn Thị Ngọc Diễm', 'gioi_tinh' => 'Nữ'],
    ['ma_sv' => '4751190008', 'ho_ten' => 'Nguyễn Nhất Duy', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190009', 'ho_ten' => 'Trần Bảo Duy', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190010', 'ho_ten' => 'Nguyễn Quang Đại', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190011', 'ho_ten' => 'Trần Quốc Đạt', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190012', 'ho_ten' => 'Phan Trần Hoàng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190013', 'ho_ten' => 'Nguyễn Gia Huy', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190014', 'ho_ten' => 'Nguyễn Hồng Huy', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190015', 'ho_ten' => 'Phan Lê Hưng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190017', 'ho_ten' => 'Võ Minh Khải', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190018', 'ho_ten' => 'Phạm Đăng Khoa', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190019', 'ho_ten' => 'Nguyễn Nhuận Khôi', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190020', 'ho_ten' => 'Nguyễn Trung Kiên', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190021', 'ho_ten' => 'Võ Huỳnh Tuấn Kiệt', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190022', 'ho_ten' => 'Lưu Đức Lộc', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190023', 'ho_ten' => 'Đinh Văn Lưu', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190024', 'ho_ten' => 'Ngô Thanh Minh', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190025', 'ho_ten' => 'Phạm Khôi Nguyên', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190026', 'ho_ten' => 'Nguyễn Hoàng Phi', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190027', 'ho_ten' => 'Giáp Trần Phước', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190028', 'ho_ten' => 'Nguyễn Lê Hữu Phước', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190029', 'ho_ten' => 'Hồ Minh Quân', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190030', 'ho_ten' => 'Nguyễn Duy Quốc', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190031', 'ho_ten' => 'Trần Như Quỳnh', 'gioi_tinh' => 'Nữ'],
    ['ma_sv' => '4751190032', 'ho_ten' => 'Trần Xuân Sang', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190033', 'ho_ten' => 'Võ Ngọc Sanh', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190034', 'ho_ten' => 'Bùi Duy Tân', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190036', 'ho_ten' => 'Phan Văn Thông', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190037', 'ho_ten' => 'Lê Thanh Phương Thùy', 'gioi_tinh' => 'Nữ'],
    ['ma_sv' => '4751190038', 'ho_ten' => 'Nguyễn Thành Tính', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190039', 'ho_ten' => 'Đặng Văn Trọng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190040', 'ho_ten' => 'Nguyễn Phan Thanh Trọng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190041', 'ho_ten' => 'Nguyễn Trung', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190042', 'ho_ten' => 'Nguyễn Thành Trung', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190043', 'ho_ten' => 'Đào Anh Tuấn', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190044', 'ho_ten' => 'Nguyễn Xuân Tùng', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190045', 'ho_ten' => 'Võ Long Vũ', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190046', 'ho_ten' => 'Đặng Trần Gia Vỹ', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190047', 'ho_ten' => 'Nguyễn Kiều An', 'gioi_tinh' => 'Nữ'],
    ['ma_sv' => '4751190053', 'ho_ten' => 'Trịnh Hoài Nam', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190054', 'ho_ten' => 'Mai Công Nguyên', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190055', 'ho_ten' => 'Huỳnh Phạm Minh Nguyệt', 'gioi_tinh' => 'Nữ'],
    ['ma_sv' => '4751190058', 'ho_ten' => 'Nguyễn Nhân Tâm', 'gioi_tinh' => 'Nam'],
    ['ma_sv' => '4751190060', 'ho_ten' => 'Hà Phước Dũng', 'gioi_tinh' => 'Nam']
];

$diaChis = ['Quy Nhơn, Bình Định', 'Tuy Phước, Bình Định', 'An Nhơn, Bình Định', 'Phù Cát, Bình Định', 'Hoài Nhơn, Bình Định', 'Tuy Hòa, Phú Yên', 'Sông Cầu, Phú Yên', 'Quảng Ngãi', 'Gia Lai', 'Nha Trang, Khánh Hòa'];

$stmtUser = $conn->prepare("INSERT INTO users (username, password, role, email, two_factor_auth) VALUES (?, ?, 'student', ?, 0)");
$stmtSv = $conn->prepare("INSERT INTO sinh_vien (user_id, lop_sinh_hoat_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, dia_chi, email, so_dien_thoai, nien_khoa, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '2024-2028', 'Đang học')");

$lopKtpmId = $lopMap['KTPM K47'];
$sinhVienList = []; // Để theo dõi các sinh viên phục vụ đăng ký, xếp lịch học, điểm, học phí

foreach ($ktpm_k47_students as $sv) {
    $username = $sv['ma_sv'];
    $email = strtolower($username) . "@st.qnu.edu.vn";
    
    $stmtUser->bind_param("sss", $username, $hashedPassword, $email);
    if ($stmtUser->execute()) {
        $userId = $conn->insert_id;
        
        $ngaySinh = "2006-" . sprintf("%02d-%02d", rand(1, 12), rand(1, 28));
        $diaChi = $diaChis[array_rand($diaChis)];
        $sdt = "09" . rand(10000000, 99999999);
        
        $stmtSv->bind_param("iisssssss", $userId, $lopKtpmId, $sv['ma_sv'], $sv['ho_ten'], $ngaySinh, $sv['gioi_tinh'], $diaChi, $email, $sdt);
        if ($stmtSv->execute()) {
            $sinhVienList[] = [
                'id' => $conn->insert_id,
                'ma_sv' => $sv['ma_sv'],
                'ho_ten' => $sv['ho_ten'],
                'nganh_id' => $nganhMap['Kỹ thuật phần mềm'],
                'khoa_id' => $khoaMap['Khoa Công nghệ thông tin'],
                'lop_sinh_hoat_id' => $lopKtpmId,
                'ten_lop' => 'KTPM K47'
            ];
        }
    }
}
echo " - Đã tạo xong 50 sinh viên lớp KTPM K47.\n";

// B. Sinh viên cho 50 ngành khác (1 lớp/ngành, 10 sinh viên/lớp)
$hos = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương'];
$demsNam = ['Văn', 'Hữu', 'Đức', 'Quốc', 'Minh', 'Thành', 'Hoàng', 'Tuấn', 'Thế', 'Xuân', 'Gia', 'Anh'];
$tensNam = ['Hùng', 'Hải', 'Nam', 'Sơn', 'Tùng', 'Đạt', 'Lộc', 'Duy', 'Phong', 'Bảo', 'Minh', 'Khang', 'Thịnh', 'Phúc', 'An'];
$demsNu = ['Thị', 'Quỳnh', 'Mỹ', 'Ngọc', 'Thu', 'Thanh', 'Kiều', 'Trúc', 'Phương', 'Như', 'Khánh', 'Minh'];
$tensNu = ['Hoa', 'Lan', 'Mai', 'Trang', 'Vy', 'Linh', 'Anh', 'Chi', 'Hà', 'Yến', 'Hương', 'Thảo', 'Trinh', 'Dung', 'Nhi'];

$maSvIndex = 200001; // Mã sinh viên các ngành khác bắt đầu từ 4751200001

foreach ($lopMap as $tenLop => $lopId) {
    if ($tenLop === 'KTPM K47') continue; // Lớp KTPM K47 đã được tạo trước đó
    
    // Lấy thông tin nganh_id và khoa_id
    $resLopInfo = $conn->query("SELECT nganh_id FROM lop_sinh_hoat WHERE id = $lopId");
    $lopRow = $resLopInfo->fetch_assoc();
    $nganhId = $lopRow['nganh_id'];
    
    $resNganhInfo = $conn->query("SELECT khoa_id, ten_nganh FROM nganh WHERE id = $nganhId");
    $nganhRow = $resNganhInfo->fetch_assoc();
    $khoaId = $nganhRow['khoa_id'];
    $tenNganh = $nganhRow['ten_nganh'];
    
    for ($i = 1; $i <= 10; $i++) {
        $maSv = "4751" . $maSvIndex++;
        $username = $maSv;
        $email = strtolower($username) . "@st.qnu.edu.vn";
        
        $stmtUser->bind_param("sss", $username, $hashedPassword, $email);
        if ($stmtUser->execute()) {
            $userId = $conn->insert_id;
            
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
            
            $ngaySinh = "2006-" . sprintf("%02d-%02d", rand(1, 12), rand(1, 28));
            $diaChi = $diaChis[array_rand($diaChis)];
            $sdt = "09" . rand(10000000, 99999999);
            
            $stmtSv->bind_param("iisssssss", $userId, $lopId, $maSv, $hoTen, $ngaySinh, $gioiTinh, $diaChi, $email, $sdt);
            if ($stmtSv->execute()) {
                $sinhVienList[] = [
                    'id' => $conn->insert_id,
                    'ma_sv' => $maSv,
                    'ho_ten' => $hoTen,
                    'nganh_id' => $nganhId,
                    'khoa_id' => $khoaId,
                    'lop_sinh_hoat_id' => $lopId,
                    'ten_lop' => $tenLop
                ];
            }
        }
    }
}
$stmtUser->close();
$stmtSv->close();
echo " - Đã tạo xong sinh viên các lớp thuộc 50 ngành khác (Tổng số sinh viên: " . count($sinhVienList) . ").\n\n";

// 5. KHỞI TẠO MÔN HỌC (HỌC PHẦN) VÀ CTDT CHI TIẾT THEO MÃ QNU THẬT
echo "5. Đang khởi tạo danh sách môn học (Học phần) thực tế của QNU...\n";

// ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, so_tiet_ly_thuyet, so_tiet_thuc_hanh, loai_phong, ma_hp_tien_quyet
$subjectsDef = [
    // A. Khối môn học đại cương dùng chung cho QNU (tất cả các ngành học)
    ['1130299', 'Triết học Mác - Lênin', 3, 'Đại cương', 1, 40, 0, 'lon', null],
    ['1130300', 'Kinh tế chính trị Mác - Lênin', 2, 'Đại cương', 2, 27, 0, 'lon', '1130299'],
    ['1130049', 'Pháp luật đại cương', 2, 'Đại cương', 2, 27, 0, 'lon', null],
    ['1130301', 'Chủ nghĩa xã hội khoa học', 2, 'Đại cương', 3, 27, 0, 'lon', '1130300'],
    ['1130302', 'Lịch sử Đảng Cộng sản Việt Nam', 2, 'Đại cương', 4, 27, 0, 'lon', '1130301'],
    ['1130091', 'Tư tưởng Hồ Chí Minh', 2, 'Đại cương', 5, 27, 0, 'lon', '1130302'],
    ['1090061', 'Tiếng Anh 1', 3, 'Đại cương', 1, 45, 0, 'thuong', null],
    ['1090166', 'Tiếng Anh 2', 4, 'Đại cương', 2, 60, 0, 'thuong', '1090061'],
    ['1050242', 'Tin học cơ sở', 3, 'Đại cương', 1, 30, 30, 'maytinh', null],
    
    // Thể chất 1 (Học kỳ 1 tự chọn Nhóm 01)
    ['1120239', 'Giáo dục thể chất 1 (Pickle ball 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120181', 'Giáo dục thể chất 1 (Cầu lông 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120184', 'Giáo dục thể chất 1 (Võ cổ truyền Việt Nam 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120187', 'Giáo dục thể chất 1 (Võ Taekwondo 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120190', 'Giáo dục thể chất 1 (Võ Karatedo 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120172', 'Giáo dục thể chất 1 (Bóng đá 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    ['1120175', 'Giáo dục thể chất 1 (Bóng chuyền 1)', 1, 'Đại cương', 1, 4, 26, 'thechat', null],
    
    // Thể chất 2 (Học kỳ 2 tự chọn Nhóm 02)
    ['1120173', 'Giáo dục thể chất 2 (Bóng đá 2)', 1, 'Đại cương', 2, 4, 26, 'thechat', null],
    ['1120176', 'Giáo dục thể chất 2 (Bóng chuyền 2)', 1, 'Đại cương', 2, 4, 26, 'thechat', null],
    ['1120182', 'Giáo dục thể chất 2 (Cầu lông 2)', 1, 'Đại cương', 2, 4, 26, 'thechat', null],
    
    // Thể chất 3 (Học kỳ 3 tự chọn Nhóm 03)
    ['1120174', 'Giáo dục thể chất 3 (Bóng đá 3)', 1, 'Đại cương', 3, 4, 26, 'thechat', null],
    ['1120177', 'Giáo dục thể chất 3 (Bóng chuyền 3)', 1, 'Đại cương', 3, 4, 26, 'thechat', null],
    ['1120183', 'Giáo dục thể chất 3 (Cầu lông 3)', 1, 'Đại cương', 3, 4, 26, 'thechat', null],
    
    // Giáo dục quốc phòng (Học kỳ 4)
    ['1120168', 'Giáo dục quốc phòng - An ninh 1', 3, 'Đại cương', 4, 37, 0, 'lon', null],
    ['1120169', 'Giáo dục quốc phòng - An ninh 2', 2, 'Đại cương', 4, 22, 0, 'lon', null],
    ['1120170', 'Giáo dục quốc phòng - An ninh 3', 2, 'Đại cương', 4, 14, 16, 'lon', null],
    ['1120171', 'Giáo dục quốc phòng - An ninh 4', 2, 'Đại cương', 4, 4, 56, 'lon', null],
    
    // B. Các môn học chuyên ngành Giáo dục chính trị (GDCT 2025)
    ['1130221', 'Mỹ học và giáo dục thẩm mỹ', 2, 'Bắt buộc', 1, 30, 0, 'thuong', null],
    ['2010155', 'Dẫn luận ngôn ngữ và Tiếng Việt thực hành', 2, 'Bắt buộc', 1, 25, 5, 'thuong', null],
    ['1130220', 'Đạo đức học và giáo dục đạo đức', 2, 'Bắt buộc', 1, 30, 0, 'thuong', null],
    ['1100086', 'Tâm lý học', 3, 'Bắt buộc', 2, 30, 15, 'thuong', null],
    ['1130451', 'Lôgích học', 2, 'Bắt buộc', 2, 27, 6, 'thuong', null],
    ['1100038', 'Xã hội học', 2, 'Bắt buộc', 2, 20, 5, 'thuong', null],
    ['2030410', 'Giáo dục học', 4, 'Bắt buộc', 3, 36, 20, 'thuong', null],
    ['1130450', 'Pháp luật dân sự, lao động, hôn nhân và gia đình', 2, 'Bắt buộc', 3, 20, 7, 'thuong', null],
    ['1130070', 'Quản lý kinh tế', 2, 'Bắt buộc', 3, 30, 0, 'thuong', null],
    ['1130162', 'Pháp luật quốc tế', 2, 'Bắt buộc', 3, 25, 5, 'thuong', null],
    ['2010156', 'Giao tiếp sư phạm', 2, 'Bắt buộc', 4, 20, 20, 'thuong', null],
    ['2010171', 'Hoạt động trải nghiệm, hướng nghiệp ở trường phổ thông', 2, 'Bắt buộc', 4, 20, 20, 'thuong', null],
    
    // C. Các môn học chuyên ngành Kỹ thuật phần mềm (KTPM) thực tế
    // Học kỳ 1
    ['1010038', 'Đại số tuyến tính', 3, 'Bắt buộc', 1, 45, 0, 'thuong', null],
    ['1010245', 'Giải tích', 3, 'Bắt buộc', 1, 45, 0, 'thuong', null],
    ['1050074', 'Toán logic', 2, 'Bắt buộc', 1, 30, 0, 'thuong', null],
    ['1050124', 'Thực hành máy tính (lắp ráp, cài đặt, bảo trì)', 1, 'Bắt buộc', 1, 0, 30, 'maytinh', null],
    ['1050192', 'Giới thiệu ngành và hướng nghiệp', 2, 'Bắt buộc', 1, 30, 0, 'thuong', null],
    
    // Học kỳ 2
    ['1050133', 'Lập trình cơ bản', 4, 'Bắt buộc', 2, 45, 30, 'maytinh', null],
    ['1050016', 'Hệ quản trị cơ sở dữ liệu', 3, 'Bắt buộc', 2, 30, 30, 'maytinh', null],
    ['2030003', 'Kỹ năng giao tiếp', 2, 'Bắt buộc', 2, 25, 10, 'thuong', null],
    
    // Học kỳ 3
    ['1050024', 'Lập trình hướng đối tượng', 3, 'Bắt buộc', 3, 30, 30, 'maytinh', '1050133'],
    ['1050075', 'Toán rời rạc', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['1050003', 'Cấu trúc dữ liệu và giải thuật', 4, 'Bắt buộc', 3, 40, 40, 'maytinh', '1050133'],
    ['1010126', 'Xác suất thống kê', 3, 'Bắt buộc', 3, 45, 0, 'thuong', '1010245'],
    ['1050228', 'Cơ sở dữ liệu', 3, 'Bắt buộc', 3, 45, 0, 'maytinh', null],
    
    // Học kỳ 4
    ['1050261', 'Thực tập nhận thức', 1, 'Bắt buộc', 4, 0, 0, 'thuong', null],
    ['1050197', 'Mạng máy tính', 3, 'Bắt buộc', 4, 30, 30, 'maytinh', null],
    ['1050200', 'Lập trình ứng dụng Web', 3, 'Bắt buộc', 4, 30, 30, 'maytinh', '1050016'],
    ['1050021', 'Kiến trúc máy tính', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['1050194', 'Lập trình ứng dụng Desktop', 3, 'Bắt buộc', 4, 30, 30, 'maytinh', '1050133'],
    ['1050202', 'Phân tích và thiết kế hệ thống thông tin', 3, 'Bắt buộc', 4, 30, 20, 'maytinh', null],
    
    // Học kỳ 5
    ['1150422', 'Khởi nghiệp', 2, 'Bắt buộc', 5, 25, 0, 'thuong', '1130302'],
    ['1050277', 'Tiếng Anh cho CNTT', 2, 'Bắt buộc', 5, 25, 0, 'thuong', '1090166'],
    ['1050196', 'Hệ điều hành', 3, 'Bắt buộc', 5, 40, 10, 'maytinh', '1050021'],
    ['1050201', 'Công nghệ phần mềm', 3, 'Bắt buộc', 5, 33, 12, 'maytinh', null],
    ['1050264', 'Phân tích và thiết kế phần mềm', 3, 'Bắt buộc', 5, 35, 20, 'maytinh', null],
    ['1050262', 'Công nghệ Java', 3, 'Tự chọn', 5, 30, 30, 'maytinh', null],
    ['1050263', 'Công nghệ dotNET', 3, 'Tự chọn', 5, 30, 30, 'maytinh', null],
    
    // Học kỳ 6
    ['1050220', 'Trí tuệ nhân tạo', 3, 'Bắt buộc', 6, 45, 0, 'maytinh', null],
    ['1050216', 'Mẫu thiết kế phần mềm', 3, 'Bắt buộc', 6, 30, 30, 'maytinh', null],
    ['1050210', 'Phát triển phần mềm hướng đối tượng', 3, 'Bắt buộc', 6, 30, 30, 'maytinh', null],
    ['1050205', 'Đảm bảo chất lượng phần mềm', 3, 'Bắt buộc', 6, 30, 30, 'maytinh', null],
    ['1050206', 'Lập trình ứng dụng Mobile', 3, 'Bắt buộc', 6, 30, 30, 'maytinh', null],
    ['1050207', 'Quản lý dự án phần mềm', 3, 'Tự chọn', 6, 30, 30, 'maytinh', null],
    ['1050211', 'Phát triển phần mềm nguồn mở', 3, 'Tự chọn', 6, 30, 30, 'maytinh', null],
    
    // Học kỳ 7
    ['1050213', 'Một số vấn đề hiện đại của CNPM', 2, 'Bắt buộc', 7, 20, 20, 'maytinh', null],
    ['1050214', 'Đồ án công nghệ phần mềm 1', 3, 'Bắt buộc', 7, 0, 0, 'maytinh', null],
    ['1050215', 'Kiến trúc hướng dịch vụ', 3, 'Bắt buộc', 7, 33, 24, 'maytinh', null],
    ['1050265', 'Phân tích dữ liệu lớn', 3, 'Bắt buộc', 7, 30, 30, 'maytinh', null],
    ['1050267', 'Công nghệ Web', 3, 'Bắt buộc', 7, 30, 30, 'maytinh', null],
    ['1050266', 'Lập trình hệ thống nhúng', 3, 'Tự chọn', 7, 30, 30, 'maytinh', null],
    ['1050167', 'Lập trình Game', 3, 'Tự chọn', 7, 30, 30, 'maytinh', null],
    
    // Học kỳ 8
    ['1050221', 'Điện toán đám mây', 3, 'Bắt buộc', 8, 30, 30, 'maytinh', null],
    ['1050222', 'Học máy và ứng dụng', 3, 'Bắt buộc', 8, 40, 10, 'maytinh', null],
    ['1050219', 'Đồ án công nghệ phần mềm 2', 4, 'Bắt buộc', 8, 0, 0, 'maytinh', null],
    ['1050268', 'Lập trình mạng', 3, 'Tự chọn', 8, 30, 30, 'maytinh', null],
    ['1050269', 'Lập trình trí tuệ nhân tạo', 3, 'Tự chọn', 8, 30, 30, 'maytinh', null],
    ['1050270', 'Khai phá dữ liệu', 3, 'Tự chọn', 8, 35, 20, 'maytinh', null],
    ['1050271', 'An toàn và bảo mật hệ thống thông tin', 3, 'Tự chọn', 8, 30, 20, 'maytinh', null],
    
    // Học kỳ 9
    ['1050272', 'Thực tập tốt nghiệp', 3, 'Bắt buộc', 9, 0, 0, 'thuong', '1050261'],
    ['1050331', 'Đồ án tốt nghiệp', 8, 'Bắt buộc', 9, 0, 0, 'maytinh', null],
    
    // D. Môn chuyên ngành cho các Khoa khác (mô phỏng theo khoa)
    ['1130401', 'Luật Hiến pháp Việt Nam', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['1130402', 'Luật Dân sự 1', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['1210301', 'Sinh học đại cương', 3, 'Bắt buộc', 3, 30, 30, 'lab', null],
    ['1210302', 'Hóa học đại cương 2', 3, 'Bắt buộc', 4, 30, 30, 'lab', null],
    ['1100301', 'Cơ sở văn hóa Việt Nam', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['1100302', 'Xã hội học đại cương', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['1090301', 'Nghe nói Tiếng Anh nâng cao 1', 3, 'Bắt buộc', 3, 15, 60, 'thuong', null],
    ['1090302', 'Ngữ pháp Tiếng Anh nâng cao', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['2030301', 'Tâm lý học trẻ em', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['2030302', 'Phương pháp dạy học Tiếng Việt ở tiểu học', 3, 'Bắt buộc', 4, 30, 30, 'thuong', null],
    ['1060301', 'Vẽ kỹ thuật xây dựng', 3, 'Bắt buộc', 3, 30, 30, 'lab', null],
    ['1060302', 'Kỹ thuật điện đại cương', 3, 'Bắt buộc', 4, 30, 30, 'lab', null],
    ['1020301', 'Đại số tuyến tính nâng cao', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['1020302', 'Xác suất thống kê toán', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['1080301', 'Nguyên lý kế toán doanh nghiệp', 3, 'Bắt buộc', 3, 30, 30, 'thuong', null],
    ['1080302', 'Kinh tế học vi mô', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['1080401', 'Quản trị tài chính', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['1080402', 'Quản trị học đại cương', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null],
    ['2010301', 'Tâm lý học sư phạm nâng cao', 3, 'Bắt buộc', 3, 45, 0, 'thuong', null],
    ['2010302', 'Giáo dục học phổ thông', 3, 'Bắt buộc', 4, 45, 0, 'thuong', null]
];

$hpInserted = []; // ma_hp => database_id
$hpDetails = [];  // database_id => details_array

$stmtHp = $conn->prepare("INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, so_tiet_ly_thuyet, so_tiet_thuc_hanh, khoa_phu_trach, mo_ta, trang_thai_hoat_dong, ma_hp_tien_quyet) VALUES (?, ?, ?, ?, ?, '2024-2028', ?, ?, ?, ?, 1, ?)");

// Ánh xạ khoa phụ trách
$prefixToKhoa = [
    '113' => 'Khoa Lý luận chính trị - Luật & Quản lý nhà nước',
    '109' => 'Khoa Ngoại ngữ',
    '105' => 'Khoa Công nghệ thông tin',
    '112' => 'Khoa Giáo dục Thể chất',
    '115' => 'Khoa Kinh tế & Kế toán',
    '201' => 'Khoa Sư phạm',
    '121' => 'Khoa Khoa học tự nhiên',
    '110' => 'Khoa Khoa học xã hội & nhân văn',
    '203' => 'Khoa Giáo dục tiểu học & mầm non',
    '106' => 'Khoa Kỹ thuật & Công nghệ',
    '102' => 'Khoa Toán & Thống kê',
    '108' => 'Khoa Kinh tế & Kế toán'
];

foreach ($subjectsDef as $sub) {
    $maHp = $sub[0];
    $tenHp = $sub[1];
    $soTc = $sub[2];
    $loai = $sub[3];
    $hk = $sub[4];
    $lt = $sub[5];
    $th = $sub[6];
    $loaiPhong = $sub[7];
    $prereq = isset($sub[8]) ? $sub[8] : null;
    
    $prefix = substr($maHp, 0, 3);
    $khoaName = isset($prefixToKhoa[$prefix]) ? $prefixToKhoa[$prefix] : 'Khoa Sư phạm';
    if ($maHp === '1080401' || $maHp === '1080402') {
        $khoaName = 'Khoa Tài chính - Ngân hàng & Quản trị kinh doanh';
    }
    
    $mota = "Môn học học phần $tenHp.";
    
    $stmtHp->bind_param("ssisiiisss", $maHp, $tenHp, $soTc, $loai, $hk, $lt, $th, $khoaName, $mota, $prereq);
    if ($stmtHp->execute()) {
        $hpId = $conn->insert_id;
        $hpInserted[$maHp] = $hpId;
        $hpDetails[$hpId] = [
            'id' => $hpId,
            'ma_hp' => $maHp,
            'ten_hp' => $tenHp,
            'so_tin_chi' => $soTc,
            'loai' => $loai,
            'hoc_ky' => $hk,
            'loai_phong' => $loaiPhong,
            'khoa_phu_trach' => $khoaName
        ];
    }
}
$stmtHp->close();
echo " - Đã tạo xong danh sách môn học.\n";

// Xây dựng CTDT cho từng ngành học dựa theo quy tắc phân chia khoa/ngành học sinh hoạt
$stmtCtdt = $conn->prepare("INSERT INTO ctdt_chi_tiet (nganh_id, hoc_phan_id, hoc_ky) VALUES (?, ?, ?)");

$resNganhInDb = $conn->query("SELECT id, ten_nganh, khoa_id FROM nganh");
$allNganhs = $resNganhInDb->fetch_all(MYSQLI_ASSOC);

$resKhoaInDb = $conn->query("SELECT id, ten_khoa FROM khoa");
$khoaDbMap = [];
while ($kRow = $resKhoaInDb->fetch_assoc()) {
    $khoaDbMap[$kRow['id']] = $kRow['ten_khoa'];
}

foreach ($allNganhs as $ng) {
    $nganhId = $ng['id'];
    $tenNganh = $ng['ten_nganh'];
    $khoaNameOfNganh = $khoaDbMap[$ng['khoa_id']];
    
    if ($tenNganh === 'Kỹ thuật phần mềm') {
        // Gán toàn bộ môn học KTPM vào đúng các học kỳ thiết kế
        $ktpmAllMaHps = [
            // HK1
            '1130299', '1090061', '1010038', '1010245', '1050074', '1050124', '1050192',
            '1120239', '1120181', '1120184', '1120187', '1120190', '1120172', '1120175',
            // HK2
            '1130300', '1130049', '1090166', '1050133', '1050016', '2030003',
            '1120173', '1120176', '1120182', // PE 2 Group 02
            // HK3
            '1130301', '1050024', '1050075', '1050003', '1010126', '1050228',
            '1120174', '1120177', '1120183', // PE 3 Group 03
            // HK4
            '1130302', '1120168', '1120169', '1120170', '1120171', '1050261', '1050197', '1050200', '1050021', '1050194', '1050202',
            // HK5
            '1130091', '1150422', '1050277', '1050196', '1050201', '1050264', '1050262', '1050263',
            // HK6
            '1050220', '1050216', '1050210', '1050205', '1050206', '1050207', '1050211',
            // HK7
            '1050213', '1050214', '1050215', '1050265', '1050267', '1050266', '1050167',
            // HK8
            '1050221', '1050222', '1050219', '1050268', '1050269', '1050270', '1050271',
            // HK9
            '1050272', '1050331'
        ];
        foreach ($ktpmAllMaHps as $maHp) {
            if (isset($hpInserted[$maHp])) {
                $hpId = $hpInserted[$maHp];
                $hk = $hpDetails[$hpId]['hoc_ky'];
                $stmtCtdt->bind_param("iii", $nganhId, $hpId, $hk);
                $stmtCtdt->execute();
            }
        }
    } else {
        // Gán 9 môn đại cương dùng chung cho các ngành khác
        $daiCuongMaHps = [
            '1130299', '1130300', '1130049', '1130301', '1130302', '1130091',
            '1090061', '1090166', '1050242', '1120172', '1120173', '1120174',
            '1120168', '1120169', '1120170', '1120171'
        ];
        foreach ($daiCuongMaHps as $maHp) {
            if (isset($hpInserted[$maHp])) {
                $hpId = $hpInserted[$maHp];
                $hk = $hpDetails[$hpId]['hoc_ky'];
                $stmtCtdt->bind_param("iii", $nganhId, $hpId, $hk);
                $stmtCtdt->execute();
            }
        }
        
        // Gán các môn học chuyên ngành
        if ($tenNganh === 'Giáo dục chính trị') {
            $gdctChuyenNganhMaHps = [
                '1130221', '2010155', '1130220', '1100086', '1130451', '1100038',
                '2030410', '1130450', '1130070', '1130162', '2010156', '2010171'
            ];
            foreach ($gdctChuyenNganhMaHps as $maHp) {
                if (isset($hpInserted[$maHp])) {
                    $hpId = $hpInserted[$maHp];
                    $hk = $hpDetails[$hpId]['hoc_ky'];
                    $stmtCtdt->bind_param("iii", $nganhId, $hpId, $hk);
                    $stmtCtdt->execute();
                }
            }
        } elseif ($tenNganh === 'Công nghệ thông tin' || $tenNganh === 'Trí tuệ nhân tạo') {
            $cnttChuyenNganhMaHps = [
                '1050133', '1050016', '1050024', '1050075', '1050003', '1010126', '1050228',
                '1050197', '1050200', '1050021', '1050194', '1050202', '1050196', '1050201'
            ];
            foreach ($cnttChuyenNganhMaHps as $maHp) {
                if (isset($hpInserted[$maHp])) {
                    $hpId = $hpInserted[$maHp];
                    $hk = $hpDetails[$hpId]['hoc_ky'];
                    $stmtCtdt->bind_param("iii", $nganhId, $hpId, $hk);
                    $stmtCtdt->execute();
                }
            }
        } else {
            foreach ($hpDetails as $hpId => $detail) {
                $maHp = $detail['ma_hp'];
                $khoaPhuTrach = $detail['khoa_phu_trach'];
                
                $prefix = substr($maHp, 0, 3);
                if (in_array($maHp, $daiCuongMaHps) || $prefix === '105' || $maHp === '1130221' || $maHp === '2010155' || $maHp === '1130220' || $maHp === '1100086' || $maHp === '1130451' || $maHp === '1100038' || $maHp === '2030410' || $maHp === '1130450' || $maHp === '1130070' || $maHp === '1130162' || $maHp === '2010156' || $maHp === '2010171') {
                    continue;
                }
                
                if ($khoaPhuTrach === $khoaNameOfNganh) {
                    $hk = $detail['hoc_ky'];
                    $stmtCtdt->bind_param("iii", $nganhId, $hpId, $hk);
                    $stmtCtdt->execute();
                }
            }
        }
    }
}
$stmtCtdt->close();
echo " - Đã tạo xong Chương trình đào tạo chi tiết cho tất cả 51 ngành.\n\n";

// 6. TẠO LỚP HỌC PHẦN (LOP_HOC_PHAN)
echo "6. Đang tạo các Lớp học phần cho học kỳ hiện tại và các kỳ trước...\n";

$giangViens = [
    'TS. Nguyễn Văn Hùng', 'ThS. Trần Thị Lan', 'TS. Lê Văn Minh', 'ThS. Hoàng Văn E', 
    'ThS. Phạm Thị Hoa', 'TS. Hoàng Quang Trung', 'ThS. Nguyễn Thị F', 'TS. Trần Văn G', 
    'TS. Lê Văn H', 'TS. Lý Văn I', 'ThS. Nguyễn Văn J', 'Cô Đỗ Thị K', 'Thầy Vũ Văn L',
    'TS. Phạm Minh Tuấn', 'TS. Đặng Thanh Sơn', 'ThS. Lê Hoàng Nam', 'ThS. Nguyễn Thị Mai',
    'TS. Bùi Quốc Bảo', 'ThS. Huỳnh Tấn Đạt', 'TS. Phan Anh Tuấn', 'TS. Vũ Thị Quỳnh'
];

$phongs = [
    'thuong' => ['A1.101', 'A1.202', 'A1.303', 'A2.102', 'A2.204', 'A3.101', 'A3.203'],
    'maytinh' => ['A4.PM01', 'A4.PM02', 'A5.PM01', 'A5.PM02'],
    'lab' => ['A7.LAB01', 'A7.LAB02', 'A7.LAB03'],
    'lon' => ['A8.101', 'A8.102', 'A8.201'],
    'thechat' => ['Nhà thi đấu đa năng']
];

$lopHocPhanList = [];

$stmtLhp = $conn->prepare("INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, ngay_bat_dau_dk, ngay_ket_thuc_dk, trang_thai_mo_lop) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");

foreach ($hpDetails as $hpId => $hp) {
    $maHp = $hp['ma_hp'];
    $hkCtdt = $hp['hoc_ky'];
    
    // Phân bổ thời gian thực tế
    $ngayBdDk = null;
    $ngayKtDk = null;
    $trangThaiMoLop = 'Đang mở';
    
    if ($hkCtdt == 1) {
        $hkThucTe = 1;
        $namHocThucTe = '2024-2025';
        $ngayBd = '2024-09-05';
        $ngayKt = '2025-01-15';
        $ngayBdDk = '2024-08-15 00:00:00';
        $ngayKtDk = '2024-08-30 23:59:59';
        $trangThaiMoLop = 'Đã đóng';
    } elseif ($hkCtdt == 2) {
        $hkThucTe = 2;
        $namHocThucTe = '2024-2025';
        $ngayBd = '2025-01-15';
        $ngayKt = '2025-05-30';
        $ngayBdDk = '2025-01-05 00:00:00';
        $ngayKtDk = '2025-01-20 23:59:59';
        $trangThaiMoLop = 'Đã đóng';
    } elseif ($hkCtdt == 3) {
        $hkThucTe = 1;
        $namHocThucTe = '2025-2026';
        $ngayBd = '2025-09-05';
        $ngayKt = '2026-01-15';
        $ngayBdDk = '2025-08-15 00:00:00';
        $ngayKtDk = '2025-08-30 23:59:59';
        $trangThaiMoLop = 'Đã đóng';
    } else { // Kỳ 4 (Kỳ hiện tại)
        $hkThucTe = 2;
        $namHocThucTe = '2025-2026';
        $ngayBd = '2026-01-15';
        $ngayKt = '2026-05-30';
        $ngayBdDk = '2026-06-01 00:00:00';
        $ngayKtDk = '2026-06-30 23:59:59'; // Bao phủ thời điểm hiện tại 2026-06-18
        $trangThaiMoLop = 'Đang mở';
    }
    
    $maLopHp = $maHp . "-L01";
    $giangVien = $giangViens[array_rand($giangViens)];
    $sisoMax = 80;
    
    $stmtLhp->bind_param("sisssisssss", $maLopHp, $hpId, $giangVien, $hkThucTe, $namHocThucTe, $sisoMax, $ngayBd, $ngayKt, $ngayBdDk, $ngayKtDk, $trangThaiMoLop);
    if ($stmtLhp->execute()) {
        $lhpId = $conn->insert_id;
        
        $loaiPhong = $hp['loai_phong'];
        $dsPhongs = $phongs[$loaiPhong];
        $phongHoc = $dsPhongs[array_rand($dsPhongs)];
        
        $lopHocPhanList[$lhpId] = [
            'id' => $lhpId,
            'ma_lop_hp' => $maLopHp,
            'hoc_phan_id' => $hpId,
            'ma_hp' => $maHp,
            'ten_hp' => $hp['ten_hp'],
            'giang_vien' => $giangVien,
            'hoc_ky' => $hkThucTe,
            'nam_hoc' => $namHocThucTe,
            'phong_hoc' => $phongHoc,
            'hk_ctdt' => $hkCtdt,
            'ngay_bat_dau' => $ngayBd,
            'ngay_ket_thuc' => $ngayKt
        ];
    }
}
$stmtLhp->close();
echo " - Đã tạo xong " . count($lopHocPhanList) . " Lớp học phần.\n\n";

// 7. SINH THỜI KHÓA BIỂU, ĐĂNG KÝ HỌC PHẦN, ĐIỂM, RÈN LUYỆN, HỌC PHÍ CHO TỪNG SINH VIÊN
echo "7. Đang sinh dữ liệu hoạt động (Thời khóa biểu, Đăng ký học phần, Điểm số, Rèn luyện, Học phí)... \n";

$stmtDk = $conn->prepare("INSERT INTO dang_ky_hp (sinh_vien_id, lop_hoc_phan_id, hoc_ky, nam_hoc, trang_thai) VALUES (?, ?, ?, ?, 'Đã duyệt')");
$stmtDiem = $conn->prepare("INSERT INTO diem_hoc_tap (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, diem_cc, diem_gk, diem_ck, diem_tong, diem_chu, diem_he4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtTkb = $conn->prepare("INSERT INTO thoi_khoa_bieu (sinh_vien_id, hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, lop_hoc_phan_id, ngay_bat_dau, ngay_ket_thuc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmtDrl = $conn->prepare("INSERT INTO diem_ren_luyen (sinh_vien_id, hoc_ky, nam_hoc, diem, xep_loai, ghi_chu) VALUES (?, ?, ?, ?, ?, 'Hoàn thành tốt các hoạt động phong trào và học tập.')");
$stmtHpTable = $conn->prepare("INSERT INTO hoc_phi (sinh_vien_id, hoc_ky, nam_hoc, so_tien, da_nop, han_nop, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?)");

$ctdtByNganh = [];
$resCtdtList = $conn->query("SELECT nganh_id, hoc_phan_id, hoc_ky FROM ctdt_chi_tiet");
while ($cRow = $resCtdtList->fetch_assoc()) {
    $ctdtByNganh[$cRow['nganh_id']][] = [
        'hp_id' => $cRow['hoc_phan_id'],
        'hk_ctdt' => $cRow['hoc_ky']
    ];
}

$lhpByHp = [];
foreach ($lopHocPhanList as $lhpId => $lhp) {
    $lhpByHp[$lhp['hoc_phan_id']] = $lhp;
}

$countTkb = 0;
$countDk = 0;
$countDiem = 0;

foreach ($sinhVienList as $sv) {
    $svId = $sv['id'];
    $nganhId = $sv['nganh_id'];
    
    $myCtdt = isset($ctdtByNganh[$nganhId]) ? $ctdtByNganh[$nganhId] : [];
    
    // Lọc môn học đối với khóa K47: chỉ đăng ký học kỳ 1, 2, 3, 4
    $filteredCtdt = [];
    $gdtc1_candidates = [];
    $gdtc2_candidates = [];
    $gdtc3_candidates = [];
    
    foreach ($myCtdt as $item) {
        $hpId = $item['hp_id'];
        $hkCtdt = $item['hk_ctdt'];
        
        if (!isset($hpDetails[$hpId])) continue;
        $hp = $hpDetails[$hpId];
        $maHp = $hp['ma_hp'];
        
        // Không đăng ký các học kỳ tương lai (5-9)
        if ($hkCtdt > 4) {
            continue;
        }
        
        // Giáo dục thể chất ở học kỳ 1, 2, 3
        if (strpos($maHp, '112') === 0 && $hkCtdt < 4) {
            if ($hkCtdt == 1) {
                $gdtc1_candidates[] = $item;
            } elseif ($hkCtdt == 2) {
                $gdtc2_candidates[] = $item;
            } elseif ($hkCtdt == 3) {
                $gdtc3_candidates[] = $item;
            }
        } else {
            // Các môn học bắt buộc/đại cương khác bao gồm cả Giáo dục quốc phòng ở HK4
            $filteredCtdt[] = $item;
        }
    }
    
    // Mỗi học kỳ 1, 2, 3 chỉ chọn đúng 1 học phần Giáo dục thể chất ngẫu nhiên để học
    if (!empty($gdtc1_candidates)) {
        $filteredCtdt[] = $gdtc1_candidates[array_rand($gdtc1_candidates)];
    }
    if (!empty($gdtc2_candidates)) {
        $filteredCtdt[] = $gdtc2_candidates[array_rand($gdtc2_candidates)];
    }
    if (!empty($gdtc3_candidates)) {
        $filteredCtdt[] = $gdtc3_candidates[array_rand($gdtc3_candidates)];
    }
    
    foreach ($filteredCtdt as $item) {
        $hpId = $item['hp_id'];
        $hkCtdt = $item['hk_ctdt'];
        
        if (!isset($lhpByHp[$hpId])) continue;
        $lhp = $lhpByHp[$hpId];
        $lhpId = $lhp['id'];
        
        $hkThucTe = $lhp['hoc_ky'];
        $namHocThucTe = $lhp['nam_hoc'];
        
        // 1. Đăng ký học phần
        // Kiểm tra sĩ số hiện tại của lớp trước khi chèn đăng ký giả lập
        $resLhp = $conn->query("SELECT si_so_hien_tai, si_so_toi_da FROM lop_hoc_phan WHERE id = $lhpId");
        $lhpRow = $resLhp->fetch_assoc();
        $curEnrolled = $lhpRow ? (int)$lhpRow['si_so_hien_tai'] : 0;
        $maxEnrolled = $lhpRow ? (int)$lhpRow['si_so_toi_da'] : 80;
        
        if ($curEnrolled < $maxEnrolled) {
            $hkThucTeStr = (string)$hkThucTe;
            $stmtDk->bind_param("iiss", $svId, $lhpId, $hkThucTeStr, $namHocThucTe);
            if ($stmtDk->execute()) {
                $countDk++;
                $conn->query("UPDATE lop_hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = $lhpId");
            }
            
            // 2. Thời khóa biểu (Thứ và tiết cố định theo từng lớp sinh hoạt để đồng bộ)
            $hashVal = crc32($sv['lop_sinh_hoat_id'] . '_' . $hpId);
            $thu = ($hashVal % 6) + 2; 
            
            // Phân bổ theo ca học của QNU: sáng (Ca 1: tiết 1-2, Ca 2: tiết 3-5), chiều (Ca 3: tiết 6-7, Ca 4: tiết 8-10)
            $slot = ($hashVal >> 2) % 4;
            if ($slot == 0) {
                $tietBd = 1;
                $soTiet = 2;
            } elseif ($slot == 1) {
                $tietBd = 3;
                $soTiet = 3;
            } elseif ($slot == 2) {
                $tietBd = 6;
                $soTiet = 2;
            } else {
                $tietBd = 8;
                $soTiet = 3;
            }
            
            $phongHoc = $lhp['phong_hoc'];
            $giangVien = $lhp['giang_vien'];
            $ngayBd = $lhp['ngay_bat_dau'];
            $ngayKt = $lhp['ngay_ket_thuc'];
            
            $stmtTkb->bind_param("iiiiisssisss", $svId, $hpId, $thu, $tietBd, $soTiet, $phongHoc, $giangVien, $hkThucTe, $namHocThucTe, $lhpId, $ngayBd, $ngayKt);
            if ($stmtTkb->execute()) {
                $countTkb++;
            }
            
            // 3. Điểm học tập cho kỳ trước (< 4)
            if ($hkCtdt < 4) {
                $diemCc = rand(80, 100) / 10.0;
                $diemGk = rand(60, 95) / 10.0;
                $diemCk = rand(50, 98) / 10.0;
                
                if (rand(1, 100) <= 2) { // Trượt môn (tỷ lệ 2%)
                    $diemCc = rand(70, 90) / 10.0;
                    $diemGk = rand(30, 50) / 10.0;
                    $diemCk = rand(20, 39) / 10.0;
                }
                
                $diemTong = round($diemCc * 0.1 + $diemGk * 0.3 + $diemCk * 0.6, 2);
                
                $diemHe4 = 0.0;
                $diemChu = 'F';
                if ($diemTong >= 9.0) { $diemChu = 'A+'; $diemHe4 = 4.0; }
                elseif ($diemTong >= 8.0) { $diemChu = 'A';  $diemHe4 = 3.5; }
                elseif ($diemTong >= 7.0) { $diemChu = 'B+'; $diemHe4 = 3.0; }
                elseif ($diemTong >= 6.0) { $diemChu = 'B';  $diemHe4 = 2.5; }
                elseif ($diemTong >= 5.0) { $diemChu = 'C';  $diemHe4 = 2.0; }
                elseif ($diemTong >= 4.0) { $diemChu = 'D';  $diemHe4 = 1.5; }
                
                $stmtDiem->bind_param("iiiiddddsd", $svId, $hpId, $hkThucTe, $namHocThucTe, $diemCc, $diemGk, $diemCk, $diemTong, $diemChu, $diemHe4);
                if ($stmtDiem->execute()) {
                    $countDiem++;
                }
            }
        }
    }
    
    // 4. Điểm rèn luyện các kỳ trước
    $drlConfigs = [
        [1, '2024-2025'],
        [2, '2024-2025'],
        [1, '2025-2026']
    ];
    foreach ($drlConfigs as $conf) {
        $drlDiem = rand(72, 95);
        $xepLoai = 'Khá';
        if ($drlDiem >= 90) $xepLoai = 'Xuất sắc';
        elseif ($drlDiem >= 80) $xepLoai = 'Tốt';
        elseif ($drlDiem >= 65) $xepLoai = 'Khá';
        else $xepLoai = 'Trung bình';
        
        $stmtDrl->bind_param("iiiss", $svId, $conf[0], $conf[1], $drlDiem, $xepLoai);
        $stmtDrl->execute();
    }
    
    // 5. Học phí lịch sử và hiện tại
    $hpConfigs = [
        [1, '2024-2025', '2024-10-15', 'Đã nộp'],
        [2, '2024-2025', '2025-03-15', 'Đã nộp'],
        [1, '2025-2026', '2025-10-15', 'Đã nộp']
    ];
    foreach ($hpConfigs as $conf) {
        $soTien = rand(8800000, 11500000);
        $daNop = $soTien;
        $stmtHpTable->bind_param("iisddss", $svId, $conf[0], $conf[1], $soTien, $daNop, $conf[2], $conf[3]);
        $stmtHpTable->execute();
    }
    
    // Kỳ này (Kỳ 2 năm 2025-2026)
    $soTienKyNay = rand(9000000, 12000000);
    $hanNopKyNay = '2026-03-15';
    $randState = rand(1, 100);
    if ($randState <= 45) {
        $trangThaiKyNay = 'Đã nộp';
        $daNopKyNay = $soTienKyNay;
    } elseif ($randState <= 85) {
        $trangThaiKyNay = 'Chưa nộp';
        $daNopKyNay = 0;
    } else {
        $trangThaiKyNay = 'Nợ';
        $daNopKyNay = rand(3000000, 6000000);
    }
    $kyHienTai = 2;
    $namHocHienTai = '2025-2026';
    $stmtHpTable->bind_param("iisddss", $svId, $kyHienTai, $namHocHienTai, $soTienKyNay, $daNopKyNay, $hanNopKyNay, $trangThaiKyNay);
    $stmtHpTable->execute();
}

$stmtDk->close();
$stmtDiem->close();
$stmtTkb->close();
$stmtDrl->close();
$stmtHpTable->close();

echo " - Đã tạo xong $countDk đăng ký học phần.\n";
echo " - Đã xếp xong $countTkb bản ghi thời khóa biểu sinh viên.\n";
echo " - Đã ghi nhận xong $countDiem điểm học tập.\n\n";

// 8. TÀI LIỆU CHIA SẺ MẪU
echo "8. Đang tạo các tài liệu học tập chia sẻ mẫu...\n";
$documentTemplates = [
    'Đề thi thử học kỳ', 'Tóm tắt lý thuyết cốt lõi', 'Bài tập lớn tham khảo', 
    'Sơ đồ tư duy môn học', 'Hướng dẫn thực hành chi tiết', 'Tài liệu ôn tập cuối kỳ'
];
$fileTypes = ['pdf', 'docx', 'pptx', 'zip'];
$uploadDir = ROOT . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$stmtTl = $conn->prepare("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file, luot_tai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 60; $i++) {
    $svRandom = $sinhVienList[array_rand($sinhVienList)];
    $svId = $svRandom['id'];
    
    $myCtdt = $ctdtByNganh[$svRandom['nganh_id']];
    $hpRandom = $myCtdt[array_rand($myCtdt)];
    $hpId = $hpRandom['hp_id'];
    
    $tenMoc = $hpDetails[$hpId]['ten_hp'];
    $tieuDe = $documentTemplates[array_rand($documentTemplates)] . " - Môn " . $tenMoc;
    $moTa = "Tài liệu học tập tự ôn tập môn học $tenMoc.";
    
    $ext = $fileTypes[array_rand($fileTypes)];
    $tenFile = str_replace(' ', '_', strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $tieuDe))) . '.' . $ext;
    $newFilename = time() . '_' . $svId . '_' . rand(1000, 9999) . '_' . $tenFile;
    $destFilepath = $uploadDir . $newFilename;
    
    file_put_contents($destFilepath, "Nội dung tài liệu học tập chia sẻ QNU SMS: " . $tieuDe);
    
    $kichThuoc = filesize($destFilepath);
    $loaiFile = strtoupper($ext);
    $luotTai = rand(0, 80);

    $stmtTl->bind_param("iissssisi", $svId, $hpId, $tieuDe, $moTa, $tenFile, $newFilename, $kichThuoc, $loaiFile, $luotTai);
    $stmtTl->execute();
}
$stmtTl->close();
echo " - Đã tạo xong 60 tài liệu chia sẻ mẫu.\n\n";

// 9. THÔNG BÁO MẪU
echo "9. Đang tạo các thông báo mẫu từ Ban giám hiệu/Admin...\n";
$thongBaos = [
    [
        'tieu_de' => 'Thông báo về việc đăng ký học phần học kỳ 2 năm học 2025-2026',
        'noi_dung' => 'Nhà trường thông báo thời gian đăng ký học phần chính thức Học kỳ 2 năm học 2025-2026 bắt đầu từ ngày 15/12/2025 đến ngày 10/01/2026. Sinh viên kiểm tra kỹ thời khóa biểu và đăng ký đúng hạn.',
        'loai' => 'warning'
    ],
    [
        'tieu_de' => 'Thông báo kế hoạch nghỉ Tết Nguyên Đán năm 2026',
        'noi_dung' => 'Ban Giám hiệu thông báo thời gian nghỉ Tết Nguyên Đán năm 2026 dành cho sinh viên toàn trường kéo dài 2 tuần từ ngày 09/02/2026 đến hết ngày 22/02/2026. Chúc các em sinh viên và gia đình có một năm mới an khang thịnh vượng.',
        'loai' => 'info'
    ],
    [
        'tieu_de' => 'Chúc mừng năm học mới và kế hoạch phát học bổng khuyến khích học tập',
        'noi_dung' => 'Nhà trường đã hoàn tất việc xét duyệt học bổng khuyến khích học tập học kỳ 1 năm học 2025-2026. Kết quả chi tiết đã được cập nhật vào tài khoản cá nhân của từng sinh viên được thụ hưởng. Số tiền sẽ được chuyển trực tiếp vào tài khoản ngân hàng của sinh viên trước ngày 25/01/2026.',
        'loai' => 'success'
    ]
];

$stmtTb = $conn->prepare("INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui_id, ngay_tao) VALUES (?, ?, ?, ?, NOW())");
$stmtTbSv = $conn->prepare("INSERT INTO thong_bao_sinh_vien (thong_bao_id, sinh_vien_id, da_doc) VALUES (?, ?, 0)");

$resAdmin = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
$adminRow = $resAdmin->fetch_assoc();
$adminUserId = $adminRow['id'];

foreach ($thongBaos as $tb) {
    $stmtTb->bind_param("sssi", $tb['tieu_de'], $tb['noi_dung'], $tb['loai'], $adminUserId);
    if ($stmtTb->execute()) {
        $tbId = $conn->insert_id;
        
        foreach ($sinhVienList as $sv) {
            $svId = $sv['id'];
            $stmtTbSv->bind_param("ii", $tbId, $svId);
            $stmtTbSv->execute();
        }
    }
}
$stmtTb->close();
$stmtTbSv->close();
echo " - Đã gửi các thông báo đến toàn bộ sinh viên.\n\n";

echo "=========================================================\n";
echo "DATABASE SEEDER TÍCH HỢP CTDT THỰC TẾ ĐÃ THÀNH CÔNG RỰC RỠ!\n";
echo "=========================================================\n";
?>
