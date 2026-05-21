<?php
/**
 * admin/hoc_phan/index.php - Quan ly hoc phan cho admin
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

function hpRedirect(array $params = []): void {
    $url = BASE_URL . '/admin/hoc_phan/index.php';
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

function hpOption(string $value, ?string $current): string {
    return $value === $current ? 'selected' : '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? 'list';

    if ($postAction === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $ma_hp = strtoupper(trim($_POST['ma_hp'] ?? ''));
        $ten_hp = trim($_POST['ten_hp'] ?? '');
        $so_tin_chi = max(1, min(10, (int)($_POST['so_tin_chi'] ?? 3)));
        $loai = trim($_POST['loai'] ?? 'Bắt buộc');
        $hoc_ky = max(1, min(8, (int)($_POST['hoc_ky'] ?? 1)));
        $nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);

        $validLoai = ['Bắt buộc', 'Tự chọn', 'Đại cương'];
        if (!in_array($loai, $validLoai, true)) {
            $loai = 'Bắt buộc';
        }

        if ($ma_hp === '' || $ten_hp === '') {
            setFlash('danger', 'Mã học phần và tên học phần không được để trống.');
        } else {
            $stmt = $db->prepare('SELECT id FROM hoc_phan WHERE ma_hp = ? AND id <> ? LIMIT 1');
            $stmt->bind_param('si', $ma_hp, $id);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();

            if ($exists) {
                setFlash('danger', 'Mã học phần đã tồn tại. Vui lòng dùng mã khác.');
            } elseif ($id > 0) {
                $stmt = $db->prepare('
                    UPDATE hoc_phan
                    SET ma_hp = ?, ten_hp = ?, so_tin_chi = ?, loai = ?, hoc_ky = ?, nien_khoa = ?
                    WHERE id = ?
                ');
                $stmt->bind_param('ssisisi', $ma_hp, $ten_hp, $so_tin_chi, $loai, $hoc_ky, $nien_khoa, $id);
                setFlash($stmt->execute() ? 'success' : 'danger', $stmt->error ?: 'Cập nhật học phần thành công.');
            } else {
                $stmt = $db->prepare('
                    INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa)
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt->bind_param('ssisis', $ma_hp, $ten_hp, $so_tin_chi, $loai, $hoc_ky, $nien_khoa);
                setFlash($stmt->execute() ? 'success' : 'danger', $stmt->error ?: 'Thêm học phần thành công.');
            }
        }

        hpRedirect(['search' => $_POST['search_keep'] ?? '']);
    }

    if ($postAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM hoc_phan WHERE id = ?');
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                setFlash('success', 'Xóa học phần thành công.');
            } else {
                setFlash('danger', 'Không thể xóa học phần đang được dùng trong dữ liệu khác.');
            }
        }

        hpRedirect(['search' => $_POST['search_keep'] ?? '']);
    }
}

$search = trim($_GET['search'] ?? '');
$hocKyFilter = (int)($_GET['hoc_ky'] ?? 0);
$loaiFilter = trim($_GET['loai'] ?? '');

$sql = 'SELECT * FROM hoc_phan WHERE 1 = 1';
$types = '';
$params = [];

if ($search !== '') {
    $like = '%' . $search . '%';
    $sql .= ' AND (ma_hp LIKE ? OR ten_hp LIKE ? OR nien_khoa LIKE ?)';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($hocKyFilter >= 1 && $hocKyFilter <= 8) {
    $sql .= ' AND hoc_ky = ?';
    $types .= 'i';
    $params[] = $hocKyFilter;
}

if (in_array($loaiFilter, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
    $sql .= ' AND loai = ?';
    $types .= 's';
    $params[] = $loaiFilter;
}

$sql .= ' ORDER BY hoc_ky ASC, ma_hp ASC';
$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$item = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare('SELECT * FROM hoc_phan WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    if (!$item) {
        setFlash('danger', 'Không tìm thấy học phần cần sửa.');
        hpRedirect();
    }
}

$totalCredits = array_sum(array_map(static fn($row) => (int)$row['so_tin_chi'], $list));
$page_title = 'Quản lý học phần';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Học phần</span>
      </div>
      <h1><i class="fas fa-book"></i> Quản lý học phần</h1>
      <p>Thêm, sửa, xóa và tìm kiếm học phần theo dữ liệu đào tạo Đại học Quy Nhơn.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-layer-group"></i>
        <div>
          <h3>Học phần đang hiển thị</h3>
          <div class="stat-value"><?= count($list) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#28a745">
        <i class="fas fa-award" style="color:#28a745"></i>
        <div>
          <h3>Tổng tín chỉ</h3>
          <div class="stat-value"><?= $totalCredits ?></div>
        </div>
      </div>
    </div>

    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa học phần' : 'Thêm học phần' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <div class="form-row full">
            <div class="form-group">
              <label>Mã học phần <span style="color:red">*</span></label>
              <input type="text" name="ma_hp" class="form-control" value="<?= e($item['ma_hp'] ?? '') ?>" required maxlength="20" placeholder="VD: CNTT010">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Tên học phần <span style="color:red">*</span></label>
              <input type="text" name="ten_hp" class="form-control" value="<?= e($item['ten_hp'] ?? '') ?>" required maxlength="150" placeholder="VD: Lập trình Web">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tín chỉ</label>
              <input type="number" name="so_tin_chi" class="form-control" value="<?= (int)($item['so_tin_chi'] ?? 3) ?>" min="1" max="10">
            </div>
            <div class="form-group">
              <label>Loại học phần</label>
              <select name="loai" class="form-control">
                <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $loai): ?>
                  <option value="<?= e($loai) ?>" <?= hpOption($loai, $item['loai'] ?? 'Bắt buộc') ?>><?= e($loai) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Học kỳ</label>
              <input type="number" name="hoc_ky" class="form-control" value="<?= (int)($item['hoc_ky'] ?? HOC_KY_HIEN_TAI) ?>" min="1" max="8">
            </div>
            <div class="form-group">
              <label>Niên khóa</label>
              <input type="text" name="nien_khoa" class="form-control" value="<?= e($item['nien_khoa'] ?? NAM_HOC_HIEN_TAI) ?>" maxlength="20" placeholder="2021-2025">
            </div>
          </div>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0">
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Mã, tên học phần hoặc niên khóa..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <option value="0">Tất cả</option>
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKyFilter === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Loại</label>
            <select name="loai" class="form-control">
              <option value="">Tất cả</option>
              <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $loai): ?>
                <option value="<?= e($loai) ?>" <?= $loaiFilter === $loai ? 'selected' : '' ?>><?= e($loai) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
          <a href="<?= BASE_URL ?>/admin/hoc_phan/index.php" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm mới</button>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-inbox" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy học phần phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã HP</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">TC</th>
                  <th>Loại</th>
                  <th style="text-align:center">HK</th>
                  <th>Niên khóa</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><code><?= e($row['ma_hp']) ?></code></td>
                    <td><?= e($row['ten_hp']) ?></td>
                    <td style="text-align:center"><?= (int)$row['so_tin_chi'] ?></td>
                    <td><span class="badge"><?= e($row['loai']) ?></span></td>
                    <td style="text-align:center"><?= (int)$row['hoc_ky'] ?></td>
                    <td><?= e($row['nien_khoa'] ?? '') ?></td>
                    <td style="text-align:center">
                      <div class="table-actions">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&search=<?= urlencode($search) ?>">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Xóa học phần này?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <input type="hidden" name="search_keep" value="<?= e($search) ?>">
                          <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

<script>
function closeModal() {
  document.getElementById('formModal').classList.remove('active');
  if (new URLSearchParams(location.search).has('action')) {
    history.replaceState(null, '', 'index.php');
  }
}
function showAddForm() {
  document.getElementById('formModal').classList.add('active');
}
</script>
</body>
</html>
