<?php
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
?>
<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<style>
@media print {
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

.print-header {
  display: none;
}
.print-footer-signature {
  display: none;
}

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
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Xem báo cáo điểm</span>
      </div>
      <h1><i class="fas fa-chart-bar"></i> Báo cáo kết quả học tập</h1>
      <p>Xem bảng điểm chi tiết, tính toán GPA/CPA toàn khóa của sinh viên và xuất tệp tin dữ liệu.</p>
    </div>

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
          <a href="<?= BASE_URL ?>/admin/diem/bao-cao" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Nhập lại</a>
        </form>
      </div>
    </div>

    <?php if ($student): ?>
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

        <?php foreach ($by_nh_hk as $nh => $hk_groups): ?>
          <?php foreach ($hk_groups as $hk => $mons):
            $sum_tc_hk = 0; $sum_diem_hk = 0;
            foreach ($mons as $m) {
                if (!is_null($m['diem_he4'])) {
                    $sum_tc_hk += $m['so_tin_chi'];
                    $sum_diem_hk += $m['diem_he4'] * $m['so_tin_chi'];
                }
            }
            $cpa_hk = $sum_tc_hk > 0 ? round($sum_diem_hk / $sum_tc_hk, 2) : 0;
            
            $drl_info = $gradeModel->getTrainingGradeBySemester($student['id'], $hk, $nh);
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
