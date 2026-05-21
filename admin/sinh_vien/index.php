<?php
/**
 * admin/sinh_vien/index.php - Danh sách sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$db = getDB();
$page = (int)($_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Xây dựng query tìm kiếm
$where = "1=1";
$params = [];
if (!empty($search)) {
    $where = "(ma_sv LIKE ? OR ho_ten LIKE ? OR email LIKE ?)";
    $params = ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%'];
}

// Lấy tổng số bản ghi
$sql_count = "SELECT COUNT(*) as total FROM sinh_vien WHERE " . $where;
$stmt = $db->prepare($sql_count);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();
$total_pages = ceil($total / $per_page);

// Lấy danh sách sinh viên
$sql = "SELECT sv.*, u.username FROM sinh_vien sv 
        LEFT JOIN users u ON u.id = sv.user_id
        WHERE " . $where . " 
        ORDER BY sv.ma_sv ASC LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$bind_params = array_merge($params, [$per_page, $offset]);
$types = str_repeat('s', count($params)) . 'ii';
$stmt->bind_param($types, ...$bind_params);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Quản lý Sinh viên';
require_once ROOT . '/includes/admin/header_admin.php';
?>

<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/alerts.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-graduation-cap"></i> Quản lý Sinh viên</h1>
    </div>

    <!-- Card: Danh sách -->
    <div class="card fade-in">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3><i class="fas fa-list"></i> Danh sách sinh viên (<?= $total ?> sinh viên)</h3>
        <a href="<?= BASE_URL ?>/admin/sinh_vien/add.php" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Thêm sinh viên
        </a>
      </div>

      <div class="card-body">
        
        <!-- Search Box -->
        <div style="margin-bottom: 20px;">
          <form method="GET" style="display: flex; gap: 10px;">
            <input 
              type="text" 
              name="search" 
              placeholder="Tìm kiếm theo mã SV, tên hoặc email..." 
              value="<?= e($search) ?>"
              style="flex: 1; padding: 10px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans);"
            >
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fas fa-search"></i> Tìm kiếm
            </button>
            <?php if (!empty($search)): ?>
              <a href="<?= BASE_URL ?>/admin/sinh_vien/index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Xóa tìm kiếm
              </a>
            <?php endif; ?>
          </form>
        </div>

        <!-- Table -->
        <?php if (count($students) > 0): ?>
          <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
              <thead>
                <tr style="background: var(--primary-light); border-bottom: 2px solid var(--primary);">
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Mã SV</th>
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Họ tên</th>
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Email</th>
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Lớp</th>
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Ngành</th>
                  <th style="padding: 12px; text-align: left; font-weight: 600;">Trạng thái</th>
                  <th style="padding: 12px; text-align: center; font-weight: 600;">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $student): ?>
                  <tr style="border-bottom: 1px solid var(--border); transition: background var(--transition);" class="hover-row">
                    <td style="padding: 12px; font-weight: 600;">
                      <span style="background: var(--primary-light); padding: 4px 10px; border-radius: 4px; color: var(--primary);">
                        <?= e($student['ma_sv']) ?>
                      </span>
                    </td>
                    <td style="padding: 12px;">
                      <strong><?= e($student['ho_ten']) ?></strong>
                      <br>
                      <small style="color: var(--text-muted);">@<?= e($student['username'] ?? 'N/A') ?></small>
                    </td>
                    <td style="padding: 12px; font-size: 13px;">
                      <?= e($student['email'] ?? 'N/A') ?>
                    </td>
                    <td style="padding: 12px;">
                      <?= e($student['lop'] ?? 'N/A') ?>
                    </td>
                    <td style="padding: 12px;">
                      <?= e($student['nganh'] ?? 'N/A') ?>
                    </td>
                    <td style="padding: 12px;">
                      <span style="display: inline-block; padding: 4px 10px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; 
                        background: <?php 
                          $status = $student['trang_thai'];
                          if ($status === 'Đang học') echo '#d4edda';
                          elseif ($status === 'Tốt nghiệp') echo '#cce5ff';
                          elseif ($status === 'Tạm dừng') echo '#fff3cd';
                          else echo '#f8d7da';
                        ?>;
                        color: <?php 
                          if ($status === 'Đang học') echo '#155724';
                          elseif ($status === 'Tốt nghiệp') echo '#004085';
                          elseif ($status === 'Tạm dừng') echo '#856404';
                          else echo '#721c24';
                        ?>;">
                        <?= e($status) ?>
                      </span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                      <a href="<?= BASE_URL ?>/admin/sinh_vien/edit.php?id=<?= $student['id'] ?>" class="btn btn-sm" style="background: #17a2b8; color: #fff;">
                        <i class="fas fa-edit"></i> Sửa
                      </a>
                      <a href="<?= BASE_URL ?>/admin/sinh_vien/process_delete.php?id=<?= $student['id'] ?>" class="btn btn-sm" style="background: #dc3545; color: #fff;" onclick="return confirm('Xóa sinh viên này? (Sẽ xóa tài khoản liên kết)');">
                        <i class="fas fa-trash"></i> Xóa
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center; gap: 5px; flex-wrap: wrap;">
              <?php
              $start = max(1, $page - 2);
              $end = min($total_pages, $page + 2);
              
              if ($page > 1) {
                echo '<a href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">« First</a>';
                echo '<a href="?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">‹ Prev</a>';
              }
              
              for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                  echo '<span class="btn btn-sm" style="background: var(--primary); color: #fff; padding: 6px 12px;">' . $i . '</span>';
                } else {
                  echo '<a href="?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">' . $i . '</a>';
                }
              }
              
              if ($page < $total_pages) {
                echo '<a href="?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">Next ›</a>';
                echo '<a href="?page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">Last »</a>';
              }
              ?>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <div style="padding: 40px; text-align: center; background: var(--primary-light); border-radius: var(--radius); color: var(--text-muted);">
            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; display: block;"></i>
            <p>Không có sinh viên nào <?php if (!empty($search)) echo 'phù hợp với tìm kiếm'; ?></p>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

<style>
  .hover-row:hover {
    background: #f8f9fa;
  }
</style>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
