<?php
/**
 * includes/admin/alerts.php - Hiển thị alerts (success/error)
 * Gọi vào sau navbar_admin.php
 */
?>
<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success" style="margin: 20px auto; max-width: 1400px; padding: 16px 20px; background: #d4edda; border-left: 4px solid #28a745; border-radius: var(--radius-sm); color: #155724; display: flex; align-items: center; gap: 12px;">
    <i class="fas fa-check-circle" style="font-size: 18px; flex-shrink: 0;"></i>
    <span><?= e($_SESSION['success']) ?></span>
    <button onclick="this.parentElement.style.display='none'" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; color: #155724; opacity: 0.7;"><i class="fas fa-times"></i></button>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
  <div style="margin: 20px auto; max-width: 1400px;">
    <?php foreach ($_SESSION['errors'] as $error): ?>
      <div class="alert alert-danger" style="margin-bottom: 10px; padding: 16px 20px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: var(--radius-sm); color: #721c24; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-exclamation-circle" style="font-size: 18px; flex-shrink: 0;"></i>
        <span><?= e($error) ?></span>
        <button onclick="this.parentElement.style.display='none'" style="margin-left: auto; background: none; border: none; cursor: pointer; font-size: 18px; color: #721c24; opacity: 0.7;"><i class="fas fa-times"></i></button>
      </div>
    <?php endforeach; ?>
  </div>
  <?php unset($_SESSION['errors']); ?>
<?php endif; ?>
