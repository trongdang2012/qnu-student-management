<?php
/**
 * student/truc_tuyen/dang_ky.php - UC9: Đăng ký học phần trực tuyến
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db  = getDB();
$sid = (int)$sv['id'];
$hk  = HOC_KY_HIEN_TAI;
$nh  = NAM_HOC_HIEN_TAI;

// ── Xử lý đăng ký ───────────────────────────────────────────
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']    ?? '';
    $hp_id_raw = (int)($_POST['hoc_phan_id'] ?? 0);

    if ($action === 'dang_ky' && $hp_id_raw > 0) {
        // Lấy thông tin học phần để kiểm tra điều kiện
        $stmt_course = $db->prepare("SELECT ma_hp, ten_hp, thu, tiet_bat_dau, so_tiet, si_so_toi_da, si_so_hien_tai, ma_hp_tien_quyet FROM hoc_phan WHERE id = ?");
        $stmt_course->bind_param('i', $hp_id_raw);
        $stmt_course->execute();
        $target_course = $stmt_course->get_result()->fetch_assoc();
        $stmt_course->close();

        if (!$target_course) {
            $msg = ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau'];
        } else {
            $ma_hp = $target_course['ma_hp'];
            $ten_hp = $target_course['ten_hp'];
            $target_thu = $target_course['thu'];
            $target_start = $target_course['tiet_bat_dau'];
            $target_count = $target_course['so_tiet'];
            $si_so_toi_da = (int)$target_course['si_so_toi_da'];
            $si_so_hien_tai = (int)$target_course['si_so_hien_tai'];
            $prereq_ma = $target_course['ma_hp_tien_quyet'];

            // 1. Kiểm tra đã đăng ký trước đó
            $chk = $db->prepare("SELECT id FROM dang_ky_hp WHERE sinh_vien_id=? AND hoc_phan_id=? AND hoc_ky=? AND nam_hoc=? AND trang_thai IN ('Chờ duyệt', 'Đã duyệt')");
            $hk_str = (string)$hk;
            $chk->bind_param('iiss', $sid, $hp_id_raw, $hk_str, $nh);
            $chk->execute();
            $already_registered = $chk->get_result()->num_rows > 0;
            $chk->close();

            if ($already_registered) {
                $msg = ['type' => 'warning', 'text' => 'Bạn đã đăng ký học phần này'];
            }
            // 2. Kiểm tra học phần đã đầy
            elseif ($si_so_hien_tai >= $si_so_toi_da) {
                $msg = ['type' => 'danger', 'text' => 'Học phần đã đủ số lượng'];
            }
            // 3. Kiểm tra điều kiện tiên quyết
            else {
                $prereq_failed = false;
                if (!empty($prereq_ma)) {
                    // Lấy id học phần tiên quyết
                    $stmt_pr = $db->prepare("SELECT id, ten_hp FROM hoc_phan WHERE ma_hp = ?");
                    $stmt_pr->bind_param('s', $prereq_ma);
                    $stmt_pr->execute();
                    $res_pr = $stmt_pr->get_result()->fetch_assoc();
                    $stmt_pr->close();

                    if ($res_pr) {
                        $prereq_id = (int)$res_pr['id'];
                        $prereq_name = $res_pr['ten_hp'];
                        
                        // Kiểm tra sinh viên đã học và đạt môn này chưa (điểm hệ 4 >= 1.0)
                        $stmt_pass = $db->prepare("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = ? AND hoc_phan_id = ? AND diem_he4 >= 1.0");
                        $stmt_pass->bind_param('ii', $sid, $prereq_id);
                        $stmt_pass->execute();
                        $has_passed = $stmt_pass->get_result()->num_rows > 0;
                        $stmt_pass->close();

                        if (!$has_passed) {
                            $msg = ['type' => 'danger', 'text' => 'Không đủ điều kiện đăng ký'];
                            $prereq_failed = true;
                        }
                    }
                }

                if (!$prereq_failed) {
                    // 4. Kiểm tra trùng lịch học
                    $clash = false;
                    $clashing_course_name = '';

                    // Lấy danh sách lịch các môn đã đăng ký kỳ này của sinh viên
                    $stmt_active = $db->prepare("
                        SELECT hp.ten_hp, hp.thu, hp.tiet_bat_dau, hp.so_tiet
                        FROM dang_ky_hp dk
                        JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
                        WHERE dk.sinh_vien_id = ? AND dk.hoc_ky = ? AND dk.nam_hoc = ? AND dk.trang_thai IN ('Chờ duyệt', 'Đã duyệt')
                    ");
                    $stmt_active->bind_param('iss', $sid, $hk_str, $nh);
                    $stmt_active->execute();
                    $res_active = $stmt_active->get_result();

                    while ($row = $res_active->fetch_assoc()) {
                        $act_thu = $row['thu'];
                        $act_start = $row['tiet_bat_dau'];
                        $act_count = $row['so_tiet'];

                        if ($act_thu == $target_thu && $act_thu !== null && $target_thu !== null) {
                            // Kiểm tra giao khoảng thời gian tiết học
                            if ($target_start < $act_start + $act_count && $act_start < $target_start + $target_count) {
                                $clash = true;
                                $clashing_course_name = $row['ten_hp'];
                                break;
                            }
                        }
                    }
                    $stmt_active->close();

                    if ($clash) {
                        $msg = ['type' => 'danger', 'text' => 'Trùng lịch học'];
                    } else {
                        // Thỏa mãn mọi điều kiện -> Lưu thông tin đăng ký và cập nhật số lượng
                        $db->begin_transaction();
                        try {
                            $ins = $db->prepare("INSERT INTO dang_ky_hp (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, trang_thai) VALUES (?, ?, ?, ?, 'Chờ duyệt')");
                            $ins->bind_param('iiss', $sid, $hp_id_raw, $hk_str, $nh);
                            $ins->execute();
                            $ins->close();

                            $upd = $db->prepare("UPDATE hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = ?");
                            $upd->bind_param('i', $hp_id_raw);
                            $upd->execute();
                            $upd->close();

                            $db->commit();
                            $msg = ['type' => 'success', 'text' => 'Đăng ký thành công!'];
                        } catch (Exception $e) {
                            $db->rollback();
                            $msg = ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau'];
                        }
                    }
                }
            }
        }
    }

    if ($action === 'huy' && $hp_id_raw > 0) {
        $db->begin_transaction();
        try {
            $del = $db->prepare("DELETE FROM dang_ky_hp WHERE sinh_vien_id=? AND hoc_phan_id=? AND hoc_ky=? AND nam_hoc=? AND trang_thai='Chờ duyệt'");
            $hk_str = (string)$hk;
            $del->bind_param('iiss', $sid, $hp_id_raw, $hk_str, $nh);
            $del->execute();
            $affected = $del->affected_rows;
            $del->close();

            if ($affected > 0) {
                $upd = $db->prepare("UPDATE hoc_phan SET si_so_hien_tai = GREATEST(0, si_so_hien_tai - 1) WHERE id = ?");
                $upd->bind_param('i', $hp_id_raw);
                $upd->execute();
                $upd->close();

                $db->commit();
                $msg = ['type' => 'success', 'text' => 'Đã hủy đăng ký học phần.'];
            } else {
                $db->rollback();
                $msg = ['type' => 'warning', 'text' => 'Không thể hủy. Học phần đã được duyệt hoặc đã bị hủy.'];
            }
        } catch (Exception $e) {
            $db->rollback();
            $msg = ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau'];
        }
    }
}

// ── Danh sách HP đã đăng ký kỳ này ─────────────────────────
$da_dk = $db->query("
    SELECT dk.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.thu, hp.tiet_bat_dau, hp.so_tiet, hp.phong_hoc, hp.giang_vien, hp.si_so_toi_da, hp.si_so_hien_tai, hp.ma_hp_tien_quyet
    FROM dang_ky_hp dk
    JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
    WHERE dk.sinh_vien_id = $sid AND dk.hoc_ky = '$hk' AND dk.nam_hoc = '$nh'
    ORDER BY dk.ngay_dang_ky DESC
")->fetch_all(MYSQLI_ASSOC);
$da_dk_ids = array_column($da_dk, 'hoc_phan_id');

// ── Danh sách HP có thể đăng ký (HK hiện tại trong CTDT, chưa đăng ký, chưa có điểm) ──
$nganh_esc = $db->real_escape_string($sv['nganh']);
$id_str = empty($da_dk_ids) ? '0' : implode(',', $da_dk_ids);
$co_the_dk = $db->query("
    SELECT hp.id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai, hp.thu, hp.tiet_bat_dau, hp.so_tiet, hp.phong_hoc, hp.giang_vien, hp.si_so_toi_da, hp.si_so_hien_tai, hp.ma_hp_tien_quyet
    FROM ctdt_chi_tiet c
    JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
    WHERE c.nganh = '$nganh_esc'
      AND hp.id NOT IN ($id_str)
      AND NOT EXISTS (
          SELECT 1 FROM diem_hoc_tap d
          WHERE d.hoc_phan_id = hp.id AND d.sinh_vien_id = $sid AND d.diem_he4 >= 1.0
      )
    ORDER BY c.hoc_ky, hp.ten_hp
")->fetch_all(MYSQLI_ASSOC);

// Tổng TC đã đăng ký kỳ này
$tc_dang_ky = array_sum(array_column($da_dk, 'so_tin_chi'));

// ── Helper: Định dạng lịch học ──────────────────────────────
function formatLichHoc($thu, $tiet_bd, $so_tiet, $phong, $gv): string {
    if (!$thu) return '<span class="text-muted">Chưa xếp lịch</span>';
    $thu_lbl = $thu == 8 ? 'Chủ nhật' : "Thứ $thu";
    $tiet_kt = $tiet_bd + $so_tiet - 1;
    return "<strong>$thu_lbl</strong><div class='text-muted' style='font-size:12px;margin-top:2px;'><i class='fas fa-clock'></i> Tiết $tiet_bd - $tiet_kt<br><i class='fas fa-map-marker-alt'></i> Phòng $phong<br><i class='fas fa-user-tie'></i> $gv</div>";
}

// ── Helper: Định dạng học phần tiên quyết ───────────────────
function formatTienQuyet($ma_prereq, $db): string {
    if (!$ma_prereq) return '<span class="text-muted">—</span>';
    $stmt = $db->prepare("SELECT ten_hp FROM hoc_phan WHERE ma_hp = ?");
    $stmt->bind_param('s', $ma_prereq);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $name = $res ? $res['ten_hp'] : $ma_prereq;
    return "<span class='badge badge-warning' style='font-size:11px;' data-tooltip='Yêu cầu hoàn thành trước'><i class='fas fa-link'></i> $name</span>";
}

function dkBadge(string $tt): string {
    return match($tt) {
        'Đã duyệt' => '<span class="badge badge-success"><i class="fas fa-check"></i> Đã duyệt</span>',
        'Từ chối'  => '<span class="badge badge-danger"><i class="fas fa-times"></i> Từ chối</span>',
        'Đã hủy'   => '<span class="badge badge-secondary">Đã hủy</span>',
        default    => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Chờ duyệt</span>',
    };
}

$page_title  = 'Đăng ký học phần';
$active_menu = 'truc_tuyen';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
        <span>›</span><span>Trực tuyến</span>
        <span>›</span><span>Đăng ký học phần</span>
      </div>
      <h1><i class="fas fa-plus-circle"></i> Đăng ký học phần</h1>
      <p>Học kỳ <?= $hk ?> — Năm học <?= NAM_HOC_HIEN_TAI ?></p>
    </div>

    <!-- Flash -->
    <?php if ($msg): ?>
      <div class="alert alert-<?= $msg['type'] ?>" data-auto-dismiss>
        <i class="fas fa-<?= $msg['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
        <?= e($msg['text']) ?>
      </div>
    <?php endif; ?>

    <!-- Thông tin -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--primary)"><?= count($da_dk) ?></div>
            <div class="stat-label">Học phần đã đăng ký HK<?= $hk ?></div>
          </div>
        </div>
      </div>
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon green"><i class="fas fa-layer-group"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--success)"><?= $tc_dang_ky ?></div>
            <div class="stat-label">Tín chỉ đã đăng ký (khuyến nghị 15-21 TC)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <div class="dk-tabs" style="padding:0 20px;margin:0">
          <button class="dk-tab active" data-tab="da-dk" id="tab-da-dk">
            <i class="fas fa-list-check"></i> Đã đăng ký (<?= count($da_dk) ?>)
          </button>
          <button class="dk-tab" data-tab="co-the" id="tab-co-the">
            <i class="fas fa-plus"></i> Học phần có thể đăng ký (<?= count($co_the_dk) ?>)
          </button>
        </div>

        <!-- Panel: Đã đăng ký -->
        <div class="dk-panel active" id="panel-da-dk">
          <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th>Lịch học</th>
              <th style="text-align:center">Ngày đăng ký</th>
              <th style="text-align:center">Trạng thái</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($da_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Chưa đăng ký học phần nào. Chọn tab bên phải để đăng ký.
              </td></tr>
            <?php else: ?>
            <?php foreach ($da_dk as $dk): ?>
              <tr>
                <td><code><?= e($dk['ma_hp']) ?></code></td>
                <td class="fw-500"><?= e($dk['ten_hp']) ?></td>
                <td style="text-align:center"><?= (int)$dk['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-secondary"><?= e($dk['loai'] ?? '') ?></span>
                </td>
                <td><?= formatLichHoc($dk['thu'], $dk['tiet_bat_dau'], $dk['so_tiet'], $dk['phong_hoc'], $dk['giang_vien']) ?></td>
                <td style="text-align:center;font-size:13px">
                  <?= date('d/m/Y H:i', strtotime($dk['ngay_dang_ky'])) ?>
                </td>
                <td style="text-align:center"><?= dkBadge($dk['trang_thai']) ?></td>
                <td style="text-align:center">
                  <?php if ($dk['trang_thai'] === 'Chờ duyệt'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action" value="huy">
                      <input type="hidden" name="hoc_phan_id" value="<?= (int)$dk['hoc_phan_id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm"
                              data-confirm="Bạn có chắc muốn hủy đăng ký học phần này?">
                        <i class="fas fa-times"></i> Hủy
                      </button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--text-muted);font-size:13px">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Panel: Có thể đăng ký -->
        <div class="dk-panel" id="panel-co-the">
          <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
            <input type="text" id="tableSearch" data-table="#tblCoDk"
                   placeholder="🔍 Tìm học phần..." class="form-control"
                   style="max-width:280px;padding:7px 12px;font-size:14px">
          </div>
          <div class="table-wrap">
          <table id="tblCoDk">
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th>Lịch học</th>
              <th>Học phần tiên quyết</th>
              <th style="text-align:center">Còn lại</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($co_the_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Bạn đã đăng ký hoặc hoàn thành tất cả học phần.
              </td></tr>
            <?php else: ?>
            <?php foreach ($co_the_dk as $hp): 
              $con_lai = $hp['si_so_toi_da'] - $hp['si_so_hien_tai'];
              $is_full = $con_lai <= 0;
            ?>
              <tr>
                <td><code><?= e($hp['ma_hp']) ?></code></td>
                <td class="fw-500"><?= e($hp['ten_hp']) ?></td>
                <td style="text-align:center;font-weight:700"><?= (int)$hp['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-<?= $hp['loai']==='Bắt buộc'?'danger':($hp['loai']==='Tự chọn'?'warning':'info') ?>">
                    <?= e($hp['loai']) ?>
                  </span>
                </td>
                <td><?= formatLichHoc($hp['thu'], $hp['tiet_bat_dau'], $hp['so_tiet'], $hp['phong_hoc'], $hp['giang_vien']) ?></td>
                <td><?= formatTienQuyet($hp['ma_hp_tien_quyet'], $db) ?></td>
                <td style="text-align:center">
                  <?php if ($is_full): ?>
                    <span class="badge badge-danger" style="font-weight:700">Đầy (0/<?= $hp['si_so_toi_da'] ?>)</span>
                  <?php else: ?>
                    <span class="badge badge-success" style="font-weight:700"><?= $con_lai ?> / <?= $hp['si_so_toi_da'] ?></span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="dang_ky">
                    <input type="hidden" name="hoc_phan_id" value="<?= (int)$hp['id'] ?>">
                    <?php if ($is_full): ?>
                      <button type="submit" class="btn btn-secondary btn-sm"
                              data-confirm="Học phần đã đủ số lượng, bạn có chắc chắn muốn thử đăng ký?">
                        <i class="fas fa-exclamation-triangle"></i> Đăng ký (Đầy)
                      </button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-primary btn-sm"
                              data-confirm="Đăng ký học phần: <?= e($hp['ten_hp']) ?>?">
                        <i class="fas fa-plus"></i> Đăng ký
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
          </div>
        </div>

      </div><!-- /card-body -->
    </div><!-- /card -->

    <!-- Ghi chú -->
    <div class="card mt-16 fade-in">
      <div class="card-body">
        <h4 style="color:var(--primary);margin-bottom:10px"><i class="fas fa-info-circle"></i> Lưu ý khi đăng ký học phần</h4>
        <ul style="font-size:14px;color:var(--text-muted);line-height:2;padding-left:20px">
          <li>Sinh viên chỉ được đăng ký tối đa <strong>30 tín chỉ/học kỳ</strong> và tối thiểu <strong>12 tín chỉ/học kỳ</strong> (trừ kỳ cuối).</li>
          <li>Sau khi đăng ký, học phần sẽ ở trạng thái <strong>Chờ duyệt</strong> cho đến khi Phòng Đào tạo xét duyệt.</li>
          <li>Bạn chỉ có thể <strong>hủy</strong> học phần khi trạng thái là <strong>Chờ duyệt</strong>.</li>
          <li>Hạn đăng ký học phần: <strong>2 tuần đầu mỗi học kỳ</strong>.</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
