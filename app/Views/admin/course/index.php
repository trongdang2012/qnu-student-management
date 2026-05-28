<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phần</span>
      </div>
      <h1><i class="fas fa-book"></i> Quản lý Học phần</h1>
      <p>Thêm, sửa, xóa và tìm kiếm học phần theo dữ liệu tín chỉ Đại học Quy Nhơn.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in" style="margin-bottom: 20px;">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-layer-group"></i>
        <div>
          <h3>Tổng số học phần</h3>
          <div class="stat-value"><?= (int)$totalItems ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745">
        <i class="fas fa-award" style="color:#28a745"></i>
        <div>
          <h3>Trang hiện tại</h3>
          <div class="stat-value"><?= (int)$page ?> / <?= (int)$totalPages ?></div>
        </div>
      </div>
    </div>

    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa học phần' : 'Thêm học phần mới' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <div class="form-row">
            <div class="form-group">
              <label>Mã học phần <span style="color:red">*</span></label>
              <?php 
                $isReadOnly = false;
                if ($action === 'edit' && isset($item['id'])) {
                    // Check xem có lớp học phần không
                    $db = \App\Core\Database::getInstance();
                    $chk = $db->fetch('SELECT COUNT(*) as total FROM lop_hoc_phan WHERE hoc_phan_id = :hp_id', ['hp_id' => $item['id']]);
                    if ($chk && (int)$chk['total'] > 0) {
                        $isReadOnly = true;
                    }
                }
              ?>
              <input type="text" name="ma_hp" class="form-control" value="<?= e($item['ma_hp'] ?? '') ?>" required maxlength="20" placeholder="VD: CNTT001" <?= $isReadOnly ? 'readonly style="background:#e9ecef;"' : '' ?>>
              <?php if ($isReadOnly): ?>
                <small style="color:red;font-size:11px;">Mã học phần không thể sửa vì đã có lớp học phần được tạo.</small>
              <?php endif; ?>
            </div>
            <div class="form-group">
              <label>Tên học phần <span style="color:red">*</span></label>
              <input type="text" name="ten_hp" class="form-control" value="<?= e($item['ten_hp'] ?? '') ?>" required maxlength="150" placeholder="VD: Cấu trúc dữ liệu và giải thuật">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tín chỉ <span style="color:red">*</span></label>
              <input type="number" name="so_tin_chi" class="form-control" value="<?= (int)($item['so_tin_chi'] ?? 3) ?>" min="1" max="10" required>
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
              <label>Số tiết lý thuyết</label>
              <input type="number" name="so_tiet_ly_thuyet" class="form-control" value="<?= (int)($item['so_tiet_ly_thuyet'] ?? 0) ?>" min="0" max="150">
            </div>
            <div class="form-group">
              <label>Số tiết thực hành</label>
              <input type="number" name="so_tiet_thuc_hanh" class="form-control" value="<?= (int)($item['so_tiet_thuc_hanh'] ?? 0) ?>" min="0" max="150">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Học kỳ đề xuất</label>
              <input type="number" name="hoc_ky" class="form-control" value="<?= (int)($item['hoc_ky'] ?? 1) ?>" min="1" max="8">
            </div>
            <div class="form-group">
              <label>Niên khóa</label>
              <input type="text" name="nien_khoa" class="form-control" value="<?= e($item['nien_khoa'] ?? NAM_HOC_HIEN_TAI) ?>" placeholder="VD: 2022-2026">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Khoa/Bộ môn phụ trách <span style="color:red">*</span></label>
              <select name="khoa_phu_trach" class="form-control" required>
                <option value="">-- Chọn Khoa phụ trách --</option>
                <?php foreach (['Kỹ thuật - Công nghệ', 'Kinh tế - Luật', 'Ngoại ngữ', 'Khoa học Tự nhiên', 'Khoa học Xã hội và Nhân văn'] as $kh): ?>
                  <option value="<?= e($kh) ?>" <?= ($item['khoa_phu_trach'] ?? '') === $kh ? 'selected' : '' ?>><?= e($kh) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Môn tiên quyết (Optional)</label>
              <select name="ma_hp_tien_quyet" class="form-control">
                <option value="">Không có môn tiên quyết</option>
                <?php foreach ($allCoursesForPrereq as $c): ?>
                  <?php if (!isset($item['id']) || $c['id'] != $item['id']): ?>
                    <option value="<?= e($c['ma_hp']) ?>" <?= ($item['ma_hp_tien_quyet'] ?? '') === $c['ma_hp'] ? 'selected' : '' ?>>
                      <?= e($c['ma_hp']) ?> - <?= e($c['ten_hp']) ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Mô tả học phần</label>
              <textarea name="mo_ta" class="form-control" rows="3" placeholder="Mô tả tóm tắt nội dung học phần..."><?= e($item['mo_ta'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:normal;cursor:pointer">
              <input type="checkbox" name="trang_thai_hoat_dong" value="1" <?= ($item['trang_thai_hoat_dong'] ?? 1) == 1 ? 'checked' : '' ?>>
              <strong>Học phần đang hoạt động</strong>
            </label>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu dữ liệu</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" action="<?= BASE_URL ?>/admin/hoc-phan" class="action-bar" style="align-items:flex-end;margin-bottom:0;flex-wrap:wrap;gap:10px">
          <div class="form-group search-box" style="margin:0;flex:1;min-width:200px">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Mã học phần hoặc tên học phần..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:110px">
            <label style="font-size:12px">Học kỳ đề xuất</label>
            <select name="hoc_ky" class="form-control">
              <option value="0">Tất cả</option>
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKyFilter === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:120px">
            <label style="font-size:12px">Loại môn</label>
            <select name="loai" class="form-control">
              <option value="">Tất cả</option>
              <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $loaiFilter === $l ? 'selected' : '' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Khoa phụ trách</label>
            <select name="khoa" class="form-control">
              <option value="">Tất cả các Khoa</option>
              <?php foreach (['Kỹ thuật - Công nghệ', 'Kinh tế - Luật', 'Ngoại ngữ', 'Khoa học Tự nhiên', 'Khoa học Xã hội và Nhân văn'] as $kh): ?>
                <option value="<?= e($kh) ?>" <?= $khoaFilter === $kh ? 'selected' : '' ?>><?= e($kh) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/hoc-phan" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm học phần</button>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-inbox" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy học phần nào phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã HP</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">TC</th>
                  <th style="text-align:center">Lý thuyết</th>
                  <th style="text-align:center">Thực hành</th>
                  <th>Học kỳ đề xuất</th>
                  <th>Khoa/Bộ môn phụ trách</th>
                  <th>Môn tiên quyết</th>
                  <th style="text-align:center">Trạng thái</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><code><?= e($row['ma_hp']) ?></code></td>
                    <td><strong><?= e($row['ten_hp']) ?></strong></td>
                    <td style="text-align:center;font-weight:bold"><?= (int)$row['so_tin_chi'] ?></td>
                    <td style="text-align:center"><?= (int)$row['so_tiet_ly_thuyet'] ?> tiết</td>
                    <td style="text-align:center"><?= (int)$row['so_tiet_thuc_hanh'] ?> tiết</td>
                    <td style="text-align:center">Học kỳ <?= (int)$row['hoc_ky'] ?></td>
                    <td><?= e($row['khoa_phu_trach']) ?></td>
                    <td><?= $row['ma_hp_tien_quyet'] ? '<span class="badge" style="background:#e9ecef;color:#495057;border:1px solid #ced4da;">' . e($row['ma_hp_tien_quyet']) . '</span>' : '<span style="color:#999;font-size:12px">Không</span>' ?></td>
                    <td style="text-align:center">
                      <?php if (($row['trang_thai_hoat_dong'] ?? 1) == 1): ?>
                        <span class="badge" style="background:#d4edda;color:#155724;border: 1px solid #c3e6cb;">Hoạt động</span>
                      <?php else: ?>
                        <span class="badge" style="background:#f8d7da;color:#721c24;border: 1px solid #f5c6cb;">Tạm khóa</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                      <div class="table-actions" style="display:flex;justify-content:center;gap:5px">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>&page=<?= $page ?>">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa học phần này không?')">
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

          <!-- Phân trang chuyên nghiệp -->
          <?php if ($totalPages > 1): ?>
            <div style="display:flex;justify-content:center;align-items:center;padding:15px;gap:5px;border-top:1px solid #eee">
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-secondary" href="?page=1&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><i class="fas fa-angles-left"></i> Đầu</a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><i class="fas fa-angle-left"></i> Trước</a>
              <?php endif; ?>

              <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
              ?>
                <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><?= $p ?></a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>">Sau <i class="fas fa-angle-right"></i></a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&loai=<?= urlencode($loaiFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>">Cuối <i class="fas fa-angles-right"></i></a>
              <?php endif; ?>
            </div>
          <?php endif; ?>

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
