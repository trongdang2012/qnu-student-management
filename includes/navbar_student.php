<?php
/**
 * Top Navbar dành cho sinh viên
 * Yêu cầu $sv (mảng thông tin sinh viên) và $active_menu đã được set
 */
$_base = BASE_URL;
$_name = isset($sv) ? e($sv['ho_ten']) : 'Sinh viên';
$_msv  = isset($sv) ? e($sv['ma_sv'])  : '';
$_avatar = (isset($sv) && !empty($sv['anh_dai_dien']))
         ? $_base . '/uploads/' . e($sv['anh_dai_dien'])
         : $_base . '/assets/img/default-avatar.svg';

$_menu = $active_menu ?? '';
?>
<nav class="student-navbar" role="navigation" aria-label="Main navigation">
  <div class="navbar-inner">

    <!-- Brand -->
    <a href="<?= $_base ?>/student/dashboard" class="navbar-brand" aria-label="QNU SMS - Trang chủ">
      <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
      <span>QNU SMS</span>
    </a>

    <!-- Toggle (mobile) -->
    <button class="navbar-toggle" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="Mở menu">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Nav links -->
    <ul class="navbar-nav" id="navMenu" role="menubar">

      <!-- Trang chủ -->
      <li class="nav-item" role="none">
        <a href="<?= $_base ?>/student/dashboard"
           class="nav-link <?= $_menu === 'dashboard' ? 'active' : '' ?>"
           role="menuitem">
          Tổng quan
        </a>
      </li>

      <!-- Cá nhân -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'ca_nhan' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          Cá nhân <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/ho-so" role="menuitem">
            <span class="menu-icon"><i class="fas fa-id-card"></i></span> Thông tin cá nhân
          </a></li>
          <li><a href="<?= $_base ?>/student/cap-nhat" role="menuitem">
            <span class="menu-icon"><i class="fas fa-edit"></i></span> Cập nhật thông tin
          </a></li>
          <li>
          <a href="<?= BASE_URL ?>/student/tien-do" class="<?= $active_menu === 'ca_nhan' ? 'active' : '' ?>">
            <span class="menu-icon"><i class="fas fa-tasks"></i></span> Tiến độ học tập
          </a>
        </li>
        </ul>
      </li>

      <!-- Học tập -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'hoc_tap' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          Học tập <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/chuong-trinh" role="menuitem">
            <span class="menu-icon"><i class="fas fa-list-alt"></i></span> Chương trình đào tạo
          </a></li>
          <li><a href="<?= $_base ?>/student/thoi-khoa-bieu" role="menuitem">
            <span class="menu-icon"><i class="fas fa-calendar-alt"></i></span> Thời khóa biểu
          </a></li>
          <li><a href="<?= $_base ?>/student/diem-hoc-tap" role="menuitem">
            <span class="menu-icon"><i class="fas fa-graduation-cap"></i></span> Điểm học tập
          </a></li>
          <li><a href="<?= $_base ?>/student/diem-ren-luyen" role="menuitem">
            <span class="menu-icon"><i class="fas fa-star"></i></span> Điểm rèn luyện
          </a></li>
          <li><div class="dropdown-divider"></div></li>
          <li><a href="<?= $_base ?>/student/hoc-phi" role="menuitem">
            <span class="menu-icon"><i class="fas fa-money-bill-wave"></i></span> Học phí
          </a></li>
        </ul>
      </li>

      <!-- Trực tuyến -->
      <li class="nav-item dropdown" role="none">
        <a href="#" class="nav-link <?= $_menu === 'truc_tuyen' ? 'active' : '' ?>" role="menuitem" aria-haspopup="true">
          Trực tuyến <span class="arrow">▾</span>
        </a>
        <ul class="dropdown-menu" role="menu">
          <li><a href="<?= $_base ?>/student/dang-ky-hoc-phan" role="menuitem">
            <span class="menu-icon"><i class="fas fa-plus-circle"></i></span> Đăng ký học phần
          </a></li>
          <li><a href="<?= $_base ?>/student/tai-lieu" role="menuitem">
            <span class="menu-icon"><i class="fas fa-share-alt"></i></span> Tài liệu chia sẻ
          </a></li>
        </ul>
      </li>

    </ul><!-- /navMenu -->

    <div class="navbar-actions" style="display:flex; align-items:center; gap: 20px;">
      <!-- Notification Bell with Dropdown -->
      <div class="nav-bell-container" style="position:relative; z-index: 1005;">
        <a href="javascript:void(0)" class="nav-bell" id="bellToggle" style="position:relative; color: #1d2c5e; text-decoration:none; font-size: 1.3rem; display: flex; align-items: center;">
          <i class="far fa-bell"></i>
          <?php 
            $studentModel = new \App\Models\StudentModel();
            $unreadCount = isset($sv) ? $studentModel->getUnreadNotificationCount($sv['id']) : 0;
            if ($unreadCount > 0): 
          ?>
            <span class="badge-dot" id="bellBadge" style="position:absolute; top: 0px; right: 0px; width: 8px; height: 8px; background:#dc3545; border-radius:50%; border: 1.5px solid #fff;"></span>
          <?php endif; ?>
        </a>

        <!-- Bell Dropdown Menu -->
        <div class="bell-dropdown" id="bellDropdown" style="display:none; position:absolute; top: 40px; right: -50px; width: 330px; background:#fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); z-index: 1000; overflow:hidden; border-top: 3px solid var(--primary);">
          <div class="bell-header" style="padding: 12px 16px; border-bottom: 1px solid var(--border); background: #f8f9fa; font-weight:700; font-size:13.5px; color:var(--text); display:flex; justify-content:space-between; align-items:center;">
            <span><i class="fas fa-bell" style="color: var(--primary);"></i> Thông báo gần đây</span>
            <?php if ($unreadCount > 0): ?>
              <span id="dropdownUnreadLabel" style="background: rgba(220,53,69,0.1); color:#dc3545; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;"><?= $unreadCount ?> tin mới</span>
            <?php endif; ?>
          </div>
          <div class="bell-list" style="max-height: 280px; overflow-y:auto;">
            <?php 
              $allNotices = isset($sv) ? $studentModel->getNotifications($sv['id']) : [];
              $recentNotices = array_slice($allNotices, 0, 5);
              if (empty($recentNotices)):
            ?>
              <div style="padding: 30px 15px; text-align:center; color:var(--text-muted); font-size:13px;">
                <i class="fas fa-envelope-open" style="font-size: 24px; color: #ccc; margin-bottom: 8px; display:block;"></i>
                Không có thông báo nào.
              </div>
            <?php else: ?>
              <?php foreach ($recentNotices as $rn): ?>
                <?php 
                  $rn_read = (int)$rn['da_doc'] === 1;
                  $rn_icon = 'fa-info-circle';
                  $rn_color = '#17a2b8';
                  $rn_bg = $rn_read ? '#fff' : '#f4f8ff';
                  
                  if ($rn['loai'] === 'warning') {
                      $rn_icon = 'fa-exclamation-triangle';
                      $rn_color = '#ffc107';
                      $rn_bg = $rn_read ? '#fff' : '#fff9e6';
                  } elseif ($rn['loai'] === 'success') {
                      $rn_icon = 'fa-check-circle';
                      $rn_color = '#28a745';
                      $rn_bg = $rn_read ? '#fff' : '#e6f4ea';
                  }
                ?>
                <div class="bell-item <?= $rn_read ? 'read' : 'unread' ?>" 
                     data-id="<?= $rn['id'] ?>"
                     data-read="<?= $rn_read ? '1' : '0' ?>"
                     style="padding: 12px 16px; border-bottom: 1px solid #f1f3f5; cursor:pointer; transition:background 0.2s; display:flex; gap:12px; align-items:flex-start; background: <?= $rn_bg ?>;"
                     onclick="clickNoticeFromBell(this, <?= $rn['id'] ?>, '<?= e($rn['tieu_de']) ?>', '<?= e($rn['nguoi_gui_ten'] ?? 'Hệ thống') ?>', '<?= date('d/m/Y H:i', strtotime($rn['ngay_tao'])) ?>', <?= htmlspecialchars(json_encode($rn['noi_dung'], JSON_UNESCAPED_UNICODE)) ?>, '<?= $rn['loai'] ?>')">
                  <div style="background: rgba(<?= $rn['loai'] === 'warning' ? '255,193,7,0.15' : ($rn['loai'] === 'success' ? '40,167,69,0.15' : '23,162,184,0.15') ?>, 1); color:<?= $rn_color ?>; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px;">
                    <i class="fas <?= $rn_icon ?>"></i>
                  </div>
                  <div style="flex:1; min-width:0;">
                    <div class="notice-title-text" style="font-weight:<?= $rn_read ? '500' : '700' ?>; font-size:13px; color:<?= $rn_read ? '#666' : '#222' ?>; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:3px;">
                      <?= htmlspecialchars($rn['tieu_de']) ?>
                    </div>
                    <div style="font-size:11px; color:#888; display:flex; justify-content:space-between; align-items:center;">
                      <span><?= date('d/m/Y H:i', strtotime($rn['ngay_tao'])) ?></span>
                      <?php if (!$rn_read): ?>
                        <span class="notice-new-badge" style="color:#dc3545; font-weight:700; font-size:9.5px; background:rgba(220,53,69,0.1); padding:1px 5px; border-radius:4px;">MỚI</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <a href="<?= $_base ?>/student/thong-bao" class="bell-footer" style="display:block; padding: 12px; border-top: 1px solid var(--border); background: #f8f9fa; font-weight:600; font-size:12.5px; color:var(--primary); text-align:center; text-decoration:none; transition: background 0.2s; border-radius: 0 0 12px 12px;">
            Xem tất cả thông báo <i class="fas fa-arrow-right" style="font-size:10px; margin-left:3px;"></i>
          </a>
        </div>
      </div>

      <!-- User info (right) -->
      <div class="navbar-user" id="userMenu" style="z-index: 1005;">
        <div class="user-toggle" style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
          <img src="<?= $_avatar ?>" alt="Avatar" class="user-avatar" id="userAvatarToggle">
          <span class="user-name"><?= $_name ?></span>
          <i class="fas fa-chevron-down user-name-arrow" style="font-size: 10px; color: #1d2c5e; margin-left: 2px;"></i>
        </div>
        <div class="user-dropdown" role="menu">
          <div class="user-dropdown-header">
            <div class="ud-name"><?= $_name ?></div>
            <div class="ud-id">MSSV: <?= $_msv ?></div>
          </div>
          <a href="<?= $_base ?>/student/ho-so" role="menuitem">
            <i class="fas fa-user"></i> Hồ sơ của tôi
          </a>
          <a href="<?= $_base ?>/student/cap-nhat" role="menuitem">
            <i class="fas fa-cog"></i> Cài đặt
          </a>
          <a href="<?= $_base ?>/auth/logout" class="logout-link" role="menuitem"
             onclick="return confirm('Bạn có muốn đăng xuất không?')">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
          </a>
        </div>
      </div>
    </div><!-- /navbar-actions -->

