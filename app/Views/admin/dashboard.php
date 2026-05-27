<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
      <p style="color:#666;margin:5px 0 0">Hệ thống Quản lý Sinh viên - Đại học Quy Nhơn</p>
    </div>

    <!-- Thống kê -->
    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-users"></i>
        <div>
          <h3>Tổng sinh viên</h3>
          <div class="stat-value"><?= (int)$stats['total_students'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745">
        <i class="fas fa-book" style="color:#28a745"></i>
        <div>
          <h3>Học phần</h3>
          <div class="stat-value"><?= (int)$stats['total_hoc_phan'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#e83e8c">
        <i class="fas fa-chalkboard-teacher" style="color:#e83e8c"></i>
        <div>
          <h3>Giảng viên</h3>
          <div class="stat-value"><?= (int)$stats['total_giang_vien'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#6f42c1">
        <i class="fas fa-sitemap" style="color:#6f42c1"></i>
        <div>
          <h3>Khoa</h3>
          <div class="stat-value"><?= (int)$stats['total_khoa'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#fd7e14">
        <i class="fas fa-chalkboard" style="color:#fd7e14"></i>
        <div>
          <h3>Lớp học</h3>
          <div class="stat-value"><?= (int)$stats['total_lop'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-calendar-alt" style="color:#ffc107"></i>
        <div>
          <h3>Thời khóa biểu</h3>
          <div class="stat-value"><?= (int)$stats['total_schedule'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#17a2b8">
        <i class="fas fa-check-square" style="color:#17a2b8"></i>
        <div>
          <h3>Đã duyệt</h3>
          <div class="stat-value"><?= (int)$stats['total_registrations'] ?></div>
        </div>
      </div>
    </div>

    <!-- Hành động nhanh -->
    <div class="card fade-in" style="margin-bottom:20px">
      <div class="card-header">
        <h3><i class="fas fa-bolt"></i> Hành động nhanh</h3>
      </div>
      <div class="card-body" style="display:flex;gap:15px;flex-wrap:wrap;padding:20px">
        <a href="<?= BASE_URL ?>/admin/hoc-phan" class="btn btn-primary">
          <i class="fas fa-plus"></i> Quản lý học phần
        </a>
        <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="btn btn-info">
          <i class="fas fa-calendar-plus"></i> Quản lý TKB
        </a>
      </div>
    </div>

    <!-- Thông tin hệ thống -->
    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Thông tin hệ thống</h3>
      </div>
      <div class="card-body" style="padding:20px">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
          <tr>
            <td style="padding:10px;border-bottom:1px solid #eee"><strong>Tên hệ thống:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #eee"><?= APP_NAME ?></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #eee"><strong>Phiên bản:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #eee"><?= APP_VERSION ?></td>
          </tr>
          <tr>
            <td style="padding:10px;border-bottom:1px solid #eee"><strong>Học kỳ hiện tại:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #eee">HK <?= HOC_KY_HIEN_TAI ?> / <?= NAM_HOC_HIEN_TAI ?></td>
          </tr>
          <tr>
            <td style="padding:10px"><strong>Cơ sở dữ liệu:</strong></td>
            <td style="padding:10px"><?= DB_NAME ?> (MySQL)</td>
          </tr>
        </table>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
