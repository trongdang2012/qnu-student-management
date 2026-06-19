<?php
$page_title = 'Xác minh mã Passcode';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác minh mã Passcode | <?= APP_SHORT_NAME ?></title>
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
        <div class="logo-circle" style="background-color: #ffc107;">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1>Xác minh Passcode</h1>
        <p>Nhập mã xác nhận nhận được từ email</p>
      </div>

      <div class="login-body">

        <?php
            // Ẩn bớt email hiển thị
            if (!empty($email) && strpos($email, '@') !== false) {
                $parts = explode("@", $email);
                if (strlen($parts[0]) > 2) {
                    // Chỉ lặp tối đa 5 dấu sao để tránh chuỗi quá dài gây tràn khung
                    $stars = str_repeat("*", min(5, strlen($parts[0]) - 2));
                    $hiddenEmail = substr($parts[0], 0, 2) . $stars . "@" . $parts[1];
                } else {
                    $hiddenEmail = $email;
                }
            } else {
                $hiddenEmail = 'email đăng ký của bạn';
            }
        ?>

        <div class="alert alert-warning" style="word-break: break-word; overflow-wrap: break-word; display: flex; align-items: flex-start; gap: 10px;">
          <i class="fas fa-envelope-open-text" style="margin-top: 3px; flex-shrink: 0;"></i>
          <div>Một mã Passcode gồm **6 chữ số** đã được gửi tới email <strong><?= e($hiddenEmail) ?></strong>. Mã này có hiệu lực trong vòng 5 phút.</div>
        </div>

        <?php if (isset($_GET['local_bypass'])): ?>
          <div class="alert alert-danger" style="margin-top: 10px; display: flex; align-items: flex-start; gap: 10px;">
            <i class="fas fa-exclamation-triangle" style="margin-top: 3px; flex-shrink: 0;"></i>
            <div>
              <strong>[Chế độ nhà phát triển - Lỗi gửi mail]</strong><br>
              Mã Passcode của bạn là: <strong style="font-size: 16px; color: #c0392b;"><?= e($_SESSION['reset_passcode'] ?? '') ?></strong><br>
              (Đã lưu mã này vào file <code>scratch/otp.txt</code>)
            </div>
          </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/auth/verify-passcode" method="POST" id="verifyForm" data-validate-form novalidate>
          <div class="form-group">
            <label for="passcode"><i class="fas fa-lock-open"></i> Mã Passcode <span class="required">*</span></label>
            <input type="text" id="passcode" name="passcode" class="form-control" placeholder="X-X-X-X-X-X"
              data-validate="required" autocomplete="off" required
              style="text-align: center; font-size: 22px; letter-spacing: 3px; font-weight: bold; width: 100%; box-sizing: border-box;">
            <span class="form-error"></span>
          </div>

          <button type="submit" class="btn btn-warning btn-lg" style="width: 100%; margin-top: 10px;" id="verifyBtn">
            <i class="fas fa-check-double"></i> Xác minh mã
          </button>

          <div style="display:flex; justify-content:space-between; margin-top: 20px; font-size:13px;">
            <a href="<?= BASE_URL ?>/auth/forgot-password" style="color:var(--text-muted);"><i class="fas fa-arrow-left"></i> Nhập lại email</a>
            <a href="<?= BASE_URL ?>/auth/login" style="color:var(--text-muted);">Hủy bỏ</a>
          </div>
        </form>
      </div>

    </div>
  </main>

  <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
  <script>
    const verifyForm = document.getElementById('verifyForm');
    const verifyBtn = document.getElementById('verifyBtn');
    
    if (verifyForm) {
      verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const passcode = document.getElementById('passcode').value.trim();
        if (!passcode) return;

        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xác minh...';

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
              title: 'Xác minh thành công',
              text: 'Mã xác thực chính xác! Vui lòng đặt mật khẩu mới của bạn.',
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              window.location.href = data.redirect;
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Xác minh thất bại',
              text: data.message,
              confirmButtonColor: '#ffc107'
            }).then(() => {
                if (data.session_out) {
                    window.location.href = '<?= BASE_URL ?>/auth/forgot-password';
                }
            });
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="fas fa-check-double"></i> Xác minh mã';
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: 'error',
            title: 'Lỗi kết nối',
            text: 'Mất kết nối với máy chủ, vui lòng thử lại.',
            confirmButtonColor: '#ffc107'
          });
          verifyBtn.disabled = false;
          verifyBtn.innerHTML = '<i class="fas fa-check-double"></i> Xác minh mã';
        });
      });
    }
  </script>
</body>
</html>
