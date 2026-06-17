<?php
function statusBadge(string $tt): string {
    return match($tt) {
        'Đã nộp'  => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Đã nộp đủ</span>',
        'Nợ'      => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Còn nợ</span>',
        default   => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Chưa nộp</span>',
    };
}
?>
<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container" style="max-width:960px">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Học tập</span>
        <span>›</span><span>Học phí</span>
      </div>
      <h1><i class="fas fa-money-bill-wave"></i> Tra cứu học phí</h1>
      <p>Xem lịch sử nộp học phí và các khoản nợ đọng.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <!-- Tổng quan -->
    <div class="stat-grid fade-in">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
          <div class="stat-value" style="font-size:20px"><?= formatMoney($tuitionFeesInfo['tong_hoc_phi']) ?></div>
          <div class="stat-label">Tổng học phí toàn khóa</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
        <div>
          <div class="stat-value" style="font-size:20px;color:var(--success)"><?= formatMoney($tuitionFeesInfo['tong_da_nop']) ?></div>
          <div class="stat-label">Đã nộp</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon <?= $tuitionFeesInfo['tong_no'] > 0 ? 'red' : 'green' ?>">
          <i class="fas fa-<?= $tuitionFeesInfo['tong_no'] > 0 ? 'exclamation-triangle' : 'thumbs-up' ?>"></i>
        </div>
        <div>
          <div class="<?= $tuitionFeesInfo['tong_no'] > 0 ? 'debt-amount' : 'paid-amount' ?>"><?= formatMoney($tuitionFeesInfo['tong_no']) ?></div>
          <div class="stat-label"><?= $tuitionFeesInfo['tong_no'] > 0 ? 'Còn nợ học phí' : 'Không có nợ học phí' ?></div>
        </div>
      </div>
    </div>

    <?php if ($tuitionFeesInfo['tong_no'] > 0): ?>
    <!-- Cảnh báo nợ học phí -->
    <div class="alert alert-danger fade-in">
      <i class="fas fa-exclamation-triangle" style="font-size:20px"></i>
      <div>
        <strong>Cảnh báo!</strong> Bạn đang có khoản nợ học phí
        <strong><?= formatMoney($tuitionFeesInfo['tong_no']) ?></strong>.
        Vui lòng nộp đầy đủ trước kỳ thi để không bị ảnh hưởng đến việc dự thi.
        <br><small>Liên hệ Phòng Tài chính — Kế toán: <strong>(0256) 3846 344 (ext.105)</strong></small>
      </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success fade-in">
      <i class="fas fa-check-circle" style="font-size:20px"></i>
      <div><strong>Tốt lắm!</strong> Bạn không có khoản nợ học phí nào. Chúc bạn học tốt! 🎉</div>
    </div>
    <?php endif; ?>

    <!-- Bảng học phí -->
    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-table"></i> Chi tiết học phí từng học kỳ</h3>
      </div>
      <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Năm học</th>
          <th style="text-align:center">Học kỳ</th>
          <th style="text-align:right">Học phí</th>
          <th style="text-align:right">Đã nộp</th>
          <th style="text-align:right">Còn nợ</th>
          <th style="text-align:center">Hạn nộp</th>
          <th style="text-align:center">Trạng thái</th>
          <th style="text-align:center">Hành động</th>
        </tr></thead>
        <tbody>
        <?php if (empty($tuitionFeesInfo['hp_list'])): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">Chưa có dữ liệu học phí.</td></tr>
        <?php else: ?>
        <?php $displayedCourseTerms = []; ?>
        <?php foreach ($tuitionFeesInfo['hp_list'] as $hp):
          $no    = $hp['so_tien'] - $hp['da_nop'];
          $qua_han = !empty($hp['han_nop']) && strtotime($hp['han_nop']) < time() && $hp['trang_thai'] !== 'Đã nộp';
          $courseTermKey = $hp['nam_hoc'] . '|' . $hp['hoc_ky'];
          $showRegisteredCourses = !isset($displayedCourseTerms[$courseTermKey]);
          $displayedCourseTerms[$courseTermKey] = true;
        ?>
        <tr <?= $qua_han ? 'style="background:#fff5f5"' : '' ?>>
          <td><?= e($hp['nam_hoc']) ?></td>
          <td style="text-align:center">HK <?= (int)$hp['hoc_ky'] ?></td>
          <td style="text-align:right;font-weight:500"><?= formatMoney((float)$hp['so_tien']) ?></td>
          <td style="text-align:right;color:var(--success);font-weight:500"><?= formatMoney((float)$hp['da_nop']) ?></td>
          <td style="text-align:right;font-weight:700;color:<?= $no > 0 ? 'var(--danger)' : 'var(--success)' ?>">
            <?= $no > 0 ? formatMoney($no) : '<i class="fas fa-check"></i> 0 đ' ?>
          </td>
          <td style="text-align:center;font-size:14px">
            <?php if (!empty($hp['han_nop'])): ?>
              <span <?= $qua_han ? 'style="color:var(--danger);font-weight:700"' : '' ?>>
                <?= date('d/m/Y', strtotime($hp['han_nop'])) ?>
                <?= $qua_han ? '<br><small style="color:var(--danger)">⚠ Đã quá hạn</small>' : '' ?>
              </span>
            <?php else: ?> — <?php endif; ?>
          </td>
          <td style="text-align:center"><?= statusBadge($hp['trang_thai']) ?></td>
          <td style="text-align:center">
            <?php if ($hp['trang_thai'] !== 'Đã nộp'): ?>
              <form method="POST" action="<?= BASE_URL ?>/student/hoc-phi/nop" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="tuition_id" value="<?= (int)$hp['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">Nộp học phí</button>
              </form>
            <?php else: ?>
              <span style="color:var(--success);font-weight:600">Đã hoàn tất</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if ($showRegisteredCourses): ?>
        <tr style="background:#fcfcfc;border-bottom:1px solid var(--border)">
          <td colspan="8" style="padding:15px 20px">
            <div style="font-weight:600;color:var(--text-dark);margin-bottom:10px;font-size:14px">
              <i class="fas fa-book" style="color:var(--primary)"></i> Danh sách học phần đã đăng ký học kỳ <?= (int)$hp['hoc_ky'] ?>:
            </div>
            <div style="padding-left:10px">
              <?php if (empty($hp['registered_courses'])): ?>
              <p style="color:var(--text-muted);font-size:13px;margin:0;">Chưa có dữ liệu học phần cho học kỳ này.</p>
              <?php else: ?>
              <table style="width:100%;max-width:750px;border-collapse:collapse;margin:5px 0;font-size:13px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.05);border-radius:6px;overflow:hidden">
                <thead>
                  <tr style="background:#f4f6f9;color:var(--text-dark);text-align:left;font-weight:600;border-bottom:1px solid #e9ecef">
                    <th style="padding:8px 12px;width:60px;text-align:center;border:1px solid #e9ecef">STT</th>
                    <th style="padding:8px 12px;width:150px;border:1px solid #e9ecef">Mã học phần</th>
                    <th style="padding:8px 12px;border:1px solid #e9ecef">Tên học phần</th>
                    <th style="padding:8px 12px;width:120px;text-align:center;border:1px solid #e9ecef">Số tín chỉ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $stt = 1; ?>
                  <?php foreach ($hp['registered_courses'] as $course): ?>
                  <tr style="border-bottom:1px solid #f1f3f5">
                    <td style="padding:8px 12px;text-align:center;color:var(--text-muted);border:1px solid #e9ecef"><?= $stt++ ?></td>
                    <td style="padding:8px 12px;font-family:monospace;font-weight:600;color:#1565c0;border:1px solid #e9ecef"><?= e($course['ma_hp']) ?></td>
                    <td style="padding:8px 12px;color:#2c3e50;font-weight:500;border:1px solid #e9ecef"><?= e($course['ten_hp']) ?></td>
                    <td style="padding:8px 12px;text-align:center;font-weight:600;color:#2c3e50;border:1px solid #e9ecef"><?= (int)$course['so_tin_chi'] ?> TC</td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
          <tr style="background:#f0f4ff;font-weight:700">
            <td colspan="2" style="border:1px solid var(--border);padding:10px 14px;text-align:right">Tổng cộng:</td>
            <td style="border:1px solid var(--border);text-align:right"><?= formatMoney($tuitionFeesInfo['tong_hoc_phi']) ?></td>
            <td style="border:1px solid var(--border);text-align:right;color:var(--success)"><?= formatMoney($tuitionFeesInfo['tong_da_nop']) ?></td>
            <td style="border:1px solid var(--border);text-align:right;color:<?= $tuitionFeesInfo['tong_no'] > 0 ? 'var(--danger)' : 'var(--success)' ?>">
              <?= formatMoney($tuitionFeesInfo['tong_no']) ?>
            </td>
            <td colspan="3" style="border:1px solid var(--border)"></td>
          </tr>
        </tfoot>
      </table>
      </div>
      <div class="card-footer">
        <p class="text-muted" style="font-size:13px">
          <i class="fas fa-info-circle"></i>
          Nộp học phí tại <strong>Phòng Tài chính - Kế toán</strong> (Cơ sở 1 - 170 An Dương Vương, Quy Nhơn) hoặc chuyển khoản theo hướng dẫn trên cổng thông tin.
        </p>
      </div>
    </div>

    <!-- Hướng dẫn thanh toán -->
    <div class="card mt-16 fade-in">
      <div class="card-header"><h3><i class="fas fa-university"></i> Thông tin thanh toán</h3></div>
      <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
          <div>
            <h4 style="color:var(--primary);margin-bottom:10px"><i class="fas fa-building"></i> Tài khoản ngân hàng</h4>
            <table style="border:none;font-size:14px">
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted);width:140px">Ngân hàng:</td><td style="border:none;padding:5px 0;font-weight:500">Vietcombank - CN Quy Nhơn</td></tr>
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted)">Số tài khoản:</td><td style="border:none;padding:5px 0;font-weight:500;font-family:monospace">0071000123456</td></tr>
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted)">Chủ tài khoản:</td><td style="border:none;padding:5px 0;font-weight:500">TRƯỜNG ĐẠI HỌC QUY NHƠN</td></tr>
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted)">Nội dung CK:</td><td style="border:none;padding:5px 0;font-weight:500;color:var(--primary)"><?= e($sv['ma_sv']) ?> - Hoc phi HK<?= HOC_KY_HIEN_TAI ?></td></tr>
            </table>
          </div>
          <div>
            <h4 style="color:var(--primary);margin-bottom:10px"><i class="fas fa-phone-alt"></i> Liên hệ hỗ trợ</h4>
            <table style="border:none;font-size:14px">
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted);width:120px">Điện thoại:</td><td style="border:none;padding:5px 0;font-weight:500">(0256) 3846 344 (ext.105)</td></tr>
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted)">Email:</td><td style="border:none;padding:5px 0"><a href="mailto:taichinh@qnu.edu.vn">taichinh@qnu.edu.vn</a></td></tr>
              <tr><td style="border:none;padding:5px 0;color:var(--text-muted)">Giờ làm việc:</td><td style="border:none;padding:5px 0">7:30 - 11:30 | 13:30 - 17:00</td></tr>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
