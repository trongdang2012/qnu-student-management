<?php
require_once 'config/database.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

// Xem cấu trúc bảng dang_ky_hp
echo "=== CẤU TRÚC BẢNG dang_ky_hp ===\n";
$result = $db->query('DESCRIBE dang_ky_hp');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Xem 1 bản ghi mẫu
echo "\n=== DỮ LIỆU MẪU ===\n";
$result = $db->query('SELECT * FROM dang_ky_hp LIMIT 1');
$sample = $result->fetch(PDO::FETCH_ASSOC);
echo json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
