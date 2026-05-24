<?php
/**
 * Xử lý đăng nhập
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Đặt header JSON
header('Content-Type: application/json; charset=utf-8');

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, username, password, role, email FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.']);
    exit;
}

$email = $user['email'];
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Tài khoản của bạn chưa được thiết lập email.']);
    exit;
}

require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../includes/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../includes/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/vendor/PHPMailer/src/SMTP.php';

// Khởi tạo phiên tạm
session_regenerate_id(true);
$otp = sprintf("%06d", mt_rand(0, 999999));
$_SESSION['pending_user'] = [
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'email' => $email
];
$_SESSION['login_otp'] = $otp;
$_SESSION['login_otp_expiry'] = time() + 5 * 60; // 5 phút

// Gửi email
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Mã xác thực đăng nhập (OTP) - QNU SMS';
    $mail->Body    = "<h2>Chào {$user['username']},</h2>
                      <p>Mã xác thực (OTP) để đăng nhập vào Hệ thống Quản lý Sinh viên của bạn là: 
                      <strong style='font-size:24px; color:blue;'>{$otp}</strong></p>
                      <p>Mã này có hiệu lực trong vòng 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.</p>";
    $mail->AltBody = "Mã xác thực đăng nhập của bạn là: {$otp}. Mã có hiệu lực 5 phút.";

    $mail->send();
    echo json_encode(['success' => true, 'redirect' => BASE_URL . '/auth/otp.php']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Không thể gửi email OTP. Vui lòng thử lại sau.']);
}
exit;
?>
