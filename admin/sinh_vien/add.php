<?php
/**
 * admin/sinh_vien/add.php - Thêm sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$page_title = 'Thêm Sinh viên';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/alerts.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-plus"></i> Thêm Sinh viên</h1>
    </div>

    <!-- Form Card -->
    <div class="card fade-in" style="max-width: 700px;">
      <div class="card-header">
        <h3><i class="fas fa-form"></i> Nhập thông tin sinh viên</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/sinh_vien/process_add.php">
          
          <!-- Mã SV -->
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-id-card"></i> Mã Sinh viên <span style="color: var(--danger);">*</span>
            </label>
            <input 
              type="text" 
              name="ma_sv" 
              placeholder="VD: SV001, 2023001"
              required
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
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
              placeholder="Nhập họ và tên"
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
              <option value="Nam">Nam</option>
              <option value="Nữ">Nữ</option>
              <option value="Khác">Khác</option>
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
              placeholder="sinh.vien@qnu.edu.vn"
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
              placeholder="0123456789"
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
              placeholder="VD: Công nghệ thông tin"
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
              placeholder="VD: A1, K63A"
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
              placeholder="VD: K63"
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
              placeholder="VD: 2023-2024"
              value="<?= NAM_HOC_HIEN_TAI ?>"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px;"
            >
          </div>

          <!-- Địa chỉ -->
          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text);">
              <i class="fas fa-map-marker-alt"></i> Địa chỉ
            </label>
            <textarea 
              name="dia_chi" 
              placeholder="Nhập địa chỉ đầy đủ"
              rows="3"
              style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 15px; resize: vertical;"
            ></textarea>
          </div>

          <!-- Buttons -->
          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
              <i class="fas fa-save"></i> Thêm sinh viên
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
