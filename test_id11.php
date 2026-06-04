<?php
require_once 'config/database.php';

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}

// Giả lập StudentModel::getTuitionFees() cho sinh viên ID 11
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$studentId = 11; // Dương Phương Lan

$sql = "SELECT hp.* FROM hoc_phi hp WHERE hp.sinh_vien_id = :sid ORDER BY hp.nam_hoc DESC, hp.hoc_ky DESC";
$stmt = $db->prepare($sql);
$stmt->execute(['sid' => $studentId]);
$hp_list = $stmt->fetchAll();

echo "<h2>Test getTuitionFees() - Sinh viên ID 11 (Dương Phương Lan)</h2>";
echo "Số bản ghi học phí: " . count($hp_list) . "<br><br>";

foreach ($hp_list as $hp) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Năm " . $hp['nam_hoc'] . ", HK " . $hp['hoc_ky'] . "</strong><br>";
    echo "Học phí: " . formatMoney($hp['so_tien']) . "<br>";
    
    // Lấy registered_courses
    $stmt2 = $db->prepare("
        SELECT hp_reg.ma_hp, hp_reg.ten_hp, hp_reg.so_tin_chi
        FROM dang_ky_hp dk
        JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
        JOIN hoc_phan hp_reg ON hp_reg.id = lhp.hoc_phan_id
        WHERE dk.sinh_vien_id = :sid AND dk.nam_hoc = :nh AND dk.hoc_ky = :hk AND dk.trang_thai = 'Đã duyệt'
        ORDER BY hp_reg.ma_hp
    ");
    $stmt2->execute(['sid' => $studentId, 'nh' => $hp['nam_hoc'], 'hk' => $hp['hoc_ky']]);
    $registered_courses = $stmt2->fetchAll();
    
    if (!empty($registered_courses)) {
        echo "<strong style='color: blue;'>✓ Các học phần đã đăng ký:</strong><br>";
        foreach ($registered_courses as $course) {
            echo "  • " . $course['ma_hp'] . " - " . $course['ten_hp'] . " (" . $course['so_tin_chi'] . " TC)<br>";
        }
    } else {
        echo "<strong style='color: #999;'>Không có học phần đã đăng ký</strong><br>";
    }
    echo "</div>";
}
?>
