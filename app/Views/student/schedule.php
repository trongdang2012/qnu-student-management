<?php
$list_hk = [1,2,3,4,5,6,7,8];
$thu_list  = [2,3,4,5,6,7,8];
$tiet_list = range(1, 10);
$gio = [1=>'7:00',2=>'7:55',3=>'8:50',4=>'9:55',5=>'10:50',6=>'13:00',7=>'13:55',8=>'14:50',9=>'15:55',10=>'16:50'];
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
          <table class="tkb-table">
            <thead>
              <tr>
                <th style="width:80px;text-align:center">Tiết</th>
                <?php foreach ($thu_list as $thu): ?>
                  <th style="text-align:center"><?= e(tenThu($thu)) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $rendered = [];
              foreach ($tiet_list as $tiet):
              ?>
                <tr>
                  <td class="tkb-time">
                    <strong><?= $tiet ?></strong>
                    <span><?= e($gio[$tiet] ?? '') ?></span>
                  </td>
                  <?php foreach ($thu_list as $thu):
                    $cell_key = $thu . '_' . $tiet;
                    if (isset($rendered[$cell_key])) continue;
                    $subject = $scheduleData['grid'][$thu][$tiet] ?? null;
                  ?>
                    <?php if ($subject && (int)$subject['tiet_bat_dau'] === $tiet): ?>
                      <?php
                        $rowspan = min((int)$subject['so_tiet'], count($tiet_list) - $tiet + 1);
                        for ($t2 = $tiet + 1; $t2 < $tiet + $rowspan; $t2++) {
                            $rendered[$thu . '_' . $t2] = true;
                        }
                      ?>
                      <td class="tkb-cell-filled" rowspan="<?= $rowspan ?>">
                        <div class="tkb-subject"
                             title="<?= e($subject['ten_hp']) ?> | <?= e($subject['giang_vien'] ?? '') ?> | Phòng <?= e($subject['phong_hoc'] ?? '') ?>">
                          <div class="sub-name"><?= e($subject['ten_hp']) ?></div>
                          <div class="sub-code"><?= e($subject['ma_hp']) ?></div>
                          <?php if (!empty($subject['phong_hoc'])): ?>
                            <div class="sub-room"><i class="fas fa-door-open" style="font-size:10px"></i> <?= e($subject['phong_hoc']) ?></div>
                          <?php endif; ?>
                          <?php if (!empty($subject['giang_vien'])): ?>
                            <div class="sub-gv"><i class="fas fa-chalkboard-teacher" style="font-size:10px"></i> <?= e($subject['giang_vien']) ?></div>
                          <?php endif; ?>
                        </div>
                      </td>
                    <?php else: ?>
                      <td class="tkb-empty"></td>
                    <?php endif; ?>
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
