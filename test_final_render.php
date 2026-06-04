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

$studentId = 11;

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
    <title>Học phí - Sinh viên ID 11</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #0066cc; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin: 20px 0; }
        .stat-card { background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #0066cc; }
        .stat-value { font-size: 24px; font-weight: bold; color: #0066cc; }
        .stat-label { color: #666; font-size: 13px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #0066cc; color: white; padding: 12px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 12px; }
        tr:hover { background: #f9f9f9; }
        .course-row { background: #f9f9f9; }
        .course-row td { padding: 12px 14px; }
        .course-badge { display: inline-block; background: #e3f2fd; border: 1px solid #90caf9; border-radius: 4px; padding: 4px 10px; font-size: 12px; color: #1565c0; font-weight: 500; margin: 2px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; }
        .badge-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 Tra cứu học phí - Sinh viên ID 11 (Dương Phương Lan)</h1>
        
        <!-- Tổng quan -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value"><?= formatMoney($tuitionFeesInfo['tong_hoc_phi']) ?></div>
                <div class="stat-label">Tổng học phí toàn khóa</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: green"><?= formatMoney($tuitionFeesInfo['tong_da_nop']) ?></div>
                <div class="stat-label">Đã nộp</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color: <?= $tuitionFeesInfo['tong_no'] > 0 ? 'red' : 'green' ?>"><?= formatMoney($tuitionFeesInfo['tong_no']) ?></div>
                <div class="stat-label"><?= $tuitionFeesInfo['tong_no'] > 0 ? 'Còn nợ học phí' : 'Không có nợ học phí' ?></div>
            </div>
        </div>
        
        <?php if ($tuitionFeesInfo['tong_no'] <= 0): ?>
        <div class="alert alert-success">
            <strong>✓ Tốt lắm!</strong> Bạn không có khoản nợ học phí nào. Chúc bạn học tốt!
        </div>
        <?php endif; ?>
        
        <!-- Bảng học phí -->
        <h2>📊 Chi tiết học phí từng học kỳ</h2>
        <table>
            <thead>
                <tr>
                    <th>Năm học</th>
                    <th>Học kỳ</th>
                    <th>Học phí</th>
                    <th>Đã nộp</th>
                    <th>Còn nợ</th>
                    <th>Hạn nộp</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
        <?php if (empty($tuitionFeesInfo['hp_list'])): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có dữ liệu học phí.</td></tr>
        <?php else: ?>
        <?php foreach ($tuitionFeesInfo['hp_list'] as $hp):
            $no = $hp['so_tien'] - $hp['da_nop'];
            $qua_han = !empty($hp['han_nop']) && strtotime($hp['han_nop']) < time() && $hp['trang_thai'] !== 'Đã nộp';
        ?>
                <!-- Row for registered courses -->
        <?php if (!empty($hp['registered_courses'])): ?>
                <tr class="course-row">
                    <td colspan="7">
                        <div style="font-weight:600;color:#333;margin-bottom:8px;font-size:13px">
                            <i class="fas fa-book"></i> Các học phần đã đăng ký:
                        </div>
                        <div>
        <?php foreach ($hp['registered_courses'] as $course): ?>
                            <span class="course-badge">
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
                    <td>HK <?= (int)$hp['hoc_ky'] ?></td>
                    <td style="font-weight:500;text-align:right"><?= formatMoney((float)$hp['so_tien']) ?></td>
                    <td style="color:green;font-weight:500;text-align:right"><?= formatMoney((float)$hp['da_nop']) ?></td>
                    <td style="font-weight:700;color:<?= $no > 0 ? 'red' : 'green' ?>;text-align:right">
                        <?= $no > 0 ? formatMoney($no) : '✓ 0 đ' ?>
                    </td>
                    <td style="text-align:center;font-size:14px">
        <?php if (!empty($hp['han_nop'])): ?>
                        <span <?= $qua_han ? 'style="color:red;font-weight:700"' : '' ?>>
                            <?= date('d/m/Y', strtotime($hp['han_nop'])) ?>
        <?php else: ?> — <?php endif; ?>
                        </span>
                    </td>
                    <td style="text-align:center"><?= statusBadge($hp['trang_thai']) ?></td>
                </tr>
        <?php endforeach; ?>
        <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f0f4ff;font-weight:700">
                    <td colspan="2" style="border-top:2px solid #ddd;text-align:right;padding:10px 14px;">Tổng cộng:</td>
                    <td style="border-top:2px solid #ddd;text-align:right"><?= formatMoney($tuitionFeesInfo['tong_hoc_phi']) ?></td>
                    <td style="border-top:2px solid #ddd;text-align:right;color:green"><?= formatMoney($tuitionFeesInfo['tong_da_nop']) ?></td>
                    <td style="border-top:2px solid #ddd;text-align:right;color:<?= $tuitionFeesInfo['tong_no'] > 0 ? 'red' : 'green' ?>">
                        <?= formatMoney($tuitionFeesInfo['tong_no']) ?>
                    </td>
                    <td colspan="2" style="border-top:2px solid #ddd"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
