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
        <span>Ngành học</span>
      </div>
      <h1><i class="fas fa-graduation-cap"></i> Quản lý danh mục Ngành học</h1>
      <p>Quản lý các ngành đào tạo và gắn chúng vào các Khoa tương ứng.</p>
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
          <h3 style="margin: 0; color: var(--primary);">Danh sách Ngành</h3>
          <span class="badge-count"><?= $total ?> ngành</span>
          <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Thêm Ngành
          </button>
        </div>
        
        <form method="GET" style="display: flex; gap: 10px;">
          <?php if ($filter_khoa > 0): ?>
            <input type="hidden" name="khoa_id" value="<?= $filter_khoa ?>">
          <?php endif; ?>
          <input type="text" name="search" class="form-control" placeholder="Tìm kiếm ngành, khoa..." value="<?= e($search) ?>" style="width: 220px; font-size: 14px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Tìm</button>
          <?php if (!empty($search) || $filter_khoa > 0): ?>
            <a href="<?= BASE_URL ?>/admin/nganh" class="btn btn-secondary btn-sm" title="Xóa bộ lọc"><i class="fas fa-sync-alt"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <!-- Bộ lọc theo khoa -->
      <div class="filter-bar">
        <label><i class="fas fa-filter"></i> Lọc theo khoa:</label>
        <select class="form-control" id="filter-khoa" onchange="applyFilter()">
          <option value="0">-- Tất cả khoa --</option>
          <?php foreach ($faculties as $f): ?>
            <option value="<?= $f['id'] ?>" <?= $filter_khoa == $f['id'] ? 'selected' : '' ?>><?= e($f['ten_khoa']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th style="width: 80px; text-align: center;">STT</th>
              <th>Tên ngành học</th>
              <th>Khoa trực thuộc</th>
              <th style="width: 180px; text-align: center;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($majors)): ?>
              <tr>
                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  <i class="fas fa-folder-open" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                  Không tìm thấy ngành học nào.
                </td>
              </tr>
            <?php else: ?>
              <?php 
              $stt = ($page - 1) * 15 + 1;
              foreach ($majors as $m): 
              ?>
                <tr class="hover-row">
                  <td style="text-align: center; font-weight: 500; color: var(--text-muted);"><?= $stt++ ?></td>
                  <td style="font-weight: 600; color: var(--text);"><?= e($m['ten_nganh']) ?></td>
                  <td>
                    <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 13px; font-weight: 500; padding: 4px 10px;">
                      <?= e($m['ten_khoa'] ?? 'Chưa gắn') ?>
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 8px;">
                      <button type="button" class="btn btn-outline btn-sm" onclick="editMajor(<?= $m['id'] ?>, '<?= e($m['ten_nganh']) ?>', <?= $m['khoa_id'] ?>)">
                        <i class="fas fa-edit"></i> Sửa
                      </button>
                      <form action="<?= BASE_URL ?>/admin/nganh/delete" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngành học này? Tất cả lớp trực thuộc ngành này sẽ bị xóa!');" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
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

<!-- MODAL THÊM/SỬA NGÀNH -->
<div class="crud-modal-overlay" id="majorModal">
  <div class="crud-modal">
    <div class="crud-modal-header">
      <h3 id="modal-title"><i class="fas fa-plus-circle"></i> Thêm Ngành mới</h3>
      <button class="close-btn" type="button" onclick="closeModal()">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/nganh/save" method="POST" id="major-form">
      <input type="hidden" name="id" id="major-id" value="0">
      <div class="crud-modal-body">
        <div class="form-group">
          <label for="ten_nganh" style="font-weight: 500; display: block; margin-bottom: 8px;">Tên ngành học <span style="color: red;">*</span></label>
          <input type="text" name="ten_nganh" id="ten_nganh" class="form-control" placeholder="VD: Công nghệ thông tin" required style="width: 100%;">
        </div>
        <div class="form-group">
          <label for="khoa_id" style="font-weight: 500; display: block; margin-bottom: 8px;">Thuộc khoa <span style="color: red;">*</span></label>
          <select name="khoa_id" id="khoa_id" class="form-control" required style="width: 100%;">
            <option value="">-- Chọn Khoa --</option>
            <?php foreach ($faculties as $f): ?>
              <option value="<?= $f['id'] ?>"><?= e($f['ten_khoa']) ?></option>
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
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm Ngành mới';
  document.getElementById('major-id').value = '0';
  document.getElementById('ten_nganh').value = '';
  document.getElementById('khoa_id').value = '';
  document.getElementById('majorModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_nganh').focus(), 200);
}

function editMajor(id, name, khoa_id) {
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa Ngành';
  document.getElementById('major-id').value = id;
  document.getElementById('ten_nganh').value = name;
  document.getElementById('khoa_id').value = khoa_id;
  document.getElementById('majorModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_nganh').focus(), 200);
}

function closeModal() {
  document.getElementById('majorModal').classList.remove('active');
}

document.getElementById('majorModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

function applyFilter() {
  const khoaId = document.getElementById('filter-khoa').value;
  const params = new URLSearchParams(window.location.search);
  if (khoaId > 0) {
    params.set('khoa_id', khoaId);
  } else {
    params.delete('khoa_id');
  }
  params.delete('page');
  window.location.href = '<?= BASE_URL ?>/admin/nganh?' + params.toString();
}
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
