<?php
/**
 * admin/diem/bao_cao.php - UC31: Xem báo cáo điểm cho Admin
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();
$db = getDB();

$ma_sv = isset($_GET['ma_sv']) ? trim($_GET['ma_sv']) : '';
$student = null;
$diem_list = [];
$error_msg = '';
$info_msg = '';

// Nếu người dùng nhấn nút xem báo cáo hoặc nhập MSSV
if (isset($_GET['action']) && $_GET['action'] === 'view') {
    if ($ma_sv === '') {
        $error_msg = 'Vui lòng nhập thông tin tìm kiếm (Mã sinh viên).';
    } else {
        // Tìm sinh viên
        $stmt = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = ?");
        $stmt->bind_param('s', $ma_sv);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        
        if (!$student) {
            $error_msg = 'Không tìm thấy sinh viên.';
        } else {
            $sid = (int)$student['id'];
            
            // Lấy danh sách điểm
            $query = "
                SELECT d.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.loai
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = ?
                ORDER BY d.nam_hoc ASC, d.hoc_ky ASC, hp.ten_hp ASC
            ";
            $stmt = $db->prepare($query);
            $stmt->bind_param('i', $sid);
            $stmt->execute();
            $diem_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            if (empty($diem_list)) {
                $info_msg = 'Chưa có dữ liệu điểm cho sinh viên này.';
            }
        }
    }
}

// ── Xử lý Xuất Excel (CSV UTF-8 BOM) ─────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'export' && $ma_sv !== '') {
    // Tìm sinh viên
    $stmt = $db->prepare("SELECT * FROM sinh_vien WHERE ma_sv = ?");
    $stmt->bind_param('s', $ma_sv);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    if ($student) {
        $sid = (int)$student['id'];
        
        // Lấy điểm
        $query = "
            SELECT d.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.loai
            FROM diem_hoc_tap d
            JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
            WHERE d.sinh_vien_id = ?
            ORDER BY d.nam_hoc ASC, d.hoc_ky ASC, hp.ten_hp ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $sid);
        $stmt->execute();
        $diem_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Tính toán số liệu thống kê
        $sum_tc = 0; $sum_he4 = 0; $tc_tich_luy = 0; $so_mon_F = 0;
        foreach ($diem_list as $d) {
            if (!is_null($d['diem_he4'])) {
                $sum_tc += $d['so_tin_chi'];
                $sum_he4 += $d['diem_he4'] * $d['so_tin_chi'];
                if ($d['diem_he4'] >= 1.0) {
                    $tc_tich_luy += $d['so_tin_chi'];
                } else {
                    $so_mon_F++;
                }
            }
        }
        $cpa = $sum_tc > 0 ? round($sum_he4 / $sum_tc, 2) : 0;
        
        function xepLoaiCSV(float $cpa): string {
            if ($cpa >= 3.6) return 'Xuất sắc';
            if ($cpa >= 3.2) return 'Giỏi';
            if ($cpa >= 2.5) return 'Khá';
            if ($cpa >= 2.0) return 'Trung bình';
            return 'Yếu';
        }
        
        // Headers xuất file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bang_diem_' . $student['ma_sv'] . '.csv"');
        
        // Thêm UTF-8 BOM để Excel hiển thị đúng dấu Tiếng Việt
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Sử dụng ký tự tab làm dấu ngăn cách (Tab-separated values) để Excel mở trực tiếp không lỗi cột
        fputcsv($output, ['BÁO CÁO KẾT QUẢ HỌC TẬP SINH VIÊN'], "\t");
        fputcsv($output, [], "\t");
        fputcsv($output, ['THÔNG TIN SINH VIÊN'], "\t");
        fputcsv($output, ['Mã sinh viên:', $student['ma_sv']], "\t");
        fputcsv($output, ['Họ và tên:', $student['ho_ten']], "\t");
        fputcsv($output, ['Ngày sinh:', date('d/m/Y', strtotime($student['ngay_sinh']))], "\t");
        fputcsv($output, ['Lớp học:', $student['lop']], "\t");
        fputcsv($output, ['Ngành:', $student['nganh']], "\t");
        fputcsv($output, ['Khoa:', $student['khoa']], "\t");
        fputcsv($output, ['Niên khóa:', $student['nien_khoa']], "\t");
        fputcsv($output, [], "\t");
        
        fputcsv($output, ['TỔNG HỢP TOÀN KHÓA'], "\t");
        fputcsv($output, ['CPA (Hệ 4):', number_format($cpa, 2)], "\t");
        fputcsv($output, ['Tín chỉ tích lũy:', $tc_tich_luy], "\t");
        fputcsv($output, ['Số học phần tích lũy:', count($diem_list)], "\t");
        fputcsv($output, ['Số học phần chưa đạt (F):', $so_mon_F], "\t");
        fputcsv($output, ['Xếp loại học lực:', xepLoaiCSV($cpa)], "\t");
        fputcsv($output, [], "\t");
        
        fputcsv($output, ['CHI TIẾT BẢNG ĐIỂM'], "\t");
        fputcsv($output, ['Mã HP', 'Tên học phần', 'Số TC', 'Kỳ học', 'Năm học', 'Điểm CC (10%)', 'Điểm GK (30%)', 'Điểm CK (60%)', 'Điểm tổng kết', 'Hệ 4', 'Điểm chữ'], "\t");
        
        foreach ($diem_list as $d) {
            fputcsv($output, [
                $d['ma_hp'],
                $d['ten_hp'],
                $d['so_tin_chi'],
                'HK' . $d['hoc_ky'],
                $d['nam_hoc'],
                is_null($d['diem_cc']) ? '—' : $d['diem_cc'],
                is_null($d['diem_gk']) ? '—' : $d['diem_gk'],
                is_null($d['diem_ck']) ? '—' : $d['diem_ck'],
                is_null($d['diem_tong']) ? '—' : $d['diem_tong'],
                is_null($d['diem_he4']) ? '—' : $d['diem_he4'],
                is_null($d['diem_chu']) ? '—' : $d['diem_chu']
            ], "\t");
        }
        
        fclose($output);
        exit;
    }
}

// Thống kê tổng hợp toàn khóa của sinh viên
$cpa = 0.0;
$tc_tich_luy = 0;
$so_mon = 0;
$so_mon_F = 0;
$by_nh_hk = [];

if ($student && !empty($diem_list)) {
    $sum_tc = 0;
    $sum_he4 = 0;
    foreach ($diem_list as $d) {
        $by_nh_hk[$d['nam_hoc']][$d['hoc_ky']][] = $d;
        
        if (!is_null($d['diem_he4'])) {
            $sum_tc += $d['so_tin_chi'];
            $sum_he4 += $d['diem_he4'] * $d['so_tin_chi'];
            $so_mon++;
            
            if ($d['diem_he4'] >= 1.0) {
                $tc_tich_luy += $d['so_tin_chi'];
            } else {
                $so_mon_F++;
            }
        }
    }
    $cpa = $sum_tc > 0 ? round($sum_he4 / $sum_tc, 2) : 0;
}

function xepLoaiHocLuc(float $cpa): string {
    if ($cpa >= 3.6) return 'Xuất sắc';
    if ($cpa >= 3.2) return 'Giỏi';
    if ($cpa >= 2.5) return 'Khá';
    if ($cpa >= 2.0) return 'Trung bình';
    return 'Yếu';
}

function colorCPA(float $cpa): string {
    if ($cpa >= 3.2) return 'var(--success)';
    if ($cpa >= 2.5) return 'var(--primary)';
    if ($cpa >= 2.0) return '#fd7e14';
    return 'var(--danger)';
}

$page_title = 'Báo cáo điểm sinh viên';
require_once ROOT . '/includes/admin/header_admin.php';
require_once ROOT . '/includes/admin/navbar_admin.php';
?>

<!-- Style dành riêng cho In ấn Bảng điểm chính thức -->
<style>
@media print {
  /* Ẩn các thành phần giao diện không cần thiết khi in */
  .admin-navbar, .breadcrumb, .action-bar, .btn, .card-header, footer, .search-container {
    display: none !important;
  }
  .admin-wrapper {
    padding-top: 0 !important;
    background: none !important;
  }
  .admin-container {
    max-width: 100% !important;
    padding: 0 !important;
  }
  .card {
    box-shadow: none !important;
    border: none !important;
    margin-bottom: 15px !important;
    background: white !important;
  }
  .card-body {
    padding: 0 !important;
  }
  body {
    background: white !important;
    color: black !important;
    font-family: "Times New Roman", Times, serif !important;
  }
  .table-wrap table {
    border-collapse: collapse !important;
    width: 100% !important;
  }
  .table-wrap th, .table-wrap td {
    border: 1px solid #333 !important;
    color: black !important;
    padding: 6px 8px !important;
    font-size: 13px !important;
  }
  .table-wrap th {
    background: #eaeaea !important;
  }
  .diem-summary {
    border: 1px solid #333 !important;
    border-radius: 0 !important;
    margin-bottom: 20px !important;
    grid-template-columns: repeat(5, 1fr) !important;
  }
  .diem-summary-item {
    border-left: 1px solid #333 !important;
    padding: 10px !important;
  }
  .diem-summary-item:first-child {
    border-left: none !important;
  }
  .ds-value {
    color: black !important;
    font-size: 20px !important;
  }
  .print-header {
    display: block !important;
    text-align: center;
    margin-bottom: 30px;
  }
  .print-title {
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    margin-top: 10px;
  }
  .print-footer-signature {
    display: flex !important;
    justify-content: space-between;
    margin-top: 40px;
    padding: 0 40px;
  }
  .signature-box {
    text-align: center;
    width: 200px;
  }
}

