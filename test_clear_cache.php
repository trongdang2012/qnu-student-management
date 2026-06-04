<?php
// Clear Zend OpCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OpCache cleared<br>";
} else {
    echo "⚠ OpCache không enabled<br>";
}

// Giờ test lại
require_once 'app/Core/Database.php';
require_once 'app/Models/StudentModel.php';

$studentModel = new \App\Models\StudentModel();
$tuitionFeesInfo = $studentModel->getTuitionFees(1);

echo "<h2>Test sau khi clear OpCache</h2>";
echo "Chi tiết dòng 1 (2025-2026 HK 2):<br>";
$hp = $tuitionFeesInfo['hp_list'][0];
echo "Năm: " . $hp['nam_hoc'] . ", HK: " . $hp['hoc_ky'] . "<br>";
echo "Có registered_courses? " . (isset($hp['registered_courses']) ? "✓ CÓ" : "✗ KHÔNG") . "<br>";

if (isset($hp['registered_courses']) && !empty($hp['registered_courses'])) {
    echo "Số học phần: " . count($hp['registered_courses']) . "<br>";
    foreach ($hp['registered_courses'] as $course) {
        echo "  - " . $course['ma_hp'] . "<br>";
    }
}
?>
