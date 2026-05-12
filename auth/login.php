<?php
/**
 * Trang đăng nhập
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';

// Nếu đã đăng nhập thì redirect
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $errors = [
        'invalid'       => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        'no_permission' => 'Bạn không có quyền truy cập trang này.',
        'session_out'   => 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.',
    ];
    $error = $errors[$_GET['error']] ?? 'Đã xảy ra lỗi. Vui lòng thử lại.';
}

$page_title = 'Đăng nhập';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Đăng nhập hệ thống quản lý sinh viên QNU">
  <title>Đăng nhập | <?= APP_SHORT_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student.css">
</head>
<body>

<main class="login-page">
  <div class="login-box fade-in">

    <!-- Header -->
    <div class="login-header">
      <div class="logo-circle">
        <i class="fas fa-university"></i>
      </div>
      <h1><?= APP_NAME ?></h1>
      <p>Cổng thông tin dành cho Sinh viên</p>
    </div>

    <!-- Body -->
    <div class="login-body">

      <?php if ($error): ?>
        <div class="alert alert-danger" data-auto-dismiss>
          <i class="fas fa-exclamation-circle"></i>
          <?= e($error) ?>
        </div>
      <?php endif; ?>

      <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
          <i class="fas fa-info-circle"></i> <?= e($flash['msg']) ?>
        </div>
      <?php endif; ?>

      <form action="<?= BASE_URL ?>/auth/process_login.php" method="POST"
            id="loginForm" data-validate-form novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">

        <div class="form-group">
          <label for="username"><i class="fas fa-user"></i> Tên đăng nhập <span class="required">*</span></label>
          <input type="text" id="username" name="username" class="form-control"
                 placeholder="Nhập MSSV hoặc tên đăng nhập"
                 data-validate="required"
                 value="<?= isset($_GET['u']) ? e($_GET['u']) : '' ?>"
                 autocomplete="username" required>
          <span class="form-error"></span>
        </div>

        <div class="form-group">
          <label for="password"><i class="fas fa-lock"></i> Mật khẩu <span class="required">*</span></label>
          <div style="position:relative;">
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="Nhập mật khẩu"
                   data-validate="required minlen" data-minlen="6"
                   autocomplete="current-password" required
                   style="padding-right:44px;">
            <button type="button" id="togglePwd"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);"
                    aria-label="Hiện/ẩn mật khẩu">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
          <span class="form-error"></span>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-bottom:18px;">
          <a href="#" style="font-size:13px;color:var(--primary);">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Đăng nhập
        </button>
      </form>
    </div>

    <!-- Footer -->
    <div class="login-footer">
      Tài khoản demo: <strong>sv001</strong> / mật khẩu: <strong>password</strong>
    </div>

  </div><!-- /login-box -->
</main>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
  // Toggle show/hide password
  const togglePwd = document.getElementById('togglePwd');
  const pwdInput  = document.getElementById('password');
  const eyeIcon   = document.getElementById('eyeIcon');
  if (togglePwd) {
    togglePwd.addEventListener('click', () => {
      const shown = pwdInput.type === 'text';
      pwdInput.type = shown ? 'password' : 'text';
      eyeIcon.className = shown ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
  }
</script>
</body>
</html>
