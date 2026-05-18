<?php
/**
 * student/ca_nhan/cap_nhat.php - Cập nhật SĐT, Email và Ảnh đại diện
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
$errors  = [];
$success = false;

// ── Hằng số upload ────────────────────────────────────────────
define('UPLOAD_DIR',      ROOT . '/uploads/avatars/');
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024); // 2 MB
define('UPLOAD_ALLOWED',  ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Tạo thư mục nếu chưa có
if (!is_dir(UPLOAD_DIR)) {
    if (!@mkdir(UPLOAD_DIR, 0775, true)) {
        // Ghi log lỗi để debug
        error_log('[QNU-SMS] Không thể tạo thư mục: ' . UPLOAD_DIR);
    }
}

// ── Xử lý POST ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $sdt   = trim($_POST['so_dien_thoai'] ?? '');

    // --- Validate Email ---
    if (empty($email)) {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Địa chỉ email không hợp lệ.';
    }

    // --- Validate SĐT ---
    if (!empty($sdt) && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $sdt)) {
        $errors['sdt'] = 'Số điện thoại không hợp lệ (VD: 0912345678).';
    }

    // --- Xử lý upload ảnh ---
    $new_avatar = null; // null = không thay đổi
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Lỗi khi tải file lên. Vui lòng thử lại.';
        } elseif ($file['size'] > UPLOAD_MAX_SIZE) {
            $errors['avatar'] = 'Ảnh quá lớn. Tối đa 2MB.';
        } elseif (!in_array(mime_content_type($file['tmp_name']), UPLOAD_ALLOWED)) {
            $errors['avatar'] = 'Chỉ chấp nhận ảnh JPG, PNG, GIF, WEBP.';
        } else {
            // Tạo tên file duy nhất
            $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename   = 'avatar_' . $sid . '_' . time() . '.' . $ext;
            $dest       = UPLOAD_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Xóa ảnh cũ nếu có
                if (!empty($sv['anh_dai_dien'])) {
                    $old = ROOT . '/uploads/' . $sv['anh_dai_dien'];
                    if (file_exists($old)) @unlink($old);
                }
                $new_avatar = 'avatars/' . $filename;
            } else {
                $errors['avatar'] = 'Không thể lưu ảnh. Kiểm tra quyền thư mục uploads/.';
            }
        }
    }

    // --- Lưu vào DB ---
    if (empty($errors)) {
        if ($new_avatar !== null) {
            $stmt = $db->prepare("UPDATE sinh_vien SET email=?, so_dien_thoai=?, anh_dai_dien=? WHERE id=?");
            $stmt->bind_param('sssi', $email, $sdt, $new_avatar, $sid);
        } else {
            $stmt = $db->prepare("UPDATE sinh_vien SET email=?, so_dien_thoai=? WHERE id=?");
            $stmt->bind_param('ssi', $email, $sdt, $sid);
        }

        if ($stmt->execute()) {
            setFlash('success', 'Cập nhật thông tin thành công!');
            header('Location: ' . BASE_URL . '/student/ca_nhan/cap_nhat.php');
            exit;
        } else {
            $errors['db'] = 'Có lỗi xảy ra khi lưu dữ liệu.';
        }
        $stmt->close();
    }

    // Giữ lại giá trị người dùng nhập
    $sv['email']         = $email;
    $sv['so_dien_thoai'] = $sdt;
}

// ── Avatar hiện tại ────────────────────────────────────────────
$avatar_url = (!empty($sv['anh_dai_dien']))
    ? BASE_URL . '/uploads/' . e($sv['anh_dai_dien'])
    : BASE_URL . '/assets/img/default-avatar.png';

$page_title  = 'Cập nhật thông tin';
$active_menu = 'ca_nhan';
require_once ROOT . '/includes/header.php';
?>

<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container" style="max-width:780px">

    <!-- Tiêu đề -->
    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard.php">Tổng quan</a>
        <span>›</span>
        <a href="<?= BASE_URL ?>/student/ca_nhan/thong_tin.php">Cá nhân</a>
        <span>›</span><span>Cập nhật</span>
      </div>
      <h1><i class="fas fa-edit"></i> Cập nhật thông tin</h1>
      <p>Chỉnh sửa <strong>Email</strong>, <strong>Số điện thoại</strong> và <strong>Ảnh đại diện</strong>. Thông tin khác liên hệ phòng Đào tạo.</p>
    </div>

    <!-- Flash / Lỗi DB -->
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" data-auto-dismiss>
        <i class="fas fa-check-circle"></i> <?= e($flash['msg']) ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($errors['db'])): ?>
      <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= e($errors['db']) ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" id="updateForm" novalidate>

      <!-- ── CARD 1: Ảnh đại diện ── -->
      <div class="card fade-in" style="margin-bottom:20px">
        <div class="card-header">
          <h3><i class="fas fa-camera"></i> Ảnh đại diện</h3>
        </div>
        <div class="card-body">
          <div style="display:flex;align-items:center;gap:28px;flex-wrap:wrap">

            <!-- Preview ảnh -->
            <div style="position:relative;flex-shrink:0">
              <img id="avatarPreview" src="<?= $avatar_url ?>" alt="Ảnh đại diện"
                   style="width:110px;height:110px;border-radius:50%;object-fit:cover;
                          border:4px solid var(--primary-light);box-shadow:0 2px 12px rgba(0,86,179,.15)">
              <label for="avatar"
                     style="position:absolute;bottom:4px;right:4px;
                            width:32px;height:32px;border-radius:50%;
                            background:var(--primary);color:#fff;
                            display:flex;align-items:center;justify-content:center;
                            cursor:pointer;font-size:14px;
                            box-shadow:0 2px 6px rgba(0,0,0,.25);
                            transition:background .2s"
                     title="Chọn ảnh mới"
                     onmouseover="this.style.background='#004a9e'"
                     onmouseout="this.style.background='var(--primary)'">
                <i class="fas fa-pen"></i>
              </label>
            </div>

            <!-- Hướng dẫn + input -->
            <div style="flex:1;min-width:200px">
              <p style="font-size:14px;color:var(--text);margin-bottom:10px">
                <strong>Chọn ảnh mới</strong> từ máy tính của bạn.<br>
                <span style="font-size:13px;color:var(--text-muted)">
                  Hỗ trợ: JPG, PNG, GIF, WEBP &mdash; Tối đa <strong>2MB</strong>
                </span>
              </p>

              <input type="file" id="avatar" name="avatar" accept="image/*"
                     style="display:none" onchange="previewAvatar(this)">

              <label for="avatar" class="btn btn-secondary" style="cursor:pointer;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-upload"></i> Chọn ảnh
              </label>
              <span id="avatarFileName" style="font-size:13px;color:var(--text-muted);margin-left:10px"></span>

              <?php if (!empty($errors['avatar'])): ?>
                <p style="color:var(--danger);font-size:13px;margin-top:8px">
                  <i class="fas fa-exclamation-circle"></i> <?= e($errors['avatar']) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ── CARD 2: Thông tin ── -->
      <div class="card fade-in">
        <div class="card-header">
          <h3><i class="fas fa-user-edit"></i> Thông tin có thể chỉnh sửa</h3>
        </div>
        <div class="card-body">

          <!-- Thông tin chỉ đọc -->
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Họ và tên</label>
              <input type="text" class="form-control" value="<?= e($sv['ho_ten']) ?>" disabled>
            </div>
            <div class="form-group">
              <label>MSSV</label>
              <input type="text" class="form-control" value="<?= e($sv['ma_sv']) ?>" disabled>
            </div>
          </div>
          <div class="form-row" style="margin-bottom:4px">
            <div class="form-group">
              <label>Ngày sinh</label>
              <input type="text" class="form-control"
                     value="<?= $sv['ngay_sinh'] ? date('d/m/Y', strtotime($sv['ngay_sinh'])) : '' ?>" disabled>
            </div>
            <div class="form-group">
              <label>Lớp</label>
              <input type="text" class="form-control" value="<?= e($sv['lop']) ?>" disabled>
            </div>
          </div>

          <hr style="border:none;border-top:1px dashed var(--border);margin:16px 0">
          <p class="text-muted mb-16" style="font-size:14px">
            <i class="fas fa-pencil-alt" style="color:var(--primary)"></i> Chỉnh sửa các trường bên dưới:
          </p>

          <!-- Email -->
          <div class="form-group">
            <label for="email">
              <i class="fas fa-envelope"></i> Email liên hệ <span class="required">*</span>
            </label>
            <input type="email" id="email" name="email"
                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   value="<?= e($sv['email'] ?? '') ?>"
                   placeholder="example@gmail.com"
                   autocomplete="email">
            <?php if (isset($errors['email'])): ?>
              <span class="form-error" style="display:block"><?= e($errors['email']) ?></span>
            <?php else: ?>
              <span class="form-hint">Địa chỉ email dùng để nhận thông báo từ trường.</span>
            <?php endif; ?>
          </div>

          <!-- SĐT -->
          <div class="form-group">
            <label for="so_dien_thoai">
              <i class="fas fa-phone"></i> Số điện thoại
            </label>
            <input type="tel" id="so_dien_thoai" name="so_dien_thoai"
                   class="form-control <?= isset($errors['sdt']) ? 'is-invalid' : '' ?>"
                   value="<?= e($sv['so_dien_thoai'] ?? '') ?>"
                   placeholder="0912 345 678"
                   autocomplete="tel">
            <?php if (isset($errors['sdt'])): ?>
              <span class="form-error" style="display:block"><?= e($errors['sdt']) ?></span>
            <?php else: ?>
              <span class="form-hint">Số điện thoại liên hệ (không bắt buộc).</span>
            <?php endif; ?>
          </div>

          <!-- Nút -->
          <div style="display:flex;gap:12px;margin-top:8px">
            <button type="submit" class="btn btn-primary" id="btnSave">
              <i class="fas fa-save"></i> Lưu thay đổi
            </button>
            <a href="<?= BASE_URL ?>/student/ca_nhan/thong_tin.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Quay lại
            </a>
          </div>

        </div>
        <div class="card-footer">
          <p class="text-muted" style="font-size:13px">
            <i class="fas fa-info-circle"></i>
            Muốn thay đổi thông tin khác (họ tên, ngày sinh, lớp...) vui lòng liên hệ
            <strong>Phòng Đào tạo</strong> ĐT: (0256) 3846 344.
          </p>
        </div>
      </div>

    </form>

  </div>
</div>

<script>
function previewAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];

  // Hiển thị tên file
  document.getElementById('avatarFileName').textContent = file.name;

  // Kiểm tra kích thước client-side
  if (file.size > 2 * 1024 * 1024) {
    alert('Ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 2MB.');
    input.value = '';
    document.getElementById('avatarFileName').textContent = '';
    return;
  }

  // Preview ngay lập tức
  const reader = new FileReader();
  reader.onload = function (e) {
    document.getElementById('avatarPreview').src = e.target.result;
  };
  reader.readAsDataURL(file);
}
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
