<?php
/**
 * admin/sinh_vien/process_add.php - Xử lý thêm sinh viên
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/sinh_vien/add.php');
    exit;
}

$ma_sv = trim($_POST['ma_sv'] ?? '');
$ho_ten = trim($_POST['ho_ten'] ?? '');
$ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
$gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
$email = trim($_POST['email'] ?? '');
$so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
$nganh = trim($_POST['nganh'] ?? '');
$lop = trim($_POST['lop'] ?? '');
$khoa = trim($_POST['khoa'] ?? '');
$nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
$dia_chi = trim($_POST['dia_chi'] ?? '');

$errors = [];

// Validation
if (empty($ma_sv)) {
    $errors[] = 'Mã sinh viên không được để trống';
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
    header('Location: ' . BASE_URL . '/admin/sinh_vien/add.php');
    exit;
}

$db = getDB();

// Kiểm tra mã SV đã tồn tại
$stmt = $db->prepare("SELECT id FROM sinh_vien WHERE ma_sv = ?");
$stmt->bind_param('s', $ma_sv);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['errors'] = ['Mã sinh viên đã tồn tại trong hệ thống'];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/add.php');
    exit;
}
$stmt->close();

// Chuyển đổi ngày sinh từ Y-m-d sang DATE format của MySQL
$ngay_sinh_db = !empty($ngay_sinh) ? $ngay_sinh : NULL;

// Thêm sinh viên mới (user_id = 0 - chưa liên kết tài khoản)
$user_id = 0;
$trang_thai = 'Đang học';

$stmt = $db->prepare("INSERT INTO sinh_vien 
  (user_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, email, so_dien_thoai, nganh, lop, khoa, nien_khoa, dia_chi, trang_thai)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
  'isssssssssss',
  $user_id,
  $ma_sv,
  $ho_ten,
  $ngay_sinh_db,
  $gioi_tinh,
  $email,
  $so_dien_thoai,
  $nganh,
  $lop,
  $khoa,
  $nien_khoa,
  $dia_chi,
  $trang_thai
);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Thêm sinh viên thành công!';
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/index.php');
} else {
    $_SESSION['errors'] = ['Lỗi khi thêm sinh viên: ' . $db->error];
    $stmt->close();
    header('Location: ' . BASE_URL . '/admin/sinh_vien/add.php');
}
exit;
?>
