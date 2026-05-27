<?php
/**
 * Navbar dành cho admin
 */
$active_menu = $active_menu ?? 'dashboard';
?>
<nav class="admin-navbar">
  <div class="admin-navbar-inner">
    <div class="admin-navbar-brand">
      <div class="logo-icon">
        <i class="fas fa-school"></i>
      </div>
      <a href="<?= BASE_URL ?>/admin/dashboard" style="color:#fff;text-decoration:none">
        QNU SMS - Admin
      </a>
    </div>
    
    <ul class="admin-navbar-menu">
      <li class="nav-item <?= $active_menu==='dashboard'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">
          <i class="fas fa-chart-line"></i> Tổng quan
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='users'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/users">
          <i class="fas fa-users"></i> Tài khoản
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='sinh_vien'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/sinh-vien">
          <i class="fas fa-graduation-cap"></i> Sinh viên
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='hoc_phan'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/hoc-phan">
          <i class="fas fa-book"></i> Học phần
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='hoc_phi'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/hoc-phi">
          <i class="fas fa-money-bill-wave"></i> Học phí
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='tai_lieu'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/tai-lieu">
          <i class="fas fa-folder-open"></i> Tài liệu
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='thoi_khoa_bieu'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/thoi-khoa-bieu">
          <i class="fas fa-calendar-alt"></i> Thời khóa biểu
        </a>
      </li>
      <li class="nav-item dropdown <?= $active_menu==='diem'?'active':'' ?>">
        <a class="nav-link" href="#">
          <i class="fas fa-graduation-cap"></i> Quản lý điểm <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu">
          <li><a href="<?= BASE_URL ?>/admin/diem/hoc-tap">
            <span class="menu-icon"><i class="fas fa-book-open"></i></span> Điểm học tập
          </a></li>
          <li><a href="<?= BASE_URL ?>/admin/diem/ren-luyen">
            <span class="menu-icon"><i class="fas fa-star"></i></span> Điểm rèn luyện
          </a></li>
          <li><a href="<?= BASE_URL ?>/admin/diem/bao-cao">
            <span class="menu-icon"><i class="fas fa-chart-bar"></i></span> Báo cáo điểm
          </a></li>
        </ul>
      </li>
      <li class="nav-item <?= $active_menu==='data_sync'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/data-sync">
          <i class="fas fa-database"></i> Đồng bộ dữ liệu
        </a>
      </li>
      <li class="nav-item <?= $active_menu==='thong_bao'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/thong-bao">
          <i class="fas fa-bell"></i> Quản lý thông báo
        </a>
      </li>
    </ul>

    <div class="admin-navbar-right">
      <a href="<?= BASE_URL ?>/auth/logout" class="nav-link">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </div>
  <div class="sidebar-resizer" id="sidebarResizer"></div>
</nav>
