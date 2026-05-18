/**
 * QNU SMS - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

  // ── Mobile Navbar Toggle ────────────────────────────────────
  const navToggle = document.getElementById('navToggle');
  const navMenu   = document.getElementById('navMenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', navMenu.classList.contains('open'));
    });
    // Đóng khi click ra ngoài
    document.addEventListener('click', (e) => {
      if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
        navMenu.classList.remove('open');
      }
    });
  }

  // ── Dropdown Menu Click Toggle ──────────────────────────────
  const dropdownToggles = document.querySelectorAll('.nav-item.dropdown > .nav-link, .navbar-user > .user-avatar, .navbar-user > .user-name');
  dropdownToggles.forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      const parent = this.closest('.nav-item.dropdown, .navbar-user');
      const wasOpen = parent.classList.contains('show');
      
      // Close all others
      document.querySelectorAll('.nav-item.dropdown, .navbar-user').forEach(el => el.classList.remove('show'));
      
      if (!wasOpen) {
        parent.classList.add('show');
      }
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-item.dropdown') && !e.target.closest('.navbar-user')) {
      document.querySelectorAll('.nav-item.dropdown, .navbar-user').forEach(el => el.classList.remove('show'));
    }
  });

  // ── Tab system (đăng ký học phần) ───────────────────────────
  document.querySelectorAll('.dk-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tab;
      document.querySelectorAll('.dk-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.dk-panel').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      const panel = document.getElementById('panel-' + target);
      if (panel) panel.classList.add('active');
    });
  });

  // ── Auto-dismiss flash alerts ────────────────────────────────
  const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // ── Progress Bar Animate ─────────────────────────────────────
  const bars = document.querySelectorAll('.progress-bar[data-width]');
  bars.forEach(bar => {
    const w = bar.dataset.width;
    setTimeout(() => { bar.style.width = w + '%'; }, 200);
  });

  // ── Upload zone drag & drop ──────────────────────────────────
  const uploadZone = document.getElementById('uploadZone');
  const fileInput  = document.getElementById('fileInput');
  if (uploadZone && fileInput) {
    uploadZone.addEventListener('click', () => fileInput.click());
    uploadZone.addEventListener('dragover', e => {
      e.preventDefault();
      uploadZone.classList.add('drag-over');
    });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
    uploadZone.addEventListener('drop', e => {
      e.preventDefault();
      uploadZone.classList.remove('drag-over');
      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo(e.dataTransfer.files[0]);
      }
    });
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length) updateFileInfo(fileInput.files[0]);
    });
  }

  function updateFileInfo(file) {
    const info = document.getElementById('fileInfo');
    if (!info) return;
    const size = (file.size / 1024).toFixed(1);
    info.innerHTML = `<strong>📄 ${escHtml(file.name)}</strong> <span class="text-muted">(${size} KB)</span>`;
    info.style.display = 'block';
  }

  // ── Table search filter ──────────────────────────────────────
  const searchInput = document.getElementById('tableSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      const table = document.querySelector(this.dataset.table || 'table');
      if (!table) return;
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── Confirm delete buttons ───────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm || 'Bạn có chắc chắn không?')) {
        e.preventDefault();
      }
    });
  });

  // ── Tooltip đơn giản ────────────────────────────────────────
  document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.style.position = 'relative';
    el.addEventListener('mouseenter', () => {
      const tip = document.createElement('div');
      tip.className = 'tooltip-box';
      tip.textContent = el.dataset.tooltip;
      tip.style.cssText = 'position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:5px 10px;border-radius:4px;font-size:12px;white-space:nowrap;z-index:9999;pointer-events:none;';
      el.appendChild(tip);
    });
    el.addEventListener('mouseleave', () => {
      el.querySelectorAll('.tooltip-box').forEach(t => t.remove());
    });
  });

  // ── Helper: escape HTML ──────────────────────────────────────
  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

});
