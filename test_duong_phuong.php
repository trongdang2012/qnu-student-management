<?php
require_once 'config/database.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Tìm sinh viên có tên "Dương Phương"
echo "<h2>Tìm sinh viên tên 'Dương Phương'</h2>";
$result = $db->query("SELECT * FROM sinh_vien WHERE ho_ten LIKE '%Dương Phương%'");
$students = $result->fetchAll();

if (empty($students)) {
    echo "Không tìm thấy sinh viên tên 'Dương Phương'<br>";
} else {
    foreach ($students as $sv) {
        echo "<h3>Sinh viên: " . $sv['ho_ten'] . " (Mã: " . $sv['ma_sv'] . ", ID: " . $sv['id'] . ")</h3>";
        
        // Tìm các học phí
        $hp = $db->prepare("SELECT * FROM hoc_phi WHERE sinh_vien_id = ? ORDER BY nam_hoc DESC, hoc_ky DESC");
        $hp->execute([$sv['id']]);
        $hp_list = $hp->fetchAll();
        
        echo "Số bản ghi học phí: " . count($hp_list) . "<br>";
        
        // Với mỗi học kỳ, kiểm tra dữ liệu đăng ký
        foreach ($hp_list as $hp_item) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
            echo "<strong>Năm " . $hp_item['nam_hoc'] . ", HK " . $hp_item['hoc_ky'] . "</strong><br>";
            
            // Kiểm tra dữ liệu đăng ký
            $dk = $db->prepare("
                SELECT COUNT(*) as cnt
                FROM dang_ky_hp
                WHERE sinh_vien_id = ? AND nam_hoc = ? AND hoc_ky = ? AND trang_thai = 'Đã duyệt'
            ");
            $dk->execute([$sv['id'], $hp_item['nam_hoc'], $hp_item['hoc_ky']]);
            $dk_count = $dk->fetch()['cnt'];
            
            echo "Số học phần đã đăng ký: " . $dk_count . "<br>";
            
            if ($dk_count > 0) {
                echo "Chi tiết học phần:<br>";
                $dk2 = $db->prepare("
                    SELECT hp_reg.ma_hp, hp_reg.ten_hp
                    FROM dang_ky_hp dk
                    JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
                    JOIN hoc_phan hp_reg ON hp_reg.id = lhp.hoc_phan_id
                    WHERE dk.sinh_vien_id = ? AND dk.nam_hoc = ? AND dk.hoc_ky = ? AND dk.trang_thai = 'Đã duyệt'
                    ORDER BY hp_reg.ma_hp
                ");
                $dk2->execute([$sv['id'], $hp_item['nam_hoc'], $hp_item['hoc_ky']]);
                $courses = $dk2->fetchAll();
                foreach ($courses as $c) {
                    echo "  • " . $c['ma_hp'] . " - " . $c['ten_hp'] . "<br>";
                }
            }
            
            echo "</div>";
        }
    }
}

// Tìm tất cả sinh viên có name như hình
echo "<h2>Tìm sinh viên có 'Phương' trong tên</h2>";
$result2 = $db->query("SELECT * FROM sinh_vien WHERE ho_ten LIKE '%Phương%' LIMIT 5");
$all_students = $result2->fetchAll();
foreach ($all_students as $s) {
    echo $s['ma_sv'] . " - " . $s['ho_ten'] . " (ID: " . $s['id'] . ")<br>";
}
?>
