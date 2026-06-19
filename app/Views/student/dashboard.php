<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Debt warning banner -->
    <?php if (isset($stats['no_mon_list']) && count($stats['no_mon_list']) > 0): ?>
      <div class="debt-warning-banner fade-in">
        <div class="debt-warning-content">
          <div class="debt-warning-icon">
            <i class="fas fa-exclamation-triangle animate-pulse"></i>
          </div>
          <div class="debt-warning-body">
            <h4>Cảnh báo học vụ: Bạn đang bị nợ môn (<?= count($stats['no_mon_list']) ?> môn học bị điểm F)</h4>
            <p>Vui lòng đăng ký học lại sớm nhất có thể để đảm bảo tiến độ tốt nghiệp. Các môn học này chưa được tính vào tín chỉ tích lũy.</p>
            <div class="debt-badges">
              <?php foreach ($stats['no_mon_list'] as $m): ?>
                <span class="debt-badge">
                  <strong><?= e($m['ma_hp']) ?></strong> - <?= e($m['ten_hp']) ?> (<?= (int)$m['so_tin_chi'] ?> TC)
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="debt-warning-action">
          <a href="<?= BASE_URL ?>/student/dang-ky-hoc-phan" class="btn btn-danger">
            <i class="fas fa-plus-circle"></i> Đăng ký học lại
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Welcome Section (Row 1) -->
    <div class="row-welcome fade-in">
      <!-- Cột trái (Welcome Banner + 3 Stat Cards) -->
      <div class="col-welcome-left">
        <!-- Welcome Banner -->
        <?php
        $hour = (int)date('H');
        $greeting = 'Xin chào';
        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Chào buổi sáng';
        } elseif ($hour >= 12 && $hour < 18) {
            $greeting = 'Chào buổi chiều';
        } else {
            $greeting = 'Chào buổi tối';
        }
        ?>
        <div class="welcome-banner-modern">
          <div class="welcome-text-wrap">
            <h2><?= $greeting ?>, <?= e($sv['ho_ten']) ?>!</h2>
            <p><?= e($sv['nganh']) ?> &nbsp;|&nbsp; Lớp <?= e($sv['lop']) ?> &nbsp;|&nbsp; Năm học <?= NAM_HOC_HIEN_TAI ?> - Học kỳ <?= HOC_KY_HIEN_TAI ?></p>
          </div>
          <!-- Các hình khối 3D neon lung linh trang trí -->
          <div class="decor-shape shape-1"></div>
          <div class="decor-shape shape-2"></div>
          <div class="decor-shape shape-3"></div>
        </div>

        <!-- 3 Stat Cards -->
        <div class="stat-grid-modern">
          <!-- Card 1: GPA -->
          <div class="stat-card-modern">
            <div class="stat-icon-modern blue"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info-modern">
              <div class="stat-label-modern">GPA hiện tại:</div>
              <div class="stat-value-modern"><?= number_format($stats['cpa'], 2) ?></div>
            </div>
          </div>
          <!-- Card 2: Tín chỉ tích lũy -->
          <div class="stat-card-modern">
            <div class="stat-icon-modern purple"><i class="fas fa-trophy"></i></div>
            <div class="stat-info-modern">
              <div class="stat-label-modern">Tín chỉ tích lũy:</div>
              <div class="stat-value-modern"><?= (int)$stats['tc_dat'] ?> TC</div>
            </div>
          </div>
          <!-- Card 3: Học phần đang học -->
          <div class="stat-card-modern">
            <div class="stat-icon-modern dark-blue"><i class="fas fa-graduation-cap"></i></div>
            <div class="stat-info-modern">
              <div class="stat-label-modern">Học phần đang học:</div>
              <div class="stat-value-modern"><?= $stats['hp_hk'] ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cột phải (Tiến độ tốt nghiệp) -->
      <div class="col-welcome-right">
        <div class="progress-card-modern">
          <?php $pct = $stats['tc_total'] > 0 ? min(100, round($stats['tc_dat'] / $stats['tc_total'] * 100)) : 0; ?>
          <h3 class="progress-title-modern">Tiến độ tốt nghiệp: <?= $pct ?>%</h3>
          
          <div class="radial-gauge-container">
            <svg width="180" height="180" viewBox="0 0 120 120" class="radial-gauge-svg" style="width: 100%; height: 100%;">
              <defs>
                <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" style="stop-color:#3a86ff;stop-opacity:1" />
                  <stop offset="100%" style="stop-color:#8338ec;stop-opacity:1" />
                </linearGradient>
              </defs>
              <circle cx="60" cy="60" r="50" fill="none" stroke="#e6e8f0" stroke-width="8" />
              <circle cx="60" cy="60" r="50" fill="none" stroke="url(#gaugeGrad)" stroke-width="8"
                      stroke-dasharray="314.16" stroke-dashoffset="<?= 314.16 - (314.16 * $pct / 100) ?>"
                      stroke-linecap="round" class="radial-gauge-progress" />
            </svg>
            <div class="radial-gauge-content">
              <div class="gauge-percent"><?= $pct ?>%</div>
              <div class="gauge-stats"><?= (int)$stats['tc_dat'] ?> / <?= (int)$stats['tc_total'] ?> TC</div>
              <div class="gauge-subtext">
                <?php if ($pct < 50): ?>
                  Bạn mới bắt đầu chặng đường! Cố lên!
                <?php elseif ($pct < 100): ?>
                  Bạn đang trong giai đoạn cuối! Cố lên!
                <?php else: ?>
                  Đã hoàn thành xuất sắc!
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lower Section (Row 2) -->
    <div class="row-lower fade-in">
      <!-- Cột trái (Thông tin cá nhân + Thông báo mới) -->
      <div class="col-lower-left">
        <!-- Thông tin cá nhân -->
        <div class="card-modern profile-card-modern">
          <div class="card-header-modern">
            <h3>Thông tin Cá nhân</h3>
          </div>
          <div class="card-body-modern">
            <div class="info-row-modern">
              <span class="info-label-modern">MSSV:</span>
              <span class="info-value-modern"><?= e($sv['ma_sv']) ?></span>
            </div>
            <div class="info-row-modern">
              <span class="info-label-modern">Ngành:</span>
              <span class="info-value-modern"><?= e($sv['nganh']) ?></span>
            </div>
            <div class="info-row-modern">
              <span class="info-label-modern">Lớp:</span>
              <span class="info-value-modern"><?= e($sv['lop']) ?></span>
            </div>
            <div class="info-row-modern">
              <span class="info-label-modern">Trạng thái:</span>
              <span class="info-value-modern">
                <span class="pill-green-modern"><?= e($sv['trang_thai']) ?></span>
              </span>
            </div>
            <div class="info-row-modern">
              <span class="info-label-modern">Email:</span>
              <span class="info-value-modern text-truncate" title="<?= e($sv['email']) ?>"><?= e($sv['email'] ?? '—') ?></span>
            </div>
          </div>
        </div>

        <!-- Thông báo mới -->
        <div class="card-modern notice-card-modern">
          <div class="card-header-modern display-flex-between">
            <h3>Thông báo mới</h3>
            <a href="<?= BASE_URL ?>/student/thong-bao" class="arrow-link-modern"><i class="fas fa-chevron-right"></i></a>
          </div>
          <div class="card-body-modern notice-list-modern">
            <?php 
              $topNotices = array_slice($latestNotices ?? [], 0, 1);
              if (empty($topNotices)):
            ?>
              <p class="text-muted text-center" style="font-size: 13px; margin: 10px 0;">Chưa có thông báo mới.</p>
            <?php else: ?>
              <?php foreach ($topNotices as $n): ?>
                <div class="notice-item-modern" onclick="openNoticeDetailModal(<?= $n['id'] ?>, '<?= e($n['tieu_de']) ?>', '<?= e($n['nguoi_gui_ten'] ?? 'Hệ thống') ?>', '<?= date('d/m/Y H:i', strtotime($n['ngay_tao'])) ?>', <?= htmlspecialchars(json_encode($n['noi_dung'], JSON_UNESCAPED_UNICODE)) ?>, '<?= $n['loai'] ?>')">
                  <div class="notice-icon-modern"><i class="fas fa-bell animate-wiggle"></i></div>
                  <div class="notice-info-modern">
                    <div class="notice-title-modern"><?= e($n['tieu_de']) ?></div>
                    <div class="notice-text-modern"><?= mb_strimwidth(strip_tags($n['noi_dung']), 0, 65, '...') ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Cột phải (Điểm học phần gần đây) -->
      <div class="col-lower-right">
        <div class="card-modern grades-card-modern">
          <div class="card-header-modern">
            <h3>Điểm học phần gần đây</h3>
          </div>
          <div class="card-body-modern table-container-modern">
            <?php if (empty($diem_recent)): ?>
              <p class="text-muted text-center" style="padding: 30px;">Chưa có điểm học phần.</p>
            <?php else: ?>
              <table class="table-modern">
                <thead>
                  <tr>
                    <th>Học phần</th>
                    <th class="text-center">Tín chỉ TC</th>
                    <th class="text-center">TK điểm</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($diem_recent as $d): ?>
                    <tr>
                      <td class="font-medium"><?= e($d['ten_hp']) ?></td>
                      <td class="text-center"><?= (int)$d['so_tin_chi'] ?></td>
                      <td class="text-center font-bold"><?= number_format((float)$d['diem_tong'], 1) ?></td>
                      <td class="text-center">
                        <span class="grade-badge-modern grade-<?= e($d['diem_chu'] ?? 'F') ?>">
                          <?= e($d['diem_chu'] ?? 'F') ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <a href="<?= BASE_URL ?>/student/diem-hoc-tap" class="btn-action-modern">Xem chi tiết</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->

<?php require_once ROOT . '/includes/footer.php'; ?>
