<?php
/**
 * admin/hoc_phi/cap_nhat.php - Cập nhật mức học phí cho từng bản ghi học phí
 */
define('ROOT', __DIR__ . '/../../');
require_once ROOT . 'config/constants.php';
require_once ROOT . 'config/database.php';
require_once ROOT . 'includes/session.php';

requireAdmin();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $so_tien = max(0, (float)($_POST['so_tien'] ?? 0));
    $han_nop = trim($_POST['han_nop'] ?? '');

    $stmt = $db->prepare('SELECT da_nop FROM hoc_phi WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$record) {
        setFlash('danger', 'Không tìm thấy bản ghi học phí cần cập nhật.');
    } else {
        $da_nop = (float)$record['da_nop'];
        if ($da_nop >= $so_tien && $so_tien > 0) {
            $trang_thai = 'Đã nộp';
        } elseif ($da_nop > 0) {
            $trang_thai = 'Nợ';
        } else {
            $trang_thai = 'Chưa nộp';
        }

        $stmt = $db->prepare('UPDATE hoc_phi SET so_tien = ?, han_nop = ?, trang_thai = ? WHERE id = ?');
        $stmt->bind_param('issi', $so_tien, $han_nop, $trang_thai, $id);
        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật học phí thành công.');
        } else {
            setFlash('danger', 'Lỗi khi cập nhật học phí: ' . $stmt->error);
        }
        $stmt->close();
    }

    header('Location: ' . BASE_URL . '/admin/hoc_phi/cap_nhat.php');
    exit;
}

$editRecord = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare('SELECT hf.*, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop FROM hoc_phi hf JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id WHERE hf.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editRecord = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$editRecord) {
        setFlash('danger', 'Không tìm thấy học phí cần sửa.');
        header('Location: ' . BASE_URL . '/admin/hoc_phi/cap_nhat.php');
        exit;
    }
}

$fees = $db->query('SELECT hf.*, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop FROM hoc_phi hf JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id ORDER BY hf.nam_hoc DESC, hf.hoc_ky DESC, sv.khoa, sv.nganh, sv.lop')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Cập nhật học phí';
require_once ROOT . 'includes/admin/header_admin.php';
?>

<?php require_once ROOT . 'includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phí</span>
        <span>›</span><span>Cập nhật học phí</span>
      </div>
      <h1><i class="fas fa-edit"></i> Cập nhật mức học phí</h1>
      <p>Sửa lại số tiền và hạn nộp của từng bản ghi học phí.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if ($editRecord): ?>
      <div class="card fade-in" style="margin-bottom:24px;">
        <div class="card-header"><h3><i class="fas fa-pencil-alt"></i> Sửa học phí sinh viên</h3></div>
        <div class="card-body">
          <form method="POST" style="max-width:560px;">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)$editRecord['id'] ?>">
            <div class="form-group">
              <label>Sinh viên</label>
              <input type="text" class="form-control" value="<?= e($editRecord['ma_sv'] . ' - ' . $editRecord['ho_ten']) ?>" readonly>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Học kỳ</label>
                <input type="text" class="form-control" value="HK <?= (int)$editRecord['hoc_ky'] ?> / <?= e($editRecord['nam_hoc']) ?>" readonly>
              </div>
              <div class="form-group">
                <label>Trạng thái hiện tại</label>
                <input type="text" class="form-control" value="<?= e($editRecord['trang_thai']) ?>" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Số tiền học phí (VND)</label>
                <input type="number" step="1000" min="0" name="so_tien" class="form-control" value="<?= (float)$editRecord['so_tien'] ?>" required>
              </div>
              <div class="form-group">
                <label>Hạn nộp</label>
                <input type="date" name="han_nop" class="form-control" value="<?= e($editRecord['han_nop']) ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
            <a href="<?= BASE_URL ?>/admin/hoc_phi/cap_nhat.php" class="btn btn-secondary">Hủy</a>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-header"><h3><i class="fas fa-table"></i> Danh sách học phí</h3></div>
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
              <th style="text-align:right">Đã nộp</th>
              <th>Hạn nộp</th>
              <th>Trạng thái</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($fees)): ?>
              <tr><td colspan="11" style="text-align:center;padding:24px;color:var(--text-muted)">Chưa có dữ liệu học phí.</td></tr>
            <?php else: ?>
              <?php foreach ($fees as $fee): ?>
                <tr>
                  <td><?= e($fee['ma_sv']) ?></td>
                  <td><?= e($fee['ho_ten']) ?></td>
                  <td><?= e($fee['khoa']) ?></td>
                  <td><?= e($fee['nganh']) ?></td>
                  <td><?= e($fee['lop']) ?></td>
                  <td style="text-align:center">HK <?= (int)$fee['hoc_ky'] ?> / <?= e($fee['nam_hoc']) ?></td>
                  <td style="text-align:right"><?= formatMoney((float)$fee['so_tien']) ?></td>
                  <td style="text-align:right;color:var(--success)"><?= formatMoney((float)$fee['da_nop']) ?></td>
                  <td style="text-align:center"><?= e($fee['han_nop'] ?: '—') ?></td>
                  <td style="text-align:center"><span class="badge badge-<?= $fee['trang_thai'] === 'Đã nộp' ? 'success' : ($fee['trang_thai'] === 'Nợ' ? 'danger' : 'warning') ?>"><?= e($fee['trang_thai']) ?></span></td>
                  <td style="text-align:center"><a class="btn btn-sm btn-info" href="<?= BASE_URL ?>/admin/hoc_phi/cap_nhat.php?action=edit&id=<?= (int)$fee['id'] ?>">Sửa</a></td>
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
