<?php
$page_title = 'Xác thực OTP';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác thực OTP | <?= APP_SHORT_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

  <main class="login-page">
    <div class="login-box fade-in">

      <!-- Header -->
      <div class="login-header">
        <div class="logo-circle" style="background-color: #28a745;">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Xác thực 2 bước</h1>
        <p>Vui lòng kiểm tra email của bạn</p>
      </div>

      <!-- Body -->
      <div class="login-body">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger" data-auto-dismiss>
            <i class="fas fa-exclamation-circle"></i>
            <?= e($error === 'invalid' ? 'Mã xác thực không đúng.' : 'Đã xảy ra lỗi.') ?>
          </div>
        <?php endif; ?>

        <?php
            // Ẩn bớt email
            if (!empty($email) && strpos($email, '@') !== false) {
                $parts = explode("@", $email);
                if (strlen($parts[0]) > 2) {
                    $hiddenEmail = substr($parts[0], 0, 2) . str_repeat("*", strlen($parts[0]) - 2) . "@" . $parts[1];
                } else {
                    $hiddenEmail = $email;
                }
            } else {
                $hiddenEmail = 'email của bạn';
            }
        ?>

        <div class="alert alert-info">
          <i class="fas fa-envelope"></i>
          <div>Chúng tôi đã gửi một mã gồm 6 chữ số đến email <strong><?= e($hiddenEmail) ?></strong>. Mã có hiệu lực
            trong vòng 5 phút.</div>
        </div>

        <form action="<?= BASE_URL ?>/auth/otp" method="POST" id="otpForm" data-validate-form novalidate>
          <div class="form-group">
            <label for="otp"><i class="fas fa-key"></i> Mã xác thực <span class="required">*</span></label>
            <input type="text" id="otp" name="otp" class="form-control" placeholder="X-X-X-X-X-X"
              data-validate="required" autocomplete="off" required
              style="text-align: center; font-size: 20px; letter-spacing: 2px; font-weight: bold; width: 100%; box-sizing: border-box;">
            <span class="form-error"></span>
          </div>

          <button type="submit" class="btn btn-success btn-lg" style="width: 100%;" id="otpBtn">
            <i class="fas fa-check-circle"></i> Xác thực
          </button>

          <div style="text-align: center; margin-top: 15px;">
            <a href="<?= BASE_URL ?>/auth/login" style="font-size:14px;color:var(--text-muted);"><i
                class="fas fa-arrow-left"></i> Quay lại đăng nhập</a>
          </div>
        </form>
      </div>

    </div>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
  <script>
    const otpForm = document.getElementById('otpForm');
    const otpBtn = document.getElementById('otpBtn');
    
    if (otpForm) {
      otpForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const otpInput = document.getElementById('otp').value.trim();
        if (!otpInput) return;

        otpBtn.disabled = true;
        otpBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác thực...';

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
              title: 'Thành công',
              text: 'Xác thực hợp lệ!',
              showConfirmButton: false,
              timer: 1000
            }).then(() => {
              window.location.href = data.redirect;
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Xác thực thất bại',
              text: data.message,
              confirmButtonColor: '#28a745'
            }).then(() => {
                if (data.session_out) {
                    window.location.href = '<?= BASE_URL ?>/auth/login?error=session_out';
                }
            });
            otpBtn.disabled = false;
            otpBtn.innerHTML = '<i class="fas fa-check-circle"></i> Xác thực';
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Mất kết nối, vui lòng thử lại.',
            confirmButtonColor: '#28a745'
          });
          otpBtn.disabled = false;
          otpBtn.innerHTML = '<i class="fas fa-check-circle"></i> Xác thực';
        });
      });
    }
  </script>
</body>
</html>
