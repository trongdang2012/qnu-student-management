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
  width: 450px;
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
        <span>Phòng học</span>
      </div>
      <h1><i class="fas fa-school"></i> Quản lý Phòng học</h1>
      <p>Quản lý danh sách phòng học lý thuyết, thực hành tin học của QNU.</p>
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
          <h3 style="margin: 0; color: var(--primary);">Danh sách Phòng học</h3>
          <span class="badge-count"><?= $total ?> phòng</span>
          <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Thêm Phòng học
          </button>
        </div>
        
        <form method="GET" style="display: flex; gap: 10px;">
          <input type="text" name="search" class="form-control" placeholder="Tìm kiếm phòng..." value="<?= e($search) ?>" style="width: 220px; font-size: 14px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Tìm</button>
          <?php if (!empty($search)): ?>
            <a href="<?= BASE_URL ?>/admin/phong-hoc" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th style="width: 80px; text-align: center;">STT</th>
              <th>Tên phòng</th>
              <th>Loại phòng</th>
              <th>Sức chứa (SV)</th>
              <th style="width: 180px; text-align: center;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($phong_hocs)): ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  <i class="fas fa-folder-open" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                  Không tìm thấy phòng học nào.
                </td>
              </tr>
            <?php else: ?>
              <?php 
              $stt = ($page - 1) * 15 + 1;
              foreach ($phong_hocs as $p): 
              ?>
                <tr class="hover-row">
                  <td style="text-align: center; font-weight: 500; color: var(--text-muted);"><?= $stt++ ?></td>
                  <td style="font-weight: 600; color: var(--primary);"><?= e($p['ten_phong']) ?></td>
                  <td>
                    <span class="badge badge-<?= $p['loai_phong'] === 'Thực hành' ? 'warning' : 'info' ?>">
                      <?= e($p['loai_phong']) ?>
                    </span>
                  </td>
                  <td style="font-weight: 500;"><?= e($p['suc_chua']) ?> sinh viên</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 8px;">
                      <button type="button" class="btn btn-outline btn-sm" onclick="editPhongHoc(<?= $p['id'] ?>, '<?= e($p['ten_phong']) ?>', '<?= e($p['loai_phong']) ?>', <?= $p['suc_chua'] ?>)">
                        <i class="fas fa-edit"></i> Sửa
                      </button>
                      <form action="<?= BASE_URL ?>/admin/phong-hoc/delete" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng học này? Lớp học phần học phòng này sẽ được cập nhật trống.');" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
              <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn <?= $page === $i ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="min-width: 32px; justify-content: center;">
                <?= $i ?>
              </a>
            <?php endfor; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- MODAL THÊM/SỬA PHÒNG HỌC -->
<div class="crud-modal-overlay" id="phongHocModal">
  <div class="crud-modal">
    <div class="crud-modal-header">
      <h3 id="modal-title"><i class="fas fa-plus-circle"></i> Thêm Phòng học mới</h3>
      <button class="close-btn" type="button" onclick="closeModal()">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/phong-hoc/save" method="POST" id="phonghoc-form">
      <input type="hidden" name="id" id="phonghoc-id" value="0">
      <div class="crud-modal-body">
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="ten_phong" style="font-weight: 500; display: block; margin-bottom: 8px;">Tên phòng học <span style="color: red;">*</span></label>
          <input type="text" name="ten_phong" id="ten_phong" class="form-control" placeholder="VD: Phòng 101-A1" required style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="loai_phong" style="font-weight: 500; display: block; margin-bottom: 8px;">Loại phòng</label>
          <select name="loai_phong" id="loai_phong" class="form-control" style="width: 100%;">
            <option value="Lý thuyết">Lý thuyết (Phòng thường)</option>
            <option value="Thực hành">Thực hành (Phòng máy tin học, phòng Lab)</option>
          </select>
        </div>
        <div class="form-group">
          <label for="suc_chua" style="font-weight: 500; display: block; margin-bottom: 8px;">Sức chứa tối đa (SV)</label>
          <input type="number" name="suc_chua" id="suc_chua" class="form-control" value="40" min="10" max="200" required style="width: 100%;">
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
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm Phòng học mới';
  document.getElementById('phonghoc-id').value = '0';
  document.getElementById('ten_phong').value = '';
  document.getElementById('loai_phong').value = 'Lý thuyết';
  document.getElementById('suc_chua').value = '40';
  document.getElementById('phongHocModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_phong').focus(), 200);
}

function editPhongHoc(id, name, type, capacity) {
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa Phòng học';
  document.getElementById('phonghoc-id').value = id;
  document.getElementById('ten_phong').value = name;
  document.getElementById('loai_phong').value = type;
  document.getElementById('suc_chua').value = capacity;
  document.getElementById('phongHocModal').classList.add('active');
  setTimeout(() => document.getElementById('ten_phong').focus(), 200);
}

function closeModal() {
  document.getElementById('phongHocModal').classList.remove('active');
}

// Đóng modal khi click nền
document.getElementById('phongHocModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
