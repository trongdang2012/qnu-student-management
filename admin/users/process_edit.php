<?php
/**
 * admin/users/process_edit.php - Xử lý sửa tài khoản
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$role = trim($_POST['role'] ?? '');

$errors = [];

if ($id <= 0) {
    $errors[] = 'ID không hợp lệ';
}

if (!empty($password) || !empty($password_confirm)) {
    if (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
}

if (!in_array($role, ['admin', 'student'])) {
    $errors[] = 'Role không hợp lệ';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . BASE_URL . '/admin/users/edit.php?id=' . $id);
    exit;
}

$db = getDB();

// Kiểm tra tài khoản tồn tại
$stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['errors'] = ['Tài khoản không tồn tại'];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/index.php');
    exit;
}
$stmt->close();

// Cập nhật tài khoản
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("UPDATE users SET password = ?, role = ? WHERE id = ?");
    $stmt->bind_param('ssi', $hashed_password, $role, $id);
} else {
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param('si', $role, $id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = 'Cập nhật tài khoản thành công!';
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi cập nhật tài khoản: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/edit.php?id=' . $id);
}
exit;
?>
