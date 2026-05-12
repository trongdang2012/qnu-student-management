<?php
/**
 * Footer dùng chung cho trang sinh viên
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}
$_base = BASE_URL;
?>
  <footer class="student-footer">
    <p>© <?= date('Y') ?> <strong>Trường Đại học Quy Nhơn</strong> — Hệ thống Quản lý Sinh viên v<?= APP_VERSION ?>
    &nbsp;|&nbsp; <a href="mailto:daoao@qnu.edu.vn">daotao@qnu.edu.vn</a></p>
  </footer>

  <!-- Scripts -->
  <script src="<?= $_base ?>/assets/js/main.js"></script>
  <script src="<?= $_base ?>/assets/js/validation.js"></script>
</body>
</html>
