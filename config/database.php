<?php
/**
 * Kết nối cơ sở dữ liệu MySQL
 * QNU Student Management System
 */

function env(string $key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', 'hoghuyk11'));
define('DB_NAME', env('DB_NAME', 'qnu_sms'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (mysqli_sql_exception $e) {
            $error = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            die('<div style="padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:5px;font-family:Roboto,sans-serif;"><strong>Lỗi kết nối CSDL:</strong> ' . $error . '<br><small>Kiểm tra config/database.php và mật khẩu MySQL của bạn.</small></div>');
        }

        if ($conn->connect_error) {
            $error = htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8');
            die('<div style="padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:5px;font-family:Roboto,sans-serif;"><strong>Lỗi kết nối CSDL:</strong> ' . $error . '<br><small>Kiểm tra config/database.php và mật khẩu MySQL của bạn.</small></div>');
        }
        $conn->set_charset(DB_CHARSET);
    }
    return $conn;
}
?>
