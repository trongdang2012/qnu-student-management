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
      <p>Chọn các sinh viên đã nộp học phí và đánh dấu trạng thái là đã nộp.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in" style="margin-bottom:20px;">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Bản ghi chưa nộp / còn nợ</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan/save">
          <input type="hidden" name="action" value="confirm">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th style="width:44px"></th>
                  <th>MSV</th>
                  <th>Họ tên</th>
                  <th>Khoa</th>
                  <th>Ngành</th>
                  <th>Lớp</th>
                  <th style="text-align:center">HK</th>
                  <th style="text-align:right">Số tiền</th>
                  <th style="text-align:right">Đã nộp</th>
                  <th>Trạng thái</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pendingFees)): ?>
                  <tr><td colspan="11" style="text-align:center;padding:24px;color:var(--text-muted)">Không có bản ghi học phí cần xác nhận.</td></tr>
                <?php else: ?>
                  <?php foreach ($pendingFees as $fee): ?>
                    <tr>
                      <td style="text-align:center"><input type="checkbox" name="selected[]" value="<?= (int)$fee['id'] ?>"></td>
                      <td><?= e($fee['ma_sv']) ?></td>
                      <td><?= e($fee['ho_ten']) ?></td>
                      <td><?= e($fee['khoa']) ?></td>
                      <td><?= e($fee['nganh']) ?></td>
                      <td><?= e($fee['lop']) ?></td>
                      <td style="text-align:center">HK <?= (int)$fee['hoc_ky'] ?> / <?= e($fee['nam_hoc']) ?></td>
                      <td style="text-align:right"><?= formatMoney((float)$fee['so_tien']) ?></td>
                      <td style="text-align:right;color:var(--success)"><?= formatMoney((float)$fee['da_nop']) ?></td>
                      <td style="text-align:center"><span class="badge badge-<?= $fee['trang_thai'] === 'Nợ' ? 'danger' : 'warning' ?>"><?= e($fee['trang_thai']) ?></span></td>
                      <td style="text-align:center"><a class="btn btn-sm btn-success" href="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan?action=mark&id=<?= (int)$fee['id'] ?>">Đã nộp</a></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <?php if (!empty($pendingFees)): ?>
          <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Xác nhận chọn</button>
            <span class="text-muted">Chọn nhiều dòng rồi nhấn <strong>Xác nhận chọn</strong> để đánh dấu đã nộp.</span>
          </div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
