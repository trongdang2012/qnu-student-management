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
        <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0">
          <div class="form-group" style="margin:0;min-width:110px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= $hoc_ky === $i ? 'selected' : '' ?>>Học kỳ <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:140px">
            <label style="font-size:12px">Năm học</label>
            <input type="text" name="nam_hoc" class="form-control" placeholder="2023-2024" value="<?= e($nam_hoc) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Lớp học</label>
            <select name="lop" class="form-control">
              <option value="">Tất cả lớp</option>
              <?php foreach ($list_lop as $l): ?>
                <option value="<?= e($l['lop']) ?>" <?= $lop_filter === $l['lop'] ? 'selected' : '' ?>><?= e($l['lop']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm sinh viên</label>
            <input type="text" name="search" class="form-control" placeholder="Mã SV hoặc Họ tên..." value="<?= e($search) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/diem/ren-luyen" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (empty($list_sv)): ?>
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
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list_sv as $row): 
                  $t1 = 0; $t2 = 0; $t3 = 0; $t4 = 0; $t5 = 0; $user_note = '';
                  if (!empty($row['ghi_chu'])) {
                      $json = json_decode($row['ghi_chu'], true);
                      if (is_array($json) && isset($json['t1'])) {
                          $t1 = (int)$json['t1'];
                          $t2 = (int)$json['t2'];
                          $t3 = (int)$json['t3'];
                          $t4 = (int)$json['t4'];
                          $t5 = (int)$json['t5'];
                          $user_note = $json['user_note'] ?? '';
                      } else {
                          $user_note = $row['ghi_chu'];
                          $s = (int)$row['diem'];
                          $t1 = min(30, round($s * 0.3));
                          $t2 = min(25, round($s * 0.25));
                          $t3 = min(20, round($s * 0.2));
                          $t4 = min(15, round($s * 0.15));
                          $t5 = max(0, $s - ($t1 + $t2 + $t3 + $t4));
                      }
                  }
                ?>
                  <tr>
                    <td><code><?= e($row['ma_sv']) ?></code></td>
                    <td style="font-weight: 500"><?= e($row['ho_ten']) ?></td>
                    <td><?= e($row['lop'] ?? 'Chưa xếp lớp') ?></td>
                    <td><?= e($row['nganh'] ?? 'Chưa rõ') ?></td>
                    <td style="text-align:center; font-weight:bold; font-size:16px">
                      <?= is_null($row['diem']) ? '<span style="color:#bbb">—</span>' : (int)$row['diem'] ?>
                    </td>
                    <td style="text-align:center">
                      <?php if (!is_null($row['xep_loai'])): ?>
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
                        <span class="badge badge-secondary" style="background:#bbb; font-size:12px">Chưa nhập</span>
                      <?php endif; ?>
                    </td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:13px; color:#555">
                      <?= e($user_note) ?: '<span style="color:#bbb">Không có</span>' ?>
                    </td>
                    <td style="text-align:center">
                      <button class="btn btn-sm btn-primary" type="button" 
                              onclick="openDrlModal(this)"
                              data-id="<?= $row['sinh_vien_id'] ?>"
                              data-name="<?= e($row['ho_ten']) ?>"
                              data-code="<?= e($row['ma_sv']) ?>"
                              data-t1="<?= $t1 ?>"
                              data-t2="<?= $t2 ?>"
                              data-t3="<?= $t3 ?>"
                              data-t4="<?= $t4 ?>"
                              data-t5="<?= $t5 ?>"
                              data-note="<?= e($user_note) ?>">
                        <i class="fas fa-edit"></i> Nhập/Sửa
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Modal nhập điểm rèn luyện -->
    <div class="modal" id="drlModal">
      <div class="modal-content" style="max-width:650px">
        <div class="modal-header">
          <h2>Nhập/Sửa điểm rèn luyện</h2>
          <button class="modal-close" type="button" onclick="closeDrlModal()">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/admin/diem/ren-luyen/save" id="drlForm">
          <input type="hidden" name="action" value="save_drl">
          <input type="hidden" name="sinh_vien_id" id="m_sv_id">
          <input type="hidden" name="hoc_ky" value="<?= $hoc_ky ?>">
          <input type="hidden" name="nam_hoc" value="<?= e($nam_hoc) ?>">
          <input type="hidden" name="search" value="<?= e($search) ?>">
          <input type="hidden" name="lop" value="<?= e($lop_filter) ?>">
          
          <div style="background:#f4f6fa; padding:12px; border-radius:6px; margin-bottom:20px; display:flex; justify-content:space-between">
            <div>Sinh viên: <strong id="m_sv_name">...</strong></div>
            <div>Mã số: <strong id="m_sv_code">...</strong></div>
          </div>
          
          <p style="font-size:13px; color:#777; margin-bottom:15px"><i class="fas fa-info-circle"></i> Nhập điểm chi tiết cho từng tiêu chí đánh giá (tổng điểm tối đa là 100):</p>
          
          <!-- Tiêu chí 1 -->
          <div class="form-row full" style="margin-bottom:12px">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <label style="font-weight:500; font-size:14px; margin:0">1. Ý thức học tập (Max 30)</label>
              <input type="number" name="t1" id="m_t1" class="form-control crit-input" min="0" max="30" required style="max-width:80px; text-align:center">
            </div>
          </div>
          
          <!-- Tiêu chí 2 -->
          <div class="form-row full" style="margin-bottom:12px">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <label style="font-weight:500; font-size:14px; margin:0">2. Chấp hành nội quy, quy chế (Max 25)</label>
              <input type="number" name="t2" id="m_t2" class="form-control crit-input" min="0" max="25" required style="max-width:80px; text-align:center">
            </div>
          </div>
          
          <!-- Tiêu chí 3 -->
          <div class="form-row full" style="margin-bottom:12px">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <label style="font-weight:500; font-size:14px; margin:0">3. Hoạt động chính trị - xã hội, văn thể mỹ (Max 20)</label>
              <input type="number" name="t3" id="m_t3" class="form-control crit-input" min="0" max="20" required style="max-width:80px; text-align:center">
            </div>
          </div>
          
          <!-- Tiêu chí 4 -->
          <div class="form-row full" style="margin-bottom:12px">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <label style="font-weight:500; font-size:14px; margin:0">4. Ý thức công dân và cộng đồng (Max 15)</label>
              <input type="number" name="t4" id="m_t4" class="form-control crit-input" min="0" max="15" required style="max-width:80px; text-align:center">
            </div>
          </div>
          
          <!-- Tiêu chí 5 -->
          <div class="form-row full" style="margin-bottom:15px">
            <div style="display:flex; justify-content:space-between; align-items:center">
              <label style="font-weight:500; font-size:14px; margin:0">5. Công tác lớp, đoàn thể và thành tích (Max 10)</label>
              <input type="number" name="t5" id="m_t5" class="form-control crit-input" min="0" max="10" required style="max-width:80px; text-align:center">
            </div>
          </div>
          
          <!-- Tổng điểm & Xếp loại hiển thị trực quan -->
          <div style="border-top:2px dashed #eee; padding:15px 0; margin-top:15px; display:flex; justify-content:space-between; align-items:center">
            <div style="font-size:18px; font-weight:bold">
              Tổng điểm rèn luyện: <span id="m_total_display" style="color:var(--primary)">0</span> / 100
            </div>
            <div style="font-size:16px; font-weight:bold">
              Xếp loại: <span id="m_grade_display" class="badge badge-secondary">Chưa rõ</span>
            </div>
          </div>
          
          <!-- Ghi chú đóng góp -->
          <div class="form-row full">
            <div class="form-group">
              <label>Ghi chú / Thành tích nổi bật</label>
              <textarea name="user_note" id="m_note" class="form-control" placeholder="Nhập ghi chú khen thưởng, vi phạm hoặc đóng góp xã hội..." rows="2"></textarea>
            </div>
          </div>
          
          <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeDrlModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu điểm</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

