<?php
// Giả lập controller gọi method getTuitionFees
require_once 'app/Core/Database.php';
require_once 'app/Models/StudentModel.php';
require_once 'app/Models/CourseModel.php';

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}

// Kiểm tra StudentModel
$studentModel = new \App\Models\StudentModel();

// Test với sinh viên ID 1
$tuitionFeesInfo = $studentModel->getTuitionFees(1);

echo "<h2>Test getTuitionFees() - Sinh viên ID 1</h2>";
echo "<h3>Tổng quan:</h3>";
echo "- Tổng học phí: " . formatMoney($tuitionFeesInfo['tong_hoc_phi']) . "<br>";
echo "- Đã nộp: " . formatMoney($tuitionFeesInfo['tong_da_nop']) . "<br>";
echo "- Còn nợ: " . formatMoney($tuitionFeesInfo['tong_no']) . "<br>";

echo "<h3>Chi tiết từng học kỳ:</h3>";
foreach ($tuitionFeesInfo['hp_list'] as $hp) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Năm " . $hp['nam_hoc'] . ", HK " . $hp['hoc_ky'] . "</strong><br>";
    echo "Học phí: " . formatMoney($hp['so_tien']) . "<br>";
    
    // Kiểm tra xem có registered_courses không
    if (isset($hp['registered_courses'])) {
        echo "<strong style='color: blue;'>✓ registered_courses đã được set</strong> - Số học phần: " . count($hp['registered_courses']) . "<br>";
        
        if (!empty($hp['registered_courses'])) {
            echo "Các học phần:<br>";
            foreach ($hp['registered_courses'] as $course) {
                echo "  • " . $course['ma_hp'] . " - " . $course['ten_hp'] . " (" . $course['so_tin_chi'] . " TC)<br>";
            }
        }
    } else {
        echo "<strong style='color: red;'>✗ registered_courses KHÔNG được set</strong><br>";
    }
    
    echo "</div>";
}
?>
