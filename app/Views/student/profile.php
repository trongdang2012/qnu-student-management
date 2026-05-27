<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <!-- Breadcrumb + Tiêu đề -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Cá nhân</span>
        <span>›</span><span>Thông tin</span>
      </div>
      <h1><i class="fas fa-id-card"></i> Thông tin cá nhân</h1>
      <p>Xem đầy đủ thông tin hồ sơ sinh viên của bạn.</p>
    </div>

    <!-- Flash -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-check-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Profile Header Card -->
    <div class="profile-header fade-in">
      <div class="profile-avatar-wrap">
        <img id="avatarPreview"
             src="<?= (!empty($sv['anh_dai_dien'])) ? BASE_URL.'/uploads/'.e($sv['anh_dai_dien']) : BASE_URL.'/assets/img/default-avatar.svg' ?>"
             alt="Ảnh đại diện" class="profile-avatar">
      </div>
      <div class="profile-info">
        <h2><?= e($sv['ho_ten']) ?></h2>
        <div class="ma-sv"><i class="fas fa-id-badge"></i> MSSV: <strong><?= e($sv['ma_sv']) ?></strong></div>
        <div class="tags">
          <span class="badge badge-primary"><i class="fas fa-graduation-cap"></i> <?= e($sv['nganh']) ?></span>
          <span class="badge badge-info"><i class="fas fa-users"></i> <?= e($sv['lop']) ?></span>
          <span class="badge badge-success"><?= e($sv['trang_thai']) ?></span>
        </div>
      </div>
      <div style="margin-left:auto">
        <a href="<?= BASE_URL ?>/student/cap-nhat" class="btn btn-primary">
          <i class="fas fa-edit"></i> Cập nhật
        </a>
      </div>
    </div>

    <!-- Thông tin chi tiết -->
    <div class="content-grid">

      <!-- Cột trái: thông tin cơ bản -->
      <div>
        <div class="card fade-in">
          <div class="card-header">
            <h3><i class="fas fa-user"></i> Thông tin cơ bản</h3>
          </div>
          <div class="card-body" style="padding:0">
            <table class="info-table">
              <tr><td>Họ và tên</td><td><strong><?= e($sv['ho_ten']) ?></strong></td></tr>
              <tr><td>Mã số sinh viên</td><td><?= e($sv['ma_sv']) ?></td></tr>
              <tr><td>Ngày sinh</td><td><?= $sv['ngay_sinh'] ? date('d/m/Y', strtotime($sv['ngay_sinh'])) : '—' ?></td></tr>
              <tr><td>Giới tính</td><td><?= e($sv['gioi_tinh'] ?? '—') ?></td></tr>
              <tr><td>Địa chỉ</td><td><?= e($sv['dia_chi'] ?? '—') ?></td></tr>
              <tr><td>Email</td><td>
                <a href="mailto:<?= e($sv['email'] ?? '') ?>"><?= e($sv['email'] ?? '—') ?></a>
              </td></tr>
              <tr><td>Số điện thoại</td><td><?= e($sv['so_dien_thoai'] ?? '—') ?></td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Cột phải: thông tin học vụ -->
      <div>
        <div class="card fade-in">
          <div class="card-header">
            <h3><i class="fas fa-university"></i> Thông tin học vụ</h3>
          </div>
          <div class="card-body" style="padding:0">
            <table class="info-table">
              <tr><td>Khoa</td><td><?= e($sv['khoa'] ?? '—') ?></td></tr>
              <tr><td>Ngành học</td><td><?= e($sv['nganh'] ?? '—') ?></td></tr>
              <tr><td>Lớp</td><td><?= e($sv['lop'] ?? '—') ?></td></tr>
              <tr><td>Niên khóa</td><td><?= e($sv['nien_khoa'] ?? '—') ?></td></tr>
              <tr><td>Trạng thái</td><td>
                <span class="badge badge-success"><?= e($sv['trang_thai']) ?></span>
              </td></tr>
              <tr><td>Tài khoản</td><td><?= e($_SESSION['username']) ?></td></tr>
            </table>
          </div>
        </div>

        <!-- Liên kết nhanh -->
        <div class="card mt-12 fade-in">
          <div class="card-body" style="display:flex;flex-direction:column;gap:8px;padding:16px">
            <a href="<?= BASE_URL ?>/student/tien-do" class="btn btn-outline w-100">
              <i class="fas fa-tasks"></i> Xem tiến độ tín chỉ
            </a>
            <a href="<?= BASE_URL ?>/student/diem-hoc-tap" class="btn btn-outline w-100">
              <i class="fas fa-graduation-cap"></i> Xem bảng điểm
            </a>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
