<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Lớp học phần</span>
      </div>
      <h1><i class="fas fa-chalkboard-teacher"></i> Quản lý Lớp học phần</h1>
      <p>Mở lớp học phần mới, phân bổ giảng viên, sĩ số tối đa, và xếp thời khóa biểu tín chỉ tối ưu.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in" style="margin-bottom: 20px;">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <?php
      $classStats = $classStats ?? [];
      $classAlerts = $classAlerts ?? [];
      $capacityTotal = max(0, (int)($classStats['capacity_total'] ?? 0));
      $enrolledTotal = max(0, (int)($classStats['enrolled_total'] ?? 0));
      $fillRate = $capacityTotal > 0 ? min(100, round(($enrolledTotal / $capacityTotal) * 100, 1)) : 0;
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
      .ops-list li{border-bottom:1px solid #f1f5f9;padding:8px 0;font-size:13px}
      .ops-list li:last-child{border-bottom:0}
      .ops-tag{display:inline-flex;align-items:center;border-radius:999px;padding:2px 8px;font-size:12px;background:#fff7ed;color:#9a3412;margin-top:4px}
      .capacity-bar{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:8px}
      .capacity-bar span{display:block;height:100%;background:#2563eb}
      @media (max-width: 900px){.ops-panel{grid-template-columns:1fr}.ops-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
    </style>

    <div class="ops-panel fade-in">
      <div class="ops-card">
        <h3><i class="fas fa-gauge-high"></i> Điều hành lớp học phần</h3>
        <div class="ops-metrics">
          <div class="ops-metric"><span>Đang mở</span><strong><?= (int)($classStats['open_total'] ?? 0) ?></strong></div>
          <div class="ops-metric"><span>Tỷ lệ lấp đầy</span><strong><?= $fillRate ?>%</strong></div>
          <div class="ops-metric"><span>Thiếu giảng viên</span><strong><?= (int)($classStats['missing_teacher_total'] ?? 0) ?></strong></div>
          <div class="ops-metric"><span>Đã đủ chỗ</span><strong><?= (int)($classStats['full_total'] ?? 0) ?></strong></div>
        </div>
        <div class="capacity-bar"><span style="width:<?= min(100, $fillRate) ?>%"></span></div>
        <p style="margin:8px 0 0;color:#6b7280;font-size:12px"><?= $enrolledTotal ?> / <?= $capacityTotal ?> chỗ đã có sinh viên đăng ký.</p>
      </div>
      <div class="ops-card">
        <h3><i class="fas fa-clipboard-check"></i> Việc cần xử lý</h3>
        <?php if (empty($classAlerts)): ?>
          <p style="margin:0;color:#16a34a;font-size:13px">Chưa có lớp thiếu dữ liệu vận hành quan trọng.</p>
        <?php else: ?>
          <ul class="ops-list">
            <?php foreach ($classAlerts as $c): ?>
              <li>
                <strong><?= e($c['ma_lop_hp']) ?></strong> - <?= e($c['ten_hp']) ?><br>
                <?php if (empty($c['giang_vien'])): ?><span class="ops-tag">Chưa phân công giảng viên</span><?php endif; ?>
                <?php if ((int)$c['si_so_hien_tai'] >= (int)$c['si_so_toi_da']): ?><span class="ops-tag">Đã đủ sĩ số</span><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-university"></i>
        <div>
          <h3>Lớp học phần</h3>
          <div class="stat-value"><?= (int)$totalItems ?> lớp</div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-check"></i>
        <div>
          <h3>Học kỳ hiện hành</h3>
          <div class="stat-value">HK<?= HOC_KY_HIEN_TAI ?> (<?= NAM_HOC_HIEN_TAI ?>)</div>
        </div>
      </div>
      <div class="stat-card" style="cursor: pointer;" onclick="location.href='<?= BASE_URL ?>/admin/lop-hoc-phan/optimize'">
        <i class="fas fa-magic"></i>
        <div>
          <h3>TKB tự động</h3>
          <div class="stat-value" style="font-size: 16px;">⚡ Xếp TKB tối ưu</div>
        </div>
      </div>
    </div>

    <!-- Modal Form Thêm/Sửa Lớp học phần -->
    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Cập nhật lớp học phần' : 'Tạo lớp học phần mới' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <?php if ($action === 'edit'): ?>
            <div class="form-group">
              <label>Mã lớp học phần</label>
              <input type="text" class="form-control" value="<?= e($item['ma_lop_hp']) ?>" readonly style="background:#e9ecef;">
              <input type="hidden" name="ma_lop_hp" value="<?= e($item['ma_lop_hp']) ?>">
            </div>
            <div class="form-group">
              <label>Học phần liên kết</label>
              <input type="text" class="form-control" value="<?= e($item['ma_hp']) ?> - <?= e($item['ten_hp']) ?>" readonly style="background:#e9ecef;">
              <input type="hidden" name="hoc_phan_id" value="<?= (int)$item['hoc_phan_id'] ?>">
              <input type="hidden" name="hoc_ky" value="<?= (int)$item['hoc_ky'] ?>">
              <input type="hidden" name="nam_hoc" value="<?= e($item['nam_hoc']) ?>">
            </div>
          <?php else: ?>
            <div class="form-row">
              <div class="form-group">
                <label>Mã lớp học phần <span style="color:red">*</span></label>
                <input type="text" name="ma_lop_hp" class="form-control" required placeholder="VD: CNTT001-L01" style="text-transform: uppercase;">
              </div>
              <div class="form-group">
                <label>Chọn học phần liên kết <span style="color:red">*</span></label>
                <select name="hoc_phan_id" class="form-control" required>
                  <option value="">-- Chọn học phần --</option>
                  <?php foreach ($allCourses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['ma_hp']) ?> - <?= e($c['ten_hp']) ?> (HK<?= $c['hoc_ky'] ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Học kỳ</label>
                <input type="number" name="hoc_ky" class="form-control" value="<?= HOC_KY_HIEN_TAI ?>" min="1" max="8" required>
              </div>
              <div class="form-group">
                <label>Năm học</label>
                <input type="text" name="nam_hoc" class="form-control" value="<?= NAM_HOC_HIEN_TAI ?>" required placeholder="2025-2026">
              </div>
            </div>
          <?php endif; ?>

          <div class="form-row">
            <div class="form-group">
              <label>Giảng viên phụ trách <span style="color:red">*</span></label>
              <input type="text" name="giang_vien" class="form-control" value="<?= e($item['giang_vien'] ?? '') ?>" required placeholder="VD: TS. Nguyễn Văn A">
            </div>
            <div class="form-group">
              <label>Sĩ số tối đa <span style="color:red">*</span></label>
              <input type="number" name="si_so_toi_da" class="form-control" value="<?= (int)($item['si_so_toi_da'] ?? 80) ?>" min="10" max="150" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Ngày bắt đầu học <span style="color:red">*</span></label>
              <input type="date" name="ngay_bat_dau" class="form-control" value="<?= e($item['ngay_bat_dau'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Ngày kết thúc học <span style="color:red">*</span></label>
              <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= e($item['ngay_ket_thuc'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Bắt đầu đăng ký HP</label>
              <input type="datetime-local" name="ngay_bat_dau_dk" class="form-control" value="<?= (!empty($item['ngay_bat_dau_dk'])) ? date('Y-m-d\TH:i', strtotime($item['ngay_bat_dau_dk'])) : '' ?>">
            </div>
            <div class="form-group">
              <label>Kết thúc đăng ký HP</label>
              <input type="datetime-local" name="ngay_ket_thuc_dk" class="form-control" value="<?= (!empty($item['ngay_ket_thuc_dk'])) ? date('Y-m-d\TH:i', strtotime($item['ngay_ket_thuc_dk'])) : '' ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Trạng thái lớp</label>
            <select name="trang_thai_mo_lop" class="form-control">
              <?php foreach (['Đang mở', 'Đã đóng', 'Lên kế hoạch'] as $st): ?>
                <option value="<?= e($st) ?>" <?= ($item['trang_thai_mo_lop'] ?? 'Đang mở') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu dữ liệu</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Thanh công cụ tìm kiếm và lọc -->
    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" action="<?= BASE_URL ?>/admin/lop-hoc-phan" class="action-bar" style="align-items:flex-end;margin-bottom:0;flex-wrap:wrap;gap:10px">
          <div class="form-group search-box" style="margin:0;flex:1;min-width:200px">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Mã lớp, tên học phần hoặc mã học phần..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:110px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <option value="0">Tất cả</option>
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKyFilter === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Giảng viên dạy</label>
            <input type="text" name="giang_vien" class="form-control" placeholder="Tên giảng viên..." value="<?= e($giangVienFilter) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Khoa quản lý</label>
            <select name="khoa" class="form-control">
              <option value="">Tất cả các Khoa</option>
              <?php foreach (['Kỹ thuật - Công nghệ', 'Kinh tế - Luật', 'Ngoại ngữ', 'Khoa học Tự nhiên', 'Khoa học Xã hội và Nhân văn'] as $kh): ?>
                <option value="<?= e($kh) ?>" <?= $khoaFilter === $kh ? 'selected' : '' ?>><?= e($kh) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/lop-hoc-phan" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-warning" onclick="showBatchOpenModal()"><i class="fas fa-magic"></i> Mở lớp tự động</button>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Mở lớp mới</button>
        </form>
      </div>
    </div>

    <!-- Bảng danh sách Lớp học phần -->
    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-inbox" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy Lớp học phần nào phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã lớp HP</th>
                  <th>Học phần liên kết</th>
                  <th>Giảng viên phụ trách</th>
                  <th style="text-align:center">Học kỳ</th>
                  <th style="text-align:center">Năm học</th>
                  <th style="text-align:center">Sĩ số tối đa</th>
                  <th style="text-align:center">Đã đăng ký</th>
                  <th>Thời gian học & ĐK</th>
                  <th style="text-align:center">Trạng thái</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><code><strong><?= e($row['ma_lop_hp']) ?></strong></code></td>
                    <td>
                      <strong><?= e($row['ten_hp']) ?></strong><br>
                      <small style="color:#666">Mã HP: <?= e($row['ma_hp']) ?> (<?= (int)$row['so_tin_chi'] ?> TC)</small>
                    </td>
                    <td><strong><?= e($row['giang_vien']) ?></strong></td>
                    <td style="text-align:center">HK<?= (int)$row['hoc_ky'] ?></td>
                    <td style="text-align:center"><?= e($row['nam_hoc']) ?></td>
                    <td style="text-align:center;font-weight:bold"><?= (int)$row['si_so_toi_da'] ?></td>
                    <td style="text-align:center">
                      <span class="badge" style="background:#e9ecef;color:#212529;font-weight:bold">
                        <?= (int)$row['si_so_hien_tai'] ?> SV
                      </span>
                    </td>
                    <td style="font-size:12px; line-height: 1.4;">
                      <span class="badge" style="background:#e8f4fd;color:#0056B3;margin-bottom:3px;display:inline-block;padding:2px 6px;">Học tập</span><br>
                      Từ: <?= date('d/m/Y', strtotime($row['ngay_bat_dau'])) ?><br>
                      Đến: <?= date('d/m/Y', strtotime($row['ngay_ket_thuc'])) ?><br>
                      <span class="badge" style="background:#fff3cd;color:#856404;margin-top:5px;margin-bottom:3px;display:inline-block;padding:2px 6px;">Đăng ký HP</span><br>
                      <?php if (!empty($row['ngay_bat_dau_dk']) || !empty($row['ngay_ket_thuc_dk'])): ?>
                        Mở: <?= !empty($row['ngay_bat_dau_dk']) ? date('d/m H:i', strtotime($row['ngay_bat_dau_dk'])) : '—' ?><br>
                        Hạn: <?= !empty($row['ngay_ket_thuc_dk']) ? date('d/m H:i', strtotime($row['ngay_ket_thuc_dk'])) : '—' ?>
                      <?php else: ?>
                        <span class="text-muted">Không giới hạn</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                      <?php if ($row['trang_thai_mo_lop'] === 'Đang mở'): ?>
                        <span class="badge" style="background:#d4edda;color:#155724;border: 1px solid #c3e6cb;">Đang mở</span>
                      <?php elseif ($row['trang_thai_mo_lop'] === 'Đã đóng'): ?>
                        <span class="badge" style="background:#f8d7da;color:#721c24;border: 1px solid #f5c6cb;">Đã đóng</span>
                      <?php else: ?>
                        <span class="badge" style="background:#fff3cd;color:#856404;border: 1px solid #ffeeba;">Lên kế hoạch</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                      <div class="table-actions" style="display:flex;justify-content:center;gap:5px">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>&page=<?= $page ?>">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa Lớp học phần này không?')">
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

          <!-- Phân trang -->
          <?php if ($totalPages > 1): ?>
            <div style="display:flex;justify-content:center;align-items:center;padding:15px;gap:5px;border-top:1px solid #eee">
              <?php if ($page > 1): ?>
                <a class="btn btn-sm btn-secondary" href="?page=1&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><i class="fas fa-angles-left"></i> Đầu</a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><i class="fas fa-angle-left"></i> Trước</a>
              <?php endif; ?>

              <?php 
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
              ?>
                <a class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>"><?= $p ?></a>
              <?php endfor; ?>

              <?php if ($page < $totalPages): ?>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>">Sau <i class="fas fa-angle-right"></i></a>
                <a class="btn btn-sm btn-secondary" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= $hocKyFilter ?>&giang_vien=<?= urlencode($giangVienFilter) ?>&khoa=<?= urlencode($khoaFilter) ?>">Cuối <i class="fas fa-angles-right"></i></a>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

    <!-- Modal Mở lớp học phần tự động hàng loạt -->
    <div class="modal" id="batchOpenModal">
      <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
          <h2><i class="fas fa-magic"></i> Mở Lớp Học Phần Tự Động</h2>
          <button class="modal-close" type="button" onclick="closeBatchOpenModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/batch-open">
          <div class="alert alert-info" style="margin-bottom:15px">
            <strong>📌 Lưu ý quan trọng:</strong><br>
            • <strong>HK CTDT</strong>: Học kỳ theo Chương trình đào tạo (dùng để lấy danh sách môn học cần mở)<br>
            • <strong>HK Học vụ</strong>: Học kỳ học vụ hiện hành (1, 2, 3) - sẽ được lưu vào lớp học phần<br>
            • Giảng viên sẽ để <strong>trống</strong> - Admin phải phân công sau
          </div>

          <div class="form-group">
            <label>Ngành đào tạo <span style="color:red">*</span></label>
            <select name="nganh" class="form-control" required>
              <option value="">-- Chọn ngành đào tạo --</option>
              <?php if (isset($nganhList)): ?>
                <?php foreach ($nganhList as $n): ?>
                  <option value="<?= e($n['nganh']) ?>"><?= e($n['nganh']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-row" style="margin-top:15px;gap:10px">
            <div class="form-group" style="flex:1">
              <label>HK theo CTDT (1-8) <span style="color:red">*</span></label>
              <select name="hoc_ky_ctdt" class="form-control" required>
                <?php for ($i = 1; $i <= 8; $i++): ?>
                  <option value="<?= $i ?>" <?= $i == HOC_KY_HIEN_TAI ? 'selected' : '' ?>>HK<?= $i ?></option>
                <?php endfor; ?>
              </select>
              <small style="color:#666">Để lấy danh sách môn</small>
            </div>
            <div class="form-group" style="flex:1">
              <label>HK Học vụ hiện hành (1-3) <span style="color:red">*</span></label>
              <select name="hoc_ky_hoc_vu" class="form-control" required>
                <option value="1" <?= HOC_KY_HIEN_TAI == 1 ? 'selected' : '' ?>>HK1</option>
                <option value="2" <?= HOC_KY_HIEN_TAI == 2 ? 'selected' : '' ?>>HK2</option>
                <option value="3" <?= HOC_KY_HIEN_TAI == 3 ? 'selected' : '' ?>>HK3</option>
              </select>
              <small style="color:#666">Để lưu vào lớp</small>
            </div>
          </div>

          <div class="form-group" style="margin-top:15px">
            <label>Năm học <span style="color:red">*</span></label>
            <input type="text" name="nam_hoc" class="form-control" value="<?= NAM_HOC_HIEN_TAI ?>" required placeholder="VD: 2025-2026">
          </div>

          <div class="form-row" style="margin-top:15px;gap:10px">
            <div class="form-group" style="flex:1">
              <label>Bắt đầu đăng ký HP</label>
              <input type="datetime-local" name="ngay_bat_dau_dk" class="form-control" value="<?= date('Y-m-d\T00:00') ?>">
            </div>
            <div class="form-group" style="flex:1">
              <label>Kết thúc đăng ký HP</label>
              <input type="datetime-local" name="ngay_ket_thuc_dk" class="form-control" value="<?= date('Y-m-d\T23:59', strtotime('+14 days')) ?>">
            </div>
          </div>

          <div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:12px;margin-top:15px;font-size:12px;line-height:1.6">
            <strong>🎯 Cách hoạt động:</strong><br>
            1. Chọn ngành và HK CTDT để lấy danh sách môn<br>
            2. Hệ thống sẽ mở lớp với HK học vụ được chọn<br>
            3. Mã lớp tự động sinh: <code>MAHP-L01</code>, <code>MAHP-L02</code>, ...<br>
            4. Giảng viên để trống - <strong>Admin phải vào từng lớp để phân công</strong><br>
            5. Có thể chạy lại để mở thêm lớp L02, L03, ... nếu cần
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeBatchOpenModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-magic"></i> Tạo các lớp</button>
          </div>
        </form>
      </div>
    </div>

<script>
function closeModal() {
  document.getElementById('formModal').classList.remove('active');
  if (new URLSearchParams(location.search).has('action')) {
    history.replaceState(null, '', '<?= BASE_URL ?>/admin/lop-hoc-phan');
  }
}
function showAddForm() {
  document.getElementById('formModal').classList.add('active');
}
function showBatchOpenModal() {
  document.getElementById('batchOpenModal').classList.add('active');
}
function closeBatchOpenModal() {
  document.getElementById('batchOpenModal').classList.remove('active');
}
</script>
