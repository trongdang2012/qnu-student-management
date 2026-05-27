<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <div class="page-title fade-in">
      <h1><i class="fas fa-bell"></i> Quản lý thông báo</h1>
      <p style="color:#666;margin:5px 0 0">Gửi thông báo định hướng riêng biệt và theo dõi lịch sử thông báo đến sinh viên</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success fade-in" data-auto-dismiss>
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger fade-in" data-auto-dismiss>
        <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <!-- Menu Tabs -->
    <div class="dk-tabs fade-in" style="margin-bottom: 20px; border-bottom: 2px solid var(--border);">
      <button class="dk-tab active" data-tab="tab-lich-su" id="btn-tab-lich-su" style="padding: 12px 20px; font-weight:600; font-size:14px; border:none; background:none; cursor:pointer; outline:none; transition: all 0.2s;">
        <i class="fas fa-history"></i> Lịch sử thông báo (<?= count($notifications) ?>)
      </button>
      <button class="dk-tab" data-tab="tab-canh-bao" id="btn-tab-canh-bao" style="padding: 12px 20px; font-weight:600; font-size:14px; border:none; background:none; cursor:pointer; outline:none; transition: all 0.2s;">
        <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Sinh viên bị cảnh báo (<?= count($warningStudents) ?>)
      </button>
    </div>

    <!-- Panel 1: Lịch sử thông báo -->
    <div class="dk-panel active fade-in" id="panel-lich-su">
      <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0"><i class="fas fa-list"></i> Danh sách thông báo</h3>
          <a href="<?= BASE_URL ?>/admin/thong-bao/tao-moi" class="btn btn-primary btn-sm">
            <i class="fas fa-paper-plane"></i> Gửi thông báo mới
          </a>
        </div>
        <div class="card-body">
          <?php if (empty($notifications)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
              <i class="fas fa-inbox" style="font-size: 40px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
              <p>Chưa có thông báo nào được gửi.</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table class="table" style="width:100%; border-collapse: collapse; text-align: left; font-size:14px;">
                <thead>
                  <tr style="border-bottom: 2px solid var(--primary);">
                    <th style="padding: 12px; font-weight:600; width: 60px;">ID</th>
                    <th style="padding: 12px; font-weight:600;">Tiêu đề</th>
                    <th style="padding: 12px; font-weight:600; width: 120px;">Loại</th>
                    <th style="padding: 12px; font-weight:600; width: 140px;">Người gửi</th>
                    <th style="padding: 12px; font-weight:600; width: 160px;">Ngày tạo</th>
                    <th style="padding: 12px; text-align: center; font-weight:600; width: 100px;">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($notifications as $n): ?>
                    <tr style="border-bottom: 1px solid var(--border);" class="hover-row">
                      <td style="padding: 12px; color:var(--text-muted); font-family:monospace;">#<?= $n['id'] ?></td>
                      <td style="padding: 12px;"><strong style="color:var(--text);"><?= htmlspecialchars($n['tieu_de']) ?></strong></td>
                      <td style="padding: 12px;">
                        <?php 
                          if ($n['loai'] === 'info') echo '<span class="badge" style="background:#17a2b8;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">Thông tin</span>';
                          elseif ($n['loai'] === 'warning') echo '<span class="badge" style="background:#ffc107;color:#000;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">Cảnh báo</span>';
                          elseif ($n['loai'] === 'success') echo '<span class="badge" style="background:#28a745;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">Thành công</span>';
                        ?>
                      </td>
                      <td style="padding: 12px;"><?= htmlspecialchars($n['nguoi_gui_ten'] ?? 'Hệ thống') ?></td>
                      <td style="padding: 12px; color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($n['ngay_tao'])) ?></td>
                      <td style="padding: 12px; text-align: center;">
                        <a href="<?= BASE_URL ?>/admin/thong-bao/delete?id=<?= $n['id'] ?>" class="btn btn-sm btn-danger" style="padding:5px 10px;" onclick="return confirm('Bạn có chắc muốn xóa thông báo này? Khỏi hệ thống của cả sinh viên.')">
                          <i class="fas fa-trash"></i> Xóa
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

    <!-- Panel 2: Sinh viên bị cảnh báo (Đa Phân Loại) -->
    <div class="dk-panel fade-in" id="panel-canh-bao" style="display: none;">
      
      <!-- Sub-tabs chuyển đổi cảnh báo con -->
      <div class="sub-tabs-container" style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid var(--border); padding-bottom: 0;">
        <button class="sub-tab active" data-subtab="subtab-hoc-tap" style="padding: 10px 18px; font-weight: 600; font-size: 13.5px; border: 1px solid var(--border); border-bottom: none; background: #f8f9fa; cursor: pointer; border-radius: 6px 6px 0 0; transition: all 0.2s; display: flex; align-items: center; gap: 6px; position: relative; bottom: -2px;">
          <i class="fas fa-graduation-cap"></i> Cảnh báo học tập (<?= count($warningStudents) ?>)
        </button>
        <button class="sub-tab" data-subtab="subtab-hoc-phi" style="padding: 10px 18px; font-weight: 600; font-size: 13.5px; border: 1px solid var(--border); border-bottom: none; background: #f8f9fa; cursor: pointer; border-radius: 6px 6px 0 0; transition: all 0.2s; display: flex; align-items: center; gap: 6px; position: relative; bottom: -2px;">
          <i class="fas fa-money-bill-wave"></i> Nợ học phí (<?= count($tuitionWarnings) ?>)
        </button>
        <button class="sub-tab" data-subtab="subtab-ren-luyen" style="padding: 10px 18px; font-weight: 600; font-size: 13.5px; border: 1px solid var(--border); border-bottom: none; background: #f8f9fa; cursor: pointer; border-radius: 6px 6px 0 0; transition: all 0.2s; display: flex; align-items: center; gap: 6px; position: relative; bottom: -2px;">
          <i class="fas fa-star"></i> Rèn luyện yếu (<?= count($drlWarnings) ?>)
        </button>
      </div>

      <!-- Sub-panel 1: Cảnh báo học tập -->
      <div class="sub-panel card" id="subtab-hoc-tap">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0; color:#dc3545;"><i class="fas fa-exclamation-triangle"></i> Danh sách sinh viên bị cảnh báo học tập</h3>
          <?php if (!empty($warningStudents)): ?>
            <button type="button" class="btn btn-danger btn-sm" onclick="openQuickNotificationModal('canh_bao', '', 'Nhóm sinh viên bị cảnh báo', 'academic')">
              <i class="fas fa-bullhorn"></i> Gửi thông báo toàn bộ (<?= count($warningStudents) ?> em)
            </button>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php if (empty($warningStudents)): ?>
            <div style="padding: 40px; text-align: center; color: #28a745;">
              <i class="fas fa-check-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
              <p><strong>Tuyệt vời!</strong> Hiện tại không có sinh viên nào bị cảnh báo học tập (điểm F < 4.0).</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table class="table" style="width:100%; border-collapse: collapse; text-align: left; font-size:14px;">
                <thead>
                  <tr style="border-bottom: 2px solid #dc3545;">
                    <th style="padding: 12px; font-weight:600; width: 120px;">Mã SV</th>
                    <th style="padding: 12px; font-weight:600;">Họ tên</th>
                    <th style="padding: 12px; font-weight:600; width: 110px;">Lớp</th>
                    <th style="padding: 12px; font-weight:600;">Ngành</th>
                    <th style="padding: 12px; font-weight:600; width: 150px; text-align: center;">Số môn điểm F</th>
                    <th style="padding: 12px; text-align: center; font-weight:600; width: 160px;">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($warningStudents as $ws): ?>
                    <tr style="border-bottom: 1px solid var(--border);" class="hover-row">
                      <td style="padding: 12px; font-weight:600;">
                        <span style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 4px; font-family: monospace;">
                          <?= e($ws['ma_sv']) ?>
                        </span>
                      </td>
                      <td style="padding: 12px;"><strong><?= e($ws['ho_ten']) ?></strong></td>
                      <td style="padding: 12px;"><code><?= e($ws['lop']) ?></code></td>
                      <td style="padding: 12px;"><?= e($ws['nganh']) ?></td>
                      <td style="padding: 12px; text-align: center;">
                        <span class="badge" style="background:#dc3545; color:#fff; padding:4px 10px; border-radius:10px; font-weight:700; font-size:12px;">
                          <?= (int)$ws['so_mon_f'] ?> môn
                        </span>
                      </td>
                      <td style="padding: 12px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <?php if ((int)$ws['so_lan_gui'] > 0): ?>
                          <span class="badge-sent" title="Đã gửi thông báo học tập"><i class="fas fa-check-double"></i> Đã gửi (<?= (int)$ws['so_lan_gui'] ?>)</span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline btn-sm" style="border: 1px solid #dc3545; color: #dc3545; padding: 5px 10px; font-size: 12.5px; cursor:pointer; background:transparent; border-radius:4px; transition: all 0.2s;"
                                onclick="openQuickNotificationModal('sinh_vien', '<?= e($ws['ma_sv']) ?>', 'Sinh viên: <?= e($ws['ho_ten']) ?> (<?= e($ws['ma_sv']) ?>)', 'academic')">
                          <i class="fas fa-paper-plane"></i> Gửi riêng
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sub-panel 2: Cảnh báo nợ học phí -->
      <div class="sub-panel card" id="subtab-hoc-phi" style="display: none;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0; color:#e08e00;"><i class="fas fa-money-bill-wave"></i> Danh sách sinh viên đang nợ học phí</h3>
          <?php if (!empty($tuitionWarnings)): ?>
            <button type="button" class="btn btn-warning btn-sm" style="color:#000;" onclick="openQuickNotificationModal('no_hoc_phi', '', 'Nhóm sinh viên nợ học phí', 'tuition')">
              <i class="fas fa-bullhorn"></i> Gửi thông báo toàn bộ (<?= count($tuitionWarnings) ?> em)
            </button>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php if (empty($tuitionWarnings)): ?>
            <div style="padding: 40px; text-align: center; color: #28a745;">
              <i class="fas fa-check-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
              <p><strong>Tuyệt vời!</strong> Không có sinh viên nào nợ học phí ở học kỳ hiện tại.</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table class="table" style="width:100%; border-collapse: collapse; text-align: left; font-size:14px;">
                <thead>
                  <tr style="border-bottom: 2px solid #e08e00;">
                    <th style="padding: 12px; font-weight:600; width: 120px;">Mã SV</th>
                    <th style="padding: 12px; font-weight:600;">Họ tên</th>
                    <th style="padding: 12px; font-weight:600; width: 110px;">Lớp</th>
                    <th style="padding: 12px; font-weight:600;">Ngành</th>
                    <th style="padding: 12px; font-weight:600; width: 160px; text-align: right;">Học phí còn nợ</th>
                    <th style="padding: 12px; text-align: center; font-weight:600; width: 160px;">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tuitionWarnings as $tw): ?>
                    <tr style="border-bottom: 1px solid var(--border);" class="hover-row">
                      <td style="padding: 12px; font-weight:600;">
                        <span style="background: #e2f0d9; color: #385723; padding: 4px 10px; border-radius: 4px; font-family: monospace;">
                          <?= e($tw['ma_sv']) ?>
                        </span>
                      </td>
                      <td style="padding: 12px;"><strong><?= e($tw['ho_ten']) ?></strong></td>
                      <td style="padding: 12px;"><code><?= e($tw['lop']) ?></code></td>
                      <td style="padding: 12px;"><?= e($tw['nganh']) ?></td>
                      <td style="padding: 12px; text-align: right; font-weight: 700; color: #c00000;">
                        <?= formatMoney($tw['tong_no']) ?>
                      </td>
                      <td style="padding: 12px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <?php if ((int)$tw['so_lan_gui'] > 0): ?>
                          <span class="badge-sent warning" title="Đã gửi thông báo nhắc học phí"><i class="fas fa-check-double"></i> Đã gửi (<?= (int)$tw['so_lan_gui'] ?>)</span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline btn-sm btn-sub-warning" style="border: 1px solid #e08e00; color: #e08e00; padding: 5px 10px; font-size: 12.5px; cursor:pointer; background:transparent; border-radius:4px; transition: all 0.2s;"
                                onclick="openQuickNotificationModal('sinh_vien', '<?= e($tw['ma_sv']) ?>', 'Sinh viên: <?= e($tw['ho_ten']) ?> (<?= e($tw['ma_sv']) ?>)', 'tuition')">
                          <i class="fas fa-paper-plane"></i> Gửi riêng
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sub-panel 3: Cảnh báo điểm rèn luyện yếu -->
      <div class="sub-panel card" id="subtab-ren-luyen" style="display: none;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0; color:#8e44ad;"><i class="fas fa-star"></i> Danh sách sinh viên có điểm rèn luyện yếu</h3>
          <?php if (!empty($drlWarnings)): ?>
            <button type="button" class="btn btn-sm" style="background:#8e44ad; color:#fff;" onclick="openQuickNotificationModal('ren_luyen', '', 'Nhóm rèn luyện yếu', 'drl')">
              <i class="fas fa-bullhorn"></i> Gửi thông báo toàn bộ (<?= count($drlWarnings) ?> em)
            </button>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php if (empty($drlWarnings)): ?>
            <div style="padding: 40px; text-align: center; color: #28a745;">
              <i class="fas fa-check-circle" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
              <p><strong>Tuyệt vời!</strong> Tất cả sinh viên đều có điểm rèn luyện đạt chuẩn (>= 65 điểm).</p>
            </div>
          <?php else: ?>
            <div style="overflow-x: auto;">
              <table class="table" style="width:100%; border-collapse: collapse; text-align: left; font-size:14px;">
                <thead>
                  <tr style="border-bottom: 2px solid #8e44ad;">
                    <th style="padding: 12px; font-weight:600; width: 120px;">Mã SV</th>
                    <th style="padding: 12px; font-weight:600;">Họ tên</th>
                    <th style="padding: 12px; font-weight:600; width: 110px;">Lớp</th>
                    <th style="padding: 12px; font-weight:600; width: 100px; text-align: center;">Học kỳ</th>
                    <th style="padding: 12px; font-weight:600; width: 120px; text-align: center;">Điểm DRL</th>
                    <th style="padding: 12px; font-weight:600; width: 120px; text-align: center;">Xếp loại</th>
                    <th style="padding: 12px; text-align: center; font-weight:600; width: 160px;">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($drlWarnings as $dw): ?>
                    <tr style="border-bottom: 1px solid var(--border);" class="hover-row">
                      <td style="padding: 12px; font-weight:600;">
                        <span style="background: #f3e5f5; color: #6a1b9a; padding: 4px 10px; border-radius: 4px; font-family: monospace;">
                          <?= e($dw['ma_sv']) ?>
                        </span>
                      </td>
                      <td style="padding: 12px;"><strong><?= e($dw['ho_ten']) ?></strong></td>
                      <td style="padding: 12px;"><code><?= e($dw['lop']) ?></code></td>
                      <td style="padding: 12px; text-align: center;"><?= e($dw['hoc_ky']) ?> (<?= e($dw['nam_hoc']) ?>)</td>
                      <td style="padding: 12px; text-align: center; font-weight: 700; color: #8e44ad;">
                        <?= (int)$dw['diem'] ?>
                      </td>
                      <td style="padding: 12px; text-align: center;">
                        <span class="badge" style="background:#f39c12; color:#fff; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">
                          <?= e($dw['xep_loai']) ?>
                        </span>
                      </td>
                      <td style="padding: 12px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                        <?php if ((int)$dw['so_lan_gui'] > 0): ?>
                          <span class="badge-sent purple" title="Đã gửi thông báo rèn luyện"><i class="fas fa-check-double"></i> Đã gửi (<?= (int)$dw['so_lan_gui'] ?>)</span>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline btn-sm btn-sub-purple" style="border: 1px solid #8e44ad; color: #8e44ad; padding: 5px 10px; font-size: 12.5px; cursor:pointer; background:transparent; border-radius:4px; transition: all 0.2s;"
                                onclick="openQuickNotificationModal('sinh_vien', '<?= e($dw['ma_sv']) ?>', 'Sinh viên: <?= e($dw['ho_ten']) ?> (<?= e($dw['ma_sv']) ?>)', 'drl')">
                          <i class="fas fa-paper-plane"></i> Gửi riêng
                        </button>
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
</div>

