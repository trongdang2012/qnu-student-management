<?php
/**
 * admin/hoc_phi/index.php - Quản lý học phí theo khoa / ngành / lớp
 */
define('ROOT', __DIR__ . '/../../');
require_once ROOT . 'config/constants.php';
require_once ROOT . 'config/database.php';
require_once ROOT . 'includes/session.php';

requireAdmin();
$db = getDB();

$selectedKhoa = trim($_GET['khoa'] ?? '');
$selectedNganh = trim($_GET['nganh'] ?? '');
$selectedLop = trim($_GET['lop'] ?? '');

$khoaList = $db->query("SELECT DISTINCT khoa FROM sinh_vien WHERE COALESCE(khoa,'') <> '' ORDER BY khoa ASC")->fetch_all(MYSQLI_ASSOC);

$nganhSql = "SELECT DISTINCT nganh FROM sinh_vien WHERE COALESCE(nganh,'') <> ''";
$types = '';
$params = [];
if ($selectedKhoa !== '') {
    $nganhSql .= " AND khoa = ?";
    $types .= 's';
    $params[] = $selectedKhoa;
}
$nganhSql .= " ORDER BY nganh ASC";
$nganhStmt = $db->prepare($nganhSql);
if ($params) {
    $nganhStmt->bind_param($types, ...$params);
}
$nganhStmt->execute();
$nganhList = $nganhStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$nganhStmt->close();

$lopSql = "SELECT DISTINCT lop FROM sinh_vien WHERE COALESCE(lop,'') <> ''";
$types = '';
$params = [];
if ($selectedKhoa !== '') {
    $lopSql .= " AND khoa = ?";
    $types .= 's';
    $params[] = $selectedKhoa;
}
if ($selectedNganh !== '') {
    $lopSql .= " AND nganh = ?";
    $types .= 's';
    $params[] = $selectedNganh;
}
$lopSql .= " ORDER BY lop ASC";
$lopStmt = $db->prepare($lopSql);
if ($params) {
    $lopStmt->bind_param($types, ...$params);
}
$lopStmt->execute();
$lopList = $lopStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$lopStmt->close();

$sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop,
           COALESCE(SUM(hp.so_tien), 0) AS total_fee,
           COALESCE(SUM(hp.da_nop), 0) AS total_paid,
           COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
        FROM sinh_vien sv
        LEFT JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
        WHERE 1 = 1";
$types = '';
$params = [];
if ($selectedKhoa !== '') {
    $sql .= ' AND sv.khoa = ?';
    $types .= 's';
    $params[] = $selectedKhoa;
}
if ($selectedNganh !== '') {
    $sql .= ' AND sv.nganh = ?';
    $types .= 's';
    $params[] = $selectedNganh;
}
if ($selectedLop !== '') {
    $sql .= ' AND sv.lop = ?';
    $types .= 's';
    $params[] = $selectedLop;
}
$sql .= ' GROUP BY sv.id ORDER BY sv.khoa, sv.nganh, sv.lop, sv.ho_ten';
$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$summary = ['total' => 0, 'paid' => 0, 'unpaid' => 0, 'owing' => 0, 'fee' => 0, 'paid_amount' => 0, 'owed_amount' => 0];
foreach ($students as $row) {
    $summary['total']++;
    $summary['fee'] += (float)$row['total_fee'];
    $summary['paid_amount'] += (float)$row['total_paid'];
    $summary['owed_amount'] += max(0, (float)$row['total_owed']);
    if ((float)$row['total_fee'] === 0.0) {
        $summary['unpaid']++;
    } elseif ((float)$row['total_paid'] >= (float)$row['total_fee']) {
        $summary['paid']++;
    } elseif ((float)$row['total_paid'] <= 0.0) {
        $summary['unpaid']++;
    } else {
        $summary['owing']++;
    }
}

function studentStatus(array $row): string {
    if ((float)$row['total_fee'] === 0.0) {
        return 'Chưa phát sinh';
    }
    if ((float)$row['total_paid'] >= (float)$row['total_fee']) {
        return 'Đã đóng';
    }
    if ((float)$row['total_paid'] <= 0.0) {
        return 'Chưa đóng';
    }
    return 'Nợ';
}

$page_title = 'Quản lý học phí';
require_once ROOT . 'includes/admin/header_admin.php';
?>

