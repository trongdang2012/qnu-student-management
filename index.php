<?php
/**
 * index.php - Điểm vào chính, tự redirect
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/session.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/student/dashboard.php');
    }
} else {
    header('Location: ' . BASE_URL . '/auth/login.php');
}
exit;
?>
