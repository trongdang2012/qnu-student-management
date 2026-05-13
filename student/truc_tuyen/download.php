<?php
/**
 * Xử lý tải xuống file tài liệu
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/student/truc_tuyen/chia_se_tl.php'); exit; }

$db = getDB();
$r  = $db->query("SELECT * FROM tai_lieu WHERE id=$id");
$tl = $r->fetch_assoc();
if (!$tl) { die('Tài liệu không tồn tại.'); }

$file_path = UPLOAD_DIR . $tl['duong_dan'];
if (!file_exists($file_path)) { die('File không tồn tại trên máy chủ.'); }

// Tăng lượt tải
$db->query("UPDATE tai_lieu SET luot_tai = luot_tai + 1 WHERE id=$id");

// Headers để download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . addslashes($tl['ten_file']) . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: no-cache');
header('Expires: 0');
readfile($file_path);
exit;
?>