<!-- ========================================================================= -->
<!-- MODAL SOẠN THÔNG BÁO NHANH -->
<!-- ========================================================================= -->
<div id="quickNotificationModal" class="modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div class="modal-card card fade-in" style="width: 550px; max-width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border-radius: var(--radius); overflow: hidden;">
    <div id="modal_header_container" class="card-header" style="background: #dc3545; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; transition: background 0.3s ease;">
      <h3 style="margin: 0; color: #fff; font-size: 16px;"><i class="fas fa-paper-plane"></i> Soạn thông báo cảnh báo nhanh</h3>
      <span style="cursor: pointer; font-size: 20px; font-weight: bold; line-height: 1;" onclick="closeQuickNotificationModal()">&times;</span>
    </div>
    <div class="card-body" style="padding: 20px;">
      <form id="quickNotificationForm" action="<?= BASE_URL ?>/admin/thong-bao/tao-moi" method="POST">
        <input type="hidden" name="loai" value="warning">
        <input type="hidden" id="modal_target_type" name="target_type" value="canh_bao">
        <input type="hidden" id="modal_target_value" name="target_value" value="">
        
        <!-- Đối tượng nhận -->
        <div class="form-group" style="margin-bottom: 15px;">
          <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 13.5px;">Đối tượng nhận:</label>
          <input type="text" id="modal_target_display" class="form-control" readonly 
                 style="background: #e9ecef; cursor: not-allowed; font-weight: 600; color: var(--primary);">
        </div>
        
        <!-- Tiêu đề -->
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="tieu_de" style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 13.5px;">Tiêu đề thông báo <span style="color:#dc3545;">*</span></label>
          <input type="text" id="modal_tieu_de" name="tieu_de" class="form-control" required placeholder="VD: Cảnh báo học tập học kỳ..."
                 value="Thông báo cảnh báo kết quả học tập học kỳ <?= HOC_KY_HIEN_TAI ?>">
        </div>
        
        <!-- Nội dung -->
        <div class="form-group" style="margin-bottom: 20px;">
          <label for="noi_dung" style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 13.5px;">Nội dung thông báo <span style="color:#dc3545;">*</span></label>
          <textarea id="modal_noi_dung" name="noi_dung" class="form-control" rows="6" required style="resize: vertical; font-family: var(--font-sans); line-height: 1.5; font-size: 13.5px;">Chào em,
