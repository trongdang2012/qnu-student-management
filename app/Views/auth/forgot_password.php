<?php
$page_title = 'Quên mật khẩu';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Khôi phục mật khẩu hệ thống quản lý sinh viên QNU">
  <title>Quên mật khẩu | <?= APP_SHORT_NAME ?></title>
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
        <div class="logo-circle" style="background-color: #dc3545;">
          <i class="fas fa-key"></i>
        </div>
        <h1>Quên mật khẩu</h1>
        <p>Khôi phục quyền truy cập tài khoản</p>
      </div>

      <div class="login-body">
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i>
          <div>Vui lòng cung cấp chính xác <strong>Tên đăng nhập (MSSV)</strong> và <strong>Email</strong> liên kết với tài khoản để nhận mã xác thực đặt lại mật khẩu.</div>
        </div>

        <form action="<?= BASE_URL ?>/auth/forgot-password" method="POST" id="forgotPwdForm" data-validate-form novalidate>
          
          <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Tên đăng nhập <span class="required">*</span></label>
            <input type="text" id="username" name="username" class="form-control"
              placeholder="Nhập MSSV hoặc tên đăng nhập" data-validate="required" required autocomplete="username">
            <span class="form-error"></span>
          </div>

          <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email liên kết <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control"
              placeholder="Nhập email của bạn (Ví dụ: an***@student.qnu.edu.vn)" data-validate="required email" required autocomplete="email">
            <span class="form-error"></span>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px;">
            <a href="<?= BASE_URL ?>/auth/login" style="font-size:14px; color:var(--text-muted);"><i class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>
            <button type="submit" class="btn btn-primary" id="submitBtn" style="min-width: 130px;">
              <i class="fas fa-paper-plane"></i> Gửi Passcode
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
  <script>
    const forgotPwdForm = document.getElementById('forgotPwdForm');
    const submitBtn = document.getElementById('submitBtn');

    if (forgotPwdForm) {
      forgotPwdForm.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        if (!username || !email) {
          return; 
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

        fetch(this.action, {
          method: 'POST',
          body: new FormData(this),
          headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Đã gửi mã xác thực',
              text: 'Mã đặt lại mật khẩu (Passcode) đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư!',
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              window.location.href = data.redirect;
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Lỗi xác thực',
              text: data.message,
              confirmButtonColor: '#dc3545'
            });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Passcode';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Lỗi kết nối',
            text: 'Đã xảy ra lỗi hệ thống, vui lòng thử lại sau.',
            confirmButtonColor: '#dc3545'
          });
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Passcode';
        });
      });
    }
  </script>
</body>
</html>
