<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller {

    public function index() {
        if (isLoggedIn()) {
            $this->redirect('/student/dashboard');
        }
        $error = $_GET['error'] ?? '';
        $this->view('auth/login', ['error' => $error]);
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.']);
        }

        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->json(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.']);
        }

        // Đăng nhập thẳng nếu tắt xác thực 2 lớp (two_factor_auth == 0)
        if (isset($user['two_factor_auth']) && $user['two_factor_auth'] == 0) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['user_role']= $user['role'];
            $_SESSION['login_at'] = time();

            if ($user['role'] === 'admin') {
                return $this->json(['success' => true, 'needs_2fa' => false, 'redirect' => BASE_URL . '/admin/dashboard']);
            } else {
                return $this->json(['success' => true, 'needs_2fa' => false, 'redirect' => BASE_URL . '/student/dashboard']);
            }
        }

        $email = $user['email'];
        if (empty($email)) {
            return $this->json(['success' => false, 'message' => 'Tài khoản của bạn chưa được thiết lập email.']);
        }

        require_once ROOT . '/config/mail.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/Exception.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/PHPMailer.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/SMTP.php';

        $otp = sprintf("%06d", mt_rand(0, 999999));
        $_SESSION['pending_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'email' => $email
        ];
        $_SESSION['login_otp'] = $otp;
        $_SESSION['login_otp_expiry'] = time() + 5 * 60; // 5 phút

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
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
            return $this->json(['success' => true, 'needs_2fa' => true, 'redirect' => BASE_URL . '/auth/otp']);
        } catch (\Exception $e) {
            // Bypass ghi OTP ra file cho môi trường phát triển local khi SMTP lỗi
            $logPath = ROOT . '/scratch/otp.txt';
            if (!is_dir(ROOT . '/scratch')) {
                mkdir(ROOT . '/scratch', 0777, true);
            }
            file_put_contents($logPath, "Mã OTP đăng nhập của bạn là: " . $otp . "\n(Thời gian tạo: " . date('Y-m-d H:i:s') . ")");
            
            return $this->json([
                'success' => true, 
                'needs_2fa' => true, 
                'redirect' => BASE_URL . '/auth/otp?local_bypass=1'
            ]);
        }
    }

    public function otp() {
        if (!isset($_SESSION['pending_user'])) {
            $this->redirect('/auth/login');
        }
        $error = $_GET['error'] ?? '';
        $email = $_SESSION['pending_user']['email'] ?? '';
        $this->view('auth/otp', ['error' => $error, 'email' => $email]);
    }

    public function processOtp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['pending_user'])) {
            return $this->json(['success' => false, 'message' => 'Phiên làm việc không hợp lệ.']);
        }

        $otp_input = trim($_POST['otp'] ?? '');

        if (time() > $_SESSION['login_otp_expiry']) {
            unset($_SESSION['pending_user'], $_SESSION['login_otp'], $_SESSION['login_otp_expiry']);
            return $this->json(['success' => false, 'message' => 'Mã xác thực đã hết hạn, vui lòng đăng nhập lại.', 'session_out' => true]);
        }

        if ($otp_input === $_SESSION['login_otp']) {
            $user = $_SESSION['pending_user'];
            unset($_SESSION['pending_user'], $_SESSION['login_otp'], $_SESSION['login_otp_expiry']);

            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['user_role']= $user['role'];
            $_SESSION['login_at'] = time();

            if ($user['role'] === 'admin') {
                return $this->json(['success' => true, 'redirect' => BASE_URL . '/admin/dashboard']);
            } else {
                return $this->json(['success' => true, 'redirect' => BASE_URL . '/student/dashboard']);
            }
        } else {
            return $this->json(['success' => false, 'message' => 'Mã xác thực không đúng.']);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/auth/login');
    }

    public function forgotPassword() {
        if (isLoggedIn()) {
            $this->redirect('/student/dashboard');
        }
        $this->view('auth/forgot_password');
    }

    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($username) || empty($email)) {
            return $this->json(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin tên đăng nhập và email.']);
        }

        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);

        if (!$user || strcasecmp($user['email'], $email) !== 0) {
            return $this->json(['success' => false, 'message' => 'Tên đăng nhập hoặc email không đúng.']);
        }

        require_once ROOT . '/config/mail.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/Exception.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/PHPMailer.php';
        require_once ROOT . '/includes/vendor/PHPMailer/src/SMTP.php';

        $passcode = sprintf("%06d", mt_rand(0, 999999));
        $_SESSION['reset_user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ];
        $_SESSION['reset_passcode'] = $passcode;
        $_SESSION['reset_passcode_expiry'] = time() + 5 * 60; // 5 phút

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
            $mail->addAddress($user['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Mã xác thực đặt lại mật khẩu (Passcode) - QNU SMS';
            $mail->Body    = "<h2>Chào {$user['username']},</h2>
                              <p>Bạn đã yêu cầu khôi phục mật khẩu trên Hệ thống Quản lý Sinh viên QNU.</p>
                              <p>Mã xác thực đặt lại mật khẩu (Passcode) của bạn là: 
                              <strong style='font-size:24px; color:red;'>{$passcode}</strong></p>
                              <p>Mã này có hiệu lực trong vòng 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai khác.</p>
                              <p>Nếu bạn không gửi yêu cầu này, vui lòng bỏ qua email.</p>";
            $mail->AltBody = "Mã xác thực đặt lại mật khẩu của bạn là: {$passcode}. Mã có hiệu lực 5 phút.";

            $mail->send();
            return $this->json(['success' => true, 'redirect' => BASE_URL . '/auth/verify-passcode']);
        } catch (\Exception $e) {
            // Bypass ghi Passcode ra file
            $logPath = ROOT . '/scratch/otp.txt';
            if (!is_dir(ROOT . '/scratch')) {
                mkdir(ROOT . '/scratch', 0777, true);
            }
            file_put_contents($logPath, "Mã Passcode đặt lại mật khẩu của bạn là: " . $passcode . "\n(Thời gian tạo: " . date('Y-m-d H:i:s') . ")");
            
            return $this->json([
                'success' => true, 
                'redirect' => BASE_URL . '/auth/verify-passcode?local_bypass=1'
            ]);
        }
    }

    public function verifyPasscode() {
        if (!isset($_SESSION['reset_user'])) {
            $this->redirect('/auth/forgot-password');
        }
        $email = $_SESSION['reset_user']['email'] ?? '';
        $this->view('auth/verify_passcode', ['email' => $email]);
    }

    public function processVerifyPasscode() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['reset_user'])) {
            return $this->json(['success' => false, 'message' => 'Phiên làm việc không hợp lệ.']);
        }

        $passcode_input = trim($_POST['passcode'] ?? '');

        if (empty($passcode_input)) {
            return $this->json(['success' => false, 'message' => 'Vui lòng nhập mã xác nhận (Passcode).']);
        }

        if (time() > $_SESSION['reset_passcode_expiry']) {
            unset($_SESSION['reset_user'], $_SESSION['reset_passcode'], $_SESSION['reset_passcode_expiry']);
            return $this->json(['success' => false, 'message' => 'Mã xác thực đã hết hạn, vui lòng làm lại từ đầu.', 'session_out' => true]);
        }

        if ($passcode_input === $_SESSION['reset_passcode']) {
            $_SESSION['reset_passcode_verified'] = true;
            return $this->json(['success' => true, 'redirect' => BASE_URL . '/auth/reset-password']);
        } else {
            return $this->json(['success' => false, 'message' => 'Mã xác thực đặt lại mật khẩu không đúng.']);
        }
    }

    public function resetPassword() {
        if (!isset($_SESSION['reset_user']) || !isset($_SESSION['reset_passcode_verified']) || $_SESSION['reset_passcode_verified'] !== true) {
            $this->redirect('/auth/forgot-password');
        }
        $this->view('auth/reset_password');
    }

    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['reset_user']) || !isset($_SESSION['reset_passcode_verified']) || $_SESSION['reset_passcode_verified'] !== true) {
            return $this->json(['success' => false, 'message' => 'Phiên làm việc không hợp lệ.']);
        }

        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirm_password)) {
            return $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ mật khẩu mới và mật khẩu xác nhận.']);
        }

        if (strlen($password) < 6) {
            return $this->json(['success' => false, 'message' => 'Mật khẩu mới phải có độ dài tối thiểu 6 ký tự.']);
        }

        if ($password !== $confirm_password) {
            return $this->json(['success' => false, 'message' => 'Mật khẩu mới và mật khẩu xác nhận không khớp.']);
        }

        $user = $_SESSION['reset_user'];
        $newPasswordHash = password_hash($password, PASSWORD_BCRYPT);

        $userModel = new UserModel();
        if ($userModel->updatePassword($user['id'], $newPasswordHash)) {
            unset($_SESSION['reset_user'], $_SESSION['reset_passcode'], $_SESSION['reset_passcode_expiry'], $_SESSION['reset_passcode_verified']);
            return $this->json(['success' => true, 'redirect' => BASE_URL . '/auth/login?forgot_success=1']);
        } else {
            return $this->json(['success' => false, 'message' => 'Không thể đặt lại mật khẩu. Vui lòng thử lại sau.']);
        }
    }
}
