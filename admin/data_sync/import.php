<?php
/**
 * admin/data_sync/import.php - Script nhập CSDL
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Lỗi khi tải file lên.";
        header("Location: " . BASE_URL . "/admin/data_sync/index.php");
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        $_SESSION['error'] = "Chỉ chấp nhận file định dạng .sql";
        header("Location: " . BASE_URL . "/admin/data_sync/index.php");
        exit;
    }

    // Đường dẫn tới mysql (Trong XAMPP mặc định)
    $mysqlPath = 'C:\xampp\mysql\bin\mysql.exe';

    if (!file_exists($mysqlPath)) {
        // Thử fallback nếu không dùng XAMPP
        $mysqlPath = 'mysql'; 
    }

    $db_host = DB_HOST;
    $db_user = DB_USER;
    $db_pass = DB_PASS;
    $db_name = DB_NAME;

    $tmpPath = $file['tmp_name'];

    // Xây dựng command
    // Lưu ý: mysql.exe cần nhận đầu vào từ file, dùng dấu < 
    $command = "\"$mysqlPath\" -h $db_host -u $db_user";
    if (!empty($db_pass)) {
        $command .= " -p$db_pass";
    }
    $command .= " $db_name < \"$tmpPath\"";

    // Thực thi
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        $_SESSION['success'] = "Phục hồi cơ sở dữ liệu thành công!";
    } else {
        $_SESSION['error'] = "Đã xảy ra lỗi khi phục hồi CSDL. Mã lỗi: $return_var";
    }

    header("Location: " . BASE_URL . "/admin/data_sync/index.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/admin/data_sync/index.php");
    exit;
}
?>