<!-- ========================================================================= -->
<!-- MODAL XEM CHI TIẾT THÔNG BÁO (DÙNG CHUNG) -->
<!-- ========================================================================= -->
<div id="noticeDetailModal" class="modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; align-items: center; justify-content: center;">
  <div class="modal-card card fade-in" id="modal_notice_card" style="width: 520px; max-width: 90%; box-shadow: 0 15px 40px rgba(0,0,0,0.25); border-radius: 12px; overflow: hidden; border-left: none; background:#fff;">
    <div class="card-header" style="padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-bottom: 1px solid var(--border);">
      <h3 id="modal_notice_title" style="margin: 0; font-size: 15.5px; color: var(--text); font-weight:700; line-height:1.4;">Tiêu đề thông báo</h3>
      <span style="cursor: pointer; font-size: 24px; font-weight: bold; line-height: 1; color:#888;" onclick="closeNoticeDetailModal()">&times;</span>
    </div>
    <div class="card-body" style="padding: 20px 24px;">
      <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px; display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 10px;">
        <span><i class="far fa-clock"></i> <span id="modal_notice_time">Thời gian</span></span>
        <span><i class="far fa-user"></i> Người gửi: <strong id="modal_notice_sender" style="color:var(--text);">Ban Đào tạo</strong></span>
      </div>
      <div id="modal_notice_content" style="font-size: 14px; line-height: 1.6; color: #444; white-space: pre-wrap; word-break: break-word; max-height: 300px; overflow-y:auto; padding-right:5px;">
        Nội dung chi tiết thông báo...
      </div>
      <div style="text-align: right; margin-top: 24px; border-top: 1px solid #f1f3f5; padding-top:15px;">
        <button class="btn btn-secondary" onclick="closeNoticeDetailModal()" style="padding: 8px 20px; font-size:13px; border-radius:6px; cursor:pointer;">Đóng lại</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bellToggle = document.getElementById('bellToggle');
    const bellDropdown = document.getElementById('bellDropdown');
    
    if (bellToggle && bellDropdown) {
        // Toggle dropdown
        bellToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = bellDropdown.style.display === 'block';
            bellDropdown.style.display = isVisible ? 'none' : 'block';
            
            // Đóng menu avatar nếu đang mở
            const userDropdown = document.querySelector('.user-dropdown');
            if (userDropdown) userDropdown.style.display = 'none';
        });
        
        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!bellDropdown.contains(e.target) && e.target !== bellToggle && !bellToggle.contains(e.target)) {
                bellDropdown.style.display = 'none';
            }
        });
    }
});

