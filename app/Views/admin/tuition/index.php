<?php 
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
require_once ROOT . '/includes/admin/header_admin.php'; 
?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phí</span>
      </div>
      <h1><i class="fas fa-money-bill-wave"></i> Quản lý học phí</h1>
      <p>Chọn khoa, ngành và lớp để xem danh sách sinh viên đã/ chưa đóng học phí.</p>
    </div>

    <div class="card fade-in" style="margin-bottom:24px;">
      <div class="card-body" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:flex-start;">
        <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/hoc-phi/cap-nhat"><i class="fas fa-edit"></i> Cập nhật học phí</a>
        <a class="btn btn-success" href="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan"><i class="fas fa-check-circle"></i> Xác nhận học phí</a>
        <a class="btn btn-info" href="<?= BASE_URL ?>/admin/hoc-phi/bao-cao"><i class="fas fa-chart-bar"></i> Báo cáo học phí</a>
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
      <div class="stat-card">
        <i class="fas fa-check-circle"></i>
        <div>
          <h3>Đã đóng</h3>
          <div class="stat-value"><?= $summary['paid'] ?></div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-exclamation-circle"></i>
        <div>
          <h3>Còn nợ</h3>
          <div class="stat-value"><?= $summary['owing'] ?></div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-times-circle"></i>
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

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
