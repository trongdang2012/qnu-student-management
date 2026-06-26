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

  // ── Dropdown click toggle ────────────────────────────────────
  // Nav item dropdowns (Cá nhân, Học tập, Trực tuyến)
  function closeDropdowns() {
    document.querySelectorAll('.nav-item.dropdown, .navbar-user').forEach(el => el.classList.remove('show'));
  }

  document.querySelectorAll('.nav-item.dropdown > .nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      // Chỉ toggle class .show bằng JS trên các thiết bị di động (width <= 768px)
      // Trên desktop, hiệu ứng hover bằng CSS sẽ tự động mở/đóng menu rất mượt mà
      if (window.innerWidth <= 768) {
        e.stopPropagation(); // Ngăn event bubble lên document (tránh bị đóng ngay lập tức)
        const item = this.closest('.nav-item.dropdown');
        const isOpen = item.classList.contains('show');
        // Đóng tất cả trước
        closeDropdowns();
        // Nếu chưa mở thì mở, nếu đã mở thì để đóng (toggle)
        if (!isOpen) item.classList.add('show');
      }
    });
  });

  // User menu (avatar + tên người dùng + nút toggle)
  document.querySelectorAll('.navbar-user .user-toggle, .navbar-user .user-avatar, .navbar-user .user-name').forEach(el => {
    el.addEventListener('click', function (e) {
      e.stopPropagation();
      const userMenu = this.closest('.navbar-user');
      const isOpen = userMenu.classList.contains('show');
      closeDropdowns();
      if (!isOpen) userMenu.classList.add('show');
    });
  });

  // Click ra ngoài thì đóng tất cả dropdown
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-item.dropdown') && !e.target.closest('.navbar-user')) {
      closeDropdowns();
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
  // ĐÃ VÔ HIỆU HÓA: Khối mã này gây xung đột với JS cục bộ trong app/Views/student/documents.php
  // dẫn đến việc click chọn file bị lặp 2 lần và gây reload trang/tắt modal ở Student.
  // Toàn bộ logic kéo thả và cập nhật thông tin file hiện được xử lý trực tiếp trong view documents.php.
  /*
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
  */

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

  // ── SweetAlert2 Confirm overrides ──────────────────────────────
  // Chuyển inline onclick/onsubmit chứa confirm() thành data-confirm
  document.querySelectorAll('[onclick*="confirm"], [onsubmit*="confirm"]').forEach(el => {
    let attr = el.hasAttribute('onclick') ? 'onclick' : 'onsubmit';
    let code = el.getAttribute(attr);
    let match = code.match(/confirm\(['"](.*?)['"]\)/);
    if(match) {
      el.setAttribute('data-confirm', match[1]);
      el.removeAttribute(attr);
      if(attr === 'onsubmit') {
         el.classList.add('needs-confirm-submit');
      } else {
         el.classList.add('needs-confirm-click');
      }
    }
  });

  // Bắt sự kiện click
  document.body.addEventListener('click', function(e) {
    let btn = e.target.closest('[data-confirm]');
    if(!btn) return;
    
    // Nếu là nút submit thì bỏ qua để xử lý ở sự kiện submit
    if((btn.tagName === 'BUTTON' && btn.type === 'submit') || (btn.tagName === 'INPUT' && btn.type === 'submit')) {
       return; 
    }

    if(btn.tagName === 'A' || btn.classList.contains('needs-confirm-click')) {
        e.preventDefault();
        Swal.fire({
          title: 'Xác nhận',
          text: btn.dataset.confirm || 'Bạn có chắc chắn không?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#0056B3',
          cancelButtonColor: '#dc3545',
          confirmButtonText: 'Đồng ý',
          cancelButtonText: 'Hủy'
        }).then((result) => {
          if (result.isConfirmed) {
            if(btn.href) window.location.href = btn.href;
            else if(btn.dataset.action) eval(btn.dataset.action);
          }
        });
    }
  });

  // Bắt sự kiện submit form
  document.body.addEventListener('submit', function(e) {
    let form = e.target;
    let submitBtn = e.submitter;
    let hasConfirm = form.hasAttribute('data-confirm') || form.classList.contains('needs-confirm-submit');
    let confirmMsg = form.dataset.confirm || 'Bạn có chắc chắn?';
    
    if(!hasConfirm && submitBtn && submitBtn.hasAttribute('data-confirm')) {
        hasConfirm = true;
        confirmMsg = submitBtn.dataset.confirm;
    }

    if(hasConfirm) {
      e.preventDefault();
      Swal.fire({
          title: 'Xác nhận',
          text: confirmMsg,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#0056B3',
          cancelButtonColor: '#dc3545',
          confirmButtonText: 'Đồng ý',
          cancelButtonText: 'Hủy'
        }).then((result) => {
          if (result.isConfirmed) {
            form.removeAttribute('data-confirm');
            form.classList.remove('needs-confirm-submit');
            if(submitBtn) submitBtn.removeAttribute('data-confirm');
            form.submit();
          }
        });
    }
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