// Xem chi tiết thông báo từ Dropdown Chuông
function clickNoticeFromBell(el, id, tieuDe, nguoiGui, thoiGian, noiDung, loai) {
    // Đóng dropdown chuông
    const bellDropdown = document.getElementById('bellDropdown');
    if (bellDropdown) bellDropdown.style.display = 'none';
    
    // Nạp dữ liệu vào Modal dùng chung
    openNoticeDetailModal(id, tieuDe, nguoiGui, thoiGian, noiDung, loai);
    
    // Nếu chưa đọc thì đánh dấu đã đọc
    if (el.dataset.read === '0') {
        processMarkRead(id, function() {
            // Cập nhật trạng thái trong dropdown
            el.dataset.read = '1';
            el.style.background = '#fff';
            
            const titleEl = el.querySelector('.notice-title-text');
            if (titleEl) titleEl.style.fontWeight = '500';
            if (titleEl) titleEl.style.color = '#666';
            
            const badge = el.querySelector('.notice-new-badge');
            if (badge) badge.remove();
            
            // Cập nhật chuông thông báo trên toàn hệ thống
            decrementBellCount();
            
            // Cập nhật trạng thái card nếu đang ở trang thông báo chính
            const mainCard = document.querySelector(`.notification-card[data-id="${id}"]`);
            if (mainCard && mainCard.dataset.read === '0') {
                mainCard.dataset.read = '1';
                mainCard.style.background = '#fff';
                mainCard.style.cursor = 'default';
                mainCard.removeAttribute('onclick');
                
                const mBadge = mainCard.querySelector('.badge');
                if (mBadge) mBadge.remove();
                
                const mTitle = mainCard.querySelector('h3');
                if (mTitle) mTitle.style.color = '#666';
            }
        });
    }
}

