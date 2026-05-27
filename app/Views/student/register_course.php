<?php
function formatLichHoc($thu, $tiet_bd, $so_tiet, $phong, $gv): string {
    if (!$thu) return '<span class="text-muted">Chưa xếp lịch</span>';
    $thu_lbl = $thu == 8 ? 'Chủ nhật' : "Thứ $thu";
    $tiet_kt = $tiet_bd + $so_tiet - 1;
    return "<strong>$thu_lbl</strong><div class='text-muted' style='font-size:12px;margin-top:2px;'><i class='fas fa-clock'></i> Tiết $tiet_bd - $tiet_kt<br><i class='fas fa-map-marker-alt'></i> Phòng $phong<br><i class='fas fa-user-tie'></i> $gv</div>";
}

function formatTienQuyet($ma_prereq, $db): string {
    if (!$ma_prereq) return '<span class="text-muted">—</span>';
    // $db is actually \App\Core\Database::getInstance()
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
      <h1><i class="fas fa-plus-circle"></i> Đăng ký học phần</h1>
      <p>Học kỳ <?= $hk ?> — Năm học <?= $nh ?></p>
    </div>

    <!-- Thông tin -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--primary)"><?= count($da_dk) ?></div>
            <div class="stat-label">Học phần đã đăng ký HK<?= $hk ?></div>
          </div>
        </div>
      </div>
      <div class="card fade-in">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;padding:16px">
          <div class="stat-icon green"><i class="fas fa-layer-group"></i></div>
          <div>
            <div style="font-size:28px;font-weight:700;color:var(--success)"><?= $tc_dang_ky ?></div>
            <div class="stat-label">Tín chỉ đã đăng ký (khuyến nghị 15-21 TC)</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card fade-in">
      <div class="card-body" style="padding:0">
        <div class="dk-tabs" style="padding:0 20px;margin:0">
          <button class="dk-tab active" data-tab="da-dk" id="tab-da-dk">
            <i class="fas fa-list-check"></i> Đã đăng ký (<?= count($da_dk) ?>)
          </button>
          <button class="dk-tab" data-tab="co-the" id="tab-co-the">
            <i class="fas fa-plus"></i> Học phần có thể đăng ký (<?= count($co_the_dk) ?>)
          </button>
        </div>

        <!-- Panel: Đã đăng ký -->
        <div class="dk-panel active" id="panel-da-dk">
          <div class="table-wrap">
          <table>
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th>Lịch học</th>
              <th style="text-align:center">Ngày đăng ký</th>
              <th style="text-align:center">Trạng thái</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($da_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Chưa đăng ký học phần nào. Chọn tab bên phải để đăng ký.
              </td></tr>
            <?php else: ?>
            <?php foreach ($da_dk as $dk): ?>
              <tr>
                <td><code><?= e($dk['ma_hp']) ?></code></td>
                <td class="fw-500"><?= e($dk['ten_hp']) ?></td>
                <td style="text-align:center"><?= (int)$dk['so_tin_chi'] ?></td>
                <td style="text-align:center">
                  <span class="badge badge-secondary"><?= e($dk['loai'] ?? '') ?></span>
                </td>
                <td><?= formatLichHoc($dk['thu'], $dk['tiet_bat_dau'], $dk['so_tiet'], $dk['phong_hoc'], $dk['giang_vien']) ?></td>
                <td style="text-align:center;font-size:13px">
                  <?= date('d/m/Y H:i', strtotime($dk['ngay_dang_ky'])) ?>
                </td>
                <td style="text-align:center"><?= dkBadge($dk['trang_thai']) ?></td>
                <td style="text-align:center">
                  <?php if ($dk['trang_thai'] === 'Chờ duyệt'): ?>
                    <form class="ajax-form-dk" method="POST" style="display:inline" data-confirm="Bạn có chắc muốn hủy đăng ký học phần này?">
                      <input type="hidden" name="action" value="huy">
                      <input type="hidden" name="hoc_phan_id" value="<?= (int)$dk['hoc_phan_id'] ?>">
                      <button type="button" class="btn btn-danger btn-sm btn-submit-dk">
                        <i class="fas fa-times"></i> Hủy
                      </button>
                    </form>
                  <?php else: ?>
                    <span style="color:var(--text-muted);font-size:13px">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Panel: Có thể đăng ký -->
        <div class="dk-panel" id="panel-co-the">
          <div style="padding:12px 20px;border-bottom:1px solid var(--border)">
            <input type="text" id="tableSearch" data-table="#tblCoDk"
                   placeholder="🔍 Tìm học phần..." class="form-control"
                   style="max-width:280px;padding:7px 12px;font-size:14px">
          </div>
          <div class="table-wrap">
          <table id="tblCoDk">
            <thead><tr>
              <th>Mã HP</th><th>Tên học phần</th>
              <th style="text-align:center">TC</th>
              <th style="text-align:center">Loại</th>
              <th>Lịch học</th>
              <th>Học phần tiên quyết</th>
              <th style="text-align:center">Còn lại</th>
              <th style="text-align:center">Thao tác</th>
            </tr></thead>
            <tbody>
            <?php if (empty($co_the_dk)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted)">
                Bạn đã đăng ký hoặc hoàn thành tất cả học phần.
              </td></tr>
            <?php else: ?>
            <?php 
            $db = \App\Core\Database::getInstance();
            foreach ($co_the_dk as $hp): 
              $con_lai = $hp['si_so_toi_da'] - $hp['si_so_hien_tai'];
              $is_full = $con_lai <= 0;
            ?>
              <tr>
                <td><code><?= e($hp['ma_hp']) ?></code></td>
                <td class="fw-500"><?= e($hp['ten_hp']) ?></td>
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
                    <span class="badge badge-danger" style="font-weight:700">Đầy (0/<?= $hp['si_so_toi_da'] ?>)</span>
                  <?php else: ?>
                    <span class="badge badge-success" style="font-weight:700"><?= $con_lai ?> / <?= $hp['si_so_toi_da'] ?></span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center">
                  <form class="ajax-form-dk" method="POST" style="display:inline" data-confirm="<?= $is_full ? 'Học phần đã đủ số lượng, bạn có chắc chắn muốn thử đăng ký?' : 'Đăng ký học phần: ' . e($hp['ten_hp']) . '?' ?>">
                    <input type="hidden" name="action" value="dang_ky">
                    <input type="hidden" name="hoc_phan_id" value="<?= (int)$hp['id'] ?>">
                    <?php if ($is_full): ?>
                      <button type="button" class="btn btn-secondary btn-sm btn-submit-dk">
                        <i class="fas fa-exclamation-triangle"></i> Đăng ký (Đầy)
                      </button>
                    <?php else: ?>
                      <button type="button" class="btn btn-primary btn-sm btn-submit-dk">
                        <i class="fas fa-plus"></i> Đăng ký
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

      </div><!-- /card-body -->
    </div><!-- /card -->

    <!-- Ghi chú -->
    <div class="card mt-16 fade-in">
      <div class="card-body">
        <h4 style="color:var(--primary);margin-bottom:10px"><i class="fas fa-info-circle"></i> Lưu ý khi đăng ký học phần</h4>
        <ul style="font-size:14px;color:var(--text-muted);line-height:2;padding-left:20px">
          <li>Sinh viên chỉ được đăng ký tối đa <strong>30 tín chỉ/học kỳ</strong> và tối thiểu <strong>12 tín chỉ/học kỳ</strong> (trừ kỳ cuối).</li>
          <li>Sau khi đăng ký, học phần sẽ ở trạng thái <strong>Chờ duyệt</strong> cho đến khi Phòng Đào tạo xét duyệt.</li>
          <li>Bạn chỉ có thể <strong>hủy</strong> học phần khi trạng thái là <strong>Chờ duyệt</strong>.</li>
          <li>Hạn đăng ký học phần: <strong>2 tuần đầu mỗi học kỳ</strong>.</li>
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
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmMsg = form.getAttribute('data-confirm');
            
            Swal.fire({
                title: 'Xác nhận',
                text: confirmMsg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0056B3',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button to prevent double submit
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
                    
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
                                title: data.type === 'success' ? 'Thành công' : 'Thông báo',
                                text: data.text,
                                showConfirmButton: data.type !== 'success',
                                timer: data.type === 'success' ? 1500 : undefined
                            }).then(() => {
                                if (data.type === 'success') {
                                    // Tải lại trang nhẹ nhàng để cập nhật cả 2 bảng
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
                        Swal.fire('Lỗi', 'Mất kết nối với máy chủ!', 'error');
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
