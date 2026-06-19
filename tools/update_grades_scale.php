<?php
/**
 * Script cập nhật lại thang điểm 7 bậc cho tất cả bản ghi điểm học tập hiện có
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "1. Đọc dữ liệu điểm học tập cần cập nhật...\n";
    $stmt = $conn->query("SELECT id, diem_tong FROM diem_hoc_tap WHERE diem_tong IS NOT NULL");
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Phát hiện " . count($grades) . " dòng điểm cần quy đổi.\n";
    
    $stmtUpdate = $conn->prepare("UPDATE diem_hoc_tap SET diem_chu = ?, diem_he4 = ? WHERE id = ?");
    
    $count = 0;
    foreach ($grades as $g) {
        $id = $g['id'];
        $diemTong = (float)$g['diem_tong'];
        
        $diemChu = 'F';
        $diemHe4 = 0.0;
        
        if ($diemTong >= 9.0) { $diemChu = 'A+'; $diemHe4 = 4.0; }
        elseif ($diemTong >= 8.0) { $diemChu = 'A'; $diemHe4 = 3.5; }
        elseif ($diemTong >= 7.0) { $diemChu = 'B+'; $diemHe4 = 3.0; }
        elseif ($diemTong >= 6.0) { $diemChu = 'B'; $diemHe4 = 2.5; }
        elseif ($diemTong >= 5.0) { $diemChu = 'C'; $diemHe4 = 2.0; }
        elseif ($diemTong >= 4.0) { $diemChu = 'D'; $diemHe4 = 1.5; }
        
        $stmtUpdate->execute([$diemChu, $diemHe4, $id]);
        $count++;
    }
    
    echo "=== HOÀN THÀNH CẬP NHẬT $count DÒNG ĐIỂM THÀNH CÔNG! ===\n";
} catch (Exception $e) {
    echo "LỖI THỰC HIỆN: " . $e->getMessage() . "\n";
}
