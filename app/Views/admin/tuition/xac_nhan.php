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

      <?php if ($flash = getFlash()): ?>
        <div class="alert alert-<?= e($flash['type']) ?> fade-in">
          <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
          <?= e($flash['msg']) ?>
        </div>
      <?php endif; ?>

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
        <?php if (!empty($pendingFees)): ?>
        <form method="POST" action="<?= BASE_URL ?>/admin/hoc-phi/xac-nhan/save">
          <input type="hidden" name="action" value="confirm">
          <div style="margin-bottom:12px;display:flex;gap:10px;align-items:center;justify-content:flex-end;">
            <button type="submit" class="btn btn-success" onclick="return confirm('Xác nhận các khoản học phí đã chọn là đã nộp?')">
              <i class="fas fa-check-double"></i> Xác nhận đã chọn
            </button>
          </div>
        <?php endif; ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <?php if (!empty($pendingFees)): ?>
                <th style="width:42px;text-align:center"><input type="checkbox" onclick="document.querySelectorAll('.tuition-check').forEach(cb => cb.checked = this.checked)"></th>
                <?php endif; ?>
                <th>MSV</th>
                <th>Họ tên</th>
                <th>Khoa</th>
                <th>Ngành</th>
                <th>Lớp</th>
                <th style="text-align:center">HK</th>
                <th style="text-align:right">Số tiền</th>
                <th>Trạng thái</th>
                <?php if (!empty($pendingFees)): ?>
                <th style="text-align:center">Hành động</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pendingFees)): ?>
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Không có bản ghi học phí cần xác nhận.</td></tr>
              <?php else: ?>
                <?php foreach ($pendingFees as $fee): ?>
                  <tr>
                    <td style="text-align:center"><input class="tuition-check" type="checkbox" name="selected[]" value="<?= (int)$fee['id'] ?>"></td>
                    <td><?= e($fee['ma_sv']) ?></td>
                    <td><?= e($fee['ho_ten']) ?></td>
                    <td><?= e($fee['khoa']) ?></td>
                    <td><?= e($fee['nganh']) ?></td>
                    <td><?= e($fee['lop']) ?></td>
                    <td style="text-align:center">HK <?= (int)$fee['hoc_ky'] ?> / <?= e($fee['nam_hoc']) ?></td>
                    <td style="text-align:right"><?= formatMoney((float)$fee['so_tien']) ?></td>
                    <td style="text-align:center"><span class="badge badge-<?= $fee['trang_thai'] === 'Nợ' ? 'danger' : 'warning' ?>"><?= e($fee['trang_thai']) ?></span></td>
                    <td style="text-align:center">
                      <button type="submit" name="single_id" value="<?= (int)$fee['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Xác nhận sinh viên này đã nộp học phí?')">
                        <i class="fas fa-check"></i> Xác nhận
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if (!empty($pendingFees)): ?>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
