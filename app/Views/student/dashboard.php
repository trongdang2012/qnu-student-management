<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <!-- Welcome Banner -->
    <div class="welcome-banner fade-in">
      <div>
        <h2>Xin chào, <?= e($sv['ho_ten']) ?>! 👋</h2>
        <p>
          <i class="fas fa-graduation-cap"></i> <?= e($sv['nganh']) ?> &nbsp;|&nbsp;
          <i class="fas fa-users"></i> Lớp <?= e($sv['lop']) ?> &nbsp;|&nbsp;
          <i class="fas fa-calendar"></i> Năm học <?= NAM_HOC_HIEN_TAI ?> - Học kỳ <?= HOC_KY_HIEN_TAI ?>
        </p>
      </div>
      <img src="<?= (empty($sv['anh_dai_dien'])) ? BASE_URL.'/assets/img/default-avatar.svg' : BASE_URL.'/uploads/'.e($sv['anh_dai_dien']) ?>"
           alt="Avatar" class="welcome-avatar">
    </div>

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

      <style>
        .debt-warning-banner {
          background: rgba(220, 53, 69, 0.08);
          border: 1px solid rgba(220, 53, 69, 0.2);
          backdrop-filter: blur(8px);
          -webkit-backdrop-filter: blur(8px);
          border-radius: var(--radius-lg, 12px);
          padding: 18px 24px;
          margin-bottom: 24px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 20px;
          box-shadow: 0 4px 15px rgba(220, 53, 69, 0.05);
        }
        .debt-warning-content {
          display: flex;
          align-items: flex-start;
          gap: 16px;
          flex: 1;
        }
        .debt-warning-icon {
          background: rgba(220, 53, 69, 0.15);
          color: var(--danger);
          font-size: 24px;
          width: 48px;
          height: 48px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }
        .debt-warning-body h4 {
          margin: 0 0 4px 0;
          color: #b02a37;
          font-size: 16px;
          font-weight: 700;
        }
        .debt-warning-body p {
          margin: 0 0 12px 0;
          color: #5c636a;
          font-size: 14px;
          line-height: 1.5;
        }
        .debt-badges {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
        }
        .debt-badge {
          background: rgba(220, 53, 69, 0.08);
          border: 1px solid rgba(220, 53, 69, 0.15);
          color: #b02a37;
          padding: 4px 10px;
          border-radius: 6px;
          font-size: 12.5px;
          display: inline-flex;
          align-items: center;
        }
        .debt-warning-action .btn-danger {
          background: #dc3545;
          color: #fff;
          border: none;
          padding: 10px 20px;
          font-weight: 600;
          font-size: 14px;
          border-radius: 8px;
          white-space: nowrap;
          transition: all 0.2s ease;
          display: inline-flex;
          align-items: center;
          gap: 8px;
          text-decoration: none;
          box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }
        .debt-warning-action .btn-danger:hover {
          background: #bb2d3b;
          transform: translateY(-2px);
          box-shadow: 0 6px 15px rgba(220, 53, 69, 0.3);
        }
        .animate-pulse {
          animation: pulse 2s infinite;
        }
        @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.1); }
          100% { transform: scale(1); }
        }
        @media (max-width: 768px) {
          .debt-warning-banner {
            flex-direction: column;
            align-items: stretch;
            padding: 16px;
          }
          .debt-warning-action {
            text-align: right;
            margin-top: 8px;
          }
          .debt-warning-action .btn-danger {
            width: 100%;
            justify-content: center;
          }
        }
      </style>
    <?php endif; ?>

    <!-- Flash message -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Stat cards -->
    <div class="stat-grid">
      <div class="stat-card fade-in">
        <div class="stat-icon blue"><i class="fas fa-book"></i></div>
        <div>
          <div class="stat-value"><?= $stats['hp_hk'] ?></div>
          <div class="stat-label">Học phần đang học</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="stat-value"><?= (int)$stats['tc_dat'] ?><span style="font-size:16px;font-weight:400;"> TC</span></div>
          <div class="stat-label">Tín chỉ tích lũy</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div>
          <div class="stat-value"><?= number_format($stats['cpa'], 2) ?></div>
          <div class="stat-label">CPA hiện tại</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon <?= $stats['hoc_phi_no'] > 0 ? 'red' : 'green' ?>">
          <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:18px;color:<?= $stats['hoc_phi_no'] > 0 ? 'var(--danger)' : 'var(--success)' ?>">
            <?= $stats['hoc_phi_no'] > 0 ? formatMoney($stats['hoc_phi_no']) : 'Đã nộp' ?>
          </div>
          <div class="stat-label">Học phí còn nợ</div>
        </div>
      </div>
    </div>

    <!-- Content grid -->
    <div class="content-grid">

      <!-- Left: tiến độ + điểm gần đây -->
      <div>
        <!-- Tiến độ tốt nghiệp -->
        <div class="card mb-20 fade-in">
          <div class="card-header">
            <h3><i class="fas fa-tasks"></i> Tiến độ tốt nghiệp</h3>
            <a href="<?= BASE_URL ?>/student/tien-do" class="btn btn-outline btn-sm">Xem chi tiết</a>
          </div>
          <div class="card-body">
            <?php $pct = $stats['tc_total'] > 0 ? min(100, round($stats['tc_dat'] / $stats['tc_total'] * 100)) : 0; ?>
            <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
              <span>Tín chỉ tích lũy: <strong><?= (int)$stats['tc_dat'] ?> / <?= (int)$stats['tc_total'] ?> TC</strong></span>
              <span style="color:var(--primary);font-weight:700;"><?= $pct ?>%</span>
            </div>
            <div class="progress">
              <div class="progress-bar <?= $pct >= 80 ? 'green' : ($pct >= 50 ? '' : 'orange') ?>"
                   style="width:0" data-width="<?= $pct ?>"></div>
            </div>
            <p class="text-muted mt-8" style="font-size:13px;">
              <?php if ($pct < 50): ?>
                <i class="fas fa-info-circle"></i> Bạn đã hoàn thành <?= $pct ?>% chương trình đào tạo.
              <?php elseif ($pct < 100): ?>
                <i class="fas fa-fire"></i> Bạn đang trong giai đoạn cuối! Cố lên!
              <?php else: ?>
                <i class="fas fa-trophy" style="color:gold"></i> Bạn đã hoàn thành chương trình đào tạo!
              <?php endif; ?>
            </p>
          </div>
        </div>

        <!-- Điểm học phần gần đây -->
        <div class="card fade-in">
          <div class="card-header">
            <h3><i class="fas fa-graduation-cap"></i> Điểm học phần gần đây</h3>
            <a href="<?= BASE_URL ?>/student/diem-hoc-tap" class="btn btn-outline btn-sm">Tất cả</a>
          </div>
          <div class="card-body" style="padding:0">
            <?php if (empty($diem_recent)): ?>
              <p class="text-muted" style="padding:20px;text-align:center;">Chưa có điểm học phần.</p>
            <?php else: ?>
            <div class="table-wrap">
            <table>
              <thead><tr>
                <th>Học phần</th>
                <th style="text-align:center">TC</th>
                <th style="text-align:center">Điểm TK</th>
                <th style="text-align:center">Xếp loại</th>
              </tr></thead>
              <tbody>
              <?php foreach ($diem_recent as $d): ?>
                <tr>
                  <td><?= e($d['ten_hp']) ?></td>
                  <td style="text-align:center"><?= (int)$d['so_tin_chi'] ?></td>
                  <td style="text-align:center;font-weight:700"><?= number_format((float)$d['diem_tong'], 1) ?></td>
                  <td style="text-align:center">
                    <span class="badge badge-<?= badgeDiemChu($d['diem_chu'] ?? 'F') ?>">
                      <?= e($d['diem_chu'] ?? 'F') ?>
                    </span>
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

      <!-- Right: thông tin sinh viên + điểm rèn luyện -->
      <div>
        <!-- Thông tin nhanh -->
        <div class="card mb-20 fade-in">
          <div class="card-header">
            <h3><i class="fas fa-user"></i> Thông tin</h3>
            <a href="<?= BASE_URL ?>/student/ho-so" class="btn btn-outline btn-sm">Hồ sơ</a>
          </div>
          <div class="card-body" style="padding:14px">
            <table style="border:none;font-size:14px;">
              <tr>
                <td style="border:none;padding:6px 10px;color:var(--text-muted);width:50%">MSSV</td>
                <td style="border:none;padding:6px 10px;font-weight:500"><?= e($sv['ma_sv']) ?></td>
              </tr>
              <tr>
                <td style="border:none;padding:6px 10px;color:var(--text-muted)">Ngành</td>
                <td style="border:none;padding:6px 10px"><?= e($sv['nganh']) ?></td>
              </tr>
              <tr>
                <td style="border:none;padding:6px 10px;color:var(--text-muted)">Lớp</td>
                <td style="border:none;padding:6px 10px"><?= e($sv['lop']) ?></td>
              </tr>
              <tr>
                <td style="border:none;padding:6px 10px;color:var(--text-muted)">Trạng thái</td>
                <td style="border:none;padding:6px 10px">
                  <span class="badge badge-success"><?= e($sv['trang_thai']) ?></span>
                </td>
              </tr>
              <tr>
                <td style="border:none;padding:6px 10px;color:var(--text-muted)">Email</td>
                <td style="border:none;padding:6px 10px;word-break:break-all"><?= e($sv['email'] ?? '—') ?></td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Điểm rèn luyện -->
        <div class="card fade-in">
          <div class="card-header">
            <h3><i class="fas fa-star"></i> Điểm rèn luyện</h3>
            <a href="<?= BASE_URL ?>/student/diem-ren-luyen" class="btn btn-outline btn-sm">Chi tiết</a>
          </div>
          <div class="card-body" style="text-align:center;padding:24px">
            <?php if ($drl): ?>
              <?php
                $drl_color = $drl['diem'] >= 80 ? 'var(--success)' : ($drl['diem'] >= 65 ? 'var(--primary)' : ($drl['diem'] >= 50 ? 'var(--warning)' : 'var(--danger)'));
              ?>
              <div style="font-size:52px;font-weight:700;color:<?= $drl_color ?>;line-height:1">
                <?= (int)$drl['diem'] ?>
              </div>
              <div style="font-size:15px;color:var(--text-muted);margin-top:6px">điểm / 100</div>
              <span class="badge badge-<?= $drl['diem'] >= 80 ? 'success' : ($drl['diem'] >= 65 ? 'primary' : 'warning') ?>"
                    style="margin-top:10px;font-size:14px;padding:6px 18px">
                <?= e($drl['xep_loai'] ?? '') ?>
              </span>
            <?php else: ?>
              <p class="text-muted">Chưa có điểm rèn luyện.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /content-grid -->

    <!-- Quick links -->
    <div class="card mt-16 fade-in">
      <div class="card-header"><h3><i class="fas fa-th"></i> Truy cập nhanh</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;">
          <?php
          $links = [
            ['url'=>BASE_URL.'/student/thoi-khoa-bieu','icon'=>'fas fa-calendar-alt','label'=>'Thời khóa biểu','color'=>'#e8f0fb','ic_color'=>'var(--primary)'],
            ['url'=>BASE_URL.'/student/dang-ky-hoc-phan',    'icon'=>'fas fa-plus-circle', 'label'=>'Đăng ký HP',      'color'=>'#d4edda','ic_color'=>'var(--success)'],
            ['url'=>BASE_URL.'/student/hoc-phi',       'icon'=>'fas fa-money-bill',  'label'=>'Học phí',          'color'=>'#fff3cd','ic_color'=>'#856404'],
            ['url'=>BASE_URL.'/student/chuong-trinh',  'icon'=>'fas fa-list-alt',    'label'=>'Chương trình ĐT','color'=>'#d1ecf1','ic_color'=>'#0c5460'],
            ['url'=>BASE_URL.'/student/tai-lieu', 'icon'=>'fas fa-share-alt',   'label'=>'Tài liệu',         'color'=>'#f3e5f5','ic_color'=>'#8e44ad'],
            ['url'=>BASE_URL.'/student/cap-nhat',      'icon'=>'fas fa-edit',        'label'=>'Cập nhật TT',    'color'=>'#fde8e8','ic_color'=>'var(--danger)'],
          ];
          foreach ($links as $lk):
          ?>
          <a href="<?= $lk['url'] ?>"
             style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 12px;background:<?= $lk['color'] ?>;border-radius:var(--radius);transition:transform .18s,box-shadow .18s;text-decoration:none;"
             onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-md)'"
             onmouseout="this.style.transform='';this.style.boxShadow=''">
            <i class="<?= $lk['icon'] ?>" style="font-size:24px;color:<?= $lk['ic_color'] ?>"></i>
            <span style="font-size:13px;font-weight:500;color:var(--text);text-align:center"><?= $lk['label'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /wrapper -->

<?php require_once ROOT . '/includes/footer.php'; ?>
