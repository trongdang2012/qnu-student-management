<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/diem/hoc-tap">Điểm học tập</a>
        <span>›</span><span>Chi tiết</span>
      </div>
      <h1><i class="fas fa-edit"></i> Nhập/Sửa điểm chi tiết</h1>
      <p>
        Học phần: <strong><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</strong> | 
        Số tín chỉ: <strong><?= (int)$hp['so_tin_chi'] ?></strong> | 
        Học kỳ: <strong>HK <?= (int)$hp['hoc_ky'] ?></strong>
      </p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <div><?= $flash['msg'] ?></div>
      </div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
      <div class="card fade-in text-center" style="padding:40px">
        <div style="font-size:48px;margin-bottom:15px">⚠️</div>
        <h3 style="color:var(--text-muted)">Không có sinh viên trong học phần này</h3>
        <p class="text-muted">Chưa có sinh viên nào đăng ký và được duyệt học phần này trong hệ thống.</p>
        <div style="margin-top:20px">
          <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card fade-in">
        <div class="card-header" style="background:#fafafa">
          <h3><i class="fas fa-users"></i> Danh sách sinh viên (<?= count($students) ?>)</h3>
          <div style="display:flex; gap:10px">
            <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <button type="submit" form="gradesForm" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Lưu điểm</button>
          </div>
        </div>
        
        <div class="card-body" style="padding:0">
          <form id="gradesForm" method="POST" action="<?= BASE_URL ?>/admin/diem/hoc-tap/save">
            <input type="hidden" name="action" value="save_grades">
            <input type="hidden" name="hoc_phan_id" value="<?= $hoc_phan_id ?>">
            
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>MSSV</th>
                    <th>Họ và tên</th>
                    <th>Lớp</th>
                    <th style="text-align:center; width:120px">CC (10%)</th>
                    <th style="text-align:center; width:120px">GK (30%)</th>
                    <th style="text-align:center; width:120px">CK (60%)</th>
                    <th style="text-align:center; width:110px">Tổng kết</th>
                    <th style="text-align:center; width:100px">Hệ 4</th>
                    <th style="text-align:center; width:100px">Điểm chữ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $sv): ?>
                    <tr class="student-row" data-sv-id="<?= $sv['sinh_vien_id'] ?>">
                      <td><code><?= e($sv['ma_sv']) ?></code></td>
                      <td style="font-weight: 500"><?= e($sv['ho_ten']) ?></td>
                      <td><?= e($sv['lop'] ?? 'Chưa rõ') ?></td>
                      
                      <td style="text-align:center">
                        <input type="number" step="0.1" min="0" max="10" 
                               name="diem[<?= $sv['sinh_vien_id'] ?>][cc]" 
                               value="<?= is_null($sv['diem_cc']) ? '' : number_format((float)$sv['diem_cc'], 1) ?>" 
                               class="form-control text-center grade-cc" 
                               style="max-width:80px; margin:0 auto; padding:6px" placeholder="0 - 10">
                      </td>
                      
                      <td style="text-align:center">
                        <input type="number" step="0.1" min="0" max="10" 
                               name="diem[<?= $sv['sinh_vien_id'] ?>][gk]" 
                               value="<?= is_null($sv['diem_gk']) ? '' : number_format((float)$sv['diem_gk'], 1) ?>" 
                               class="form-control text-center grade-gk" 
                               style="max-width:80px; margin:0 auto; padding:6px" placeholder="0 - 10">
                      </td>
                      
                      <td style="text-align:center">
                        <input type="number" step="0.1" min="0" max="10" 
                               name="diem[<?= $sv['sinh_vien_id'] ?>][ck]" 
                               value="<?= is_null($sv['diem_ck']) ? '' : number_format((float)$sv['diem_ck'], 1) ?>" 
                               class="form-control text-center grade-ck" 
                               style="max-width:80px; margin:0 auto; padding:6px" placeholder="0 - 10">
                      </td>
                      
                      <td style="text-align:center; font-weight:700; font-size:15px; color:var(--primary)">
                        <span class="span-total"><?= is_null($sv['diem_tong']) ? '—' : number_format((float)$sv['diem_tong'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center; font-weight:500; color:#555">
                        <span class="span-he4"><?= is_null($sv['diem_he4']) ? '—' : number_format((float)$sv['diem_he4'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center">
                        <?php if (!is_null($sv['diem_chu'])): ?>
                          <span class="badge badge-<?= badgeDiemChu($sv['diem_chu']) ?> badge-letter" style="font-size:12px; min-width:35px">
                            <?= e($sv['diem_chu']) ?>
                          </span>
                        <?php else: ?>
                          <span class="badge badge-secondary badge-letter" style="font-size:12px; min-width:35px; background:#aaa">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:15px; padding:20px; background:#fcfcfc; border-top:1px solid #eee">
              <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy</a>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu bảng điểm</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.student-row');
    
    function getDiemChu(score) {
        if (score >= 9.0) return { letter: 'A+', type: 'success' };
        if (score >= 8.5) return { letter: 'A', type: 'success' };
        if (score >= 8.0) return { letter: 'B+', type: 'primary' };
        if (score >= 7.0) return { letter: 'B', type: 'primary' };
        if (score >= 6.5) return { letter: 'C+', type: 'warning' };
        if (score >= 5.5) return { letter: 'C', type: 'warning' };
        if (score >= 5.0) return { letter: 'D+', type: 'secondary' };
        if (score >= 4.0) return { letter: 'D', type: 'secondary' };
        return { letter: 'F', type: 'danger' };
    }
    
    function getDiemHe4(score) {
        if (score >= 9.0) return 4.0;
        if (score >= 8.5) return 3.7;
        if (score >= 8.0) return 3.5;
        if (score >= 7.0) return 3.0;
        if (score >= 6.5) return 2.5;
        if (score >= 5.5) return 2.0;
        if (score >= 5.0) return 1.5;
        if (score >= 4.0) return 1.0;
        return 0.0;
    }

    rows.forEach(row => {
        const ccInput = row.querySelector('.grade-cc');
        const gkInput = row.querySelector('.grade-gk');
        const ckInput = row.querySelector('.grade-ck');
        const totalSpan = row.querySelector('.span-total');
        const he4Span = row.querySelector('.span-he4');
        const letterBadge = row.querySelector('.badge-letter');
        
        function calculateRow() {
            const ccVal = ccInput.value.trim();
            const gkVal = gkInput.value.trim();
            const ckVal = ckInput.value.trim();
            
            if (ccVal === '' || gkVal === '' || ckVal === '') {
                totalSpan.textContent = '—';
                he4Span.textContent = '—';
                letterBadge.textContent = '—';
                letterBadge.className = 'badge badge-secondary badge-letter';
                letterBadge.style.backgroundColor = '#aaa';
                return;
            }
            
            const cc = parseFloat(ccVal);
            const gk = parseFloat(gkVal);
            const ck = parseFloat(ckVal);
            
            if (isNaN(cc) || cc < 0 || cc > 10 || 
                isNaN(gk) || gk < 0 || gk > 10 || 
                isNaN(ck) || ck < 0 || ck > 10) {
                totalSpan.textContent = 'Lỗi';
                he4Span.textContent = 'Lỗi';
                letterBadge.textContent = 'ERR';
                letterBadge.className = 'badge badge-danger badge-letter';
                letterBadge.style.backgroundColor = '';
                return;
            }
            
            const total = Math.round((cc * 0.1 + gk * 0.3 + ck * 0.6) * 100) / 100;
            totalSpan.textContent = total.toFixed(1);
            
            const he4 = getDiemHe4(total);
            he4Span.textContent = he4.toFixed(1);
            
            const chu = getDiemChu(total);
            letterBadge.textContent = chu.letter;
            letterBadge.className = `badge badge-${chu.type} badge-letter`;
            letterBadge.style.backgroundColor = '';
        }
        
        [ccInput, gkInput, ckInput].forEach(input => {
            input.addEventListener('input', calculateRow);
        });
    });
});
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
