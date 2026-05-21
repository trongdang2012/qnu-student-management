<?php
/**
 * admin/hoc_phi/bao_cao.php - Báo cáo tình hình học phí sinh viên
 */
define('ROOT', __DIR__ . '/../../');
require_once ROOT . 'config/constants.php';
require_once ROOT . 'config/database.php';
require_once ROOT . 'includes/session.php';

requireAdmin();
$db = getDB();

$totals = $db->query("SELECT COUNT(DISTINCT sv.id) AS students,
  COALESCE(SUM(hp.so_tien), 0) AS total_fee,
  COALESCE(SUM(hp.da_nop), 0) AS total_paid,
  COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
  FROM sinh_vien sv
  JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id")->fetch_assoc();

$statusCounts = $db->query("SELECT
  SUM(CASE WHEN hp.da_nop >= hp.so_tien AND hp.so_tien > 0 THEN 1 ELSE 0 END) AS paid_count,
  SUM(CASE WHEN hp.da_nop = 0 AND hp.so_tien > 0 THEN 1 ELSE 0 END) AS unpaid_count,
  SUM(CASE WHEN hp.da_nop > 0 AND hp.da_nop < hp.so_tien THEN 1 ELSE 0 END) AS owing_count
  FROM hoc_phi hp")->fetch_assoc();

$byKhoa = $db->query("SELECT sv.khoa,
  COUNT(DISTINCT sv.id) AS students,
  COALESCE(SUM(hp.so_tien), 0) AS total_fee,
  COALESCE(SUM(hp.da_nop), 0) AS total_paid,
  COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
  FROM sinh_vien sv
  JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
  GROUP BY sv.khoa
  ORDER BY total_owed DESC, sv.khoa ASC")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Báo cáo học phí';
require_once ROOT . 'includes/admin/header_admin.php';
?>

<?php require_once ROOT . 'includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phí</span>
        <span>›</span><span>Báo cáo học phí</span>
      </div>
      <h1><i class="fas fa-chart-bar"></i> Báo cáo học phí</h1>
      <p>Tổng quan và báo cáo chi tiết theo khoa về tình hình nộp học phí.</p>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-users"></i>
        <div>
          <h3>Sinh viên có học phí</h3>
          <div class="stat-value"><?= (int)$totals['students'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#007bff">
        <i class="fas fa-wallet" style="color:#007bff"></i>
        <div>
          <h3>Tổng học phí</h3>
          <div class="stat-value"><?= formatMoney((float)$totals['total_fee']) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745">
        <i class="fas fa-hand-holding-usd" style="color:#28a745"></i>
        <div>
          <h3>Đã nộp</h3>
          <div class="stat-value"><?= formatMoney((float)$totals['total_paid']) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#dc3545">
        <i class="fas fa-exclamation-triangle" style="color:#dc3545"></i>
        <div>
          <h3>Còn nợ</h3>
          <div class="stat-value"><?= formatMoney((float)$totals['total_owed']) ?></div>
        </div>
      </div>
    </div>

    <div class="admin-grid fade-in" style="margin-top:20px;">
      <div class="stat-card">
        <i class="fas fa-check-circle"></i>
        <div>
          <h3>Đã đóng</h3>
          <div class="stat-value"><?= (int)$statusCounts['paid_count'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-clock" style="color:#ffc107"></i>
        <div>
          <h3>Chưa đóng</h3>
          <div class="stat-value"><?= (int)$statusCounts['unpaid_count'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#dc3545">
        <i class="fas fa-heart-broken" style="color:#dc3545"></i>
        <div>
          <h3>Đang nợ</h3>
          <div class="stat-value"><?= (int)$statusCounts['owing_count'] ?></div>
        </div>
      </div>
    </div>

    <div class="card fade-in" style="margin-top:24px;">
      <div class="card-header"><h3><i class="fas fa-building"></i> Báo cáo theo khoa</h3></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Khoa</th>
              <th style="text-align:center">Số SV</th>
              <th style="text-align:right">Học phí</th>
              <th style="text-align:right">Đã nộp</th>
              <th style="text-align:right">Còn nợ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($byKhoa)): ?>
              <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted)">Chưa có dữ liệu báo cáo theo khoa.</td></tr>
            <?php else: ?>
              <?php foreach ($byKhoa as $row): ?>
                <tr>
                  <td><?= e($row['khoa']) ?></td>
                  <td style="text-align:center"><?= (int)$row['students'] ?></td>
                  <td style="text-align:right"><?= formatMoney((float)$row['total_fee']) ?></td>
                  <td style="text-align:right"><?= formatMoney((float)$row['total_paid']) ?></td>
                  <td style="text-align:right"><?= formatMoney((float)$row['total_owed']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . 'includes/admin/footer_admin.php'; ?>
