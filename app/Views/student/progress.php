<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Cá nhân</span>
        <span>›</span><span>Tiến độ học tập</span>
      </div>
      <h1><i class="fas fa-tasks"></i> Tiến độ học tập</h1>
      <p>Theo dõi tiến độ hoàn thành chương trình đào tạo của bạn.</p>
    </div>

    <?php if ($progressInfo['canh_bao_lien_tiep'] > 0): ?>
    <div class="alert <?= $progressInfo['canh_bao_lien_tiep'] >= 3 ? 'alert-danger' : 'alert-warning' ?> mb-20 fade-in" style="font-size: 16px; border-left: 5px solid <?= $progressInfo['canh_bao_lien_tiep'] >= 3 ? '#dc3545' : '#ffc107' ?>; background-color: <?= $progressInfo['canh_bao_lien_tiep'] >= 3 ? '#f8d7da' : '#fff3cd' ?>; color: <?= $progressInfo['canh_bao_lien_tiep'] >= 3 ? '#721c24' : '#856404' ?>;">
      <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-right: 10px; margin-top: 2px;"></i>
      <div>
        <strong>Cảnh báo học tập!</strong> Bạn đang bị cảnh báo học tập <strong><?= $progressInfo['canh_bao_lien_tiep'] ?> kỳ liên tiếp</strong> (Do điểm trung bình học kỳ < 4.0).
        <?php if ($progressInfo['canh_bao_lien_tiep'] >= 3): ?>
          <br>Lưu ý: Bị cảnh báo 3 kỳ liên tiếp sẽ dẫn đến <strong>BUỘC THÔI HỌC</strong> theo quy chế. Vui lòng liên hệ cố vấn học tập ngay!
        <?php else: ?>
          <br>Hãy chú ý đăng ký học lại và cải thiện kết quả học tập ở kỳ tiếp theo nhé!
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tổng quan -->
    <div class="stat-grid fade-in">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
        <div>
          <div class="stat-value"><?= $progressInfo['tc_total'] ?> <span style="font-size:14px;font-weight:400">TC</span></div>
          <div class="stat-label">Tổng tín chỉ toàn khóa</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
        <div>
          <div class="stat-value"><?= $progressInfo['tc_dat_total'] ?> <span style="font-size:14px;font-weight:400">TC</span></div>
          <div class="stat-label">Đã tích lũy</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div>
        <div>
          <div class="stat-value"><?= $progressInfo['tc_total'] - $progressInfo['tc_dat_total'] ?> <span style="font-size:14px;font-weight:400">TC</span></div>
          <div class="stat-label">Còn lại</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-chart-bar"></i></div>
        <div>
          <div class="stat-value"><?= number_format($progressInfo['cpa'], 2) ?></div>
          <div class="stat-label">CPA hiện tại</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green" style="background:#f0fdf4;color:#15803d;"><i class="fas fa-graduation-cap"></i></div>
        <div>
          <div class="stat-value"><?= number_format($progressInfo['gpa10'], 2) ?></div>
          <div class="stat-label">GPA tổng thể (hệ 10)</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red" style="background:#fef2f2;color:#ef4444;"><i class="fas fa-exclamation-circle"></i></div>
        <div>
          <div class="stat-value"><?= count($progressInfo['no_mon_list']) ?> <span style="font-size:14px;font-weight:400">môn (<?= $progressInfo['tong_tc_no'] ?> TC)</span></div>
          <div class="stat-label">Đang nợ</div>
        </div>
      </div>
    </div>

    <!-- Thanh tiến độ tổng -->
    <div class="card mb-20 fade-in">
      <div class="card-header">
        <h3><i class="fas fa-graduation-cap"></i> Tiến độ tốt nghiệp</h3>
        <span style="font-size:22px;font-weight:700;color:var(--primary)"><?= $progressInfo['pct_total'] ?>%</span>
      </div>
      <div class="card-body">
        <div class="progress" style="height:22px">
          <div class="progress-bar <?= $progressInfo['pct_total'] >= 80 ? 'green' : ($progressInfo['pct_total'] >= 50 ? '' : 'orange') ?>"
               style="width:0;font-size:13px;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:500"
               data-width="<?= $progressInfo['pct_total'] ?>">
            <?= $progressInfo['pct_total'] ?>%
          </div>
        </div>
        <p class="text-muted mt-8" style="font-size:14px;text-align:center">
          Bạn đã tích lũy được <strong><?= $progressInfo['tc_dat_total'] ?> / <?= $progressInfo['tc_total'] ?> tín chỉ</strong>
          — còn <strong><?= $progressInfo['tc_total'] - $progressInfo['tc_dat_total'] ?> tín chỉ</strong> nữa để tốt nghiệp.
        </p>
      </div>
    </div>

    <!-- Mục tiêu học tập & Định hướng tốt nghiệp -->
    <div class="card mb-20 fade-in" style="background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%); border-left: 5px solid var(--primary);">
      <div class="card-header" style="border-bottom: 1px solid rgba(0, 86, 179, 0.1);">
        <h3><i class="fas fa-bullseye" style="color: var(--primary);"></i> Mục tiêu học tập & Định hướng tốt nghiệp</h3>
        <button class="btn btn-sm btn-outline" onclick="openTargetModal()">
          <i class="fas fa-edit"></i> <?= $analysis ? 'Thay đổi mục tiêu' : 'Thiết lập mục tiêu' ?>
        </button>
      </div>
      <div class="card-body">
        <?php if (!$analysis): ?>
          <div style="text-align: center; padding: 20px 10px;">
            <i class="fas fa-route" style="font-size: 36px; color: #ccc; margin-bottom: 10px; display: block;"></i>
            <p style="margin: 0; font-size: 14.5px; color: var(--text-muted);">Bạn chưa thiết lập mục tiêu GPA sau khi tốt nghiệp. Hãy thiết lập mục tiêu để hệ thống tự động tính toán lộ trình học tập tối thiểu cho bạn!</p>
            <button class="btn btn-primary mt-12" onclick="openTargetModal()">
              <i class="fas fa-plus-circle"></i> Thiết lập mục tiêu ngay
            </button>
          </div>
        <?php else: ?>
          <?php
            $targetVal = (float)$analysis['target'];
            // Quy đổi sang thang 10
            $targetVal10 = round($targetVal * 2.5, 2);
            $classification = 'Trung bình';
            $classColor = 'secondary';
            if ($targetVal >= 3.6) {
                $classification = 'Xuất sắc';
                $classColor = 'danger';
            } elseif ($targetVal >= 3.2) {
                $classification = 'Giỏi';
                $classColor = 'success';
            } elseif ($targetVal >= 2.5) {
                $classification = 'Khá';
                $classColor = 'primary';
            } elseif ($targetVal >= 2.0) {
                $classification = 'Trung bình';
                $classColor = 'warning';
            }
          ?>
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
              <p style="margin: 0 0 6px; font-size: 14px; color: var(--text-muted);">Mục tiêu CPA sau khi tốt nghiệp:</p>
              <div style="display: flex; align-items: baseline; gap: 10px;">
                <span style="font-size: 28px; font-weight: 800; color: var(--primary); line-height: 1;"><?= number_format($targetVal, 2) ?></span>
                <span style="font-size: 14px; color: var(--text-muted);">(Tương đương hệ 10: <strong><?= number_format($targetVal10, 2) ?></strong>)</span>
                <span class="badge badge-<?= $classColor ?>" style="font-size: 12.5px; padding: 4px 12px; font-weight: 600;">Hạng tốt nghiệp: <?= $classification ?></span>
              </div>
            </div>
            <div>
              <button class="btn btn-primary" onclick="openAnalysisModal()" style="box-shadow: 0 4px 12px rgba(0, 86, 179, 0.15);">
                <i class="fas fa-magic"></i> Xem điều kiện & Gợi ý lộ trình
              </button>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($progressInfo['no_mon_list']) > 0): ?>
    <!-- Đề xuất môn học (Môn nợ) -->
    <div class="card mb-20 fade-in">
      <div class="card-header">
        <h3 style="color: #ef4444;"><i class="fas fa-lightbulb"></i> Đề xuất đăng ký học lại</h3>
      </div>
      <div class="card-body" style="padding:0">
        <div style="padding: 15px 20px; background: #fef2f2;">
            <p style="margin:0; color: #991b1b;">Bạn đang có <strong><?= count($progressInfo['no_mon_list']) ?></strong> học phần chưa đạt (điểm < 4.0). Hệ thống khuyến nghị ưu tiên đăng ký học lại các học phần này trong học kỳ tới:</p>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th style="text-align:center">Điểm rớt</th>
            </tr></thead>
            <tbody>
            <?php foreach ($progressInfo['no_mon_list'] as $m): ?>
              <tr>
                <td><code><?= e($m['ma_hp']) ?></code></td>
                <td><strong><?= e($m['ten_hp']) ?></strong></td>
                <td style="text-align:center"><?= (int)$m['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-<?= $m['loai']==='Bắt buộc' ? 'danger' : ($m['loai']==='Tự chọn' ? 'warning' : 'info') ?>">
                    <?= e($m['loai']) ?>
                  </span>
                </td>
                <td style="text-align:center;color:#ef4444;font-weight:bold;">
                  <?= number_format((float)$m['diem_tong'], 1) ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Tiến độ theo nhóm môn -->
    <div class="card mb-20 fade-in">
      <div class="card-header"><h3><i class="fas fa-th-list"></i> Tiến độ theo nhóm học phần</h3></div>
      <div class="card-body">
        <div class="credit-progress-grid">
          <?php
          $loai_list = ['Bắt buộc','Đại cương','Tự chọn'];
          $loai_colors = ['Bắt buộc'=>'','Đại cương'=>'green','Tự chọn'=>'orange'];
          foreach ($loai_list as $loai):
            $total = $progressInfo['tc_ctdt'][$loai] ?? 0;
            $done  = $progressInfo['tc_dat'][$loai]  ?? 0;
            $pct   = $total > 0 ? min(100, round($done / $total * 100)) : 0;
          ?>
          <div class="credit-item">
            <div class="credit-label">
              <span><strong><?= e($loai) ?></strong></span>
              <span class="credit-count"><?= $done ?> / <?= $total ?> TC</span>
            </div>
            <div class="progress">
              <div class="progress-bar <?= $loai_colors[$loai] ?>"
                   style="width:0" data-width="<?= $pct ?>"></div>
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;text-align:right"><?= $pct ?>% hoàn thành</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Bảng chi tiết theo từng học kỳ -->
    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-list-alt"></i> Chi tiết theo học kỳ</h3>
      </div>
      <div class="card-body" style="padding:0">
        <?php foreach ($progressInfo['hoc_ky_groups'] as $hk => $mons): ?>
          <?php
            $tc_hk = array_sum(array_column($mons, 'so_tin_chi'));
            $done_hk = 0;
            foreach ($mons as $m) {
                if (!is_null($m['diem_he4']) && $m['diem_he4'] >= 1.0) $done_hk += $m['so_tin_chi'];
            }
          ?>
          <div style="padding:14px 20px;background:#f7f9fc;border-bottom:2px solid var(--border);display:flex;align-items:center;justify-content:space-between">
            <h4 style="margin:0;color:var(--primary)"><i class="fas fa-book-open"></i> Học kỳ <?= (int)$hk ?></h4>
            <span class="badge badge-primary"><?= $done_hk ?> / <?= $tc_hk ?> TC hoàn thành</span>
          </div>
          <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th style="text-align:center">Điểm TK</th>
              <th style="text-align:center">Xếp loại</th>
              <th style="text-align:center">Trạng thái</th>
            </tr></thead>
            <tbody>
            <?php foreach ($mons as $m): ?>
              <?php
                $dat_mon = !is_null($m['diem_he4']) && $m['diem_he4'] >= 1.0;
                $chua_co = is_null($m['diem_tong']);
              ?>
              <tr>
                <td><code><?= e($m['ma_hp']) ?></code></td>
                <td><?= e($m['ten_hp']) ?></td>
                <td style="text-align:center"><?= (int)$m['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-<?= $m['loai']==='Bắt buộc' ? 'danger' : ($m['loai']==='Tự chọn' ? 'warning' : 'info') ?>">
                    <?= e($m['loai']) ?>
                  </span>
                </td>
                <td style="text-align:center;font-weight:700">
                  <?= $chua_co ? '<span style="color:var(--text-muted)">—</span>' : number_format((float)$m['diem_tong'], 1) ?>
                </td>
                <td style="text-align:center">
                  <?php if (!$chua_co): ?>
                    <span class="badge badge-<?= badgeDiemChu($m['diem_chu'] ?? 'F') ?>"><?= e($m['diem_chu'] ?? 'F') ?></span>
                  <?php else: ?>
                    <span style="color:var(--text-muted)">—</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <?php if ($chua_co): ?>
                    <span class="badge badge-secondary">Chưa có điểm</span>
                  <?php elseif ($dat_mon): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Đạt</span>
                  <?php else: ?>
                    <span class="badge badge-danger"><i class="fas fa-times"></i> Không đạt</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL THIẾT LẬP MỤC TIÊU GPA -->
<!-- ========================================================================= -->
<div id="targetModal" class="modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div class="modal-card card fade-in" style="width: 450px; max-width: 90%; box-shadow: var(--shadow-md); border-radius: 12px; overflow: hidden; background:#fff;">
    <div class="card-header" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-bottom: 1px solid var(--border);">
      <h3 style="margin: 0; font-size: 16px; color: var(--primary); font-weight:700;"><i class="fas fa-bullseye"></i> Thiết lập CPA mục tiêu</h3>
      <span style="cursor: pointer; font-size: 24px; font-weight: bold; line-height: 1; color:#888;" onclick="closeTargetModal()">&times;</span>
    </div>
    <form id="targetForm" style="margin: 0;">
      <div class="card-body" style="padding: 20px 24px;">
        <div class="form-group">
          <label for="gpa_muc_tieu">CPA Mục tiêu tốt nghiệp mong muốn (Hệ 4):</label>
          <input type="number" step="0.01" min="1.0" max="4.0" class="form-control" id="gpa_muc_tieu" name="gpa_muc_tieu" placeholder="VD: 3.20" value="<?= $analysis ? number_format($analysis['target'], 2) : '' ?>" required>
          <div class="form-hint" style="margin-top: 8px; font-size: 12.5px; line-height: 1.5;">
            <strong>Mốc xếp hạng tham khảo (QNU):</strong><br>
            • Xuất sắc: <code>&ge; 3.60</code> (Hệ 10: &ge; 9.0)<br>
            • Giỏi: <code>3.20 - 3.59</code> (Hệ 10: 8.0 - 8.9)<br>
            • Khá: <code>2.50 - 3.19</code> (Hệ 10: 7.0 - 7.9)<br>
            • Trung bình: <code>2.00 - 2.49</code> (Hệ 10: 5.5 - 6.9)
          </div>
        </div>
        <div id="targetMsg" class="alert alert-danger" style="display: none; padding: 10px; font-size: 13px; margin-bottom: 0; margin-top: 15px;"></div>
      </div>
      <div class="card-footer" style="text-align: right; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding: 14px 20px; background: #f8f9fa;">
        <button type="button" class="btn btn-secondary" onclick="closeTargetModal()">Hủy bỏ</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu mục tiêu</button>
      </div>
    </form>
  </div>
</div>

<?php if ($analysis): ?>
<!-- ========================================================================= -->
<!-- MODAL XEM PHÂN TÍCH LỘ TRÌNH TỐI THIỂU -->
<!-- ========================================================================= -->
<div id="analysisModal" class="modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div class="modal-card card fade-in" style="width: 720px; max-width: 95%; max-height: 90vh; box-shadow: var(--shadow-md); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; background:#fff;">
    <div class="card-header" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-bottom: 1px solid var(--border); flex-shrink: 0;">
      <h3 style="margin: 0; font-size: 16px; color: var(--primary); font-weight:700;"><i class="fas fa-magic"></i> Lộ trình học tập & Phân tích tối thiểu</h3>
      <span style="cursor: pointer; font-size: 24px; font-weight: bold; line-height: 1; color:#888;" onclick="closeAnalysisModal()">&times;</span>
    </div>
    <div class="card-body" style="padding: 24px; overflow-y: auto; flex: 1;">
      
      <!-- Box tóm tắt -->
      <div style="background: #e8f0fb; border-radius: var(--radius); padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        <div>
          <p style="margin: 0; font-size: 13.5px; color: var(--text-muted);">Mục tiêu CPA tốt nghiệp:</p>
          <strong style="font-size: 24px; color: var(--primary);"><?= number_format($analysis['target'], 2) ?></strong>
        </div>
        <div>
          <p style="margin: 0; font-size: 13.5px; color: var(--text-muted);">CPA hiện tại:</p>
          <strong style="font-size: 24px; color: #333;"><?= number_format($progressInfo['cpa'], 2) ?></strong>
        </div>
        <div>
          <p style="margin: 0; font-size: 13.5px; color: var(--text-muted);">Tín chỉ còn lại cần học:</p>
          <strong style="font-size: 24px; color: #333;"><?= $analysis['tc_remain'] ?> TC</strong>
        </div>
      </div>

      <!-- Phân tích chính -->
      <?php if ($analysis['status'] === 'achieved'): ?>
        <div class="alert alert-success" style="font-size: 14.5px; margin-bottom: 20px;">
          <i class="fas fa-check-circle" style="font-size: 22px;"></i>
          <div>
            <strong>Chúc mừng! Bạn đã chắc chắn đạt được mục tiêu tốt nghiệp đề ra.</strong><br>
            Hiện tại điểm CPA của bạn (<?= number_format($progressInfo['cpa'], 2) ?>) đã cao hơn hoặc bằng điểm mục tiêu (<?= number_format($analysis['target'], 2) ?>). Đối với <?= $analysis['tc_remain'] ?> tín chỉ còn lại, bạn chỉ cần hoàn thành đạt chuẩn qua môn tối thiểu (điểm chữ D hoặc cao hơn) là sẽ hoàn tất chương trình đào tạo với tấm bằng xếp loại như mong đợi!
          </div>
        </div>
      <?php elseif ($analysis['status'] === 'impossible'): ?>
        <div class="alert alert-danger" style="font-size: 14.5px; margin-bottom: 20px;">
          <i class="fas fa-exclamation-triangle" style="font-size: 22px;"></i>
          <div>
            <strong>Mục tiêu bất khả thi nếu chỉ học các môn còn lại!</strong><br>
            Điểm trung bình hệ 4 cần đạt của <?= $analysis['tc_remain'] ?> tín chỉ còn lại là <strong><?= number_format($analysis['cpa_remain_avg'], 2) ?></strong> (vượt quá điểm số tối đa 4.00).<br>
            Để đạt được mục tiêu, bạn <strong>bắt buộc phải học cải thiện</strong> các môn học cũ có điểm số thấp (D, D+, C) nhằm kéo CPA tích lũy hiện tại lên, đồng thời nỗ lực tối đa trong các học phần còn lại. Xem gợi ý học cải thiện thông minh bên dưới!
          </div>
        </div>
      <?php else: ?>
        <div style="margin-bottom: 24px;">
          <h4 style="margin: 0 0 12px; color: #212529; font-weight: 700; font-size: 15px;"><i class="fas fa-route" style="color:var(--success);"></i> Kịch bản điểm số tối thiểu để đạt mục tiêu:</h4>
          <p style="font-size: 13.5px; color:#555; margin-bottom: 15px;">Điểm trung bình tích lũy hệ 4 của các môn còn lại cần đạt là: <strong style="font-size: 16px; color: var(--primary);"><?= number_format($analysis['cpa_remain_avg'], 2) ?></strong> (tương đương hệ 10 là khoảng <strong><?= number_format($analysis['cpa_remain_avg'] * 2.5, 1) ?></strong> trở lên).</p>
          
          <div style="display:flex; flex-direction:column; gap: 12px;">
            <?php foreach ($analysis['scenarios'] as $idx => $sc): ?>
              <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; background: #fafbfc;">
                <strong style="font-size: 13.5px; color: var(--primary); display:block; margin-bottom: 4px;"><i class="far fa-star"></i> <?= e($sc['title']) ?></strong>
                <span style="font-size: 13px; color: #4a5568; line-height: 1.5;"><?= $sc['detail'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Gợi ý cải thiện điểm -->
      <?php if (!empty($analysis['improvements'])): ?>
        <div style="margin-top: 10px; border-top: 1px solid #edf2f7; padding-top: 20px;">
          <h4 style="margin: 0 0 8px; color: #b02a37; font-weight: 700; font-size: 15px;"><i class="fas fa-lightbulb" style="color:var(--warning);"></i> Gợi ý cải thiện điểm thông minh:</h4>
          <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Hệ thống phát hiện các học phần đã đạt nhưng điểm số còn thấp. Nếu bạn đăng ký học cải thiện các môn này lên điểm **A (4.0)**, áp lực cho các môn học còn lại sẽ được giảm bớt:</p>
          
          <div class="table-wrap" style="margin-top:0;">
            <table style="font-size: 12.5px;">
              <thead>
                <tr>
                  <th>Mã học phần</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">Tín chỉ</th>
                  <th style="text-align:center">Điểm hiện tại</th>
                  <th style="text-align:center">CPA tích lũy mới</th>
                  <th style="text-align:center">Điểm TB các môn còn lại mới</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($analysis['improvements'] as $imp): ?>
                  <tr>
                    <td><code><?= e($imp['ma_hp']) ?></code></td>
                    <td class="font-medium"><?= e($imp['ten_hp']) ?></td>
                    <td style="text-align:center"><?= $imp['so_tin_chi'] ?> TC</td>
                    <td style="text-align:center;">
                      <span class="badge badge-warning" style="background: rgba(255,193,7,0.15); color: #856404; font-weight:bold;"><?= $imp['diem_chu'] ?> (<?= number_format($imp['diem_he4'], 1) ?>)</span>
                    </td>
                    <td style="text-align:center; font-weight: bold; color: var(--success);">
                      <?= number_format($imp['cpa_new'], 2) ?>
                    </td>
                    <td style="text-align:center; font-weight: bold; color: var(--primary);">
                      <?= $imp['cpa_remain_avg_new'] > 4.0 ? '<span style="color:#ef4444;">Bất khả thi</span>' : number_format($imp['cpa_remain_avg_new'], 2) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div>
    <div class="card-footer" style="text-align: right; border-top: 1px solid var(--border); padding: 14px 20px; flex-shrink: 0; background: #f8f9fa;">
      <button class="btn btn-secondary" onclick="closeAnalysisModal()">Đóng lại</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function openTargetModal() {
    document.getElementById('targetModal').style.display = 'flex';
    document.getElementById('gpa_muc_tieu').focus();
}

function closeTargetModal() {
    document.getElementById('targetModal').style.display = 'none';
    document.getElementById('targetMsg').style.display = 'none';
}

function openAnalysisModal() {
    const modal = document.getElementById('analysisModal');
    if (modal) modal.style.display = 'flex';
}

function closeAnalysisModal() {
    const modal = document.getElementById('analysisModal');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Đóng modal khi click ra vùng ngoài card
    const targetModal = document.getElementById('targetModal');
    if (targetModal) {
        targetModal.addEventListener('click', function(e) {
            if (e.target === targetModal) {
                closeTargetModal();
            }
        });
    }

    const analysisModal = document.getElementById('analysisModal');
    if (analysisModal) {
        analysisModal.addEventListener('click', function(e) {
            if (e.target === analysisModal) {
                closeAnalysisModal();
            }
        });
    }

    // Xử lý gửi Form lưu mục tiêu
    const targetForm = document.getElementById('targetForm');
    if (targetForm) {
        targetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const gpaVal = document.getElementById('gpa_muc_tieu').value;
            const targetMsg = document.getElementById('targetMsg');
            
            targetMsg.style.display = 'none';
            
            fetch('<?= BASE_URL ?>/student/tien-do/dat-muc-tieu', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'gpa_muc_tieu=' + encodeURIComponent(gpaVal)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    targetMsg.innerText = data.message || 'Lỗi lưu mục tiêu GPA.';
                    targetMsg.style.display = 'block';
                }
            })
            .catch(err => {
                console.error(err);
                targetMsg.innerText = 'Lỗi kết nối máy chủ.';
                targetMsg.style.display = 'block';
            });
        });
    }
});
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
