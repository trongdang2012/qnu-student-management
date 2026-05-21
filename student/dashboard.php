<?php
/**
 * student/dashboard.php - Trang tổng quan sinh viên
 */
define('ROOT', __DIR__ . '/..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db  = getDB();
$sid = (int)$sv['id'];

// Tổng tín chỉ đã hoàn thành (điểm >= 4)
$r = $db->query("SELECT SUM(hp.so_tin_chi) AS tc
    FROM diem_hoc_tap d
    JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
    WHERE d.sinh_vien_id = $sid AND d.diem_he4 >= 1.0");
$tc_dat = (float)($r->fetch_assoc()['tc'] ?? 0);

// Tổng tín chỉ CTDT
$r2 = $db->query("SELECT SUM(hp.so_tin_chi) AS tc
    FROM ctdt_chi_tiet c JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
    WHERE c.nganh = '{$db->real_escape_string($sv['nganh'])}'");
$tc_total = (float)($r2->fetch_assoc()['tc'] ?? 130);

// CPA
$r3 = $db->query("SELECT
    SUM(d.diem_he4 * hp.so_tin_chi) / SUM(hp.so_tin_chi) AS cpa
    FROM diem_hoc_tap d JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
    WHERE d.sinh_vien_id = $sid AND d.diem_he4 IS NOT NULL");
$cpa = round((float)($r3->fetch_assoc()['cpa'] ?? 0), 2);

// Học phí nợ
$r4 = $db->query("SELECT SUM(so_tien - da_nop) AS no
    FROM hoc_phi WHERE sinh_vien_id = $sid AND trang_thai IN ('Nợ','Chưa nộp')");
$hoc_phi_no = (float)($r4->fetch_assoc()['no'] ?? 0);

// Số HP đang học kỳ này
$r5 = $db->query("SELECT COUNT(*) AS cnt FROM dang_ky_hp
    WHERE sinh_vien_id = $sid AND hoc_ky = '" . HOC_KY_HIEN_TAI . "' AND trang_thai='Đã duyệt'");
$hp_hk = (int)($r5->fetch_assoc()['cnt'] ?? 0);

// Điểm rèn luyện kỳ gần nhất
$r6 = $db->query("SELECT diem, xep_loai FROM diem_ren_luyen
    WHERE sinh_vien_id = $sid ORDER BY id DESC LIMIT 1");
$drl = $r6->fetch_assoc();

// 4 điểm học phần gần nhất
$r7 = $db->query("SELECT d.*, hp.ten_hp, hp.so_tin_chi
    FROM diem_hoc_tap d JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
    WHERE d.sinh_vien_id = $sid AND d.diem_tong IS NOT NULL
    ORDER BY d.id DESC LIMIT 4");
$diem_recent = $r7->fetch_all(MYSQLI_ASSOC);

$page_title  = 'Tổng quan';
$active_menu = 'dashboard';
require_once ROOT . '/includes/header.php';
?>

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
          <div class="stat-value"><?= $hp_hk ?></div>
          <div class="stat-label">Học phần đang học</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="stat-value"><?= (int)$tc_dat ?><span style="font-size:16px;font-weight:400;"> TC</span></div>
          <div class="stat-label">Tín chỉ tích lũy</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div>
          <div class="stat-value"><?= number_format($cpa, 2) ?></div>
          <div class="stat-label">CPA hiện tại</div>
        </div>
      </div>
      <div class="stat-card fade-in">
        <div class="stat-icon <?= $hoc_phi_no > 0 ? 'red' : 'green' ?>">
          <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:18px;color:<?= $hoc_phi_no > 0 ? 'var(--danger)' : 'var(--success)' ?>">
            <?= $hoc_phi_no > 0 ? formatMoney($hoc_phi_no) : 'Đã nộp' ?>
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
            <a href="<?= BASE_URL ?>/student/ca_nhan/tien_do.php" class="btn btn-outline btn-sm">Xem chi tiết</a>
          </div>
          <div class="card-body">
            <?php $pct = $tc_total > 0 ? min(100, round($tc_dat / $tc_total * 100)) : 0; ?>
            <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px;">
              <span>Tín chỉ tích lũy: <strong><?= (int)$tc_dat ?> / <?= (int)$tc_total ?> TC</strong></span>
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
            <a href="<?= BASE_URL ?>/student/hoc_tap/diem_hoc_tap.php" class="btn btn-outline btn-sm">Tất cả</a>
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
            <a href="<?= BASE_URL ?>/student/ca_nhan/thong_tin.php" class="btn btn-outline btn-sm">Hồ sơ</a>
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
            <a href="<?= BASE_URL ?>/student/hoc_tap/diem_ren_luyen.php" class="btn btn-outline btn-sm">Chi tiết</a>
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
            ['url'=>BASE_URL.'/student/hoc_tap/thoi_khoa_bieu.php','icon'=>'fas fa-calendar-alt','label'=>'Thời khóa biểu','color'=>'#e8f0fb','ic_color'=>'var(--primary)'],
            ['url'=>BASE_URL.'/student/truc_tuyen/dang_ky.php',    'icon'=>'fas fa-plus-circle', 'label'=>'Đăng ký HP',      'color'=>'#d4edda','ic_color'=>'var(--success)'],
            ['url'=>BASE_URL.'/student/hoc_tap/hoc_phi.php',       'icon'=>'fas fa-money-bill',  'label'=>'Học phí',          'color'=>'#fff3cd','ic_color'=>'#856404'],
            ['url'=>BASE_URL.'/student/hoc_tap/chuong_trinh.php',  'icon'=>'fas fa-list-alt',    'label'=>'Chương trình ĐT','color'=>'#d1ecf1','ic_color'=>'#0c5460'],
            ['url'=>BASE_URL.'/student/truc_tuyen/chia_se_tl.php', 'icon'=>'fas fa-share-alt',   'label'=>'Tài liệu',         'color'=>'#f3e5f5','ic_color'=>'#8e44ad'],
            ['url'=>BASE_URL.'/student/ca_nhan/cap_nhat.php',      'icon'=>'fas fa-edit',        'label'=>'Cập nhật TT',    'color'=>'#fde8e8','ic_color'=>'var(--danger)'],
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
