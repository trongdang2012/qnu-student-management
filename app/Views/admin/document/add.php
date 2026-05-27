<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/tai-lieu">Tài liệu</a>
        <span>›</span><span>Thêm</span>
      </div>
      <h1>Thêm tài liệu</h1>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/tai-lieu/process-add" enctype="multipart/form-data">
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
              <a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/tai-lieu" style="margin-left:8px;">Hủy</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
