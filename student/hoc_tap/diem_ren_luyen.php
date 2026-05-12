<?php
/**
 * student/hoc_tap/diem_ren_luyen.php - UC7: Xem điểm rèn luyện
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

$drl_list = $db->query("
    SELECT * FROM diem_ren_luyen
    WHERE sinh_vien_id = $sid
    ORDER BY nam_hoc, hoc_ky
")->fetch_all(MYSQLI_ASSOC);

// Điểm trung bình rèn luyện
$avg_drl = count($drl_list) > 0
    ? round(array_sum(array_column($drl_list,'diem')) / count($drl_list), 1)
    : 0;

// Thang điểm rèn luyện
function rlColor(int $d): string {
    if ($d >= 90) return 'var(--success)';
    if ($d >= 80) return '#17a2b8';
    if ($d >= 65) return 'var(--primary)';
    if ($d >= 50) return '#fd7e14';
    return 'var(--danger)';
}
function rlBadge(string $xep): string {
    $map = ['Xuất sắc'=>'success','Tốt'=>'info','Khá'=>'primary','Trung bình'=>'warning','Yếu'=>'danger','Kém'=>'danger'];
    return $map[$xep] ?? 'secondary';
}

$page_title  = 'Điểm rèn luyện';
$active_menu = 'hoc_tap';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container" style="max-width:900px">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
        <span>›</span><span>Học tập</span>
        <span>›</span><span>Điểm rèn luyện</span>
      </div>
      <h1><i class="fas fa-star"></i> Điểm rèn luyện</h1>
      <p>Theo dõi kết quả rèn luyện hàng học kỳ của bạn.</p>
    </div>

    <!-- Thang điểm -->
    <div class="card mb-20 fade-in">
      <div class="card-header"><h3><i class="fas fa-info-circle"></i> Thang đánh giá điểm rèn luyện</h3></div>
      <div class="card-body">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php
          $thang = [
            ['90-100','Xuất sắc','success'],
            ['80-89', 'Tốt',     'info'],
            ['65-79', 'Khá',     'primary'],
            ['50-64', 'Trung bình','warning'],
            ['35-49', 'Yếu',    'danger'],
            ['0-34',  'Kém',    'danger'],
          ];
          foreach ($thang as [$range, $label, $cls]): ?>
            <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:#f9f9f9;border-radius:6px;font-size:14px">
              <span class="badge badge-<?= $cls ?>"><?= $range ?></span>
              <span><?= $label ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <?php if (empty($drl_list)): ?>
      <div class="card fade-in">
        <div class="card-body text-center" style="padding:40px">
          <div style="font-size:48px;margin-bottom:12px">⭐</div>
          <h3 style="color:var(--text-muted)">Chưa có dữ liệu điểm rèn luyện</h3>
        </div>
      </div>
    <?php else: ?>

    <!-- Tổng quan -->
    <div class="stat-grid fade-in">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-star"></i></div>
        <div>
          <div class="stat-value" style="color:<?= rlColor((int)$avg_drl) ?>"><?= $avg_drl ?></div>
          <div class="stat-label">Điểm TB toàn khóa</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-trophy"></i></div>
        <div>
          <div class="stat-value"><?= max(array_column($drl_list,'diem')) ?></div>
          <div class="stat-label">Điểm cao nhất</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-arrow-down"></i></div>
        <div>
          <div class="stat-value"><?= min(array_column($drl_list,'diem')) ?></div>
          <div class="stat-label">Điểm thấp nhất</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
        <div>
          <div class="stat-value"><?= count($drl_list) ?></div>
          <div class="stat-label">Số kỳ có điểm</div>
        </div>
      </div>
    </div>

    <!-- Biểu đồ thanh (CSS-based) -->
    <div class="card mb-20 fade-in">
      <div class="card-header"><h3><i class="fas fa-chart-bar"></i> Biểu đồ điểm rèn luyện</h3></div>
      <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:16px;height:160px;padding:0 10px">
          <?php foreach ($drl_list as $drl): ?>
            <?php $h = round($drl['diem'] / 100 * 140); ?>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px">
              <div style="font-size:13px;font-weight:700;color:<?= rlColor((int)$drl['diem']) ?>"><?= (int)$drl['diem'] ?></div>
              <div style="width:100%;height:<?= $h ?>px;background:<?= rlColor((int)$drl['diem']) ?>;border-radius:6px 6px 0 0;transition:all .8s ease;min-height:4px" title="<?= (int)$drl['diem'] ?> điểm"></div>
              <div style="font-size:11px;color:var(--text-muted);text-align:center">HK<?= $drl['hoc_ky'] ?><br><?= e($drl['nam_hoc']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Đường cơ sở -->
        <div style="border-top:2px solid var(--border);margin-top:0;padding-top:4px;display:flex;justify-content:flex-end">
          <span style="font-size:12px;color:var(--text-muted)">Điểm / 100</span>
        </div>
      </div>
    </div>

    <!-- Bảng chi tiết -->
    <div class="card fade-in">
      <div class="card-header"><h3><i class="fas fa-table"></i> Chi tiết từng học kỳ</h3></div>
      <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Năm học</th>
          <th style="text-align:center">Học kỳ</th>
          <th style="text-align:center">Điểm</th>
          <th style="text-align:center">Xếp loại</th>
          <th style="text-align:center">Thanh điểm</th>
          <th>Ghi chú</th>
        </tr></thead>
        <tbody>
        <?php foreach ($drl_list as $drl): ?>
          <tr>
            <td><?= e($drl['nam_hoc']) ?></td>
            <td style="text-align:center">Học kỳ <?= (int)$drl['hoc_ky'] ?></td>
            <td style="text-align:center;font-size:20px;font-weight:700;color:<?= rlColor((int)$drl['diem']) ?>">
              <?= (int)$drl['diem'] ?>
            </td>
            <td style="text-align:center">
              <span class="badge badge-<?= rlBadge($drl['xep_loai'] ?? '') ?>" style="font-size:13px;padding:5px 14px">
                <?= e($drl['xep_loai'] ?? '—') ?>
              </span>
            </td>
            <td style="min-width:150px">
              <div class="progress" style="height:10px">
                <div class="progress-bar <?= (int)$drl['diem'] >= 80 ? 'green' : ((int)$drl['diem'] >= 50 ? '' : 'red') ?>"
                     style="width:<?= (int)$drl['diem'] ?>%"></div>
              </div>
            </td>
            <td style="font-size:13px;color:var(--text-muted)"><?= e($drl['ghi_chu'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background:#f0f4ff;font-weight:700">
            <td colspan="2" style="border:1px solid var(--border);padding:10px 14px;text-align:right">Điểm trung bình:</td>
            <td style="border:1px solid var(--border);text-align:center;font-size:20px;color:<?= rlColor((int)$avg_drl) ?>"><?= $avg_drl ?></td>
            <td colspan="3" style="border:1px solid var(--border)"></td>
          </tr>
        </tfoot>
      </table>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
