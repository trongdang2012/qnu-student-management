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

    <!-- Thanh tìm kiếm Mã sinh viên mới -->
    <div class="card fade-in" style="margin-bottom: 20px;">
      <div class="card-body" style="padding: 16px;">
        <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="action-bar" style="align-items: flex-end; margin-bottom: 0; flex-wrap: wrap; gap: 10px;">
          <div class="form-group" style="margin: 0; flex: 1; min-width: 250px;">
            <label style="font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">Tra cứu Thời khóa biểu cá nhân của Sinh viên</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" name="ma_sv" class="form-control" placeholder="Nhập Mã số sinh viên chính xác... (Ví dụ: 4741190039)" value="<?= e($maSv ?? '') ?>" required>
              <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-search"></i> Tra cứu</button>
              <?php if (!empty($maSv)): ?>
                <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="btn btn-secondary" style="white-space: nowrap;"><i class="fas fa-arrow-left"></i> Quay lại Quản lý chung</a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in" style="margin-bottom:20px">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($maSv)): ?>
      <!-- HIỂN THỊ THỜI KHÓA BIỂU SINH VIÊN KHI TÌM KIẾM -->
      <?php if (!empty($studentError)): ?>
        <div class="alert alert-danger fade-in">
          <i class="fas fa-exclamation-circle"></i> <?= e($studentError) ?>
        </div>
      <?php else: ?>
        <!-- Card thông tin sinh viên và chọn kỳ học -->
        <div class="card fade-in" style="margin-bottom: 20px;">
          <div class="card-body">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
              <div>
                <h2 style="margin: 0 0 10px 0; color: #111827; font-size: 20px;"><i class="fas fa-user-graduate"></i> Sinh viên: <?= e($student['ho_ten']) ?></h2>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(200px, 1fr)); gap: 8px; font-size: 14px; color: #4b5563;">
                  <div><strong>Mã sinh viên:</strong> <code><?= e($student['ma_sv']) ?></code></div>
                  <div><strong>Lớp sinh hoạt:</strong> <?= e($student['lop'] ?? 'Chưa rõ') ?></div>
                  <div><strong>Ngành học:</strong> <?= e($student['ten_nganh'] ?? 'Chưa rõ') ?></div>
                  <div><strong>Khoa:</strong> <?= e($student['ten_khoa'] ?? 'Chưa rõ') ?></div>
                </div>
              </div>
              
              <div>
                <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu">
                  <input type="hidden" name="ma_sv" value="<?= e($maSv) ?>">
                  <div class="form-group" style="margin: 0; min-width: 220px;">
                    <label style="font-size: 12px; font-weight: bold; margin-bottom: 4px; display: block;">Chọn học kỳ hiển thị</label>
                    <select name="term" class="form-control" onchange="this.form.submit()">
                      <?php if (empty($registeredTerms)): ?>
                        <option value="">Chưa đăng ký học phần nào</option>
                      <?php else: ?>
                        <?php foreach ($registeredTerms as $term): ?>
                          <?php $termVal = $term['hoc_ky'] . '-' . $term['nam_hoc']; ?>
                          <option value="<?= e($termVal) ?>" <?= $selectedTerm === $termVal ? 'selected' : '' ?>>
                            Học kỳ <?= $term['hoc_ky'] ?> - Năm học <?= e($term['nam_hoc']) ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Lưới thời khóa biểu của sinh viên -->
        <div class="card fade-in" style="overflow-x: auto;">
          <div class="card-body">
            <h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a;"><i class="fas fa-calendar-week"></i> Thời khóa biểu chi tiết học kỳ</h3>
            <?php if (empty($studentGrid)): ?>
              <div style="padding: 40px; text-align: center; color: #777;">
                <i class="fas fa-calendar-xmark" style="font-size: 42px; margin-bottom: 12px; display: block;"></i>
                Sinh viên chưa có lịch học nào được xếp hoặc chưa đăng ký lớp học phần trong kỳ học này.
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table class="grid-table" style="border-collapse: collapse; width: 100%; text-align: center;">
                  <thead>
                    <tr style="background:#f8f9fa;">
                      <th style="width: 10%; border: 1px solid #dee2e6; padding: 10px;">Tiết \ Thứ</th>
                      <?php for ($t = 2; $t <= 7; $t++): ?>
                        <th style="width: 13%; border: 1px solid #dee2e6; padding: 10px;">Thứ <?= $t ?></th>
                      <?php endfor; ?>
                      <th style="width: 12%; border: 1px solid #dee2e6; padding: 10px;">Chủ Nhật</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php for ($tiet = 1; $tiet <= 10; $tiet++): ?>
                      <tr>
                        <td style="font-weight:bold; background:#f8f9fa; border: 1px solid #dee2e6; padding:8px 4px;">Tiết <?= $tiet ?></td>
                        <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                          <?php if (isset($studentGrid[$thu][$tiet])): ?>
                            <?php 
                              $cell = $studentGrid[$thu][$tiet]; 
                              if ((int)$cell['tiet_bat_dau'] === $tiet):
                                  $duration = (int)$cell['so_tiet'];
                            ?>
                              <td rowspan="<?= $duration ?>" style="background:#e0f2fe; border: 1px solid #dee2e6; padding:8px; vertical-align:middle; text-align:center;">
                                <div style="font-weight:bold; color:#0369a1; font-size:13px;"><?= e($cell['ma_lop_hp']) ?></div>
                                <div style="font-size:12px; margin-top:2px; font-weight:600; color: #1e293b;"><?= e($cell['ten_hp']) ?></div>
                                <div style="font-size:11px; color:#475569; margin-top:4px;"><i class="fas fa-door-open"></i> Phòng: <strong><?= e($cell['phong_hoc'] ?: '—') ?></strong></div>
                                <div style="font-size:11px; color:#475569; margin-top:2px;"><i class="fas fa-user-tie"></i> GV: <?= e($cell['giang_vien'] ?: '—') ?></div>
                              </td>
                            <?php endif; ?>
                          <?php else: ?>
                            <?php 
                              $isCovered = false;
                              for ($prevTiet = 1; $prevTiet < $tiet; $prevTiet++) {
                                  if (isset($studentGrid[$thu][$prevTiet])) {
                                      $prevCell = $studentGrid[$thu][$prevTiet];
                                      if ($prevTiet + (int)$prevCell['so_tiet'] > $tiet) {
                                          $isCovered = true;
                                          break;
                                      }
                                  }
                              }
                              if (!$isCovered):
                            ?>
                              <td style="border: 1px solid #dee2e6; color:#cbd5e1; font-size:11px; padding: 12px 8px;">—</td>
                            <?php endif; ?>
                          <?php endif; ?>
                        <?php endfor; ?>
                      </tr>
                    <?php endfor; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- HIỂN THỊ GIAO DIỆN QUẢN LÝ THỜI KHÓA BIỂU CŨ -->
      <?php
        $scheduleStats = $scheduleStats ?? [];
        $unscheduledClasses = $unscheduledClasses ?? [];
        $roomUtilization = $roomUtilization ?? [];
        $scheduledPercent = (float)($scheduleStats['scheduled_percent'] ?? 0);
      ?>

      <style>
        .ops-panel{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:16px;margin-bottom:20px}
        .ops-card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
        .ops-card h3{margin:0 0 12px;font-size:16px;color:#111827;display:flex;align-items:center;gap:8px}
        .ops-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
        .ops-metric{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:12px}
        .ops-metric span{display:block;font-size:12px;color:#6b7280;margin-bottom:6px}
        .ops-metric strong{font-size:22px;color:#111827}
        .ops-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}
        .ops-list li{border-bottom:1px solid #f1f5f9;padding:8px 0;font-size:13px}
        .ops-list li:last-child{border-bottom:0}
        .ops-tag{display:inline-flex;align-items:center;border-radius:999px;padding:2px 8px;font-size:12px;background:#eef2ff;color:#3730a3;margin-top:4px}
        .capacity-bar{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:8px}
        .capacity-bar span{display:block;height:100%;background:#16a34a}
        .ops-scroll{max-height:220px;overflow-y:auto;padding-right:6px}
        .ops-scroll::-webkit-scrollbar{width:4px}
        .ops-scroll::-webkit-scrollbar-track{background:#f1f5f9;border-radius:4px}
        .ops-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
        .ops-scroll::-webkit-scrollbar-thumb:hover{background:#94a3b8}
        @media (max-width: 1100px){.ops-panel{grid-template-columns:1fr}.ops-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}
      </style>

      <div class="ops-panel fade-in">
        <div class="ops-card">
          <h3><i class="fas fa-chart-pie"></i> Tiến độ xếp thời khóa biểu</h3>
          <div class="ops-metrics">
            <div class="ops-metric"><span>Lớp đã có lịch</span><strong><?= (int)($scheduleStats['scheduled_class_total'] ?? 0) ?>/<?= (int)($scheduleStats['class_total'] ?? 0) ?></strong></div>
            <div class="ops-metric"><span>Chưa xếp lịch</span><strong><?= (int)($scheduleStats['unscheduled_total'] ?? 0) ?></strong></div>
            <div class="ops-metric"><span>Phòng đang dùng</span><strong><?= (int)($scheduleStats['room_total'] ?? 0) ?></strong></div>
            <div class="ops-metric"><span>Tổng tiết/tuần</span><strong><?= (int)($scheduleStats['period_total'] ?? 0) ?></strong></div>
          </div>
          <div class="capacity-bar"><span style="width:<?= min(100, $scheduledPercent) ?>%"></span></div>
          <p style="margin:8px 0 0;color:#6b7280;font-size:12px"><?= $scheduledPercent ?>% lớp học phần trong kỳ đã có lịch.</p>
        </div>
        <div class="ops-card">
          <h3><i class="fas fa-calendar-xmark"></i> Lớp chưa có lịch</h3>
          <?php if (empty($unscheduledClasses)): ?>
            <p style="margin:0;color:#16a34a;font-size:13px">Tất cả lớp trong kỳ đã có thời khóa biểu.</p>
          <?php else: ?>
            <div class="ops-scroll">
              <ul class="ops-list">
                <?php foreach ($unscheduledClasses as $c): ?>
                  <li>
                    <strong><?= e($c['ma_lop_hp']) ?></strong> - <?= e($c['ten_hp']) ?><br>
                    <span class="ops-tag"><?= (int)$c['si_so_hien_tai'] ?>/<?= (int)$c['si_so_toi_da'] ?> SV</span>
                    <?php if (empty($c['giang_vien'])): ?><span class="ops-tag">Thiếu giảng viên</span><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
        <div class="ops-card">
          <h3><i class="fas fa-door-open"></i> Phòng dùng nhiều</h3>
          <?php if (empty($roomUtilization)): ?>
            <p style="margin:0;color:#6b7280;font-size:13px">Chưa có dữ liệu sử dụng phòng.</p>
          <?php else: ?>
            <div class="ops-scroll">
              <ul class="ops-list">
                <?php foreach ($roomUtilization as $r): ?>
                  <li>
                    <strong><?= e($r['phong_hoc']) ?></strong>
                    <span class="ops-tag"><?= (int)$r['period_total'] ?> tiết / <?= (int)$r['schedule_total'] ?> ca</span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="admin-grid fade-in">
        <div class="stat-card">
          <i class="fas fa-clock"></i>
          <div>
            <h3>Số ca học đã xếp</h3>
            <div class="stat-value"><?= count($list) ?> ca</div>
          </div>
        </div>
        <div class="stat-card">
          <i class="fas fa-school"></i>
          <div>
            <h3>Số phòng học sử dụng</h3>
            <div class="stat-value"><?= count($phongsList) ?> phòng</div>
          </div>
        </div>
        <div class="stat-card" style="cursor:pointer" onclick="confirmOptimize()">
          <i class="fas fa-wand-magic-sparkles"></i>
          <div>
            <h3>Xếp TKB tự động</h3>
            <div class="stat-value" style="font-size:16px;">⚡ Tự động phân lịch tối ưu</div>
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
                                    <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/delete" style="display:inline" onsubmit="return confirmDelete(this)">
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
                          <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/delete" style="display:inline" onsubmit="return confirmDelete(this)">
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
    <?php endif; ?>
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

function confirmOptimize() {
  Swal.fire({
    title: 'Xác nhận xếp lịch?',
    text: 'Xếp lại toàn bộ lịch học tự động cho các lớp trong kỳ này? Lịch cũ sẽ bị xóa.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Đồng ý',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      // Hiển thị modal tiến trình xếp lịch
      Swal.fire({
        title: 'Đang xếp lịch tự động',
        html: `
          <p style="font-size:14px;color:#6b7280;margin-bottom:15px;">Thuật toán đang tính toán phân bổ lịch học tối ưu cho các lớp...</p>
          <div style="background: #e2e8f0; border-radius: 9999px; height: 16px; overflow: hidden; position: relative;">
            <div id="swal-progress-bar" style="background: #10b981; height: 100%; width: 0%; transition: width 0.1s ease; border-radius: 9999px;"></div>
          </div>
          <div id="swal-progress-text" style="margin-top: 8px; font-size: 14px; font-weight: 600; color: #374151; text-align: center;">0%</div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          const progressBar = document.getElementById('swal-progress-bar');
          const progressText = document.getElementById('swal-progress-text');
          let progress = 0;
          
          // Chạy tiến trình giả lập từ 0% đến 95%
          const timer = setInterval(() => {
            if (progress < 95) {
              const increment = Math.floor(Math.random() * 5) + 2; // Tăng ngẫu nhiên từ 2% đến 6%
              progress = Math.min(95, progress + increment);
              progressBar.style.width = progress + '%';
              progressText.innerText = progress + '%';
            }
          }, 150);

          // Gửi request AJAX
          fetch('<?= BASE_URL ?>/admin/thoi-khoa-bieu/optimize?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>&ajax=1')
            .then(response => response.json())
            .then(data => {
              clearInterval(timer);
              // Đạt 100%
              progressBar.style.width = '100%';
              progressText.innerText = '100%';
              
              setTimeout(() => {
                Swal.fire({
                  title: data.success ? 'Thành công!' : 'Thất bại!',
                  text: data.message,
                  icon: data.success ? 'success' : 'error',
                  confirmButtonText: 'Đồng ý'
                }).then(() => {
                  location.reload();
                });
              }, 400);
            })
            .catch(error => {
              clearInterval(timer);
              Swal.fire({
                title: 'Lỗi kết nối!',
                text: 'Không thể kết nối tới máy chủ. Vui lòng thử lại sau.',
                icon: 'error',
                confirmButtonText: 'Đóng'
              });
            });
        }
      });
    }
  });
}

function confirmDelete(form) {
  Swal.fire({
    title: 'Xác nhận xóa?',
    text: 'Bạn có chắc chắn muốn xóa ca học này không?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
  return false;
}
</script>
