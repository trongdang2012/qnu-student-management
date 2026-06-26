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
  /* Custom Modal CSS */
  .custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
    display: none;
    align-items: center;
    justify-content: center;
  }
  .custom-modal.active {
    display: flex;
  }
  .custom-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(4px);
  }
  .custom-modal-content {
    position: relative;
    background: #fff;
    width: 95%;
    max-width: 750px;
    border-radius: 12px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 1051;
    animation: modalZoomIn 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
  }
  
  @keyframes modalZoomIn {
    from {
      opacity: 0;
      transform: scale(0.9);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  
  .custom-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #eef2f5;
    background: #fff;
  }
  .custom-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .btn-close-modal {
    background: none;
    border: none;
    font-size: 28px;
    line-height: 1;
    color: var(--text-muted);
    cursor: pointer;
    transition: color 0.2s;
    padding: 0;
  }
  .btn-close-modal:hover {
    color: #dc3545;
  }
  .custom-modal-body {
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
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

    <!-- Dải nút bấm hành động (Xem tài liệu của tôi & Đăng tài liệu) -->
    <div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
      <button type="button" class="btn" id="btnShowMyDocs" style="display:flex; align-items:center; gap:8px; font-weight:500; padding:10px 18px; border-radius:8px; background:linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color:#fff !important; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(255, 152, 0, 0.25); transition:all 0.3s ease;">
        <i class="fas fa-folder-open"></i> <span>Xem tài liệu của tôi (<?= count($my_list) ?>)</span>
      </button>
      
      <button type="button" class="btn-upload-trigger" id="btnToggleUpload" style="margin-bottom:0; width:auto; padding:10px 18px; min-width:180px;">
        <i class="fas fa-cloud-upload-alt"></i> <span>Đăng tài liệu</span>
      </button>
    </div>

    <!-- Modal Đăng tài liệu -->
    <div class="custom-modal" id="uploadModal">
      <div class="custom-modal-overlay"></div>
      <div class="custom-modal-content" style="max-width: 600px;">
        <div class="custom-modal-header">
          <h3><i class="fas fa-cloud-upload-alt" style="color:var(--primary)"></i> Chi tiết tài liệu tải lên</h3>
          <button type="button" class="btn-close-modal" id="btnCloseUploadModal">&times;</button>
        </div>
        <div class="custom-modal-body" style="padding:16px; background:#fff;">
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

            <!-- Quyền chia sẻ -->
            <div class="form-group">
              <label style="font-size:13px; font-weight:500; display:block; margin-bottom:8px;">Chế độ chia sẻ</label>
              <label style="display:inline-flex; align-items:center; gap:8px; margin-right:18px; font-size:13px;">
                <input type="radio" name="cong_khai" value="1" checked>
                Công khai (mọi người có thể xem)
              </label>
              <label style="display:inline-flex; align-items:center; gap:8px; font-size:13px;">
                <input type="radio" name="cong_khai" value="0">
                Riêng tư (chỉ bạn thấy trong tài liệu của tôi)
              </label>
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

    <!-- Modal Sửa tài liệu -->
    <div class="custom-modal" id="editDocModal">
      <div class="custom-modal-overlay"></div>
      <div class="custom-modal-content" style="max-width: 600px;">
        <div class="custom-modal-header">
          <h3><i class="fas fa-edit" style="color:var(--primary)"></i> Chỉnh sửa tài liệu</h3>
          <button type="button" class="btn-close-modal" id="btnCloseEditModal">&times;</button>
        </div>
        <div class="custom-modal-body" style="padding:16px; background:#fff;">
          <form action="" method="POST" enctype="multipart/form-data" id="editForm" data-validate-form novalidate>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="tl_id" id="edit_tl_id" value="">

            <!-- Upload zone kéo thả file mới (Không bắt buộc) -->
            <div class="upload-zone" id="editUploadZone" style="padding: 20px 10px; border-radius: 8px;">
              <div class="upload-icon" style="font-size:28px; margin-bottom:8px;"><i class="fas fa-cloud-upload-alt"></i></div>
              <p style="font-size:13px;"><strong>Kéo & thả</strong> file mới hoặc <strong>click</strong> để thay thế (Không bắt buộc)</p>
              <p style="font-size:11px; margin-top:4px; color:var(--text-muted)">Hỗ trợ tài liệu tối đa 10MB</p>
              <input type="file" id="editFileInput" name="file_upload" style="display:none"
                     accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.png,.jpg,.jpeg">
            </div>
            <div id="editFileInfo" style="display:none;margin:10px 0;padding:8px 10px;background:#f0f4ff;border-radius:6px;font-size:12.5px;color:var(--primary)"></div>

            <!-- Tiêu đề -->
            <div class="form-group mt-12">
              <label for="edit_tieu_de" style="font-size:13px; font-weight:500;">Tiêu đề <span class="required">*</span></label>
              <input type="text" id="edit_tieu_de" name="tieu_de" class="form-control"
                     placeholder="VD: Đề cương ôn tập Toán cao cấp A1"
                     data-validate="required" maxlength="200" required>
              <span class="form-error"></span>
            </div>

            <!-- Học phần liên quan -->
            <div class="form-group">
              <label for="edit_hoc_phan_id" style="font-size:13px; font-weight:500;">Học phần liên quan</label>
              <select id="edit_hoc_phan_id" name="hoc_phan_id" class="form-control" style="font-size:13px; padding:6px">
                <option value="">— Không chọn —</option>
                <?php foreach ($hp_list as $hp): ?>
                  <option value="<?= (int)$hp['id'] ?>"><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Quyền chia sẻ -->
            <div class="form-group">
              <label style="font-size:13px; font-weight:500; display:block; margin-bottom:8px;">Chế độ chia sẻ</label>
              <label style="display:inline-flex; align-items:center; gap:8px; margin-right:18px; font-size:13px;">
                <input type="radio" name="cong_khai" id="edit_cong_khai_1" value="1">
                Công khai (mọi người có thể xem)
              </label>
              <label style="display:inline-flex; align-items:center; gap:8px; font-size:13px;">
                <input type="radio" name="cong_khai" id="edit_cong_khai_0" value="0">
                Riêng tư (chỉ bạn thấy trong tài liệu của tôi)
              </label>
            </div>

            <!-- Mô tả -->
            <div class="form-group">
              <label for="edit_mo_ta" style="font-size:13px; font-weight:500;">Mô tả ngắn</label>
              <textarea id="edit_mo_ta" name="mo_ta" class="form-control" rows="3"
                        placeholder="Mô tả tóm tắt nội dung tài liệu..."
                        style="resize:vertical; font-size:13px; padding:8px;"></textarea>
            </div>

            <!-- Nút thao tác -->
            <div style="display:flex;gap:10px;margin-top:16px;">
              <button type="button" id="btnCancelEdit" class="btn btn-secondary" style="flex:1;justify-content:center; padding:8px; font-size:13.5px">
                Hủy
              </button>
              <button type="submit" class="btn btn-primary" style="flex:2;justify-content:center; padding:8px; font-size:13.5px">
                <i class="fas fa-save"></i> Lưu thay đổi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bộ lọc và Ô tìm kiếm tài liệu (Rộng 100%) -->
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
    <h2 style="font-size:17px; font-weight:600; color:var(--text-dark); margin:24px 0 16px 4px; display:flex; align-items:center; gap:10px;">
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
        <div class="tl-card shadow-sm fade-in btn-view-detail" 
             style="border-radius:12px; border:1px solid #eef2f5; cursor:pointer;"
             data-id="<?= (int)$tl['id'] ?>"
             data-tieu-de="<?= e($tl['tieu_de']) ?>"
             data-nguoi-dang="<?= e($tl['ho_ten'] ?: 'Admin') ?>"
             data-dung-luong="<?= formatSize((int)$tl['kich_thuoc']) ?>"
             data-luot-tai="<?= (int)$tl['luot_tai'] ?>"
             data-ngay-dang="<?= date('d/m/Y H:i', strtotime($tl['ngay_dang'])) ?>"
             data-hoc-phan="<?= e($tl['ten_hp'] ?: 'Không có') ?>"
             data-mo-ta="<?= e($tl['mo_ta'] ?: 'Không có mô tả') ?>"
             data-loai-file="<?= e(strtolower($tl['loai_file'] ?? '')) ?>"
             data-preview-url="<?= !empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan']) ? BASE_URL . '/uploads/' . e($tl['duong_dan']) : '' ?>"
             data-download-url="<?= !empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan']) ? BASE_URL . '/student/download?id=' . (int)$tl['id'] : '' ?>">
          
          <!-- Thông tin tài liệu bên trái -->
          <div style="display:flex; gap:14px; align-items:center; flex:1; min-width:0;">
            <?= fileIcon($tl['loai_file'] ?? '') ?>
            <div style="flex:1; min-width:0;">
              <div class="tl-title" title="<?= e($tl['tieu_de']) ?>" style="font-size:15px; font-weight:600; color:var(--text-dark); line-height:1.4; margin-bottom:4px;">
                <?= e($tl['tieu_de']) ?>
              </div>
              <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; font-size:12px; color:var(--text-muted);">
                <?php if (!empty($tl['ten_hp'])): ?>
                  <span style="color:var(--primary); font-weight:500; display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-book"></i> <?= e($tl['ten_hp']) ?>
                  </span>
                  <span>•</span>
                <?php endif; ?>
                <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-user"></i> <?= e($tl['ho_ten'] ?: 'Admin') ?></span>
                <span>•</span>
                <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-clock"></i> <?= date('d/m/Y', strtotime($tl['ngay_dang'])) ?></span>
                <span>•</span>
                <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-hdd"></i> <?= formatSize((int)$tl['kich_thuoc']) ?></span>
                <span>•</span>
                <span style="display:inline-flex; align-items:center; gap:4px;"><i class="fas fa-download"></i> <?= (int)$tl['luot_tai'] ?> lượt tải</span>
              </div>
            </div>
          </div>

          <!-- Các nút hành động bên phải -->
          <div class="tl-actions" style="margin-top:0; gap:8px; flex-shrink:0; display:flex; align-items:center;">
            <?php if (!empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan'])): ?>
              <a href="<?= BASE_URL ?>/student/download?id=<?= (int)$tl['id'] ?>"
                 class="btn btn-primary btn-sm btn-download-file" style="justify-content:center; border-radius:6px; padding:7px 16px; font-weight:500; font-size:13px;" onclick="event.stopPropagation();">
                <i class="fas fa-download"></i> Tải xuống
              </a>
            <?php else: ?>
              <span class="btn btn-secondary btn-sm" style="opacity:.5;cursor:not-allowed;justify-content:center; border-radius:6px; padding:7px 16px; font-weight:500; font-size:13px;" onclick="event.stopPropagation();">
                <i class="fas fa-exclamation-triangle"></i> File không tồn tại
              </span>
            <?php endif; ?>

            <?php if ($tl['sinh_vien_id'] == $sv['id']): ?>
              <form method="POST" action="<?= BASE_URL ?>/student/tai-lieu" style="display:inline" class="delete-doc-form" onclick="event.stopPropagation();">
                <input type="hidden" name="action" value="xoa">
                <input type="hidden" name="tl_id" value="<?= (int)$tl['id'] ?>">
                <button type="button" class="btn btn-danger btn-sm btn-delete-submit" style="border-radius:6px; padding:7px 12px;" data-title="<?= e($tl['tieu_de']) ?>">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Modal Tài liệu của tôi -->
    <div class="custom-modal" id="myDocsModal">
      <div class="custom-modal-overlay"></div>
      <div class="custom-modal-content">
        <div class="custom-modal-header">
          <h3><i class="fas fa-folder-open" style="color:#ffc107"></i> Tài liệu của tôi</h3>
          <button type="button" class="btn-close-modal" id="btnCloseMyDocsModal">&times;</button>
        </div>
        <div class="custom-modal-body" style="padding:16px; max-height:65vh; overflow-y:auto;">
          <?php if (empty($my_list)): ?>
            <div class="text-center" style="padding: 40px 0;">
              <div style="font-size:48px;margin-bottom:12px">📂</div>
              <p style="color:var(--text-muted); font-size:14px; margin:0">Bạn chưa tải tài liệu nào lên.</p>
            </div>
          <?php else: ?>
            <div class="my-docs-list">
              <?php foreach ($my_list as $tl): ?>
                <div class="my-doc-item" style="background:#fff; margin-bottom:10px; border:1px solid #eef2f5; border-radius:8px; padding:12px;">
                  <div class="my-doc-info btn-view-detail" style="cursor:pointer;"
                       data-id="<?= (int)$tl['id'] ?>"
                       data-tieu-de="<?= e($tl['tieu_de']) ?>"
                       data-nguoi-dang="<?= e($sv['ho_ten'] ?: 'Tôi') ?>"
                       data-dung-luong="<?= formatSize((int)$tl['kich_thuoc']) ?>"
                       data-luot-tai="<?= (int)$tl['luot_tai'] ?>"
                       data-ngay-dang="<?= date('d/m/Y H:i', strtotime($tl['ngay_dang'])) ?>"
                       data-hoc-phan="<?= e($tl['ten_hp'] ?? 'Không có') ?>"
                       data-mo-ta="<?= e($tl['mo_ta'] ?: 'Không có mô tả') ?>"
                       data-loai-file="<?= e(strtolower($tl['loai_file'] ?? '')) ?>"
                       data-preview-url="<?= !empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan']) ? BASE_URL . '/uploads/' . e($tl['duong_dan']) : '' ?>"
                       data-download-url="<?= !empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan']) ? BASE_URL . '/student/download?id=' . (int)$tl['id'] : '' ?>">
                    <?= fileIcon($tl['loai_file'] ?? '') ?>
                    <div style="min-width: 0; flex: 1;">
                      <div class="my-doc-title" title="<?= e($tl['tieu_de']) ?>" style="font-weight:600; font-size:14px;">
                        <?= e($tl['tieu_de']) ?>
                      </div>
                      <div class="my-doc-meta" style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                        <span><?= formatSize((int)$tl['kich_thuoc']) ?></span>
                        <span>•</span>
                        <span><i class="fas fa-download"></i> <?= (int)$tl['luot_tai'] ?> lượt tải</span>
                        <span>•</span>
                        <span style="font-weight:600; color:<?= $tl['is_public'] ? '#198754' : '#6c757d' ?>;"><?= $tl['is_public'] ? 'Công khai' : 'Riêng tư' ?></span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="my-doc-actions">
                    <?php if (!empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan'])): ?>
                      <a href="<?= BASE_URL ?>/student/download?id=<?= (int)$tl['id'] ?>" class="btn-icon btn-icon-download" title="Tải xuống">
                        <i class="fas fa-download"></i>
                      </a>
                    <?php endif; ?>
                    
                    <button type="button" class="btn-icon btn-icon-edit btn-edit-doc-trigger" title="Sửa tài liệu"
                            data-id="<?= (int)$tl['id'] ?>"
                            data-tieu-de="<?= e($tl['tieu_de']) ?>"
                            data-mo-ta="<?= e($tl['mo_ta'] ?? '') ?>"
                            data-hoc-phan-id="<?= (int)($tl['hoc_phan_id'] ?? 0) ?>"
                            data-is-public="<?= (int)$tl['is_public'] ?>"
                            style="background-color:#e8f4fd; color:#1d9bf0;">
                      <i class="fas fa-edit"></i>
                    </button>

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

  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // JS đóng mở Modal Đăng tài liệu
    const btnToggleUpload = document.getElementById('btnToggleUpload');
    const uploadModal = document.getElementById('uploadModal');
    const btnCloseUploadModal = document.getElementById('btnCloseUploadModal');
    const btnCancelUpload = document.getElementById('btnCancelUpload');
    const uploadModalOverlay = uploadModal ? uploadModal.querySelector('.custom-modal-overlay') : null;

    if (btnToggleUpload && uploadModal) {
        btnToggleUpload.addEventListener('click', function() {
            uploadModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Ngăn cuộn trang nền
        });
    }

    function closeUploadModal() {
        if (uploadModal) {
            uploadModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (btnCloseUploadModal) {
        btnCloseUploadModal.addEventListener('click', closeUploadModal);
    }
    if (uploadModalOverlay) {
        uploadModalOverlay.addEventListener('click', closeUploadModal);
    }

    // JS đóng mở Modal Tài liệu của tôi
    const btnShowMyDocs = document.getElementById('btnShowMyDocs');
    const myDocsModal = document.getElementById('myDocsModal');
    const btnCloseMyDocsModal = document.getElementById('btnCloseMyDocsModal');
    const modalOverlay = myDocsModal ? myDocsModal.querySelector('.custom-modal-overlay') : null;

    if (btnShowMyDocs && myDocsModal) {
        btnShowMyDocs.addEventListener('click', function() {
            myDocsModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Ngăn cuộn trang nền
        });
    }

    function closeModal() {
        if (myDocsModal) {
            myDocsModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (btnCloseMyDocsModal) {
        btnCloseMyDocsModal.addEventListener('click', closeModal);
    }
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }

    // Form reset
    if (btnCancelUpload && uploadModal && btnToggleUpload) {
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
            closeUploadModal();
        });
    }

    // JS cho vùng kéo thả file
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');

    if (uploadZone && fileInput) {
        uploadZone.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileInput.click();
        });
        fileInput.addEventListener('click', (e) => e.stopPropagation());
        
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

    // Chống double submit an toàn cho Form Upload (Dùng pointer-events để tránh làm trình duyệt hủy luồng submit)
    let isUploading = false;
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            if (isUploading) {
                e.preventDefault();
                return false;
            }
            setTimeout(() => {
                if (!e.defaultPrevented) {
                    isUploading = true;
                    const submitBtn = uploadForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.style.pointerEvents = 'none';
                        submitBtn.style.opacity = '0.7';
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng tải...';
                    }
                }
            }, 0);
        });
    }

    // Chống double submit an toàn cho Form Edit
    let isEditing = false;
    const editFormEl = document.getElementById('editForm');
    if (editFormEl) {
        editFormEl.addEventListener('submit', function(e) {
            if (isEditing) {
                e.preventDefault();
                return false;
            }
            setTimeout(() => {
                if (!e.defaultPrevented) {
                    isEditing = true;
                    const submitBtn = editFormEl.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.style.pointerEvents = 'none';
                        submitBtn.style.opacity = '0.7';
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
                    }
                }
            }, 0);
        });
    }

    // JS mở Modal Sửa và nạp dữ liệu
    const editDocModal = document.getElementById('editDocModal');
    const btnCloseEditModal = document.getElementById('btnCloseEditModal');
    const btnCancelEdit = document.getElementById('btnCancelEdit');
    
    document.querySelectorAll('.btn-edit-doc-trigger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Lấy thông tin tài liệu từ thuộc tính
            const id = this.getAttribute('data-id');
            const tieuDe = this.getAttribute('data-tieu-de');
            const moTa = this.getAttribute('data-mo-ta');
            const hpId = this.getAttribute('data-hoc-phan-id');
            const isPublic = this.getAttribute('data-is-public');
            
            // Điền vào form sửa
            document.getElementById('edit_tl_id').value = id;
            document.getElementById('edit_tieu_de').value = tieuDe;
            document.getElementById('edit_mo_ta').value = moTa;
            document.getElementById('edit_hoc_phan_id').value = hpId || '';
            
            if (isPublic === '1') {
                document.getElementById('edit_cong_khai_1').checked = true;
            } else {
                document.getElementById('edit_cong_khai_0').checked = true;
            }
            
            // Xóa file đã chọn trước đó (nếu có)
            document.getElementById('editFileInput').value = '';
            const editFileInfo = document.getElementById('editFileInfo');
            if (editFileInfo) {
                editFileInfo.innerHTML = '';
                editFileInfo.style.display = 'none';
            }
            
            // Đóng modal danh sách của tôi trước
            const myDocsModal = document.getElementById('myDocsModal');
            if (myDocsModal) myDocsModal.classList.remove('active');
            
            // Mở modal sửa
            if (editDocModal) {
                editDocModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    function closeEditModal() {
        if (editDocModal) {
            editDocModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (btnCloseEditModal) btnCloseEditModal.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);

    // JS cho vùng kéo thả file modal sửa
    const editUploadZone = document.getElementById('editUploadZone');
    const editFileInput = document.getElementById('editFileInput');
    const editFileInfo = document.getElementById('editFileInfo');

    if (editUploadZone && editFileInput) {
        editUploadZone.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            editFileInput.click();
        });
        editFileInput.addEventListener('click', (e) => e.stopPropagation());
        editUploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            editUploadZone.style.borderColor = 'var(--primary)';
            editUploadZone.style.background = '#f4f7ff';
        });
        editUploadZone.addEventListener('dragleave', () => {
            editUploadZone.style.borderColor = '#ccc';
            editUploadZone.style.background = '#fff';
        });
        editUploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            editUploadZone.style.borderColor = '#ccc';
            editUploadZone.style.background = '#fff';
            if (e.dataTransfer.files.length) {
                editFileInput.files = e.dataTransfer.files;
                if (editFileInfo) {
                    editFileInfo.innerHTML = `<i class="fas fa-file-alt"></i> <strong>File mới:</strong> ${e.dataTransfer.files[0].name} (${formatBytes(e.dataTransfer.files[0].size)})`;
                    editFileInfo.style.display = 'block';
                }
            }
        });
        editFileInput.addEventListener('change', () => {
            if (editFileInput.files.length) {
                if (editFileInfo) {
                    editFileInfo.innerHTML = `<i class="fas fa-file-alt"></i> <strong>File mới:</strong> ${editFileInput.files[0].name} (${formatBytes(editFileInput.files[0].size)})`;
                    editFileInfo.style.display = 'block';
                }
            }
        });
    }

    // JS hiển thị popup chi tiết tài liệu bằng SweetAlert2
    const detailCards = document.querySelectorAll('.btn-view-detail');
    detailCards.forEach(card => {
        card.addEventListener('click', function() {
            const tieuDe = this.getAttribute('data-tieu-de');
            const nguoiDang = this.getAttribute('data-nguoi-dang');
            const dungLuong = this.getAttribute('data-dung-luong');
            const luotTai = this.getAttribute('data-luot-tai');
            const ngayDang = this.getAttribute('data-ngay-dang');
            const hocPhan = this.getAttribute('data-hoc-phan');
            const moTa = this.getAttribute('data-mo-ta');
            const downloadUrl = this.getAttribute('data-download-url');
            const loaiFile = this.getAttribute('data-loai-file') || '';
            const docId = this.getAttribute('data-id');
            const previewUrl = this.getAttribute('data-preview-url');
            
            let footerBtn = '';
            if (downloadUrl) {
                footerBtn = `<a href="${downloadUrl}" class="swal2-confirm swal2-styled" style="background-color:var(--primary); color:#fff !important; text-decoration:none; display:inline-flex; align-items:center; gap:8px; margin: 0 5px; padding: 10px 24px; border-radius: 6px;"><i class="fas fa-download"></i> Tải xuống tài liệu</a>`;
            } else {
                footerBtn = `<button class="swal2-confirm swal2-styled" style="background-color:#6c757d; color:#fff !important; cursor:not-allowed; margin: 0 5px; padding: 10px 24px; border-radius: 6px;" disabled><i class="fas fa-exclamation-triangle"></i> File không tồn tại</button>`;
            }

            let pdfPreviewHtml = '';
            if (loaiFile.toLowerCase() === 'pdf' && downloadUrl) {
                pdfPreviewHtml = `
                    <div id="pdf-preview-container" style="max-height: 250px; overflow-y: auto; margin-top: 15px; border: 1px solid #eef2f5; padding: 10px; background: #f8f9fa; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); width: 100%;">
                        <h5 style="margin-top:0; margin-bottom:10px; font-size:13px; color:var(--text-dark); text-align:left; font-weight:600; display:flex; align-items:center; gap:6px;"><i class="fas fa-eye" style="color:var(--primary);"></i> Bản xem trước (Tối đa 3 trang):</h5>
                        <div id="pdf-pages" style="display: flex; flex-direction: column; gap: 10px; align-items: center; width: 100%;"></div>
                        <div id="pdf-loading" style="text-align:center; padding: 20px 0; color:var(--text-muted); font-size:13px;"><i class="fas fa-spinner fa-spin"></i> Đang tải bản xem trước...</div>
                    </div>
                `;
            }

            Swal.fire({
                title: '<span style="font-size:18px;font-weight:700;color:var(--primary);"><i class="fas fa-info-circle"></i> Chi tiết tài liệu học tập</span>',
                html: `
                    <div style="text-align: left; font-size: 14px; margin-top: 10px;">
                        <div style="margin-bottom: 12px; line-height: 1.5;">
                            <strong>Tiêu đề:</strong> <span style="color:var(--text-dark); font-weight: 500;">${tieuDe}</span>
                        </div>
                        <div style="margin-bottom: 8px;">
                           <strong>Học phần:</strong> <span class="badge badge-info" style="font-size:12px; padding: 3px 8px; font-weight: 500; background-color:#e1f5fe; color:#0288d1; border-radius:4px;"><i class="fas fa-book"></i> ${hocPhan}</span>
                        </div>
                        <hr style="border:0; border-top:1px solid #eee; margin:12px 0;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div><strong>Người chia sẻ:</strong> <br><i class="fas fa-user" style="color:var(--text-muted)"></i> ${nguoiDang}</div>
                            <div><strong>Ngày đăng:</strong> <br><i class="fas fa-clock" style="color:var(--text-muted)"></i> ${ngayDang}</div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div><strong>Dung lượng:</strong> <br><i class="fas fa-file" style="color:var(--text-muted)"></i> ${dungLuong}</div>
                            <div><strong>Lượt tải:</strong> <br><i class="fas fa-download" style="color:var(--text-muted)"></i> ${luotTai} lượt</div>
                        </div>
                        <hr style="border:0; border-top:1px solid #eee; margin:12px 0;">
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; border: 1px solid #eef2f5;">
                            <strong>Mô tả ngắn:</strong>
                            <div style="color: var(--text-muted); margin-top: 6px; white-space: pre-wrap; line-height: 1.5; font-style: italic;">
                                ${moTa}
                            </div>
                        </div>
                        ${pdfPreviewHtml}
                    </div>
                `,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Đóng',
                cancelButtonColor: '#6c757d',
                footer: footerBtn,
                width: '550px',
                didOpen: () => {
                    if (loaiFile.toLowerCase() === 'pdf' && previewUrl) {
                        const pagesDiv = document.getElementById('pdf-pages');
                        const loadingDiv = document.getElementById('pdf-loading');
                        
                        const pdfjsLib = window.pdfjsLib || window['pdfjs-dist/build/pdf'];
                        if (pdfjsLib) {
                            try {
                                // Khởi tạo Worker vượt qua CORS Web Worker Policy bằng Blob URL
                                const workerCode = `importScripts('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js');`;
                                const blob = new Blob([workerCode], { type: 'application/javascript' });
                                pdfjsLib.GlobalWorkerOptions.workerSrc = URL.createObjectURL(blob);
                                
                                pdfjsLib.getDocument(previewUrl).promise.then(pdf => {
                                    if (loadingDiv) loadingDiv.style.display = 'none';
                                    
                                    const numPages = Math.min(pdf.numPages, 3);
                                    for (let i = 1; i <= numPages; i++) {
                                        pdf.getPage(i).then(page => {
                                            const scale = 1.0;
                                            const viewport = page.getViewport({ scale: scale });
                                            
                                            const canvas = document.createElement('canvas');
                                            canvas.style.width = '100%';
                                            canvas.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
                                            canvas.style.borderRadius = '4px';
                                            canvas.style.marginBottom = '8px';
                                            
                                            const context = canvas.getContext('2d');
                                            canvas.height = viewport.height;
                                            canvas.width = viewport.width;
                                            
                                            pagesDiv.appendChild(canvas);
                                            
                                            const renderContext = {
                                                canvasContext: context,
                                                viewport: viewport
                                            };
                                            page.render(renderContext);
                                        });
                                    }
                                }).catch(err => {
                                    console.error("Lỗi xem trước PDF:", err);
                                    if (loadingDiv) {
                                        loadingDiv.innerHTML = '<span style="color:#dc3545; font-size:12.5px;"><i class="fas fa-exclamation-triangle"></i> Không thể tải bản xem trước file này.</span>';
                                    }
                                });
                            } catch (workerErr) {
                                console.warn("Lỗi khởi tạo Worker, dùng chế độ Fake Worker:", workerErr);
                                pdfjsLib.getDocument(previewUrl).promise.then(pdf => {
                                    if (loadingDiv) loadingDiv.style.display = 'none';
                                    const numPages = Math.min(pdf.numPages, 3);
                                    for (let i = 1; i <= numPages; i++) {
                                        pdf.getPage(i).then(page => {
                                            const scale = 1.0;
                                            const viewport = page.getViewport({ scale: scale });
                                            const canvas = document.createElement('canvas');
                                            canvas.style.width = '100%';
                                            canvas.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
                                            canvas.style.borderRadius = '4px';
                                            canvas.style.marginBottom = '8px';
                                            const context = canvas.getContext('2d');
                                            canvas.height = viewport.height;
                                            canvas.width = viewport.width;
                                            pagesDiv.appendChild(canvas);
                                            const renderContext = { canvasContext: context, viewport: viewport };
                                            page.render(renderContext);
                                        });
                                    }
                                }).catch(err => {
                                    console.error("Lỗi xem trước PDF (Fake Worker mode):", err);
                                    if (loadingDiv) {
                                        loadingDiv.innerHTML = '<span style="color:#dc3545; font-size:12.5px;"><i class="fas fa-exclamation-triangle"></i> Không thể tải bản xem trước file này.</span>';
                                    }
                                });
                            }
                        } else {
                            if (loadingDiv) {
                                loadingDiv.innerHTML = '<span style="color:#dc3545; font-size:12.5px;"><i class="fas fa-exclamation-triangle"></i> Thư viện xem trước chưa được tải.</span>';
                            }
                        }
                    }
                }
            });
        });
    });

});
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
