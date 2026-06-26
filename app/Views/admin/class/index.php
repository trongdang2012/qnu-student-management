<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<style>
.ops-panel {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}
.ops-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 16px;
  box-shadow: var(--shadow);
}
.ops-card h3 {
  margin: 0 0 12px;
  font-size: 16px;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 8px;
}
.ops-metrics {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
.ops-metric {
  background: #f8fafc;
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 12px;
}
.ops-metric span {
  display: block;
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 6px;
}
.ops-metric strong {
  font-size: 20px;
  color: var(--text-dark);
}
.ops-list {
  display: grid;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}
.ops-list li {
  border-bottom: 1px solid var(--border);
  padding: 8px 0;
  font-size: 13px;
}
.ops-list li:last-child {
  border-bottom: 0;
}
.ops-tag {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 2px 8px;
  font-size: 12px;
  background: #fff7ed;
  color: #9a3412;
  margin-top: 4px;
}
.capacity-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 999px;
  overflow: hidden;
  margin-top: 8px;
}
.capacity-bar span {
  display: block;
  height: 100%;
  background: var(--primary);
}
@media (max-width: 900px) {
  .ops-panel { grid-template-columns: 1fr; }
  .ops-metrics { grid-template-columns: repeat(2, 1fr); }
}

/* Modal form style overrides */
.form-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
}
@media (max-width: 600px) {
  .form-grid-3 { grid-template-columns: 1fr; }
}
</style>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Tiêu đề trang -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Lớp học phần</span>
      </div>
      <h1><i class="fas fa-chalkboard-teacher"></i> Quản lý Lớp học phần</h1>
      <p>Tạo lớp học phần, phân công giảng viên và phòng học trống, xếp thời khóa biểu và mở cổng đăng ký tín chỉ.</p>
    </div>

    <!-- Thông báo flash -->
    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in" style="margin-bottom: 20px;">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <?php
      $capacityTotal = max(0, (int)($classStats['capacity_total'] ?? 0));
      $enrolledTotal = max(0, (int)($classStats['enrolled_total'] ?? 0));
      $fillRate = $capacityTotal > 0 ? min(100, round(($enrolledTotal / $capacityTotal) * 100, 1)) : 0;
    ?>

    <!-- Bảng điều khiển vận hành -->
    <div class="ops-panel fade-in">
      <div class="ops-card">
        <h3><i class="fas fa-gauge-high"></i> Điều hành lớp học phần</h3>
        <div class="ops-metrics">
          <div class="ops-metric"><span>Đang mở</span><strong><?= (int)($classStats['open_total'] ?? 0) ?></strong></div>
          <div class="ops-metric"><span>Tỷ lệ lấp đầy</span><strong><?= $fillRate ?>%</strong></div>
          <div class="ops-metric"><span>Thiếu GV/Phòng</span><strong><?= (int)($classStats['missing_teacher_total'] ?? 0) ?></strong></div>
          <div class="ops-metric"><span>Đã đủ chỗ</span><strong><?= (int)($classStats['full_total'] ?? 0) ?></strong></div>
        </div>
        <div class="capacity-bar"><span style="width:<?= min(100, $fillRate) ?>%"></span></div>
        <p style="margin:8px 0 0;color:var(--text-muted);font-size:12px"><?= $enrolledTotal ?> / <?= $capacityTotal ?> chỗ đã được sinh viên đăng ký.</p>
      </div>
      <div class="ops-card">
        <h3><i class="fas fa-triangle-exclamation" style="color: #ea580c;"></i> Cảnh báo hệ thống</h3>
        <?php if (empty($classAlerts)): ?>
          <p style="margin:0;color:#16a34a;font-size:13px"><i class="fas fa-circle-check"></i> Tất cả các lớp vận hành đầy đủ giảng viên, phòng học và thời gian học.</p>
        <?php else: ?>
          <ul class="ops-list" style="max-height:140px; overflow-y:auto;">
            <?php foreach ($classAlerts as $c): ?>
              <?php
                // Xác định các lỗi cảnh báo
                $errors = [];
                if (empty($c['giang_vien'])) $errors[] = 'Thiếu giảng viên/phòng';
                if ((int)$c['si_so_hien_tai'] >= (int)$c['si_so_toi_da']) $errors[] = 'Đã đầy chỗ';
                if (empty($c['ngay_bat_dau_dk']) || empty($c['ngay_ket_thuc_dk'])) $errors[] = 'Chưa cấu hình đợt đăng ký';
                
                $alertData = [
                  'id' => (int)$c['id'],
                  'ma_lop_hp' => $c['ma_lop_hp'],
                  'ten_hp' => $c['ten_hp'],
                  'errors' => $errors,
                  'giang_vien' => $c['giang_vien'],
                  'si_so_hien_tai' => (int)$c['si_so_hien_tai'],
                  'si_so_toi_da' => (int)$c['si_so_toi_da'],
                  'ngay_bat_dau_dk' => $c['ngay_bat_dau_dk'],
                  'ngay_ket_thuc_dk' => $c['ngay_ket_thuc_dk']
                ];
              ?>
              <li style="cursor: pointer; padding: 8px; border-radius: 4px; transition: background 0.2s;" 
                  onmouseover="this.style.background='#f1f5f9'" 
                  onmouseout="this.style.background='transparent'"
                  onclick="showOperationalAlertDetail(<?= htmlspecialchars(json_encode($alertData), ENT_QUOTES, 'UTF-8') ?>)">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                  <strong style="color: var(--primary);"><?= e($c['ma_lop_hp']) ?></strong>
                  <span style="font-size: 11px; color: var(--text-muted);"><i class="fas fa-magnifying-glass-chart"></i> Click để sửa</span>
                </div>
                <div style="font-size:12px; margin-top:2px; color: var(--text-dark);"><?= e($c['ten_hp']) ?></div>
                <div style="margin-top: 4px; display:flex; flex-wrap:wrap; gap: 4px;">
                  <?php if (empty($c['giang_vien'])): ?>
                    <span class="ops-tag" style="background:#fff7ed; color:#c2410c;"><i class="fas fa-user-slash"></i> Thiếu GV/Phòng</span>
                  <?php endif; ?>
                  <?php if ((int)$c['si_so_hien_tai'] >= (int)$c['si_so_toi_da']): ?>
                    <span class="ops-tag" style="background:#fee2e2; color:#b91c1c;"><i class="fas fa-users-slash"></i> Đầy chỗ</span>
                  <?php endif; ?>
                  <?php if (empty($c['ngay_bat_dau_dk']) || empty($c['ngay_ket_thuc_dk'])): ?>
                    <span class="ops-tag" style="background:#fef9c3; color:#a16207;"><i class="fas fa-clock"></i> Chưa hẹn giờ</span>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- Thanh thao tác nhanh -->
    <div class="admin-grid fade-in" style="margin-bottom:20px;">
      <div class="stat-card" style="cursor: pointer;" onclick="showAutoGenerateModal()">
        <i class="fas fa-wand-magic-sparkles"></i>
        <div>
          <h3>Sinh lớp & Xếp lịch tự động</h3>
          <div class="stat-value" style="font-size: 15px; color: var(--primary);">⚡ Tạo lớp theo thuật toán Greedy</div>
        </div>
      </div>
      <div class="stat-card" style="cursor: pointer;" onclick="showBatchOpenModal()">
        <i class="fas fa-calendar-check"></i>
        <div>
          <h3>Mở đăng ký hàng loạt</h3>
          <div class="stat-value" style="font-size: 15px; color: #16a34a;">📅 Hẹn giờ & Mở cổng theo Ngành</div>
        </div>
      </div>
      <div class="stat-card" id="btnBatchOpenSelected" style="cursor: pointer; display: none;" onclick="showBatchOpenSelectedModal()">
        <i class="fas fa-clock"></i>
        <div>
          <h3>Mở lớp được chọn</h3>
          <div class="stat-value" style="font-size: 15px; color: #d97706;">⏱️ Hẹn giờ đăng ký thủ công</div>
        </div>
      </div>
      <div class="stat-card" style="cursor: pointer;" onclick="confirmScanAndCancel()">
        <i class="fas fa-triangle-exclamation"></i>
        <div>
          <h3>Quét & Hủy đăng ký lỗi</h3>
          <div class="stat-value" style="font-size: 15px; color: #dc3545;">⚠️ Hủy lớp < 15 SV & Quét tín chỉ</div>
        </div>
      </div>
    </div>

    <!-- Form ẩn để quét và hủy đăng ký lỗi -->
    <form id="scanCancelForm" method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/scan-and-cancel" style="display:none;"></form>

    <!-- Modal Form Thêm/Sửa Lớp học phần -->
    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa Lớp học phần' : 'Tạo Lớp học phần mới' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <?php if ($action === 'edit'): ?>
            <div class="form-row">
              <div class="form-group">
                <label>Mã lớp học phần</label>
                <input type="text" class="form-control" value="<?= e($item['ma_lop_hp']) ?>" readonly style="background:#e9ecef;">
                <input type="hidden" name="ma_lop_hp" value="<?= e($item['ma_lop_hp']) ?>">
              </div>
              <div class="form-group">
                <label>Học phần liên kết</label>
                <input type="text" class="form-control" value="<?= e($item['ma_hp']) ?> - <?= e($item['ten_hp']) ?> (<?= (int)$item['so_tin_chi'] ?> TC)" readonly style="background:#e9ecef;">
                <input type="hidden" name="hoc_phan_id" value="<?= (int)$item['hoc_phan_id'] ?>">
                <input type="hidden" name="hoc_ky" value="<?= (int)$item['hoc_ky'] ?>">
                <input type="hidden" name="nam_hoc" value="<?= e($item['nam_hoc']) ?>">
              </div>
            </div>
          <?php else: ?>
            <div class="form-row">
              <div class="form-group">
                <label>Mã lớp học phần <span style="color:red">*</span></label>
                <input type="text" name="ma_lop_hp" class="form-control" required placeholder="VD: CNTT001-L01" style="text-transform: uppercase;">
              </div>
              <div class="form-group">
                <label>Học phần liên kết <span style="color:red">*</span></label>
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
                <label>Học kỳ học vụ</label>
                <input type="number" name="hoc_ky" class="form-control" value="<?= HOC_KY_HIEN_TAI ?>" min="1" max="8" required>
              </div>
              <div class="form-group">
                <label>Năm học</label>
                <input type="text" name="nam_hoc" class="form-control" value="<?= NAM_HOC_HIEN_TAI ?>" required placeholder="VD: 2025-2026">
              </div>
            </div>
          <?php endif; ?>

          <!-- Chọn giảng viên và phòng học từ danh mục -->
          <div class="form-row">
            <div class="form-group">
              <label>Giảng viên giảng dạy</label>
              <select name="giang_vien_id" class="form-control">
                <option value="">-- Chưa phân công --</option>
                <?php foreach ($giangViens as $gv): ?>
                  <option value="<?= $gv['id'] ?>" <?= (int)($item['giang_vien_id'] ?? 0) === (int)$gv['id'] ? 'selected' : '' ?>>
                    <?= e($gv['ma_gv']) ?> - <?= e($gv['ho_ten']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Phòng học</label>
              <select name="phong_hoc_id" class="form-control">
                <option value="">-- Chưa xếp phòng --</option>
                <?php foreach ($phongHocs as $ph): ?>
                  <option value="<?= $ph['id'] ?>" <?= (int)($item['phong_hoc_id'] ?? 0) === (int)$ph['id'] ? 'selected' : '' ?>>
                    <?= e($ph['ten_phong']) ?> (<?= e($ph['loai_phong']) ?> - Sức chứa: <?= $ph['suc_chua'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Xếp lịch học trực tiếp (Thứ, Tiết, Số tiết) -->
          <div style="background:#f8fafc; border:1px solid var(--border); border-radius:6px; padding:12px; margin-bottom:15px;">
            <label style="font-weight:600; display:block; margin-bottom:8px; color:var(--primary); font-size:13px;"><i class="fas fa-calendar-days"></i> Xếp Lịch Học</label>
            <div class="form-grid-3">
              <div class="form-group" style="margin:0;">
                <label style="font-size:11px;">Thứ học</label>
                <select name="thu" class="form-control" style="font-size:13px; padding:6px 10px;">
                  <option value="">-- Chưa xếp thứ --</option>
                  <?php for ($t = 2; $t <= 7; $t++): ?>
                    <option value="<?= $t ?>" <?= (int)($item['thu'] ?? 0) === $t ? 'selected' : '' ?>>Thứ <?= $t ?></option>
                  <?php endfor; ?>
                  <option value="8" <?= (int)($item['thu'] ?? 0) === 8 ? 'selected' : '' ?>>Chủ Nhật</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:11px;">Tiết học bắt đầu</label>
                <select name="tiet_bat_dau" class="form-control" style="font-size:13px; padding:6px 10px;">
                  <option value="">-- Chưa xếp tiết --</option>
                  <option value="1" <?= (int)($item['tiet_bat_dau'] ?? 0) === 1 ? 'selected' : '' ?>>Tiết 1 (Sáng)</option>
                  <option value="2" <?= (int)($item['tiet_bat_dau'] ?? 0) === 2 ? 'selected' : '' ?>>Tiết 2 (Sáng)</option>
                  <option value="3" <?= (int)($item['tiet_bat_dau'] ?? 0) === 3 ? 'selected' : '' ?>>Tiết 3 (Sáng)</option>
                  <option value="4" <?= (int)($item['tiet_bat_dau'] ?? 0) === 4 ? 'selected' : '' ?>>Tiết 4 (Sáng)</option>
                  <option value="5" <?= (int)($item['tiet_bat_dau'] ?? 0) === 5 ? 'selected' : '' ?>>Tiết 5 (Sáng)</option>
                  <option value="6" <?= (int)($item['tiet_bat_dau'] ?? 0) === 6 ? 'selected' : '' ?>>Tiết 6 (Chiều)</option>
                  <option value="7" <?= (int)($item['tiet_bat_dau'] ?? 0) === 7 ? 'selected' : '' ?>>Tiết 7 (Chiều)</option>
                  <option value="8" <?= (int)($item['tiet_bat_dau'] ?? 0) === 8 ? 'selected' : '' ?>>Tiết 8 (Chiều)</option>
                  <option value="9" <?= (int)($item['tiet_bat_dau'] ?? 0) === 9 ? 'selected' : '' ?>>Tiết 9 (Chiều)</option>
                  <option value="10" <?= (int)($item['tiet_bat_dau'] ?? 0) === 10 ? 'selected' : '' ?>>Tiết 10 (Chiều)</option>
                </select>
              </div>
              <div class="form-group" style="margin:0;">
                <label style="font-size:11px;">Số tiết của buổi học</label>
                <input type="number" name="so_tiet" class="form-control" value="<?= (int)($item['so_tiet'] ?? 3) ?>" min="1" max="5" placeholder="VD: 3" style="font-size:13px; padding:6px 10px;">
              </div>
            </div>
            <small style="color:var(--text-muted); font-size:11px; display:block; margin-top:8px;">Hệ thống sẽ tự động quét trùng lịch giảng viên và phòng học. Tiết 1-5 thuộc ca Sáng, Tiết 6-10 thuộc ca Chiều.</small>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Sĩ số tối đa <span style="color:red">*</span></label>
              <input type="number" name="si_so_toi_da" class="form-control" value="<?= (int)($item['si_so_toi_da'] ?? 80) ?>" min="10" max="150" required>
            </div>
            <div class="form-group">
              <label>Trạng thái lớp</label>
              <select name="trang_thai_mo_lop" class="form-control">
                <?php foreach (['Đang mở', 'Đã đóng', 'Lên kế hoạch'] as $st): ?>
                  <option value="<?= e($st) ?>" <?= ($item['trang_thai_mo_lop'] ?? 'Đang mở') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Ngày học bắt đầu <span style="color:red">*</span></label>
              <input type="date" name="ngay_bat_dau" class="form-control" value="<?= e($item['ngay_bat_dau'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Ngày học kết thúc <span style="color:red">*</span></label>
              <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= e($item['ngay_ket_thuc'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Thời gian mở đăng ký HP</label>
              <input type="datetime-local" name="ngay_bat_dau_dk" class="form-control" value="<?= (!empty($item['ngay_bat_dau_dk'])) ? date('Y-m-d\TH:i', strtotime($item['ngay_bat_dau_dk'])) : '' ?>">
            </div>
            <div class="form-group">
              <label>Thời gian đóng đăng ký HP</label>
              <input type="datetime-local" name="ngay_ket_thuc_dk" class="form-control" value="<?= (!empty($item['ngay_ket_thuc_dk'])) ? date('Y-m-d\TH:i', strtotime($item['ngay_ket_thuc_dk'])) : '' ?>">
            </div>
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
            <input type="text" name="search" class="form-control" placeholder="Mã lớp, tên hoặc mã học phần..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:110px">
            <label style="font-size:12px">Học kỳ học vụ</label>
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
            <label style="font-size:12px">Khoa phụ trách môn</label>
            <select name="khoa" class="form-control">
              <option value="">Tất cả các Khoa</option>
              <?php foreach ($faculties as $f): ?>
                <option value="<?= e($f['ten_khoa']) ?>" <?= $khoaFilter === $f['ten_khoa'] ? 'selected' : '' ?>><?= e($f['ten_khoa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/lop-hoc-phan" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Tạo lớp thủ công</button>
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
            <table id="classesTable">
              <thead>
                <tr>
                  <th style="width: 40px; text-align: center;">
                    <input type="checkbox" id="checkAllClasses" onclick="toggleAllCheckboxes(this)">
                  </th>
                  <th>Mã lớp HP</th>
                  <th>Học phần liên kết</th>
                  <th>Giảng viên</th>
                  <th>Phòng học</th>
                  <th style="text-align:center">Lịch học</th>
                  <th style="text-align:center">Sĩ số (Max)</th>
                  <th>Thời gian học & ĐK</th>
                  <th style="text-align:center">Trạng thái</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr class="hover-row">
                    <td style="text-align: center;">
                      <input type="checkbox" name="class_select" value="<?= $row['id'] ?>" onclick="checkCheckboxState()">
                    </td>
                    <td><code><strong><?= e($row['ma_lop_hp']) ?></strong></code></td>
                    <td>
                      <strong><?= e($row['ten_hp']) ?></strong><br>
                      <small style="color:#6b7280">Mã HP: <?= e($row['ma_hp']) ?> (<?= (int)$row['so_tin_chi'] ?> TC)</small>
                    </td>
                    <td><?= e($row['giang_vien'] ?: '—') ?></td>
                    <td>
                      <?php if (!empty($row['phong_hoc'])): ?>
                        <span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-weight:bold;"><?= e($row['phong_hoc']) ?></span>
                      <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px">Chưa xếp phòng</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center; font-weight: 500;">
                      <?php if (!empty($row['thu']) && !empty($row['tiet_bat_dau'])): ?>
                        Thứ <?= (int)$row['thu'] === 8 ? 'CN' : (int)$row['thu'] ?><br>
                        Tiết <?= (int)$row['tiet_bat_dau'] ?> - <?= (int)$row['tiet_bat_dau'] + (int)$row['so_tiet'] - 1 ?>
                      <?php else: ?>
                        <span style="color:#94a3b8;font-size:12px">Chưa xếp lịch</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                      <span class="badge" style="background:#f1f5f9;color:#334155;font-weight:bold">
                        <?= (int)$row['si_so_hien_tai'] ?> / <?= (int)$row['si_so_toi_da'] ?> SV
                      </span>
                    </td>
                    <td style="font-size:11px; line-height: 1.4;">
                      <span style="color:#2563eb;font-weight:500;">Học:</span> <?= date('d/m/y', strtotime($row['ngay_bat_dau'])) ?> → <?= date('d/m/y', strtotime($row['ngay_ket_thuc'])) ?><br>
                      <?php if (!empty($row['ngay_bat_dau_dk'])): ?>
                        <span style="color:#d97706;font-weight:500;">ĐK:</span> <?= date('d/m H:i', strtotime($row['ngay_bat_dau_dk'])) ?> → <?= date('d/m H:i', strtotime($row['ngay_ket_thuc_dk'])) ?>
                      <?php else: ?>
                        <span style="color:#94a3b8;">ĐK: Chưa mở cổng</span>
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
                        <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa Lớp học phần này không? Lịch học thèm theo lớp học phần cũng sẽ bị xóa.')" style="margin:0;">
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

<!-- Modal 1: Tự động sinh lớp học phần & xếp thời khóa biểu -->
<div class="modal" id="autoGenerateModal">
  <div class="modal-content" style="max-width: 550px;">
    <div class="modal-header">
      <h2><i class="fas fa-wand-magic-sparkles"></i> Sinh lớp & Xếp lịch tự động</h2>
      <button class="modal-close" type="button" onclick="closeAutoGenerateModal()">&times;</button>
    </div>
    <form id="autoGenerateForm" method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/auto-generate">
      <div class="alert alert-info" style="margin-bottom:15px; font-size:12px; line-height:1.5;">
        <strong>🎯 Thuật toán Greedy tối ưu:</strong> Tự động quét học phần trong CTĐT của ngành cho mọi khóa học, tìm giảng viên của khoa và phòng lý thuyết/thực hành còn trống, sau đó phân bổ ca Sáng (tiết 1-5) hoặc Chiều (tiết 6-10) sao cho không trùng lịch với giảng viên, phòng học và sinh viên cùng khóa. Các lớp không xếp được lịch do thiếu phòng/giờ sẽ được tạo và đánh dấu lỗi để Admin chỉnh sửa tay.
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Chọn Khoa <span style="color:red">*</span></label>
        <select name="khoa_id" id="auto_khoa_id" class="form-control" required onchange="loadMajorsForAuto(this.value)">
          <option value="">-- Chọn Khoa --</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= $f['id'] ?>"><?= e($f['ten_khoa']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Chọn Ngành học <span style="color:red">*</span></label>
        <select name="nganh_id" id="auto_nganh_id" class="form-control" required>
          <option value="">-- Chọn Ngành học --</option>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Học kỳ học vụ</label>
          <select name="hoc_ky_hoc_vu" class="form-control">
            <option value="1" <?= HOC_KY_HIEN_TAI == 1 ? 'selected' : '' ?>>Học kỳ 1 (Kỳ chính)</option>
            <option value="2" <?= HOC_KY_HIEN_TAI == 2 ? 'selected' : '' ?>>Học kỳ 2 (Kỳ chính)</option>
            <option value="3" <?= HOC_KY_HIEN_TAI == 3 ? 'selected' : '' ?>>Học kỳ 3 (Kỳ hè)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Năm học</label>
          <input type="text" name="nam_hoc" class="form-control" value="<?= NAM_HOC_HIEN_TAI ?>" required placeholder="2025-2026">
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-secondary" onclick="closeAutoGenerateModal()">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-wand-magic-sparkles"></i> Bắt đầu xếp lịch</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Mở đợt đăng ký tín chỉ hàng loạt cho Ngành -->
<div class="modal" id="batchOpenModal">
  <div class="modal-content" style="max-width: 550px;">
    <div class="modal-header">
      <h2><i class="fas fa-calendar-days"></i> Mở cổng đăng ký học phần hàng loạt</h2>
      <button class="modal-close" type="button" onclick="closeBatchOpenModal()">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/batch-open">
      <div class="alert alert-info" style="margin-bottom:15px; font-size:12px;">
        <strong>📢 Thông báo sinh viên:</strong> Hệ thống sẽ mở cổng đăng ký hàng loạt cho tất cả các lớp của ngành học được chọn trong năm học này, đồng thời tự động gửi thông báo hệ thống trực tiếp đến sinh viên.
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Chọn Khoa phụ trách</label>
        <select name="khoa_id" id="open_khoa_id" class="form-control" onchange="loadMajorsForOpen(this.value)">
          <option value="">-- Chọn Khoa --</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= $f['id'] ?>"><?= e($f['ten_khoa']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 12px;">
        <label>Chọn Ngành học <span style="color:red">*</span></label>
        <select name="nganh_id" id="open_nganh_id" class="form-control" required>
          <option value="">-- Chọn Ngành học --</option>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Thời gian bắt đầu mở cổng <span style="color:red">*</span></label>
          <input type="datetime-local" name="ngay_bat_dau_dk" class="form-control" required value="<?= date('Y-m-d\T07:00') ?>">
        </div>
        <div class="form-group">
          <label>Thời gian đóng cổng <span style="color:red">*</span></label>
          <input type="datetime-local" name="ngay_ket_thuc_dk" class="form-control" required value="<?= date('Y-m-d\T17:00', strtotime('+14 days')) ?>">
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-secondary" onclick="closeBatchOpenModal()">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Kích hoạt mở cổng</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 3: Hẹn giờ đăng ký cho các lớp học phần được chọn -->
<div class="modal" id="batchOpenSelectedModal">
  <div class="modal-content" style="max-width: 500px;">
    <div class="modal-header">
      <h2><i class="fas fa-clock"></i> Hẹn giờ mở đăng ký lớp được chọn</h2>
      <button class="modal-close" type="button" onclick="closeBatchOpenSelectedModal()">&times;</button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/lop-hoc-phan/batch-open-selected" id="batchOpenSelectedForm">
      <div id="selectedClassesContainer"></div>
      <div class="alert alert-warning" style="margin-bottom:15px; font-size:12px;">
        Hẹn giờ mở đăng ký và tự động chuyển đổi trạng thái thành "Đang mở" cho các lớp học phần đã được tích chọn trong bảng.
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Thời gian bắt đầu <span style="color:red">*</span></label>
          <input type="datetime-local" name="ngay_bat_dau_dk_selected" class="form-control" required value="<?= date('Y-m-d\T07:00') ?>">
        </div>
        <div class="form-group">
          <label>Thời gian đóng cổng <span style="color:red">*</span></label>
          <input type="datetime-local" name="ngay_ket_thuc_dk_selected" class="form-control" required value="<?= date('Y-m-d\T17:00', strtotime('+7 days')) ?>">
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button type="button" class="btn btn-secondary" onclick="closeBatchOpenSelectedModal()">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Hẹn giờ mở lớp</button>
      </div>
    </form>
  </div>
</div>

<script>
// JSON danh sách ngành theo khoa
const majorsData = <?= json_encode($majors) ?>;

function loadMajorsForAuto(khoaId) {
  const select = document.getElementById('auto_nganh_id');
  select.innerHTML = '<option value="">-- Chọn Ngành học --</option>';
  const filtered = majorsData.filter(m => parseInt(m.khoa_id) === parseInt(khoaId));
  filtered.forEach(m => {
    select.innerHTML += `<option value="${m.id}">${m.ten_nganh}</option>`;
  });
}

function loadMajorsForOpen(khoaId) {
  const select = document.getElementById('open_nganh_id');
  select.innerHTML = '<option value="">-- Chọn Ngành học --</option>';
  const filtered = majorsData.filter(m => parseInt(m.khoa_id) === parseInt(khoaId));
  filtered.forEach(m => {
    select.innerHTML += `<option value="${m.id}">${m.ten_nganh}</option>`;
  });
}

// Checkbox select all
function toggleAllCheckboxes(master) {
  const checkboxes = document.getElementsByName('class_select');
  checkboxes.forEach(cb => cb.checked = master.checked);
  checkCheckboxState();
}

function checkCheckboxState() {
  const checkboxes = document.getElementsByName('class_select');
  let checkedCount = 0;
  checkboxes.forEach(cb => {
    if (cb.checked) checkedCount++;
  });
  
  const btn = document.getElementById('btnBatchOpenSelected');
  if (checkedCount > 0) {
    btn.style.display = 'flex';
  } else {
    btn.style.display = 'none';
  }
}

function showBatchOpenSelectedModal() {
  const checkboxes = document.getElementsByName('class_select');
  const container = document.getElementById('selectedClassesContainer');
  container.innerHTML = ''; // Clear old fields
  
  let count = 0;
  checkboxes.forEach(cb => {
    if (cb.checked) {
      container.innerHTML += `<input type="hidden" name="class_ids[]" value="${cb.value}">`;
      count++;
    }
  });
  
  if (count > 0) {
    document.getElementById('batchOpenSelectedModal').classList.add('active');
  }
}

function closeBatchOpenSelectedModal() {
  document.getElementById('batchOpenSelectedModal').classList.remove('active');
}

function showAutoGenerateModal() {
  document.getElementById('autoGenerateModal').classList.add('active');
}

function closeAutoGenerateModal() {
  document.getElementById('autoGenerateModal').classList.remove('active');
}

function showBatchOpenModal() {
  document.getElementById('batchOpenModal').classList.add('active');
}

function closeBatchOpenModal() {
  document.getElementById('batchOpenModal').classList.remove('active');
}

function closeModal() {
  document.getElementById('formModal').classList.remove('active');
  if (new URLSearchParams(location.search).has('action')) {
    history.replaceState(null, '', '<?= BASE_URL ?>/admin/lop-hoc-phan');
  }
}

function showAddForm() {
  // Trả form về mặc định
  document.getElementById('formModal').classList.add('active');
}

function confirmScanAndCancel() {
  Swal.fire({
    title: 'Xác nhận quét & hủy lỗi',
    text: 'Hệ thống sẽ quét toàn bộ các lớp có sĩ số < 15 SV để tự động hủy lớp, đồng thời quét điều chỉnh số tín chỉ đã đăng ký của mọi sinh viên nằm ngoài khoảng [2/3, 3/2] so với tín chỉ kế hoạch chuẩn của kỳ này. Các sinh viên bị ảnh hưởng sẽ nhận được thông báo chi tiết. Bạn có muốn tiếp tục?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '⚡ Bắt đầu quét & hủy',
    cancelButtonText: 'Hủy bỏ'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Đang thực hiện quét...',
        text: 'Hệ thống đang kiểm tra sĩ số lớp và số tín chỉ của sinh viên, vui lòng chờ trong giây lát...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      document.getElementById('scanCancelForm').submit();
    }
  });
}

// Xử lý submit AJAX cho form xếp lịch tự động & hiển thị progress popup
document.getElementById('autoGenerateForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const form = this;
  const formData = new FormData(form);
  formData.append('ajax', '1');
  
  closeAutoGenerateModal();
  
  let progress = 0;
  const steps = [
    "Đang quét học phần trong CTĐT của Ngành...",
    "Đang phân tích dữ liệu giảng viên & phòng học...",
    "Đang tính toán phân bổ thời khóa biểu không trùng lịch...",
    "Đang lưu thông tin các lớp học phần mới vào cơ sở dữ liệu...",
    "Đang hoàn tất quá trình xếp lịch..."
  ];
  
  Swal.fire({
    title: 'Đang xếp lịch tự động',
    html: `
      <div style="margin-bottom: 15px; font-weight: 500;" id="swal-step-text">${steps[0]}</div>
      <div class="progress-bar-container" style="width: 100%; background: #e2e8f0; height: 10px; border-radius: 999px; overflow: hidden;">
        <div id="swal-progress-bar" style="width: 5%; height: 100%; background: #0056B3; transition: width 0.2s ease;"></div>
      </div>
      <div style="margin-top: 8px; font-size: 12px; color: #718096;" id="swal-percent-text">5%</div>
    `,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
      
      // Gửi AJAX request
      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        // Mô phỏng thanh tiến trình chạy mượt lên 100%
        let interval = setInterval(() => {
          progress += 10;
          if (progress >= 100) {
            clearInterval(interval);
            
            Swal.fire({
              title: data.success ? (data.status === 'warning' ? 'Cảnh báo xếp lịch' : 'Thành công!') : 'Có lỗi xảy ra',
              html: data.message,
              icon: data.success ? (data.status === 'warning' ? 'warning' : 'success') : 'error',
              confirmButtonText: 'Đóng'
            }).then(() => {
              if (data.success) {
                window.location.reload();
              }
            });
          } else {
            const stepIndex = Math.min(Math.floor(progress / 20), steps.length - 1);
            document.getElementById('swal-step-text').textContent = steps[stepIndex];
            document.getElementById('swal-progress-bar').style.width = progress + '%';
            document.getElementById('swal-percent-text').textContent = progress + '%';
          }
        }, 150);
      })
      .catch(err => {
        Swal.fire({
          title: 'Lỗi hệ thống',
          text: 'Không thể kết nối đến máy chủ để tự động xếp lịch. Chi tiết: ' + err.message,
          icon: 'error',
          confirmButtonText: 'Đóng'
        });
      });
    }
  });
});

// Hiển thị gợi ý sửa lỗi cảnh báo vận hành
function showOperationalAlertDetail(data) {
  let errorHtml = '<div style="text-align: left; font-size: 14px; line-height: 1.6; margin-top: 10px;">';
  errorHtml += `<p><strong>Lớp học phần:</strong> <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-weight:bold; color:#b91c1c;">${data.ma_lop_hp}</code></p>`;
  errorHtml += `<p><strong>Môn học:</strong> <span>${data.ten_hp}</span></p>`;
  errorHtml += '<hr style="margin: 12px 0; border: 0; border-top: 1px solid #e2e8f0;">';
  errorHtml += '<h4 style="margin: 0 0 8px; color: #b91c1c;"><i class="fas fa-circle-exclamation"></i> Lỗi cảnh báo chi tiết:</h4>';
  
  let solutionsHtml = '<h4 style="margin: 15px 0 8px; color: #15803d;"><i class="fas fa-lightbulb"></i> Gợi ý cách khắc phục:</h4><ul style="padding-left: 20px; margin: 0;">';
  
  data.errors.forEach(err => {
    errorHtml += `<div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; color:#374151;">
      <span style="color:#ef4444; font-size:8px;">●</span>
      <span>${err}</span>
    </div>`;
    
    if (err === 'Thiếu giảng viên/phòng') {
      solutionsHtml += `<li style="margin-bottom:6px;">Lớp học phần đang trống Giảng viên, Phòng học hoặc chưa xếp lịch TKB học. Bạn hãy click <strong>"Sửa lớp ngay"</strong>, phân công giảng viên rảnh, phòng học thích hợp còn trống và cấu hình lịch học (Thứ, Tiết bắt đầu, Số tiết).</li>`;
    } else if (err === 'Đã đầy chỗ') {
      solutionsHtml += `<li style="margin-bottom:6px;">Lớp đã đạt sĩ số tối đa (${data.si_so_hien_tai}/${data.si_so_toi_da} SV). Bạn hãy click <strong>"Sửa lớp ngay"</strong> để nâng sĩ số tối đa của lớp học phần này lên (VD: 100 hoặc 120 SV), hoặc mở thêm lớp học phần mới cho môn này.</li>`;
    } else if (err === 'Chưa cấu hình đợt đăng ký') {
      solutionsHtml += `<li style="margin-bottom:6px;">Lớp học chưa cấu hình ngày bắt đầu và kết thúc đăng ký. Hãy click <strong>"Sửa lớp ngay"</strong> để nhập cụ thể <strong>Thời gian bắt đầu</strong> và <strong>Thời gian kết thúc</strong> mở cổng đăng ký học phần.</li>`;
    }
  });
  
  solutionsHtml += '</ul>';
  errorHtml += solutionsHtml + '</div>';
  
  Swal.fire({
    title: 'Cảnh báo vận hành lớp',
    html: errorHtml,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#0056B3',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="fas fa-edit"></i> Sửa lớp ngay',
    cancelButtonText: 'Đóng'
  }).then((result) => {
    if (result.isConfirmed) {
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set('action', 'edit');
      currentUrl.searchParams.set('id', data.id);
      window.location.href = currentUrl.toString();
    }
  });
}
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
