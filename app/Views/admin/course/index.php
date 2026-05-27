<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phần</span>
      </div>
      <h1><i class="fas fa-book"></i> Quản lý học phần</h1>
      <p>Thêm, sửa, xóa và tìm kiếm học phần theo dữ liệu đào tạo Đại học Quy Nhơn.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-layer-group"></i>
        <div>
          <h3>Học phần đang hiển thị</h3>
          <div class="stat-value"><?= count($list) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745">
        <i class="fas fa-award" style="color:#28a745"></i>
        <div>
          <h3>Tổng tín chỉ</h3>
          <div class="stat-value"><?= $totalCredits ?></div>
        </div>
      </div>
    </div>

    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa học phần' : 'Thêm học phần' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <div class="form-row full">
            <div class="form-group">
              <label>Mã học phần <span style="color:red">*</span></label>
              <input type="text" name="ma_hp" class="form-control" value="<?= e($item['ma_hp'] ?? '') ?>" required maxlength="20" placeholder="VD: CNTT010">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Tên học phần <span style="color:red">*</span></label>
              <input type="text" name="ten_hp" class="form-control" value="<?= e($item['ten_hp'] ?? '') ?>" required maxlength="150" placeholder="VD: Lập trình Web">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tín chỉ</label>
              <input type="number" name="so_tin_chi" class="form-control" value="<?= (int)($item['so_tin_chi'] ?? 3) ?>" min="1" max="10">
            </div>
            <div class="form-group">
              <label>Loại học phần</label>
              <select name="loai" class="form-control">
                <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $l): ?>
                  <option value="<?= e($l) ?>" <?= ($item['loai'] ?? 'Bắt buộc') === $l ? 'selected' : '' ?>><?= e($l) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Học kỳ</label>
              <input type="number" name="hoc_ky" class="form-control" value="<?= (int)($item['hoc_ky'] ?? HOC_KY_HIEN_TAI) ?>" min="1" max="8">
            </div>
            <div class="form-group">
              <label>Niên khóa</label>
              <input type="text" name="nien_khoa" class="form-control" value="<?= e($item['nien_khoa'] ?? NAM_HOC_HIEN_TAI) ?>" maxlength="20" placeholder="2021-2025">
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
        <form method="GET" action="<?= BASE_URL ?>/admin/hoc-phan" class="action-bar" style="align-items:flex-end;margin-bottom:0">
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Mã, tên học phần hoặc niên khóa..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <option value="0">Tất cả</option>
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKyFilter === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Loại</label>
            <select name="loai" class="form-control">
              <option value="">Tất cả</option>
              <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $loaiFilter === $l ? 'selected' : '' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
          <a href="<?= BASE_URL ?>/admin/hoc-phan" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm mới</button>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-inbox" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy học phần phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã HP</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">TC</th>
                  <th>Loại</th>
                  <th style="text-align:center">HK</th>
                  <th>Niên khóa</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><code><?= e($row['ma_hp']) ?></code></td>
                    <td><?= e($row['ten_hp']) ?></td>
                    <td style="text-align:center"><?= (int)$row['so_tin_chi'] ?></td>
                    <td><span class="badge"><?= e($row['loai']) ?></span></td>
                    <td style="text-align:center"><?= (int)$row['hoc_ky'] ?></td>
                    <td><?= e($row['nien_khoa'] ?? '') ?></td>
                    <td style="text-align:center">
                      <div class="table-actions">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&search=<?= urlencode($search) ?>">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/delete" style="display:inline" onsubmit="return confirm('Xóa học phần này?')">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <input type="hidden" name="search_keep" value="<?= e($search) ?>">
                          <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</button>
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
    history.replaceState(null, '', '<?= BASE_URL ?>/admin/hoc-phan');
  }
}
function showAddForm() {
  document.getElementById('formModal').classList.add('active');
}
</script>
