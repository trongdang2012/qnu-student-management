<?php
/**
 * Quản lý phiên đăng nhập (Session)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Kiểm tra đăng nhập ───────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// ─── Yêu cầu đăng nhập - nếu chưa thì redirect login ────────────
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// ─── Yêu cầu quyền sinh viên ────────────────────────────────────
function requireStudent(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'student') {
        header('Location: ' . BASE_URL . '/auth/login.php?error=no_permission');
        exit;
    }
}

// ─── Yêu cầu quyền admin ────────────────────────────────────────
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . '/auth/login.php?error=no_permission');
        exit;
    }
}

// ─── Lấy thông tin sinh viên hiện tại từ DB ─────────────────────
function getCurrentStudent(): ?array {
    if (!isLoggedIn() || $_SESSION['role'] !== 'student') return null;
    $db = getDB();
    $uid = (int)$_SESSION['user_id'];
    $sql = "SELECT sv.*, u.username FROM sinh_vien sv
            JOIN users u ON u.id = sv.user_id
            WHERE sv.user_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

// ─── Flash message ──────────────────────────────────────────────
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// ─── Escape output ──────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ─── Format tiền tệ ─────────────────────────────────────────────
function formatMoney(float $amount): string {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// ─── Chuyển điểm số → điểm chữ ──────────────────────────────────
function diemChu(float $diem): string {
    if ($diem >= 9.0) return 'A+';
    if ($diem >= 8.5) return 'A';
    if ($diem >= 8.0) return 'B+';
    if ($diem >= 7.0) return 'B';
    if ($diem >= 6.5) return 'C+';
    if ($diem >= 5.5) return 'C';
    if ($diem >= 5.0) return 'D+';
    if ($diem >= 4.0) return 'D';
    return 'F';
}

// ─── Chuyển điểm số → hệ 4 ──────────────────────────────────────
function diemHe4(float $diem): float {
    if ($diem >= 9.0) return 4.0;
    if ($diem >= 8.5) return 3.7;
    if ($diem >= 8.0) return 3.5;
    if ($diem >= 7.0) return 3.0;
    if ($diem >= 6.5) return 2.5;
    if ($diem >= 5.5) return 2.0;
    if ($diem >= 5.0) return 1.5;
    if ($diem >= 4.0) return 1.0;
    return 0.0;
}

// ─── Màu badge trạng thái điểm ───────────────────────────────────
function badgeDiemChu(string $chu): string {
    $map = ['A+'=>'success','A'=>'success','B+'=>'primary','B'=>'primary',
            'C+'=>'warning','C'=>'warning','D+'=>'secondary','D'=>'secondary','F'=>'danger'];
    return $map[$chu] ?? 'secondary';
}

// ─── Tên thứ trong tuần ──────────────────────────────────────────
function tenThu(int $thu): string {
    $map = [2=>'Thứ 2',3=>'Thứ 3',4=>'Thứ 4',5=>'Thứ 5',6=>'Thứ 6',7=>'Thứ 7',8=>'Chủ nhật'];
    return $map[$thu] ?? "Thứ $thu";
}
?>
