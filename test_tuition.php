<?php
require_once 'config/database.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Lấy 1 sinh viên có học phí
$result = $db->query('SELECT sv.id, sv.ma_sv, sv.ho_ten, hf.nam_hoc, hf.hoc_ky FROM sinh_vien sv JOIN hoc_phi hf ON hf.sinh_vien_id = sv.id LIMIT 1');
$row = $result->fetch();
if ($row) {
    echo 'Sinh viên: ' . $row['ma_sv'] . ' - ' . $row['ho_ten'] . PHP_EOL;
    echo 'ID: ' . $row['id'] . PHP_EOL;
    echo 'Năm học: ' . $row['nam_hoc'] . ', Học kỳ: ' . $row['hoc_ky'] . PHP_EOL;
    
    // Kiểm tra dữ liệu trong dang_ky_hp
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM dang_ky_hp WHERE sinh_vien_id = ? AND nam_hoc = ? AND hoc_ky = ? AND trang_thai = "Đã duyệt"');
    $stmt->execute([$row['id'], $row['nam_hoc'], $row['hoc_ky']]);
    $count = $stmt->fetch();
    echo 'Số học phần đã dăng ký: ' . $count['cnt'] . PHP_EOL;
    
    // Xem chi tiết
    echo "\n--- Chi tiết các học phần đã đăng ký ---\n";
    $stmt2 = $db->prepare('
        SELECT hp.ma_hp, hp.ten_hp, hp.so_tin_chi 
        FROM dang_ky_hp dk
        JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
        WHERE dk.sinh_vien_id = ? AND dk.nam_hoc = ? AND dk.hoc_ky = ? AND dk.trang_thai = "Đã duyệt"
        ORDER BY hp.ma_hp
    ');
    $stmt2->execute([$row['id'], $row['nam_hoc'], $row['hoc_ky']]);
    $courses = $stmt2->fetchAll();
    foreach ($courses as $course) {
        echo $course['ma_hp'] . ' - ' . $course['ten_hp'] . ' (' . $course['so_tin_chi'] . ' TC)' . PHP_EOL;
    }
}
?>
