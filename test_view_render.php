<?php
require_once 'config/database.php';

function formatMoney($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}

function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function statusBadge(string $tt): string {
    return match($tt) {
        'Đã nộp'  => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Đã nộp đủ</span>',
        'Nợ'      => '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Còn nợ</span>',
        default   => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Chưa nộp</span>',
    };
}

// Giả lập StudentModel::getTuitionFees()
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$studentId = 1;

$sql = "SELECT hp.* FROM hoc_phi hp WHERE hp.sinh_vien_id = :sid ORDER BY hp.nam_hoc DESC, hp.hoc_ky DESC";
$hp_list = $db->prepare($sql);
$hp_list->execute(['sid' => $studentId]);
$hp_list = $hp_list->fetchAll();

$tong_no = 0;
$tong_da_nop = 0;
$tong_hoc_phi = 0;

foreach ($hp_list as &$hp) {
    $tong_hoc_phi += $hp['so_tien'];
    $tong_da_nop += $hp['da_nop'];
    $no = $hp['so_tien'] - $hp['da_nop'];
    
    if ((float)$hp['so_tien'] > 0 && (float)$hp['da_nop'] >= (float)$hp['so_tien']) {
        $hp['trang_thai'] = 'Đã nộp';
    } elseif ($no > 0) {
        $hp['trang_thai'] = 'Nợ';
    } else {
        $hp['trang_thai'] = 'Chưa nộp';
    }
    if ($no > 0) {
        $tong_no += $no;
    }
    
    // Lấy danh sách học phần đã đăng ký trong học kỳ này
    $registered_courses = $db->prepare("
        SELECT hp_reg.ma_hp, hp_reg.ten_hp, hp_reg.so_tin_chi
        FROM dang_ky_hp dk
        JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
        JOIN hoc_phan hp_reg ON hp_reg.id = lhp.hoc_phan_id
        WHERE dk.sinh_vien_id = :sid AND dk.nam_hoc = :nh AND dk.hoc_ky = :hk AND dk.trang_thai = 'Đã duyệt'
        ORDER BY hp_reg.ma_hp
    ");
    $registered_courses->execute(['sid' => $studentId, 'nh' => $hp['nam_hoc'], 'hk' => $hp['hoc_ky']]);
    $hp['registered_courses'] = $registered_courses->fetchAll();
}

$tuitionFeesInfo = [
    'hp_list' => $hp_list,
    'tong_no' => $tong_no,
    'tong_da_nop' => $tong_da_nop,
    'tong_hoc_phi' => $tong_hoc_phi
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Tuition View</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f4ff; font-weight: bold; }
        .badge { padding: 5px 10px; border-radius: 4px; }
        .badge-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <h1>Test Bảng Học Phí (Giả lập View)</h1>
    
    <table>
        <thead><tr>
            <th>Năm học</th>
            <th style="text-align:center">Học kỳ</th>
            <th style="text-align:right">Học phí</th>
            <th style="text-align:right">Đã nộp</th>
            <th style="text-align:right">Còn nợ</th>
            <th style="text-align:center">Hạn nộp</th>
            <th style="text-align:center">Trạng thái</th>
            <th style="text-align:center">Hành động</th>
        </tr></thead>
        <tbody>
        <?php if (empty($tuitionFeesInfo['hp_list'])): ?>
            <tr><td colspan="8" style="text-align:center;padding:30px;color:#999">Chưa có dữ liệu học phí.</td></tr>
        <?php else: ?>
        <?php foreach ($tuitionFeesInfo['hp_list'] as $hp):
            $no = $hp['so_tien'] - $hp['da_nop'];
            $qua_han = !empty($hp['han_nop']) && strtotime($hp['han_nop']) < time() && $hp['trang_thai'] !== 'Đã nộp';
        ?>
        <!-- Row for registered courses -->
        <?php if (!empty($hp['registered_courses'])): ?>
        <tr style="background:#f9f9f9;border-bottom:1px solid #ddd">
            <td colspan="8" style="padding:12px 14px">
                <div style="font-weight:600;color:var(--text-dark);margin-bottom:8px;font-size:13px">
                    <i class="fas fa-book"></i> Các học phần đã đăng ký:
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;padding-left:20px">
                    <?php foreach ($hp['registered_courses'] as $course): ?>
                    <span style="display:inline-block;background:#e3f2fd;border:1px solid #90caf9;border-radius:4px;padding:4px 10px;font-size:12px;color:#1565c0;font-weight:500">
                        <?= e($course['ma_hp']) ?> - <?= e($course['ten_hp']) ?> (<?= (int)$course['so_tin_chi'] ?> TC)
                    </span>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <!-- Main tuition row -->
        <tr <?= $qua_han ? 'style="background:#fff5f5"' : '' ?>>
            <td><?= e($hp['nam_hoc']) ?></td>
            <td style="text-align:center">HK <?= (int)$hp['hoc_ky'] ?></td>
            <td style="text-align:right;font-weight:500"><?= formatMoney((float)$hp['so_tien']) ?></td>
            <td style="text-align:right;color:green;font-weight:500"><?= formatMoney((float)$hp['da_nop']) ?></td>
            <td style="text-align:right;font-weight:700;color:<?= $no > 0 ? 'red' : 'green' ?>">
                <?= $no > 0 ? formatMoney($no) : '<i class="fas fa-check"></i> 0 đ' ?>
            </td>
            <td style="text-align:center;font-size:14px">
                <?php if (!empty($hp['han_nop'])): ?>
                    <span <?= $qua_han ? 'style="color:red;font-weight:700"' : '' ?>>
                        <?= date('d/m/Y', strtotime($hp['han_nop'])) ?>
                        <?= $qua_han ? '<br><small style="color:red">⚠ Đã quá hạn</small>' : '' ?>
                    </span>
                <?php else: ?> — <?php endif; ?>
            </td>
            <td style="text-align:center"><?= statusBadge($hp['trang_thai']) ?></td>
            <td style="text-align:center">
                <?php if ($hp['trang_thai'] !== 'Đã nộp'): ?>
                    <button>Nộp học phí</button>
                <?php else: ?>
                    <span style="color:green;font-weight:600">Đã hoàn tất</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f0f4ff;font-weight:700">
                <td colspan="2" style="border:1px solid #ddd;padding:10px 14px;text-align:right">Tổng cộng:</td>
                <td style="border:1px solid #ddd;text-align:right"><?= formatMoney($tuitionFeesInfo['tong_hoc_phi']) ?></td>
                <td style="border:1px solid #ddd;text-align:right;color:green"><?= formatMoney($tuitionFeesInfo['tong_da_nop']) ?></td>
                <td style="border:1px solid #ddd;text-align:right;color:<?= $tuitionFeesInfo['tong_no'] > 0 ? 'red' : 'green' ?>">
                    <?= formatMoney($tuitionFeesInfo['tong_no']) ?>
                </td>
                <td colspan="3" style="border:1px solid #ddd"></td>
            </tr>
        </tfoot>
    </table>

    <h2>Debug Info</h2>
    <pre>
Tổng bản ghi học phí: <?= count($tuitionFeesInfo['hp_list']) ?>

Chi tiết các dòng:
<?php foreach ($tuitionFeesInfo['hp_list'] as $hp): ?>
Năm <?= $hp['nam_hoc'] ?>, HK <?= $hp['hoc_ky'] ?>: 
  - Số học phần đã đăng ký: <?= count($hp['registered_courses']) ?>
  <?php if (!empty($hp['registered_courses'])): ?>
    <?php foreach ($hp['registered_courses'] as $c): ?>
    - <?= $c['ma_hp'] ?>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endforeach; ?>
    </pre>
</body>
</html>
