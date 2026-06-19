<?php
function xepLoaiHocLuc(float $cpa): string {
    if ($cpa >= 3.6) return 'Xuất sắc';
    if ($cpa >= 3.2) return 'Giỏi';
    if ($cpa >= 2.5) return 'Khá';
    if ($cpa >= 2.0) return 'Trung bình';
    return 'Yếu';
}
function colorCPA(float $cpa): string {
    if ($cpa >= 3.2) return 'var(--success)';
    if ($cpa >= 2.5) return 'var(--primary)';
    if ($cpa >= 2.0) return '#fd7e14';
    return 'var(--danger)';
}
?>
<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Học tập</span>
        <span>›</span><span>Điểm học tập</span>
      </div>
      <h1><i class="fas fa-graduation-cap"></i> Bảng điểm học tập</h1>
      <p>Tra cứu điểm chi tiết và CPA của bạn. <span style="color: #dc3545; font-weight: 500;">(Những môn có dấu * sẽ không tính điểm trung bình mà chỉ là môn điều kiện).</span></p>
    </div>

    <!-- Tổng quan CPA -->
    <div class="card mb-20 fade-in">
      <div class="card-body">
        <div class="diem-summary">
          <div class="diem-summary-item">
            <div class="ds-value" style="color:<?= colorCPA($gradesData['cpa']) ?>"><?= number_format($gradesData['cpa'], 2) ?></div>
            <div class="ds-label">CPA (Hệ 4)</div>
          </div>
          <div class="diem-summary-item green-top">
            <div class="ds-value"><?= $gradesData['tc_tich_luy'] ?></div>
            <div class="ds-label">Tín chỉ tích lũy</div>
          </div>
          <div class="diem-summary-item">
            <div class="ds-value"><?= $gradesData['so_mon'] ?></div>
            <div class="ds-label">Số học phần có điểm</div>
          </div>
          <div class="diem-summary-item red-top">
            <div class="ds-value"><?= $gradesData['so_mon_F'] ?></div>
            <div class="ds-label">Học phần chưa đạt (F)</div>
          </div>
          <div class="diem-summary-item green-top">
            <div class="ds-value" style="font-size:18px"><?= xepLoaiHocLuc($gradesData['cpa']) ?></div>
            <div class="ds-label">Xếp loại học lực</div>
          </div>
        </div>

        <!-- Thanh CPA -->
        <div>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span>CPA: <strong style="color:<?= colorCPA($gradesData['cpa']) ?>"><?= number_format($gradesData['cpa'],2) ?> / 4.0</strong></span>
            <span style="color:var(--text-muted)">Mục tiêu tốt nghiệp: ≥ 2.0</span>
          </div>
          <div class="progress" style="height:16px">
            <div class="progress-bar <?= $gradesData['cpa'] >= 3.2 ? 'green' : ($gradesData['cpa'] >= 2.0 ? '' : 'red') ?>"
                 style="width:0" data-width="<?= min(100, round($gradesData['cpa']/4*100)) ?>"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-20 fade-in">
      <div class="card-body" style="padding:12px 20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <span style="font-size:14px;font-weight:500">Lọc theo năm học:</span>
        <a href="<?= BASE_URL ?>/student/diem-hoc-tap"
           class="btn btn-sm <?= !$nh_filter ? 'btn-primary' : 'btn-outline' ?>">Tất cả</a>
        <?php foreach ($gradesData['list_nh'] as $nh): ?>
          <a href="?nh=<?= urlencode($nh['nam_hoc']) ?>"
             class="btn btn-sm <?= $nh_filter===$nh['nam_hoc'] ? 'btn-primary' : 'btn-outline' ?>">
            <?= e($nh['nam_hoc']) ?>
          </a>
        <?php endforeach; ?>

        <!-- Search -->
        <div style="margin-left:auto">
          <input type="text" id="tableSearch" data-table="#diemTable"
                 placeholder="🔍 Tìm học phần..." class="form-control"
                 style="max-width:220px;padding:7px 12px;font-size:14px">
        </div>
      </div>
    </div>

    <!-- Bảng điểm -->
    <?php
    $seen_subjects = []; // Mảng theo dõi điểm cao nhất của từng môn học tính đến thời điểm hiện tại để tính tích lũy cộng dồn
    ?>
    <?php foreach ($gradesData['by_nh_hk'] as $nh => $hk_groups): ?>
      <?php foreach ($hk_groups as $hk => $mons):
        $hk_tc_dang_ky = 0;
        $hk_tc_dat = 0;
        $hk_tc_truot = 0;
        $hk_sum_diem_he4 = 0;
        $hk_sum_diem_he10 = 0;
        $hk_tc_tinh_diem = 0;

        foreach ($mons as $m) {
            $ma_hp = $m['ma_hp'];
            $hp_id = $m['hoc_phan_id'];
            $tc = (int)$m['so_tin_chi'];
            $diem_he4 = $m['diem_he4'];
            $diem_tong = $m['diem_tong'];
            $is_dieu_kien = (strpos($ma_hp, '112') === 0);

            $hk_tc_dang_ky += $tc;

            if (!is_null($diem_he4)) {
                if ($diem_he4 >= 1.0) {
                    $hk_tc_dat += $tc;
                } else {
                    $hk_tc_truot += $tc;
                }

                if (!$is_dieu_kien) {
                    // Điểm trung bình của riêng học kỳ đó vẫn tính bình thường cho tất cả các môn trong kỳ
                    $hk_sum_diem_he4 += $diem_he4 * $tc;
                    $hk_sum_diem_he10 += $diem_tong * $tc;
                    $hk_tc_tinh_diem += $tc;

                    // Đối với điểm tích lũy cộng dồn: chỉ lưu điểm cao nhất của môn đó tính đến kỳ này
                    if (!isset($seen_subjects[$hp_id]) || $diem_he4 > $seen_subjects[$hp_id]['diem_he4']) {
                        $seen_subjects[$hp_id] = [
                            'diem_he4' => $diem_he4,
                            'diem_tong' => $diem_tong,
                            'so_tin_chi' => $tc
                        ];
                    }
                }
            }
        }

        $gpa_hk_he4 = $hk_tc_tinh_diem > 0 ? round($hk_sum_diem_he4 / $hk_tc_tinh_diem, 2) : 0;
        $gpa_hk_he10 = $hk_tc_tinh_diem > 0 ? round($hk_sum_diem_he10 / $hk_tc_tinh_diem, 2) : 0;

        // Tính toán các chỉ số tích lũy cộng dồn chính xác tính đến cuối học kỳ này từ $seen_subjects
        $accumulated_tc_tich_luy = 0;
        $accumulated_sum_diem_he4 = 0;
        $accumulated_sum_diem_he10 = 0;
        $accumulated_tc_tinh_diem = 0;

        foreach ($seen_subjects as $s_id => $s_val) {
            $s_he4 = $s_val['diem_he4'];
            $s_tong = $s_val['diem_tong'];
            $s_tc = $s_val['so_tin_chi'];

            if ($s_he4 >= 1.0) {
                $accumulated_tc_tich_luy += $s_tc;
            }
            $accumulated_sum_diem_he4 += $s_he4 * $s_tc;
            $accumulated_sum_diem_he10 += $s_tong * $s_tc;
            $accumulated_tc_tinh_diem += $s_tc;
        }

        $gpa_tich_luy_he4 = $accumulated_tc_tinh_diem > 0 ? round($accumulated_sum_diem_he4 / $accumulated_tc_tinh_diem, 2) : 0;
        $gpa_tich_luy_he10 = $accumulated_tc_tinh_diem > 0 ? round($accumulated_sum_diem_he10 / $accumulated_tc_tinh_diem, 2) : 0;
      ?>
      <div class="card mb-16 fade-in">
        <div class="table-wrap">
        <table id="diemTable" style="margin-bottom: 0;">
          <thead>
            <tr style="background: #e6f0fa; color: #1d2c5e;">
              <th style="width: 60px; text-align:center">STT</th>
              <th style="width: 120px;">Mã học phần</th>
              <th>Tên học phần</th>
              <th style="width: 80px; text-align:center">Tín chỉ</th>
              <th style="width: 100px; text-align:center">Điểm 10</th>
              <th style="width: 100px; text-align:center">Điểm 4</th>
              <th style="width: 100px; text-align:center">Điểm chữ</th>
              <th style="width: 100px; text-align:center">Kết quả</th>
              <th style="width: 80px; text-align:center">Chi tiết</th>
            </tr>
          </thead>
          <tbody>
            <!-- Dòng tiêu đề học kỳ chạy ngang bảng -->
            <tr style="background: #eef4fc; font-weight: 700; border-bottom: 2px solid #cbd5e1;">
              <td colspan="9" style="padding: 10px 14px; color: #1d2c5e; font-size: 14px;">
                Năm học: <?= e($nh) ?> - Học kỳ: HK<?= sprintf("%02d", $hk) ?>
              </td>
            </tr>
            
            <?php 
            $stt = 1;
            foreach ($mons as $m): 
              $ma_hp = $m['ma_hp'];
              $is_dieu_kien = (strpos($ma_hp, '112') === 0);
              $dat_mon = !is_null($m['diem_he4']) && $m['diem_he4'] >= 1.0;
              $chua_co = is_null($m['diem_tong']);
            ?>
            <tr style="transition: background 0.15s;">
              <td style="text-align:center; color: #666; font-size: 13.5px;"><?= $stt++ ?></td>
              <td><code style="font-size:13px; color: #334155; font-weight: 500;"><?= e($m['ma_hp']) ?></code></td>
              <td style="font-weight: 500; color: #1e293b;">
                <?= e($m['ten_hp']) ?><?= $is_dieu_kien ? ' <span style="color:#ef4444; font-weight:bold;">*</span>' : '' ?>
              </td>
              <td style="text-align:center"><?= (int)$m['so_tin_chi'] ?></td>
              
              <!-- Điểm 10 -->
              <td style="text-align:center; font-weight:700; color:<?= $chua_co ? 'var(--text-muted)' : (($m['diem_tong']>=5)?'#1e293b':'var(--danger)') ?>">
                <?= $chua_co ? '—' : number_format((float)$m['diem_tong'],1) ?>
              </td>
              
              <!-- Điểm 4 -->
              <td style="text-align:center; font-weight:600;">
                <?= $chua_co ? '—' : number_format((float)$m['diem_he4'],1) ?>
              </td>
              
              <!-- Điểm chữ -->
              <td style="text-align:center">
                <?php if (!$chua_co && !is_null($m['diem_chu'])): ?>
                  <span class="badge badge-<?= badgeDiemChu($m['diem_chu']) ?>" style="font-size:12.5px; padding:3px 10px; font-weight:600;">
                    <?= e($m['diem_chu']) ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--text-muted)">—</span>
                <?php endif; ?>
              </td>
              
              <!-- Kết quả -->
              <td style="text-align:center">
                <?php if ($chua_co): ?>
                  <span style="color:var(--text-muted)">—</span>
                <?php elseif ($dat_mon): ?>
                  <span style="color:var(--success); font-size: 1.15rem;"><i class="fas fa-check-circle"></i></span>
                <?php else: ?>
                  <span style="color:var(--danger); font-size: 1.15rem;"><i class="fas fa-times-circle"></i></span>
                <?php endif; ?>
              </td>
              
              <!-- Chi tiết -->
              <td style="text-align:center">
                <span onclick="toggleGradeDetail(this, 'detail-<?= $m['id'] ?>')" style="color: #64748b; cursor: pointer; transition: all 0.15s; display: inline-block; padding: 4px 8px;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#64748b'">
                  <i class="fas fa-chevron-down" style="font-size: 12px; transition: transform 0.2s;"></i>
                </span>
              </td>
            </tr>
            <tr id="detail-<?= $m['id'] ?>" class="detail-row" style="display: none; background: #f8fafc;">
              <td colspan="9" style="padding: 12px 20px; border-bottom: 1px solid var(--border);">
                <div style="display: flex; gap: 40px; justify-content: flex-start; align-items: center; font-size: 13.5px; color: #475569; flex-wrap: wrap;">
                  <div><strong>• Điểm chuyên cần (10%):</strong> <span style="font-weight: 600; color: #0f172a;"><?= is_null($m['diem_cc']) ? '—' : number_format((float)$m['diem_cc'], 1) ?></span></div>
                  <div><strong>• Điểm giữa kỳ (30%):</strong> <span style="font-weight: 600; color: #0f172a;"><?= is_null($m['diem_gk']) ? '—' : number_format((float)$m['diem_gk'], 1) ?></span></div>
                  <div><strong>• Điểm cuối kỳ (60%):</strong> <span style="font-weight: 600; color: #0f172a;"><?= is_null($m['diem_ck']) ? '—' : number_format((float)$m['diem_ck'], 1) ?></span></div>
                  <div style="margin-left: auto; font-style: italic; color: #94a3b8; font-size: 12.5px;">* Công thức: Tổng = CC×0.1 + GK×0.3 + CK×0.6</div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
        
        <!-- Phần thống kê dưới bảng -->
        <div class="grades-summary-footer" style="padding: 18px 24px; background: #fafcff; border-top: 1px solid var(--border); border-radius: 0 0 12px 12px; font-size: 14.5px; color: #334155; line-height: 1.6;">
          <div style="display: flex; flex-direction: column; gap: 8px;">
            <div><strong>• Tổng số tín chỉ:</strong> <?= $hk_tc_dang_ky ?></div>
            <div style="display: flex; gap: 40px; flex-wrap: wrap;">
              <span><strong>• Số tín chỉ đạt:</strong> <span style="color: var(--success); font-weight: 600;"><?= $hk_tc_dat ?></span></span>
              <span><strong>• Số tín chỉ không đạt:</strong> <span style="color: var(--danger); font-weight: 600;"><?= $hk_tc_truot ?></span></span>
            </div>
            <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 2px;">
              <span><strong>• Điểm trung bình học kỳ (Hệ 10):</strong> <span style="font-weight: 600;"><?= number_format($gpa_hk_he10, 2) ?></span></span>
              <span><strong>• Điểm trung bình học kỳ (Hệ 4):</strong> <span style="color: var(--primary); font-weight: 700;"><?= number_format($gpa_hk_he4, 2) ?></span></span>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
              <div><strong>• Số tín chỉ tích lũy:</strong> <span style="color: var(--success); font-weight: 600;"><?= $accumulated_tc_tich_luy ?></span></div>
              <div><strong>• Điểm trung bình tích lũy (Hệ 10):</strong> <span style="font-weight: 600;"><?= number_format($gpa_tich_luy_he10, 2) ?></span></div>
              <div><strong>• Điểm trung bình tích lũy (Hệ 4):</strong> <span style="color: var(--primary); font-weight: 700;"><?= number_format($gpa_tich_luy_he4, 2) ?></span></div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <?php if (empty($gradesData['diem_list'])): ?>
      <div class="card fade-in">
        <div class="card-body text-center" style="padding:40px">
          <div style="font-size:48px;margin-bottom:12px">📊</div>
          <h3 style="color:var(--text-muted)">Chưa có dữ liệu điểm</h3>
          <p class="text-muted">Bạn chưa có điểm học phần nào được ghi nhận.</p>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
function toggleGradeDetail(btn, rowId) {
    const detailRow = document.getElementById(rowId);
    if (!detailRow) return;
    
    const icon = btn.querySelector('i');
    if (detailRow.style.display === 'none') {
        detailRow.style.display = 'table-row';
        if (icon) icon.style.transform = 'rotate(180deg)';
        btn.style.color = 'var(--primary)';
    } else {
        detailRow.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
        btn.style.color = '#64748b';
    }
}
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
