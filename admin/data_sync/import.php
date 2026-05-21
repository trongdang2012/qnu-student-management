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

    $mysqlPath = str_replace('/', '\\', $mysqlPath);
    if (!file_exists($mysqlPath)) {
        $mysqlPath = 'mysql';
    }

    $command = '"' . $mysqlPath . '"';
    $command .= ' -h "' . $db_host . '"';
    $command .= ' -u "' . $db_user . '"';
    if ($db_pass !== '') {
        $command .= ' --password="' . $db_pass . '"';
    }
    $command .= ' --default-character-set=utf8mb4';
    $command .= ' "' . $db_name . '"';
    $command .= ' < "' . $tmpPath . '"';
    
    // Create a temporary batch file to avoid cmd.exe escaping issues
    $batFile = sys_get_temp_dir() . '/import_' . time() . '.bat';
    file_put_contents($batFile, "@echo off\n" . $command);
    
    exec('call "' . $batFile . '" 2>&1', $output, $return_var);
    @unlink($batFile);
    if ($return_var === 0) {
        $_SESSION['success'] = "Phục hồi cơ sở dữ liệu thành công!";
    } else {
        $stdout = implode("\n", $output);
        $message = "Đã xảy ra lỗi khi phục hồi CSDL. Mã lỗi: $return_var";
        if (!empty($stdout)) {
            $message .= " - Thông tin: " . substr($stdout, 0, 500);
        }
        $_SESSION['error'] = $message;
    }

    header("Location: " . BASE_URL . "/admin/data_sync/index.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/admin/data_sync/index.php");
    exit;
}
?>
