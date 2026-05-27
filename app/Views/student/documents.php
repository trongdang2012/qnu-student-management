<?php
function fileIcon(string $loai): string {
    return match(strtolower($loai)) {
        'pdf'           => '<div class="tl-icon pdf"><i class="fas fa-file-pdf"></i></div>',
        'doc','docx'    => '<div class="tl-icon doc"><i class="fas fa-file-word"></i></div>',
        'xls','xlsx'    => '<div class="tl-icon xls"><i class="fas fa-file-excel"></i></div>',
        'ppt','pptx'    => '<div class="tl-icon ppt"><i class="fas fa-file-powerpoint"></i></div>',
        'zip','rar'     => '<div class="tl-icon zip"><i class="fas fa-file-archive"></i></div>',
        default         => '<div class="tl-icon file"><i class="fas fa-file"></i></div>',
    };
}
function formatSize(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
    return round($bytes/1048576, 1) . ' MB';
}
?>
<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<style>
  /* Cấu trúc Layout Grid phân chia 2 khu vực */
  .docs-layout-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 24px;
    align-items: flex-start;
  }
  
  @media (max-width: 992px) {
    .docs-layout-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Styling Form Upload động */
  .upload-form-container {
    display: none;
    margin-bottom: 20px;
    animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-15px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Nút kích hoạt upload */
  .btn-upload-trigger {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    background: linear-gradient(135deg, var(--primary) 0%, #0056B3 100%);
    color: #fff !important;
    font-weight: 500;
    font-size: 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 86, 179, 0.15);
    transition: all 0.3s ease;
    margin-bottom: 16px;
  }
  
  .btn-upload-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 86, 179, 0.25);
  }
  
  .btn-upload-trigger.active {
    background: #6c757d;
    box-shadow: none;
  }

  /* Styling danh sách Tài liệu cá nhân (Cột Trái) */
  .my-doc-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    background: #fff;
    border: 1px solid #eef2f5;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
  }
  
  .my-doc-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    border-color: rgba(0, 86, 179, 0.2);
  }
  
  .my-doc-info {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
  }
  
  .my-doc-title {
    font-weight: 500;
    font-size: 13.5px;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  
  .my-doc-meta {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
    display: flex;
    gap: 8px;
  }
  
  .my-doc-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
  }
  
  .btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
  }
  
  .btn-icon-download {
    background-color: #f0f4ff;
    color: var(--primary);
  }
  
  .btn-icon-download:hover {
    background-color: var(--primary);
    color: #fff;
  }
  
  .btn-icon-delete {
    background-color: #ffeef0;
    color: #dc3545;
  }
  
  .btn-icon-delete:hover {
    background-color: #dc3545;
    color: #fff;
  }

  .my-docs-card {
    border-radius: 12px;
    border: 1px solid #eef2f5;
  }

  /* Chỉnh lại icon tài liệu */
  .tl-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
  }
</style>

