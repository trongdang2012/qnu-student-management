<?php
/**
 * student/hoc_tap/chuong_trinh.php - UC4: Xem chương trình đào tạo
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db    = getDB();
$sid   = (int)$sv['id'];
$nganh = $db->real_escape_string($sv['nganh']);

// Lấy toàn bộ CTDT theo ngành
$rows = $db->query("
    SELECT c.hoc_ky, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai,
           d.diem_tong, d.diem_chu, d.diem_he4, dk.trang_thai AS dk_trang_thai
    FROM ctdt_chi_tiet c
    JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
    LEFT JOIN diem_hoc_tap d ON d.hoc_phan_id = hp.id AND d.sinh_vien_id = $sid
    LEFT JOIN dang_ky_hp dk ON dk.hoc_phan_id = hp.id AND dk.sinh_vien_id = $sid
    WHERE c.nganh = '$nganh'
    ORDER BY c.hoc_ky, hp.loai, hp.ma_hp
")->fetch_all(MYSQLI_ASSOC);

// Nhóm theo kỳ
$by_hk = [];
$tc_by_loai = ['Bắt buộc'=>0,'Đại cương'=>0,'Tự chọn'=>0];
$tc_total = 0;
foreach ($rows as $r) {
    $by_hk[$r['hoc_ky']][] = $r;
    $tc_by_loai[$r['loai']] = ($tc_by_loai[$r['loai']] ?? 0) + $r['so_tin_chi'];
    $tc_total += $r['so_tin_chi'];
}

$page_title  = 'Chương trình đào tạo';
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
        <span>›</span><span>Chương trình đào tạo</span>
      </div>
      <h1><i class="fas fa-list-alt"></i> Chương trình đào tạo</h1>
      <p>Ngành: <strong><?= e($sv['nganh']) ?></strong> — Khóa: <?= e($sv['nien_khoa']) ?></p>
    </div>

    <!-- Tổng tín chỉ theo nhóm -->
    <div class="stat-grid fade-in">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div><div class="stat-value"><?= $tc_total ?></div><div class="stat-label">Tổng tín chỉ toàn khóa</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-book"></i></div>
        <div><div class="stat-value"><?= $tc_by_loai['Bắt buộc'] ?? 0 ?></div><div class="stat-label">Môn bắt buộc</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue" style="background:#d1ecf1;color:#0c5460"><i class="fas fa-globe"></i></div>
        <div><div class="stat-value"><?= $tc_by_loai['Đại cương'] ?? 0 ?></div><div class="stat-label">Đại cương</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-puzzle-piece"></i></div>
        <div><div class="stat-value"><?= $tc_by_loai['Tự chọn'] ?? 0 ?></div><div class="stat-label">Môn tự chọn</div></div>
      </div>
    </div>

    <!-- Bảng CTDT theo từng kỳ -->
    <?php foreach ($by_hk as $hk => $mons): ?>
      <?php
        $tc_hk = array_sum(array_column($mons,'so_tin_chi'));
      ?>
      <div class="card mb-20 fade-in">
        <div class="card-header">
          <h3><i class="fas fa-bookmark"></i> Học kỳ <?= (int)$hk ?></h3>
          <span class="badge badge-primary" style="font-size:14px"><?= $tc_hk ?> tín chỉ</span>
        </div>
        <div class="table-wrap">
        <table>
          <thead><tr>
            <th>#</th>
            <th>Mã HP</th>
            <th>Tên học phần</th>
            <th style="text-align:center">TC</th>
            <th style="text-align:center">Loại</th>
            <th style="text-align:center">Điểm TK</th>
            <th style="text-align:center">Xếp loại</th>
            <th style="text-align:center">Trạng thái</th>
          </tr></thead>
          <tbody>
          <?php foreach ($mons as $i => $m):
            $dat   = !is_null($m['diem_he4']) && $m['diem_he4'] >= 1.0;
            $khong = !is_null($m['diem_he4']) && $m['diem_he4'] < 1.0;
            $dang  = is_null($m['diem_he4']) && !empty($m['dk_trang_thai']) && $m['dk_trang_thai']==='Đã duyệt';
          ?>
          <tr>
            <td style="text-align:center;color:var(--text-muted)"><?= $i+1 ?></td>
            <td><code style="font-size:13px"><?= e($m['ma_hp']) ?></code></td>
            <td><?= e($m['ten_hp']) ?></td>
            <td style="text-align:center;font-weight:700"><?= (int)$m['so_tin_chi'] ?></td>
            <td style="text-align:center">
              <span class="badge badge-<?= $m['loai']==='Bắt buộc' ? 'danger' : ($m['loai']==='Tự chọn' ? 'warning' : 'info') ?>">
                <?= e($m['loai']) ?>
              </span>
            </td>
            <td style="text-align:center;font-weight:700">
              <?= is_null($m['diem_tong']) ? '<span style="color:var(--text-muted)">—</span>' : number_format((float)$m['diem_tong'],1) ?>
            </td>
            <td style="text-align:center">
              <?php if (!is_null($m['diem_chu'])): ?>
                <span class="badge badge-<?= badgeDiemChu($m['diem_chu']) ?>"><?= e($m['diem_chu']) ?></span>
              <?php else: ?>
                <span style="color:var(--text-muted)">—</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center">
              <?php if ($dat): ?>
                <span class="badge badge-success"><i class="fas fa-check"></i> Đã đạt</span>
              <?php elseif ($khong): ?>
                <span class="badge badge-danger"><i class="fas fa-times"></i> Chưa đạt</span>
              <?php elseif ($dang): ?>
                <span class="badge badge-primary"><i class="fas fa-spinner fa-spin"></i> Đang học</span>
              <?php else: ?>
                <span class="badge badge-secondary">Chưa học</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:#f0f4ff">
              <td colspan="3" style="text-align:right;font-weight:700;border:1px solid var(--border);padding:10px 14px">
                Tổng học kỳ <?= (int)$hk ?>:
              </td>
              <td style="text-align:center;font-weight:700;border:1px solid var(--border);color:var(--primary)"><?= $tc_hk ?></td>
              <td colspan="4" style="border:1px solid var(--border)"></td>
            </tr>
          </tfoot>
        </table>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
