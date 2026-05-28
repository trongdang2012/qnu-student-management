<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-plus"></i> Thêm Tài khoản</h1>
    </div>

    <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
      <div class="alert alert-danger fade-in" style="margin-bottom: 20px; padding: 15px; border-radius: var(--radius-sm); border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24;">
        <ul style="margin: 0; padding-left: 20px;">
          <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card fade-in" style="max-width: 600px;">
      <div class="card-header">
        <h3><i class="fas fa-form"></i> Nhập thông tin tài khoản</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/users/process-add">
          
          <!-- Username -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-at"></i> Username <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="text" 
              name="username" 
              placeholder="Nhập username (VD: sv001, admin01)"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
            <small style="color: var(--text-muted); margin-top: 5px; display: block;">Username phải duy nhất trong hệ thống</small>
          </div>

          <!-- Email -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-envelope"></i> Email <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="email" 
              name="email" 
              placeholder="Nhập email đăng ký của người dùng"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Password -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-lock"></i> Mật khẩu <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="password" 
              name="password" 
              placeholder="Tối thiểu 6 ký tự, gồm in hoa, số, ký tự đặc biệt"
              minlength="6"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Confirm Password -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-lock"></i> Xác nhận mật khẩu <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="password" 
              name="password_confirm" 
              placeholder="Xác nhận lại mật khẩu"
              minlength="6"
              required
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
              <option value="">-- Chọn role --</option>
              <option value="student">🎓 Sinh viên</option>
              <option value="admin">👤 Admin</option>
            </select>
          </div>

          <!-- Buttons -->
          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
              <i class="fas fa-save"></i> Thêm tài khoản
            </button>
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center;">
              <i class="fas fa-times"></i> Hủy
            </a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
