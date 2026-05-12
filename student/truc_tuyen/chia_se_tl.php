<?php
/**
 * student/truc_tuyen/chia_se_tl.php - UC10: Tải lên và chia sẻ tài liệu
 */
define('ROOT', __DIR__ . '/../..');
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/includes/session.php';

requireStudent();
$sv = getCurrentStudent();
if (!$sv) { header('Location: ' . BASE_URL . '/auth/logout.php'); exit; }

$db  = getDB();
$sid = (int)$sv['id'];

// ── Xử lý upload ────────────────────────────────────────────
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $tieu_de  = trim($_POST['tieu_de'] ?? '');
    $mo_ta    = trim($_POST['mo_ta']   ?? '');
    $hp_id    = (int)($_POST['hoc_phan_id'] ?? 0) ?: null;

    if (empty($tieu_de)) {
        $msg = ['type'=>'danger','text'=>'Vui lòng nhập tiêu đề tài liệu.'];
    } elseif (empty($_FILES['file_upload']['name'])) {
        $msg = ['type'=>'danger','text'=>'Vui lòng chọn file để tải lên.'];
    } else {
        $file     = $_FILES['file_upload'];
        $orig_name= basename($file['name']);
        $ext      = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $size     = $file['size'];
        $tmp      = $file['tmp_name'];

        if (!in_array($ext, ALLOWED_FILE_TYPES)) {
            $msg = ['type'=>'danger','text'=>"Loại file .$ext không được phép. Chỉ chấp nhận: " . implode(', ', ALLOWED_FILE_TYPES)];
        } elseif ($size > MAX_UPLOAD_SIZE) {
            $msg = ['type'=>'danger','text'=>'File quá lớn (tối đa 10MB).'];
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $msg = ['type'=>'danger','text'=>'Có lỗi khi upload file.'];
        } else {
            // Tạo thư mục nếu chưa có
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            $new_name = time() . '_' . $sid . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig_name);
            $dest     = UPLOAD_DIR . $new_name;

            if (move_uploaded_file($tmp, $dest)) {
                $loai = strtoupper($ext);
                $stmt = $db->prepare("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param('iissssss', $sid, $hp_id, $tieu_de, $mo_ta, $orig_name, $new_name, $size, $loai);
                if ($stmt->execute()) {
                    setFlash('success', 'Tải lên tài liệu thành công!');
                    header('Location: ' . BASE_URL . '/student/truc_tuyen/chia_se_tl.php');
                    exit;
                } else {
                    $msg = ['type'=>'danger','text'=>'Lỗi lưu dữ liệu.'];
                    unlink($dest);
                }
                $stmt->close();
            } else {
                $msg = ['type'=>'danger','text'=>'Không thể lưu file. Kiểm tra quyền thư mục upload.'];
            }
        }
    }
}

// ── Xóa tài liệu ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'xoa') {
    $tl_id = (int)($_POST['tl_id'] ?? 0);
    $r = $db->query("SELECT duong_dan FROM tai_lieu WHERE id=$tl_id AND sinh_vien_id=$sid");
    if ($row = $r->fetch_assoc()) {
        $file_path = UPLOAD_DIR . $row['duong_dan'];
        if (file_exists($file_path)) unlink($file_path);
        $db->query("DELETE FROM tai_lieu WHERE id=$tl_id AND sinh_vien_id=$sid");
        setFlash('success', 'Đã xóa tài liệu.');
        header('Location: ' . BASE_URL . '/student/truc_tuyen/chia_se_tl.php');
        exit;
    }
}

// ── Lấy danh sách tài liệu ──────────────────────────────────
$filter_hp = (int)($_GET['hp'] ?? 0);
$where_hp  = $filter_hp ? "AND tl.hoc_phan_id=$filter_hp" : '';
$mode      = $_GET['mode'] ?? 'tat_ca';  // 'tat_ca' | 'cua_toi'
$where_sv  = ($mode === 'cua_toi') ? "AND tl.sinh_vien_id=$sid" : '';

