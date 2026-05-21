<?php
/**
 * admin/sinh_vien/process_delete.php - Xử lý xóa sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['errors'] = ['ID không hợp lệ'];
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}

$db = getDB();

// Kiểm tra sinh viên tồn tại
$stmt = $db->prepare("SELECT user_id FROM sinh_vien WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    $_SESSION['errors'] = ['Sinh viên không tồn tại'];
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}

$user_id = $result['user_id'];

// Xóa sinh viên
$stmt = $db->prepare("DELETE FROM sinh_vien WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    
    // Nếu có user_id, xóa luôn tài khoản user
    if ($user_id > 0) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    $_SESSION['success'] = 'Xóa sinh viên thành công!';
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi xóa sinh viên: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
}
exit;
?>
