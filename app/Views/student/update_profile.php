<?php
$avatar_url = (!empty($sv['anh_dai_dien']))
    ? BASE_URL . '/uploads/' . e($sv['anh_dai_dien'])
    : BASE_URL . '/assets/img/default-avatar.svg';
?>
<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container" style="max-width:780px">

    <!-- Tiêu đề -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span>
        <a href="<?= BASE_URL ?>/student/ho-so">Cá nhân</a>
        <span>›</span><span>Cập nhật</span>
      </div>
      <h1><i class="fas fa-edit"></i> Cập nhật thông tin</h1>
      <p>Chỉnh sửa <strong>Email</strong>, <strong>Số điện thoại</strong> và <strong>Ảnh đại diện</strong>. Thông tin khác liên hệ phòng Đào tạo.</p>
    </div>

    <form action="<?= BASE_URL ?>/student/cap-nhat" method="POST" enctype="multipart/form-data" id="updateForm" novalidate>

      <!-- ── CARD 1: Ảnh đại diện ── -->
      <div class="card fade-in" style="margin-bottom:20px">
        <div class="card-header">
          <h3><i class="fas fa-camera"></i> Ảnh đại diện</h3>
        </div>
        <div class="card-body">
          <div style="display:flex;align-items:center;gap:28px;flex-wrap:wrap">

            <!-- Preview ảnh -->
            <div style="position:relative;flex-shrink:0">
              <img id="avatarPreview" src="<?= $avatar_url ?>" alt="Ảnh đại diện"
                   style="width:110px;height:110px;border-radius:50%;object-fit:cover;
                          border:4px solid var(--primary-light);box-shadow:0 2px 12px rgba(0,86,179,.15)">
              <label for="avatar"
                     style="position:absolute;bottom:4px;right:4px;
                            width:32px;height:32px;border-radius:50%;
                            background:var(--primary);color:#fff;
                            display:flex;align-items:center;justify-content:center;
                            cursor:pointer;font-size:14px;
                            box-shadow:0 2px 6px rgba(0,0,0,.25);
                            transition:background .2s"
                     title="Chọn ảnh mới"
                     onmouseover="this.style.background='#004a9e'"
                     onmouseout="this.style.background='var(--primary)'">
                <i class="fas fa-pen"></i>
              </label>
            </div>

            <!-- Hướng dẫn + input -->
            <div style="flex:1;min-width:200px">
              <p style="font-size:14px;color:var(--text);margin-bottom:10px">
                <strong>Chọn ảnh mới</strong> từ máy tính của bạn.<br>
                <span style="font-size:13px;color:var(--text-muted)">
                  Hỗ trợ: JPG, PNG, GIF, WEBP &mdash; Tối đa <strong>2MB</strong>
                </span>
              </p>

              <input type="file" id="avatar" name="avatar" accept="image/*"
                     style="display:none" onchange="previewAvatar(this)">

              <label for="avatar" class="btn btn-secondary" style="cursor:pointer;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-upload"></i> Chọn ảnh
              </label>
              <span id="avatarFileName" style="font-size:13px;color:var(--text-muted);margin-left:10px"></span>
              <p id="avatarError" style="color:var(--danger);font-size:13px;margin-top:8px;display:none;"></p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── CARD 2: Thông tin ── -->
      <div class="card fade-in">
        <div class="card-header">
          <h3><i class="fas fa-user-edit"></i> Thông tin có thể chỉnh sửa</h3>
        </div>
        <div class="card-body">

          <!-- Thông tin chỉ đọc -->
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Họ và tên</label>
              <input type="text" class="form-control" value="<?= e($sv['ho_ten']) ?>" disabled>
            </div>
            <div class="form-group">
              <label>MSSV</label>
              <input type="text" class="form-control" value="<?= e($sv['ma_sv']) ?>" disabled>
            </div>
          </div>
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Ngày sinh</label>
              <input type="text" class="form-control"
                     value="<?= $sv['ngay_sinh'] ? date('d/m/Y', strtotime($sv['ngay_sinh'])) : '' ?>" disabled>
            </div>
            <div class="form-group">
              <label>Lớp</label>
              <input type="text" class="form-control" value="<?= e($sv['lop']) ?>" disabled>
            </div>
          </div>

          <hr style="border:none;border-top:1px dashed var(--border);margin:16px 0">
          <p class="text-muted mb-16" style="font-size:14px">
            <i class="fas fa-pencil-alt" style="color:var(--primary)"></i> Chỉnh sửa các trường bên dưới:
          </p>

          <!-- Email -->
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i> Email liên hệ <span class="required">*</span>
            </label>
            <input type="email" id="email" name="email"
                   class="form-control"
                   value="<?= e($sv['email'] ?? '') ?>"
                   placeholder="example@gmail.com"
                   autocomplete="email">
            <span class="form-error" id="emailError" style="display:none;"></span>
            <span class="form-hint" id="emailHint">Địa chỉ email dùng để nhận thông báo từ trường.</span>
          </div>

          <!-- SĐT -->
          <div class="form-group">
            <label for="so_dien_thoai">
              <i class="fas fa-phone"></i> Số điện thoại
            </label>
            <input type="tel" id="so_dien_thoai" name="so_dien_thoai"
                   class="form-control"
                   value="<?= e($sv['so_dien_thoai'] ?? '') ?>"
                   placeholder="0912 345 678"
                   autocomplete="tel">
            <span class="form-error" id="sdtError" style="display:none;"></span>
            <span class="form-hint" id="sdtHint">Số điện thoại liên hệ (không bắt buộc).</span>
          </div>

          <!-- Xác thực 2 lớp (2FA) -->
          <div class="form-group" style="margin-top:20px; margin-bottom: 24px; padding: 15px; background: var(--bg-light, #f8f9fa); border-radius: 8px; border: 1px solid var(--border, #e2e8f0)">
            <label style="display:flex;align-items:center;cursor:pointer;margin:0;width:100%">
              <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:50%;background:rgba(40,167,69,0.1);color:var(--success, #28a745);display:flex;align-items:center;justify-content:center;font-size:18px">
                  <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                  <strong style="font-size:15px;color:var(--text, #2d3748)">Xác thực hai lớp (2FA OTP)</strong>
                  <div style="font-size:12.5px;color:var(--text-muted, #718096);margin-top:2px">Bảo vệ tài khoản bằng mã xác thực gửi qua Email khi đăng nhập.</div>
                </div>
              </div>
              <div style="margin-left:auto">
                <input type="checkbox" id="two_factor_auth" name="two_factor_auth" value="1" <?= $two_factor_auth == 1 ? 'checked' : '' ?> style="width:20px;height:20px;cursor:pointer">
              </div>
            </label>
          </div>

          <!-- Nút -->
          <div style="display:flex;gap:12px;margin-top:8px">
            <button type="submit" class="btn btn-primary" id="btnSave">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="<?= BASE_URL ?>/student/ho-so" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Quay lại
            </a>
          </div>

        </div>
        <div class="card-footer">
          <p class="text-muted" style="font-size:13px">
            <i class="fas fa-info-circle"></i>
            Muốn thay đổi thông tin khác (họ tên, ngày sinh, lớp...) vui lòng liên hệ
            <strong>Phòng Đào tạo</strong> ĐT: (0256) 3846 344.
          </p>
        </div>
      </div>

    </form>

    <!-- ── CARD 3: Đổi mật khẩu ── -->
    <form action="<?= BASE_URL ?>/student/doi-mat-khau" method="POST" id="changePasswordForm" novalidate>
      <div class="card fade-in" style="margin-top:20px;">
        <div class="card-header">
          <h3><i class="fas fa-key"></i> Đổi mật khẩu</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="old_password">
              <i class="fas fa-lock"></i> Mật khẩu hiện tại <span class="required">*</span>
            </label>
            <input type="password" id="old_password" name="old_password" class="form-control" placeholder="Nhập mật khẩu hiện tại" required>
            <span class="form-error" id="oldPwdError" style="display:none;"></span>
          </div>
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label for="new_password">
                <i class="fas fa-key"></i> Mật khẩu mới <span class="required">*</span>
              </label>
              <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Tối thiểu 6 ký tự, gồm in hoa, số, ký tự đặc biệt" required>
              <span class="form-error" id="newPwdError" style="display:none;"></span>
            </div>
            <div class="form-group">
              <label for="confirm_password">
                <i class="fas fa-check-circle"></i> Xác nhận mật khẩu <span class="required">*</span>
              </label>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
              <span class="form-error" id="confirmPwdError" style="display:none;"></span>
            </div>
          </div>
          <div style="display:flex;gap:12px;margin-top:8px">
            <button type="submit" class="btn btn-primary" id="btnChangePwd">
              <i class="fas fa-key"></i> Đổi mật khẩu
            </button>
          </div>
        </div>
      </div>
    </form>

  </div>
</div>

<script>
function previewAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  document.getElementById('avatarFileName').textContent = file.name;

  if (file.size > 2 * 1024 * 1024) {
    alert('Ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 2MB.');
    input.value = '';
    document.getElementById('avatarFileName').textContent = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = function (e) {
    document.getElementById('avatarPreview').src = e.target.result;
  };
  reader.readAsDataURL(file);
}

const updateForm = document.getElementById('updateForm');
const btnSave = document.getElementById('btnSave');

if (updateForm) {
  updateForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.form-error').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.form-hint').forEach(el => el.style.display = 'block');
    document.getElementById('avatarError').style.display = 'none';

    btnSave.disabled = true;
    btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

    const formData = new FormData(this);

    fetch(this.action, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      btnSave.disabled = false;
      btnSave.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';

      if (data.success) {
        if (data.avatar_url && document.querySelector('.user-avatar')) {
            document.querySelector('.user-avatar').src = data.avatar_url;
            document.getElementById('avatarPreview').src = data.avatar_url;
        }

        Swal.fire({
          icon: 'success',
          title: 'Thành công',
          text: data.message,
          showConfirmButton: false,
          timer: 1500
        });
      } else {
        if (data.errors) {
            if (data.errors.email) {
                document.getElementById('email').classList.add('is-invalid');
                document.getElementById('emailError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.email;
                document.getElementById('emailError').style.display = 'block';
                document.getElementById('emailHint').style.display = 'none';
            }
            if (data.errors.sdt) {
                document.getElementById('so_dien_thoai').classList.add('is-invalid');
                document.getElementById('sdtError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.sdt;
                document.getElementById('sdtError').style.display = 'block';
                document.getElementById('sdtHint').style.display = 'none';
            }
            if (data.errors.avatar) {
                document.getElementById('avatarError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.avatar;
                document.getElementById('avatarError').style.display = 'block';
            }
            if (data.errors.db) {
                 Swal.fire('Lỗi', data.errors.db, 'error');
            }
        }
      }
    })
    .catch(err => {
      console.error(err);
      btnSave.disabled = false;
      btnSave.innerHTML = '<i class="fas fa-save"></i> Lưu thay đổi';
      Swal.fire('Lỗi', 'Đã xảy ra lỗi kết nối, vui lòng thử lại.', 'error');
    });
  });
}

