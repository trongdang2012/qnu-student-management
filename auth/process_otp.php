<?php
/**
 * Xử lý xác thực OTP
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';

// Đặt header JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_user'])) {
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc không hợp lệ, vui lòng tải lại trang.']);
    exit;
}

$otp_input = trim($_POST['otp'] ?? '');

if (time() > $_SESSION['login_otp_expiry']) {
    // Xóa session tạm
    unset($_SESSION['pending_user']);
    unset($_SESSION['login_otp']);
    unset($_SESSION['login_otp_expiry']);
    unset($_SESSION['login_otp_expiry']);
    echo json_encode(['success' => false, 'message' => 'Mã xác thực đã hết hạn, vui lòng đăng nhập lại.', 'session_out' => true]);
    exit;
}

if ($otp_input === $_SESSION['login_otp']) {
    // Thành công
    $user = $_SESSION['pending_user'];
    
    // Xóa session tạm
    unset($_SESSION['pending_user']);
    unset($_SESSION['login_otp']);
    unset($_SESSION['login_otp_expiry']);

    // Khởi tạo session chính thức
    session_regenerate_id(true);
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['login_at'] = time();

    // Redirect theo role
    if ($user['role'] === 'admin') {
        echo json_encode(['success' => true, 'redirect' => BASE_URL . '/admin/dashboard.php']);
    } else {
        echo json_encode(['success' => true, 'redirect' => BASE_URL . '/student/dashboard.php']);
    }
    exit;
} else {
    // Thất bại
    echo json_encode(['success' => false, 'message' => 'Mã xác thực không đúng.']);
    exit;
}
?>
