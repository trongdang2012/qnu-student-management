<?php
/**
 * student/hoc_tap/thoi_khoa_bieu.php - UC5: Xem TKB dạng Grid
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db  = getDB();
$sid = (int)$sv['id'];

// Bộ lọc học kỳ / năm học
$hk_filter = (int)($_GET['hk'] ?? HOC_KY_HIEN_TAI);
$nh_filter  = $db->real_escape_string($_GET['nh'] ?? NAM_HOC_HIEN_TAI);

// Danh sách năm học có TKB
$list_nh = $db->query("SELECT DISTINCT nam_hoc FROM thoi_khoa_bieu WHERE sinh_vien_id=$sid ORDER BY nam_hoc DESC")->fetch_all(MYSQLI_ASSOC);
$list_hk = [1,2,3,4,5,6,7,8];

// TKB
$tkb = $db->query("
    SELECT t.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi
    FROM thoi_khoa_bieu t
    JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
    WHERE t.sinh_vien_id = $sid
      AND t.hoc_ky = $hk_filter
      AND t.nam_hoc = '$nh_filter'
    ORDER BY t.thu, t.tiet_bat_dau
")->fetch_all(MYSQLI_ASSOC);

// Tổ chức: $grid[thu][tiet] = subject
$grid = [];
foreach ($tkb as $row) {
    for ($t = $row['tiet_bat_dau']; $t < $row['tiet_bat_dau'] + $row['so_tiet']; $t++) {
        $grid[$row['thu']][$t] = $row;
    }
}

$thu_list  = [2,3,4,5,6,7];
$tiet_list = range(1, 10);

// Giờ học từng tiết
$gio = [1=>'7:00',2=>'7:55',3=>'8:50',4=>'9:55',5=>'10:50',6=>'13:00',7=>'13:55',8=>'14:50',9=>'15:55',10=>'16:50'];

$page_title  = 'Thời khóa biểu';
$active_menu = 'hoc_tap';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
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
              <?php if (empty($list_nh)): ?>
                <option value="<?= NAM_HOC_HIEN_TAI ?>"><?= NAM_HOC_HIEN_TAI ?></option>
              <?php else: ?>
                <?php foreach ($list_nh as $nh): ?>
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

    <?php if (empty($tkb)): ?>
      <div class="card fade-in">
        <div class="card-body text-center" style="padding:40px">
          <div style="font-size:48px;margin-bottom:12px">📅</div>
          <h3 style="color:var(--text-muted)">Chưa có thời khóa biểu</h3>
          <p class="text-muted">Không tìm thấy TKB cho học kỳ <?= $hk_filter ?> — <?= e($nh_filter) ?>.</p>
        </div>
      </div>
    <?php else: ?>

    <!-- Lưới TKB -->
    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-th"></i> Lịch học — HK<?= $hk_filter ?> / <?= e($nh_filter) ?></h3>
        <span class="badge badge-primary"><?= count($tkb) ?> tiết / tuần</span>
      </div>
      <div class="card-body" style="padding:16px">
        <div class="table-wrap">
          <div class="tkb-grid">
            <!-- Header row -->
            <div class="tkb-header">Tiết</div>
            <?php foreach ($thu_list as $thu): ?>
              <div class="tkb-header"><?= tenThu($thu) ?></div>
            <?php endforeach; ?>

            <!-- Rows -->
            <?php
            $rendered = [];  // theo dõi ô đã render (bỏ qua ô span)
            foreach ($tiet_list as $tiet):
            ?>
              <div class="tkb-tiet">
                <div><?= $tiet ?></div>
                <div style="font-size:10px"><?= $gio[$tiet] ?? '' ?></div>
              </div>
              <?php foreach ($thu_list as $thu):
                $cell_key = $thu . '_' . $tiet;
                if (in_array($cell_key, $rendered)) continue;
                $subject = $grid[$thu][$tiet] ?? null;
              ?>
                <div class="tkb-cell">
                  <?php if ($subject):
                    // Đánh dấu tất cả tiết span
                    for ($t2=$subject['tiet_bat_dau']; $t2<$subject['tiet_bat_dau']+$subject['so_tiet']; $t2++) {
                        if ($t2 !== $tiet) $rendered[] = $thu.'_'.$t2;
                    }
                  ?>
                    <div class="tkb-subject"
                         data-tooltip="<?= e($subject['ten_hp']) ?> | <?= e($subject['giang_vien']??'') ?> | Phòng <?= e($subject['phong_hoc']??'') ?>">
                      <div class="sub-name"><?= e($subject['ten_hp']) ?></div>
                      <?php if (!empty($subject['phong_hoc'])): ?>
                        <div class="sub-room"><i class="fas fa-door-open" style="font-size:10px"></i> <?= e($subject['phong_hoc']) ?></div>
                      <?php endif; ?>
                      <?php if (!empty($subject['giang_vien'])): ?>
                        <div class="sub-gv"><i class="fas fa-chalkboard-teacher" style="font-size:10px"></i> <?= e($subject['giang_vien']) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
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
          foreach ($tkb as $row):
            if (in_array($row['hoc_phan_id'], $seen)) continue;
            $seen[] = $row['hoc_phan_id'];
            // Thu & tiết
            $days = [];
            foreach ($tkb as $r2) {
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
