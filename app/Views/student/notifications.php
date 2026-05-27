<?php require_once ROOT . '/includes/header.php'; ?>

<div class="student-layout">
  <?php require_once ROOT . '/includes/navbar_student.php'; ?>

  <main class="student-main">
    <div class="page-header fade-in">
      <h2><i class="fas fa-bell"></i> Thông báo của tôi</h2>
      <p>Danh sách các thông báo từ hệ thống</p>
    </div>

    <div class="content-wrapper fade-in">
      <?php if (empty($notifications)): ?>
        <div class="card" style="padding: 30px; text-align: center; color: #666;">
          <i class="fas fa-box-open" style="font-size: 40px; color: #ccc; margin-bottom: 15px;"></i>
          <p>Bạn chưa có thông báo nào.</p>
        </div>
      <?php else: ?>
        <div style="display:flex; flex-direction: column; gap: 12px;">
          <?php foreach ($notifications as $n): ?>
            <?php 
              $is_read = (int)$n['da_doc'] === 1;
              $bg_color = $is_read ? '#fff' : '#f0f8ff';
              $border_color = '#eee';
              $icon = 'fa-info-circle';
              $icon_color = '#17a2b8';
              
              if ($n['loai'] === 'warning') {
                  $icon = 'fa-exclamation-triangle';
                  $icon_color = '#ffc107';
                  $bg_color = $is_read ? '#fff' : '#fff9e6';
              } elseif ($n['loai'] === 'success') {
                  $icon = 'fa-check-circle';
                  $icon_color = '#28a745';
                  $bg_color = $is_read ? '#fff' : '#e6f4ea';
              }
            ?>
            <div class="card notification-card" 
                 data-id="<?= $n['id'] ?>"
                 data-read="<?= $is_read ? '1' : '0' ?>"
                 style="background: <?= $bg_color ?>; border: 1px solid <?= $border_color ?>; border-left: 5px solid <?= $icon_color ?>; padding: 16px 20px; border-radius: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.02);"
                 onclick="viewNoticeDetail(this, <?= $n['id'] ?>, '<?= e($n['tieu_de']) ?>', '<?= e($n['nguoi_gui_ten'] ?? 'Hệ thống') ?>', '<?= date('d/m/Y H:i', strtotime($n['ngay_tao'])) ?>', <?= htmlspecialchars(json_encode($n['noi_dung'], JSON_UNESCAPED_UNICODE)) ?>, '<?= $n['loai'] ?>')"
                 onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';"
                 onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.02)';"
                 >
              <div style="display:flex; justify-content: space-between; align-items:center; gap:15px; width:100%;">
                <h3 style="margin: 0; font-size: 15px; color: <?= $is_read ? '#666' : '#222' ?>; display:flex; align-items:center; gap: 10px; font-weight: <?= $is_read ? '500' : '700' ?>; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;">
                  <i class="fas <?= $icon ?>" style="color: <?= $icon_color ?>; font-size:16px;"></i> 
                  <?= htmlspecialchars($n['tieu_de']) ?>
                  <?php if (!$is_read): ?>
                    <span class="badge" style="background:#dc3545; color:#fff; font-size:10px; padding:2px 6px; border-radius:10px; font-weight:600;">Mới</span>
                  <?php endif; ?>
                </h3>
                <span style="font-size: 12.5px; color: #888; white-space:nowrap;">
                  <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($n['ngay_tao'])) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
function viewNoticeDetail(el, id, tieuDe, nguoiGui, thoiGian, noiDung, loai) {
    // Mở popup modal chi tiết dùng chung định nghĩa ở Navbar
    if (typeof openNoticeDetailModal === 'function') {
        openNoticeDetailModal(id, tieuDe, nguoiGui, thoiGian, noiDung, loai);
    }
    
    // Nếu chưa đọc thì gọi AJAX đánh dấu đã đọc
    if (el.dataset.read === '0') {
        fetch('<?= BASE_URL ?>/student/thong-bao/doc', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                el.dataset.read = '1';
                el.style.background = '#fff';
                
                // Xóa badge "Mới"
                const badge = el.querySelector('.badge');
                if (badge) badge.remove();
                
                // Đổi màu tiêu đề
                const title = el.querySelector('h3');
                if (title) {
                    title.style.color = '#666';
                    title.style.fontWeight = '500';
                }

                // Giảm số lượng chuông trên navbar
                if (typeof decrementBellCount === 'function') {
                    decrementBellCount();
                }
                
                // Cập nhật trạng thái item trong dropdown chuông nếu có
                const dropdownItem = document.querySelector(`.bell-item[data-id="${id}"]`);
                if (dropdownItem) {
                    dropdownItem.dataset.read = '1';
                    dropdownItem.style.background = '#fff';
                    
                    const dTitle = dropdownItem.querySelector('.notice-title-text');
                    if (dTitle) {
                        dTitle.style.fontWeight = '500';
                        dTitle.style.color = '#666';
                    }
                    
                    const dBadge = dropdownItem.querySelector('.notice-new-badge');
                    if (dBadge) dBadge.remove();
                }
            }
        })
        .catch(err => console.error(err));
    }
}
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
