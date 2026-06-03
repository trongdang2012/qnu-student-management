<?php
function formatLichHoc($thu, $tiet_bd, $so_tiet, $phong, $gv): string {
    if (!$thu) return '<span class="text-muted">Chưa xếp lịch học</span>';
    $thu_lbl = $thu == 8 ? 'Chủ nhật' : "Thứ $thu";
    $tiet_kt = $tiet_bd + $so_tiet - 1;
    return "<strong>$thu_lbl</strong><div class='text-muted' style='font-size:12px;margin-top:2px;'><i class='fas fa-clock'></i> Tiết $tiet_bd - $tiet_kt<br><i class='fas fa-map-marker-alt'></i> Phòng $phong<br><i class='fas fa-user-tie'></i> $gv</div>";
}

function formatTienQuyet($ma_prereq, $db): string {
    if (!$ma_prereq) return '<span class="text-muted">—</span>';
    $res = $db->fetch("SELECT ten_hp FROM hoc_phan WHERE ma_hp = :ma", ['ma' => $ma_prereq]);
    $name = $res ? $res['ten_hp'] : $ma_prereq;
    return "<span class='badge badge-warning' style='font-size:11px;' data-tooltip='Yêu cầu hoàn thành trước'><i class='fas fa-link'></i> $name</span>";
}

function dkBadge(string $tt): string {
    return match($tt) {
        'Đã duyệt' => '<span class="badge badge-success"><i class="fas fa-check"></i> Đã duyệt</span>',
        'Từ chối'  => '<span class="badge badge-danger"><i class="fas fa-times"></i> Từ chối</span>',
        'Đã hủy'   => '<span class="badge badge-secondary">Đã hủy</span>',
        default    => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Chờ duyệt</span>',
    };
}
?>
<?php require_once ROOT . '/includes/header.php'; ?>
<?php require_once ROOT . '/includes/navbar_student.php'; ?>

