<?php
/**
 * Admin Footer
 */
?>
  <footer style="background:#1a1a2e;color:#ecf0f1;padding:20px;text-align:center;margin-top:40px;border-top:3px solid #ff6b35">
    <div style="max-width:1400px;margin:0 auto">
      <p style="margin:0;font-size:14px">
        <i class="fas fa-shield-alt"></i> 
        Hệ thống Quản lý Sinh viên - Đại học Quy Nhơn | v<?= APP_VERSION ?>
      </p>
      <p style="margin:8px 0 0;font-size:12px;color:#aaa">
        © 2024 QNU Student Management System. Hỗ trợ: admin@qnu.edu.vn
      </p>
    </div>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const resizer = document.getElementById('sidebarResizer');
      const navbar = document.querySelector('.admin-navbar');
      const wrapper = document.querySelector('.admin-wrapper');
      const hamburger = document.getElementById('adminHamburger');
      
      let isResizing = false;
      let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      let currentWidth = parseInt(localStorage.getItem('sidebarWidth')) || 250;

      function applySidebarState() {
        if (isCollapsed) {
          navbar.classList.add('collapsed');
          wrapper.classList.add('collapsed');
          hamburger.classList.add('show');
          document.documentElement.style.setProperty('--sidebar-width', '0px');
        } else {
          navbar.classList.remove('collapsed');
          wrapper.classList.remove('collapsed');
          hamburger.classList.remove('show');
          document.documentElement.style.setProperty('--sidebar-width', currentWidth + 'px');
        }
      }

      // Apply initial state
      applySidebarState();

      // Dropdown toggle
      const dropdownLinks = document.querySelectorAll('.admin-navbar-menu .nav-item.dropdown > .nav-link');
      dropdownLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          this.parentElement.classList.toggle('open');
        });
      });

      resizer.addEventListener('mousedown', function(e) {
        isResizing = true;
        resizer.classList.add('active');
        document.body.style.cursor = 'ew-resize';
        e.preventDefault();
      });

      document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        
        let newWidth = e.clientX;
        if (newWidth < 80) {
          newWidth = 0; // Snap to collapse
        } else if (newWidth > 500) {
          newWidth = 500; // Max width
        } else if (newWidth < 200) {
          newWidth = 200; // Min width before snapping
        }

        if (newWidth === 0) {
          isCollapsed = true;
        } else {
          isCollapsed = false;
          currentWidth = newWidth;
        }
        
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        localStorage.setItem('sidebarWidth', currentWidth);
        applySidebarState();
      });

      document.addEventListener('mouseup', function() {
        if (isResizing) {
          isResizing = false;
          resizer.classList.remove('active');
          document.body.style.cursor = '';
        }
      });

      hamburger.addEventListener('click', function() {
        isCollapsed = false;
        currentWidth = currentWidth < 200 ? 250 : currentWidth;
        localStorage.setItem('sidebarCollapsed', 'false');
        localStorage.setItem('sidebarWidth', currentWidth);
        applySidebarState();
      });
    });
  </script>
</body>
</html>
