<?php
/**
 * Kết nối cơ sở dữ liệu MySQL
 * QNU Student Management System
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qnu_sms');
define('DB_CHARSET', 'utf8mb4');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:5px;font-family:Roboto,sans-serif;">
                <strong>Lỗi kết nối CSDL:</strong> ' . $conn->connect_error . '
                </div>');
        }
        $conn->set_charset(DB_CHARSET);
    }
    return $conn;
}
?>