<div class="student-wrapper">
  <div class="student-container">

    <div class="page-title fade-in">
      <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/dashboard">Tổng quan</a>
        <span>›</span><span>Trực tuyến</span>
        <span>›</span><span>Đăng ký học phần</span>
      </div>
      <h1><i class="fas fa-plus-circle"></i> Đăng ký lớp học phần</h1>
      <p>Học kỳ <?= $hk ?> — Năm học <?= $nh ?> — Hệ thống đăng ký tín chỉ trực tuyến QNU</p>
    </div>

    <!-- Thông tin tổng quan -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--primary)"><?= count($da_dk) ?></div>
            <div class="stat-label">Lớp học phần đã đăng ký HK<?= $hk ?></div>
          </div>
        </div>
      </div>
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon green"><i class="fas fa-layer-group"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--success)"><?= $tc_dang_ky ?></div>
            <div class="stat-label">Số tín chỉ tích lũy đăng ký (Khuyến nghị 15-21 TC)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs danh sách lớp HP -->
    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <div class="dk-tabs" style="padding:0 20px;margin:0">
          <button class="dk-tab active" data-tab="da-dk" id="tab-da-dk">
            <i class="fas fa-list-check"></i> Lớp HP đã đăng ký (<?= count($da_dk) ?>)
          </button>
          <button class="dk-tab" data-tab="co-the" id="tab-co-the">
            <i class="fas fa-plus"></i> Lớp HP đang mở đăng ký (<?= count($co_the_dk) ?>)
          </button>
        </div>

        <!-- Panel 1: Đã đăng ký -->
        <div class="dk-panel active" id="panel-da-dk">
          <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Mã lớp HP</th>
              <th>Tên học phần</th>
              <th style="text-align:center">Tín chỉ</th>
              <th style="text-align:center">Loại môn</th>
              <th>Giảng viên & Lịch học</th>
              <th style="text-align:center">Ngày đăng ký</th>
              <th style="text-align:center">Trạng thái</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($da_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Chưa đăng ký lớp học phần nào. Vui lòng chuyển sang tab bên phải để đăng ký.
              </td></tr>
            <?php else: ?>
            <?php foreach ($da_dk as $dk): ?>
              <tr>
                <td><code><?= e($dk['ma_lop_hp']) ?></code></td>
                <td class="fw-500">
                  <strong><?= e($dk['ten_hp']) ?></strong><br>
                  <small style="color:#666">Mã HP: <?= e($dk['ma_hp']) ?></small>
                </td>
                <td style="text-align:center;font-weight:bold"><?= (int)$dk['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-secondary"><?= e($dk['loai'] ?? '') ?></span>
                </td>
                <td><?= formatLichHoc($dk['thu'], $dk['tiet_bat_dau'], $dk['so_tiet'], $dk['phong_hoc'], $dk['giang_vien']) ?></td>
                <td style="text-align:center;font-size:13px">
                  <?= date('d/m/Y H:i', strtotime($dk['ngay_dang_ky'])) ?>
                </td>
                <td style="text-align:center"><?= dkBadge($dk['trang_thai']) ?></td>
                <td style="text-align:center">
                  <?php 
                    $now = date('Y-m-d H:i:s');
                    $is_registration_active = ($dk['trang_thai_mo_lop'] === 'Đang mở')
                      && ($dk['ngay_bat_dau_dk'] === null || $now >= $dk['ngay_bat_dau_dk'])
                      && ($dk['ngay_ket_thuc_dk'] === null || $now <= $dk['ngay_ket_thuc_dk']);
                  ?>
                  <?php if (($dk['trang_thai'] === 'Chờ duyệt' || $dk['trang_thai'] === 'Đã duyệt') && $is_registration_active): ?>
                    <form class="ajax-form-dk" method="POST" style="display:inline" data-confirm="Bạn có chắc chắn muốn hủy đăng ký Lớp học phần này?">
                      <input type="hidden" name="action" value="huy">
                      <input type="hidden" name="lop_hoc_phan_id" value="<?= (int)$dk['lop_hoc_phan_id'] ?>">
                      <button type="button" class="btn btn-danger btn-sm btn-submit-dk">
                        <i class="fas fa-times"></i> Hủy lớp
                      </button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--text-muted);font-size:13px" title="Hết thời gian đăng ký hoặc lớp đã đóng."><i class="fas fa-lock"></i> Đã khóa</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Panel 2: Có thể đăng ký -->
        <div class="dk-panel" id="panel-co-the">
          <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
            <input type="text" id="tableSearch" data-table="#tblCoDk"
                   placeholder="🔍 Tìm nhanh lớp học phần, tên học phần..." class="form-control"
                   style="max-width:320px;padding:7px 12px;font-size:14px">
          </div>
          <div class="table-wrap">
          <table id="tblCoDk">
            <thead><tr>
              <th>Mã lớp HP</th>
              <th>Tên học phần</th>
              <th style="text-align:center">Tín chỉ</th>
              <th style="text-align:center">Loại môn</th>
              <th>Lịch học dự kiến</th>
              <th>Học phần tiên quyết</th>
              <th style="text-align:center">Sĩ số còn lại</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($co_the_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Hiện tại không có lớp học phần nào mở cho ngành học của bạn hoặc bạn đã hoàn thành hết chương trình đào tạo.
              </td></tr>
            <?php else: ?>
            <?php 
            $db = \App\Core\Database::getInstance();
            foreach ($co_the_dk as $hp): 
              $con_lai = $hp['si_so_toi_da'] - $hp['si_so_hien_tai'];
              $is_full = $con_lai <= 0;
            ?>
              <tr>
                <td><code><?= e($hp['ma_lop_hp']) ?></code></td>
                <td class="fw-500">
                  <strong><?= e($hp['ten_hp']) ?></strong><br>
                  <small style="color:#666">Mã HP: <?= e($hp['ma_hp']) ?></small>
                </td>
                <td style="text-align:center;font-weight:700"><?= (int)$hp['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-<?= $hp['loai']==='Bắt buộc'?'danger':($hp['loai']==='Tự chọn'?'warning':'info') ?>">
                    <?= e($hp['loai']) ?>
                  </span>
                </td>
                <td><?= formatLichHoc($hp['thu'], $hp['tiet_bat_dau'], $hp['so_tiet'], $hp['phong_hoc'], $hp['giang_vien']) ?></td>
                <td><?= formatTienQuyet($hp['ma_hp_tien_quyet'], $db) ?></td>
                <td style="text-align:center">
                  <?php if ($is_full): ?>
                    <span class="badge badge-danger" style="font-weight:700">Hết chỗ (<?= $hp['si_so_hien_tai'] ?>/<?= $hp['si_so_toi_da'] ?>)</span>
                  <?php else: ?>
                    <span class="badge badge-success" style="font-weight:700">Còn <?= $con_lai ?> / <?= $hp['si_so_toi_da'] ?></span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <form class="ajax-form-dk" method="POST" style="display:inline" data-confirm="Bạn có chắc chắn muốn đăng ký lớp học phần này: <?= e($hp['ma_lop_hp']) ?>?">
                    <input type="hidden" name="action" value="dang_ky">
                    <input type="hidden" name="lop_hoc_phan_id" value="<?= (int)$hp['lop_hoc_phan_id'] ?>">
                    <?php if ($is_full): ?>
                      <button type="button" class="btn btn-secondary btn-sm btn-submit-dk" disabled>
                        <i class="fas fa-ban"></i> Đã đầy
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-primary btn-sm btn-submit-dk">
                        <i class="fas fa-plus"></i> Đăng ký lớp
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
          </div>
        </div>

      </div>
    </div>

    <!-- Hướng dẫn đăng ký học phần -->
    <div class="card mt-16 fade-in">
      <div class="card-body">
        <h4 style="color:var(--primary);margin-bottom:10px"><i class="fas fa-info-circle"></i> Quy chế đăng ký tín chỉ trực tuyến</h4>
        <ul style="font-size:14px;color:var(--text-muted);line-height:2;padding-left:20px">
          <li>Mỗi sinh viên đăng ký học tập trực tuyến thông qua việc chọn đăng ký vào các **Lớp học phần** đang mở tương ứng.</li>
          <li>Hệ thống tự động kiểm tra trùng lịch học cá nhân ngay khi bạn bấm nút Đăng ký lớp học phần. Nếu trùng lịch học, hệ thống sẽ chặn và hiển thị thông báo.</li>
          <li>Khi đăng ký thành công, hệ thống sẽ tự động phê duyệt ngay lập tức (trạng thái hiển thị là **Đã duyệt**) và sĩ số lớp học phần sẽ tự động tăng lên 1.</li>
          <li>Bạn có thể tự hủy lớp học phần đã đăng ký (ở trạng thái **Đã duyệt**) bất cứ lúc nào trong thời gian đợt đăng ký tín chỉ còn mở.</li>
          <li>Trong trường hợp lớp học phần đã đầy sĩ số tối đa, hệ thống sẽ khóa và không cho phép đăng ký thêm nữa.</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dkForms = document.querySelectorAll('.ajax-form-dk');
    
    dkForms.forEach(form => {
        const btn = form.querySelector('.btn-submit-dk');
        if (!btn) return;
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmMsg = form.getAttribute('data-confirm');
            
            Swal.fire({
                title: 'Xác nhận đăng ký',
                text: confirmMsg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0056B3',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Xác nhận',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                if (result.isConfirmed) {
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kết nối...';
                    
                    const formData = new FormData(form);
                    fetch('<?= BASE_URL ?>/student/dang-ky-hoc-phan', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.type) {
                            Swal.fire({
                                icon: data.type === 'danger' ? 'error' : (data.type === 'success' ? 'success' : 'warning'),
                                title: data.type === 'success' ? 'Thành công' : 'Thông báo hệ thống',
                                text: data.text,
                                showConfirmButton: data.type !== 'success',
                                timer: data.type === 'success' ? 1800 : undefined
                            }).then(() => {
                                if (data.type === 'success') {
                                    window.location.reload();
                                } else {
                                    btn.disabled = false;
                                    btn.innerHTML = originalHtml;
                                }
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Lỗi kết nối', 'Mất kết nối với máy chủ QNU, vui lòng tải lại trang và thử lại.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
                }
            });
        });
    });
});
</script>

<?php require_once ROOT . '/includes/footer.php'; ?>