/* Đóng khung in mặc định */
.print-header {
  display: none;
}
.print-footer-signature {
  display: none;
}

/* Layout tóm tắt điểm */
.diem-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}
.diem-summary-item {
  background: #fdfdfd;
  border: 1px solid #eee;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.ds-value {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 5px;
}
.ds-label {
  font-size: 12px;
  color: var(--text-muted);
}
</style>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Tiêu đề trang (Web) -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Xem báo cáo điểm</span>
      </div>
      <h1><i class="fas fa-chart-bar"></i> Báo cáo kết quả học tập</h1>
      <p>Xem bảng điểm chi tiết, tính toán GPA/CPA toàn khóa của sinh viên và xuất tệp tin dữ liệu.</p>
    </div>

    <!-- Header dành riêng cho in ấn (Mặc định ẩn trên web) -->
    <div class="print-header">
      <div style="font-size: 14px; font-weight: bold;">TRƯỜNG ĐẠI HỌC QUY NHƠN</div>
      <div style="font-size: 13px; text-decoration: underline;">PHÒNG ĐÀO TẠO ĐẠI HỌC</div>
      <div class="print-title">BẢNG ĐIỂM KẾT QUẢ HỌC TẬP</div>
      <div style="font-size: 12px; font-style: italic; margin-top: 5px;">Ngày in: <?= date('d/m/Y H:i') ?></div>
    </div>

    <?php if ($error_msg): ?>
      <div class="alert alert-danger fade-in search-container">
        <i class="fas fa-exclamation-circle"></i> <?= e($error_msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($info_msg): ?>
      <div class="alert alert-info fade-in search-container">
        <i class="fas fa-info-circle"></i> <?= e($info_msg) ?>
      </div>
    <?php endif; ?>

    <!-- Bộ lọc tìm kiếm sinh viên -->
    <div class="card fade-in search-container">
      <div class="card-body" style="padding:20px">
        <form method="GET" style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap">
          <input type="hidden" name="action" value="view">
          <div class="form-group" style="margin:0; flex:1; min-width:250px">
            <label style="font-weight: 500">Mã số sinh viên (MSSV) <span style="color:red">*</span></label>
            <input type="text" name="ma_sv" class="form-control" placeholder="Nhập mã sinh viên cần tra cứu (VD: 3121410001)..." value="<?= e($ma_sv) ?>" required>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-chart-line"></i> Xem báo cáo</button>
          <?php if ($student): ?>
            <a href="?action=export&ma_sv=<?= urlencode($ma_sv) ?>" class="btn btn-success"><i class="fas fa-file-excel"></i> Xuất Excel</a>
            <button type="button" class="btn btn-info" onclick="window.print()"><i class="fas fa-print"></i> In bảng điểm</button>
          <?php endif; ?>
          <a href="bao_cao.php" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Nhập lại</a>
        </form>
      </div>
    </div>

    <?php if ($student): ?>
      <!-- Thông tin sinh viên -->
      <div class="card fade-in" style="margin-bottom:20px">
        <div class="card-header">
          <h3><i class="fas fa-id-card"></i> Thông tin sinh viên</h3>
        </div>
        <div class="card-body" style="padding:20px">
          <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:15px">
            <div><strong>Họ và tên:</strong> <?= e($student['ho_ten']) ?></div>
            <div><strong>Mã sinh viên:</strong> <code><?= e($student['ma_sv']) ?></code></div>
            <div><strong>Lớp học:</strong> <?= e($student['lop'] ?? 'Chưa rõ') ?></div>
            <div><strong>Ngày sinh:</strong> <?= $student['ngay_sinh'] ? date('d/m/Y', strtotime($student['ngay_sinh'])) : 'Chưa rõ' ?></div>
            <div><strong>Giới tính:</strong> <?= e($student['gioi_tinh'] ?? 'Chưa rõ') ?></div>
            <div><strong>Ngành học:</strong> <?= e($student['nganh'] ?? 'Chưa rõ') ?></div>
            <div><strong>Khoa:</strong> <?= e($student['khoa'] ?? 'Chưa rõ') ?></div>
            <div><strong>Niên khóa:</strong> <?= e($student['nien_khoa'] ?? 'Chưa rõ') ?></div>
            <div><strong>Trạng thái:</strong> <span class="badge" style="background:#28a745; color:white"><?= e($student['trang_thai'] ?? 'Đang học') ?></span></div>
          </div>
        </div>
      </div>

      <?php if (!empty($diem_list)): ?>
        <!-- Tổng hợp CPA toàn khóa -->
        <div class="card fade-in" style="margin-bottom:20px">
          <div class="card-header">
            <h3><i class="fas fa-calculator"></i> Tóm tắt kết quả toàn khóa</h3>
          </div>
          <div class="card-body" style="padding:20px">
            <div class="diem-summary">
              <div class="diem-summary-item" style="border-top: 4px solid <?= colorCPA($cpa) ?>">
                <div class="ds-value" style="color:<?= colorCPA($cpa) ?>"><?= number_format($cpa, 2) ?></div>
                <div class="ds-label">CPA (Hệ 4)</div>
              </div>
              <div class="diem-summary-item" style="border-top: 4px solid var(--success)">
                <div class="ds-value" style="color:var(--success)"><?= $tc_tich_luy ?></div>
                <div class="ds-label">Tín chỉ tích lũy</div>
              </div>
              <div class="diem-summary-item" style="border-top: 4px solid var(--primary)">
                <div class="ds-value"><?= $so_mon ?></div>
                <div class="ds-label">Học phần đã có điểm</div>
              </div>
              <div class="diem-summary-item" style="border-top: 4px solid var(--danger)">
                <div class="ds-value" style="color:var(--danger)"><?= $so_mon_F ?></div>
                <div class="ds-label">Học phần chưa đạt (F)</div>
              </div>
              <div class="diem-summary-item" style="border-top: 4px solid var(--success)">
                <div class="ds-value" style="font-size:18px; color:var(--success); line-height:28px"><?= xepLoaiHocLuc($cpa) ?></div>
                <div class="ds-label">Xếp loại học lực</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chi tiết bảng điểm theo kỳ học -->
        <?php foreach ($by_nh_hk as $nh => $hk_groups): ?>
          <?php foreach ($hk_groups as $hk => $mons):
            // Tính toán GPA kỳ
            $sum_tc_hk = 0; $sum_diem_hk = 0;
            foreach ($mons as $m) {
                if (!is_null($m['diem_he4'])) {
                    $sum_tc_hk += $m['so_tin_chi'];
                    $sum_diem_hk += $m['diem_he4'] * $m['so_tin_chi'];
                }
            }
            $cpa_hk = $sum_tc_hk > 0 ? round($sum_diem_hk / $sum_tc_hk, 2) : 0;
            
            // Lấy điểm rèn luyện của học kỳ này
            $stmt = $db->prepare("SELECT diem, xep_loai FROM diem_ren_luyen WHERE sinh_vien_id = ? AND hoc_ky = ? AND nam_hoc = ? LIMIT 1");
            $stmt->bind_param('iis', $sid, $hk, $nh);
            $stmt->execute();
            $drl_info = $stmt->get_result()->fetch_assoc();
          ?>
            <div class="card fade-in" style="margin-bottom:16px">
              <div class="card-header" style="background:#fafafa">
                <h3><i class="fas fa-book-open"></i> Học kỳ <?= (int)$hk ?> — Năm học <?= e($nh) ?></h3>
                <div style="font-size:13px; color:var(--text-muted)">
                  GPA học kỳ: <strong style="color:<?= colorCPA($cpa_hk) ?>; font-size:14px"><?= number_format($cpa_hk, 2) ?></strong> | 
                  Điểm rèn luyện: <strong><?= $drl_info ? $drl_info['diem'] . " (".$drl_info['xep_loai'].")" : 'Chưa có' ?></strong>
                </div>
              </div>
              <div class="card-body" style="padding:0">
                <div class="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>Mã HP</th>
                        <th>Tên học phần</th>
                        <th style="text-align:center; width:70px">Tín chỉ</th>
                        <th style="text-align:center; width:90px">CC (10%)</th>
                        <th style="text-align:center; width:90px">GK (30%)</th>
                        <th style="text-align:center; width:90px">CK (60%)</th>
                        <th style="text-align:center; width:90px">Tổng kết</th>
                        <th style="text-align:center; width:80px">Hệ 4</th>
                        <th style="text-align:center; width:90px">Xếp loại</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($mons as $m): ?>
                        <tr>
                          <td><code><?= e($m['ma_hp']) ?></code></td>
                          <td style="font-weight: 500"><?= e($m['ten_hp']) ?></td>
                          <td style="text-align:center"><?= (int)$m['so_tin_chi'] ?></td>
                          <td style="text-align:center"><?= is_null($m['diem_cc']) ? '—' : number_format((float)$m['diem_cc'], 1) ?></td>
                          <td style="text-align:center"><?= is_null($m['diem_gk']) ? '—' : number_format((float)$m['diem_gk'], 1) ?></td>
                          <td style="text-align:center"><?= is_null($m['diem_ck']) ? '—' : number_format((float)$m['diem_ck'], 1) ?></td>
                          <td style="text-align:center; font-weight:700; color:<?= is_null($m['diem_tong']) ? 'var(--text-muted)' : ($m['diem_tong'] >= 4.0 ? 'var(--text)' : 'var(--danger)') ?>">
                            <?= is_null($m['diem_tong']) ? '—' : number_format((float)$m['diem_tong'], 1) ?>
                          </td>
                          <td style="text-align:center; font-weight:500">
                            <?= is_null($m['diem_he4']) ? '—' : number_format((float)$m['diem_he4'], 1) ?>
                          </td>
                          <td style="text-align:center">
                            <?php if (!is_null($m['diem_chu'])): ?>
                              <span class="badge badge-<?= badgeDiemChu($m['diem_chu']) ?>" style="font-size:12px; padding:3px 8px; min-width:30px">
                                <?= e($m['diem_chu']) ?>
                              </span>
                            <?php else: ?>
                              <span style="color:#bbb">—</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Chữ ký in ấn (Mặc định ẩn trên web) -->
      <div class="print-footer-signature">
        <div class="signature-box">
          <div style="font-size: 13px; font-weight: bold; margin-bottom: 60px;">NGƯỜI LẬP BẢNG</div>
          <div style="font-size: 13px; font-style: italic; color: #555;">(Ký và ghi rõ họ tên)</div>
        </div>
        <div class="signature-box">
          <div style="font-size: 13px; font-weight: bold;">TRƯỞNG PHÒNG ĐÀO TẠO</div>
          <div style="font-size: 11px; font-style: italic; margin-bottom: 60px;">(Ký, đóng dấu)</div>
          <div style="font-size: 13px; font-weight: bold; text-decoration: underline;">TS. BÙI VĂN AN</div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
</body>
</html>