const changePwdForm = document.getElementById('changePasswordForm');
const btnChangePwd = document.getElementById('btnChangePwd');

if (changePwdForm) {
  changePwdForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    document.querySelectorAll('#changePasswordForm .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#changePasswordForm .form-error').forEach(el => el.style.display = 'none');

    btnChangePwd.disabled = true;
    btnChangePwd.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    const formData = new FormData(this);

    fetch(this.action, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      btnChangePwd.disabled = false;
      btnChangePwd.innerHTML = '<i class="fas fa-key"></i> Đổi mật khẩu';

      if (data.success) {
        changePwdForm.reset();
        Swal.fire({
          icon: 'success',
          title: 'Thành công',
          text: data.message,
          showConfirmButton: false,
          timer: 1500
        });
      } else {
        if (data.errors) {
            if (data.errors.old_password) {
                document.getElementById('old_password').classList.add('is-invalid');
                document.getElementById('oldPwdError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.old_password;
                document.getElementById('oldPwdError').style.display = 'block';
            }
            if (data.errors.new_password) {
                document.getElementById('new_password').classList.add('is-invalid');
                document.getElementById('newPwdError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.new_password;
                document.getElementById('newPwdError').style.display = 'block';
            }
            if (data.errors.confirm_password) {
                document.getElementById('confirm_password').classList.add('is-invalid');
                document.getElementById('confirmPwdError').innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.errors.confirm_password;
                document.getElementById('confirmPwdError').style.display = 'block';
            }
        } else {
             Swal.fire('Lỗi', data.message || 'Đã xảy ra lỗi kết nối, vui lòng thử lại.', 'error');
        }
      }
    })
    .catch(err => {
      console.error(err);
      btnChangePwd.disabled = false;
      btnChangePwd.innerHTML = '<i class="fas fa-key"></i> Đổi mật khẩu';
      Swal.fire('Lỗi', 'Đã xảy ra lỗi kết nối, vui lòng thử lại.', 'error');
    });
  });
}
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
