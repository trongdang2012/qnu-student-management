<?php
$list_hk = [1,2,3,4,5,6,7,8];
$thu_list  = [2,3,4,5,6,7,8];
$tiet_list = range(1, 10);
$gio = [
    1 => '07:00 - 07:50',
    2 => '07:50 - 08:40',
    3 => '08:50 - 09:40',
    4 => '09:40 - 10:30',
    5 => '10:40 - 11:30',
    6 => '13:00 - 13:50',
    7 => '13:50 - 14:40',
    8 => '14:50 - 15:40',
    9 => '15:40 - 16:30',
    10 => '16:40 - 17:30'
];
$tiet_gio = [
    1 => ['start' => '07:00', 'end' => '07:50'],
    2 => ['start' => '07:50', 'end' => '08:40'],
    3 => ['start' => '08:50', 'end' => '09:40'],
    4 => ['start' => '09:40', 'end' => '10:30'],
    5 => ['start' => '10:40', 'end' => '11:30'],
    6 => ['start' => '13:00', 'end' => '13:50'],
    7 => ['start' => '13:50', 'end' => '14:40'],
    8 => ['start' => '14:50', 'end' => '15:40'],
    9 => ['start' => '15:40', 'end' => '16:30'],
    10 => ['start' => '16:40', 'end' => '17:30']
];

