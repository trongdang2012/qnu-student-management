<?php
function sortUrl($col, $current_sort, $current_order, $search, $khoa, $nganh, $lop) {
    $order = ($current_sort === $col && $current_order === 'asc') ? 'desc' : 'asc';
    $params = [
        'sort' => $col,
        'order' => $order
    ];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($khoa)) $params['khoa'] = $khoa;
    if (!empty($nganh)) $params['nganh'] = $nganh;
    if (!empty($lop)) $params['lop'] = $lop;
    return '?' . http_build_query($params);
}

function sortIcon($col, $current_sort, $current_order) {
    if ($current_sort !== $col) {
        return '<i class="fas fa-sort" style="margin-left: 5px; opacity: 0.35; font-size: 11px;"></i>';
    }
    return $current_order === 'asc' 
        ? '<i class="fas fa-sort-up" style="margin-left: 5px; color: var(--primary); font-size: 13px; vertical-align: middle;"></i>'
        : '<i class="fas fa-sort-down" style="margin-left: 5px; color: var(--primary); font-size: 13px; vertical-align: middle;"></i>';
}
?>
<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">

    <!-- Page Title -->
    <div class="page-title fade-in">
      <h1><i class="fas fa-users"></i> Quản lý Tài khoản</h1>
    </div>

    <!-- Flash message -->
    <?php $flash = getFlash();
    if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
      <div class="alert alert-danger fade-in"
        style="margin-bottom: 20px; padding: 15px; border-radius: var(--radius-sm); border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24;">
        <ul style="margin: 0; padding-left: 20px;">
          <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Layout 2 cột: Sidebar Cây lọc & Bảng danh sách -->
    <div class="student-layout-grid fade-in" style="display: flex; gap: 20px; align-items: flex-start; margin-top: 15px;">
      
      <!-- Cột trái: Cây Khoa -> Ngành -> Lớp (Sidebar) -->
      <div class="sidebar-tree-card card" style="width: 290px; flex-shrink: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: var(--radius);">
        <div class="card-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 15px 20px;">
          <h3 style="margin: 0; font-size: 16px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-sitemap"></i> Cấu trúc đào tạo
          </h3>
        </div>
        <div class="card-body" style="padding: 15px 12px; background: var(--bg-card);">
          <ul class="tree-menu" style="list-style: none; padding: 0; margin: 0;">
            <!-- Tất cả tài khoản -->
            <li style="margin-bottom: 8px;">
              <a href="?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>" class="tree-item <?= (empty($khoa) && empty($nganh) && empty($lop)) ? 'active' : '' ?>" 
                 style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-sm); text-decoration: none; color: var(--text); font-weight: 600; transition: all 0.2s;">
                <i class="fas fa-users" style="color: var(--primary);"></i> Tất cả tài khoản
              </a>
            </li>
            
            <?php foreach ($facultiesClassesTree as $fName => $majors): ?>
              <?php 
                $hasActiveMajorOrClass = false;
                foreach ($majors as $mName => $classes) {
                    if ($nganh === $mName) {
                        $hasActiveMajorOrClass = true;
                        break;
                    }
                    foreach ($classes as $cName) {
                        if ($lop === $cName) { $hasActiveMajorOrClass = true; break 2; }
                    }
                }
                $isOpen = ($khoa === $fName || $hasActiveMajorOrClass);
              ?>
              <li class="tree-node <?= $isOpen ? 'open' : '' ?>" style="margin-bottom: 8px;">
                <!-- Tên Khoa -->
                <div class="tree-faculty-header" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; background: <?= ($khoa === $fName && empty($nganh) && empty($lop)) ? 'var(--primary-light)' : 'transparent' ?>;"
                     onclick="toggleTreeNode(this)">
                  <a href="?khoa=<?= urlencode($fName) ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>" class="tree-faculty-link" style="text-decoration: none; color: <?= ($khoa === $fName && empty($nganh) && empty($lop)) ? 'var(--primary)' : 'var(--text)' ?>; font-weight: 600; display: flex; align-items: center; gap: 8px; flex: 1;"
                     onclick="event.stopPropagation();">
                    <i class="fas fa-folder-open" style="color: #ffc107; font-size: 15px;"></i> 
                    <span style="font-size: 13.5px;" title="<?= e($fName) ?>"><?= e(mb_strimwidth($fName, 0, 24, '...')) ?></span>
                  </a>
                  <i class="fas fa-chevron-right toggle-icon" style="font-size: 10px; color: var(--text-muted); transition: transform 0.2s; transform: <?= $isOpen ? 'rotate(90deg)' : 'none' ?>;"></i>
                </div>
                
                <!-- Danh sách Ngành -->
                <ul class="tree-classes-list" style="list-style: none; padding-left: 20px; margin-top: 4px; display: <?= $isOpen ? 'block' : 'none' ?>;">
                  <?php foreach ($majors as $mName => $classes): ?>
                    <?php
                       $hasActiveClass = false;
                       foreach ($classes as $cName) {
                           if ($lop === $cName) { $hasActiveClass = true; break; }
                       }
                       $isMajorOpen = ($nganh === $mName || $hasActiveClass);
                    ?>
                    <li class="tree-node <?= $isMajorOpen ? 'open' : '' ?>" style="margin-bottom: 4px;">
                        <div class="tree-faculty-header" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; background: <?= ($nganh === $mName && empty($lop)) ? 'var(--primary-light)' : 'transparent' ?>;" onclick="toggleTreeNode(this)">
                            <a href="?khoa=<?= urlencode($fName) ?>&nganh=<?= urlencode($mName) ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>" class="tree-faculty-link" style="text-decoration: none; color: <?= ($nganh === $mName && empty($lop)) ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: 500; font-size: 13.5px; display: flex; align-items: center; gap: 8px; flex: 1;" onclick="event.stopPropagation();">
                                <i class="fas fa-book-open" style="color: #17a2b8; font-size: 13px;"></i>
                                <span title="<?= e($mName) ?>"><?= e(mb_strimwidth($mName, 0, 20, '...')) ?></span>
                            </a>
                            <i class="fas fa-chevron-right toggle-icon" style="font-size: 10px; color: var(--text-muted); transition: transform 0.2s; transform: <?= $isMajorOpen ? 'rotate(90deg)' : 'none' ?>;"></i>
                        </div>
                        
                        <!-- Danh sách Lớp -->
                        <ul class="tree-classes-list" style="list-style: none; padding-left: 20px; margin-top: 2px; display: <?= $isMajorOpen ? 'block' : 'none' ?>;">
                            <?php foreach ($classes as $cName): ?>
                                <li style="margin-top: 2px;">
                                    <a href="?khoa=<?= urlencode($fName) ?>&nganh=<?= urlencode($mName) ?>&lop=<?= urlencode($cName) ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>" class="tree-item <?= ($lop === $cName) ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: var(--radius-sm); text-decoration: none; color: var(--text-muted); font-size: 13px; font-weight: 400; transition: all 0.2s;">
                                        <i class="fas fa-graduation-cap" style="font-size: 12px; opacity: 0.7;"></i> <?= e($cName) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- Cột phải: Bảng Tài khoản -->
      <div class="card" style="flex: 1; min-width: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: var(--radius);">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: var(--bg-card); border-bottom: 1px solid var(--border);">
          <h3 style="margin: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
            <span id="btnToggleSidebarLeft" style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 4px; transition: background 0.2s;" title="Ẩn/Hiện cấu trúc đào tạo">
              <i class="fas fa-bars" style="color: var(--primary);"></i>
            </span>
            <span>
              <?php if (!empty($lop)): ?>
                Danh sách tài khoản lớp <span style="color: var(--primary); font-weight: 700;"><?= e($lop) ?></span> (<?= $total ?> tài khoản)
              <?php elseif (!empty($nganh)): ?>
                Danh sách tài khoản ngành <span style="color: var(--primary); font-weight: 700;"><?= e($nganh) ?></span> (<?= $total ?> tài khoản)
              <?php elseif (!empty($khoa)): ?>
                Danh sách tài khoản khoa <span style="color: var(--primary); font-weight: 700;"><?= e($khoa) ?></span> (<?= $total ?> tài khoản)
              <?php else: ?>
                Tất cả tài khoản (<?= $total ?> tài khoản)
              <?php endif; ?>
            </span>
          </h3>
          <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>/admin/users/add" class="btn btn-primary btn-sm">
              <i class="fas fa-plus"></i> Thêm tài khoản
            </a>
          </div>
        </div>

        <div class="card-body" style="padding: 20px; background: var(--bg-card);">

          <!-- Search Box -->
          <div style="margin-bottom: 20px;">
            <form method="GET" style="display: flex; gap: 10px;">
              <?php if (!empty($khoa)): ?>
                <input type="hidden" name="khoa" value="<?= e($khoa) ?>">
              <?php endif; ?>
              <?php if (!empty($nganh)): ?>
                <input type="hidden" name="nganh" value="<?= e($nganh) ?>">
              <?php endif; ?>
              <?php if (!empty($lop)): ?>
                <input type="hidden" name="lop" value="<?= e($lop) ?>">
              <?php endif; ?>
              <?php if (!empty($sort)): ?>
                <input type="hidden" name="sort" value="<?= e($sort) ?>">
              <?php endif; ?>
              <?php if (!empty($order)): ?>
                <input type="hidden" name="order" value="<?= e($order) ?>">
              <?php endif; ?>
              <input type="text" name="search" placeholder="Tìm kiếm theo username hoặc ID..." value="<?= e($search) ?>"
                style="flex: 1; padding: 10px 15px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-sans); font-size: 14px;">
              <button type="submit" class="btn btn-primary btn-sm" style="padding: 0 20px;">
                <i class="fas fa-search"></i> Tìm kiếm
              </button>
              <?php if (!empty($search) || !empty($khoa) || !empty($nganh) || !empty($lop)): ?>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary btn-sm" style="padding: 0 15px; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-times"></i> Hủy lọc
                </a>
              <?php endif; ?>
            </form>
          </div>

          <!-- Table -->
          <?php if (count($users) > 0): ?>
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                  <tr style="border-bottom: 2px solid var(--primary);">
                    <th style="padding: 12px; text-align: left; font-weight: 600; width: 80px;">
                      <a href="<?= sortUrl('id', $sort, $order, $search, $khoa, $nganh, $lop) ?>" style="text-decoration: none; color: var(--text); display: inline-flex; align-items: center;">
                        ID <?= sortIcon('id', $sort, $order) ?>
                      </a>
                    </th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">
                      <a href="<?= sortUrl('username', $sort, $order, $search, $khoa, $nganh, $lop) ?>" style="text-decoration: none; color: var(--text); display: inline-flex; align-items: center;">
                        Username <?= sortIcon('username', $sort, $order) ?>
                      </a>
                    </th>
                    <th style="padding: 12px; text-align: left; font-weight: 600; width: 150px;">
                      <a href="<?= sortUrl('role', $sort, $order, $search, $khoa, $nganh, $lop) ?>" style="text-decoration: none; color: var(--text); display: inline-flex; align-items: center;">
                        Role <?= sortIcon('role', $sort, $order) ?>
                      </a>
                    </th>
                    <th style="padding: 12px; text-align: left; font-weight: 600;">
                      <a href="<?= sortUrl('created_at', $sort, $order, $search, $khoa, $nganh, $lop) ?>" style="text-decoration: none; color: var(--text); display: inline-flex; align-items: center;">
                        Ngày tạo <?= sortIcon('created_at', $sort, $order) ?>
                      </a>
                    </th>
                    <th style="padding: 12px; text-align: center; font-weight: 600; width: 180px; color: var(--text-muted); cursor: default;">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid var(--border); transition: background var(--transition);"
                      class="hover-row">
                      <td style="padding: 12px; color: var(--text-muted); font-family: monospace;"><?= $user['id'] ?></td>
                      <td style="padding: 12px;">
                        <strong style="color: var(--text); font-size: 14.5px;"><?= e($user['username']) ?></strong>
                      </td>
                      <td style="padding: 12px;">
                        <span style="display: inline-block; padding: 4px 12px; border-radius: var(--radius-sm); font-size: 12px; font-weight: 600; 
                          background: <?= $user['role'] === 'admin' ? '#ffe8e8' : '#e8f0fb' ?>; 
                          color: <?= $user['role'] === 'admin' ? '#c41e1e' : '#0056B3' ?>;">
                          <?= $user['role'] === 'admin' ? '👤 Admin' : '🎓 Sinh viên' ?>
                        </span>
                      </td>
                      <td style="padding: 12px; font-size: 13.5px; color: var(--text-muted);">
                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                      </td>
                      <td style="padding: 12px; text-align: center;">
                        <a href="<?= BASE_URL ?>/admin/users/edit?id=<?= $user['id'] ?>" class="btn-edit-action">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="<?= BASE_URL ?>/admin/users/delete?id=<?= $user['id'] ?>" class="btn-delete-action"
                          onclick="return confirm('Xóa tài khoản này? (Sinh viên liên kết nếu có cũng sẽ bị xóa)');">
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

                $url_params = '';
                if (!empty($search)) $url_params .= '&search=' . urlencode($search);
                if (!empty($khoa)) $url_params .= '&khoa=' . urlencode($khoa);
                if (!empty($nganh)) $url_params .= '&nganh=' . urlencode($nganh);
                if (!empty($lop)) $url_params .= '&lop=' . urlencode($lop);
                if (!empty($sort)) $url_params .= '&sort=' . urlencode($sort);
                if (!empty($order)) $url_params .= '&order=' . urlencode($order);

                if ($page > 1) {
                  echo '<a href="?page=1' . $url_params . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">« First</a>';
                  echo '<a href="?page=' . ($page - 1) . $url_params . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">‹ Prev</a>';
                }

                for ($i = $start; $i <= $end; $i++) {
                  if ($i == $page) {
                    echo '<span class="btn btn-sm" style="background: var(--primary); color: #fff; padding: 6px 12px;">' . $i . '</span>';
                  } else {
                    echo '<a href="?page=' . $i . $url_params . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">' . $i . '</a>';
                  }
                }

                if ($page < $total_pages) {
                  echo '<a href="?page=' . ($page + 1) . $url_params . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">Next ›</a>';
                  echo '<a href="?page=' . $total_pages . $url_params . '" class="btn btn-sm btn-outline" style="padding: 6px 12px;">Last »</a>';
                }
                ?>
              </div>
            <?php endif; ?>



          <?php else: ?>
            <div
              style="padding: 40px; text-align: center; background: var(--primary-light); border-radius: var(--radius); color: var(--text-muted);">
              <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; display: block;"></i>
              <p>Không có tài khoản nào phù hợp với bộ lọc / từ khóa hiện tại.</p>
            </div>
          <?php endif; ?>

        </div>
      </div>

    </div>

  </div>
