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
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <style>
    /* Admin Sidebar - Vertical Layout */
    :root {
      --sidebar-width: 250px;
    }

    .admin-navbar {
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: var(--sidebar-width);
      background: var(--primary);
      box-shadow: 2px 0 10px rgba(0,0,0,.2);
      z-index: 1000;
      overflow-y: auto;
      transition: width 0.1s;
    }
    .admin-navbar.collapsed {
      transform: translateX(-100%);
      transition: transform 0.3s ease;
    }

    .sidebar-resizer {
      position: absolute;
      top: 0; right: 0; bottom: 0;
      width: 5px;
      cursor: ew-resize;
      background: transparent;
      z-index: 1010;
    }
    .sidebar-resizer:hover, .sidebar-resizer.active {
      background: rgba(255, 255, 255, 0.3);
    }

    .admin-hamburger {
      position: fixed;
      top: 15px; left: 15px;
      z-index: 1001;
      background: var(--primary);
      color: #fff;
      border: none;
      width: 40px; height: 40px;
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .admin-hamburger.show {
      opacity: 1;
      pointer-events: auto;
    }
    .admin-hamburger:hover {
      background: var(--primary-dark);
    }

    .admin-navbar-inner {
      display: flex;
      flex-direction: column;
      padding: 20px 0;
      height: 100%;
    }

    .admin-navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff;
      font-size: 18px;
      font-weight: 700;
      padding: 0 20px 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 20px;
    }

    .admin-navbar-brand .logo-icon {
      width: 38px; height: 38px;
      background: rgba(255,255,255,.15);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      color: #fff;
      flex-shrink: 0;
    }

    .admin-navbar-menu {
      display: flex;
      flex-direction: column;
      gap: 4px;
      padding: 0 10px;
      flex: 1;
      margin: 0;
      list-style: none;
    }

    .admin-navbar-menu .nav-item {
      position: relative;
      width: 100%;
    }

    .admin-navbar-menu .nav-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 14px;
      color: rgba(255,255,255,.75);
      font-size: 15px;
      font-weight: 500;
      border-radius: var(--radius-sm);
      transition: all var(--transition);
      text-decoration: none;
    }

    .admin-navbar-menu .nav-link:hover,
    .admin-navbar-menu .nav-link.active {
      background: rgba(255,255,255,.18);
      color: #fff;
    }

    /* Admin Dropdown - Accordion */
    .admin-navbar-menu .nav-item.dropdown .dropdown-menu {
      position: static;
      display: none;
      box-shadow: none;
      background: transparent;
      padding: 5px 0 5px 20px;
      margin: 0;
    }
    
    .admin-navbar-menu .nav-item.dropdown .dropdown-menu::before {
      display: none;
    }
    
    .admin-navbar-menu .nav-item.dropdown.open .dropdown-menu,
    .admin-navbar-menu .nav-item.dropdown.active .dropdown-menu {
      display: block;
    }

    .admin-navbar-menu .nav-item.dropdown .dropdown-menu a {
      color: rgba(255,255,255,0.7);
      padding: 8px 14px;
      font-size: 14px;
    }
    .admin-navbar-menu .nav-item.dropdown .dropdown-menu a:hover {
      color: #fff;
      background: rgba(255,255,255,0.1);
      border-radius: 4px;
    }
    
    .admin-navbar-menu .nav-item.dropdown .nav-link .arrow {
      transform: rotate(0deg);
      transition: transform 0.2s;
    }
    .admin-navbar-menu .nav-item.dropdown.open .nav-link .arrow,
    .admin-navbar-menu .nav-item.dropdown.active .nav-link .arrow {
      transform: rotate(180deg);
    }

    .admin-navbar-right {
      padding: 20px;
      border-top: 1px solid rgba(255,255,255,0.1);
      margin-top: auto;
    }

    .admin-navbar-right .nav-link {
      display: flex;
      align-items: center;
      gap: 10px;
      color: rgba(255,255,255,.75);
      padding: 12px 14px;
      border-radius: var(--radius-sm);
      transition: all var(--transition);
      text-decoration: none;
    }

    .admin-navbar-right .nav-link:hover {
      background: rgba(255,255,255,.18);
      color: #fff;
    }

    /* Admin wrapper */
    .admin-wrapper {
      padding-top: 24px;
      padding-bottom: 40px;
      padding-left: calc(var(--sidebar-width) + 20px);
      min-height: 100vh;
      background: #f5f6fa;
      transition: padding-left 0.1s;
    }
    .admin-wrapper.collapsed {
      padding-left: 20px;
      transition: padding-left 0.3s ease;
    }

    .admin-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .page-title {
      position: sticky;
      top: 0;
      background: #f5f6fa;
      padding: 24px 20px 15px 20px;
      margin: -24px -20px 24px -20px;
      z-index: 100;
      border-bottom: 1px solid #e1e3ea;
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
  <button class="admin-hamburger" id="adminHamburger">
    <i class="fas fa-bars"></i>
  </button>
