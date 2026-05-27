<?php
$page_title = 'Đặt lại mật khẩu mới';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đặt lại mật khẩu mới | <?= APP_SHORT_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

  <main class="login-page">
    <div class="login-box fade-in">

      <div class="login-header">
        <div class="logo-circle" style="background-color: #0056B3;">
          <i class="fas fa-user-lock"></i>
        </div>
        <h1>Đặt mật khẩu mới</h1>
        <p>Tạo mật khẩu mạnh để bảo vệ tài khoản</p>
      </div>

      <div class="login-body">
        
        <form action="<?= BASE_URL ?>/auth/reset-password" method="POST" id="resetPwdForm" data-validate-form novalidate>
          
          <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Mật khẩu mới <span class="required">*</span></label>
            <div style="position:relative;">
              <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới"
                data-validate="required minlen" data-minlen="6" required autocomplete="new-password" style="padding-right:44px;">
              <button type="button" id="togglePwd"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);"
                aria-label="Hiện/ẩn mật khẩu">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </button>
            </div>
            <span class="form-error"></span>
          </div>

          <div class="form-group">
            <label for="confirm_password"><i class="fas fa-shield-alt"></i> Xác nhận mật khẩu mới <span class="required">*</span></label>
            <div style="position:relative;">
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới"
                data-validate="required" required autocomplete="new-password" style="padding-right:44px;">
              <button type="button" id="toggleConfirmPwd"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);"
                aria-label="Hiện/ẩn mật khẩu">
                <i class="fas fa-eye" id="eyeConfirmIcon"></i>
              </button>
            </div>
            <span class="form-error" id="confirm-error"></span>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 15px;" id="resetBtn">
            <i class="fas fa-save"></i> Cập nhật mật khẩu
          </button>
        </form>
      </div>

    </div>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
  <script>
    // Toggle hiển thị mật khẩu
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    if (togglePwd) {
      togglePwd.addEventListener('click', () => {
        const shown = pwdInput.type === 'text';
        pwdInput.type = shown ? 'password' : 'text';
        eyeIcon.className = shown ? 'fas fa-eye' : 'fas fa-eye-slash';
      });
    }

    const toggleConfirmPwd = document.getElementById('toggleConfirmPwd');
    const confirmInput = document.getElementById('confirm_password');
    const eyeConfirmIcon = document.getElementById('eyeConfirmIcon');
    if (toggleConfirmPwd) {
      toggleConfirmPwd.addEventListener('click', () => {
        const shown = confirmInput.type === 'text';
        confirmInput.type = shown ? 'password' : 'text';
        eyeConfirmIcon.className = shown ? 'fas fa-eye' : 'fas fa-eye-slash';
      });
    }

    // Check mật khẩu trùng khớp thời gian thực
    confirmInput.addEventListener('input', function() {
      const errSpan = document.getElementById('confirm-error');
      if (this.value !== pwdInput.value) {
        errSpan.textContent = 'Mật khẩu xác nhận không trùng khớp.';
        errSpan.style.display = 'block';
      } else {
        errSpan.textContent = '';
        errSpan.style.display = 'none';
      }
    });

    const resetPwdForm = document.getElementById('resetPwdForm');
    const resetBtn = document.getElementById('resetBtn');

    if (resetPwdForm) {
      resetPwdForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const pwd = pwdInput.value;
        const confirm = confirmInput.value;
        if (!pwd || !confirm) return;

        if (pwd.length < 6) {
          Swal.fire({
            icon: 'warning',
            title: 'Mật khẩu yếu',
            text: 'Mật khẩu mới phải có độ dài tối thiểu từ 6 ký tự.',
            confirmButtonColor: '#0056B3'
          });
          return;
        }

        if (pwd !== confirm) {
          Swal.fire({
            icon: 'error',
            title: 'Mật khẩu không khớp',
            text: 'Mật khẩu mới và xác nhận mật khẩu phải trùng khớp với nhau.',
            confirmButtonColor: '#0056B3'
          });
          return;
        }

        resetBtn.disabled = true;
        resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';

        fetch(this.action, {
          method: 'POST',
          body: new FormData(this),
          headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Cập nhật thành công',
              text: 'Mật khẩu tài khoản của bạn đã được thay đổi!',
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              window.location.href = data.redirect;
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Cập nhật thất bại',
              text: data.message,
              confirmButtonColor: '#0056B3'
            });
            resetBtn.disabled = false;
            resetBtn.innerHTML = '<i class="fas fa-save"></i> Cập nhật mật khẩu';
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: 'error',
            title: 'Lỗi kết nối',
            text: 'Không thể kết nối đến máy chủ, vui lòng thử lại.',
            confirmButtonColor: '#0056B3'
          });
          resetBtn.disabled = false;
          resetBtn.innerHTML = '<i class="fas fa-save"></i> Cập nhật mật khẩu';
        });
      });
    }
  </script>
</body>
</html>