Qua kiểm tra kết quả học tập học kỳ hiện tại, phòng Đào tạo nhận thấy kết quả của em chưa đạt yêu cầu (có môn học bị điểm F dưới 4.0).
Đề nghị em chủ động liên hệ với Cố vấn học tập và Giảng viên bộ môn để có phương án ôn tập, cải thiện kết quả học tập tốt hơn.
Trân trọng.</textarea>
        </div>
        
        <!-- Nút hành động -->
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn btn-secondary" onclick="closeQuickNotificationModal()" style="padding: 8px 20px; font-size: 13.5px;">Hủy bỏ</button>
          <button id="modal_submit_btn" type="submit" class="btn btn-danger" style="padding: 8px 25px; font-size: 13.5px; display: flex; align-items: center; gap: 6px; transition: background 0.3s ease;">
            <i class="fas fa-paper-plane"></i> Gửi ngay
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Logic chuyển Tabs chính
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.dk-tab');
    const panels = document.querySelectorAll('.dk-panel');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class
            tabs.forEach(t => {
                t.classList.remove('active');
                t.style.borderBottom = 'none';
                t.style.color = 'var(--text-muted)';
            });
            panels.forEach(p => {
                p.style.display = 'none';
                p.classList.remove('active');
            });
            
            // Add active class
            this.classList.add('active');
            this.style.borderBottom = '2px solid var(--primary)';
            this.style.color = 'var(--primary)';
            
            const target = this.dataset.tab;
            if (target === 'tab-lich-su') {
                const panel = document.getElementById('panel-lich-su');
                if (panel) {
                    panel.style.display = 'block';
                    panel.classList.add('active');
                }
            } else if (target === 'tab-canh-bao') {
                const panel = document.getElementById('panel-canh-bao');
                if (panel) {
                    panel.style.display = 'block';
                    panel.classList.add('active');
                }
            }
        });
        
        // Mặc định CSS lúc khởi tạo
        if (tab.classList.contains('active')) {
            tab.style.borderBottom = '2px solid var(--primary)';
            tab.style.color = 'var(--primary)';
        } else {
            tab.style.color = 'var(--text-muted)';
        }
    });

    // Logic chuyển Sub-tabs cảnh báo con
    const subtabs = document.querySelectorAll('.sub-tab');
    const subpanels = document.querySelectorAll('.sub-panel');
    
    subtabs.forEach(subtab => {
        subtab.addEventListener('click', function() {
            subtabs.forEach(s => {
                s.classList.remove('active');
                s.style.background = '#f8f9fa';
                s.style.color = 'var(--text-muted)';
                s.style.borderBottomColor = 'var(--border)';
            });
            subpanels.forEach(sp => {
                sp.style.display = 'none';
            });
            
            this.classList.add('active');
            this.style.background = '#fff';
            this.style.color = 'var(--primary)';
            this.style.borderBottomColor = 'transparent';
            
            const target = this.dataset.subtab;
            const panel = document.getElementById(target);
            if (panel) {
                panel.style.display = 'block';
            }
        });
        
        // Mặc định CSS lúc khởi tạo
        if (subtab.classList.contains('active')) {
            subtab.style.background = '#fff';
            subtab.style.color = 'var(--primary)';
            subtab.style.borderBottomColor = 'transparent';
        } else {
            subtab.style.color = 'var(--text-muted)';
        }
    });
});