<div class="student-wrapper">
  <div class="student-container">

    <!-- Tiêu đề trang -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Trực tuyến</span>
        <span>›</span><span>Tài liệu chia sẻ</span>
      </div>
      <h1><i class="fas fa-share-alt"></i> Tài liệu học tập</h1>
      <p>Cổng chia sẻ tài liệu và quản lý học tập giữa các sinh viên.</p>
    </div>

    <!-- Flash message thông báo hệ thống -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-check-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>
    <?php if ($msg): ?>
      <div class="alert alert-<?= $msg['type'] ?>" data-auto-dismiss>
        <i class="fas fa-exclamation-circle"></i> <?= e($msg['text']) ?>
      </div>
    <?php endif; ?>

    <!-- Layout Grid 2 Cột chính -->
    <div class="docs-layout-grid">

      <!-- CỘT TRÁI (Tài liệu của tôi & Tải tài liệu lên - Chiếm 1/3) -->
      <div>
        
        <!-- Nút trigger mở form tải lên -->
        <button type="button" class="btn-upload-trigger" id="btnToggleUpload">
          <i class="fas fa-cloud-upload-alt"></i> <span>Tải tài liệu lên</span>
        </button>

        <!-- Hộp Form tải lên (Mặc định ẩn, click mới trượt xuống) -->
        <div class="upload-form-container" id="uploadContainer">
          <div class="card my-docs-card shadow-sm fade-in">
            <div class="card-header" style="background:#fff; border-bottom:1px solid #eef2f5; padding:14px 16px;">
              <h3 style="font-size:15px; font-weight:600;"><i class="fas fa-upload" style="color:var(--primary)"></i> Chi tiết tài liệu tải lên</h3>
            </div>
            <div class="card-body" style="padding:16px;">
              <form action="" method="POST" enctype="multipart/form-data" id="uploadForm" data-validate-form novalidate>
                <input type="hidden" name="action" value="upload">

                <!-- Upload zone kéo thả -->
                <div class="upload-zone" id="uploadZone" style="padding: 20px 10px; border-radius: 8px;">
                  <div class="upload-icon" style="font-size:28px; margin-bottom:8px;"><i class="fas fa-cloud-upload-alt"></i></div>
                  <p style="font-size:13px;"><strong>Kéo & thả</strong> file hoặc <strong>click</strong> chọn</p>
                  <p style="font-size:11px; margin-top:4px; color:var(--text-muted)">Hỗ trợ tài liệu tối đa 10MB</p>
                  <input type="file" id="fileInput" name="file_upload" style="display:none"
                         accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.png,.jpg,.jpeg">
                </div>
                <div id="fileInfo" style="display:none;margin:10px 0;padding:8px 10px;background:#f0f4ff;border-radius:6px;font-size:12.5px;color:var(--primary)"></div>

                <!-- Tiêu đề -->
                <div class="form-group mt-12">
                  <label for="tieu_de" style="font-size:13px; font-weight:500;">Tiêu đề <span class="required">*</span></label>
                  <input type="text" id="tieu_de" name="tieu_de" class="form-control"
                         placeholder="VD: Đề cương ôn tập Toán cao cấp A1"
                         data-validate="required" maxlength="200" required>
                  <span class="form-error"></span>
                </div>

                <!-- Học phần liên quan -->
                <div class="form-group">
                  <label for="hoc_phan_id" style="font-size:13px; font-weight:500;">Học phần liên quan</label>
                  <select id="hoc_phan_id" name="hoc_phan_id" class="form-control" style="font-size:13px; padding:6px">
                    <option value="">— Không chọn —</option>
                    <?php foreach ($hp_list as $hp): ?>
                      <option value="<?= (int)$hp['id'] ?>"><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- Mô tả -->
                <div class="form-group">
                  <label for="mo_ta" style="font-size:13px; font-weight:500;">Mô tả ngắn</label>
                  <textarea id="mo_ta" name="mo_ta" class="form-control" rows="3"
                            placeholder="Mô tả tóm tắt nội dung tài liệu..."
                            style="resize:vertical; font-size:13px; padding:8px;"></textarea>
                </div>

                <!-- Nút thao tác -->
                <div style="display:flex;gap:10px;margin-top:16px;">
                  <button type="button" id="btnCancelUpload" class="btn btn-secondary" style="flex:1;justify-content:center; padding:8px; font-size:13.5px">
                    Hủy
                  </button>
                  <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center; padding:8px; font-size:13.5px">
                    <i class="fas fa-upload"></i> Đăng tải
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Khối hiển thị Tài liệu của tôi -->
        <div class="card my-docs-card shadow-sm fade-in">
          <div class="card-header" style="background:#fff; border-bottom:1px solid #eef2f5; padding:14px 16px;">
            <h3 style="font-size:15px; font-weight:600;"><i class="fas fa-folder-open" style="color:#ffc107"></i> Tài liệu của tôi</h3>
          </div>
          <div class="card-body" style="padding:16px; max-height: 500px; overflow-y: auto;">
            
            <?php if (empty($my_list)): ?>
              <div class="text-center" style="padding: 24px 0;">
                <div style="font-size:32px;margin-bottom:8px">📂</div>
                <p style="color:var(--text-muted); font-size:13px; margin:0">Bạn chưa tải tài liệu nào lên.</p>
              </div>
            <?php else: ?>
              <div class="my-docs-list">
                <?php foreach ($my_list as $tl): ?>
                  <div class="my-doc-item">
                    <div class="my-doc-info">
                      <?= fileIcon($tl['loai_file'] ?? '') ?>
                      <div style="min-width: 0; flex: 1;">
                        <div class="my-doc-title" title="<?= e($tl['tieu_de']) ?>">
                          <?= e(mb_strimwidth($tl['tieu_de'], 0, 32, '...')) ?>
                        </div>
                        <div class="my-doc-meta">
                          <span><?= formatSize((int)$tl['kich_thuoc']) ?></span>
                          <span>•</span>
                          <span><i class="fas fa-download"></i> <?= (int)$tl['luot_tai'] ?></span>
                        </div>
                      </div>
                    </div>
                    
                    <div class="my-doc-actions">
                      <?php if (!empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan'])): ?>
                        <a href="<?= BASE_URL ?>/student/download?id=<?= (int)$tl['id'] ?>" class="btn-icon btn-icon-download" title="Tải xuống">
                          <i class="fas fa-download"></i>
                        </a>
                      <?php endif; ?>
                      
                      <form method="POST" action="<?= BASE_URL ?>/student/tai-lieu" style="display:inline" class="delete-doc-form">
                        <input type="hidden" name="action" value="xoa">
                        <input type="hidden" name="tl_id" value="<?= (int)$tl['id'] ?>">
                        <button type="button" class="btn-icon btn-icon-delete btn-delete-submit" title="Xóa tài liệu" data-title="<?= e($tl['tieu_de']) ?>">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- CỘT PHẢI (Tài liệu được chia sẻ từ cộng đồng - Chiếm 2/3) -->
      <div>

        <!-- Bộ lọc và Ô tìm kiếm tài liệu tích hợp tinh tế -->
        <div class="card mb-16 shadow-sm fade-in" style="border-radius:12px; border:1px solid #eef2f5;">
          <div class="card-body" style="padding:14px 16px">
            <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; width:100%;">
              
              <!-- Ô tìm kiếm -->
              <div style="flex:2; min-width:220px; position:relative;">
                <input type="text" name="q" class="form-control" 
                       placeholder="Tìm kiếm tiêu đề, mô tả tài liệu..." 
                       value="<?= e($search_query ?? '') ?>"
                       style="font-size:13.5px; padding:7px 12px 7px 36px; border-radius:6px; width:100%;">
                <i class="fas fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:13px;"></i>
              </div>
              
              <!-- Bộ lọc học phần -->
              <div style="flex:1.5; min-width:180px">
                <select name="hp" class="form-control" style="font-size:13.5px; padding:7px 12px; border-radius:6px; width:100%;">
                  <option value="">Tất cả học phần</option>
                  <?php foreach ($hp_list as $hp): ?>
                    <option value="<?= (int)$hp['id'] ?>" <?= $filter_hp==$hp['id']?'selected':'' ?>><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              
              <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="padding:7px 16px; font-size:13.5px; border-radius:6px;">
                  <i class="fas fa-filter"></i> Lọc
                </button>
                <?php if (!empty($search_query) || $filter_hp > 0): ?>
                  <a href="<?= BASE_URL ?>/student/tai-lieu" class="btn btn-secondary" style="padding:7px 16px; font-size:13.5px; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-redo"></i> Xóa bộ lọc
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Khối hiển thị Danh sách tài liệu chia sẻ -->
        <h2 style="font-size:16px; font-weight:600; color:var(--text-dark); margin: 8px 0 16px 4px; display:flex; align-items:center; gap:8px;">
          <i class="fas fa-globe-asia" style="color:var(--primary)"></i> Tài liệu từ mọi người chia sẻ
        </h2>

        <?php if (empty($tl_list)): ?>
          <div class="card shadow-sm fade-in" style="border-radius:12px; border:1px solid #eef2f5;">
            <div class="card-body text-center" style="padding:60px 40px">
              <div style="font-size:56px;margin-bottom:16px">📂</div>
              <h3 style="color:var(--text-muted); font-size:16px; font-weight:600;">Không tìm thấy tài liệu phù hợp</h3>
              <p class="text-muted" style="font-size:13.5px; margin-top:6px;">Hãy nhập từ khóa khác hoặc tải lên tài liệu môn học này!</p>
            </div>
          </div>
        <?php else: ?>
          <div class="tl-grid">
          <?php foreach ($tl_list as $tl): ?>
            <div class="tl-card shadow-sm fade-in" style="border-radius:12px; border:1px solid #eef2f5; display:flex; flex-direction:column; justify-content:space-between; height: 100%;">
              <div>
                <div style="display:flex;gap:12px;align-items:flex-start">
                  <?= fileIcon($tl['loai_file'] ?? '') ?>
                  <div style="flex:1;min-width:0">
                    <div class="tl-title" title="<?= e($tl['tieu_de']) ?>" style="font-size:14.5px; font-weight:600; line-height:1.4;">
                      <?= e(mb_strimwidth($tl['tieu_de'], 0, 52, '...')) ?>
                    </div>
                    <?php if (!empty($tl['ten_hp'])): ?>
                      <div style="font-size:12px;color:var(--primary);margin-top:4px; font-weight:500;">
                        <i class="fas fa-book"></i> <?= e($tl['ten_hp']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($tl['mo_ta'])): ?>
                  <div style="font-size:12.5px;color:var(--text-muted);line-height:1.5; margin-top: 10px; background: #fafbfc; padding: 8px 10px; border-radius: 6px;">
                    <?= e(mb_strimwidth($tl['mo_ta'], 0, 75, '...')) ?>
                  </div>
                <?php endif; ?>
              </div>

              <div>
                <div class="tl-meta" style="margin-top:12px; border-top:1px solid #f8f9fa; padding-top:10px;">
                  <span title="Người đăng"><i class="fas fa-user"></i> <?= e($tl['ho_ten']) ?></span>
                  <span title="Dung lượng"><i class="fas fa-file"></i> <?= formatSize((int)$tl['kich_thuoc']) ?></span>
                  <span title="Lượt tải"><i class="fas fa-download"></i> <?= (int)$tl['luot_tai'] ?></span>
                </div>
                
                <div style="font-size:11px;color:var(--text-muted); margin-top:6px; display:flex; justify-content:space-between; align-items:center;">
                  <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($tl['ngay_dang'])) ?></span>
                  <?php if ($tl['sinh_vien_id'] == $sv['id']): ?>
                    <span class="badge" style="background-color:#e1f5fe; color:#0288d1; font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600;">Của tôi</span>
                  <?php endif; ?>
                </div>

                <div class="tl-actions" style="margin-top:12px; gap:8px;">
                  <?php if (!empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan'])): ?>
                    <a href="<?= BASE_URL ?>/student/download?id=<?= (int)$tl['id'] ?>"
                       class="btn btn-primary btn-sm" style="flex:1;justify-content:center; border-radius:6px; padding:7px;">
                      <i class="fas fa-download"></i> Tải xuống
                    </a>
                  <?php else: ?>
                    <span class="btn btn-secondary btn-sm" style="flex:1;opacity:.5;cursor:not-allowed;justify-content:center; border-radius:6px; padding:7px;">
                      <i class="fas fa-exclamation-triangle"></i> File không tồn tại
                    </span>
                  <?php endif; ?>

                  <?php if ($tl['sinh_vien_id'] == $sv['id']): ?>
                    <form method="POST" action="<?= BASE_URL ?>/student/tai-lieu" style="display:inline" class="delete-doc-form">
                      <input type="hidden" name="action" value="xoa">
                      <input type="hidden" name="tl_id" value="<?= (int)$tl['id'] ?>">
                      <button type="button" class="btn btn-danger btn-sm btn-delete-submit" style="border-radius:6px; padding:7px;" data-title="<?= e($tl['tieu_de']) ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div><!-- /right -->

    </div><!-- /content-grid -->

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // JS đóng mở Form Upload động mượt mà
    const btnToggleUpload = document.getElementById('btnToggleUpload');
    const uploadContainer = document.getElementById('uploadContainer');
    const btnCancelUpload = document.getElementById('btnCancelUpload');

    if (btnToggleUpload && uploadContainer) {
        btnToggleUpload.addEventListener('click', function() {
            const isVisible = uploadContainer.style.display === 'block';
            if (isVisible) {
                uploadContainer.style.display = 'none';
                btnToggleUpload.classList.remove('active');
                btnToggleUpload.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> <span>Tải tài liệu lên</span>';
            } else {
                uploadContainer.style.display = 'block';
                btnToggleUpload.classList.add('active');
                btnToggleUpload.innerHTML = '<i class="fas fa-times"></i> <span>Đóng Form</span>';
            }
        });
    }

    // Form reset
    if (btnCancelUpload && uploadContainer && btnToggleUpload) {
        btnCancelUpload.addEventListener('click', function() {
            const form = document.getElementById('uploadForm');
            if (form) {
                form.reset();
                form.querySelectorAll('.form-control').forEach(el => {
                    el.classList.remove('is-invalid', 'is-valid');
                });
                form.querySelectorAll('.form-error').forEach(el => {
                    el.style.display = 'none';
                });
            }
            const info = document.getElementById('fileInfo');
            if (info) {
                info.innerHTML = '';
                info.style.display = 'none';
            }
            uploadContainer.style.display = 'none';
            btnToggleUpload.classList.remove('active');
            btnToggleUpload.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> <span>Tải tài liệu lên</span>';
        });
    }

    // JS cho vùng kéo thả file
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');

    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', () => fileInput.click());
        
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = 'var(--primary)';
            uploadZone.style.background = '#f4f7ff';
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.style.borderColor = '#ccc';
            uploadZone.style.background = '#fff';
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.style.borderColor = '#ccc';
            uploadZone.style.background = '#fff';
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showFileInfo(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                showFileInfo(fileInput.files[0]);
            }
        });
    }

    function showFileInfo(file) {
        if (fileInfo) {
            fileInfo.innerHTML = `<i class="fas fa-file-alt"></i> <strong>File đã chọn:</strong> ${file.name} (${formatBytes(file.size)})`;
            fileInfo.style.display = 'block';
        }
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // SweetAlert2 xác nhận xóa tài liệu thời gian thực chuyên nghiệp
    const deleteButtons = document.querySelectorAll('.btn-delete-submit');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const docTitle = this.getAttribute('data-title') || 'tài liệu này';
            const form = this.closest('form');
            
            Swal.fire({
                title: 'Xác nhận xóa tài liệu?',
                text: `Bạn có chắc chắn muốn xóa tài liệu "${docTitle}"? Thao tác này không thể hoàn tác!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

});
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