// Hàm giảm số lượng chuông thông báo
function decrementBellCount() {
    const bellBadge = document.getElementById('bellBadge');
    if (bellBadge) {
        let current = parseInt(bellBadge.innerText);
        if (current > 1) {
            bellBadge.innerText = current - 1;
            
            const label = document.getElementById('dropdownUnreadLabel');
            if (label) label.innerText = (current - 1) + ' tin mới';
        } else {
            bellBadge.remove();
            const label = document.getElementById('dropdownUnreadLabel');
            if (label) label.remove();
        }
    }
}

// Gọi AJAX đánh dấu đã đọc
function processMarkRead(id, callback) {
    fetch('<?= BASE_URL ?>/student/thong-bao/doc', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && callback) {
            callback();
        }
    })
    .catch(err => console.error(err));
}

// Mở Popup Modal chi tiết dùng chung
function openNoticeDetailModal(id, tieuDe, nguoiGui, thoiGian, noiDung, loai) {
    const modal = document.getElementById('noticeDetailModal');
    const mCard = document.getElementById('modal_notice_card');
    const mTitle = document.getElementById('modal_notice_title');
    const mTime = document.getElementById('modal_notice_time');
    const mSender = document.getElementById('modal_notice_sender');
    const mContent = document.getElementById('modal_notice_content');
    
    if (modal && mCard && mTitle && mTime && mSender && mContent) {
        mTitle.innerText = tieuDe;
        mTime.innerText = thoiGian;
        mSender.innerText = nguoiGui;
        mContent.innerText = noiDung;
        

        
        modal.style.display = 'flex';
    }
}

function closeNoticeDetailModal() {
    const modal = document.getElementById('noticeDetailModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.bell-item:hover {
    background: #f8f9fa !important;
}
.bell-footer:hover {
    background: #f1f3f5 !important;
    color: var(--primary-dark) !important;
}
.bell-list::-webkit-scrollbar {
    width: 6px;
}
.bell-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.bell-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
.bell-list::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}
</style>

  </div><!-- /navbar-inner -->
</nav>
