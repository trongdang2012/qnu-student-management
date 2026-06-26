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

    <?php
      $courseStats = $courseStats ?? [];
      $coursesWithoutClasses = $coursesWithoutClasses ?? [];
      $activeTotal = (int)($courseStats['active_total'] ?? 0);
      $creditTotal = (int)($courseStats['credit_total'] ?? 0);
      $withoutClassesTotal = (int)($courseStats['without_classes'] ?? 0);
    ?>

    <style>
      .ops-panel{display:grid;grid-template-columns:1.4fr 1fr;gap:16px;margin-bottom:20px}
      .ops-card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
      .ops-card h3{margin:0 0 12px;font-size:16px;color:#111827;display:flex;align-items:center;gap:8px}
      .ops-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
      .ops-metric{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:12px}
      .ops-metric span{display:block;font-size:12px;color:#6b7280;margin-bottom:6px}
      .ops-metric strong{font-size:22px;color:#111827}
      .ops-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}
      .ops-list li{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #f1f5f9;padding:8px 0;font-size:13px}
      .ops-list li:last-child{border-bottom:0}
      .ops-tag{display:inline-flex;align-items:center;border-radius:999px;padding:2px 8px;font-size:12px;background:#eef2ff;color:#3730a3;white-space:nowrap}
      @media (max-width: 900px){.ops-panel{grid-template-columns:1fr}.ops-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
    </style>

    <div class="ops-panel fade-in">
      <div class="ops-card">
        <h3><i class="fas fa-chart-line"></i> Tổng quan danh mục học phần</h3>
        <div class="ops-metrics">
          <div class="ops-metric"><span>Đang hoạt động</span><strong><?= $activeTotal ?></strong></div>
          <div class="ops-metric"><span>Tổng tín chỉ</span><strong><?= $creditTotal ?></strong></div>
          <div class="ops-metric"><span>Có tiên quyết</span><strong><?= (int)($courseStats['prerequisite_total'] ?? 0) ?></strong></div>
          <div class="ops-metric"><span>Chưa mở lớp</span><strong><?= $withoutClassesTotal ?></strong></div>
        </div>
      </div>
      <div class="ops-card">
        <h3><i class="fas fa-triangle-exclamation"></i> Cần rà soát trước kỳ đăng ký</h3>
        <?php if (empty($coursesWithoutClasses)): ?>
          <p style="margin:0;color:#16a34a;font-size:13px">Tất cả học phần hoạt động đã có ít nhất một lớp học phần.</p>
        <?php else: ?>
          <ul class="ops-list">
            <?php foreach ($coursesWithoutClasses as $c): ?>
              <li>
                <span><strong><?= e($c['ma_hp']) ?></strong> - <?= e($c['ten_hp']) ?></span>
                <span class="ops-tag">HK<?= (int)$c['hoc_ky'] ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-layer-group"></i>
        <div>
          <h3>Tổng số học phần</h3>
          <div class="stat-value"><?= (int)$totalItems ?></div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-award"></i>
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
          <input type="hidden" name="khoa_id_keep" value="<?= (int)$khoa_id ?>">
          <input type="hidden" name="nganh_id_keep" value="<?= (int)$nganh_id ?>">

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
          
          <div class="form-group" style="margin:0;min-width:200px">
            <label style="font-size:12px;font-weight:600">Chọn Khoa</label>
            <select name="khoa_id" class="form-control" onchange="this.form.search.value=''; this.form.submit()">
              <option value="0">-- Chọn Khoa --</option>
              <?php foreach ($faculties as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $khoa_id === (int)$f['id'] ? 'selected' : '' ?>><?= e($f['ten_khoa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group" style="margin:0;min-width:200px">
            <label style="font-size:12px;font-weight:600">Chọn Ngành</label>
            <select name="nganh_id" class="form-control" onchange="this.form.search.value=''; this.form.submit()">
              <option value="0">-- Chọn Ngành --</option>
              <?php foreach ($majors as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $nganh_id === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['ten_nganh']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group search-box" style="margin:0;flex:1;min-width:200px">
            <label style="font-size:12px;font-weight:600">Tìm kiếm toàn trường</label>
            <input type="text" name="search" class="form-control" placeholder="Mã hoặc tên học phần..." value="<?= e($search) ?>">
          </div>

          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm / Lọc</button>
          <a href="<?= BASE_URL ?>/admin/hoc-phan" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-warning" onclick="showDuplicateModal()"><i class="fas fa-copy"></i> Nhân bản CTĐT</button>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm học phần</button>
        </form>
      </div>
    </div>

    <!-- PHẦN HIỂN THỊ DANH SÁCH -->
    <?php if ($is_ctdt_mode): ?>
      <!-- CHẾ ĐỘ XEM CHƯƠNG TRÌNH ĐÀO TẠO CỦA NGÀNH -->
      <?php
      $grouped = [];
      for ($hk = 1; $hk <= 8; $hk++) {
          $grouped[$hk] = [];
      }
      foreach ($list as $row) {
          $hk = (int)($row['hoc_ky_ctdt'] ?? 1);
          if ($hk >= 1 && $hk <= 8) {
              $grouped[$hk][] = $row;
          }
      }
      ?>
      <div class="ctdt-container fade-in">
        <div style="background:#eef2ff; color:#3730a3; padding:12px 20px; border-radius:8px; margin-bottom:15px; font-weight:500; font-size:14px; border:1px solid #c7d2fe;">
          <i class="fas fa-info-circle"></i> Đang hiển thị Chương trình đào tạo của Ngành. Bấm vào học phần để xem chi tiết, sửa hoặc xóa.
        </div>
        <?php for ($hk = 1; $hk <= 8; $hk++): ?>
          <div class="card" style="margin-bottom:20px; border: 1px solid var(--border); border-radius:8px; overflow:hidden;">
            <div class="card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:12px 20px;">
              <h3 style="margin:0; font-size:15px; color:var(--primary); font-weight:600;">
                <i class="fas fa-layer-group"></i> Học kỳ <?= $hk ?>
              </h3>
            </div>
            <div class="card-body" style="padding:0;">
              <?php if (empty($grouped[$hk])): ?>
                <div style="padding:20px; text-align:center; color:#94a3b8; font-size:13px;">Chưa có học phần nào trong học kỳ này.</div>
              <?php else: ?>
                <div class="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th style="width:120px;">Mã HP</th>
                        <th>Tên học phần</th>
                        <th style="text-align:center; width:60px;">TC</th>
                        <th style="text-align:center; width:100px;">Lý thuyết</th>
                        <th style="text-align:center; width:100px;">Thực hành</th>
                        <th>Khoa phụ trách</th>
                        <th>Môn tiên quyết</th>
                        <th style="text-align:center; width:100px;">Trạng thái</th>
                        <th style="text-align:center; width:180px;">Hành động</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($grouped[$hk] as $row): ?>
                        <tr class="hover-row">
                          <td><code><?= e($row['ma_hp']) ?></code></td>
                          <td><strong style="color:var(--text-dark);"><?= e($row['ten_hp']) ?></strong></td>
                          <td style="text-align:center;font-weight:bold;color:var(--primary);"><?= (int)$row['so_tin_chi'] ?></td>
                          <td style="text-align:center"><?= (int)$row['so_tiet_ly_thuyet'] ?> tiết</td>
                          <td style="text-align:center"><?= (int)$row['so_tiet_thuc_hanh'] ?> tiết</td>
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
                              <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&khoa_id=<?= $khoa_id ?>&nganh_id=<?= $nganh_id ?>">
                                <i class="fas fa-edit"></i> Sửa
                              </a>
                              <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa học phần này không?')">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="khoa_id_keep" value="<?= (int)$khoa_id ?>">
                                <input type="hidden" name="nganh_id_keep" value="<?= (int)$nganh_id ?>">
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
        <?php endfor; ?>
      </div>

    <?php elseif ($search !== ''): ?>
      <!-- CHẾ ĐỘ TÌM KIẾM TOÀN TRƯỜNG -->
      <div class="card fade-in">
        <div class="card-header" style="background:#fafafa; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0;color:var(--primary);"><i class="fas fa-search"></i> Kết quả tìm kiếm</h3>
          <span class="badge-count"><?= $totalItems ?> kết quả</span>
        </div>
        <div class="card-body" style="padding:0">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã HP</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">TC</th>
                  <th style="text-align:center">Lý thuyết</th>
                  <th style="text-align:center">Thực hành</th>
                  <th>Khoa/Bộ môn phụ trách</th>
                  <th>Môn tiên quyết</th>
                  <th style="text-align:center">Trạng thái</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr class="hover-row">
                    <td><code><?= e($row['ma_hp']) ?></code></td>
                    <td><strong style="color:var(--text-dark);"><?= e($row['ten_hp']) ?></strong></td>
                    <td style="text-align:center;font-weight:bold;color:var(--primary);"><?= (int)$row['so_tin_chi'] ?></td>
                    <td style="text-align:center"><?= (int)$row['so_tiet_ly_thuyet'] ?> tiết</td>
                    <td style="text-align:center"><?= (int)$row['so_tiet_thuc_hanh'] ?> tiết</td>
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
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&search=<?= urlencode($search) ?>">
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
                <a class="btn btn-sm btn-secondary" href="?page=1&search=<?= urlencode($search) ?>"><i class="fas fa-angles-left"></i> Đầu</a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-angle-left"></i> Trước</a>
              <?php endif; ?>

              <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
              ?>
                <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Sau <i class="fas fa-angle-right"></i></a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>">Cuối <i class="fas fa-angles-right"></i></a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php else: ?>
      <!-- CHƯA CHỌN GÌ -->
      <div class="card fade-in" style="padding:40px; text-align:center; color:#64748b; border: 1px dashed #cbd5e1; border-radius:8px;">
        <i class="fas fa-folder-open" style="font-size:48px; margin-bottom:15px; color:#94a3b8; display:block;"></i>
        <h3 style="margin:0 0 10px; color:#334155;">Xem Chương trình đào tạo hoặc Tìm kiếm học phần</h3>
        <p style="margin:0; font-size:14px; max-width:500px; margin:0 auto; line-height:1.5;">Vui lòng <strong>Chọn Khoa và Ngành</strong> ở trên để xem đầy đủ Chương trình đào tạo (CTĐT) chi tiết, hoặc <strong>Nhập từ khóa vào ô tìm kiếm</strong> để tra cứu toàn trường.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

    <!-- Modal Nhân bản CTĐT -->
    <div class="modal" id="duplicateModal">
      <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
          <h2>Nhân bản Chương trình đào tạo</h2>
          <button class="modal-close" type="button" onclick="closeDuplicateModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phan/duplicate-ctdt">
          <div class="form-group">
            <label>Ngành nguồn (Đang có CTĐT) <span style="color:red">*</span></label>
            <select name="nganh_nguon" class="form-control" required>
              <option value="">-- Chọn ngành nguồn --</option>
              <?php if (isset($nganhList)): ?>
                <?php foreach ($nganhList as $n): ?>
                  <option value="<?= e($n['nganh']) ?>"><?= e($n['nganh']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-group" style="margin-top:15px">
            <label>Ngành đích (Cần nhân bản đến) <span style="color:red">*</span></label>
            <input type="text" name="nganh_dich" class="form-control" required placeholder="VD: Ngôn ngữ Trung Quốc">
            <small style="color:var(--text-muted);font-size:11px;display:block;margin-top:5px">Hệ thống sẽ sao chép toàn bộ liên kết học phần từ ngành nguồn sang ngành đích này (chỉ sao chép những môn chưa có ở ngành đích).</small>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeDuplicateModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-copy"></i> Thực hiện nhân bản</button>
          </div>
        </form>
      </div>
    </div>

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
function showDuplicateModal() {
  document.getElementById('duplicateModal').classList.add('active');
}
function closeDuplicateModal() {
  document.getElementById('duplicateModal').classList.remove('active');
}
</script>
