<?php require_once ROOT . '/includes/admin/header_admin.php'; ?>
<?php require_once ROOT . '/includes/admin/navbar_admin.php'; ?>

<div class="admin-wrapper">
  <div class="admin-container">
    
    <div class="page-title fade-in">
      <h1><i class="fas fa-paper-plane"></i> Gửi thông báo</h1>
      <p style="color:#666;margin:5px 0 0">Gửi thông báo tới hệ thống của sinh viên</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger fade-in">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <div class="card fade-in">
      <div class="card-body" style="padding:30px;">
        <form action="<?= BASE_URL ?>/admin/thong-bao/tao-moi" method="POST">
          
          <div style="margin-bottom: 20px;">
            <label style="display:block;margin-bottom:8px;font-weight:600;">Đối tượng nhận <span style="color:red">*</span></label>
            <select name="target_type" id="target_type" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required onchange="toggleTargetValue()">
              <option value="all">Toàn bộ sinh viên (Toàn trường)</option>
              <option value="sinh_vien">Một sinh viên cụ thể</option>
              <option value="khoa">Toàn bộ sinh viên thuộc Khoa</option>
              <option value="lop">Toàn bộ sinh viên thuộc Lớp</option>
              <option value="canh_bao">Sinh viên bị Cảnh báo học tập (ĐTB < 4.0)</option>
              <option value="no_hoc_phi">Sinh viên đang nợ học phí</option>
            </select>
          </div>

          <!-- Div ẩn/hiện tùy thuộc target_type -->
          <div id="target_value_container" style="margin-bottom: 20px; display: none;">
            <label id="target_value_label" style="display:block;margin-bottom:8px;font-weight:600;">Nhập giá trị <span style="color:red">*</span></label>
            
            <!-- Input text cho Mã SV -->
            <input type="text" name="target_value_text" id="target_value_text" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;display:none;" placeholder="Nhập Mã sinh viên...">
            
            <!-- Select cho Khoa -->
            <select name="target_value_khoa" id="target_value_khoa" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;display:none;">
              <?php foreach ($faculties as $f): ?>
                <option value="<?= htmlspecialchars($f['khoa']) ?>"><?= htmlspecialchars($f['khoa']) ?></option>
              <?php endforeach; ?>
            </select>
            
            <!-- Select cho Lớp -->
            <select name="target_value_lop" id="target_value_lop" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;display:none;">
              <?php foreach ($classes as $c): ?>
                <option value="<?= htmlspecialchars($c['lop']) ?>"><?= htmlspecialchars($c['lop']) ?></option>
              <?php endforeach; ?>
            </select>
            
            <!-- Hidden input that will actually be submitted -->
            <input type="hidden" name="target_value" id="target_value_actual" value="">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display:block;margin-bottom:8px;font-weight:600;">Mức độ cảnh báo <span style="color:red">*</span></label>
            <select name="loai" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required>
              <option value="info">Thông thường (Màu xanh dương)</option>
              <option value="warning">Cảnh báo (Màu vàng/cam)</option>
              <option value="success">Thành công / Hoàn thành (Màu xanh lá)</option>
            </select>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display:block;margin-bottom:8px;font-weight:600;">Tiêu đề thông báo <span style="color:red">*</span></label>
            <input type="text" name="tieu_de" class="form-control" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required placeholder="Ví dụ: Thông báo nộp học phí HK1">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display:block;margin-bottom:8px;font-weight:600;">Nội dung <span style="color:red">*</span></label>
            <textarea name="noi_dung" class="form-control" rows="6" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;" required placeholder="Nhập nội dung chi tiết..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="padding:12px 25px;font-size:16px;">
            <i class="fas fa-paper-plane"></i> Gửi thông báo
          </button>
          <a href="<?= BASE_URL ?>/admin/thong-bao" class="btn" style="padding:12px 25px;font-size:16px;background:#eee;color:#333;margin-left:10px;">Hủy</a>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
function toggleTargetValue() {
    const type = document.getElementById('target_type').value;
    const container = document.getElementById('target_value_container');
    const label = document.getElementById('target_value_label');
    const textInput = document.getElementById('target_value_text');
    const khoaSelect = document.getElementById('target_value_khoa');
    const lopSelect = document.getElementById('target_value_lop');
    const actualInput = document.getElementById('target_value_actual');

    // Reset
    textInput.style.display = 'none';
    khoaSelect.style.display = 'none';
    lopSelect.style.display = 'none';
    textInput.removeAttribute('required');
    
    if (type === 'all' || type === 'canh_bao' || type === 'no_hoc_phi') {
        container.style.display = 'none';
        actualInput.value = '';
    } else if (type === 'sinh_vien') {
        container.style.display = 'block';
        label.innerHTML = 'Nhập Mã sinh viên <span style="color:red">*</span>';
        textInput.style.display = 'block';
        textInput.setAttribute('required', 'required');
        
        // Cập nhật actual khi gõ
        textInput.oninput = function() { actualInput.value = this.value; };
        actualInput.value = textInput.value;
    } else if (type === 'khoa') {
        container.style.display = 'block';
        label.innerHTML = 'Chọn Khoa <span style="color:red">*</span>';
        khoaSelect.style.display = 'block';
        
        // Cập nhật actual khi chọn
        khoaSelect.onchange = function() { actualInput.value = this.value; };
        actualInput.value = khoaSelect.value;
    } else if (type === 'lop') {
        container.style.display = 'block';
        label.innerHTML = 'Chọn Lớp <span style="color:red">*</span>';
        lopSelect.style.display = 'block';
        
        // Cập nhật actual khi chọn
        lopSelect.onchange = function() { actualInput.value = this.value; };
        actualInput.value = lopSelect.value;
    }
}

// Khởi chạy lúc load trang
document.addEventListener('DOMContentLoaded', toggleTargetValue);
</script>

<?php require_once ROOT . '/includes/admin/footer_admin.php'; ?>