<?php require_once ROOT . 'includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phí</span>
      </div>
      <h1><i class="fas fa-money-bill-wave"></i> Quản lý học phí</h1>
      <p>Chọn khoa, ngành và lớp để xem danh sách sinh viên đã/ chưa đóng học phí.</p>
    </div>

    <div class="card fade-in" style="margin-bottom:24px;">
      <div class="card-body" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:flex-start;">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/hoc_phi/cap_nhat.php"><i class="fas fa-edit"></i> Cập nhật học phí</a>
        <a class="btn btn-success" href="<?= BASE_URL ?>/admin/hoc_phi/xac_nhan.php"><i class="fas fa-check-circle"></i> Xác nhận học phí</a>
        <a class="btn btn-info" href="<?= BASE_URL ?>/admin/hoc_phi/bao_cao.php"><i class="fas fa-chart-bar"></i> Báo cáo học phí</a>
        <span style="color:var(--text-muted);font-size:14px;">Các thao tác này sẽ cập nhật trực tiếp trạng thái học phí trên trang sinh viên.</span>
      </div>
    </div>

    <div class="card fade-in" style="margin-bottom:24px;">
      <div class="card-header">
        <h3><i class="fas fa-filter"></i> Bộ lọc</h3>
      </div>
      <div class="card-body">
        <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;align-items:end;">
          <div class="form-group">
            <label>Khoa</label>
            <select name="khoa" class="form-control" onchange="this.form.submit()">
              <option value="">-- Tất cả khoa --</option>
              <?php foreach ($khoaList as $item): ?>
                <option value="<?= e($item['khoa']) ?>" <?= $item['khoa'] === $selectedKhoa ? 'selected' : '' ?>><?= e($item['khoa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Ngành</label>
            <select name="nganh" class="form-control" onchange="this.form.submit()">
              <option value="">-- Tất cả ngành --</option>
              <?php foreach ($nganhList as $item): ?>
                <option value="<?= e($item['nganh']) ?>" <?= $item['nganh'] === $selectedNganh ? 'selected' : '' ?>><?= e($item['nganh']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Lớp</label>
            <select name="lop" class="form-control" onchange="this.form.submit()">
              <option value="">-- Tất cả lớp --</option>
              <?php foreach ($lopList as $item): ?>
                <option value="<?= e($item['lop']) ?>" <?= $item['lop'] === $selectedLop ? 'selected' : '' ?>><?= e($item['lop']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;">
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:auto;">Cập nhật bộ lọc</button>
          </div>
        </form>
      </div>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-user-graduate"></i>
        <div>
          <h3>Sinh viên</h3>
          <div class="stat-value"><?= $summary['total'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left:4px solid #28a745;">
        <i class="fas fa-check-circle" style="color:#28a745"></i>
        <div>
          <h3>Đã đóng</h3>
          <div class="stat-value"><?= $summary['paid'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left:4px solid #ffc107;">
        <i class="fas fa-exclamation-circle" style="color:#ffc107"></i>
        <div>
          <h3>Còn nợ</h3>
          <div class="stat-value"><?= $summary['owing'] ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left:4px solid #dc3545;">
        <i class="fas fa-times-circle" style="color:#dc3545"></i>
        <div>
          <h3>Chưa đóng</h3>
          <div class="stat-value"><?= $summary['unpaid'] ?></div>
        </div>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Danh sách sinh viên</h3>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>MSV</th>
              <th>Họ tên</th>
              <th>Khoa</th>
              <th>Ngành</th>
              <th>Lớp</th>
              <th style="text-align:right">Tổng phí</th>
              <th style="text-align:right">Đã nộp</th>
              <th style="text-align:right">Còn nợ</th>
              <th style="text-align:center">Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students)): ?>
              <tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text-muted)">Không có sinh viên phù hợp với bộ lọc.</td></tr>
            <?php else: ?>
              <?php foreach ($students as $student): 
                $status = studentStatus($student);
                $badgeColor = $status === 'Đã đóng' ? 'success' : ($status === 'Nợ' ? 'danger' : 'warning');
              ?>
                <tr>
                  <td><?= e($student['ma_sv']) ?></td>
                  <td><?= e($student['ho_ten']) ?></td>
                  <td><?= e($student['khoa']) ?></td>
                  <td><?= e($student['nganh']) ?></td>
                  <td><?= e($student['lop']) ?></td>
                  <td style="text-align:right;"><?= formatMoney((float)$student['total_fee']) ?></td>
                  <td style="text-align:right;color:var(--success);"><?= formatMoney((float)$student['total_paid']) ?></td>
                  <td style="text-align:right;color:var(--danger);font-weight:700;"><?= formatMoney(max(0, (float)$student['total_owed'])) ?></td>
                  <td style="text-align:center"><span class="badge badge-<?= $badgeColor ?>"><?= e($status) ?></span></td>
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