<script>
const modal = document.getElementById('drlModal');
const critInputs = document.querySelectorAll('.crit-input');
const totalDisplay = document.getElementById('m_total_display');
const gradeDisplay = document.getElementById('m_grade_display');

function getXepLoaiRL(score) {
    if (score >= 90) return { label: 'Xuất sắc', type: 'success' };
    if (score >= 80) return { label: 'Tốt', type: 'success' };
    if (score >= 70) return { label: 'Khá', type: 'primary' };
    if (score >= 50) return { label: 'Trung bình', type: 'warning' };
    if (score >= 30) return { label: 'Yếu', type: 'danger' };
    return { label: 'Kém', type: 'danger' };
}

function calculateTotal() {
    let total = 0;
    critInputs.forEach(input => {
        let val = parseInt(input.value.trim());
        if (isNaN(val) || val < 0) val = 0;
        
        const max = parseInt(input.max);
        if (val > max) val = max;
        
        total += val;
    });
    
    totalDisplay.textContent = total;
    const rl = getXepLoaiRL(total);
    gradeDisplay.textContent = rl.label;
    gradeDisplay.className = `badge badge-${rl.type}`;
}

critInputs.forEach(input => {
    input.addEventListener('input', calculateTotal);
    input.addEventListener('blur', function() {
        if (this.value.trim() === '') this.value = 0;
        calculateTotal();
    });
});

function openDrlModal(btn) {
    document.getElementById('m_sv_id').value = btn.dataset.id;
    document.getElementById('m_sv_name').textContent = btn.dataset.name;
    document.getElementById('m_sv_code').textContent = btn.dataset.code;
    
    document.getElementById('m_t1').value = btn.dataset.t1;
    document.getElementById('m_t2').value = btn.dataset.t2;
    document.getElementById('m_t3').value = btn.dataset.t3;
    document.getElementById('m_t4').value = btn.dataset.t4;
    document.getElementById('m_t5').value = btn.dataset.t5;
    
    document.getElementById('m_note').value = btn.dataset.note;
    
    calculateTotal();
    modal.classList.add('active');
}

function closeDrlModal() {
    modal.classList.remove('active');
}
</script>
