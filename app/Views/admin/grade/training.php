<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Điểm rèn luyện</span>
      </div>
      <h1><i class="fas fa-star"></i> Quản lý điểm rèn luyện</h1>
      <p>Quản lý và cập nhật điểm rèn luyện theo học kỳ cho sinh viên Đại học Quy Nhơn.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0;display:flex;flex-wrap:wrap;gap:12px">
          <div class="form-group" style="margin:0;min-width:110px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control" required>
              <option value="">-- Học kỳ --</option>
              <option value="1" <?= $hoc_ky === 1 ? 'selected' : '' ?>>Học kỳ 1</option>
              <option value="2" <?= $hoc_ky === 2 ? 'selected' : '' ?>>Học kỳ 2</option>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:140px">
            <label style="font-size:12px">Năm học</label>
            <select name="nam_hoc" class="form-control" required>
              <option value="">-- Năm học --</option>
              <?php 
              $currentYear = (int)date('Y');
              for ($y = $currentYear - 4; $y <= $currentYear + 1; $y++): 
                  $yr = $y . '-' . ($y + 1);
              ?>
                <option value="<?= $yr ?>" <?= $nam_hoc === $yr ? 'selected' : '' ?>><?= $yr ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:160px">
            <label style="font-size:12px">Khoa</label>
            <select name="khoa" id="f_khoa" class="form-control">
              <option value="">-- Chọn khoa --</option>
              <?php foreach ($list_khoa as $k): ?>
                <option value="<?= e($k['khoa']) ?>" <?= $khoa === $k['khoa'] ? 'selected' : '' ?>><?= e($k['khoa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:160px">
            <label style="font-size:12px">Ngành học</label>
            <select name="nganh" id="f_nganh" class="form-control">
              <option value="">-- Chọn ngành --</option>
              <?php foreach ($list_nganh as $n): ?>
                <option value="<?= e($n['nganh']) ?>" <?= $nganh === $n['nganh'] ? 'selected' : '' ?>><?= e($n['nganh']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Lớp học</label>
            <select name="lop" id="f_lop" class="form-control">
              <option value="">-- Chọn lớp --</option>
              <?php foreach ($list_lop as $l): ?>
                <option value="<?= e($l['lop']) ?>" <?= $lop_filter === $l['lop'] ? 'selected' : '' ?>><?= e($l['lop']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm sinh viên</label>
            <input type="text" name="search" class="form-control" placeholder="Mã SV hoặc Họ tên..." value="<?= e($search) ?>">
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
            <a href="<?= BASE_URL ?>/admin/diem/ren-luyen" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <?php if (!empty($lop_filter)): ?>
        <div class="card-header" style="background:#fafafa; flex-wrap: wrap; display:flex; justify-content:space-between; align-items:center; padding:12px 20px; border-bottom:1px solid #eee">
          <h3 style="margin:0"><i class="fas fa-users"></i> Danh sách sinh viên (<?= count($list_sv) ?>)</h3>
          
          <div style="display:flex; gap:10px; align-items:center; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/admin/diem/ren-luyen/export-template?lop=<?= urlencode($lop_filter) ?>&hoc_ky=<?= $hoc_ky ?>&nam_hoc=<?= urlencode($nam_hoc) ?>&khoa=<?= urlencode($khoa) ?>&nganh=<?= urlencode($nganh) ?>" class="btn btn-info btn-sm" style="color:white; background:#17a2b8; border:none; padding: 6px 12px; border-radius: 4px; text-decoration:none; font-size:13px">
                <i class="fas fa-download"></i> Tải template (Excel/CSV)
            </a>
            <form action="<?= BASE_URL ?>/admin/diem/ren-luyen/import" method="POST" enctype="multipart/form-data" style="display:inline-flex; align-items:center; gap:8px; margin:0;" id="importForm">
                <input type="hidden" name="hoc_ky" value="<?= $hoc_ky ?>">
                <input type="hidden" name="nam_hoc" value="<?= e($nam_hoc) ?>">
                <input type="hidden" name="khoa" value="<?= e($khoa) ?>">
                <input type="hidden" name="nganh" value="<?= e($nganh) ?>">
                <input type="hidden" name="lop" value="<?= e($lop_filter) ?>">
                
                <div style="position:relative; display:inline-block">
                  <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .csv" required style="display:none" onchange="updateFileName(this)">
                  <label for="excel_file" class="btn btn-secondary btn-sm" style="margin:0; cursor:pointer; background:#f1f3f5; border:1px solid #ced4da; color:#495057; padding: 6px 12px;">
                    <i class="fas fa-cloud-upload-alt"></i> <span id="file-label-text">Chọn file (Excel/CSV)</span>
                  </label>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size:13px"><i class="fas fa-upload"></i> Nhập/Đè điểm</button>
            </form>
          </div>
        </div>
        <div style="padding:10px 20px; background:#e9ecef; font-size:13px; color:#555; border-bottom: 1px solid #eee">
            <i class="fas fa-info-circle"></i> <strong>Hướng dẫn:</strong> Vui lòng tải template, mở bằng Excel để nhập điểm, sau đó tải lên file định dạng <strong>.xlsx</strong> hoặc <strong>.csv</strong>. Việc tải lên sẽ <strong>ghi đè</strong> điểm cũ (nếu có).
        </div>
      <?php endif; ?>

      <div class="card-body" style="padding:0">
        <?php if (empty($lop_filter)): ?>
          <div style="padding:40px;text-align:center;color:#555">
            <i class="fas fa-filter" style="font-size:48px;margin-bottom:16px;color:#a8b2c1;display:block"></i>
            <h3 style="margin:0 0 8px 0; font-weight:600">Vui lòng chọn đầy đủ thông tin bộ lọc</h3>
            <p style="margin:0;color:#777;font-size:14px">Chọn Khoa → Ngành học → Lớp học để xem danh sách sinh viên và thực hiện nhập điểm.</p>
          </div>
        <?php elseif (empty($list_sv)): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-users-slash" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy sinh viên nào phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>MSSV</th>
                  <th>Họ và tên</th>
                  <th>Lớp học</th>
                  <th>Ngành học</th>
                  <th style="text-align:center">Điểm RL</th>
                  <th style="text-align:center">Xếp loại</th>
                  <th>Ghi chú / Đóng góp</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list_sv as $row): 
                  $user_note = '';
                  if (!empty($row['ghi_chu'])) {
                      $json = json_decode($row['ghi_chu'], true);
                      if (is_array($json) && isset($json['user_note'])) {
                          $user_note = $json['user_note'] ?? '';
                      } else {
                          $user_note = $row['ghi_chu'];
                      }
                  }
                ?>
                  <tr>
                    <td><code><?= e($row['ma_sv']) ?></code></td>
                    <td style="font-weight: 500"><?= e($row['ho_ten']) ?></td>
                    <td><?= e($row['lop'] ?? 'Chưa xếp lớp') ?></td>
                    <td><?= e($row['nganh'] ?? 'Chưa rõ') ?></td>
                    <td style="text-align:center; font-weight:bold; font-size:15px">
                      <?= is_null($row['diem']) ? '<span style="color:#bbb; font-weight:normal; font-size:13px">Chưa có điểm</span>' : (int)$row['diem'] ?>
                    </td>
                    <td style="text-align:center">
                      <?php if (!is_null($row['diem']) && !is_null($row['xep_loai'])): ?>
                        <?php 
                          $rl_badge = match($row['xep_loai']) {
                              'Xuất sắc' => 'success',
                              'Tốt' => 'success',
                              'Khá' => 'primary',
                              'Trung bình' => 'warning',
                              'Yếu' => 'danger',
                              default => 'secondary'
                          };
                        ?>
                        <span class="badge badge-<?= $rl_badge ?>" style="font-size:12px; padding:4px 10px"><?= e($row['xep_loai']) ?></span>
                      <?php else: ?>
                        <span style="color:#bbb; font-size:13px">Chưa có điểm</span>
                      <?php endif; ?>
                    </td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:13px; color:#555">
                      <?= e($user_note) ?: '<span style="color:#bbb">Không có</span>' ?>
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
    const fKhoa = document.getElementById('f_khoa');
    const fNganh = document.getElementById('f_nganh');
    const fLop = document.getElementById('f_lop');

    // Helper to populate a select element
    function populateSelect(selectElem, items, valueKey, textKey) {
        const placeholder = selectElem.options[0];
        selectElem.innerHTML = '';
        if (placeholder) selectElem.appendChild(placeholder);
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item[valueKey];
            opt.textContent = item[textKey];
            selectElem.appendChild(opt);
        });
    }

    // Load departments when faculty changes
    fKhoa.addEventListener('change', function () {
        const khoa = this.value;
        const currentNganh = '<?= e($nganh) ?>';
        // Reset downstream selects
        fNganh.innerHTML = '<option value="">-- Chọn ngành --</option>';
        fNganh.disabled = true;
        fLop.innerHTML = '<option value="">-- Chọn lớp --</option>';
        fLop.disabled = true;
        if (!khoa) return;
        fetch(`<?= BASE_URL ?>/admin/diem/ren-luyen/departments?khoa=` + encodeURIComponent(khoa))
            .then(res => res.json())
            .then(data => {
                populateSelect(fNganh, data, 'nganh', 'nganh', currentNganh);
                fNganh.disabled = false;
                if (currentNganh) fNganh.dispatchEvent(new Event('change'));
            });
    });

    // Load classes when department changes
    fNganh.addEventListener('change', function () {
        const khoa = fKhoa.value;
        const nganh = this.value;
        const currentLop = '<?= e($lop_filter) ?>';
        fLop.innerHTML = '<option value="">-- Chọn lớp --</option>';
        fLop.disabled = true;
        if (!khoa || !nganh) return;
        fetch(`<?= BASE_URL ?>/admin/diem/ren-luyen/classes?khoa=` + encodeURIComponent(khoa) + `&nganh=` + encodeURIComponent(nganh))
            .then(res => res.json())
            .then(data => {
                populateSelect(fLop, data, 'lop', 'lop', currentLop);
                fLop.disabled = false;
            });
    });

    // Initial state handling
    document.addEventListener('DOMContentLoaded', () => {
        if (fKhoa.value) {
            fKhoa.dispatchEvent(new Event('change'));
        }
    });

    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : 'Chọn file (Excel/CSV)';
        document.getElementById('file-label-text').textContent = fileName;
    }
</script>
