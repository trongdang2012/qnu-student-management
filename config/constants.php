<?php
/**
 * Hằng số cấu hình hệ thống
 * QNU Student Management System
 */

// Tên hệ thống
define('APP_NAME', 'QNU - Hệ thống Quản lý Sinh viên');
define('APP_SHORT_NAME', 'QNU SMS');
define('APP_VERSION', '1.0.0');

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

// Đường dẫn gốc (Tự động phát hiện động theo host truy cập thực tế để tránh mất Session/Cookie khi chuyển hướng qua lại giữa localhost, 127.0.0.1 hoặc IP LAN)
// Nếu đã thiết lập biến môi trường BASE_URL thì ưu tiên dùng.
if (php_sapi_name() === 'cli') {
    define('BASE_URL', env('BASE_URL', 'http://localhost/qnu-student-management'));
} else {
    $isHttps = false;
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $isHttps = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $isHttps = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        $isHttps = true;
    } elseif (($_SERVER['SERVER_PORT'] ?? 80) == 443) {
        $isHttps = true;
    }
    
    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $subfolder = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] ?? '');
    define('BASE_URL', env('BASE_URL', $protocol . $host . $subfolder));
}

// Học kỳ & năm học hiện tại
define('HOC_KY_HIEN_TAI', 2);
define('NAM_HOC_HIEN_TAI', '2025-2026');

// Giới hạn upload file tài liệu (bytes) - 10MB
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Loại file được phép upload
define('ALLOWED_FILE_TYPES', ['pdf','doc','docx','xls','xlsx','ppt','pptx','zip','rar','png','jpg','jpeg']);

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
