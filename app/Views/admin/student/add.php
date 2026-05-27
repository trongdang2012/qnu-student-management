<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-plus"></i> Thêm Sinh viên</h1>
    </div>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in" style="max-width: 700px;">
      <div class="card-header">
        <h3><i class="fas fa-list-alt"></i> Nhập thông tin sinh viên</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/sinh-vien/add">
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Mã Sinh viên <span style="color: var(--danger);">*</span></label>
            <input type="text" name="ma_sv" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Họ tên <span style="color: var(--danger);">*</span></label>
            <input type="text" name="ho_ten" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ngày sinh</label>
            <input type="date" name="ngay_sinh" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Giới tính</label>
            <select name="gioi_tinh" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
              <option value="Nam">Nam</option>
              <option value="Nữ">Nữ</option>
              <option value="Khác">Khác</option>
            </select>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email</label>
            <input type="email" name="email" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Số điện thoại</label>
            <input type="tel" name="so_dien_thoai" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ngành <span style="color: var(--danger);">*</span></label>
            <input type="text" name="nganh" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Lớp <span style="color: var(--danger);">*</span></label>
            <input type="text" name="lop" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Khóa</label>
            <input type="text" name="khoa" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Niên khóa</label>
            <input type="text" name="nien_khoa" value="<?= NAM_HOC_HIEN_TAI ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Địa chỉ</label>
            <textarea name="dia_chi" rows="3" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); resize: vertical;"></textarea>
          </div>

          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">Thêm sinh viên</button>
            <a href="<?= BASE_URL ?>/admin/sinh-vien" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center;">Hủy</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
