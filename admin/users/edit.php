<?php
/**
 * admin/users/edit.php - Sửa tài khoản
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['errors'] = ['Tài khoản không tồn tại'];
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

$page_title = 'Sửa Tài khoản';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/alerts.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-edit"></i> Sửa Tài khoản</h1>
    </div>

    <!-- Form Card -->
    <div class="card fade-in" style="max-width: 600px;">
      <div class="card-header">
        <h3><i class="fas fa-form"></i> Cập nhật thông tin tài khoản</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/users/process_edit.php">
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
          
          <!-- ID (readonly) -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-hash"></i> ID
            </label>
            <input 
              type="text" 
              value="<?= $user['id'] ?>"
              readonly
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #f5f5f5; color: #666;"
            >
          </div>

          <!-- Username (readonly) -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-at"></i> Username
            </label>
            <input 
              type="text" 
              value="<?= e($user['username']) ?>"
              readonly
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #f5f5f5; color: #666;"
            >
          </div>

          <!-- New Password (optional) -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-lock"></i> Mật khẩu mới (để trống nếu không đổi)
            </label>
            <input 
              type="password" 
              name="password" 
              placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
              minlength="6"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Confirm New Password -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-lock"></i> Xác nhận mật khẩu mới
            </label>
            <input 
              type="password" 
              name="password_confirm" 
              placeholder="Xác nhận lại mật khẩu mới"
              minlength="6"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Role -->
          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-shield-alt"></i> Role (Quyền) <span style="color: var(--danger);">*</span>
            </label>
            <select 
              name="role" 
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; background: #fff; cursor: pointer;"
            >
              <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>🎓 Sinh viên</option>
              <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>👤 Admin</option>
            </select>
          </div>

          <!-- Buttons -->
          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
              <i class="fas fa-save"></i> Cập nhật
            </button>
            <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center;">
              <i class="fas fa-times"></i> Hủy
            </a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
