<?php
/**
 * admin/data_sync/export.php - Script xuất CSDL
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$filename = "backup_" . DB_NAME . "_" . date("Y-m-d_H-i-s") . ".sql";
$dumpPath = sys_get_temp_dir() . '/' . $filename;

// Đường dẫn tới mysqldump (Trong XAMPP mặc định)
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';

if (!file_exists($mysqldumpPath)) {
    // Thử fallback nếu không dùng XAMPP
    $mysqldumpPath = 'mysqldump'; 
}

$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;

// Xây dựng command
$command = "\"$mysqldumpPath\" -h $db_host -u $db_user";
if (!empty($db_pass)) {
    $command .= " -p$db_pass";
}
$command .= " $db_name > \"$dumpPath\"";

// Thực thi
exec($command, $output, $return_var);

if ($return_var === 0 && file_exists($dumpPath)) {
    // Download file
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($dumpPath).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($dumpPath));
    readfile($dumpPath);
    
    // Xóa file temp
    unlink($dumpPath);
    exit;
} else {
    $_SESSION['error'] = "Đã xảy ra lỗi khi tạo bản sao lưu. Mã lỗi: $return_var";
    header("Location: " . BASE_URL . "/admin/data_sync/index.php");
    exit;
}
?>
