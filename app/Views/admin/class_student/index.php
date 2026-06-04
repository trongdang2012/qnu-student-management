<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<style>
.list-card {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-top: 20px;
}
.table-responsive { overflow-x: auto; }
.badge-count {
  background: var(--primary-light);
  color: var(--primary);
  padding: 3px 8px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}
.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  padding: 14px 20px;
  background: #f0f4ff;
  border-bottom: 1px solid var(--border);
}
.filter-bar label {
  font-weight: 500;
  font-size: 13px;
  color: var(--text-muted);
  white-space: nowrap;
}
.filter-bar select {
  min-width: 180px;
  flex: 1;
  font-size: 14px;
}
/* Modal styles */
.crud-modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.45);
  z-index: 1000;
  justify-content: center;
  align-items: center;
  animation: fadeIn .2s;
}
.crud-modal-overlay.active { display: flex; }
.crud-modal {
  background: var(--white);
  border-radius: 12px;
  width: 480px;
  max-width: 92vw;
  box-shadow: 0 20px 60px rgba(0,0,0,.25);
  animation: slideUp .25s ease;
}
.crud-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, var(--primary), #2563eb);
  color: #fff;
  border-radius: 12px 12px 0 0;
}
.crud-modal-header h3 { margin: 0; font-size: 16px; }
.crud-modal-header .close-btn {
  background: rgba(255,255,255,.2);
  border: none;
  color: #fff;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  font-size: 18px;
  cursor: pointer;
  transition: background .2s;
}
.crud-modal-header .close-btn:hover { background: rgba(255,255,255,.35); }
.crud-modal-body { padding: 24px; }
.crud-modal-body .form-group { margin-bottom: 16px; }
.crud-modal-body .form-group:last-child { margin-bottom: 0; }
.crud-modal-footer {
  padding: 14px 24px;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background: #f9fafb;
  border-radius: 0 0 12px 12px;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <!-- Tiêu đề trang -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/dashboard"><i class="fas fa-home"></i> Admin</a>
        <span>›</span>
        <span>Quản lý đào tạo</span>
        <span>›</span>
        <span>Lớp sinh hoạt</span>
      </div>
      <h1><i class="fas fa-users"></i> Quản lý danh mục Lớp sinh hoạt</h1>
      <p>Quản lý các lớp học sinh hoạt của sinh viên và gán chúng vào các Ngành tương ứng.</p>
    </div>

    <!-- Thông báo flash -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> fade-in" style="margin-top: 15px;">
        <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="list-card fade-in">
      <!-- Thanh công cụ -->
      <div class="card-header" style="background: #fafafa; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid var(--border);">
        <div style="display: flex; align-items: center; gap: 10px;">
          <h3 style="margin: 0; color: var(--primary);">Danh sách Lớp sinh hoạt</h3>
          <span class="badge-count"><?= $total ?> lớp</span>
          <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Thêm Lớp
          </button>
        </div>
        
        <form method="GET" style="display: flex; gap: 10px;">
          <?php if ($filter_khoa > 0): ?>
            <input type="hidden" name="khoa_id" value="<?= $filter_khoa ?>">
          <?php endif; ?>
          <?php if ($filter_nganh > 0): ?>
            <input type="hidden" name="nganh_id" value="<?= $filter_nganh ?>">
          <?php endif; ?>
          <input type="text" name="search" class="form-control" placeholder="Tìm lớp, ngành, khoa..." value="<?= e($search) ?>" style="width: 220px; font-size: 14px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Tìm</button>
          <?php if (!empty($search) || $filter_khoa > 0 || $filter_nganh > 0): ?>
            <a href="<?= BASE_URL ?>/admin/lop-sinh-hoat" class="btn btn-secondary btn-sm" title="Xóa bộ lọc"><i class="fas fa-sync-alt"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <!-- Bộ lọc theo Khoa và Ngành -->
      <div class="filter-bar">
        <label><i class="fas fa-filter"></i> Lọc:</label>
        <select class="form-control" id="filter-khoa" onchange="applyKhoaFilter()">
          <option value="0">-- Tất cả khoa --</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= $f['id'] ?>" <?= $filter_khoa == $f['id'] ? 'selected' : '' ?>><?= e($f['ten_khoa']) ?></option>
          <?php endforeach; ?>
        </select>
        <select class="form-control" id="filter-nganh" onchange="applyNganhFilter()">
          <option value="0">-- Tất cả ngành --</option>
          <?php foreach ($majors as $m): ?>
            <option value="<?= $m['id'] ?>" data-khoa="<?= $m['khoa_id'] ?>" <?= $filter_nganh == $m['id'] ? 'selected' : '' ?>><?= e($m['ten_nganh']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th style="width: 80px; text-align: center;">STT</th>
              <th>Tên lớp</th>
              <th>Ngành học</th>
              <th>Khoa</th>
              <th style="width: 180px; text-align: center;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($classes)): ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  <i class="fas fa-folder-open" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                  Không tìm thấy lớp học nào.
                </td>
              </tr>
            <?php else: ?>
              <?php 
              $stt = ($page - 1) * 15 + 1;
              foreach ($classes as $c): 
              ?>
                <tr class="hover-row">
                  <td style="text-align: center; font-weight: 500; color: var(--text-muted);"><?= $stt++ ?></td>
                  <td style="font-weight: 700; color: var(--primary);"><?= e($c['ten_lop']) ?></td>
                  <td style="font-weight: 500; color: var(--text);"><?= e($c['ten_nganh']) ?></td>
                  <td>
                    <span class="badge" style="background: #e8f0fb; color: var(--primary); font-size: 12px; font-weight: 500; padding: 4px 10px;">
                      <?= e($c['ten_khoa'] ?? 'Chưa rõ') ?>
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 8px;">
                      <button type="button" class="btn btn-outline btn-sm" onclick="editClass(<?= $c['id'] ?>, '<?= e($c['ten_lop']) ?>', <?= $c['nganh_id'] ?>)">
                        <i class="fas fa-edit"></i> Sửa
                      </button>
                      <form action="<?= BASE_URL ?>/admin/lop-sinh-hoat/delete" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lớp học này? Mọi sinh viên thuộc lớp sẽ không còn lớp sinh hoạt!');" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">
                          <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Phân trang -->
      <?php if ($total_pages > 1): ?>
        <div class="card-footer" style="display: flex; justify-content: center; background: var(--white);">
          <div class="pagination" style="display: flex; gap: 5px;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <?php
                $paginationParams = ['page' => $i];
                if (!empty($search)) $paginationParams['search'] = $search;
                if ($filter_khoa > 0) $paginationParams['khoa_id'] = $filter_khoa;
                if ($filter_nganh > 0) $paginationParams['nganh_id'] = $filter_nganh;
              ?>
              <a href="?<?= http_build_query($paginationParams) ?>" class="btn <?= $page === $i ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="min-width: 32px; justify-content: center;">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- MODAL THÊM/SỬA LỚP -->
<div class="crud-modal-overlay" id="classModal">
  <div class="crud-modal">
    <div class="crud-modal-header">
      <h3 id="modal-title"><i class="fas fa-plus-circle"></i> Thêm Lớp mới</h3>
      <button class="close-btn" type="button" onclick="closeModal()">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/lop-sinh-hoat/save" method="POST" id="class-form">
      <input type="hidden" name="id" id="class-id" value="0">
      <div class="crud-modal-body">
        <div class="form-group">
          <label for="ten_lop" style="font-weight: 500; display: block; margin-bottom: 8px;">Tên lớp sinh hoạt <span style="color: red;">*</span></label>
          <input type="text" name="ten_lop" id="ten_lop" class="form-control" placeholder="VD: CNTT47A" required style="width: 100%;">
        </div>
        <div class="form-group">
          <label for="nganh_id" style="font-weight: 500; display: block; margin-bottom: 8px;">Thuộc ngành học <span style="color: red;">*</span></label>
          <select name="nganh_id" id="nganh_id" class="form-control" required style="width: 100%;">
            <option value="">-- Chọn Ngành --</option>
            <?php foreach ($majors as $m): ?>
              <option value="<?= $m['id'] ?>"><?= e($m['ten_nganh']) ?> (Khoa: <?= e($m['ten_khoa']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="crud-modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu lại</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm Lớp mới';
  document.getElementById('class-id').value = '0';
  document.getElementById('ten_lop').value = '';
  document.getElementById('nganh_id').value = '';
  document.getElementById('classModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_lop').focus(), 200);
}

function editClass(id, name, nganh_id) {
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa Lớp';
  document.getElementById('class-id').value = id;
  document.getElementById('ten_lop').value = name;
  document.getElementById('nganh_id').value = nganh_id;
  document.getElementById('classModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_lop').focus(), 200);
}

function closeModal() {
  document.getElementById('classModal').classList.remove('active');
}

document.getElementById('classModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Filter logic
function applyKhoaFilter() {
  const khoaId = document.getElementById('filter-khoa').value;
  const params = new URLSearchParams();
  if (khoaId > 0) params.set('khoa_id', khoaId);
  // Reset nganh when khoa changes
  window.location.href = '<?= BASE_URL ?>/admin/lop-sinh-hoat?' + params.toString();
}

function applyNganhFilter() {
  const nganhId = document.getElementById('filter-nganh').value;
  const khoaId = document.getElementById('filter-khoa').value;
  const params = new URLSearchParams();
  if (khoaId > 0) params.set('khoa_id', khoaId);
  if (nganhId > 0) params.set('nganh_id', nganhId);
  window.location.href = '<?= BASE_URL ?>/admin/lop-sinh-hoat?' + params.toString();
}

// Lọc dropdown ngành theo khoa đã chọn
function filterNganhDropdown() {
  const khoaId = document.getElementById('filter-khoa').value;
  const nganhSelect = document.getElementById('filter-nganh');
  const options = nganhSelect.querySelectorAll('option[data-khoa]');
  
  options.forEach(opt => {
    if (khoaId == 0 || opt.dataset.khoa == khoaId) {
      opt.style.display = '';
    } else {
      opt.style.display = 'none';
      if (opt.selected) {
        nganhSelect.value = '0';
      }
    }
  });
}

// Init filter on page load
filterNganhDropdown();
document.getElementById('filter-khoa').addEventListener('change', function() {
  // Khi chỉ thay đổi mà chưa apply, lọc dropdown trước
  filterNganhDropdown();
});
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
