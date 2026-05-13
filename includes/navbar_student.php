<?php
/**
 * Top Navbar dành cho sinh viên
 * Yêu cầu $sv (mảng thông tin sinh viên) và $active_menu đã được set
 */
$_base = BASE_URL;
$_name = isset($sv) ? e($sv['ho_ten']) : 'Sinh viên';
$_msv  = isset($sv) ? e($sv['ma_sv'])  : '';
$_avatar = (isset($sv) && !empty($sv['anh_dai_dien']))
         ? $_base . '/uploads/' . e($sv['anh_dai_dien'])
         : $_base . '/assets/img/default-avatar.png';

$_menu = $active_menu ?? '';
?>
<nav class="student-navbar" role="navigation" aria-label="Main navigation">
  <div class="navbar-inner">

    <!-- Brand -->
    <a href="<?= $_base ?>/student/dashboard.php" class="navbar-brand" aria-label="QNU SMS - Trang chủ">
      <div class="logo-icon"><i class="fas fa-university"></i></div>
      <span>QNU SMS</span>
    </a>

    <!-- Toggle (mobile) -->
    <button class="navbar-toggle" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Mở menu">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Nav links -->
    <ul class="navbar-nav" id="navMenu" role="menubar">

      <!-- Trang chủ -->
      <li class="nav-item" role="none">
        <a href="<?= $_base ?>/student/dashboard.php"
           class="nav-link <?= $_menu === 'dashboard' ? 'active' : '' ?>"
           role="menuitem">
          <i class="fas fa-home"></i> Tổng quan
        </a>
      </li>

      <!-- Cá nhân -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'ca_nhan' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          <i class="fas fa-user-circle"></i> Cá nhân <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/ca_nhan/thong_tin.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-id-card"></i></span> Thông tin cá nhân
          </a></li>
          <li><a href="<?= $_base ?>/student/ca_nhan/cap_nhat.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-edit"></i></span> Cập nhật thông tin
          </a></li>
          <li><a href="<?= $_base ?>/student/ca_nhan/tien_do.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-tasks"></i></span> Tiến độ tín chỉ
          </a></li>
        </ul>
      </li>

      <!-- Học tập -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'hoc_tap' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          <i class="fas fa-book-open"></i> Học tập <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/hoc_tap/chuong_trinh.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-list-alt"></i></span> Chương trình đào tạo
          </a></li>
          <li><a href="<?= $_base ?>/student/hoc_tap/thoi_khoa_bieu.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span> Thời khóa biểu
          </a></li>
          <li><a href="<?= $_base ?>/student/hoc_tap/diem_hoc_tap.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-graduation-cap"></i></span> Điểm học tập
          </a></li>
          <li><a href="<?= $_base ?>/student/hoc_tap/diem_ren_luyen.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-star"></i></span> Điểm rèn luyện
          </a></li>
          <li><div class="dropdown-divider"></div></li>
          <li><a href="<?= $_base ?>/student/hoc_tap/hoc_phi.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-money-bill-wave"></i></span> Học phí
          </a></li>
        </ul>
      </li>

      <!-- Trực tuyến -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'truc_tuyen' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          <i class="fas fa-laptop-code"></i> Trực tuyến <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/truc_tuyen/dang_ky.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-plus-circle"></i></span> Đăng ký học phần
          </a></li>
          <li><a href="<?= $_base ?>/student/truc_tuyen/chia_se_tl.php" role="menuitem">
            <span class="menu-icon"><i class="fas fa-share-alt"></i></span> Tài liệu chia sẻ
          </a></li>
        </ul>
      </li>

    </ul><!-- /navMenu -->

    <!-- User info (right) -->
    <div class="navbar-user" id="userMenu">
      <img src="<?= $_avatar ?>" alt="Avatar" class="user-avatar" id="userAvatarToggle">
      <span class="user-name"><?= $_name ?></span>
      <div class="user-dropdown" role="menu">
        <div class="user-dropdown-header">
          <div class="ud-name"><?= $_name ?></div>
          <div class="ud-id">MSSV: <?= $_msv ?></div>
        </div>
        <a href="<?= $_base ?>/student/ca_nhan/thong_tin.php" role="menuitem">
          <i class="fas fa-user"></i> Hồ sơ của tôi
        </a>
        <a href="<?= $_base ?>/student/ca_nhan/cap_nhat.php" role="menuitem">
          <i class="fas fa-cog"></i> Cài đặt
        </a>
        <a href="<?= $_base ?>/auth/logout.php" class="logout-link" role="menuitem"
           onclick="return confirm('Bạn có muốn đăng xuất không?')">
          <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
      </div>
    </div>

  </div><!-- /navbar-inner -->
</nav>
