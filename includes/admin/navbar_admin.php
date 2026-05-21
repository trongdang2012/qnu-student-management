<?php
/**
 * Navbar dành cho admin
 */
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="admin-navbar">
  <div class="admin-navbar-inner">
    <div class="admin-navbar-brand">
      <div class="logo-icon">
        <i class="fas fa-lock"></i>
      </div>
      <a href="<?= BASE_URL ?>/admin/dashboard.php" style="color:#fff;text-decoration:none">
        Admin Panel
      </a>
    </div>
    
    <ul class="admin-navbar-menu">
      <li class="nav-item <?= $current_page==='dashboard'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">
          <i class="fas fa-chart-line"></i> Tổng quan
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='users'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/users/index.php">
          <i class="fas fa-users"></i> Tài khoản
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='sinh_vien'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/sinh_vien/index.php">
          <i class="fas fa-graduation-cap"></i> Sinh viên
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='hoc_phan'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/hoc_phan/index.php">
          <i class="fas fa-book"></i> Học phần
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='hoc_phi'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/hoc_phi/index.php">
          <i class="fas fa-money-bill-wave"></i> Học phí
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='tai_lieu'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/tai_lieu/index.php">
          <i class="fas fa-folder-open"></i> Tài liệu
        </a>
      </li>
      <li class="nav-item <?= $current_dir==='thoi_khoa_bieu'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/thoi_khoa_bieu/index.php">
          <i class="fas fa-calendar-alt"></i> Thời khóa biểu
        </a>
      </li>
      <li class="nav-item dropdown <?= $current_dir==='diem'?'active':'' ?>">
        <a class="nav-link" href="#">
          <i class="fas fa-graduation-cap"></i> Quản lý điểm <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu">
          <li><a href="<?= BASE_URL ?>/admin/diem/hoc_tap.php">
            <span class="menu-icon"><i class="fas fa-book-open"></i></span> Điểm học tập
          </a></li>
          <li><a href="<?= BASE_URL ?>/admin/diem/ren_luyen.php">
            <span class="menu-icon"><i class="fas fa-star"></i></span> Điểm rèn luyện
          </a></li>
          <li><a href="<?= BASE_URL ?>/admin/diem/bao_cao.php">
            <span class="menu-icon"><i class="fas fa-chart-bar"></i></span> Báo cáo điểm
          </a></li>
        </ul>
      </li>
      <li class="nav-item <?= $current_dir==='data_sync'?'active':'' ?>">
        <a class="nav-link" href="<?= BASE_URL ?>/admin/data_sync/index.php">
          <i class="fas fa-database"></i> Nhập/Xuất Dữ liệu
        </a>
      </li>
    </ul>

    <div class="admin-navbar-right">
      <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
      </a>
    </div>
  </div>
</nav>