// Modal gửi thông báo nhanh
function openQuickNotificationModal(targetType, targetValue, targetDisplay, warningType) {
    const modal = document.getElementById('quickNotificationModal');
    const inputType = document.getElementById('modal_target_type');
    const inputValue = document.getElementById('modal_target_value');
    const inputDisplay = document.getElementById('modal_target_display');
    const inputTieuDe = document.getElementById('modal_tieu_de');
    const inputNoiDung = document.getElementById('modal_noi_dung');
    
    const headerContainer = document.getElementById('modal_header_container');
    const submitBtn = document.getElementById('modal_submit_btn');
    
    if (modal && inputType && inputValue && inputDisplay) {
        inputType.value = targetType;
        inputValue.value = targetValue;
        inputDisplay.value = targetDisplay;
        
        // Cấu hình nội dung và giao diện theo loại cảnh báo (warningType)
        if (warningType === 'academic') {
            // Giao diện màu đỏ báo động học tập
            if (headerContainer) headerContainer.style.background = '#dc3545';
            if (submitBtn) {
                submitBtn.style.background = '#dc3545';
                submitBtn.className = 'btn btn-danger';
            }
            
            if (targetType === 'sinh_vien') {
                inputTieuDe.value = "Thông báo cảnh báo học tập cá nhân học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nQua kiểm tra kết quả học tập học kỳ hiện tại, phòng Đào tạo nhận thấy kết quả của em chưa đạt yêu cầu (có môn học bị điểm F dưới 4.0).\n\nĐề nghị em chủ động liên hệ với Cố vấn học tập và Giảng viên bộ môn để có phương án ôn tập, cải thiện kết quả học tập tốt hơn.\n\nTrân trọng.`;
            } else {
                inputTieuDe.value = "Thông báo cảnh báo kết quả học tập học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nQua kiểm tra kết quả học tập học kỳ hiện tại, phòng Đào tạo nhận thấy kết quả của em chưa đạt yêu cầu (có môn học bị điểm F dưới 4.0).\n\nĐề nghị em chủ động liên hệ với Cố vấn học tập và Giảng viên bộ môn để có phương án ôn tập, cải thiện kết quả học tập tốt hơn.\n\nTrân trọng.`;
            }
        } 
        else if (warningType === 'tuition') {
            // Giao diện màu cam ấm học phí
            if (headerContainer) headerContainer.style.background = '#e08e00';
            if (submitBtn) {
                submitBtn.style.background = '#e08e00';
                submitBtn.className = 'btn btn-warning';
                submitBtn.style.color = '#000';
            }
            
            if (targetType === 'sinh_vien') {
                inputTieuDe.value = "Thông báo nhắc nhở nộp học phí cá nhân học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nPhòng Kế hoạch - Tài chính thông báo hiện tại em vẫn còn nợ học phí chưa hoàn thành trong học kỳ này.\n\nĐề nghị em nhanh chóng kiểm tra và nộp học phí đầy đủ để đảm bảo quyền lợi học tập, đăng ký học phần cũng như tham gia thi học kỳ.\n\nTrân trọng.`;
            } else {
                inputTieuDe.value = "Thông báo nhắc nhở hoàn thành nghĩa vụ học phí học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nPhòng Kế hoạch - Tài chính thông báo hiện tại em vẫn còn nợ học phí chưa hoàn thành trong học kỳ này.\n\nĐề nghị em nhanh chóng kiểm tra và nộp học phí đầy đủ để đảm bảo quyền lợi học tập, đăng ký học phần cũng như tham gia thi học kỳ.\n\nTrân trọng.`;
            }
        }
        else if (warningType === 'drl') {
            // Giao diện màu tím rèn luyện
            if (headerContainer) headerContainer.style.background = '#8e44ad';
            if (submitBtn) {
                submitBtn.style.background = '#8e44ad';
                submitBtn.className = 'btn btn-primary';
                submitBtn.style.color = '#fff';
            }
            
            if (targetType === 'sinh_vien') {
                inputTieuDe.value = "Thông báo nhắc nhở điểm rèn luyện cá nhân học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nQua kiểm tra kết quả đánh giá điểm rèn luyện học kỳ vừa qua, phòng Công tác sinh viên nhận thấy điểm rèn luyện của em đang ở mức yếu (dưới 65 điểm).\n\nĐề nghị em tích cực tham gia các hoạt động Đoàn - Hội, các hoạt động ngoại khóa của Trường và Khoa phát động để nâng cao điểm rèn luyện trong các học kỳ tới.\n\nTrân trọng.`;
            } else {
                inputTieuDe.value = "Thông báo nhắc nhở nâng cao điểm rèn luyện học kỳ <?= HOC_KY_HIEN_TAI ?>";
                inputNoiDung.value = `Chào em,\n\nQua kiểm tra kết quả đánh giá điểm rèn luyện học kỳ vừa qua, phòng Công tác sinh viên nhận thấy điểm rèn luyện của em đang ở mức yếu (dưới 65 điểm).\n\nĐề nghị em tích cực tham gia các hoạt động Đoàn - Hội, các hoạt động ngoại khóa của Trường và Khoa phát động để nâng cao điểm rèn luyện trong các học kỳ tới.\n\nTrân trọng.`;
            }
        }
        
        modal.style.display = 'flex';
    }
}

