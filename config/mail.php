<?php
/**
 * Cấu hình gửi mail (SMTP)
 * Vui lòng điền thông tin tài khoản Gmail của bạn vào đây.
 */

// Cấu hình SMTP Host (Gmail)
define('MAIL_HOST', 'smtp.gmail.com');

// Email dùng để gửi (Ví dụ: demo.qnu.sms@gmail.com)
define('MAIL_USERNAME', 'dangvantrong201206@gmail.com');

// Mật khẩu ứng dụng (App Password) - lấy trong phần bảo mật của Google Account
// KHÔNG phải mật khẩu đăng nhập Gmail thông thường.
define('MAIL_PASSWORD', 'duba quho brii gzgh');

// Cổng SMTP (Thường là 587 cho TLS hoặc 465 cho SSL)
define('MAIL_PORT', 587);

// Tên người gửi hiển thị
define('MAIL_FROM_NAME', 'Hệ thống Quản lý Sinh viên QNU');
?>