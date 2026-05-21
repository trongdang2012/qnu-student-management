<?php
/**
 * admin/sinh_vien/edit.php - Sửa sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM sinh_vien WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    $_SESSION['errors'] = ['Sinh viên không tồn tại'];
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}

$page_title = 'Sửa Sinh viên';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/alerts.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-edit"></i> Sửa Sinh viên</h1>
    </div>

    <!-- Form Card -->
    <div class="card fade-in" style="max-width: 700px;">
      <div class="card-header">
        <h3><i class="fas fa-form"></i> Cập nhật thông tin sinh viên</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/sinh_vien/process_edit.php">
          <input type="hidden" name="id" value="<?= $student['id'] ?>">
          
          <!-- Mã SV (readonly) -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-id-card"></i> Mã Sinh viên
            </label>
            <input 
              type="text" 
              value="<?= e($student['ma_sv']) ?>"
              readonly
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #f5f5f5; color: #666;"
            >
          </div>

          <!-- Họ tên -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-user"></i> Họ tên <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="text" 
              name="ho_ten" 
              value="<?= e($student['ho_ten']) ?>"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Ngày sinh -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-birthday-cake"></i> Ngày sinh
            </label>
            <input 
              type="date" 
              name="ngay_sinh"
              value="<?= $student['ngay_sinh'] ?? '' ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Giới tính -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-venus-mars"></i> Giới tính
            </label>
            <select 
              name="gioi_tinh"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #fff; cursor: pointer;"
            >
              <option value="Nam" <?= $student['gioi_tinh'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
              <option value="Nữ" <?= $student['gioi_tinh'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
              <option value="Khác" <?= $student['gioi_tinh'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
            </select>
          </div>

          <!-- Email -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-envelope"></i> Email
            </label>
            <input 
              type="email" 
              name="email" 
              value="<?= e($student['email'] ?? '') ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Số điện thoại -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-phone"></i> Số điện thoại
            </label>
            <input 
              type="tel" 
              name="so_dien_thoai" 
              value="<?= e($student['so_dien_thoai'] ?? '') ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Ngành -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-book"></i> Ngành <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="text" 
              name="nganh" 
              value="<?= e($student['nganh'] ?? '') ?>"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Lớp -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-users"></i> Lớp <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="text" 
              name="lop" 
              value="<?= e($student['lop'] ?? '') ?>"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Khóa -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-graduation-cap"></i> Khóa
            </label>
            <input 
              type="text" 
              name="khoa" 
              value="<?= e($student['khoa'] ?? '') ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Niên khóa -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-calendar"></i> Niên khóa
            </label>
            <input 
              type="text" 
              name="nien_khoa" 
              value="<?= e($student['nien_khoa'] ?? NAM_HOC_HIEN_TAI) ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Trạng thái -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-flag"></i> Trạng thái <span style="color: var(--danger);">*</span>
            </label>
            <select 
              name="trang_thai"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #fff; cursor: pointer;"
            >
              <option value="Đang học" <?= $student['trang_thai'] === 'Đang học' ? 'selected' : '' ?>>Đang học</option>
              <option value="Tạm dừng" <?= $student['trang_thai'] === 'Tạm dừng' ? 'selected' : '' ?>>Tạm dừng</option>
              <option value="Tốt nghiệp" <?= $student['trang_thai'] === 'Tốt nghiệp' ? 'selected' : '' ?>>Tốt nghiệp</option>
              <option value="Thôi học" <?= $student['trang_thai'] === 'Thôi học' ? 'selected' : '' ?>>Thôi học</option>
            </select>
          </div>

          <!-- Địa chỉ -->
          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-map-marker-alt"></i> Địa chỉ
            </label>
            <textarea 
              name="dia_chi" 
              rows="3"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; resize: vertical;"
            ><?= e($student['dia_chi'] ?? '') ?></textarea>
          </div>

          <!-- Buttons -->
          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
              <i class="fas fa-save"></i> Cập nhật
            </button>
            <a href="<?= BASE_URL ?>/admin/sinh_vien/index.php" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center;">
              <i class="fas fa-times"></i> Hủy
            </a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
