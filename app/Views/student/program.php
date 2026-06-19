<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>
<?php
$special_tiet = [
    '1130299' => ['LT' => 40, 'TH' => 0,  'TL' => 10, 'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1130300' => ['LT' => 27, 'TH' => 0,  'TL' => 6,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1130049' => ['LT' => 27, 'TH' => 0,  'TL' => 6,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1130301' => ['LT' => 27, 'TH' => 0,  'TL' => 6,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1130302' => ['LT' => 27, 'TH' => 0,  'TL' => 6,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1130091' => ['LT' => 27, 'TH' => 0,  'TL' => 6,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1120168' => ['LT' => 37, 'TH' => 0,  'TL' => 8,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1120169' => ['LT' => 22, 'TH' => 0,  'TL' => 8,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1120170' => ['LT' => 14, 'TH' => 16, 'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1120171' => ['LT' => 4,  'TH' => 56, 'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1050261' => ['LT' => 0,  'TH' => 0,  'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 1, 'KL' => 0],
    '1050202' => ['LT' => 30, 'TH' => 20, 'TL' => 10, 'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1150422' => ['LT' => 25, 'TH' => 0,  'TL' => 10, 'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1050277' => ['LT' => 25, 'TH' => 0,  'TL' => 10, 'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1050271' => ['LT' => 30, 'TH' => 20, 'TL' => 10, 'TT' => 0, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1050214' => ['LT' => 0,  'TH' => 0,  'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 1, 'KL' => 0],
    '1050219' => ['LT' => 0,  'TH' => 0,  'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 1, 'KL' => 0],
    '1050272' => ['LT' => 0,  'TH' => 0,  'TL' => 0,  'TT' => 1, 'BTL' => 0, 'DA' => 0, 'KL' => 0],
    '1050331' => ['LT' => 0,  'TH' => 0,  'TL' => 0,  'TT' => 0, 'BTL' => 0, 'DA' => 1, 'KL' => 0]
];

$get_tiet = function($m) use ($special_tiet) {
    $ma = $m['ma_hp'];
    if (isset($special_tiet[$ma])) {
        return $special_tiet[$ma];
    }
    return [
        'LT' => (int)$m['so_tiet_ly_thuyet'],
        'TH' => (int)$m['so_tiet_thuc_hanh'],
        'TL' => 0,
        'TT' => 0,
        'BTL' => 0,
        'DA' => 0,
        'KL' => 0
    ];
};
?>

<style>
.table-wrap table thead tr th {
    background: #1e3a8a !important;
    color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    vertical-align: middle !important;
    text-align: center;
}
.table-wrap table thead tr th[rowspan] {
    vertical-align: middle !important;
}
</style>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Học tập</span>
        <span>›</span><span>Chương trình đào tạo</span>
      </div>
      <h1><i class="fas fa-list-alt"></i> Chương trình đào tạo</h1>
      <p>Ngành: <strong><?= e($sv['nganh']) ?></strong> — Khóa: <?= e($sv['nien_khoa']) ?></p>
    </div>

    <!-- Tổng tín chỉ theo nhóm -->
    <div class="stat-grid fade-in">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div><div class="stat-value"><?= $programData['tc_total'] ?></div><div class="stat-label">Tổng tín chỉ toàn khóa</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-book"></i></div>
        <div><div class="stat-value"><?= $programData['tc_by_loai']['Bắt buộc'] ?? 0 ?></div><div class="stat-label">Môn bắt buộc</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue" style="background:#d1ecf1;color:#0c5460"><i class="fas fa-globe"></i></div>
        <div><div class="stat-value"><?= $programData['tc_by_loai']['Đại cương'] ?? 0 ?></div><div class="stat-label">Đại cương</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-puzzle-piece"></i></div>
        <div><div class="stat-value"><?= $programData['tc_by_loai']['Tự chọn'] ?? 0 ?></div><div class="stat-label">Môn tự chọn</div></div>
      </div>
    </div>

    <!-- Bảng CTDT theo từng kỳ -->
    <!-- Bảng CTDT theo từng kỳ -->
    <?php foreach ($programData['by_hk'] as $hk => $mons): ?>
      <?php
        // Gom nhóm môn học
        $bat_buoc_mons = [];
        $tu_chon_mons = [];
        foreach ($mons as $m) {
            if ($m['loai'] === 'Tự chọn') {
                $tu_chon_mons[] = $m;
            } else {
                $bat_buoc_mons[] = $m;
            }
        }
        
        // Tính số tín chỉ
        $tc_bat_buoc = 0;
        foreach ($bat_buoc_mons as $m) {
            if (strpos($m['ma_hp'], '112') !== 0) {
                $tc_bat_buoc += $m['so_tin_chi'];
            }
        }
        
        $tc_tu_chon_calc = 0;
        $the_chat_count = 0;
        $chuyen_nganh_tu_chon = [];
        
        foreach ($tu_chon_mons as $tm) {
            if (strpos($tm['ma_hp'], '112') === 0) {
                $the_chat_count++;
            } else {
                $chuyen_nganh_tu_chon[] = $tm;
            }
        }
        
        if ($the_chat_count > 0) {
            $tc_tu_chon_calc += 1.0; // Chọn 1 môn thể chất (1.0 TC)
        }
        
        $cn_count = count($chuyen_nganh_tu_chon);
        if ($cn_count > 0) {
            if ($cn_count == 2) {
                $tc_tu_chon_calc += 3.0; // 2 môn chọn 1 (3.0 TC)
            } elseif ($cn_count >= 4) {
                $tc_tu_chon_calc += 6.0; // 4 môn chọn 2 (6.0 TC)
            } else {
                $tc_tu_chon_calc += array_sum(array_column($chuyen_nganh_tu_chon, 'so_tin_chi'));
            }
        }
        
        $tc_hk = $tc_bat_buoc + $tc_tu_chon_calc;
      ?>
      <div class="card mb-20 fade-in" style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div class="table-wrap" style="margin: 0;">
          <table style="width: 100%; border-collapse: collapse; min-width: 1000px; font-size: 13px;">
            <thead>
              <tr style="background: #1e3a8a; color: #fff; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th rowspan="2" style="width: 50px; text-align: center; border: 1px solid #cbd5e1; padding: 10px 6px; color: #fff;">TT</th>
                <th rowspan="2" style="width: 120px; text-align: center; border: 1px solid #cbd5e1; padding: 10px 6px; color: #fff;">MÃ HỌC PHẦN</th>
                <th rowspan="2" style="text-align: left; border: 1px solid #cbd5e1; padding: 10px 12px; color: #fff;">TÊN HỌC PHẦN</th>
                <th rowspan="2" style="width: 60px; text-align: center; border: 1px solid #cbd5e1; padding: 10px 6px; color: #fff;">SỐ TC</th>
                <th colspan="7" style="text-align: center; border: 1px solid #cbd5e1; padding: 6px; color: #fff;">SỐ TIẾT</th>
                <th rowspan="2" style="width: 180px; text-align: left; border: 1px solid #cbd5e1; padding: 10px 12px; color: #fff;">HỌC PHẦN HỌC TRƯỚC</th>
                <th rowspan="2" style="width: 180px; text-align: left; border: 1px solid #cbd5e1; padding: 10px 12px; color: #fff;">HỌC PHẦN THAY THẾ</th>
              </tr>
              <tr style="background: #1e3a8a; color: #fff; font-size: 11px; border-bottom: 1px solid #cbd5e1;">
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">LT</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">TH</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">TL</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">TT</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">BTL</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">DA</th>
                <th style="width: 35px; text-align: center; border: 1px solid #cbd5e1; padding: 4px; color: #fff;">KL</th>
              </tr>
            </thead>
            <tbody>
              <!-- Dòng học kỳ -->
              <tr style="background: #b2c5d4; font-weight: 700; color: #1e293b;">
                <td colspan="13" style="padding: 10px 12px; border: 1px solid #cbd5e1; font-size: 13.5px;">
                  Học kỳ <?= (int)$hk ?> (<?= number_format($tc_hk, 1) ?> tín chỉ)
                </td>
              </tr>
              
              <?php 
              $stt = 1;
              
              // 1. Nhóm Bắt buộc
              if (!empty($bat_buoc_mons)): 
              ?>
                <tr style="background: #fca5a5; font-weight: 700; color: #991b1b;">
                  <td colspan="13" style="padding: 8px 12px; border: 1px solid #cbd5e1;">
                    Bắt buộc (<?= number_format($tc_bat_buoc, 1) ?> tín chỉ)
                  </td>
                </tr>
                <?php foreach ($bat_buoc_mons as $m): 
                  $is_cond = (strpos($m['ma_hp'], '112') === 0);
                ?>
                  <?php 
                    $tiet = $get_tiet($m); 
                  ?>
                  <tr style="background: #fff; color: #1e293b;">
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><?= $stt++ ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><code><?= e($m['ma_hp']) ?></code></td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-weight: 500;">
                      <?= e($m['ten_hp']) ?><?= $is_cond ? ' <span style="color:#ef4444; font-weight:bold;">*</span>' : '' ?>
                    </td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px; font-weight: 700;"><?= (int)$m['so_tin_chi'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['LT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['LT'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TH'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TH'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TL'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TT'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['BTL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['BTL'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['DA'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['DA'] ?></td>
                    <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['KL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['KL'] ?></td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #475569;"><?= !empty($m['ma_hp_tien_quyet']) ? e($m['ma_hp_tien_quyet']) : '' ?></td>
                    <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #cbd5e1;">—</td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>

              <?php 
              // 2. Nhóm Tự chọn
              if (!empty($tu_chon_mons)): 
              ?>
                <tr style="background: #fca5a5; font-weight: 700; color: #991b1b;">
                  <td colspan="13" style="padding: 8px 12px; border: 1px solid #cbd5e1;">
                    Tự chọn (<?= number_format($tc_tu_chon_calc, 1) ?> tín chỉ)
                  </td>
                </tr>
                
                <?php
                // Tách riêng thể chất (112) vào Nhóm 01 giống trong ảnh
                $the_chat_mons = [];
                $other_tu_chon = [];
                foreach ($tu_chon_mons as $m) {
                    if (strpos($m['ma_hp'], '112') === 0) {
                        $the_chat_mons[] = $m;
                    } else {
                        $other_tu_chon[] = $m;
                    }
                }
                
                if (!empty($the_chat_mons)):
                ?>
                  <tr style="background: #99f6e4; font-weight: 700; color: #0f766e;">
                    <td colspan="13" style="padding: 6px 12px; border: 1px solid #cbd5e1; font-size: 12.5px;">
                      Nhóm 0<?= (int)$hk ?> (1.0 tín chỉ)
                    </td>
                  </tr>
                  <?php foreach ($the_chat_mons as $m): 
                    $is_cond = (strpos($m['ma_hp'], '112') === 0);
                  ?>
                    <?php 
                      $tiet = $get_tiet($m); 
                    ?>
                    <tr style="background: #fff; color: #1e293b;">
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><?= $stt++ ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><code><?= e($m['ma_hp']) ?></code></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-weight: 500;">
                        <?= e($m['ten_hp']) ?><?= $is_cond ? ' <span style="color:#ef4444; font-weight:bold;">*</span>' : '' ?>
                      </td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px; font-weight: 700;"><?= (int)$m['so_tin_chi'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['LT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['LT'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TH'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TH'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TL'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TT'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['BTL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['BTL'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['DA'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['DA'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['KL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['KL'] ?></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #475569;"><?= !empty($m['ma_hp_tien_quyet']) ? e($m['ma_hp_tien_quyet']) : '' ?></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #cbd5e1;">—</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
                
                <?php
                // Hiển thị các môn tự chọn khác (nếu có)
                if (!empty($other_tu_chon)):
                  $nhom_tc = 3.0;
                  if ($hk == 8) {
                      $nhom_tc = 6.0;
                  }
                  $nhom_id = 4;
                  if ($hk == 5) $nhom_id = 4;
                  elseif ($hk == 6) $nhom_id = 5;
                  elseif ($hk == 7) $nhom_id = 6;
                  elseif ($hk == 8) $nhom_id = 7;
                ?>
                  <tr style="background: #99f6e4; font-weight: 700; color: #0f766e;">
                    <td colspan="13" style="padding: 6px 12px; border: 1px solid #cbd5e1; font-size: 12.5px;">
                      Nhóm 0<?= $nhom_id ?> (<?= number_format($nhom_tc, 1) ?> tín chỉ)
                    </td>
                  </tr>
                  <?php foreach ($other_tu_chon as $m): 
                    $is_cond = (strpos($m['ma_hp'], '112') === 0);
                  ?>
                    <?php 
                      $tiet = $get_tiet($m); 
                    ?>
                    <tr style="background: #fff; color: #1e293b;">
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><?= $stt++ ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px;"><code><?= e($m['ma_hp']) ?></code></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-weight: 500;">
                        <?= e($m['ten_hp']) ?><?= $is_cond ? ' <span style="color:#ef4444; font-weight:bold;">*</span>' : '' ?>
                      </td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 6px; font-weight: 700;"><?= (int)$m['so_tin_chi'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['LT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['LT'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TH'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TH'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TL'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['TT'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['TT'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['BTL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['BTL'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['DA'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['DA'] ?></td>
                      <td style="text-align: center; border: 1px solid #cbd5e1; padding: 8px 4px; <?= $tiet['KL'] == 0 ? 'color:#cbd5e1;' : '' ?>"><?= $tiet['KL'] ?></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #475569;"><?= !empty($m['ma_hp_tien_quyet']) ? e($m['ma_hp_tien_quyet']) : '' ?></td>
                      <td style="border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 12px; color: #cbd5e1;">—</td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Ghi chú chân trang -->
    <div style="font-size: 12px; color: #475569; line-height: 1.6; margin-top: 20px; padding: 12px 16px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 500;" class="fade-in">
      <strong>- Ghi chú:</strong> LT: Lý thuyết; TH: Thực hành; TT: Thực tập, thực tế...; BTL: Bài tập lớn; DA: Đồ án; KL: Khóa luận tốt nghiệp; (*) Học phần điều kiện
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
