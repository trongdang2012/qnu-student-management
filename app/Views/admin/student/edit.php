<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <div class="page-title fade-in">
      <h1><i class="fas fa-user-edit"></i> Sửa Sinh viên</h1>
    </div>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in" style="max-width: 700px;">
      <div class="card-header">
        <h3><i class="fas fa-list-alt"></i> Cập nhật thông tin sinh viên</h3>
      </div>

      <div class="card-body" style="padding: 30px;">
        <form method="POST" action="<?= BASE_URL ?>/admin/sinh-vien/edit">
          <input type="hidden" name="id" value="<?= $student['id'] ?>">
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Mã Sinh viên</label>
            <input type="text" value="<?= e($student['ma_sv']) ?>" readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: #f5f5f5; color: #666;">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Họ tên <span style="color: var(--danger);">*</span></label>
            <input type="text" name="ho_ten" value="<?= e($student['ho_ten']) ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ngày sinh</label>
            <input type="date" name="ngay_sinh" value="<?= $student['ngay_sinh'] ?? '' ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Giới tính</label>
            <select name="gioi_tinh" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
              <option value="Nam" <?= $student['gioi_tinh'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
              <option value="Nữ" <?= $student['gioi_tinh'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
              <option value="Khác" <?= $student['gioi_tinh'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
            </select>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email</label>
            <input type="email" name="email" value="<?= e($student['email'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Số điện thoại</label>
            <input type="tel" name="so_dien_thoai" value="<?= e($student['so_dien_thoai'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ngành <span style="color: var(--danger);">*</span></label>
            <input type="text" name="nganh" value="<?= e($student['nganh'] ?? '') ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Lớp <span style="color: var(--danger);">*</span></label>
            <input type="text" name="lop" value="<?= e($student['lop'] ?? '') ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Khóa</label>
            <input type="text" name="khoa" value="<?= e($student['khoa'] ?? '') ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Niên khóa</label>
            <input type="text" name="nien_khoa" value="<?= e($student['nien_khoa'] ?? NAM_HOC_HIEN_TAI) ?>" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Trạng thái <span style="color: var(--danger);">*</span></label>
            <select name="trang_thai" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm);">
              <option value="Đang học" <?= $student['trang_thai'] === 'Đang học' ? 'selected' : '' ?>>Đang học</option>
              <option value="Tạm dừng" <?= $student['trang_thai'] === 'Tạm dừng' ? 'selected' : '' ?>>Tạm dừng</option>
              <option value="Tốt nghiệp" <?= $student['trang_thai'] === 'Tốt nghiệp' ? 'selected' : '' ?>>Tốt nghiệp</option>
              <option value="Thôi học" <?= $student['trang_thai'] === 'Thôi học' ? 'selected' : '' ?>>Thôi học</option>
            </select>
          </div>

          <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Địa chỉ</label>
            <textarea name="dia_chi" rows="3" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); resize: vertical;"><?= e($student['dia_chi'] ?? '') ?></textarea>
          </div>

          <div style="display: flex; gap: 10px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">Cập nhật</button>
            <a href="<?= BASE_URL ?>/admin/sinh-vien" class="btn btn-secondary" style="flex: 1; padding: 12px; text-align: center;">Hủy</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
