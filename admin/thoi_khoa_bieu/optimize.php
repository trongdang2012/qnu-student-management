<?php
/**
 * admin/thoi_khoa_bieu/optimize.php - Xep thoi khoa bieu tu dong
 *
 * Cach xep:
 * - Lay hoc phan da duyet theo hoc ky/nam hoc.
 * - Gom sinh vien theo tung hoc phan de lop co cung mot slot.
 * - Chon slot diem phat thap nhat: tranh trung lich sinh vien, tranh trung phong,
 *   uu tien thu 2-6, uu tien buoi sang va can bang so tiet trong tuan.
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$db = getDB();
$hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
$namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

$rooms = ['A101', 'A102', 'A201', 'A202', 'B305', 'B306', 'Lab IT', 'Lab PM'];
$lecturers = [
    'TS. Nguyễn Văn Hùng',
    'ThS. Trần Thị Lan',
    'TS. Lê Văn Minh',
    'ThS. Phạm Thị Hoa',
    'TS. Võ Thành Nam',
    'ThS. Nguyễn Thị Linh',
    'TS. Phạm Văn Tú',
];

function optHasOccupied(array $occupied, int $day, int $start, int $length): bool {
    for ($t = $start; $t < $start + $length; $t++) {
        if (!empty($occupied[$day][$t])) {
            return true;
        }
    }
    return false;
}

function optMarkOccupied(array &$occupied, int $day, int $start, int $length): void {
    for ($t = $start; $t < $start + $length; $t++) {
        $occupied[$day][$t] = true;
    }
}

function optBlockLength(int $credits): int {
    if ($credits >= 5) return 4;
    if ($credits <= 1) return 2;
    return 3;
}

function optBestSlot(array $studentIds, int $length, array $studentOccupied, array $roomOccupied, array $dayLoad, array $rooms): ?array {
    $best = null;
    $days = [2, 3, 4, 5, 6, 7, 8];
    $starts = [1, 4, 6, 8, 2, 5, 7];

    foreach ($days as $day) {
        foreach ($starts as $start) {
            if ($start + $length - 1 > 10) {
                continue;
            }

            foreach ($rooms as $room) {
                if (isset($roomOccupied[$room]) && optHasOccupied($roomOccupied[$room], $day, $start, $length)) {
                    continue;
                }

                $conflict = false;
                foreach ($studentIds as $sid) {
                    if (isset($studentOccupied[$sid]) && optHasOccupied($studentOccupied[$sid], $day, $start, $length)) {
                        $conflict = true;
                        break;
                    }
                }

                if ($conflict) {
                    continue;
                }

                $score = 0;
                $score += ($dayLoad[$day] ?? 0) * 8;
                $score += $day === 7 ? 20 : 0;
                $score += $day === 8 ? 45 : 0;
                $score += $start >= 6 ? 8 : 0;
                $score += $start === 8 ? 5 : 0;
                $score += abs(4 - $day) * 2;

                if ($best === null || $score < $best['score']) {
                    $best = [
                        'day' => $day,
                        'start' => $start,
                        'room' => $room,
                        'score' => $score,
                    ];
                }
            }
        }
    }

    return $best;
}

$stmt = $db->prepare("
    SELECT
        hp.id AS hoc_phan_id,
        hp.ma_hp,
        hp.ten_hp,
        hp.so_tin_chi,
        GROUP_CONCAT(dk.sinh_vien_id ORDER BY dk.sinh_vien_id) AS student_ids,
        COUNT(*) AS total_students
    FROM dang_ky_hp dk
    JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
    WHERE dk.hoc_ky = ?
      AND dk.nam_hoc = ?
      AND dk.trang_thai = 'Đã duyệt'
    GROUP BY hp.id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi
    ORDER BY total_students DESC, hp.so_tin_chi DESC, hp.ma_hp ASC
");
$stmt->bind_param('is', $hocKy, $namHoc);
$stmt->execute();
$courseGroups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$existingCountStmt = $db->prepare('SELECT COUNT(*) AS total FROM thoi_khoa_bieu WHERE hoc_ky = ? AND nam_hoc = ?');
$existingCountStmt->bind_param('is', $hocKy, $namHoc);
$existingCountStmt->execute();
$existingCount = (int)$existingCountStmt->get_result()->fetch_assoc()['total'];

$studentCountStmt = $db->prepare("
    SELECT COUNT(DISTINCT sinh_vien_id) AS total
    FROM dang_ky_hp
    WHERE hoc_ky = ? AND nam_hoc = ? AND trang_thai = 'Đã duyệt'
");
$studentCountStmt->bind_param('is', $hocKy, $namHoc);
$studentCountStmt->execute();
$studentCount = (int)$studentCountStmt->get_result()->fetch_assoc()['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'optimize') {
    $replace = !empty($_POST['replace_existing']);
    $created = 0;
    $skipped = 0;
    $failed = 0;
    $logs = [];

    $studentOccupied = [];
    $roomOccupied = [];
    $dayLoad = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0];

    $db->begin_transaction();

    try {
        if ($replace) {
            $deleteStmt = $db->prepare('DELETE FROM thoi_khoa_bieu WHERE hoc_ky = ? AND nam_hoc = ?');
            $deleteStmt->bind_param('is', $hocKy, $namHoc);
            $deleteStmt->execute();
        } else {
            $stmt = $db->prepare('
                SELECT sinh_vien_id, thu, tiet_bat_dau, so_tiet, phong_hoc
                FROM thoi_khoa_bieu
                WHERE hoc_ky = ? AND nam_hoc = ?
            ');
            $stmt->bind_param('is', $hocKy, $namHoc);
            $stmt->execute();
            $existingRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            foreach ($existingRows as $row) {
                $sid = (int)$row['sinh_vien_id'];
                $day = (int)$row['thu'];
                $start = (int)$row['tiet_bat_dau'];
                $length = (int)$row['so_tiet'];
                $room = $row['phong_hoc'] ?: 'NO_ROOM';
                optMarkOccupied($studentOccupied[$sid], $day, $start, $length);
                optMarkOccupied($roomOccupied[$room], $day, $start, $length);
                $dayLoad[$day] = ($dayLoad[$day] ?? 0) + $length;
            }
        }

        $insertStmt = $db->prepare('
            INSERT INTO thoi_khoa_bieu
                (sinh_vien_id, hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $existingPairStmt = $db->prepare('
            SELECT 1
            FROM thoi_khoa_bieu
            WHERE sinh_vien_id = ? AND hoc_phan_id = ? AND hoc_ky = ? AND nam_hoc = ?
            LIMIT 1
        ');

        foreach ($courseGroups as $index => $course) {
            $allStudentIds = array_filter(array_map('intval', explode(',', $course['student_ids'])));
            $studentIds = [];

            if (!$replace) {
                foreach ($allStudentIds as $sid) {
                    $hpId = (int)$course['hoc_phan_id'];
                    $existingPairStmt->bind_param('iiis', $sid, $hpId, $hocKy, $namHoc);
                    $existingPairStmt->execute();
                    if ($existingPairStmt->get_result()->fetch_assoc()) {
                        $skipped++;
                    } else {
                        $studentIds[] = $sid;
                    }
                }
            } else {
                $studentIds = $allStudentIds;
            }

            if (!$studentIds) {
                continue;
            }

            $length = optBlockLength((int)$course['so_tin_chi']);
            $slot = optBestSlot($studentIds, $length, $studentOccupied, $roomOccupied, $dayLoad, $rooms);

            if (!$slot) {
                $failed += count($studentIds);
                $logs[] = 'Không đủ slot cho ' . $course['ma_hp'] . ' - ' . $course['ten_hp'];
                continue;
            }

            $hpId = (int)$course['hoc_phan_id'];
            $day = (int)$slot['day'];
            $start = (int)$slot['start'];
            $room = $slot['room'];
            $lecturer = $lecturers[$index % count($lecturers)];

            foreach ($studentIds as $sid) {
                $insertStmt->bind_param('iiiiissis', $sid, $hpId, $day, $start, $length, $room, $lecturer, $hocKy, $namHoc);
                if ($insertStmt->execute()) {
                    $created++;
                    optMarkOccupied($studentOccupied[$sid], $day, $start, $length);
                } else {
                    $failed++;
                }
            }

            optMarkOccupied($roomOccupied[$room], $day, $start, $length);
            $dayLoad[$day] = ($dayLoad[$day] ?? 0) + $length;
            $logs[] = $course['ma_hp'] . ' xếp vào ' . tenThu($day) . ', T' . $start . '-T' . ($start + $length - 1) . ', phòng ' . $room;
        }

        $db->commit();
        setFlash('success', "Xếp tự động xong: tạo $created lịch, bỏ qua $skipped lịch đã có, $failed mục chưa xếp được.");
        $_SESSION['optimize_log'] = array_slice($logs, 0, 12);
    } catch (Throwable $e) {
        $db->rollback();
        setFlash('danger', 'Không thể xếp thời khóa biểu: ' . $e->getMessage());
    }

    header('Location: ' . BASE_URL . '/admin/thoi_khoa_bieu/index.php?hoc_ky=' . $hocKy . '&nam_hoc=' . urlencode($namHoc));
    exit;
}

$preview = [];
$previewDayLoad = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0];
foreach ($courseGroups as $i => $course) {
    $len = optBlockLength((int)$course['so_tin_chi']);
    $day = 2 + ($i % 5);
    $start = ($i % 2 === 0) ? 1 : 4;
    $preview[] = [
        'ma_hp' => $course['ma_hp'],
        'ten_hp' => $course['ten_hp'],
        'students' => (int)$course['total_students'],
        'length' => $len,
        'hint' => tenThu($day) . ', T' . $start . '-T' . ($start + $len - 1),
    ];
    $previewDayLoad[$day] += $len;
}

$page_title = 'Xếp thời khóa biểu tự động';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/thoi_khoa_bieu/index.php">Thời khóa biểu</a>
        <span>›</span><span>Xếp tự động</span>
      </div>
      <h1><i class="fas fa-wand-magic-sparkles"></i> Xếp thời khóa biểu tự động</h1>
      <p>Thuật toán ưu tiên lịch gọn, tránh trùng sinh viên/phòng và dùng dữ liệu đăng ký học phần QNU.</p>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-book-open"></i>
        <div>
          <h3>Học phần đã duyệt</h3>
          <div class="stat-value"><?= count($courseGroups) ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#17a2b8">
        <i class="fas fa-users" style="color:#17a2b8"></i>
        <div>
          <h3>Sinh viên có đăng ký</h3>
          <div class="stat-value"><?= $studentCount ?></div>
        </div>
      </div>
      <div class="stat-card" style="border-left-color:#ffc107">
        <i class="fas fa-calendar-day" style="color:#ffc107"></i>
        <div>
          <h3>Lịch hiện có</h3>
          <div class="stat-value"><?= $existingCount ?></div>
        </div>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-sliders"></i> Tham số xếp lịch</h3>
      </div>
      <div class="card-body" style="padding:20px">
        <form method="GET" class="action-bar" style="align-items:flex-end">
          <div class="form-group" style="margin:0;min-width:130px">
            <label>Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKy === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:160px">
            <label>Năm học</label>
            <input type="text" name="nam_hoc" class="form-control" value="<?= e($namHoc) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Xem dữ liệu</button>
          <a href="<?= BASE_URL ?>/admin/thoi_khoa_bieu/index.php?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
          </a>
        </form>

        <div style="background:#f7f9fc;border-left:4px solid #0066cc;border-radius:6px;padding:14px;margin-top:14px">
          <strong>Luật tối ưu:</strong>
          gom sinh viên theo học phần, tránh trùng lịch sinh viên, tránh trùng phòng, ưu tiên thứ 2-6 và buổi sáng, hạn chế dồn quá nhiều tiết vào một ngày.
        </div>
      </div>
    </div>

    <?php if (!$courseGroups): ?>
      <div class="alert alert-info fade-in">
        <i class="fas fa-info-circle"></i>
        Chưa có đăng ký học phần đã duyệt cho HK<?= $hocKy ?> / <?= e($namHoc) ?>.
      </div>
    <?php else: ?>
      <div class="card fade-in">
        <div class="card-header">
          <h3><i class="fas fa-list-check"></i> Dữ liệu sẽ xếp</h3>
        </div>
        <div class="card-body" style="padding:0">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Học phần</th>
                  <th style="text-align:center">SV đăng ký</th>
                  <th style="text-align:center">Số tiết/buổi</th>
                  <th>Gợi ý ban đầu</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($preview as $row): ?>
                  <tr>
                    <td><code><?= e($row['ma_hp']) ?></code><br><small><?= e($row['ten_hp']) ?></small></td>
                    <td style="text-align:center"><?= (int)$row['students'] ?></td>
                    <td style="text-align:center"><?= (int)$row['length'] ?></td>
                    <td><?= e($row['hint']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card fade-in">
        <div class="card-body" style="padding:20px">
          <form method="POST" onsubmit="return confirm('Bắt đầu xếp thời khóa biểu tự động?')">
            <input type="hidden" name="action" value="optimize">
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
              <input type="checkbox" name="replace_existing" value="1">
              Ghi đè toàn bộ lịch HK<?= $hocKy ?> / <?= e($namHoc) ?> trước khi xếp lại
            </label>
            <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
              <a href="<?= BASE_URL ?>/admin/thoi_khoa_bieu/index.php?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-secondary">Hủy</a>
              <button type="submit" class="btn btn-success" style="padding:10px 28px">
                <i class="fas fa-bolt"></i> Xếp lịch ngay
              </button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>

</body>
</html>
