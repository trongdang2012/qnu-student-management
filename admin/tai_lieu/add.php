<?php
/**
 * admin/tai_lieu/add.php - thêm tài liệu
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '') {
        setFlash('danger', 'Tiêu đề là bắt buộc.');
        header('Location: ' . BASE_URL . '/admin/tai_lieu/add.php');
        exit;
    }

    $filePath = '';
    if (!empty($_FILES['file']['name'])) {
        $up = $_FILES['file'];
        if ($up['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
            $base = time() . '_' . bin2hex(random_bytes(6));
            $fname = $base . ($ext ? '.' . $ext : '');
            $target = $uploadsDir . DIRECTORY_SEPARATOR . $fname;
            if (move_uploaded_file($up['tmp_name'], $target)) {
                $filePath = 'uploads/' . $fname;
            }
        }
    }

    $items = [];
    if (file_exists($dataFile)) {
        $items = json_decode(file_get_contents($dataFile), true) ?: [];
    }
    $id = $items ? (max(array_column($items, 'id')) + 1) : 1;
    $item = [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'file' => $filePath,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $items[] = $item;
    file_put_contents($dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    setFlash('success', 'Thêm tài liệu thành công.');
    header('Location: ' . BASE_URL . '/admin/tai_lieu/index.php');
    exit;
}

$page_title = 'Thêm tài liệu';
require_once ROOT . 'includes/admin/header_admin.php';
?>

<?php require_once ROOT . 'includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/tai_lieu/index.php">Tài liệu</a>
        <span>›</span><span>Thêm</span>
      </div>
      <h1>Thêm tài liệu</h1>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="form-row full">
            <div class="form-group">
              <label>Tiêu đề</label>
              <input type="text" name="title" class="form-control" required>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Mô tả</label>
              <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>File (tùy chọn)</label>
              <input type="file" name="file" class="form-control">
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;">
              <button class="btn btn-primary" type="submit">Lưu</button>
              <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/tai_lieu/index.php" style="margin-left:8px;">Hủy</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . 'includes/admin/footer_admin.php'; ?>
