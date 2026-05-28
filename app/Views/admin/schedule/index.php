<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Thời khóa biểu</span>
      </div>
      <h1><i class="fas fa-calendar-alt"></i> Quản lý Thời khóa biểu</h1>
      <p>Quản lý lịch học của các lớp học phần. Hệ thống tự động kiểm tra trùng phòng học, giảng viên, lớp học và ca học.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in" style="margin-bottom:20px">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-clock"></i>
        <div>
          <h3>Số ca học đã xếp</h3>
          <div class="stat-value"><?= count($list) ?> ca</div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-school" style="color:#ffc107"></i>
        <div>
          <h3>Số phòng học sử dụng</h3>
          <div class="stat-value"><?= count($phongsList) ?> phòng</div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745; cursor:pointer" onclick="if(confirm('Xếp lại toàn bộ lịch học tự động cho các lớp trong kỳ này? Lịch cũ sẽ bị xóa.')) location.href='<?= BASE_URL ?>/admin/thoi-khoa-bieu/optimize?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>'">
        <i class="fas fa-wand-magic-sparkles" style="color:#28a745"></i>
        <div>
          <h3>Xếp TKB tự động</h3>
          <div class="stat-value" style="font-size:16px; color:#28a745">⚡ Tự động phân lịch tối ưu</div>
        </div>
      </div>
    </div>

    <!-- Modal Form Thêm/Sửa Lịch học -->
    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content" style="max-width: 550px">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Chỉnh sửa lịch học' : 'Xếp lịch học mới' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">
          <input type="hidden" name="hoc_ky" value="<?= $hocKy ?>">
          <input type="hidden" name="nam_hoc" value="<?= e($namHoc) ?>">

          <div class="form-group">
            <label>Lớp học phần <span style="color:red">*</span></label>
            <select name="lop_hoc_phan_id" class="form-control" required <?= $action === 'edit' ? 'disabled' : '' ?>>
              <option value="">-- Chọn Lớp học phần --</option>
              <?php foreach ($allClasses as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)($item['lop_hoc_phan_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                  <?= e($c['ma_lop_hp']) ?> - <?= e($c['ten_hp']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ($action === 'edit'): ?>
              <input type="hidden" name="lop_hoc_phan_id" value="<?= (int)$item['lop_hoc_phan_id'] ?>">
            <?php endif; ?>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Thứ học <span style="color:red">*</span></label>
              <select name="thu" class="form-control" required>
                <?php for ($t = 2; $t <= 7; $t++): ?>
                  <option value="<?= $t ?>" <?= (int)($item['thu'] ?? 2) === $t ? 'selected' : '' ?>>Thứ <?= $t ?></option>
                <?php endfor; ?>
                <option value="8" <?= (int)($item['thu'] ?? 2) === 8 ? 'selected' : '' ?>>Chủ Nhật</option>
              </select>
            </div>
            <div class="form-group">
              <label>Tiết bắt đầu <span style="color:red">*</span></label>
              <input type="number" name="tiet_bat_dau" class="form-control" value="<?= (int)($item['tiet_bat_dau'] ?? 1) ?>" min="1" max="10" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tiết học <span style="color:red">*</span></label>
              <input type="number" name="so_tiet" class="form-control" value="<?= (int)($item['so_tiet'] ?? 3) ?>" min="1" max="5" required>
            </div>
            <div class="form-group">
              <label>Phòng học <span style="color:red">*</span></label>
              <input type="text" name="phong_hoc" class="form-control" value="<?= e($item['phong_hoc'] ?? '') ?>" required placeholder="VD: A301" style="text-transform: uppercase;">
            </div>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu lịch học</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bộ lọc nâng cao -->
    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="action-bar" style="align-items:flex-end;margin-bottom:0;flex-wrap:wrap;gap:10px">
          <div class="form-group" style="margin:0;min-width:100px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKy === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Năm học</label>
            <select name="nam_hoc" class="form-control">
              <option value="<?= e(NAM_HOC_HIEN_TAI) ?>" <?= $namHoc === NAM_HOC_HIEN_TAI ? 'selected' : '' ?>><?= e(NAM_HOC_HIEN_TAI) ?></option>
              <?php foreach ($listNamHoc as $nh): ?>
                <?php if ($nh['nam_hoc'] === NAM_HOC_HIEN_TAI) continue; ?>
                <option value="<?= e($nh['nam_hoc']) ?>" <?= $namHoc === $nh['nam_hoc'] ? 'selected' : '' ?>><?= e($nh['nam_hoc']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Lọc theo Phòng</label>
            <input type="text" name="phong_hoc" class="form-control" placeholder="Phòng..." value="<?= e($phongFilter) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Lọc Giảng viên</label>
            <input type="text" name="giang_vien" class="form-control" placeholder="Giảng viên..." value="<?= e($gvFilter) ?>">
          </div>
          <div class="form-group search-box" style="margin:0;flex:1;min-width:180px">
            <label style="font-size:12px">Từ khóa</label>
            <input type="text" name="search" class="form-control" placeholder="Lớp HP, tên học phần..." value="<?= e($search) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Xếp lịch thủ công</button>
        </form>
      </div>
    </div>

    <!-- Chế độ chuyển đổi View: Danh sách và Lưới -->
    <div style="margin-bottom: 15px; display:flex; gap:10px" class="fade-in">
      <button class="btn btn-sm btn-primary" id="btnGridView" onclick="switchView('grid')"><i class="fas fa-border-all"></i> Xem dạng Lưới (Khuyên dùng)</button>
      <button class="btn btn-sm btn-secondary" id="btnListView" onclick="switchView('list')"><i class="fas fa-list"></i> Xem dạng Danh sách</button>
    </div>

    <!-- VIEW DẠNG LƯỚI THỜI KHÓA BIỂU -->
    <div id="tkbGridView" class="card fade-in" style="overflow-x: auto;">
      <div class="card-body">
        <?php if (empty($phongsList)): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-calendar-xmark" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không có dữ liệu thời khóa biểu xếp phòng.
          </div>
        <?php else: ?>
          <?php foreach ($phongsList as $phong): ?>
            <div style="margin-bottom: 25px;">
              <h3 style="background:#007bff; color:#fff; padding:6px 12px; border-radius:4px; margin-bottom:10px; display:inline-block"><i class="fas fa-school"></i> Phòng học: <?= e($phong) ?></h3>
              <div class="table-wrap">
                <table class="grid-table" style="border-collapse: collapse; width: 100%; text-align: center;">
                  <thead>
                    <tr style="background:#f8f9fa;">
                      <th style="width: 10%; border: 1px solid #dee2e6;">Tiết / Thứ</th>
                      <?php for ($t = 2; $t <= 7; $t++): ?>
                        <th style="width: 15%; border: 1px solid #dee2e6;">Thứ <?= $t ?></th>
                      <?php endfor; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($tiet = 1; $tiet <= 10; $tiet++): ?>
                      <tr>
                        <td style="font-weight:bold; background:#f8f9fa; border: 1px solid #dee2e6; padding:8px 4px;">Tiết <?= $tiet ?></td>
                        <?php for ($thu = 2; $thu <= 7; $thu++): ?>
                          <?php if (isset($grid[$phong][$thu][$tiet])): ?>
                            <?php 
                              $cell = $grid[$phong][$thu][$tiet]; 
                              // Nếu là tiết bắt đầu của ca, thì ta render block với rowspan
                              if ((int)$cell['tiet_bat_dau'] === $tiet):
                                  $duration = (int)$cell['so_tiet'];
                            ?>
                              <td rowspan="<?= $duration ?>" style="background:#e8f4fd; border: 1px solid #dee2e6; padding:8px; vertical-align:middle; position:relative;">
                                <div style="font-weight:bold; color:#0056b3; font-size:13px;"><?= e($cell['ma_lop_hp']) ?></div>
                                <div style="font-size:12px; margin-top:2px; font-weight:500;"><?= e($cell['ten_hp']) ?></div>
                                <div style="font-size:11px; color:#555; margin-top:4px;"><i class="fas fa-user-tie"></i> <?= e($cell['giang_vien']) ?></div>
                                <div style="margin-top:6px; display:flex; justify-content:center; gap:4px">
                                  <a href="?action=edit&id=<?= (int)$cell['id'] ?>&hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-sm btn-info" style="padding:2px 6px; font-size:10px;"><i class="fas fa-edit"></i></a>
                                  <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/delete" style="display:inline" onsubmit="return confirm('Xóa ca học này?')">
                                    <input type="hidden" name="id" value="<?= (int)$cell['id'] ?>">
                                    <input type="hidden" name="hoc_ky_keep" value="<?= $hocKy ?>">
                                    <input type="hidden" name="nam_hoc_keep" value="<?= e($namHoc) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="padding:2px 6px; font-size:10px;"><i class="fas fa-trash"></i></button>
                                  </form>
                                </div>
                              </td>
                            <?php endif; ?>
                          <?php else: ?>
                            <!-- Chỉ render ô trống nếu không bị đè bởi rowspan trước đó -->
                            <?php 
                              $isCovered = false;
                              for ($prevTiet = 1; $prevTiet < $tiet; $prevTiet++) {
                                  if (isset($grid[$phong][$thu][$prevTiet])) {
                                      $prevCell = $grid[$phong][$thu][$prevTiet];
                                      if ($prevTiet + (int)$prevCell['so_tiet'] > $tiet) {
                                          $isCovered = true;
                                          break;
                                      }
                                  }
                              }
                              if (!$isCovered):
                            ?>
                              <td style="border: 1px solid #dee2e6; color:#ccc; font-size:11px;">—</td>
                            <?php endif; ?>
                          <?php endif; ?>
                        <?php endfor; ?>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- VIEW DẠNG DANH SÁCH -->
    <div id="tkbListView" class="card fade-in" style="display: none;">
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
                  <th>Mã lớp học phần</th>
                  <th>Học phần</th>
                  <th style="text-align:center">Thứ</th>
                  <th style="text-align:center">Tiết học</th>
                  <th>Phòng học</th>
                  <th>Giảng viên giảng dạy</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><code><strong><?= e($row['ma_lop_hp']) ?></strong></code></td>
                    <td><strong><?= e($row['ten_hp']) ?></strong><br><small style="color:#666">Mã HP: <?= e($row['ma_hp']) ?></small></td>
                    <td style="text-align:center; font-weight:bold;">Thứ <?= (int)$row['thu'] === 8 ? 'CN' : (int)$row['thu'] ?></td>
                    <td style="text-align:center">Tiết <?= (int)$row['tiet_bat_dau'] ?> - <?= (int)$row['tiet_bat_dau'] + (int)$row['so_tiet'] - 1 ?></td>
                    <td><span class="badge" style="background:#e9ecef; color:#495057; border: 1px solid #ced4da; font-weight:bold;"><?= e($row['phong_hoc'] ?: '—') ?></span></td>
                    <td><strong><?= e($row['giang_vien'] ?: '—') ?></strong></td>
                    <td style="text-align:center">
                      <div class="table-actions" style="display:flex; justify-content:center; gap:5px">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>&search=<?= urlencode($search) ?>">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/delete" style="display:inline" onsubmit="return confirm('Bạn có muốn xóa ca học này không?')">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <input type="hidden" name="hoc_ky_keep" value="<?= $hocKy ?>">
                          <input type="hidden" name="nam_hoc_keep" value="<?= e($namHoc) ?>">
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
function switchView(view) {
  if (view === 'grid') {
    document.getElementById('tkbGridView').style.display = 'block';
    document.getElementById('tkbListView').style.display = 'none';
    document.getElementById('btnGridView').className = 'btn btn-sm btn-primary';
    document.getElementById('btnListView').className = 'btn btn-sm btn-secondary';
    localStorage.setItem('tkb_view_mode', 'grid');
  } else {
    document.getElementById('tkbGridView').style.display = 'none';
    document.getElementById('tkbListView').style.display = 'block';
    document.getElementById('btnGridView').className = 'btn btn-sm btn-secondary';
    document.getElementById('btnListView').className = 'btn btn-sm btn-primary';
    localStorage.setItem('tkb_view_mode', 'list');
  }
}

// Load view preference
document.addEventListener("DOMContentLoaded", function() {
  const mode = localStorage.getItem('tkb_view_mode') || 'grid';
  switchView(mode);
});

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