$tl_list = $db->query("
    SELECT tl.*, sv.ho_ten, sv.ma_sv, hp.ten_hp
    FROM tai_lieu tl
    JOIN sinh_vien sv ON sv.id = tl.sinh_vien_id
    LEFT JOIN hoc_phan hp ON hp.id = tl.hoc_phan_id
    WHERE 1=1 $where_sv $where_hp
    ORDER BY tl.ngay_dang DESC
")->fetch_all(MYSQLI_ASSOC);

// Danh sách học phần (để filter và form)
$hp_list = $db->query("
    SELECT hp.id, hp.ten_hp, hp.ma_hp
    FROM ctdt_chi_tiet c JOIN hoc_phan hp ON hp.id=c.hoc_phan_id
    WHERE c.nganh = '{$db->real_escape_string($sv['nganh'])}'
    ORDER BY hp.ten_hp
")->fetch_all(MYSQLI_ASSOC);

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

$page_title  = 'Tài liệu chia sẻ';
$active_menu = 'truc_tuyen';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
        <span>›</span><span>Trực tuyến</span>
        <span>›</span><span>Tài liệu chia sẻ</span>
      </div>
      <h1><i class="fas fa-share-alt"></i> Tài liệu chia sẻ</h1>
      <p>Tải lên và chia sẻ tài liệu học tập với cộng đồng sinh viên.</p>
    </div>

    <!-- Flash -->
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

    <div class="content-grid">

      <!-- Left: Form upload -->
      <div>
        <div class="card fade-in">
          <div class="card-header">
            <h3><i class="fas fa-cloud-upload-alt"></i> Tải tài liệu lên</h3>
          </div>
          <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data"
                  id="uploadForm" data-validate-form novalidate>
              <input type="hidden" name="action" value="upload">

              <!-- Upload zone -->
              <div class="upload-zone" id="uploadZone">
                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <p><strong>Kéo & thả</strong> file vào đây hoặc <strong>click</strong> để chọn</p>
                <p style="margin-top:6px">PDF, Word, Excel, PPT, ZIP — Tối đa 10MB</p>
                <input type="file" id="fileInput" name="file_upload" style="display:none"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.png,.jpg,.jpeg">
              </div>
              <div id="fileInfo" style="display:none;margin:10px 0;padding:10px;background:#f0f4ff;border-radius:6px;font-size:14px"></div>

              <!-- Tiêu đề -->
              <div class="form-group mt-12">
                <label for="tieu_de">Tiêu đề <span class="required">*</span></label>
                <input type="text" id="tieu_de" name="tieu_de" class="form-control"
                       placeholder="VD: Tài liệu ôn tập CNTT001 - Giữa kỳ"
                       data-validate="required" maxlength="200"
                       value="<?= e($_POST['tieu_de'] ?? '') ?>">
                <span class="form-error"></span>
              </div>

              <!-- Học phần -->
              <div class="form-group">
                <label for="hoc_phan_id">Học phần liên quan</label>
                <select id="hoc_phan_id" name="hoc_phan_id" class="form-control">
                  <option value="">— Không chọn —</option>
                  <?php foreach ($hp_list as $hp): ?>
                    <option value="<?= (int)$hp['id'] ?>"><?= e($hp['ten_hp']) ?> (<?= e($hp['ma_hp']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Mô tả -->
              <div class="form-group">
                <label for="mo_ta">Mô tả ngắn</label>
                <textarea id="mo_ta" name="mo_ta" class="form-control" rows="3"
                          placeholder="Mô tả nội dung tài liệu..."
                          style="resize:vertical"><?= e($_POST['mo_ta'] ?? '') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-upload"></i> Tải lên
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Right: Danh sách -->
      <div>

        <!-- Bộ lọc -->
        <div class="card mb-16 fade-in">
          <div class="card-body" style="padding:12px 16px">
            <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
              <div>
                <label style="display:block;font-size:13px;margin-bottom:4px">Hiển thị</label>
                <div style="display:flex;gap:6px">
                  <a href="?mode=tat_ca" class="btn btn-sm <?= $mode==='tat_ca'?'btn-primary':'btn-outline' ?>">Tất cả</a>
                  <a href="?mode=cua_toi" class="btn btn-sm <?= $mode==='cua_toi'?'btn-primary':'btn-outline' ?>"><i class="fas fa-user"></i> Của tôi</a>
                </div>
              </div>
              <div style="flex:1;min-width:140px">
                <label style="display:block;font-size:13px;margin-bottom:4px">Học phần</label>
                <select name="hp" class="form-control" style="font-size:13px;padding:6px">
                  <option value="">Tất cả học phần</option>
                  <?php foreach ($hp_list as $hp): ?>
                    <option value="<?= (int)$hp['id'] ?>" <?= $filter_hp==$hp['id']?'selected':'' ?>><?= e($hp['ten_hp']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <input type="hidden" name="mode" value="<?= e($mode) ?>">
              <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-filter"></i></button>
            </form>
          </div>
        </div>

        <!-- Danh sách tài liệu -->
        <?php if (empty($tl_list)): ?>
          <div class="card fade-in">
            <div class="card-body text-center" style="padding:40px">
              <div style="font-size:48px;margin-bottom:12px">📂</div>
              <h3 style="color:var(--text-muted)">Chưa có tài liệu nào</h3>
              <p class="text-muted">Hãy là người đầu tiên chia sẻ tài liệu!</p>
            </div>
          </div>
        <?php else: ?>
          <div class="tl-grid">
          <?php foreach ($tl_list as $tl): ?>
            <div class="tl-card fade-in">
              <div style="display:flex;gap:12px;align-items:flex-start">
                <?= fileIcon($tl['loai_file'] ?? '') ?>
                <div style="flex:1;min-width:0">
                  <div class="tl-title" title="<?= e($tl['tieu_de']) ?>">
                    <?= e(mb_strimwidth($tl['tieu_de'], 0, 60, '...')) ?>
                  </div>
                  <?php if (!empty($tl['ten_hp'])): ?>
                    <div style="font-size:12px;color:var(--primary);margin-top:2px">
                      <i class="fas fa-book"></i> <?= e($tl['ten_hp']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <?php if (!empty($tl['mo_ta'])): ?>
                <div style="font-size:13px;color:var(--text-muted);line-height:1.5">
                  <?= e(mb_strimwidth($tl['mo_ta'], 0, 80, '...')) ?>
                </div>
              <?php endif; ?>

              <div class="tl-meta">
                <span><i class="fas fa-user"></i> <?= e($tl['ho_ten']) ?></span>
                <span><i class="fas fa-file"></i> <?= formatSize((int)$tl['kich_thuoc']) ?></span>
                <span><i class="fas fa-download"></i> <?= (int)$tl['luot_tai'] ?></span>
              </div>
              <div style="font-size:12px;color:var(--text-muted)">
                <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($tl['ngay_dang'])) ?>
              </div>

              <div class="tl-actions">
                <?php if (!empty($tl['duong_dan']) && file_exists(UPLOAD_DIR . $tl['duong_dan'])): ?>
                  <a href="<?= BASE_URL ?>/student/truc_tuyen/download.php?id=<?= (int)$tl['id'] ?>"
                     class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
                    <i class="fas fa-download"></i> Tải xuống
                  </a>
                <?php else: ?>
                  <span class="btn btn-secondary btn-sm" style="flex:1;opacity:.5;cursor:not-allowed;justify-content:center">
                    <i class="fas fa-exclamation-triangle"></i> File không tồn tại
                  </span>
                <?php endif; ?>

                <?php if ($tl['sinh_vien_id'] == $sid): ?>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="action" value="xoa">
                    <input type="hidden" name="tl_id" value="<?= (int)$tl['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm"
                            data-confirm="Xóa tài liệu &quot;<?= e($tl['tieu_de']) ?>&quot;?">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </div>

            </div>
          <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div><!-- /right -->

    </div><!-- /content-grid -->

  </div>
</div>

<?php require_once ROOT . '/includes/footer.php'; ?>
