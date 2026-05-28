<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/hoc-phi">Học phí</a>
        <span>›</span><span>Cập nhật học phí</span>
      </div>
      <h1><i class="fas fa-edit"></i> Cập nhật mức học phí</h1>
      <p>Sửa lại số tiền và hạn nộp của từng bản ghi học phí.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in" style="margin-bottom:24px;">
      <div class="card-header"><h3><i class="fas fa-filter"></i> Áp học phí theo học phần</h3></div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phi/cap-nhat/save" class="form-grid" style="gap:12px;">
          <input type="hidden" name="action" value="apply_course_rate">

          <div class="form-group" style="flex:2;">
            <label>Học phần</label>
            <select name="course_id" class="form-control" required>
              <option value="">Chọn học phần</option>
              <?php foreach ($courseList as $course): ?>
                <option value="<?= (int)$course['id'] ?>"><?= e($course['ma_hp'] . ' - ' . $course['ten_hp'] . ' (' . $course['so_tin_chi'] . ' tín chỉ)') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Học kỳ</label>
            <select name="hoc_ky" class="form-control" required>
              <option value="">Chọn học kỳ</option>
              <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>">HK <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Năm học</label>
            <input type="text" name="nam_hoc" class="form-control" placeholder="VD: 2024-2025" required>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Đơn giá/tín chỉ</label>
            <input type="number" name="don_gia" class="form-control" step="1000" min="0" placeholder="VD: 150000" required>
          </div>
          <div class="form-group" style="flex:1;">
            <label>Hạn nộp</label>
            <input type="date" name="han_nop" class="form-control">
          </div>
          <div class="form-group" style="align-self:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-calculator"></i> Áp dụng học phí</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($editRecord): ?>
      <div class="card fade-in" style="margin-bottom:24px;">
        <div class="card-header"><h3><i class="fas fa-pencil-alt"></i> Sửa học phí sinh viên</h3></div>
        <div class="card-body">
          <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phi/cap-nhat/save" style="max-width:560px;">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)$editRecord['id'] ?>">
            <div class="form-group">
              <label>Sinh viên</label>
              <input type="text" class="form-control" value="<?= e($editRecord['ma_sv'] . ' - ' . $editRecord['ho_ten']) ?>" readonly>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Học kỳ</label>
                <input type="text" class="form-control" value="HK <?= (int)$editRecord['hoc_ky'] ?> / <?= e($editRecord['nam_hoc']) ?>" readonly>
              </div>
              <div class="form-group">
                <label>Trạng thái hiện tại</label>
                <input type="text" class="form-control" value="<?= e($editRecord['trang_thai']) ?>" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Số tiền học phí (VND)</label>
                <input type="number" step="1000" min="0" name="so_tien" class="form-control" value="<?= (float)$editRecord['so_tien'] ?>" required>
              </div>
              <div class="form-group">
                <label>Hạn nộp</label>
                <input type="date" name="han_nop" class="form-control" value="<?= e($editRecord['han_nop']) ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
            <a href="<?= BASE_URL ?>/admin/hoc-phi/cap-nhat" class="btn btn-secondary">Hủy</a>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-header"><h3><i class="fas fa-table"></i> Danh sách học phí</h3></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>MSV</th>
              <th>Họ tên</th>
              <th>Khoa</th>
              <th>Ngành</th>
              <th>Lớp</th>
              <th>Học phần</th>
              <th style="text-align:center">HK</th>
              <th style="text-align:right">Số tiền</th>
              <th style="text-align:right">Đã nộp</th>
              <th>Hạn nộp</th>
              <th>Trạng thái</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($fees)): ?>
              <tr><td colspan="11" style="text-align:center;padding:24px;color:var(--text-muted)">Chưa có dữ liệu học phí.</td></tr>
            <?php else: ?>
              <?php foreach ($fees as $fee): ?>
                <tr>
                  <td><?= e($fee['ma_sv']) ?></td>
                  <td><?= e($fee['ho_ten']) ?></td>
                  <td><?= e($fee['khoa']) ?></td>
                  <td><?= e($fee['nganh']) ?></td>
                  <td><?= e($fee['lop']) ?></td>
                  <td><?= e($fee['ma_hp'] ? $fee['ma_hp'] . ' - ' . $fee['ten_hp'] : '—') ?></td>
                  <td style="text-align:center">HK <?= (int)$fee['hoc_ky'] ?> / <?= e($fee['nam_hoc']) ?></td>
                  <td style="text-align:right"><?= formatMoney((float)$fee['so_tien']) ?></td>
                  <td style="text-align:right;color:var(--success)"><?= formatMoney((float)$fee['da_nop']) ?></td>
                  <td style="text-align:center"><?= e($fee['han_nop'] ?: '—') ?></td>
                  <td style="text-align:center"><span class="badge badge-<?= $fee['trang_thai'] === 'Đã nộp' ? 'success' : ($fee['trang_thai'] === 'Nợ' ? 'danger' : 'warning') ?>"><?= e($fee['trang_thai']) ?></span></td>
                  <td style="text-align:center"><a class="btn btn-sm btn-info" href="<?= BASE_URL ?>/admin/hoc-phi/cap-nhat?action=edit&id=<?= (int)$fee['id'] ?>">Sửa</a></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