</div>

<script>
function toggleTreeNode(el) {
    const node = el.closest('.tree-node');
    const subList = node.querySelector('.tree-classes-list');
    const toggleIcon = el.querySelector('.toggle-icon');
    const folderIcon = el.querySelector('.tree-faculty-link i');
    
    if (subList.style.display === 'none') {
        subList.style.display = 'block';
        toggleIcon.style.transform = 'rotate(90deg)';
        node.classList.add('open');
        if (folderIcon) {
            folderIcon.className = 'fas fa-folder-open';
        }
    } else {
        subList.style.display = 'none';
        toggleIcon.style.transform = 'none';
        node.classList.remove('open');
        if (folderIcon) {
            folderIcon.className = 'fas fa-folder';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar-tree-card');
    const toggleBtn = document.getElementById('btnToggleSidebarLeft');
    
    if (sidebar && toggleBtn) {
        // Kiểm tra trạng thái đã lưu
        const isHidden = localStorage.getItem('users_sidebar_hidden') === 'true';
        
        if (isHidden) {
            sidebar.style.display = 'none';
        }
        
        toggleBtn.addEventListener('click', function() {
            if (sidebar.style.display === 'none') {
                sidebar.style.display = 'block';
                localStorage.setItem('users_sidebar_hidden', 'false');
            } else {
                sidebar.style.display = 'none';
                localStorage.setItem('users_sidebar_hidden', 'true');
            }
        });
    }
});
</script>

<style>
  #btnToggleSidebarLeft:hover {
    background: var(--primary-light);
  }

  .hover-row:hover {
    background: #f8f9fa;
  }
  
  .tree-item {
    transition: all 0.2s;
  }
  .tree-item:hover {
    background: var(--primary-light);
    color: var(--primary) !important;
    padding-left: 15px !important;
  }
  .tree-item.active {
    background: var(--primary) !important;
    color: #fff !important;
    font-weight: 600;
  }
  .tree-faculty-header:hover {
    background: #f1f5fe;
  }
  
  /* Cải tiến CSS cho Sortable Columns */
  thead th a {
      transition: color 0.2s;
  }
  thead th a:hover {
      color: var(--primary) !important;
  }
  
  /* Responsive Layout */
  @media (max-width: 992px) {
      .student-layout-grid {
          flex-direction: column;
      }
      .sidebar-tree-card {
          width: 100% !important;
          margin-bottom: 20px;
      }
  }
</style>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>