function closeQuickNotificationModal() {
    const modal = document.getElementById('quickNotificationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.hover-row:hover {
    background: #f8f9fa;
}
.dk-tab:hover {
    color: var(--primary) !important;
    background: #f1f5fe !important;
    border-radius: var(--radius-sm) var(--radius-sm) 0 0;
}
.sub-tab:hover {
    background: #f1f3f5 !important;
    color: var(--primary) !important;
}
.sub-tab.active {
    background: #fff !important;
    color: var(--primary) !important;
    border-bottom: 2px solid transparent !important;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.03);
}
.form-control {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 14px;
}
.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-light);
}

.btn-outline:hover {
    background: #dc3545 !important;
    color: #fff !important;
}
.btn-sub-warning:hover {
    background: #e08e00 !important;
    color: #000 !important;
}
.btn-sub-purple:hover {
    background: #8e44ad !important;
    color: #fff !important;
}
.badge-sent {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.2);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-sent.warning {
    background: rgba(224, 142, 0, 0.1);
    color: #e08e00;
    border: 1px solid rgba(224, 142, 0, 0.2);
}
.badge-sent.purple {
    background: rgba(142, 68, 173, 0.1);
    color: #8e44ad;
    border: 1px solid rgba(142, 68, 173, 0.2);
}
</style>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
