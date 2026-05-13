<?php
/**
 * Hằng số cấu hình hệ thống
 * QNU Student Management System
 */

// Tên hệ thống
define('APP_NAME', 'QNU - Hệ thống Quản lý Sinh viên');
define('APP_SHORT_NAME', 'QNU SMS');
define('APP_VERSION', '1.0.0');

// Đường dẫn gốc (điều chỉnh theo môi trường thực tế)
define('BASE_URL', 'http://localhost/qnu-student-management');

// Học kỳ & năm học hiện tại
define('HOC_KY_HIEN_TAI', 5);
define('NAM_HOC_HIEN_TAI', '2023-2024');

// Giới hạn upload file tài liệu (bytes) - 10MB
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// Loại file được phép upload
define('ALLOWED_FILE_TYPES', ['pdf','doc','docx','xls','xlsx','ppt','pptx','zip','rar','png','jpg','jpeg']);

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
