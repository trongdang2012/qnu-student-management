<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Điểm học tập</span>
      </div>
      <h1><i class="fas fa-graduation-cap"></i> Nhập điểm học tập</h1>
      <p>Chọn học phần để nhập điểm chuyên cần, giữa kỳ, cuối kỳ cho sinh viên.</p>
    </div>

    <?php if ($flash = getFlash()): ?>
      <div class="alert alert-<?= e($flash['type']) ?> fade-in">
        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= $flash['msg'] ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-body" style="padding:16px">
        <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0">
          <div class="form-group search-box" style="margin:0">
            <label style="font-size:12px">Tìm kiếm học phần</label>
            <input type="text" name="search" class="form-control" placeholder="Mã, tên môn học hoặc niên khóa..." value="<?= e($search) ?>">
          </div>
          <div class="form-group" style="margin:0;min-width:130px">
            <label style="font-size:12px">Học kỳ</label>
            <select name="hoc_ky" class="form-control">
              <option value="0">Tất cả</option>
              <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= $hoc_ky === $i ? 'selected' : '' ?>>Học kỳ <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group" style="margin:0;min-width:150px">
            <label style="font-size:12px">Loại môn học</label>
            <select name="loai" class="form-control">
              <option value="">Tất cả</option>
              <?php foreach (['Bắt buộc', 'Tự chọn', 'Đại cương'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $loai === $l ? 'selected' : '' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
          <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
        </form>
      </div>
    </div>

    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <?php if (empty($list_hp)): ?>
          <div style="padding:40px;text-align:center;color:#777">
            <i class="fas fa-book-open" style="font-size:42px;margin-bottom:12px;display:block"></i>
            Không tìm thấy học phần nào phù hợp.
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Mã HP</th>
                  <th>Tên học phần</th>
                  <th style="text-align:center">Tín chỉ</th>
                  <th>Loại</th>
                  <th style="text-align:center">Học kỳ</th>
                  <th style="text-align:center">Số SV ĐK</th>
                  <th style="text-align:center">Trạng thái điểm</th>
                  <th style="text-align:center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list_hp as $hp): ?>
                  <tr>
                    <td><code><?= e($hp['ma_hp']) ?></code></td>
                    <td style="font-weight: 500"><?= e($hp['ten_hp']) ?></td>
                    <td style="text-align:center"><?= (int)$hp['so_tin_chi'] ?></td>
                    <td>
                      <span class="badge badge-<?= $hp['loai']==='Bắt buộc'?'danger':($hp['loai']==='Tự chọn'?'warning':'info') ?>">
                        <?= e($hp['loai']) ?>
                      </span>
                    </td>
                    <td style="text-align:center">HK <?= (int)$hp['hoc_ky'] ?></td>
                    <td style="text-align:center; font-weight: bold; color: var(--primary)">
                      <?= (int)$hp['si_so_dk'] ?>
                    </td>
                    <td style="text-align:center">
                      <?php if ($hp['si_so_dk'] == 0): ?>
                        <span class="badge badge-secondary" style="background:#bbb">Chưa có SV</span>
                      <?php elseif ($hp['so_sv_co_diem'] == 0): ?>
                        <span class="badge badge-warning" style="background:#ffc107;color:#333"><i class="fas fa-clock"></i> Chưa nhập</span>
                      <?php elseif ($hp['so_sv_co_diem'] < $hp['si_so_dk']): ?>
                        <span class="badge badge-info" style="background:#17a2b8"><i class="fas fa-spinner"></i> Đang nhập (<?= $hp['so_sv_co_diem'] ?>/<?= $hp['si_so_dk'] ?>)</span>
                      <?php else: ?>
                        <span class="badge badge-success" style="background:#28a745"><i class="fas fa-check-double"></i> Đã nhập đủ</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                      <a href="<?= BASE_URL ?>/admin/diem/hoc-tap?action=edit&hoc_phan_id=<?= (int)$hp['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Nhập điểm
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
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
