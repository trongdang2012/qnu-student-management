<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/diem/hoc-tap">Điểm học tập</a>
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
          <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
      </div>
    <?php else: ?>
      <div class="card fade-in">
        <div class="card-header" style="background:#fafafa; flex-wrap: wrap;">
          <h3><i class="fas fa-users"></i> Danh sách sinh viên (<?= count($students) ?>)</h3>
          
          <div style="display:flex; gap:10px; align-items:center; flex-wrap: wrap; margin-top: 5px;">
            <a href="<?= BASE_URL ?>/admin/diem/hoc-tap?khoa=<?= urlencode($khoa) ?>&nganh=<?= urlencode($nganh) ?>&lop=<?= urlencode($lop) ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <a href="<?= BASE_URL ?>/admin/diem/hoc-tap/export-template?hoc_phan_id=<?= $hoc_phan_id ?>&khoa=<?= urlencode($khoa) ?>&nganh=<?= urlencode($nganh) ?>&lop=<?= urlencode($lop) ?>" class="btn btn-info btn-sm" style="color:white; background:#17a2b8; border:none;">
                <i class="fas fa-download"></i> Tải template (Excel/CSV)
            </a>
            <form action="<?= BASE_URL ?>/admin/diem/hoc-tap/import" method="POST" enctype="multipart/form-data" style="display:inline-flex; align-items:center; gap:5px; margin:0;">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="hoc_phan_id" value="<?= $hoc_phan_id ?>">
                <input type="hidden" name="khoa" value="<?= e($khoa) ?>">
                <input type="hidden" name="nganh" value="<?= e($nganh) ?>">
                <input type="hidden" name="lop" value="<?= e($lop) ?>">
                <input type="file" name="excel_file" accept=".xlsx, .csv" required style="font-size:12px; max-width:200px; padding:3px;" class="form-control" title="Chọn file Excel hoặc CSV">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> Nhập/Đè điểm</button>
            </form>
          </div>
        </div>
        
        <div class="card-body" style="padding:0">
            <div style="padding:10px 20px; background:#e9ecef; font-size:13px; color:#555;">
                <i class="fas fa-info-circle"></i> <strong>Hướng dẫn:</strong> Vui lòng tải template, mở bằng Excel để nhập điểm, sau đó tải lên file định dạng <strong>.xlsx</strong> hoặc <strong>.csv</strong>. Việc tải lên sẽ <strong>ghi đè</strong> điểm cũ (nếu có).
            </div>
            
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>MSSV</th>
                    <th>Họ và tên</th>
                    <th>Lớp</th>
                    <th style="text-align:center; width:120px">CC (10%)</th>
                    <th style="text-align:center; width:120px">GK (30%)</th>
                    <th style="text-align:center; width:120px">CK (60%)</th>
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
                      
                      <td style="text-align:center">
                        <span style="font-weight:500; font-size:14px"><?= is_null($sv['diem_cc']) ? '—' : number_format((float)$sv['diem_cc'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center">
                        <span style="font-weight:500; font-size:14px"><?= is_null($sv['diem_gk']) ? '—' : number_format((float)$sv['diem_gk'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center">
                        <span style="font-weight:500; font-size:14px"><?= is_null($sv['diem_ck']) ? '—' : number_format((float)$sv['diem_ck'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center; font-weight:700; font-size:15px; color:var(--primary)">
                        <span class="span-total"><?= is_null($sv['diem_tong']) ? '—' : number_format((float)$sv['diem_tong'], 1) ?></span>
                      </td>
                      
                      <td style="text-align:center; font-weight:500; color:#555">
                        <span class="span-he4"><?= is_null($sv['diem_he4']) ? '—' : number_format((float)$sv['diem_he4'], 1) ?></span>
                      </td>
                      
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
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>



<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
