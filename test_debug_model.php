<?php
require_once 'app/Core/Database.php';
require_once 'app/Models/StudentModel.php';

// Kiểm tra file được load
$reflector = new ReflectionClass('App\Models\StudentModel');
$filename = $reflector->getFileName();
echo "File được load: $filename<br>";

// Đọc method getTuitionFees
$method = $reflector->getMethod('getTuitionFees');
echo "Method getTuitionFees xuất hiện tại dòng: " . $method->getStartLine() . " - " . $method->getEndLine() . "<br>";

// Lấy source code
$file_content = file_get_contents($filename);
$lines = explode("\n", $file_content);

echo "<h3>Source code của getTuitionFees():</h3>";
echo "<pre>";
for ($i = $method->getStartLine() - 1; $i < $method->getEndLine(); $i++) {
    echo ($i + 1) . ": " . htmlspecialchars($lines[$i]) . "\n";
}
echo "</pre>";

// Kiểm tra xem có 'registered_courses' không
if (strpos($file_content, 'registered_courses') !== false) {
    echo "<p style='color: green;'><strong>✓ Tìm thấy 'registered_courses' trong file</strong></p>";
    
    // Kiểm tra dòng
    $pos = strpos($file_content, 'registered_courses');
    $line_num = substr_count($file_content, "\n", 0, $pos) + 1;
    echo "Xuất hiện tại dòng: $line_num<br>";
} else {
    echo "<p style='color: red;'><strong>✗ KHÔNG tìm thấy 'registered_courses' trong file</strong></p>";
}
?>
