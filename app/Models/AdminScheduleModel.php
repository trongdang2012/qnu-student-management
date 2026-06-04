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
            return "TrÃ¹ng phÃ²ng há»c {$phong} vá»›i lá»›p {$conflictRoom['ma_lop_hp']} ({$conflictRoom['ten_hp']}) táº¡i tiáº¿t {$conflictRoom['tiet_bat_dau']}-" . ($conflictRoom['tiet_bat_dau'] + $conflictRoom['so_tiet'] - 1);
        }

        // 2. Kiá»ƒm tra trÃ¹ng giáº£ng viÃªn
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
            return "Giáº£ng viÃªn {$giangVien} bá»‹ trÃ¹ng lá»‹ch dáº¡y lá»›p {$conflictGv['ma_lop_hp']} ({$conflictGv['ten_hp']}) táº¡i tiáº¿t {$conflictGv['tiet_bat_dau']}-" . ($conflictGv['tiet_bat_dau'] + $conflictGv['so_tiet'] - 1);
        }

        // 3. Kiá»ƒm tra trÃ¹ng lá»‹ch há»c cá»§a chÃ­nh lá»›p há»c pháº§n nÃ y
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
            return "Lá»›p há»c pháº§n nÃ y Ä‘Ã£ cÃ³ lá»‹ch há»c vÃ o Thá»© {$thu}, tiáº¿t {$conflictClass['tiet_bat_dau']}-" . ($conflictClass['tiet_bat_dau'] + $conflictClass['so_tiet'] - 1);
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
     * Xáº¿p thá»i khÃ³a biá»ƒu tá»± Ä‘á»™ng cho cÃ¡c lá»›p chÆ°a cÃ³ lá»‹ch
     *
     * THUáº¬T TOÃN:
     * 1. TÃ¬m táº¥t cáº£ lá»›p há»c pháº§n chÆ°a cÃ³ báº¥t ká»³ lá»‹ch nÃ o
     * 2. Vá»›i má»—i lá»›p, xáº¿p lá»‹ch vÃ o cÃ¡c slot trá»‘ng (khÃ´ng bá»‹ xung Ä‘á»™t)
     * 3. Æ¯u tiÃªn cÃ¡c ngÃ y trong tuáº§n (2-6), giáº£m dáº§n cho thá»© 7-CN
     * 4. Æ¯u tiÃªn tiáº¿t sÃ¡ng (1-4) trÆ°á»›c tiáº¿t chiá»u (6-9)
     * 5. Kiá»ƒm tra xung Ä‘á»™t vá»: phÃ²ng há»c, giáº£ng viÃªn, lá»‹ch sinh viÃªn (náº¿u cÃ³)
     *
     * @param int $hk - Há»c ká»³ há»c vá»¥
     * @param string $nh - NÄƒm há»c
     * @return array Káº¿t quáº£ thá»±c hiá»‡n
     */
    public function optimizeSchedules($hk, $nh) {
        // Láº¥y táº¥t cáº£ lá»›p há»c pháº§n cáº§n xáº¿p lá»‹ch (nhá»¯ng lá»›p CHÆ¯A cÃ³ báº¥t ká»³ lá»‹ch nÃ o)
        $sql = "
            SELECT l.id, l.ma_lop_hp, h.ma_hp, h.ten_hp, h.so_tin_chi, l.giang_vien, l.ngay_bat_dau, l.ngay_ket_thuc
            FROM lop_hoc_phan l
            JOIN hoc_phan h ON l.hoc_phan_id = h.id
            WHERE l.hoc_ky = :hk
              AND l.nam_hoc = :nh
              AND l.id NOT IN (
                  SELECT DISTINCT lop_hoc_phan_id FROM thoi_khoa_bieu
                  WHERE hoc_ky = :hk AND nam_hoc = :nh
              )
            ORDER BY l.ma_lop_hp ASC
        ";

        $classes = $this->db->fetchAll($sql, [
            'hk' => $hk,
            'nh' => $nh
        ]);

        if (empty($classes)) {
            return [
                'status' => 'warning',
                'message' => 'KhÃ´ng cÃ³ lá»›p há»c pháº§n nÃ o cáº§n xáº¿p lá»‹ch (táº¥t cáº£ Ä‘á»u Ä‘Ã£ cÃ³ lá»‹ch hoáº·c khÃ´ng cÃ³ lá»›p nÃ o trong há»c ká»³ nÃ y).'
            ];
        }

        // Danh sÃ¡ch phÃ²ng há»c kháº£ dá»¥ng
        $phongs = [
            'A101', 'A102', 'A103', 'A201', 'A202', 'A301',
            'B101', 'B102', 'B201', 'B202', 'B203', 'B301', 'B302', 'B303', 'B304', 'B305',
            'Lab IT', 'Lab Äiá»‡n', 'PhÃ²ng 101', 'PhÃ²ng 102', 'PhÃ²ng 103'
        ];

        // Äá»‹nh nghÄ©a cÃ¡c slot thá»i gian Æ°u tiÃªn
        // Format: ['thu' => X (2-8), 'tiet_start' => Y, 'tiet_end' => Z]
        // SÃ¡ng: Tiáº¿t 1-5 (7h-12h)
        // Chiá»u: Tiáº¿t 6-10 (13h-18h)
        // Tá»‘i: Tiáº¿t 11+ (19h+)
        $slots = [];

        // Thá»© 2-5 (Æ°u tiÃªn nháº¥t): SÃ¡ng (tiáº¿t 1) hoáº·c Chiá»u (tiáº¿t 6)
        for ($thu = 2; $thu <= 5; $thu++) {
            $slots[] = ['thu' => $thu, 'tiet' => 1, 'priority' => 10];  // SÃ¡ng
            $slots[] = ['thu' => $thu, 'tiet' => 6, 'priority' => 9];   // Chiá»u
        }

        // Thá»© 6 (Æ°u tiÃªn káº¿ tiáº¿p)
        $slots[] = ['thu' => 6, 'tiet' => 1, 'priority' => 8];
        $slots[] = ['thu' => 6, 'tiet' => 6, 'priority' => 7];

        // Thá»© 7 (Æ°u tiÃªn tháº¥p)
        $slots[] = ['thu' => 7, 'tiet' => 1, 'priority' => 5];
        $slots[] = ['thu' => 7, 'tiet' => 6, 'priority' => 4];

        // Chá»§ nháº­t (Æ°u tiÃªn tháº¥p nháº¥t)
        $slots[] = ['thu' => 8, 'tiet' => 1, 'priority' => 2];
        $slots[] = ['thu' => 8, 'tiet' => 6, 'priority' => 1];

        $successCount = 0;
        $failedClasses = [];

        foreach ($classes as $class) {
            $lopHpId = $class['id'];
            $giangVien = $class['giang_vien'] ?? '(ChÆ°a phÃ¢n cÃ´ng)';
            $soTinChi = max(1, (int)$class['so_tin_chi']);
            $soTiet = min($soTinChi, 5); // Tá»‘i Ä‘a 5 tiáº¿t cho 1 buá»•i

            $placed = false;

            // Shuffle phÃ²ng Ä‘á»ƒ phÃ¢n bá»• Ä‘á»u
            shuffle($phongs);

            // Duyá»‡t tá»«ng slot theo Æ°u tiÃªn (cao nháº¥t trÆ°á»›c)
            usort($slots, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });

            foreach ($slots as $slot) {
                $thu = $slot['thu'];
                $tietBd = $slot['tiet'];

                // Kiá»ƒm tra xung Ä‘á»™t cho cÃ¡c tiáº¿t theo sau
                $tietKt = $tietBd + $soTiet - 1;

                // Äáº£m báº£o khÃ´ng vÆ°á»£t quÃ¡ tiáº¿t cuá»‘i cá»§a buá»•i há»c
                // SÃ¡ng: 1-5, Chiá»u: 6-10, Tá»‘i: 11+
                if (($tietBd <= 5 && $tietKt > 5) || ($tietBd >= 6 && $tietBd <= 10 && $tietKt > 10)) {
                    // Bá» qua slot nÃ y vÃ¬ sáº½ vÆ°á»£t quÃ¡ giá» há»c cá»§a buá»•i
                    continue;
                }

                foreach ($phongs as $phong) {
                    // Kiá»ƒm tra xung Ä‘á»™t phÃ²ng, giáº£ng viÃªn, thá»i gian
                    $conflict = $this->checkConflict(0, $phong, $giangVien, $lopHpId, $thu, $tietBd, $soTiet, $hk, $nh);

                    if ($conflict === false) {
                        // KhÃ´ng cÃ³ xung Ä‘á»™t, thÃªm lá»‹ch há»c
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
                            break 2; // ThoÃ¡t cáº£ 2 vÃ²ng láº·p (phÃ²ng vÃ  slot)
                        } catch (\Exception $e) {
                            // Bá» qua lá»—i vÃ  thá»­ phÃ²ng tiáº¿p theo
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
                'message' => "âœ“ Xáº¿p lá»‹ch tá»± Ä‘á»™ng thÃ nh cÃ´ng! <strong>{$successCount}/" . count($classes) . " lá»›p há»c pháº§n</strong> Ä‘Ã£ Ä‘Æ°á»£c sáº¯p xáº¿p thá»i khÃ³a biá»ƒu mÃ  khÃ´ng xáº£y ra xung Ä‘á»™t."
            ];
        } else {
            return [
                'status' => 'warning',
                'message' => "âš ï¸ Xáº¿p lá»‹ch hoÃ n táº¥t vá»›i <strong>{$successCount}/" . count($classes) . " lá»›p</strong>. <strong>" . count($failedClasses) . " lá»›p khÃ´ng thá»ƒ xáº¿p lá»‹ch</strong> do xung Ä‘á»™t tÃ i nguyÃªn: <br><em>" . implode(', ', $failedClasses) . "</em>"
            ];
        }
    }
}
