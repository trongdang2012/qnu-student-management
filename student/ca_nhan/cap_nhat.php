<?php
/**
 * student/ca_nhan/cap_nhat.php - UC2: Cập nhật SĐT, Email
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db  = getDB();
$sid = (int)$sv['id'];
$errors   = [];
$success  = false;

// ── Xử lý POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sdt   = trim($_POST['so_dien_thoai'] ?? '');

    // Validate
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không hợp lệ.';
    }
    if (!empty($sdt) && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $sdt)) {
        $errors['sdt'] = 'Số điện thoại không hợp lệ (VD: 0912345678).';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE sinh_vien SET email=?, so_dien_thoai=? WHERE id=?");
        $stmt->bind_param('ssi', $email, $sdt, $sid);
        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật thông tin thành công!');
            header('Location: ' . BASE_URL . '/student/ca_nhan/cap_nhat.php');
            exit;
        } else {
            $errors['db'] = 'Có lỗi xảy ra khi lưu dữ liệu.';
        }
        $stmt->close();
    }
    // Nếu lỗi thì giữ lại giá trị người dùng nhập
    $sv['email']         = $email;
    $sv['so_dien_thoai'] = $sdt;
}

$page_title  = 'Cập nhật thông tin';
$active_menu = 'ca_nhan';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container" style="max-width:760px">

    <!-- Tiêu đề -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
        <span>›</span>
        <a href="<?= BASE_URL ?>/student/ca_nhan/thong_tin.php">Cá nhân</a>
        <span>›</span><span>Cập nhật</span>
      </div>
      <h1><i class="fas fa-edit"></i> Cập nhật thông tin</h1>
      <p>Chỉ có thể chỉnh sửa <strong>Email</strong> và <strong>Số điện thoại</strong>. Thông tin khác liên hệ phòng Đào tạo.</p>
    </div>

    <!-- Flash -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-check-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors['db'])): ?>
      <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= e($errors['db']) ?></div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-user-edit"></i> Thông tin có thể chỉnh sửa</h3>
      </div>
      <div class="card-body">
        <form action="" method="POST" id="updateForm" data-validate-form novalidate>

          <!-- Thông tin chỉ đọc -->
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Họ và tên</label>
              <input type="text" class="form-control" value="<?= e($sv['ho_ten']) ?>" disabled>
            </div>
            <div class="form-group">
              <label>MSSV</label>
              <input type="text" class="form-control" value="<?= e($sv['ma_sv']) ?>" disabled>
            </div>
          </div>
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Ngày sinh</label>
              <input type="text" class="form-control" value="<?= $sv['ngay_sinh'] ? date('d/m/Y', strtotime($sv['ngay_sinh'])) : '' ?>" disabled>
            </div>
            <div class="form-group">
              <label>Lớp</label>
              <input type="text" class="form-control" value="<?= e($sv['lop']) ?>" disabled>
            </div>
          </div>

          <hr style="border:none;border-top:1px dashed var(--border);margin:16px 0">
          <p class="text-muted mb-16" style="font-size:14px;"><i class="fas fa-pencil-alt" style="color:var(--primary)"></i> Chỉnh sửa các trường bên dưới:</p>

          <!-- Email -->
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i> Email liên hệ <span class="required">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   value="<?= e($sv['email'] ?? '') ?>"
                   placeholder="example@gmail.com"
                   data-validate="required email"
                   autocomplete="email">
            <?php if (isset($errors['email'])): ?>
              <span class="form-error" style="display:block"><?= e($errors['email']) ?></span>
            <?php else: ?>
              <span class="form-hint">Địa chỉ email dùng để nhận thông báo từ trường.</span>
            <?php endif; ?>
          </div>

          <!-- SĐT -->
          <div class="form-group">
            <label for="so_dien_thoai">
              <i class="fas fa-phone"></i> Số điện thoại
            </label>
            <input type="tel" id="so_dien_thoai" name="so_dien_thoai"
                   class="form-control <?= isset($errors['sdt']) ? 'is-invalid' : '' ?>"
                   value="<?= e($sv['so_dien_thoai'] ?? '') ?>"
                   placeholder="0912 345 678"
                   data-validate="phone"
                   autocomplete="tel">
            <?php if (isset($errors['sdt'])): ?>
              <span class="form-error" style="display:block"><?= e($errors['sdt']) ?></span>
            <?php else: ?>
              <span class="form-hint">Số điện thoại liên hệ (không bắt buộc).</span>
            <?php endif; ?>
          </div>

          <!-- Nút -->
          <div style="display:flex;gap:12px;margin-top:8px">
            <button type="submit" class="btn btn-primary" id="btnSave">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="<?= BASE_URL ?>/student/ca_nhan/thong_tin.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Quay lại
            </a>
          </div>

        </form>
      </div>
      <div class="card-footer">
        <p class="text-muted" style="font-size:13px;">
          <i class="fas fa-info-circle"></i>
          Muốn thay đổi thông tin khác (họ tên, ngày sinh, lớp...) vui lòng liên hệ
          <strong>Phòng Đào tạo</strong> — ĐT: (0256) 3846 344.
        </p>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
