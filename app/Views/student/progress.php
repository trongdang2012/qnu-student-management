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

    <?php if (count($progressInfo['no_mon_list']) > 0): ?>
    <!-- Đề xuất môn học (Môn nợ) -->
    <div class="card mb-20 fade-in" style="border-left: 4px solid #ef4444;">
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

<?php require_once ROOT . '/includes/footer.php'; ?>
