<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><span>Tài liệu</span>
      </div>
      <h1><i class="fas fa-book"></i> Quản lý Tài liệu</h1>
      <p>Quản lý tài liệu nội bộ: thêm / sửa / xóa và tải xuống.</p>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h3>Danh sách tài liệu</h3>
        <div>
          <a class="btn btn-success" href="<?= BASE_URL ?>/admin/tai-lieu/add"><i class="fas fa-plus"></i> Thêm tài liệu</a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Tiêu đề</th>
                <th>Mô tả</th>
                <th>Người đăng</th>
                <th>Chế độ</th>
                <th>File</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($items)): ?>
                <tr><td colspan="8" class="text-center text-muted">Chưa có tài liệu nào.</td></tr>
              <?php else: ?>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td><?= e($item['id']) ?></td>
                    <td><?= e($item['tieu_de']) ?></td>
                    <td style="max-width:360px;"><?= e($item['mo_ta'] ?? '') ?></td>
                    <td><?= e($item['ho_ten'] ?: 'Admin') ?></td>
                    <td><?= ($item['is_public'] ?? 1) ? '<span class="badge badge-success">Công khai</span>' : '<span class="badge badge-warning">Riêng tư</span>' ?></td>
                    <td>
                      <?php if (!empty($item['duong_dan'])): ?>
                        <a href="<?= BASE_URL ?>/admin/tai-lieu/download?file=<?= rawurlencode(basename($item['duong_dan'])) ?>" target="_blank"><i class="fas fa-download"></i> Tải</a>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td><?= e($item['ngay_dang'] ?? '') ?></td>
                    <td class="table-actions">
                      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/admin/tai-lieu/edit?id=<?= e($item['id']) ?>">Sửa</a>
                      <form method="POST" action="<?= BASE_URL ?>/admin/tai-lieu/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa tài liệu này?');">
                        <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Xóa</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
