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
  width: 500px;
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
        <span>Giảng viên</span>
      </div>
      <h1><i class="fas fa-chalkboard-teacher"></i> Quản lý Giảng viên</h1>
      <p>Quản lý danh sách giảng viên trực thuộc các Khoa của trường Đại học Quy Nhơn.</p>
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
          <h3 style="margin: 0; color: var(--primary);">Danh sách Giảng viên</h3>
          <span class="badge-count"><?= $total ?> giảng viên</span>
          <button type="button" class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="fas fa-plus"></i> Thêm Giảng viên
          </button>
        </div>
        
        <form method="GET" style="display: flex; gap: 10px;">
          <input type="text" name="search" class="form-control" placeholder="Tìm kiếm giảng viên..." value="<?= e($search) ?>" style="width: 250px; font-size: 14px;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Tìm</button>
          <?php if (!empty($search)): ?>
            <a href="<?= BASE_URL ?>/admin/giang-vien" class="btn btn-secondary btn-sm"><i class="fas fa-sync-alt"></i></a>
          <?php endif; ?>
        </form>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th style="width: 70px; text-align: center;">STT</th>
              <th style="width: 120px;">Mã GV</th>
              <th>Họ và tên</th>
              <th>Khoa</th>
              <th>Email</th>
              <th>Số điện thoại</th>
              <th style="width: 180px; text-align: center;">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($giang_viens)): ?>
              <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                  <i class="fas fa-folder-open" style="font-size: 30px; display: block; margin-bottom: 10px;"></i>
                  Không tìm thấy giảng viên nào.
                </td>
              </tr>
            <?php else: ?>
              <?php 
              $stt = ($page - 1) * 15 + 1;
              foreach ($giang_viens as $gv): 
              ?>
                <tr class="hover-row">
                  <td style="text-align: center; font-weight: 500; color: var(--text-muted);"><?= $stt++ ?></td>
                  <td style="font-weight: 600; color: var(--text-dark);"><?= e($gv['ma_gv']) ?></td>
                  <td style="font-weight: 500; color: var(--primary);"><?= e($gv['ho_ten']) ?></td>
                  <td><?= e($gv['ten_khoa'] ?? 'Chưa xác định') ?></td>
                  <td><?= e($gv['email']) ?></td>
                  <td><?= e($gv['so_dien_thoai']) ?></td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 8px;">
                      <button type="button" class="btn btn-outline btn-sm" onclick="editGiangVien(<?= $gv['id'] ?>, '<?= e($gv['ma_gv']) ?>', '<?= e($gv['ho_ten']) ?>', '<?= $gv['khoa_id'] ?>', '<?= e($gv['email']) ?>', '<?= e($gv['so_dien_thoai']) ?>')">
                        <i class="fas fa-edit"></i> Sửa
                      </button>
                      <form action="<?= BASE_URL ?>/admin/giang-vien/delete" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này? Lớp học phần do giảng viên này dạy sẽ được cập nhật trống.');" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $gv['id'] ?>">
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

<!-- MODAL THÊM/SỬA GIẢNG VIÊN -->
<div class="crud-modal-overlay" id="giangVienModal">
  <div class="crud-modal">
    <div class="crud-modal-header">
      <h3 id="modal-title"><i class="fas fa-plus-circle"></i> Thêm Giảng viên mới</h3>
      <button class="close-btn" type="button" onclick="closeModal()">&times;</button>
    </div>
    <form action="<?= BASE_URL ?>/admin/giang-vien/save" method="POST" id="giangvien-form">
      <input type="hidden" name="id" id="giangvien-id" value="0">
      <div class="crud-modal-body">
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="ma_gv" style="font-weight: 500; display: block; margin-bottom: 8px;">Mã giảng viên <span style="color: red;">*</span></label>
          <input type="text" name="ma_gv" id="ma_gv" class="form-control" placeholder="VD: GV101" required style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="ho_ten" style="font-weight: 500; display: block; margin-bottom: 8px;">Họ và tên giảng viên <span style="color: red;">*</span></label>
          <input type="text" name="ho_ten" id="ho_ten" class="form-control" placeholder="VD: TS. Nguyễn Văn A" required style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="khoa_id" style="font-weight: 500; display: block; margin-bottom: 8px;">Khoa phụ trách</label>
          <select name="khoa_id" id="khoa_id" class="form-control" style="width: 100%;">
            <option value="">-- Chọn Khoa --</option>
            <?php foreach ($faculties as $f): ?>
              <option value="<?= $f['id'] ?>"><?= e($f['ten_khoa']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
          <label for="email" style="font-weight: 500; display: block; margin-bottom: 8px;">Email</label>
          <input type="email" name="email" id="email" class="form-control" placeholder="VD: gv@qnu.edu.vn" style="width: 100%;">
        </div>
        <div class="form-group">
          <label for="so_dien_thoai" style="font-weight: 500; display: block; margin-bottom: 8px;">Số điện thoại</label>
          <input type="text" name="so_dien_thoai" id="so_dien_thoai" class="form-control" placeholder="VD: 0987654321" style="width: 100%;">
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
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm Giảng viên mới';
  document.getElementById('giangvien-id').value = '0';
  document.getElementById('ma_gv').value = '';
  document.getElementById('ma_gv').readOnly = false;
  document.getElementById('ho_ten').value = '';
  document.getElementById('khoa_id').value = '';
  document.getElementById('email').value = '';
  document.getElementById('so_dien_thoai').value = '';
  document.getElementById('giangVienModal').classList.add('active');
  setTimeout(() => document.getElementById('ma_gv').focus(), 200);
}

function editGiangVien(id, ma_gv, ho_ten, khoa_id, email, so_dien_thoai) {
  document.getElementById('modal-title').innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa Giảng viên';
  document.getElementById('giangvien-id').value = id;
  document.getElementById('ma_gv').value = ma_gv;
  document.getElementById('ma_gv').readOnly = true;
  document.getElementById('ho_ten').value = ho_ten;
  document.getElementById('khoa_id').value = khoa_id;
  document.getElementById('email').value = email;
  document.getElementById('so_dien_thoai').value = so_dien_thoai;
  document.getElementById('giangVienModal').classList.add('active');
  setTimeout(() => document.getElementById('ho_ten').focus(), 200);
}

function closeModal() {
  document.getElementById('giangVienModal').classList.remove('active');
}

// Đóng modal khi click nền
document.getElementById('giangVienModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
