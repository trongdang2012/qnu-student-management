<?php
/**
 * admin/users/process_add.php - Xử lý thêm tài khoản
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/users/add.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$role = trim($_POST['role'] ?? '');

$errors = [];

// Validation
if (empty($username)) {
    $errors[] = 'Username không được để trống';
}
if (strlen($username) < 3) {
    $errors[] = 'Username phải có ít nhất 3 ký tự';
}
if (empty($password)) {
    $errors[] = 'Mật khẩu không được để trống';
}
if (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
}
if ($password !== $password_confirm) {
    $errors[] = 'Mật khẩu xác nhận không khớp';
}
if (!in_array($role, ['admin', 'student'])) {
    $errors[] = 'Role không hợp lệ';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . BASE_URL . '/admin/users/add.php');
    exit;
}

$db = getDB();

// Kiểm tra username đã tồn tại
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ['Username đã tồn tại trong hệ thống'];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/add.php');
    exit;
}
$stmt->close();

// Thêm tài khoản mới
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $username, $hashed_password, $role);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Thêm tài khoản thành công!';
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi thêm tài khoản: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/add.php');
}
exit;
?>
