<?php
namespace App\Models;

use App\Core\Database;

class AdminScheduleModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getSchedules($hocKy, $namHoc, $search = '', $phongFilter = '', $gvFilter = '', $lhpFilter = '') {
        $sql = '
            SELECT t.*, l.ma_lop_hp, hp.ma_hp, hp.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            WHERE t.hoc_ky = :hk AND t.nam_hoc = :nh
        ';
        $params = ['hk' => $hocKy, 'nh' => $namHoc];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (l.ma_lop_hp LIKE :search1 OR hp.ma_hp LIKE :search2 OR hp.ten_hp LIKE :search3 OR t.phong_hoc LIKE :search4 OR t.giang_vien LIKE :search5)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
            $params['search4'] = $like;
            $params['search5'] = $like;
        }

        if ($phongFilter !== '') {
            $sql .= ' AND t.phong_hoc = :phongFilter';
            $params['phongFilter'] = $phongFilter;
        }

        if ($gvFilter !== '') {
            $sql .= ' AND t.giang_vien = :gvFilter';
            $params['gvFilter'] = $gvFilter;
        }

        if ($lhpFilter !== '') {
            $sql .= ' AND t.lop_hoc_phan_id = :lhpFilter';
            $params['lhpFilter'] = $lhpFilter;
        }

        $sql .= ' ORDER BY t.thu ASC, t.tiet_bat_dau ASC, l.ma_lop_hp ASC';
        return $this->db->fetchAll($sql, $params);
    }

    public function getDistinctYears() {
        return $this->db->fetchAll("SELECT DISTINCT nam_hoc FROM lop_hoc_phan ORDER BY nam_hoc DESC LIMIT 8");
    }

    public function getScheduleById($id) {
        return $this->db->fetch('
            SELECT t.*, l.ma_lop_hp, hp.ten_hp, hp.ma_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            WHERE t.id = :id
        ', ['id' => $id]);
    }

    public function getAllClasses($hk, $nh) {
        return $this->db->fetchAll('
            SELECT l.id, l.ma_lop_hp, hp.ten_hp
            FROM lop_hoc_phan l
            JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
            WHERE l.hoc_ky = :hk AND l.nam_hoc = :nh
            ORDER BY l.ma_lop_hp
        ', ['hk' => $hk, 'nh' => $nh]);
    }

    public function getScheduleDashboardStats($hk, $nh) {
        $scheduleStats = $this->db->fetch("
            SELECT
                COUNT(*) AS schedule_total,
                COUNT(DISTINCT phong_hoc) AS room_total,
                COUNT(DISTINCT giang_vien) AS teacher_total,
                COUNT(DISTINCT lop_hoc_phan_id) AS scheduled_class_total,
                SUM(so_tiet) AS period_total
            FROM thoi_khoa_bieu
            WHERE hoc_ky = :hk AND nam_hoc = :nh
        ", ['hk' => $hk, 'nh' => $nh]);

        $classStats = $this->db->fetch("
            SELECT COUNT(*) AS class_total
            FROM lop_hoc_phan
            WHERE hoc_ky = :hk AND nam_hoc = :nh
        ", ['hk' => $hk, 'nh' => $nh]);

        $unscheduled = $this->db->fetch("
            SELECT COUNT(*) AS total
            FROM lop_hoc_phan l
            LEFT JOIN thoi_khoa_bieu t
              ON t.lop_hoc_phan_id = l.id
             AND t.hoc_ky = l.hoc_ky
             AND t.nam_hoc = l.nam_hoc
            WHERE l.hoc_ky = :hk
              AND l.nam_hoc = :nh
              AND t.id IS NULL
        ", ['hk' => $hk, 'nh' => $nh]);

        $classTotal = (int)($classStats['class_total'] ?? 0);
        $scheduledClassTotal = (int)($scheduleStats['scheduled_class_total'] ?? 0);

        return [
            'schedule_total' => (int)($scheduleStats['schedule_total'] ?? 0),
            'room_total' => (int)($scheduleStats['room_total'] ?? 0),
            'teacher_total' => (int)($scheduleStats['teacher_total'] ?? 0),
            'scheduled_class_total' => $scheduledClassTotal,
            'class_total' => $classTotal,
            'unscheduled_total' => (int)($unscheduled['total'] ?? 0),
            'period_total' => (int)($scheduleStats['period_total'] ?? 0),
            'scheduled_percent' => $classTotal > 0 ? round(($scheduledClassTotal / $classTotal) * 100, 1) : 0
        ];
    }

    public function getUnscheduledClasses($hk, $nh, $limit = 10) {
        return $this->db->fetchAll("
            SELECT l.id, l.ma_lop_hp, l.giang_vien, l.si_so_hien_tai, l.si_so_toi_da,
                   hp.ma_hp, hp.ten_hp, hp.so_tin_chi
            FROM lop_hoc_phan l
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            LEFT JOIN thoi_khoa_bieu t
              ON t.lop_hoc_phan_id = l.id
             AND t.hoc_ky = l.hoc_ky
             AND t.nam_hoc = l.nam_hoc
            WHERE l.hoc_ky = :hk
              AND l.nam_hoc = :nh
              AND t.id IS NULL
            ORDER BY l.si_so_hien_tai DESC, l.ma_lop_hp ASC
            LIMIT " . (int)$limit, ['hk' => $hk, 'nh' => $nh]);
    }

    public function getRoomUtilization($hk, $nh, $limit = 8) {
        return $this->db->fetchAll("
            SELECT phong_hoc, COUNT(*) AS schedule_total, SUM(so_tiet) AS period_total
            FROM thoi_khoa_bieu
            WHERE hoc_ky = :hk
              AND nam_hoc = :nh
              AND COALESCE(phong_hoc, '') <> ''
            GROUP BY phong_hoc
            ORDER BY period_total DESC, phong_hoc ASC
            LIMIT " . (int)$limit, ['hk' => $hk, 'nh' => $nh]);
    }

    public function checkConflict($excludeId, $phong, $giangVien, $lopHpId, $thu, $tietBd, $soTiet, $hk, $nh) {
        $tietKt = $tietBd + $soTiet - 1;

        // 1. Kiá»ƒm tra trÃ¹ng phÃ²ng há»c
        $sqlRoom = '
            SELECT t.*, l.ma_lop_hp, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.phong_hoc = :phong
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictRoom = $this->db->fetch($sqlRoom, [
            'excludeId' => $excludeId, 'phong' => $phong, 'thu' => $thu,
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictRoom) {
            return "Trùng phòng học {$phong} với lớp {$conflictRoom['ma_lop_hp']} ({$conflictRoom['ten_hp']}) tại tiết {$conflictRoom['tiet_bat_dau']}-" . ($conflictRoom['tiet_bat_dau'] + $conflictRoom['so_tiet'] - 1);
        }

        // 2. Kiểm tra trùng giảng viên
        $sqlGv = '
            SELECT t.*, l.ma_lop_hp, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.giang_vien = :gv
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictGv = $this->db->fetch($sqlGv, [
            'excludeId' => $excludeId, 'gv' => $giangVien, 'thu' => $thu,
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictGv) {
            return "Giảng viên {$giangVien} bị trùng lịch dạy lớp {$conflictGv['ma_lop_hp']} ({$conflictGv['ten_hp']}) tại tiết {$conflictGv['tiet_bat_dau']}-" . ($conflictGv['tiet_bat_dau'] + $conflictGv['so_tiet'] - 1);
        }

        // 3. Kiểm tra trùng lịch học của chính lớp học phần này
        $sqlClass = '
            SELECT t.*, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.lop_hoc_phan_id = :lopHpId
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictClass = $this->db->fetch($sqlClass, [
            'excludeId' => $excludeId, 'lopHpId' => $lopHpId, 'thu' => $thu,
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictClass) {
            return "Lớp học phần này đã có lịch học vào Thứ {$thu}, tiết {$conflictClass['tiet_bat_dau']}-" . ($conflictClass['tiet_bat_dau'] + $conflictClass['so_tiet'] - 1);
        }

        return false;
    }

    public function insertSchedule($data) {
        $sql = '
            INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc)
            VALUES (:lop_hoc_phan_id, :thu, :tiet_bd, :so_tiet, :phong, :gv, :hk, :nh, :ngay_bat_dau, :ngay_ket_thuc)
        ';
        return $this->db->query($sql, $data);
    }

    public function updateSchedule($id, $data) {
        $sql = '
            UPDATE thoi_khoa_bieu
            SET lop_hoc_phan_id = :lop_hoc_phan_id, thu = :thu, tiet_bat_dau = :tiet_bd, so_tiet = :so_tiet,
                phong_hoc = :phong, giang_vien = :gv, hoc_ky = :hk, nam_hoc = :nh, ngay_bat_dau = :ngay_bat_dau, ngay_ket_thuc = :ngay_ket_thuc
            WHERE id = :id
        ';
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }

    public function deleteSchedule($id) {
        return $this->db->query('DELETE FROM thoi_khoa_bieu WHERE id = :id', ['id' => $id]);
    }

    /**
     * Xếp thời khóa biểu tự động cho các lớp chưa có lịch
     *
     * THUẬT TOÁN:
     * 1. Tìm tất cả lớp học phần chưa có bất kỳ lịch nào
     * 2. Với mỗi lớp, xếp lịch vào các slot trống (không bị xung đột)
     * 3. Ưu tiên các ngày trong tuần (2-6), giảm dần cho thứ 7-CN
     * 4. Ưu tiên tiết sáng (1-4) trước tiết chiều (6-9)
     * 5. Kiểm tra xung đột về: phòng học, giảng viên, lịch sinh viên (nếu có)
     *
     * @param int $hk - Học kỳ học vụ
     * @param string $nh - Năm học
     * @return array Kết quả thực hiện
     */
    public function optimizeSchedules($hk, $nh) {
        // Lấy tất cả lớp học phần cần xếp lịch (những lớp CHƯA có bất kỳ lịch nào)
        $sql = "
            SELECT l.id, l.ma_lop_hp, h.ma_hp, h.ten_hp, h.so_tin_chi, l.giang_vien, l.ngay_bat_dau, l.ngay_ket_thuc
            FROM lop_hoc_phan l
            JOIN hoc_phan h ON l.hoc_phan_id = h.id
            WHERE l.hoc_ky = :hk1
              AND l.nam_hoc = :nh1
              AND l.id NOT IN (
                  SELECT DISTINCT lop_hoc_phan_id FROM thoi_khoa_bieu
                  WHERE hoc_ky = :hk2 AND nam_hoc = :nh2
              )
            ORDER BY l.ma_lop_hp ASC
        ";

        $classes = $this->db->fetchAll($sql, [
            'hk1' => $hk,
            'nh1' => $nh,
            'hk2' => $hk,
            'nh2' => $nh
        ]);

        if (empty($classes)) {
            return [
                'status' => 'warning',
                'message' => 'Không có lớp học phần nào cần xếp lịch (tất cả đều đã có lịch hoặc không có lớp nào trong học kỳ này).'
            ];
        }

        // Danh sách phòng học khả dụng
        $phongs = [
            'A101', 'A102', 'A103', 'A201', 'A202', 'A301',
            'B101', 'B102', 'B201', 'B202', 'B203', 'B301', 'B302', 'B303', 'B304', 'B305',
            'Lab IT', 'Lab Điện', 'Phòng 101', 'Phòng 102', 'Phòng 103'
        ];

        // Định nghĩa các slot thời gian ưu tiên
        // Format: ['thu' => X (2-8), 'tiet_start' => Y, 'tiet_end' => Z]
        // Sáng: Tiết 1-5 (7h-12h)
        // Chiều: Tiết 6-10 (13h-18h)
        // Tối: Tiết 11+ (19h+)
        $slots = [];

        // Thứ 2-5 (ưu tiên nhất): Sáng (tiết 1) hoặc Chiều (tiết 6)
        for ($thu = 2; $thu <= 5; $thu++) {
            $slots[] = ['thu' => $thu, 'tiet' => 1, 'priority' => 10];  // Sáng
            $slots[] = ['thu' => $thu, 'tiet' => 6, 'priority' => 9];   // Chiều
        }

        // Thứ 6 (ưu tiên kế tiếp)
        $slots[] = ['thu' => 6, 'tiet' => 1, 'priority' => 8];
        $slots[] = ['thu' => 6, 'tiet' => 6, 'priority' => 7];

        // Thứ 7 (ưu tiên thấp)
        $slots[] = ['thu' => 7, 'tiet' => 1, 'priority' => 5];
        $slots[] = ['thu' => 7, 'tiet' => 6, 'priority' => 4];

        // Chủ nhật (ưu tiên thấp nhất)
        $slots[] = ['thu' => 8, 'tiet' => 1, 'priority' => 2];
        $slots[] = ['thu' => 8, 'tiet' => 6, 'priority' => 1];

        $successCount = 0;
        $failedClasses = [];

        foreach ($classes as $class) {
            $lopHpId = $class['id'];
            $giangVien = $class['giang_vien'] ?? '(Chưa phân công)';
            $soTinChi = max(1, (int)$class['so_tin_chi']);
            $soTiet = min($soTinChi, 5); // Tối đa 5 tiết cho 1 buổi

            $placed = false;

            // Shuffle phòng để phân bổ đều
            shuffle($phongs);

            // Duyệt từng slot theo ưu tiên (cao nhất trước)
            usort($slots, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });

            foreach ($slots as $slot) {
                $thu = $slot['thu'];
                $tietBd = $slot['tiet'];

                // Kiểm tra xung đột cho các tiết theo sau
                $tietKt = $tietBd + $soTiet - 1;

                // Đảm bảo không vượt quá tiết cuối của buổi học
                // Sáng: 1-5, Chiều: 6-10, Tối: 11+
                if (($tietBd <= 5 && $tietKt > 5) || ($tietBd >= 6 && $tietBd <= 10 && $tietKt > 10)) {
                    // Bỏ qua slot này vì sẽ vượt quá giờ học của buổi
                    continue;
                }

                foreach ($phongs as $phong) {
                    // Kiểm tra xung đột phòng, giảng viên, thời gian
                    $conflict = $this->checkConflict(0, $phong, $giangVien, $lopHpId, $thu, $tietBd, $soTiet, $hk, $nh);

                    if ($conflict === false) {
                        // Không có xung đột, thêm lịch học
                        try {
                            $this->db->query('
                                INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc)
                                VALUES (:lop_hp_id, :thu, :tiet_bd, :so_tiet, :phong, :gv, :hk, :nh, :ngay_bd, :ngay_kt)
                            ', [
                                'lop_hp_id' => $lopHpId,
                                'thu' => $thu,
                                'tiet_bd' => $tietBd,
                                'so_tiet' => $soTiet,
                                'phong' => $phong,
                                'gv' => $giangVien,
                                'hk' => $hk,
                                'nh' => $nh,
                                'ngay_bd' => $class['ngay_bat_dau'],
                                'ngay_kt' => $class['ngay_ket_thuc']
                            ]);

                            $successCount++;
                            $placed = true;
                            break 2; // Thoát cả 2 vòng lặp (phòng và slot)
                        } catch (\Exception $e) {
                            // Bỏ qua lỗi và thử phòng tiếp theo
                            continue;
                        }
                    }
                }
            }

            if (!$placed) {
                $failedClasses[] = $class['ma_lop_hp'] . ' (' . $class['ten_hp'] . ')';
            }
        }

        if (empty($failedClasses)) {
            return [
                'status' => 'success',
                'message' => "✓ Xếp lịch tự động thành công! <strong>{$successCount}/" . count($classes) . " lớp học phần</strong> đã được sắp xếp thời khóa biểu mà không xảy ra xung đột."
            ];
        } else {
            return [
                'status' => 'warning',
                'message' => "⚠️ Xếp lịch hoàn tất với <strong>{$successCount}/" . count($classes) . " lớp</strong>. <strong>" . count($failedClasses) . " lớp không thể xếp lịch</strong> do xung đột tài nguyên: <br><em>" . implode(', ', $failedClasses) . "</em>"
            ];
        }
    }
}
