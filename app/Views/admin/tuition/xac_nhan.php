<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/hoc-phi">Học phí</a>
        <span>›</span><span>Xác nhận học phí</span>
      </div>
      <h1><i class="fas fa-check-circle"></i> Xác nhận học phí</h1>
      <p>Nhấp vào nút "Xác nhận" trên từng dòng để đánh dấu bản ghi học phí là đã nộp và gửi thông báo xác nhận đến sinh viên.</p>
    </div>

      <div class="filter-bar fade-in" style="margin:16px 0;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <form method="GET" action="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
          <div style="display:flex;flex-direction:column;">
            <label for="ma_sv" style="font-size:14px;color:var(--text-muted);margin-bottom:6px">Mã sinh viên</label>
            <input id="ma_sv" name="ma_sv" type="text" class="form-control" value="<?= e($filterMSV ?? '') ?>" placeholder="Nhập mã SV để lọc" style="min-width:220px;padding:10px;border:1px solid #ccc;border-radius:6px;">
          </div>
          <button type="submit" class="btn btn-primary" style="padding:10px 18px;">Lọc</button>
          <?php if (!empty($filterMSV)): ?>
            <a href="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan" class="btn btn-secondary" style="padding:10px 18px;">Xóa bộ lọc</a>
          <?php endif; ?>
        </form>
      </div>
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Bản ghi chưa nộp / còn nợ</h3>
      </div>
      <div class="card-body">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>MSV</th>
                <th>Họ tên</th>
                <th>Khoa</th>
                <th>Ngành</th>
                <th>Lớp</th>
                <th style="text-align:center">HK</th>
                <th style="text-align:right">Số tiền</th>
                <th>Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pendingFees)): ?>
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Không có bản ghi học phí cần xác nhận.</td></tr>
              <?php else: ?>
                <?php foreach ($pendingFees as $fee): ?>
                  <tr>
                    <td><?= e($fee['ma_sv']) ?></td>
                    <td><?= e($fee['ho_ten']) ?></td>
                    <td><?= e($fee['khoa']) ?></td>
                    <td><?= e($fee['nganh']) ?></td>
                    <td><?= e($fee['lop']) ?></td>
                    <td style="text-align:center">HK <?= (int)$fee['hoc_ky'] ?> / <?= e($fee['nam_hoc']) ?></td>
                    <td style="text-align:right"><?= formatMoney((float)$fee['so_tien']) ?></td>
                    <td style="text-align:center"><span class="badge badge-<?= $fee['trang_thai'] === 'Nợ' ? 'danger' : 'warning' ?>"><?= e($fee['trang_thai']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
