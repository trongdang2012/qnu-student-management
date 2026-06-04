<?php
require_once 'config/database.php';

// Tạo database instance
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$sv_id = 1; // ID của sinh viên đầu tiên

// Lấy thông tin tuition phí
$sql = "SELECT hp.*";
$sql .= " FROM hoc_phi hp";
$sql .= " WHERE hp.sinh_vien_id = :sid ORDER BY hp.nam_hoc DESC, hp.hoc_ky DESC";

$stmt = $db->prepare($sql);
$stmt->execute(['sid' => $sv_id]);
$hp_list = $stmt->fetchAll();

echo "<h2>Kết quả Test</h2>";
echo "<p>Sinh viên ID: $sv_id</p>";
echo "<p>Số bản ghi học phí: " . count($hp_list) . "</p>";

foreach ($hp_list as $hp) {
    echo "<hr>";
    echo "<h3>Năm {$hp['nam_hoc']}, HK {$hp['hoc_ky']}</h3>";
    
    // Lấy dữ liệu học phần đã đăng ký
    $stmt2 = $db->prepare("
        SELECT hp_reg.ma_hp, hp_reg.ten_hp, hp_reg.so_tin_chi
        FROM dang_ky_hp dk
        JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
        JOIN hoc_phan hp_reg ON hp_reg.id = lhp.hoc_phan_id
        WHERE dk.sinh_vien_id = :sid AND dk.nam_hoc = :nh AND dk.hoc_ky = :hk AND dk.trang_thai = 'Đã duyệt'
        ORDER BY hp_reg.ma_hp
    ");
    $stmt2->execute(['sid' => $sv_id, 'nh' => $hp['nam_hoc'], 'hk' => $hp['hoc_ky']]);
    $courses = $stmt2->fetchAll();
    
    if (!empty($courses)) {
        echo "<p style='background: #e3f2fd; padding: 10px; border-radius: 4px;'>";
        echo "<strong>✓ Các học phần đã đăng ký:</strong><br>";
        foreach ($courses as $course) {
            echo "  • {$course['ma_hp']} - {$course['ten_hp']} ({$course['so_tin_chi']} TC)<br>";
        }
        echo "</p>";
    } else {
        echo "<p style='color: #999;'>Không có học phần đã đăng ký</p>";
    }
    
    echo "<p><strong>Thông tin học phí:</strong><br>";
    echo "  Học phí: " . number_format($hp['so_tien']) . " đ<br>";
    echo "  Đã nộp: " . number_format($hp['da_nop']) . " đ<br>";
    $no = $hp['so_tien'] - $hp['da_nop'];
    echo "  Còn nợ: " . number_format($no) . " đ";
    echo "</p>";
}
?>
