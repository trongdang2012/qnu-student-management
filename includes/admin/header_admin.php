<?php
/**
 * Header dành cho trang admin
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
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="description" content="Admin Panel - Hệ thống Quản lý Sinh viên Đại học Quy Nhơn">
  <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?>Admin - <?= APP_SHORT_NAME ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- CSS -->
  <link rel="stylesheet" href="<?= $_base ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= $_base ?>/assets/css/student.css">
  
  <style>
    /* Admin Navbar - giống student navbar */
    .admin-navbar {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: var(--header-h);
      background: #1a1a2e;
      box-shadow: 0 2px 10px rgba(0,0,0,.2);
      z-index: 1000;
      display: flex;
      align-items: center;
      border-bottom: 3px solid #ff6b35;
    }

    .admin-navbar-inner {
      max-width: 1400px;
      width: 100%;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .admin-navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      font-size: 18px;
      font-weight: 700;
      flex-shrink: 0;
    }

    .admin-navbar-brand .logo-icon {
      width: 38px; height: 38px;
      background: rgba(255,107,53,.2);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      color: #ff6b35;
    }

    .admin-navbar-menu {
      display: flex;
      align-items: center;
      gap: 4px;
      flex: 1;
      justify-content: flex-start;
      margin-left: 20px;
    }

    .admin-navbar-menu .nav-item {
      position: relative;
    }

    .admin-navbar-menu .nav-link {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      color: rgba(255,255,255,.75);
      font-size: 14px;
      font-weight: 500;
      border-radius: var(--radius-sm);
      transition: all var(--transition);
      white-space: nowrap;
    }

    .admin-navbar-menu .nav-link:hover,
    .admin-navbar-menu .nav-link.active {
      background: rgba(255,107,53,.2);
      color: #ff6b35;
    }

    .admin-navbar-right {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-left: auto;
    }

    .admin-navbar-right .nav-link {
      display: flex;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,.75);
      padding: 8px 12px;
      border-radius: var(--radius-sm);
      transition: all var(--transition);
    }

    .admin-navbar-right .nav-link:hover {
      background: rgba(255,107,53,.2);
      color: #ff6b35;
    }

    /* Admin wrapper */
    .admin-wrapper {
      padding-top: calc(var(--header-h) + 24px);
      padding-bottom: 40px;
      min-height: 100vh;
      background: #f5f6fa;
    }

    .admin-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .page-title {
      margin-bottom: 24px;
    }

    .page-title h1 {
      font-size: 28px;
      font-weight: 700;
      color: var(--text-dark);
      margin: 0 0 6px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .page-title p {
      color: var(--text-muted);
      margin: 0;
    }

    .breadcrumb {
      font-size: 12px;
      color: var(--text-muted);
      margin-bottom: 10px;
    }

    .breadcrumb a {
      color: var(--primary);
      text-decoration: none;
    }

    .breadcrumb span {
      margin: 0 4px;
    }

    /* Admin Grid */
    .admin-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      border-left: 4px solid var(--primary);
      box-shadow: 0 2px 4px rgba(0,0,0,0.08);
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .stat-card i {
      font-size: 32px;
      color: var(--primary);
      opacity: 0.7;
    }

    .stat-card h3 {
      margin: 0 0 5px;
      color: var(--text-muted);
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 600;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary);
      margin: 0;
    }

    .action-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .action-bar .search-box {
      flex: 1;
      min-width: 250px;
    }

    .table-actions {
      display: flex;
      gap: 6px;
      justify-content: center;
    }

    .table-actions .btn-sm {
      padding: 6px 10px;
      font-size: 12px;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 2000;
      align-items: center;
      justify-content: center;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: white;
      border-radius: 8px;
      padding: 30px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
    }

    .modal-header h2 {
      margin: 0;
      color: var(--text-dark);
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #999;
    }

    .modal-close:hover {
      color: #333;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 15px;
    }

    .form-row.full {
      grid-template-columns: 1fr;
    }

    .alert {
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideDown .3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert-info {
      background: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    .card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.08);
      margin-bottom: 20px;
    }

    .card-header {
      padding: 16px 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .card-header h3 {
      margin: 0;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card-body {
      padding: 20px;
    }

    /* Fade in animation */
    .fade-in {
      animation: fadeIn .4s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    /* Text utilities */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-muted); }
  </style>
</head>
<body>
