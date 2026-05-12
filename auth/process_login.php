<?php
/**
 * Xử lý đăng nhập
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ' . BASE_URL . '/auth/login.php?error=invalid');
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ' . BASE_URL . '/auth/login.php?error=invalid&u=' . urlencode($username));
    exit;
}

// Đăng nhập thành công - khởi tạo session
session_regenerate_id(true);
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];
$_SESSION['login_at'] = time();

// Redirect theo role
if ($user['role'] === 'admin') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/student/dashboard.php');
}
exit;
?>
