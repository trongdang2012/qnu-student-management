<?php
/**
 * admin/sinh_vien/process_edit.php - Xử lý sửa sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$ho_ten = trim($_POST['ho_ten'] ?? '');
$ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
$gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
$email = trim($_POST['email'] ?? '');
$so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
$nganh = trim($_POST['nganh'] ?? '');
$lop = trim($_POST['lop'] ?? '');
$khoa = trim($_POST['khoa'] ?? '');
$nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
$trang_thai = trim($_POST['trang_thai'] ?? 'Đang học');
$dia_chi = trim($_POST['dia_chi'] ?? '');

$errors = [];

// Validation
if ($id <= 0) {
    $errors[] = 'ID không hợp lệ';
}
if (empty($ho_ten)) {
    $errors[] = 'Họ tên không được để trống';
}
if (empty($nganh)) {
    $errors[] = 'Ngành không được để trống';
}
if (empty($lop)) {
    $errors[] = 'Lớp không được để trống';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: ' . BASE_URL . '/admin/sinh_vien/edit.php?id=' . $id);
    exit;
}

$db = getDB();

// Kiểm tra sinh viên tồn tại
$stmt = $db->prepare("SELECT id FROM sinh_vien WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['errors'] = ['Sinh viên không tồn tại'];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
    exit;
}
$stmt->close();

// Chuyển đổi ngày sinh
$ngay_sinh_db = !empty($ngay_sinh) ? $ngay_sinh : NULL;

// Cập nhật sinh viên
$stmt = $db->prepare("UPDATE sinh_vien SET 
  ho_ten = ?, ngay_sinh = ?, gioi_tinh = ?, email = ?, so_dien_thoai = ?, 
  nganh = ?, lop = ?, khoa = ?, nien_khoa = ?, trang_thai = ?, dia_chi = ?
  WHERE id = ?");

$stmt->bind_param(
  'sssssssssssi',
  $ho_ten,
  $ngay_sinh_db,
  $gioi_tinh,
  $email,
  $so_dien_thoai,
  $nganh,
  $lop,
  $khoa,
  $nien_khoa,
  $trang_thai,
  $dia_chi,
  $id
);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Cập nhật thông tin sinh viên thành công!';
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi cập nhật: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/edit.php?id=' . $id);
}
exit;
?>
