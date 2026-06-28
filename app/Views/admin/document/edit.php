<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    <div class="page-title">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span><a href="<?= BASE_URL ?>/admin/tai-lieu">Tài liệu</a>
        <span>›</span><span>Sửa</span>
      </div>
      <h1>Sửa tài liệu</h1>
    </div>

    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/tai-lieu/process-edit" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
          <input type="hidden" name="id" value="<?= e($item['id']) ?>">
          <div class="form-row full">
            <div class="form-group">
              <label>Tiêu đề</label>
              <input type="text" name="title" class="form-control" required value="<?= e($item['tieu_de'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Mô tả</label>
              <textarea name="description" class="form-control" rows="4"><?= e($item['mo_ta'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Chế độ chia sẻ</label>
              <select name="is_public" class="form-control">
                <option value="1" <?= (!empty($item['is_public']) ? 'selected' : '') ?>>Công khai</option>
                <option value="0" <?= (empty($item['is_public']) ? 'selected' : '') ?>>Riêng tư</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>File hiện tại</label>
              <div>
                <?php if (!empty($item['duong_dan'])): ?>
                  <a href="<?= BASE_URL ?>/admin/tai-lieu/download?file=<?= rawurlencode(basename($item['duong_dan'])) ?>" target="_blank"><?= basename($item['duong_dan']) ?></a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </div>
            </div>
            <div class="form-group">
              <label>Thay file (tùy chọn)</label>
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
