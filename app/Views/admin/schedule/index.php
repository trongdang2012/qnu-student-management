<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Thời khóa biểu</span>
      </div>
      <h1><i class="fas fa-calendar-alt"></i> Quản lý thời khóa biểu</h1>
      <p>Quản trị lịch học theo sinh viên, học phần, phòng học và giảng viên.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['optimize_log'])): ?>
      <div class="card fade-in" style="border-left:4px solid #28a745">
        <div class="card-body" style="padding:14px 18px">
          <strong><i class="fas fa-list-check"></i> Kết quả xếp tự động</strong>
          <ul style="margin:10px 0 0 20px">
            <?php foreach ($_SESSION['optimize_log'] as $line): ?>
              <li><?= e($line) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php unset($_SESSION['optimize_log']); ?>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-calendar-check"></i>
        <div>
          <h3>Lịch đang hiển thị</h3>
          <div class="stat-value"><?= count($list) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-clock" style="color:#ffc107"></i>
        <div>
          <h3>HK / Năm học</h3>
          <div class="stat-value" style="font-size:22px">HK<?= $hocKy ?></div>
        </div>
      </div>
    </div>

    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa lịch học' : 'Thêm lịch học' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <div class="form-row full">
            <div class="form-group">
              <label>Sinh viên <span style="color:red">*</span></label>
              <select name="sinh_vien_id" class="form-control" required>
                <option value="">-- Chọn sinh viên --</option>
                <?php foreach ($allStudents as $sv): ?>
                  <option value="<?= (int)$sv['id'] ?>" <?= (int)($item['sinh_vien_id'] ?? 0) === (int)$sv['id'] ? 'selected' : '' ?>>
                    <?= e($sv['ma_sv'] . ' - ' . $sv['ho_ten'] . ' (' . $sv['lop'] . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Học phần <span style="color:red">*</span></label>
              <select name="hoc_phan_id" class="form-control" required>
                <option value="">-- Chọn học phần --</option>
                <?php foreach ($allHocPhan as $hp): ?>
                  <option value="<?= (int)$hp['id'] ?>" <?= (int)($item['hoc_phan_id'] ?? 0) === (int)$hp['id'] ? 'selected' : '' ?>>
                    <?= e($hp['ma_hp'] . ' - ' . $hp['ten_hp'] . ' (' . $hp['so_tin_chi'] . ' TC)') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Thứ</label>
              <select name="thu" class="form-control">
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                  <option value="<?= $thu ?>" <?= (int)($item['thu'] ?? 2) === $thu ? 'selected' : '' ?>><?= e(tenThu($thu)) ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Tiết bắt đầu</label>
              <input type="number" name="tiet_bat_dau" class="form-control" value="<?= (int)($item['tiet_bat_dau'] ?? 1) ?>" min="1" max="10" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tiết</label>
              <input type="number" name="so_tiet" class="form-control" value="<?= (int)($item['so_tiet'] ?? 3) ?>" min="1" max="5">
            </div>
            <div class="form-group">
              <label>Phòng học</label>
              <input type="text" name="phong_hoc" class="form-control" value="<?= e($item['phong_hoc'] ?? '') ?>" maxlength="20" placeholder="VD: A301">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Giảng viên</label>
              <input type="text" name="giang_vien" class="form-control" value="<?= e($item['giang_vien'] ?? '') ?>" maxlength="100" placeholder="VD: TS. Nguyễn Văn Hùng">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Học kỳ</label>
              <input type="number" name="hoc_ky" class="form-control" value="<?= (int)($item['hoc_ky'] ?? $hocKy) ?>" min="1" max="8">
            </div>
            <div class="form-group">
              <label>Năm học</label>
              <input type="text" name="nam_hoc" class="form-control" value="<?= e($item['nam_hoc'] ?? $namHoc) ?>" maxlength="20">
            </div>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="action-bar" style="align-items:flex-end;margin-bottom:0">
          <div class="form-group" style="margin:0;min-width:120px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKy === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Năm học</label>
            <select name="nam_hoc" class="form-control">
              <option value="<?= e(NAM_HOC_HIEN_TAI) ?>" <?= $namHoc === NAM_HOC_HIEN_TAI ? 'selected' : '' ?>><?= e(NAM_HOC_HIEN_TAI) ?></option>
              <?php foreach ($listNamHoc as $nh): ?>
                <?php if ($nh['nam_hoc'] === NAM_HOC_HIEN_TAI) continue; ?>
                <option value="<?= e($nh['nam_hoc']) ?>" <?= $namHoc === $nh['nam_hoc'] ? 'selected' : '' ?>><?= e($nh['nam_hoc']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Sinh viên, học phần, phòng, giảng viên..." value="<?= e($search) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm mới</button>
          <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu/optimize?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-warning">
            <i class="fas fa-wand-magic-sparkles"></i> Xếp tự động
          </a>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-calendar-xmark" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không có lịch học phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Sinh viên</th>
                  <th>Học phần</th>
                  <th style="text-align:center">Thứ</th>
                  <th style="text-align:center">Tiết</th>
                  <th>Phòng</th>
                  <th>Giảng viên</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><small><?= e($row['ma_sv'] . ' - ' . $row['ho_ten']) ?></small></td>
                    <td><code><?= e($row['ma_hp']) ?></code><br><small><?= e($row['ten_hp']) ?></small></td>
                    <td style="text-align:center"><?= e(tenThu((int)$row['thu'])) ?></td>
                    <td style="text-align:center">T<?= (int)$row['tiet_bat_dau'] ?>-T<?= (int)$row['tiet_bat_dau'] + (int)$row['so_tiet'] - 1 ?></td>
                    <td><?= e($row['phong_hoc'] ?: '—') ?></td>
                    <td><?= e($row['giang_vien'] ?: '—') ?></td>
                    <td style="text-align:center">
                      <div class="table-actions">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>&search=<?= urlencode($search) ?>">
                          <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/delete" style="display:inline" onsubmit="return confirm('Xóa lịch học này?')">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <input type="hidden" name="hoc_ky_keep" value="<?= $hocKy ?>">
                          <input type="hidden" name="nam_hoc_keep" value="<?= e($namHoc) ?>">
                          <input type="hidden" name="search_keep" value="<?= e($search) ?>">
                          <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

<script>
function closeModal() {
  document.getElementById('formModal').classList.remove('active');
  if (new URLSearchParams(location.search).has('action')) {
    history.replaceState(null, '', '<?= BASE_URL ?>/admin/thoi-khoa-bieu');
  }
}
function showAddForm() {
  document.getElementById('formModal').classList.add('active');
}
</script>
