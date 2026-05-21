<?php
/**
 * Header dùng chung cho trang sinh viên
 * Gọi: require_once ROOT . '/includes/header.php';
 * Biến cần truyền vào trước khi include:
 *   $page_title  - Tiêu đề thẻ <title>
 *   $active_menu - Tên menu đang active ('ca_nhan','hoc_tap','truc_tuyen','dashboard')
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}
$_base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hệ thống Quản lý Sinh viên - Trường Đại học Quy Nhơn">
  <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?><?= APP_SHORT_NAME ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Font Awesome (icons) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="<?= $_base ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= $_base ?>/assets/css/student.css">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
