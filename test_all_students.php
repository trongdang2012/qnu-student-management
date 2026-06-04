<?php
require_once 'config/database.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Lấy sinh viên hiện tại (ngoài lề: lấy tất cả sinh viên để xem)
echo "<h2>Danh sách sinh viên và dữ liệu đăng ký học phần</h2>";

$result = $db->query("
    SELECT sv.id, sv.ma_sv, sv.ho_ten, 
           COUNT(dk.id) as so_dang_ky,
           GROUP_CONCAT(DISTINCT CONCAT(dk.nam_hoc, '_', dk.hoc_ky) ORDER BY dk.nam_hoc DESC, dk.hoc_ky DESC) as ky_hoc
    FROM sinh_vien sv
    LEFT JOIN dang_ky_hp dk ON dk.sinh_vien_id = sv.id AND dk.trang_thai = 'Đã duyệt'
    GROUP BY sv.id, sv.ma_sv, sv.ho_ten
    ORDER BY sv.id
    LIMIT 10
");

foreach ($result->fetchAll() as $sv) {
    echo "<h3>" . $sv['ma_sv'] . " - " . $sv['ho_ten'] . " (ID: " . $sv['id'] . ")</h3>";
    echo "Số lần đăng ký: " . $sv['so_dang_ky'] . "<br>";
    if ($sv['ky_hoc']) {
        echo "Các học kỳ: " . $sv['ky_hoc'] . "<br>";
    }
    
    // Chi tiết từng học kỳ
    $stmt = $db->prepare("
        SELECT DISTINCT dk.nam_hoc, dk.hoc_ky, COUNT(*) as so_hp
        FROM dang_ky_hp dk
        WHERE dk.sinh_vien_id = ? AND dk.trang_thai = 'Đã duyệt'
        GROUP BY dk.nam_hoc, dk.hoc_ky
        ORDER BY dk.nam_hoc DESC, dk.hoc_ky DESC
    ");
    $stmt->execute([$sv['id']]);
    $by_ky = $stmt->fetchAll();
    
    if ($by_ky) {
        echo "<ul>";
        foreach ($by_ky as $ky) {
            echo "<li>" . $ky['nam_hoc'] . " HK " . $ky['hoc_ky'] . ": " . $ky['so_hp'] . " học phần</li>";
        }
        echo "</ul>";
    }
    echo "<hr>";
}
?>