function getColorForSubject(string $maHp): array {
    $hash = md5($maHp);
    $colors = [
        ['bg' => '#eef2ff', 'border' => '#6366f1', 'text' => '#4f46e5'], // Indigo
        ['bg' => '#f0fdf4', 'border' => '#22c55e', 'text' => '#16a34a'], // Green
        ['bg' => '#fff7ed', 'border' => '#f97316', 'text' => '#ea580c'], // Orange
        ['bg' => '#faf5ff', 'border' => '#a855f7', 'text' => '#9333ea'], // Purple
        ['bg' => '#ecfeff', 'border' => '#06b6d4', 'text' => '#0891b2'], // Cyan
        ['bg' => '#fff1f2', 'border' => '#f43f5e', 'text' => '#e11d48'], // Rose
        ['bg' => '#fef8e7', 'border' => '#eab308', 'text' => '#ca8a04'], // Yellow
    ];
    $index = hexdec(substr($hash, 0, 4)) % count($colors);
    return $colors[$index];
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
        <span>›</span><span>Thời khóa biểu</span>
      </div>
      <h1><i class="fas fa-calendar-alt"></i> Thời khóa biểu</h1>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-20 fade-in">
      <div class="card-body" style="padding:14px">
        <form method="GET" style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end">
          <div class="form-group" style="margin:0;min-width:160px">
            <label style="margin-bottom:4px">Năm học</label>
            <select name="nh" class="form-control">
              <?php if (empty($scheduleData['list_nh'])): ?>
                <option value="<?= NAM_HOC_HIEN_TAI ?>"><?= NAM_HOC_HIEN_TAI ?></option>
              <?php else: ?>
                <?php foreach ($scheduleData['list_nh'] as $nh): ?>
                  <option value="<?= e($nh['nam_hoc']) ?>" <?= $nh['nam_hoc']==$nh_filter?'selected':'' ?>><?= e($nh['nam_hoc']) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:120px">
            <label style="margin-bottom:4px">Học kỳ</label>
            <select name="hk" class="form-control">
              <?php foreach ($list_hk as $hk): ?>
                <option value="<?= $hk ?>" <?= $hk==$hk_filter?'selected':'' ?>>Học kỳ <?= $hk ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
        </form>
      </div>
    </div>

    <?php if (empty($scheduleData['tkb'])): ?>
      <div class="card fade-in">
        <div class="card-body text-center" style="padding:40px">
          <div style="font-size:48px;margin-bottom:12px">📅</div>
          <h3 style="color:var(--text-muted)">Chưa có thời khóa biểu</h3>
          <p class="text-muted">Không tìm thấy TKB cho học kỳ <?= $hk_filter ?> — <?= e($nh_filter) ?>.</p>
        </div>
      </div>
    <?php else: ?>

    <!-- Bảng TKB -->
    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-th"></i> Lịch học — HK<?= $hk_filter ?> / <?= e($nh_filter) ?></h3>
        <span class="badge badge-primary"><?= $scheduleData['tong_so_tiet'] ?> tiết / tuần</span>
      </div>
      <div class="card-body" style="padding:16px">
        <div class="table-wrap">
          <?php
          // Gom nhóm môn học theo buổi
          $grid_buoi = [];
          foreach ($thu_list as $thu) {
              $grid_buoi[$thu]['sang'] = [];
              $grid_buoi[$thu]['chieu'] = [];
              $grid_buoi[$thu]['toi'] = [];
          }

          foreach ($scheduleData['tkb'] as $row) {
              $thu = (int)$row['thu'];
              $tiet_bd = (int)$row['tiet_bat_dau'];
              if ($tiet_bd <= 5) {
                  $grid_buoi[$thu]['sang'][] = $row;
              } elseif ($tiet_bd <= 10) {
                  $grid_buoi[$thu]['chieu'][] = $row;
              } else {
                  $grid_buoi[$thu]['toi'][] = $row;
              }
          }

          // Lấy ngày trong tuần hiện hành
          $today = new DateTime();
          $dayOfWeek = (int)$today->format('N');
          $monday = clone $today;
          if ($dayOfWeek > 1) {
              $monday->modify('-' . ($dayOfWeek - 1) . ' days');
          }
          $ngay_trong_tuan = [];
          for ($i = 0; $i < 7; $i++) {
              $d = clone $monday;
              $d->modify('+' . $i . ' days');
              $ngay_trong_tuan[$i + 2] = $d->format('d/m/Y');
          }
          ?>
          <table class="tkb-grid-table" style="width:100%; border-collapse:collapse; min-width:900px;">
            <thead>
              <tr style="background:#1e3a8a; color:#fff;">
                <th style="width:90px; text-align:center; padding:12px; border:1px solid #cbd5e1; font-weight:700;">Buổi</th>
                <?php foreach ($thu_list as $thu): ?>
                  <th style="text-align:center; padding:12px; border:1px solid #cbd5e1;">
                    <div style="font-weight:700; font-size:14px; color:#fff;"><?= e(tenThu($thu)) ?></div>
                    <div style="font-size:11px; font-weight:normal; margin-top:4px; opacity:0.9;"><?= $ngay_trong_tuan[$thu] ?></div>
                  </th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach (['sang' => 'Sáng', 'chieu' => 'Chiều', 'toi' => 'Tối'] as $buoi_key => $buoi_name): ?>
                <tr>
                  <td class="tkb-session-cell" style="text-align:center; vertical-align:middle; font-weight:700; background:#f8fafc; border:1px solid #cbd5e1; color:#1e293b; padding:15px 10px;">
                    <strong><?= $buoi_name ?></strong>
                  </td>
                  <?php foreach ($thu_list as $thu): 
                    $subjects = $grid_buoi[$thu][$buoi_key];
                  ?>
                    <td class="tkb-grid-cell" style="vertical-align:top; border:1px solid #cbd5e1; background:#fff; padding:10px; width:14.28%; min-height:160px;">
                      <?php if (!empty($subjects)): ?>
                        <div style="display:flex; flex-direction:column; gap:10px; height:100%;">
                          <?php foreach ($subjects as $subject): 
                            $col = getColorForSubject($subject['ma_hp']);
                            $start_tiet = (int)$subject['tiet_bat_dau'];
                            $end_tiet = $start_tiet + (int)$subject['so_tiet'] - 1;
                            
                            // Tách nhóm từ ma_lop_hp
                            $nhom = '01';
                            if (!empty($subject['ma_lop_hp'])) {
                                $parts = explode('-', $subject['ma_lop_hp']);
                                $last = end($parts);
                                $nhom = str_replace('L', '', $last);
                            }
                          ?>
                            <div class="tkb-subject-card" 
                                 style="background:<?= $col['bg'] ?>; border-left:4px solid <?= $col['border'] ?>; border-radius:8px; padding:10px; font-size:12px; line-height:1.6; text-align:center; box-shadow:0 1px 3px rgba(0,0,0,0.05); color:#1e293b; transition:transform 0.15s; font-family:inherit;"
                                 onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                              <div style="margin-bottom:3px;">-Môn: <?= e($subject['ten_hp']) ?> (<?= e($subject['ma_hp']) ?>)</div>
                              <div style="margin-bottom:3px;">-Nhóm: <?= $nhom ?></div>
                              <div style="margin-bottom:3px;">-Lớp: <?= e($sv['lop'] ?? 'KTPM47') ?></div>
                              <div style="margin-bottom:3px;">-Tiết: <?= $start_tiet ?>-><?= $end_tiet ?></div>
                              <?php if (!empty($subject['giang_vien'])): ?>
                                <div style="border-top: 1px dashed rgba(0,0,0,0.1); margin-top:4px; padding-top:4px; color:#475569;">-GV: <?= e($subject['giang_vien']) ?></div>
                              <?php endif; ?>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Danh sách môn học kỳ -->
        <h4 style="margin:20px 0 10px;color:var(--primary)"><i class="fas fa-list"></i> Danh sách học phần</h4>
        <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Học phần</th><th>Mã HP</th>
            <th style="text-align:center">TC</th>
            <th style="text-align:center">Lịch</th>
            <th>Phòng</th><th>Giảng viên</th>
          </tr></thead>
          <tbody>
          <?php
          $seen = [];
          foreach ($scheduleData['tkb'] as $row):
            if (in_array($row['hoc_phan_id'], $seen)) continue;
            $seen[] = $row['hoc_phan_id'];
            // Thu & tiết
            $days = [];
            foreach ($scheduleData['tkb'] as $r2) {
                if ($r2['hoc_phan_id'] == $row['hoc_phan_id'])
                    $days[] = tenThu($r2['thu']) . ' (T' . $r2['tiet_bat_dau'] . '-T' . ($r2['tiet_bat_dau']+$r2['so_tiet']-1) . ')';
            }
          ?>
          <tr>
            <td><?= e($row['ten_hp']) ?></td>
            <td><code><?= e($row['ma_hp']) ?></code></td>
            <td style="text-align:center"><?= (int)$row['so_tin_chi'] ?></td>
            <td><?= implode(', ', array_unique($days)) ?></td>
            <td><?= e($row['phong_hoc'] ?? '—') ?></td>
            <td><?= e($row['giang_vien'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
