<?php
/**
 * admin/data_sync/index.php - Giao diện Nhập/Xuất CSDL
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$page_title = 'Nhập/Xuất Dữ liệu';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <div class="page-title fade-in">
      <h1><i class="fas fa-database"></i> Nhập / Xuất Dữ liệu CSDL</h1>
      <p style="color:#666;margin:5px 0 0">Sao lưu hoặc phục hồi cơ sở dữ liệu hệ thống</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success" style="margin-bottom:20px;padding:15px;background:#d4edda;color:#155724;border-radius:4px;border:1px solid #c3e6cb">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger" style="margin-bottom:20px;padding:15px;background:#f8d7da;color:#721c24;border-radius:4px;border:1px solid #f5c6cb">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <div class="admin-grid fade-in" style="grid-template-columns: 1fr 1fr;">
      <!-- Khối Export -->
      <div class="card" style="border-top: 4px solid #28a745;">
        <div class="card-header">
          <h3><i class="fas fa-file-export"></i> Xuất dữ liệu (Export)</h3>
        </div>
        <div class="card-body" style="padding:20px">
          <p>Tải về máy tính một bản sao lưu toàn bộ cấu trúc và dữ liệu của hệ thống dưới dạng tệp `.sql`.</p>
          <p style="color:#666; font-size: 0.9em;">(Lưu ý: Tệp sao lưu sẽ chứa toàn bộ tài khoản, điểm số, học phần,... của hệ thống hiện tại).</p>
          <a href="<?= BASE_URL ?>/admin/data_sync/export.php" class="btn btn-success" style="margin-top:15px; display:inline-block">
            <i class="fas fa-download"></i> Tải về tệp SQL
          </a>
        </div>
      </div>

      <!-- Khối Import -->
      <div class="card" style="border-top: 4px solid #dc3545;">
        <div class="card-header">
          <h3><i class="fas fa-file-import"></i> Nhập dữ liệu (Import)</h3>
        </div>
        <div class="card-body" style="padding:20px">
          <p>Phục hồi cơ sở dữ liệu từ một tệp `.sql` đã được sao lưu trước đó hoặc từ máy thành viên khác.</p>
          <div style="padding: 10px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em;">
            <strong>Cảnh báo:</strong> Việc này sẽ XÓA TOÀN BỘ dữ liệu hiện tại và GHI ĐÈ bằng dữ liệu từ tệp tải lên. Hãy chắc chắn trước khi thực hiện.
          </div>
          <form action="<?= BASE_URL ?>/admin/data_sync/import.php" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom:15px;">
              <input type="file" name="sql_file" accept=".sql" required style="display:block; width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
            </div>
            <button type="submit" class="btn btn-danger">
              <i class="fas fa-upload"></i> Tải lên và Phục hồi
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
</body>
</html>
