<?php
/**
 * admin/thoi_khoa_bieu/index.php - Quan ly thoi khoa bieu
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

function tkbRedirect(array $params = []): void {
    $url = BASE_URL . '/admin/thoi_khoa_bieu/index.php';
    if ($params) {
        $url .= '?' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

function tkbOverlap(mysqli $db, int $excludeId, int $sinhVienId, string $phongHoc, int $thu, int $tietBatDau, int $soTiet, int $hocKy, string $namHoc): ?string {
    $tietKetThuc = $tietBatDau + $soTiet - 1;

    $stmt = $db->prepare('
        SELECT hp.ma_hp, hp.ten_hp, t.tiet_bat_dau, t.so_tiet
        FROM thoi_khoa_bieu t
        JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
        WHERE t.id <> ?
          AND t.sinh_vien_id = ?
          AND t.hoc_ky = ?
          AND t.nam_hoc = ?
          AND t.thu = ?
          AND t.tiet_bat_dau <= ?
          AND (t.tiet_bat_dau + t.so_tiet - 1) >= ?
        LIMIT 1
    ');
    $stmt->bind_param('iiisiii', $excludeId, $sinhVienId, $hocKy, $namHoc, $thu, $tietKetThuc, $tietBatDau);
    $stmt->execute();
    $studentConflict = $stmt->get_result()->fetch_assoc();
    if ($studentConflict) {
        return 'Sinh viên đã có lịch ' . $studentConflict['ma_hp'] . ' trùng khoảng tiết này.';
    }

    if ($phongHoc !== '') {
        $stmt = $db->prepare('
            SELECT sv.ma_sv, sv.ho_ten, t.tiet_bat_dau, t.so_tiet
            FROM thoi_khoa_bieu t
            JOIN sinh_vien sv ON sv.id = t.sinh_vien_id
            WHERE t.id <> ?
              AND t.phong_hoc = ?
              AND t.hoc_ky = ?
              AND t.nam_hoc = ?
              AND t.thu = ?
              AND t.tiet_bat_dau <= ?
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= ?
            LIMIT 1
        ');
        $stmt->bind_param('isisiii', $excludeId, $phongHoc, $hocKy, $namHoc, $thu, $tietKetThuc, $tietBatDau);
        $stmt->execute();
        $roomConflict = $stmt->get_result()->fetch_assoc();
        if ($roomConflict) {
            return 'Phòng ' . $phongHoc . ' đã có lịch trong khoảng tiết này.';
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? 'list';
    $keepParams = [
        'hoc_ky' => (int)($_POST['hoc_ky_keep'] ?? HOC_KY_HIEN_TAI),
        'nam_hoc' => $_POST['nam_hoc_keep'] ?? NAM_HOC_HIEN_TAI,
        'search' => $_POST['search_keep'] ?? '',
    ];

    if ($postAction === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $sinhVienId = (int)($_POST['sinh_vien_id'] ?? 0);
        $hocPhanId = (int)($_POST['hoc_phan_id'] ?? 0);
        $thu = max(2, min(8, (int)($_POST['thu'] ?? 2)));
        $tietBatDau = max(1, min(10, (int)($_POST['tiet_bat_dau'] ?? 1)));
        $soTiet = max(1, min(5, (int)($_POST['so_tiet'] ?? 3)));
        $phongHoc = trim($_POST['phong_hoc'] ?? '');
        $giangVien = trim($_POST['giang_vien'] ?? '');
        $hocKy = max(1, min(8, (int)($_POST['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        $keepParams['hoc_ky'] = $hocKy;
        $keepParams['nam_hoc'] = $namHoc;

        if ($sinhVienId <= 0 || $hocPhanId <= 0) {
            setFlash('danger', 'Vui lòng chọn sinh viên và học phần.');
        } elseif ($tietBatDau + $soTiet - 1 > 10) {
            setFlash('danger', 'Khoảng tiết không hợp lệ. Lịch chỉ hỗ trợ tiết 1 đến tiết 10.');
        } elseif ($conflict = tkbOverlap($db, $id, $sinhVienId, $phongHoc, $thu, $tietBatDau, $soTiet, $hocKy, $namHoc)) {
            setFlash('danger', $conflict);
        } else {
            if ($id > 0) {
                $stmt = $db->prepare('
                    UPDATE thoi_khoa_bieu
                    SET sinh_vien_id = ?, hoc_phan_id = ?, thu = ?, tiet_bat_dau = ?, so_tiet = ?,
                        phong_hoc = ?, giang_vien = ?, hoc_ky = ?, nam_hoc = ?
                    WHERE id = ?
                ');
                $stmt->bind_param('iiiiissisi', $sinhVienId, $hocPhanId, $thu, $tietBatDau, $soTiet, $phongHoc, $giangVien, $hocKy, $namHoc, $id);
                setFlash($stmt->execute() ? 'success' : 'danger', $stmt->error ?: 'Cập nhật lịch học thành công.');
            } else {
                $stmt = $db->prepare('
                    INSERT INTO thoi_khoa_bieu
                        (sinh_vien_id, hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->bind_param('iiiiissis', $sinhVienId, $hocPhanId, $thu, $tietBatDau, $soTiet, $phongHoc, $giangVien, $hocKy, $namHoc);
                setFlash($stmt->execute() ? 'success' : 'danger', $stmt->error ?: 'Thêm lịch học thành công.');
            }
        }

        tkbRedirect($keepParams);
    }

    if ($postAction === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare('DELETE FROM thoi_khoa_bieu WHERE id = ?');
            $stmt->bind_param('i', $id);
            setFlash($stmt->execute() ? 'success' : 'danger', $stmt->error ?: 'Xóa lịch học thành công.');
        }

        tkbRedirect($keepParams);
    }
}

$hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
$namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
$search = trim($_GET['search'] ?? '');

$sql = '
    SELECT t.*, sv.ma_sv, sv.ho_ten, hp.ma_hp, hp.ten_hp
    FROM thoi_khoa_bieu t
    JOIN sinh_vien sv ON sv.id = t.sinh_vien_id
    JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
    WHERE t.hoc_ky = ? AND t.nam_hoc = ?
';
$types = 'is';
$params = [$hocKy, $namHoc];

if ($search !== '') {
    $like = '%' . $search . '%';
    $sql .= ' AND (sv.ma_sv LIKE ? OR sv.ho_ten LIKE ? OR hp.ma_hp LIKE ? OR hp.ten_hp LIKE ? OR t.phong_hoc LIKE ? OR t.giang_vien LIKE ?)';
    $types .= 'ssssss';
    array_push($params, $like, $like, $like, $like, $like, $like);
}

$sql .= ' ORDER BY t.thu ASC, t.tiet_bat_dau ASC, sv.ho_ten ASC';
$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$allStudents = $db->query('SELECT id, ma_sv, ho_ten, lop FROM sinh_vien ORDER BY lop, ho_ten')->fetch_all(MYSQLI_ASSOC);
$allHocPhan = $db->query('SELECT id, ma_hp, ten_hp, so_tin_chi FROM hoc_phan ORDER BY hoc_ky, ma_hp')->fetch_all(MYSQLI_ASSOC);
$listNamHoc = $db->query("SELECT DISTINCT nam_hoc FROM thoi_khoa_bieu ORDER BY nam_hoc DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

$item = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare('SELECT * FROM thoi_khoa_bieu WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    if (!$item) {
        setFlash('danger', 'Không tìm thấy lịch học cần sửa.');
        tkbRedirect(['hoc_ky' => $hocKy, 'nam_hoc' => $namHoc]);
    }
}

$page_title = 'Quản lý thời khóa biểu';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Thời khóa biểu</span>
      </div>
      <h1><i class="fas fa-calendar-alt"></i> Quản lý thời khóa biểu</h1>
      <p>Quản trị lịch học theo sinh viên, học phần, phòng học và giảng viên.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['optimize_log'])): ?>
      <div class="card fade-in" style="border-left:4px solid #28a745">
        <div class="card-body" style="padding:14px 18px">
          <strong><i class="fas fa-list-check"></i> Kết quả xếp tự động</strong>
          <ul style="margin:10px 0 0 20px">
            <?php foreach ($_SESSION['optimize_log'] as $line): ?>
              <li><?= e($line) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <?php unset($_SESSION['optimize_log']); ?>
    <?php endif; ?>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-calendar-check"></i>
        <div>
          <h3>Lịch đang hiển thị</h3>
          <div class="stat-value"><?= count($list) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-clock" style="color:#ffc107"></i>
        <div>
          <h3>HK / Năm học</h3>
          <div class="stat-value" style="font-size:22px">HK<?= $hocKy ?></div>
        </div>
      </div>
    </div>

    <div class="modal <?= ($action === 'edit' || $action === 'add') ? 'active' : '' ?>" id="formModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2><?= $action === 'edit' ? 'Sửa lịch học' : 'Thêm lịch học' ?></h2>
          <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int)($item['id'] ?? 0) ?>">
          <input type="hidden" name="hoc_ky_keep" value="<?= $hocKy ?>">
          <input type="hidden" name="nam_hoc_keep" value="<?= e($namHoc) ?>">
          <input type="hidden" name="search_keep" value="<?= e($search) ?>">

          <div class="form-row full">
            <div class="form-group">
              <label>Sinh viên <span style="color:red">*</span></label>
              <select name="sinh_vien_id" class="form-control" required>
                <option value="">-- Chọn sinh viên --</option>
                <?php foreach ($allStudents as $sv): ?>
                  <option value="<?= (int)$sv['id'] ?>" <?= (int)($item['sinh_vien_id'] ?? 0) === (int)$sv['id'] ? 'selected' : '' ?>>
                    <?= e($sv['ma_sv'] . ' - ' . $sv['ho_ten'] . ' (' . $sv['lop'] . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Học phần <span style="color:red">*</span></label>
              <select name="hoc_phan_id" class="form-control" required>
                <option value="">-- Chọn học phần --</option>
                <?php foreach ($allHocPhan as $hp): ?>
                  <option value="<?= (int)$hp['id'] ?>" <?= (int)($item['hoc_phan_id'] ?? 0) === (int)$hp['id'] ? 'selected' : '' ?>>
                    <?= e($hp['ma_hp'] . ' - ' . $hp['ten_hp'] . ' (' . $hp['so_tin_chi'] . ' TC)') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Thứ</label>
              <select name="thu" class="form-control">
                <?php for ($thu = 2; $thu <= 8; $thu++): ?>
                  <option value="<?= $thu ?>" <?= (int)($item['thu'] ?? 2) === $thu ? 'selected' : '' ?>><?= e(tenThu($thu)) ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Tiết bắt đầu</label>
              <input type="number" name="tiet_bat_dau" class="form-control" value="<?= (int)($item['tiet_bat_dau'] ?? 1) ?>" min="1" max="10" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Số tiết</label>
              <input type="number" name="so_tiet" class="form-control" value="<?= (int)($item['so_tiet'] ?? 3) ?>" min="1" max="5">
            </div>
            <div class="form-group">
              <label>Phòng học</label>
              <input type="text" name="phong_hoc" class="form-control" value="<?= e($item['phong_hoc'] ?? '') ?>" maxlength="20" placeholder="VD: A301">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Giảng viên</label>
              <input type="text" name="giang_vien" class="form-control" value="<?= e($item['giang_vien'] ?? '') ?>" maxlength="100" placeholder="VD: TS. Nguyễn Văn Hùng">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Học kỳ</label>
              <input type="number" name="hoc_ky" class="form-control" value="<?= (int)($item['hoc_ky'] ?? $hocKy) ?>" min="1" max="8">
            </div>
            <div class="form-group">
              <label>Năm học</label>
              <input type="text" name="nam_hoc" class="form-control" value="<?= e($item['nam_hoc'] ?? $namHoc) ?>" maxlength="20">
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
          <div class="form-group" style="margin:0;min-width:120px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKy === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Năm học</label>
            <select name="nam_hoc" class="form-control">
              <option value="<?= e(NAM_HOC_HIEN_TAI) ?>" <?= $namHoc === NAM_HOC_HIEN_TAI ? 'selected' : '' ?>><?= e(NAM_HOC_HIEN_TAI) ?></option>
              <?php foreach ($listNamHoc as $nh): ?>
                <?php if ($nh['nam_hoc'] === NAM_HOC_HIEN_TAI) continue; ?>
                <option value="<?= e($nh['nam_hoc']) ?>" <?= $namHoc === $nh['nam_hoc'] ? 'selected' : '' ?>><?= e($nh['nam_hoc']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" placeholder="Sinh viên, học phần, phòng, giảng viên..." value="<?= e($search) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
          <button type="button" class="btn btn-success" onclick="showAddForm()"><i class="fas fa-plus"></i> Thêm mới</button>
          <a href="<?= BASE_URL ?>/admin/thoi_khoa_bieu/optimize.php?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-warning">
            <i class="fas fa-wand-magic-sparkles"></i> Xếp tự động
          </a>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (!$list): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-calendar-xmark" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không có lịch học phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Sinh viên</th>
                  <th>Học phần</th>
                  <th style="text-align:center">Thứ</th>
                  <th style="text-align:center">Tiết</th>
                  <th>Phòng</th>
                  <th>Giảng viên</th>
                  <th style="text-align:center">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $row): ?>
                  <tr>
                    <td><small><?= e($row['ma_sv'] . ' - ' . $row['ho_ten']) ?></small></td>
                    <td><code><?= e($row['ma_hp']) ?></code><br><small><?= e($row['ten_hp']) ?></small></td>
                    <td style="text-align:center"><?= e(tenThu((int)$row['thu'])) ?></td>
                    <td style="text-align:center">T<?= (int)$row['tiet_bat_dau'] ?>-T<?= (int)$row['tiet_bat_dau'] + (int)$row['so_tiet'] - 1 ?></td>
                    <td><?= e($row['phong_hoc'] ?: '—') ?></td>
                    <td><?= e($row['giang_vien'] ?: '—') ?></td>
                    <td style="text-align:center">
                      <div class="table-actions">
                        <a class="btn btn-sm btn-info" href="?action=edit&id=<?= (int)$row['id'] ?>&hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>&search=<?= urlencode($search) ?>">
                          <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Xóa lịch học này?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <input type="hidden" name="hoc_ky_keep" value="<?= $hocKy ?>">
                          <input type="hidden" name="nam_hoc_keep" value="<?= e($namHoc) ?>">
                          <input type="hidden" name="search_keep" value="<?= e($search) ?>">
                          <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
}
function showAddForm() {
  document.getElementById('formModal').classList.add('active');
}
</script>
</body>
</html>
