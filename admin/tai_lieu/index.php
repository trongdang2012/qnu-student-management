<?php
/**
 * admin/tai_lieu/index.php - danh sách tài liệu
 */
define('ROOT', __DIR__ . '/../../');
require_once ROOT . 'config/constants.php';
require_once ROOT . 'includes/session.php';

requireAdmin();

$dataFile = __DIR__ . '/data.json';
$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

$items = [];
if (file_exists($dataFile)) {
    $items = json_decode(file_get_contents($dataFile), true) ?: [];
}

$page_title = 'Tài liệu';
require_once ROOT . 'includes/admin/header_admin.php';
?>

<?php require_once ROOT . 'includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
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
          <a class="btn btn-success" href="<?= BASE_URL ?>/admin/tai_lieu/add.php"><i class="fas fa-plus"></i> Thêm tài liệu</a>
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
                <th>File</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($items)): ?>
                <tr><td colspan="6" class="text-center text-muted">Chưa có tài liệu nào.</td></tr>
              <?php else: ?>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td><?= e($item['id']) ?></td>
                    <td><?= e($item['title']) ?></td>
                    <td style="max-width:360px;"><?= e($item['description'] ?? '') ?></td>
                    <td>
                      <?php if (!empty($item['file'])): ?>
                        <a href="<?= BASE_URL ?>/admin/tai_lieu/uploads/<?= rawurlencode(basename($item['file'])) ?>" target="_blank"><i class="fas fa-download"></i> Tải</a>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td><?= e($item['created_at'] ?? '') ?></td>
                    <td class="table-actions">
                      <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>/admin/tai_lieu/edit.php?id=<?= e($item['id']) ?>">Sửa</a>
                      <form method="POST" action="<?= BASE_URL ?>/admin/tai_lieu/delete.php" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa tài liệu này?');">
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

<?php require_once ROOT . 'includes/admin/footer_admin.php'; ?>
