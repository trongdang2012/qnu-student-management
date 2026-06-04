<?php
require_once 'config/database.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$sv_id = 1; // ID của Nguyễn Phương Anh

// Lấy tất cả học phí của sinh viên
echo "=== TẤT CẢ HỌC PHÍ CỦA SINH VIÊN ===\n";
$result = $db->query("SELECT * FROM hoc_phi WHERE sinh_vien_id = $sv_id ORDER BY nam_hoc DESC, hoc_ky DESC");
$hp_list = $result->fetchAll();
foreach ($hp_list as $hp) {
    echo "Năm: {$hp['nam_hoc']}, HK: {$hp['hoc_ky']}\n";
}

// Lấy tất cả đăng ký học phần của sinh viên
echo "\n=== TẤT CẢ ĐĂNG KÝ HỌC PHẦN CỦA SINH VIÊN ===\n";
$result = $db->query("SELECT * FROM dang_ky_hp WHERE sinh_vien_id = $sv_id ORDER BY nam_hoc DESC, hoc_ky DESC");
$dk_list = $result->fetchAll();
foreach ($dk_list as $dk) {
    echo "Năm: {$dk['nam_hoc']}, HK: {$dk['hoc_ky']}, Trạng thái: {$dk['trang_thai']}\n";
}

// Check chi tiết với từng học phí
echo "\n=== CHI TIẾT ĐĂNG KÝ CHO MỖI HỌC KỲ ===\n";
foreach ($hp_list as $hp) {
    echo "\n--- Năm {$hp['nam_hoc']}, HK {$hp['hoc_ky']} ---\n";
    
    $stmt = $db->prepare('
        SELECT hp_reg.ma_hp, hp_reg.ten_hp, hp_reg.so_tin_chi 
        FROM dang_ky_hp dk
        JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
        JOIN hoc_phan hp_reg ON hp_reg.id = lhp.hoc_phan_id
        WHERE dk.sinh_vien_id = ? AND dk.nam_hoc = ? AND dk.hoc_ky = ? AND dk.trang_thai = "Đã duyệt"
        ORDER BY hp_reg.ma_hp
    ');
    $stmt->execute([$sv_id, $hp['nam_hoc'], $hp['hoc_ky']]);
    $courses = $stmt->fetchAll();
    
    if (empty($courses)) {
        echo "Không có học phần\n";
    } else {
        foreach ($courses as $course) {
            echo $course['ma_hp'] . ' - ' . $course['ten_hp'] . PHP_EOL;
        }
    }
}
?>
