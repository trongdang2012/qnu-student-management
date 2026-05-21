<?php
/**
 * admin/users/process_delete.php - Xử lý xóa tài khoản
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['errors'] = ['ID không hợp lệ'];
    header('Location: ' . BASE_URL . '/admin/users/index.php');
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

// Xóa tài khoản (sẽ tự động xóa sinh viên liên kết do FOREIGN KEY)
$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Xóa tài khoản thành công!';
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi xóa tài khoản: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/users/index.php');
}
exit;
?>
