<?php
/**
 * admin/tai_lieu/delete.php - xóa tài liệu
 */
define('ROOT', __DIR__ . '/../../');
require_once ROOT . 'config/constants.php';
require_once ROOT . 'includes/session.php';

requireAdmin();

$dataFile = __DIR__ . '/data.json';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Yêu cầu không hợp lệ.');
    header('Location: ' . BASE_URL . '/admin/tai_lieu/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'ID không hợp lệ.');
    header('Location: ' . BASE_URL . '/admin/tai_lieu/index.php');
    exit;
}

$items = [];
if (file_exists($dataFile)) {
    $items = json_decode(file_get_contents($dataFile), true) ?: [];
}

$found = false;
foreach ($items as $k => $it) {
    if ((int)$it['id'] === $id) {
        // remove file if exists
        if (!empty($it['file'])) {
            $path = __DIR__ . '/' . $it['file'];
            if (file_exists($path)) @unlink($path);
        }
        unset($items[$k]);
        $found = true;
        break;
    }
}

if ($found) {
    // reindex array
    $items = array_values($items);
    file_put_contents($dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    setFlash('success', 'Xóa tài liệu thành công.');
} else {
    setFlash('danger', 'Không tìm thấy tài liệu.');
}

header('Location: ' . BASE_URL . '/admin/tai_lieu/index.php');
exit;
