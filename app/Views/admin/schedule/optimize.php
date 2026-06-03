<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu">Thời khóa biểu</a>
        <span>›</span><span>Xếp tự động</span>
      </div>
      <h1><i class="fas fa-wand-magic-sparkles"></i> Xếp thời khóa biểu tự động</h1>
      <p>Thuật toán ưu tiên lịch gọn, tránh trùng sinh viên/phòng và dùng dữ liệu đăng ký học phần QNU.</p>
    </div>

    <div class="admin-grid fade-in">
      <div class="stat-card">
        <i class="fas fa-book-open"></i>
        <div>
          <h3>Học phần đã duyệt</h3>
          <div class="stat-value"><?= count($courseGroups) ?></div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-users"></i>
        <div>
          <h3>Sinh viên có đăng ký</h3>
          <div class="stat-value"><?= $studentCount ?></div>
        </div>
      </div>
      <div class="stat-card">
        <i class="fas fa-calendar-day"></i>
        <div>
          <h3>Lịch hiện có</h3>
          <div class="stat-value"><?= $existingCount ?></div>
        </div>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-header">
        <h3><i class="fas fa-sliders"></i> Tham số xếp lịch</h3>
      </div>
      <div class="card-body" style="padding:20px">
        <form method="GET" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/optimize" class="action-bar" style="align-items:flex-end">
          <div class="form-group" style="margin:0;min-width:130px">
            <label>Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <?php for ($hk = 1; $hk <= 8; $hk++): ?>
                <option value="<?= $hk ?>" <?= $hocKy === $hk ? 'selected' : '' ?>>HK<?= $hk ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:160px">
            <label>Năm học</label>
            <input type="text" name="nam_hoc" class="form-control" value="<?= e($namHoc) ?>">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Xem dữ liệu</button>
          <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
          </a>
        </form>

        <div style="background:#f7f9fc;border-left:none;border-radius:6px;padding:14px;margin-top:14px">
          <strong>Luật tối ưu:</strong>
          gom sinh viên theo học phần, tránh trùng lịch sinh viên, tránh trùng phòng, ưu tiên thứ 2-6 và buổi sáng, hạn chế dồn quá nhiều tiết vào một ngày.
        </div>
      </div>
    </div>

    <?php if (!$courseGroups): ?>
      <div class="alert alert-info fade-in">
        <i class="fas fa-info-circle"></i>
        Chưa có đăng ký học phần đã duyệt cho HK<?= $hocKy ?> / <?= e($namHoc) ?>.
      </div>
    <?php else: ?>
      <div class="card fade-in">
        <div class="card-header">
          <h3><i class="fas fa-list-check"></i> Dữ liệu sẽ xếp</h3>
        </div>
        <div class="card-body" style="padding:0">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Học phần</th>
                  <th style="text-align:center">SV đăng ký</th>
                  <th style="text-align:center">Số tiết/buổi</th>
                  <th>Gợi ý ban đầu</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($preview as $row): ?>
                  <tr>
                    <td><code><?= e($row['ma_hp']) ?></code><br><small><?= e($row['ten_hp']) ?></small></td>
                    <td style="text-align:center"><?= (int)$row['students'] ?></td>
                    <td style="text-align:center"><?= (int)$row['length'] ?></td>
                    <td><?= e($row['hint']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card fade-in">
        <div class="card-body" style="padding:20px">
          <form method="POST" action="<?= BASE_URL ?>/admin/thoi-khoa-bieu/optimize/save" onsubmit="return confirm('Bắt đầu xếp thời khóa biểu tự động?')">
            <input type="hidden" name="action" value="optimize">
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
              <input type="checkbox" name="replace_existing" value="1">
              Ghi đè toàn bộ lịch HK<?= $hocKy ?> / <?= e($namHoc) ?> trước khi xếp lại
            </label>
            <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
              <a href="<?= BASE_URL ?>/admin/thoi-khoa-bieu?hoc_ky=<?= $hocKy ?>&nam_hoc=<?= urlencode($namHoc) ?>" class="btn btn-secondary">Hủy</a>
              <button type="submit" class="btn btn-success" style="padding:10px 28px">
                <i class="fas fa-bolt"></i> Xếp lịch ngay
              </button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
