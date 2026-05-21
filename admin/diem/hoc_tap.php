<?php
/**
 * admin/diem/hoc_tap.php - UC27 & UC28: Nhập và sửa điểm học tập cho Admin
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();
$db = getDB();

$action = $_GET['action'] ?? 'list';
$hoc_phan_id = (int)($_GET['hoc_phan_id'] ?? 0);

// Xử lý lưu điểm (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_grades') {
    $hoc_phan_id = (int)($_POST['hoc_phan_id'] ?? 0);
    
    // Lấy thông tin học phần
    $stmt = $db->prepare("SELECT * FROM hoc_phan WHERE id = ?");
    $stmt->bind_param('i', $hoc_phan_id);
    $stmt->execute();
    $hp = $stmt->get_result()->fetch_assoc();
    
    if (!$hp) {
        setFlash('danger', 'Không tìm thấy học phần.');
        header('Location: hoc_tap.php');
        exit;
    }
    
    $diem_data = $_POST['diem'] ?? [];
    $errors = [];
    $to_save = [];
    
    // 1. Kiểm tra tính hợp lệ của dữ liệu
    foreach ($diem_data as $sv_id => $grades) {
        $sv_id = (int)$sv_id;
        
        // Lấy thông tin sinh viên để báo lỗi chi tiết
        $stmt = $db->prepare("SELECT ho_ten, ma_sv FROM sinh_vien WHERE id = ?");
        $stmt->bind_param('i', $sv_id);
        $stmt->execute();
        $sv = $stmt->get_result()->fetch_assoc();
        $sv_name = $sv ? $sv['ho_ten'] . " (".$sv['ma_sv'].")" : "Sinh viên ID $sv_id";
        
        $cc_str = trim($grades['cc'] ?? '');
        $gk_str = trim($grades['gk'] ?? '');
        $ck_str = trim($grades['ck'] ?? '');
        
        // Nếu để trống toàn bộ, bỏ qua không xử lý (cho phép không nhập điểm đồng loạt)
        if ($cc_str === '' && $gk_str === '' && $ck_str === '') {
            continue;
        }
        
        // Nếu một trong các cột bị để trống
        if ($cc_str === '' || $gk_str === '' || $ck_str === '') {
            $errors[] = "Vui lòng nhập đầy đủ thông tin điểm cho sinh viên $sv_name.";
            continue;
        }
        
        // Kiểm tra xem có phải số hợp lệ từ 0 đến 10 không
        $cc = filter_var($cc_str, FILTER_VALIDATE_FLOAT);
        $gk = filter_var($gk_str, FILTER_VALIDATE_FLOAT);
        $ck = filter_var($ck_str, FILTER_VALIDATE_FLOAT);
        
        if ($cc === false || $cc < 0 || $cc > 10 ||
            $gk === false || $gk < 0 || $gk > 10 ||
            $ck === false || $ck < 0 || $ck > 10) {
            $errors[] = "Điểm chuyên cần, giữa kỳ, cuối kỳ của sinh viên $sv_name không hợp lệ, vui lòng nhập lại (0–10).";
            continue;
        }
        
        // Tính toán các loại điểm
        $diem_tong = round($cc * 0.1 + $gk * 0.3 + $ck * 0.6, 2);
        $diem_chu = diemChu($diem_tong);
        $diem_he4 = diemHe4($diem_tong);
        
        // Lấy học kỳ và năm học từ đăng ký học phần để lưu cho chính xác
        $stmt = $db->prepare("SELECT hoc_ky, nam_hoc FROM dang_ky_hp WHERE sinh_vien_id = ? AND hoc_phan_id = ? AND trang_thai = 'Đã duyệt' LIMIT 1");
        $stmt->bind_param('ii', $sv_id, $hoc_phan_id);
        $stmt->execute();
        $dk_info = $stmt->get_result()->fetch_assoc();
        
        $hk_val = $dk_info ? (int)$dk_info['hoc_ky'] : (int)$hp['hoc_ky'];
        $nh_val = $dk_info ? $dk_info['nam_hoc'] : $hp['nien_khoa'];
        
        $to_save[] = [
            'sinh_vien_id' => $sv_id,
            'hoc_ky' => $hk_val,
            'nam_hoc' => $nh_val,
            'diem_cc' => $cc,
            'diem_gk' => $gk,
            'diem_ck' => $ck,
            'diem_tong' => $diem_tong,
            'diem_chu' => $diem_chu,
            'diem_he4' => $diem_he4
        ];
    }
    
    // Nếu có lỗi, hiển thị thông báo và giữ lại giao diện
    if (!empty($errors)) {
        setFlash('danger', implode('<br>', $errors));
        header("Location: hoc_tap.php?action=edit&hoc_phan_id=$hoc_phan_id");
        exit;
    }
    
    // 2. Thực hiện lưu vào CSDL
    $db->begin_transaction();
    try {
        foreach ($to_save as $row) {
            // Kiểm tra xem đã tồn tại bản ghi điểm chưa
            $stmt = $db->prepare("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = ? AND hoc_phan_id = ? AND hoc_ky = ? AND nam_hoc = ?");
            $stmt->bind_param('iiis', $row['sinh_vien_id'], $hoc_phan_id, $row['hoc_ky'], $row['nam_hoc']);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            
            if ($exists) {
                // UPDATE
                $stmt = $db->prepare("
                    UPDATE diem_hoc_tap 
                    SET diem_cc = ?, diem_gk = ?, diem_ck = ?, diem_tong = ?, diem_chu = ?, diem_he4 = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('ddddsdi', $row['diem_cc'], $row['diem_gk'], $row['diem_ck'], $row['diem_tong'], $row['diem_chu'], $row['diem_he4'], $exists['id']);
                $stmt->execute();
            } else {
                // INSERT
                $stmt = $db->prepare("
                    INSERT INTO diem_hoc_tap (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, diem_cc, diem_gk, diem_ck, diem_tong, diem_chu, diem_he4)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('iiiiddddsd', $row['sinh_vien_id'], $hoc_phan_id, $row['hoc_ky'], $row['nam_hoc'], $row['diem_cc'], $row['diem_gk'], $row['diem_ck'], $row['diem_tong'], $row['diem_chu'], $row['diem_he4']);
                $stmt->execute();
            }
        }
        $db->commit();
        setFlash('success', 'Cập nhật điểm học phần thành công!');
    } catch (Exception $e) {
        $db->rollback();
        setFlash('danger', 'Lỗi hệ thống khi lưu: ' . $e->getMessage());
    }
    
    header("Location: hoc_tap.php?action=edit&hoc_phan_id=$hoc_phan_id");
    exit;
}

// ── Màn hình 1: Danh sách học phần ───────────────────────────
if ($action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
    $loai = trim($_GET['loai'] ?? '');
    
    $sql = "
        SELECT hp.*,
               (SELECT COUNT(*) FROM dang_ky_hp WHERE hoc_phan_id = hp.id AND trang_thai = 'Đã duyệt') AS si_so_dk,
               (SELECT COUNT(DISTINCT sinh_vien_id) FROM diem_hoc_tap WHERE hoc_phan_id = hp.id) AS so_sv_co_diem
        FROM hoc_phan hp
        WHERE 1 = 1
    ";
    $types = '';
    $params = [];
    
    if ($search !== '') {
        $like = '%' . $search . '%';
        $sql .= " AND (hp.ma_hp LIKE ? OR hp.ten_hp LIKE ? OR hp.nien_khoa LIKE ?)";
        $types .= 'sss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    
    if ($hoc_ky >= 1 && $hoc_ky <= 8) {
        $sql .= " AND hp.hoc_ky = ?";
        $types .= 'i';
        $params[] = $hoc_ky;
    }
    
    if (in_array($loai, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
        $sql .= " AND hp.loai = ?";
        $types .= 's';
        $params[] = $loai;
    }
    
    $sql .= " ORDER BY hp.hoc_ky ASC, hp.ma_hp ASC";
    
    $stmt = $db->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $list_hp = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $page_title = 'Nhập điểm học tập';
    require_once ROOT . '/includes/admin/header_admin.php';
    require_once ROOT . '/includes/admin/navbar_admin.php';
    ?>
    <div class="admin-wrapper">
      <div class="admin-container">
        
        <div class="page-title fade-in">
          <div class="breadcrumb">
            <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
            <span>›</span><span>Điểm học tập</span>
          </div>
          <h1><i class="fas fa-graduation-cap"></i> Nhập điểm học tập</h1>
          <p>Chọn học phần để nhập điểm chuyên cần, giữa kỳ, cuối kỳ cho sinh viên.</p>
        </div>

        <?php if ($flash = getFlash()): ?>
          <div class="alert alert-<?= e($flash['type']) ?> fade-in">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $flash['msg'] ?>
          </div>
        <?php endif; ?>

        <!-- Bộ lọc tìm kiếm -->
        <div class="card fade-in">
          <div class="card-body" style="padding:16px">
            <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0">
              <div class="form-group search-box" style="margin:0">
                <label style="font-size:12px">Tìm kiếm học phần</label>
                <input type="text" name="search" class="form-control" placeholder="Mã, tên môn học hoặc niên khóa..." value="<?= e($search) ?>">
              </div>
              <div class="form-group" style="margin:0;min-width:130px">
                <label style="font-size:12px">Học kỳ</label>
                <select name="hoc_ky" class="form-control">
                  <option value="0">Tất cả</option>
                  <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?= $i ?>" <?= $hoc_ky === $i ? 'selected' : '' ?>>Học kỳ <?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="form-group" style="margin:0;min-width:150px">
                <label style="font-size:12px">Loại môn học</label>
                <select name="loai" class="form-control">
                  <option value="">Tất cả</option>
                  <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $l): ?>
                    <option value="<?= e($l) ?>" <?= $loai === $l ? 'selected' : '' ?>><?= e($l) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
              <a href="hoc_tap.php" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
            </form>
          </div>
        </div>

        <!-- Bảng danh sách học phần -->
        <div class="card fade-in">
          <div class="card-body" style="padding:0">
            <?php if (empty($list_hp)): ?>
              <div style="padding:40px;text-align:center;color:#777">
                <i class="fas fa-book-open" style="font-size:42px;margin-bottom:12px;display:block"></i>
                Không tìm thấy học phần nào phù hợp.
              </div>
            <?php else: ?>
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Mã học phần</th>
                      <th>Tên học phần</th>
                      <th style="text-align:center">Tín chỉ</th>
                      <th>Loại</th>
                      <th style="text-align:center">Học kỳ</th>
                      <th style="text-align:center">Số SV Đăng ký</th>
                      <th style="text-align:center">Trạng thái điểm</th>
                      <th style="text-align:center">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($list_hp as $hp): ?>
                      <tr>
                        <td><code><?= e($hp['ma_hp']) ?></code></td>
                        <td style="font-weight: 500"><?= e($hp['ten_hp']) ?></td>
                        <td style="text-align:center"><?= (int)$hp['so_tin_chi'] ?></td>
                        <td>
                          <span class="badge badge-<?= $hp['loai']==='Bắt buộc'?'danger':($hp['loai']==='Tự chọn'?'warning':'info') ?>">
                            <?= e($hp['loai']) ?>
                          </span>
                        </td>
                        <td style="text-align:center">HK <?= (int)$hp['hoc_ky'] ?></td>
                        <td style="text-align:center; font-weight: bold; color: var(--primary)">
                          <?= (int)$hp['si_so_dk'] ?>
                        </td>
                        <td style="text-align:center">
                          <?php if ($hp['si_so_dk'] == 0): ?>
                            <span class="badge badge-secondary" style="background:#bbb">Chưa có SV</span>
                          <?php elseif ($hp['so_sv_co_diem'] == 0): ?>
                            <span class="badge badge-warning" style="background:#ffc107;color:#333"><i class="fas fa-clock"></i> Chưa nhập điểm</span>
                          <?php elseif ($hp['so_sv_co_diem'] < $hp['si_so_dk']): ?>
                            <span class="badge badge-info" style="background:#17a2b8"><i class="fas fa-spinner"></i> Đang nhập (<?= $hp['so_sv_co_diem'] ?>/<?= $hp['si_so_dk'] ?>)</span>
                          <?php else: ?>
                            <span class="badge badge-success" style="background:#28a745"><i class="fas fa-check-double"></i> Đã nhập đủ</span>
                          <?php endif; ?>
                        </td>
                        <td style="text-align:center">
                          <a href="?action=edit&hoc_phan_id=<?= (int)$hp['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Nhập/Sửa điểm
                          </a>
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
    <?php
    require_once ROOT . '/includes/admin/footer_admin.php';
} 

// ── Màn hình 2: Giao diện nhập/sửa điểm chi tiết ───────────────
elseif ($action === 'edit' && $hoc_phan_id > 0) {
    // Lấy thông tin học phần
    $stmt = $db->prepare("SELECT * FROM hoc_phan WHERE id = ?");
    $stmt->bind_param('i', $hoc_phan_id);
    $stmt->execute();
    $hp = $stmt->get_result()->fetch_assoc();
    
    if (!$hp) {
        setFlash('danger', 'Không tìm thấy học phần yêu cầu.');
        header('Location: hoc_tap.php');
        exit;
    }
    
    // Lấy danh sách sinh viên đăng ký học phần này (trạng thái Đã duyệt)
    $stmt = $db->prepare("
        SELECT sv.id AS sinh_vien_id, sv.ma_sv, sv.ho_ten, sv.lop, dk.hoc_ky, dk.nam_hoc,
               d.diem_cc, d.diem_gk, d.diem_ck, d.diem_tong, d.diem_chu, d.diem_he4
        FROM dang_ky_hp dk
        JOIN sinh_vien sv ON sv.id = dk.sinh_vien_id
        LEFT JOIN diem_hoc_tap d ON d.sinh_vien_id = sv.id AND d.hoc_phan_id = dk.hoc_phan_id
        WHERE dk.hoc_phan_id = ? AND dk.trang_thai = 'Đã duyệt'
        ORDER BY sv.ma_sv ASC
    ");
    $stmt->bind_param('i', $hoc_phan_id);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $page_title = 'Nhập điểm học phần: ' . $hp['ten_hp'];
    require_once ROOT . '/includes/admin/header_admin.php';
    require_once ROOT . '/includes/admin/navbar_admin.php';
    ?>
    <div class="admin-wrapper">
      <div class="admin-container">
        
        <div class="page-title fade-in">
          <div class="breadcrumb">
            <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
            <span>›</span><a href="hoc_tap.php">Điểm học tập</a>
            <span>›</span><span>Chi tiết</span>
          </div>
          <h1><i class="fas fa-edit"></i> Nhập/Sửa điểm chi tiết</h1>
          <p>
            Học phần: <strong><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</strong> | 
            Số tín chỉ: <strong><?= (int)$hp['so_tin_chi'] ?></strong> | 
            Học kỳ: <strong>HK <?= (int)$hp['hoc_ky'] ?></strong>
          </p>
        </div>

        <?php if ($flash = getFlash()): ?>
          <div class="alert alert-<?= e($flash['type']) ?> fade-in">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <div><?= $flash['msg'] ?></div>
          </div>
        <?php endif; ?>

        <?php if (empty($students)): ?>
          <div class="card fade-in text-center" style="padding:40px">
            <div style="font-size:48px;margin-bottom:15px">⚠️</div>
            <h3 style="color:var(--text-muted)">Không có sinh viên trong học phần này</h3>
            <p class="text-muted">Chưa có sinh viên nào đăng ký và được duyệt học phần này trong hệ thống.</p>
            <div style="margin-top:20px">
              <a href="hoc_tap.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
            </div>
          </div>
        <?php else: ?>
          <div class="card fade-in">
            <div class="card-header" style="background:#fafafa">
              <h3><i class="fas fa-users"></i> Danh sách sinh viên (<?= count($students) ?>)</h3>
              <div style="display:flex; gap:10px">
                <a href="hoc_tap.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
                <button type="submit" form="gradesForm" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Lưu điểm</button>
              </div>
            </div>
            
            <div class="card-body" style="padding:0">
              <form id="gradesForm" method="POST">
                <input type="hidden" name="action" value="save_grades">
                <input type="hidden" name="hoc_phan_id" value="<?= $hoc_phan_id ?>">
                
                <div class="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>MSSV</th>
                        <th>Họ và tên</th>
                        <th>Lớp</th>
                        <th style="text-align:center; width:120px">Chuyên cần (10%)</th>
                        <th style="text-align:center; width:120px">Giữa kỳ (30%)</th>
                        <th style="text-align:center; width:120px">Cuối kỳ (60%)</th>
                        <th style="text-align:center; width:110px">Tổng kết</th>
                        <th style="text-align:center; width:100px">Hệ 4</th>
                        <th style="text-align:center; width:100px">Điểm chữ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $sv): ?>
                        <tr class="student-row" data-sv-id="<?= $sv['sinh_vien_id'] ?>">
                          <td><code><?= e($sv['ma_sv']) ?></code></td>
                          <td style="font-weight: 500"><?= e($sv['ho_ten']) ?></td>
                          <td><?= e($sv['lop'] ?? 'Chưa rõ') ?></td>
                          
                          <!-- Chuyên cần -->
                          <td style="text-align:center">
                            <input type="number" step="0.1" min="0" max="10" 
                                   name="diem[<?= $sv['sinh_vien_id'] ?>][cc]" 
                                   value="<?= is_null($sv['diem_cc']) ? '' : number_format((float)$sv['diem_cc'], 1) ?>" 
                                   class="form-control text-center grade-cc" 
                                   style="max-width:80px; margin:0 auto; padding:6px"
                                   placeholder="0 - 10">
                          </td>
                          
                          <!-- Giữa kỳ -->
                          <td style="text-align:center">
                            <input type="number" step="0.1" min="0" max="10" 
                                   name="diem[<?= $sv['sinh_vien_id'] ?>][gk]" 
                                   value="<?= is_null($sv['diem_gk']) ? '' : number_format((float)$sv['diem_gk'], 1) ?>" 
                                   class="form-control text-center grade-gk" 
                                   style="max-width:80px; margin:0 auto; padding:6px"
                                   placeholder="0 - 10">
                          </td>
                          
                          <!-- Cuối kỳ -->
                          <td style="text-align:center">
                            <input type="number" step="0.1" min="0" max="10" 
                                   name="diem[<?= $sv['sinh_vien_id'] ?>][ck]" 
                                   value="<?= is_null($sv['diem_ck']) ? '' : number_format((float)$sv['diem_ck'], 1) ?>" 
                                   class="form-control text-center grade-ck" 
                                   style="max-width:80px; margin:0 auto; padding:6px"
                                   placeholder="0 - 10">
                          </td>
                          
                          <!-- Điểm tổng kết (thang 10) -->
                          <td style="text-align:center; font-weight:700; font-size:15px; color:var(--primary)">
                            <span class="span-total"><?= is_null($sv['diem_tong']) ? '—' : number_format((float)$sv['diem_tong'], 1) ?></span>
                          </td>
                          
                          <!-- Điểm hệ 4 -->
                          <td style="text-align:center; font-weight:500; color:#555">
                            <span class="span-he4"><?= is_null($sv['diem_he4']) ? '—' : number_format((float)$sv['diem_he4'], 1) ?></span>
                          </td>
                          
                          <!-- Điểm chữ -->
                          <td style="text-align:center">
                            <?php if (!is_null($sv['diem_chu'])): ?>
                              <span class="badge badge-<?= badgeDiemChu($sv['diem_chu']) ?> badge-letter" style="font-size:12px; min-width:35px">
                                <?= e($sv['diem_chu']) ?>
                              </span>
                            <?php else: ?>
                              <span class="badge badge-secondary badge-letter" style="font-size:12px; min-width:35px; background:#aaa">—</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:15px; padding:20px; background:#fcfcfc; border-top:1px solid #eee">
                  <a href="hoc_tap.php" class="btn btn-secondary"><i class="fas fa-times"></i> Hủy / Quay lại</a>
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu bảng điểm</button>
                </div>
              </form>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Script tính điểm động bằng Javascript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('.student-row');
        
        function getDiemChu(score) {
            if (score >= 9.0) return { letter: 'A+', type: 'success' };
            if (score >= 8.5) return { letter: 'A', type: 'success' };
            if (score >= 8.0) return { letter: 'B+', type: 'primary' };
            if (score >= 7.0) return { letter: 'B', type: 'primary' };
            if (score >= 6.5) return { letter: 'C+', type: 'warning' };
            if (score >= 5.5) return { letter: 'C', type: 'warning' };
            if (score >= 5.0) return { letter: 'D+', type: 'secondary' };
            if (score >= 4.0) return { letter: 'D', type: 'secondary' };
            return { letter: 'F', type: 'danger' };
        }
        
        function getDiemHe4(score) {
            if (score >= 9.0) return 4.0;
            if (score >= 8.5) return 3.7;
            if (score >= 8.0) return 3.5;
            if (score >= 7.0) return 3.0;
            if (score >= 6.5) return 2.5;
            if (score >= 5.5) return 2.0;
            if (score >= 5.0) return 1.5;
            if (score >= 4.0) return 1.0;
            return 0.0;
        }

        rows.forEach(row => {
            const ccInput = row.querySelector('.grade-cc');
            const gkInput = row.querySelector('.grade-gk');
            const ckInput = row.querySelector('.grade-ck');
            const totalSpan = row.querySelector('.span-total');
            const he4Span = row.querySelector('.span-he4');
            const letterBadge = row.querySelector('.badge-letter');
            
            function calculateRow() {
                const ccVal = ccInput.value.trim();
                const gkVal = gkInput.value.trim();
                const ckVal = ckInput.value.trim();
                
                if (ccVal === '' || gkVal === '' || ckVal === '') {
                    totalSpan.textContent = '—';
                    he4Span.textContent = '—';
                    letterBadge.textContent = '—';
                    letterBadge.className = 'badge badge-secondary badge-letter';
                    letterBadge.style.backgroundColor = '#aaa';
                    return;
                }
                
                const cc = parseFloat(ccVal);
                const gk = parseFloat(gkVal);
                const ck = parseFloat(ckVal);
                
                if (isNaN(cc) || cc < 0 || cc > 10 || 
                    isNaN(gk) || gk < 0 || gk > 10 || 
                    isNaN(ck) || ck < 0 || ck > 10) {
                    totalSpan.textContent = 'Lỗi';
                    he4Span.textContent = 'Lỗi';
                    letterBadge.textContent = 'ERR';
                    letterBadge.className = 'badge badge-danger badge-letter';
                    letterBadge.style.backgroundColor = '';
                    return;
                }
                
                const total = Math.round((cc * 0.1 + gk * 0.3 + ck * 0.6) * 100) / 100;
                totalSpan.textContent = total.toFixed(1);
                
                const he4 = getDiemHe4(total);
                he4Span.textContent = he4.toFixed(1);
                
                const chu = getDiemChu(total);
                letterBadge.textContent = chu.letter;
                letterBadge.className = `badge badge-${chu.type} badge-letter`;
                letterBadge.style.backgroundColor = '';
            }
            
            [ccInput, gkInput, ckInput].forEach(input => {
                input.addEventListener('input', calculateRow);
            });
        });
    });
    </script>
    <?php
    require_once ROOT . '/includes/admin/footer_admin.php';
}
?>
