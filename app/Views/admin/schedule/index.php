<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Thời khóa biểu</span>
      </div>
      <h1><i class="fas fa-calendar-alt"></i> Tra cứu Thời khóa biểu</h1>
      <p>Nhập mã số sinh viên để xem chi tiết thời khóa biểu cá nhân của sinh viên theo dạng lưới hoặc dạng danh sách.</p>
    </div>

    <!-- Thanh tìm kiếm Mã sinh viên -->
    <div class="card fade-in" style="margin-bottom: 20px;">
      <div class="card-body" style="padding: 16px;">
        <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="action-bar" style="align-items: flex-end; margin-bottom: 0; flex-wrap: wrap; gap: 10px;">
          <div class="form-group" style="margin: 0; flex: 1; min-width: 250px;">
            <label style="font-weight: 600; font-size: 13px; color: #374151; margin-bottom: 6px; display: block;">Tra cứu Thời khóa biểu cá nhân của Sinh viên</label>
            <div style="display: flex; gap: 8px;">
              <input type="text" name="ma_sv" class="form-control" placeholder="Nhập Mã số sinh viên chính xác... (Ví dụ: 4751190039)" value="<?= e($maSv ?? '') ?>" required>
              <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-search"></i> Tra cứu</button>
              <?php if (!empty($maSv)): ?>
                <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu" class="btn btn-secondary" style="white-space: nowrap;"><i class="fas fa-rotate-left"></i> Xóa tra cứu</a>
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
      <!-- HIỂN THỊ THỜI KHÓA BIỂU SINH VIÊN KHI TRA CỨU -->
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

        <!-- Chế độ chuyển đổi View: Lưới và Danh sách -->
        <div style="margin-bottom: 15px; display:flex; gap:10px" class="fade-in">
          <button class="btn btn-sm btn-primary" id="btnGridView" onclick="switchView('grid')"><i class="fas fa-border-all"></i> Xem dạng Lưới</button>
          <button class="btn btn-sm btn-secondary" id="btnListView" onclick="switchView('list')"><i class="fas fa-list"></i> Xem dạng Danh sách</button>
        </div>

        <!-- VIEW DẠNG LƯỚI THỜI KHÓA BIỂU -->
        <div id="tkbGridView" class="card fade-in" style="overflow-x: auto;">
          <div class="card-body">
            <h3 style="margin-top: 0; margin-bottom: 15px; color: #1e3a8a;"><i class="fas fa-calendar-week"></i> Thời khóa biểu dạng lưới</h3>
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

        <!-- VIEW DẠNG DANH SÁCH -->
        <div id="tkbListView" class="card fade-in" style="display: none;">
          <div class="card-body" style="padding:0">
            <h3 style="margin-top: 15px; margin-left: 20px; margin-bottom: 15px; color: #1e3a8a;"><i class="fas fa-list-ul"></i> Thời khóa biểu dạng danh sách</h3>
            <?php if (empty($studentSchedule)): ?>
              <div style="padding: 40px; text-align: center; color: #777;">
                <i class="fas fa-calendar-xmark" style="font-size: 42px; margin-bottom: 12px; display: block;"></i>
                Sinh viên chưa đăng ký lớp học phần trong kỳ học này.
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th style="padding-left: 20px;">Mã lớp HP</th>
                      <th>Môn học</th>
                      <th style="text-align:center">Thứ</th>
                      <th style="text-align:center">Tiết học</th>
                      <th style="text-align:center">Phòng học</th>
                      <th>Giảng viên</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($studentSchedule as $row): ?>
                      <tr class="hover-row">
                        <td style="padding-left: 20px;"><code><strong><?= e($row['ma_lop_hp']) ?></strong></code></td>
                        <td><strong><?= e($row['ten_hp']) ?></strong><br><small style="color:#666">Mã HP: <?= e($row['ma_hp']) ?></small></td>
                        <td style="text-align:center; font-weight:bold; color:var(--primary);">Thứ <?= (int)$row['thu'] === 8 ? 'CN' : (int)$row['thu'] ?></td>
                        <td style="text-align:center">Tiết <?= (int)$row['tiet_bat_dau'] ?> - <?= (int)$row['tiet_bat_dau'] + (int)$row['so_tiet'] - 1 ?></td>
                        <td style="text-align:center"><span class="badge" style="background:#e8f4fd; color:#0284c7; border: 1px solid #bae6fd; font-weight:bold;"><?= e($row['phong_hoc'] ?: '—') ?></span></td>
                        <td><strong><?= e($row['giang_vien'] ?: '—') ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- TRẠNG THÁI CHƯA TRA CỨU -->
      <div class="card fade-in" style="padding:40px; text-align:center; color:#64748b; border: 1px dashed #cbd5e1; border-radius:8px;">
        <i class="fas fa-search" style="font-size:48px; margin-bottom:15px; color:#94a3b8; display:block;"></i>
        <h3 style="margin:0 0 10px; color:#334155;">Tra cứu Thời khóa biểu cá nhân của Sinh viên</h3>
        <p style="margin:0; font-size:14px; max-width:500px; margin:0 auto; line-height:1.5;">Vui lòng <strong>nhập Mã số sinh viên</strong> ở hộp tìm kiếm phía trên để tra cứu thời khóa biểu cá nhân của sinh viên theo từng học kỳ ở hai chế độ: Dạng lưới hoặc Dạng danh sách.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function switchView(view) {
  if (view === 'grid') {
    document.getElementById('tkbGridView').style.display = 'block';
    document.getElementById('tkbListView').style.display = 'none';
    document.getElementById('btnGridView').className = 'btn btn-sm btn-primary';
    document.getElementById('btnListView').className = 'btn btn-sm btn-secondary';
    localStorage.setItem('student_tkb_view_mode', 'grid');
  } else {
    document.getElementById('tkbGridView').style.display = 'none';
    document.getElementById('tkbListView').style.display = 'block';
    document.getElementById('btnGridView').className = 'btn btn-sm btn-secondary';
    document.getElementById('btnListView').className = 'btn btn-sm btn-primary';
    localStorage.setItem('student_tkb_view_mode', 'list');
  }
}

// Khôi phục tùy chọn hiển thị trước đó từ localStorage
document.addEventListener("DOMContentLoaded", function() {
  const mode = localStorage.getItem('student_tkb_view_mode') || 'grid';
  if (document.getElementById('tkbGridView')) {
    switchView(mode);
  }
});
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
