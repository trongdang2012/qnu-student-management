<?php
require_once 'config/database.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Kiểm tra tổng số bản ghi trong dang_ky_hp
$result = $db->query('SELECT COUNT(*) as cnt FROM dang_ky_hp');
$total = $result->fetch()['cnt'];
echo "Tổng số bản ghi trong dang_ky_hp: $total\n\n";

// Kiểm tra xem có học phần đã duyệt không
$result = $db->query('SELECT COUNT(*) as cnt FROM dang_ky_hp WHERE trang_thai = "Đã duyệt"');
$approved = $result->fetch()['cnt'];
echo "Số bản ghi đã duyệt: $approved\n\n";

// Lấy một vài sinh viên có dữ liệu dang_ky_hp
echo "--- Sinh viên có đăng ký học phần ---\n";
$result = $db->query('
    SELECT DISTINCT sv.id, sv.ma_sv, sv.ho_ten, COUNT(dk.id) as so_hp
    FROM sinh_vien sv
    JOIN dang_ky_hp dk ON dk.sinh_vien_id = sv.id
    WHERE dk.trang_thai = "Đã duyệt"
    GROUP BY sv.id, sv.ma_sv, sv.ho_ten
    LIMIT 5
');
$students = $result->fetchAll();
foreach ($students as $sv) {
    echo $sv['ma_sv'] . ' - ' . $sv['ho_ten'] . ' (' . $sv['so_hp'] . ' học phần)\n';
}

// Nếu không có, kiểm tra dữ liệu thô
if (empty($students)) {
    echo "\n!!! Không có dữ liệu dang_ky_hp !!!\n";
    echo "\nKiểm tra bảng dang_ky_hp có cột nào không:\n";
    $result = $db->query('DESCRIBE dang_ky_hp');
    $columns = $result->fetchAll();
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
