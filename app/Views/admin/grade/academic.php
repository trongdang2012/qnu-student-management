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

    <!-- Layout 2 cột: Sidebar Cây lọc & Bảng danh sách -->
    <div class="student-layout-grid fade-in" style="display: flex; gap: 20px; align-items: flex-start; margin-top: 15px;">
      
      <!-- Cột trái: Cây Khoa -> Ngành -> Lớp (Sidebar) -->
      <div class="sidebar-tree-card card" style="width: 290px; flex-shrink: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: var(--radius); margin-bottom:0;">
        <div class="card-header" style="background: var(--bg-card); border-bottom: 1px solid var(--border); padding: 15px 20px;">
          <h3 style="margin: 0; font-size: 16px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-sitemap"></i> Cấu trúc đào tạo
          </h3>
        </div>
        <div class="card-body" style="padding: 15px 12px; background: var(--bg-card);">
          <ul class="tree-menu" style="list-style: none; padding: 0; margin: 0;">
            <!-- Tất cả học phần -->
            <li style="margin-bottom: 8px;">
              <a href="?search=<?= urlencode($search) ?>&hoc_ky=<?= (int)$hoc_ky ?>&loai=<?= urlencode($loai) ?>" class="tree-item <?= (empty($khoa) && empty($nganh) && empty($lop)) ? 'active' : '' ?>" 
                 style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius-sm); text-decoration: none; color: var(--text); font-weight: 600; transition: all 0.2s;">
                <i class="fas fa-book-open" style="color: var(--primary);"></i> Tất cả học phần
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
                  <a href="?khoa=<?= urlencode($fName) ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= (int)$hoc_ky ?>&loai=<?= urlencode($loai) ?>" class="tree-faculty-link" style="text-decoration: none; color: <?= ($khoa === $fName && empty($nganh) && empty($lop)) ? 'var(--primary)' : 'var(--text)' ?>; font-weight: 600; display: flex; align-items: center; gap: 8px; flex: 1;"
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
                            <a href="?khoa=<?= urlencode($fName) ?>&nganh=<?= urlencode($mName) ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= (int)$hoc_ky ?>&loai=<?= urlencode($loai) ?>" class="tree-faculty-link" style="text-decoration: none; color: <?= ($nganh === $mName && empty($lop)) ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: 500; font-size: 13.5px; display: flex; align-items: center; gap: 8px; flex: 1;" onclick="event.stopPropagation();">
                                <i class="fas fa-book-open" style="color: #17a2b8; font-size: 13px;"></i>
                                <span title="<?= e($mName) ?>"><?= e(mb_strimwidth($mName, 0, 20, '...')) ?></span>
                            </a>
                            <i class="fas fa-chevron-right toggle-icon" style="font-size: 10px; color: var(--text-muted); transition: transform 0.2s; transform: <?= $isMajorOpen ? 'rotate(90deg)' : 'none' ?>;"></i>
                        </div>
                        
                        <!-- Danh sách Lớp -->
                        <ul class="tree-classes-list" style="list-style: none; padding-left: 20px; margin-top: 2px; display: <?= $isMajorOpen ? 'block' : 'none' ?>;">
                            <?php foreach ($classes as $cName): ?>
                                <li style="margin-top: 2px;">
                                    <a href="?khoa=<?= urlencode($fName) ?>&nganh=<?= urlencode($mName) ?>&lop=<?= urlencode($cName) ?>&search=<?= urlencode($search) ?>&hoc_ky=<?= (int)$hoc_ky ?>&loai=<?= urlencode($loai) ?>" class="tree-item <?= ($lop === $cName) ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: var(--radius-sm); text-decoration: none; color: var(--text-muted); font-size: 13px; font-weight: 400; transition: all 0.2s;">
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

      <!-- Cột phải: Bảng Học phần -->
      <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 15px;">
        <div class="card" style="box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: var(--radius); margin-bottom: 0;">
          <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: var(--bg-card); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
              <span id="btnToggleSidebarLeft" style="cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 4px; transition: background 0.2s;" title="Ẩn/Hiện cấu trúc đào tạo">
                <i class="fas fa-bars" style="color: var(--primary);"></i>
              </span>
              <span>
                <?php if (!empty($lop)): ?>
                  Học phần có sinh viên lớp <span style="color: var(--primary); font-weight: 700;"><?= e($lop) ?></span> đăng ký (<?= count($list_hp) ?> học phần)
                <?php elseif (!empty($nganh)): ?>
                  Học phần có sinh viên ngành <span style="color: var(--primary); font-weight: 700;"><?= e($nganh) ?></span> đăng ký (<?= count($list_hp) ?> học phần)
                <?php elseif (!empty($khoa)): ?>
                  Học phần có sinh viên khoa <span style="color: var(--primary); font-weight: 700;"><?= e($khoa) ?></span> đăng ký (<?= count($list_hp) ?> học phần)
                <?php else: ?>
                  Tất cả học phần đăng ký điểm (<?= count($list_hp) ?> học phần)
                <?php endif; ?>
              </span>
            </h3>
          </div>
          <div class="card-body" style="padding:16px; background: var(--bg-card);">
            <form method="GET" class="action-bar" style="align-items:flex-end;margin-bottom:0; flex-wrap: wrap; gap: 10px;">
              <?php if (!empty($khoa)): ?>
                <input type="hidden" name="khoa" value="<?= e($khoa) ?>">
              <?php endif; ?>
              <?php if (!empty($nganh)): ?>
                <input type="hidden" name="nganh" value="<?= e($nganh) ?>">
              <?php endif; ?>
              <?php if (!empty($lop)): ?>
                <input type="hidden" name="lop" value="<?= e($lop) ?>">
              <?php endif; ?>
              
              <div class="form-group search-box" style="margin:0; flex: 1; min-width: 200px;">
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
              <?php if (!empty($search) || $hoc_ky > 0 || !empty($loai) || !empty($khoa) || !empty($nganh) || !empty($lop)): ?>
                <a href="<?= BASE_URL ?>/admin/diem/hoc-tap" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Hủy lọc</a>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <div class="card" style="box-shadow: 0 4px 20px rgba(0,0,0,0.05); border-radius: var(--radius); margin-top: 0;">
          <div class="card-body" style="padding:0; background: var(--bg-card);">
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
                          <a href="<?= BASE_URL ?>/admin/diem/hoc-tap?action=edit&hoc_phan_id=<?= (int)$hp['id'] ?>&khoa=<?= urlencode($khoa) ?>&nganh=<?= urlencode($nganh) ?>&lop=<?= urlencode($lop) ?>" class="btn btn-sm btn-primary">
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
        const isHidden = localStorage.getItem('grade_sidebar_hidden') === 'true';
        
        if (isHidden) {
            sidebar.style.display = 'none';
        }
        
        toggleBtn.addEventListener('click', function() {
            if (sidebar.style.display === 'none') {
                sidebar.style.display = 'block';
                localStorage.setItem('grade_sidebar_hidden', 'false');
            } else {
                sidebar.style.display = 'none';
                localStorage.setItem('grade_sidebar_hidden', 'true');
            }
        });
    }
});
</script>

<style>
  #btnToggleSidebarLeft:hover {
    background: var(--primary-light);
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
