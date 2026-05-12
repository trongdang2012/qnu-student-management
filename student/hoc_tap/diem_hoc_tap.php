<?php
/**
 * student/hoc_tap/diem_hoc_tap.php - UC6: Xem CPA và điểm các môn
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

// Bộ lọc năm học
$nh_filter = $db->real_escape_string($_GET['nh'] ?? '');

// Danh sách năm học có điểm
$list_nh = $db->query("SELECT DISTINCT nam_hoc FROM diem_hoc_tap WHERE sinh_vien_id=$sid ORDER BY nam_hoc DESC")->fetch_all(MYSQLI_ASSOC);

// Lấy điểm (có lọc hoặc tất cả)
$where_nh = $nh_filter ? "AND d.nam_hoc='$nh_filter'" : '';
$diem_list = $db->query("
    SELECT d.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.loai
    FROM diem_hoc_tap d
    JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
    WHERE d.sinh_vien_id = $sid $where_nh
    ORDER BY d.nam_hoc, d.hoc_ky, hp.ten_hp
")->fetch_all(MYSQLI_ASSOC);

// Nhóm theo năm học → kỳ
$by_nh_hk = [];
foreach ($diem_list as $d) {
    $by_nh_hk[$d['nam_hoc']][$d['hoc_ky']][] = $d;
}

// CPA toàn khóa (chỉ tính môn có điểm)
$r_cpa = $db->query("
    SELECT SUM(d.diem_he4 * hp.so_tin_chi) / SUM(hp.so_tin_chi) AS cpa,
           SUM(hp.so_tin_chi) AS tc_tich_luy,
           COUNT(*) AS so_mon
    FROM diem_hoc_tap d
    JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
    WHERE d.sinh_vien_id = $sid AND d.diem_he4 IS NOT NULL
");
$cpa_data = $r_cpa->fetch_assoc();
$cpa        = round((float)($cpa_data['cpa'] ?? 0), 2);
$tc_tich_luy= (int)($cpa_data['tc_tich_luy'] ?? 0);
$so_mon     = (int)($cpa_data['so_mon'] ?? 0);

// Số môn F (dưới 4.0 thang 10)
$r_f = $db->query("SELECT COUNT(*) AS cnt FROM diem_hoc_tap WHERE sinh_vien_id=$sid AND diem_he4 IS NOT NULL AND diem_he4 < 1.0");
$so_mon_F = (int)($r_f->fetch_assoc()['cnt'] ?? 0);

// Xếp loại học lực
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

$page_title  = 'Điểm học tập';
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
        <span>›</span><span>Điểm học tập</span>
      </div>
      <h1><i class="fas fa-graduation-cap"></i> Bảng điểm học tập</h1>
      <p>Tra cứu điểm chi tiết và CPA của bạn.</p>
    </div>

    <!-- Tổng quan CPA -->
    <div class="card mb-20 fade-in">
      <div class="card-body">
        <div class="diem-summary">
          <div class="diem-summary-item">
            <div class="ds-value" style="color:<?= colorCPA($cpa) ?>"><?= number_format($cpa, 2) ?></div>
            <div class="ds-label">CPA (Hệ 4)</div>
          </div>
          <div class="diem-summary-item green-top">
            <div class="ds-value"><?= $tc_tich_luy ?></div>
            <div class="ds-label">Tín chỉ tích lũy</div>
          </div>
          <div class="diem-summary-item">
            <div class="ds-value"><?= $so_mon ?></div>
            <div class="ds-label">Số học phần có điểm</div>
          </div>
          <div class="diem-summary-item red-top">
            <div class="ds-value"><?= $so_mon_F ?></div>
            <div class="ds-label">Học phần chưa đạt (F)</div>
          </div>
          <div class="diem-summary-item green-top">
            <div class="ds-value" style="font-size:18px"><?= xepLoaiHocLuc($cpa) ?></div>
            <div class="ds-label">Xếp loại học lực</div>
          </div>
        </div>

        <!-- Thanh CPA -->
        <div>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px">
            <span>CPA: <strong style="color:<?= colorCPA($cpa) ?>"><?= number_format($cpa,2) ?> / 4.0</strong></span>
            <span style="color:var(--text-muted)">Mục tiêu tốt nghiệp: ≥ 2.0</span>
          </div>
          <div class="progress" style="height:16px">
            <div class="progress-bar <?= $cpa >= 3.2 ? 'green' : ($cpa >= 2.0 ? '' : 'red') ?>"
                 style="width:0" data-width="<?= min(100, round($cpa/4*100)) ?>"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card mb-20 fade-in">
      <div class="card-body" style="padding:12px 20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <span style="font-size:14px;font-weight:500">Lọc theo năm học:</span>
        <a href="<?= BASE_URL ?>/student/hoc_tap/diem_hoc_tap.php"
           class="btn btn-sm <?= !$nh_filter ? 'btn-primary' : 'btn-outline' ?>">Tất cả</a>
        <?php foreach ($list_nh as $nh): ?>
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
    <?php foreach ($by_nh_hk as $nh => $hk_groups): ?>
      <?php foreach ($hk_groups as $hk => $mons):
        // CPA từng kỳ
        $sum_tc = 0; $sum_diem = 0;
        foreach ($mons as $m) {
            if (!is_null($m['diem_he4'])) { $sum_tc += $m['so_tin_chi']; $sum_diem += $m['diem_he4'] * $m['so_tin_chi']; }
        }
        $cpa_hk = $sum_tc > 0 ? round($sum_diem / $sum_tc, 2) : 0;
      ?>
      <div class="card mb-16 fade-in">
        <div class="card-header">
          <h3><i class="fas fa-book"></i>
            HK<?= (int)$hk ?> — <?= e($nh) ?>
          </h3>
          <span style="font-size:14px;color:var(--text-muted)">
            GPA kỳ: <strong style="color:<?= colorCPA($cpa_hk) ?>"><?= number_format($cpa_hk,2) ?></strong>
          </span>
        </div>
        <div class="table-wrap">
        <table id="diemTable">
          <thead><tr>
            <th>Mã HP</th>
            <th>Tên học phần</th>
            <th style="text-align:center">TC</th>
            <th style="text-align:center">CC (10%)</th>
            <th style="text-align:center">GK (30%)</th>
            <th style="text-align:center">CK (60%)</th>
            <th style="text-align:center">Điểm TK</th>
            <th style="text-align:center">Hệ 4</th>
            <th style="text-align:center">Xếp loại</th>
          </tr></thead>
          <tbody>
          <?php foreach ($mons as $m): ?>
          <tr>
            <td><code style="font-size:13px"><?= e($m['ma_hp']) ?></code></td>
            <td><?= e($m['ten_hp']) ?></td>
            <td style="text-align:center"><?= (int)$m['so_tin_chi'] ?></td>
            <td style="text-align:center"><?= is_null($m['diem_cc']) ? '—' : number_format((float)$m['diem_cc'],1) ?></td>
            <td style="text-align:center"><?= is_null($m['diem_gk']) ? '—' : number_format((float)$m['diem_gk'],1) ?></td>
            <td style="text-align:center"><?= is_null($m['diem_ck']) ? '—' : number_format((float)$m['diem_ck'],1) ?></td>
            <td style="text-align:center;font-weight:700;font-size:16px;color:<?= is_null($m['diem_tong']) ? 'var(--text-muted)' : (($m['diem_tong']>=5)?'var(--text)':'var(--danger)') ?>">
              <?= is_null($m['diem_tong']) ? '—' : number_format((float)$m['diem_tong'],1) ?>
            </td>
            <td style="text-align:center">
              <?= is_null($m['diem_he4']) ? '<span style="color:var(--text-muted)">—</span>' : number_format((float)$m['diem_he4'],1) ?>
            </td>
            <td style="text-align:center">
              <?php if (!is_null($m['diem_chu'])): ?>
                <span class="badge badge-<?= badgeDiemChu($m['diem_chu']) ?>" style="font-size:14px;padding:4px 14px">
                  <?= e($m['diem_chu']) ?>
                </span>
              <?php else: ?>
                <span style="color:var(--text-muted)">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:#f0f4ff;font-weight:700">
              <td colspan="2" style="border:1px solid var(--border);padding:10px 14px;text-align:right">
                GPA học kỳ:
              </td>
              <td style="border:1px solid var(--border);text-align:center"><?= $sum_tc ?></td>
              <td colspan="5" style="border:1px solid var(--border)"></td>
              <td style="border:1px solid var(--border);text-align:center;color:<?= colorCPA($cpa_hk) ?>">
                <?= number_format($cpa_hk,2) ?>
              </td>
            </tr>
          </tfoot>
        </table>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endforeach; ?>

    <?php if (empty($diem_list)): ?>
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

<?php require_once ROOT . '/includes/footer.php'; ?>
