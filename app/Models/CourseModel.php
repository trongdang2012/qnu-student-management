<?php
namespace App\Models;

use App\Core\Database;

class CourseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getProgramDetails($studentId, $nganh) {
        $rows = $this->db->fetchAll("
            SELECT c.hoc_ky, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai,
                   d.diem_tong, d.diem_chu, d.diem_he4, dk.trang_thai AS dk_trang_thai
            FROM ctdt_chi_tiet c
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            LEFT JOIN diem_hoc_tap d ON d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid
            LEFT JOIN dang_ky_hp dk ON dk.hoc_phan_id = hp.id AND dk.sinh_vien_id = :sid2
            WHERE c.nganh = :nganh
            ORDER BY c.hoc_ky, hp.loai, hp.ma_hp
        ", ['sid' => $studentId, 'sid2' => $studentId, 'nganh' => $nganh]);

        $by_hk = [];
        $tc_by_loai = ['Bắt buộc'=>0,'Đại cương'=>0,'Tự chọn'=>0];
        $tc_total = 0;
        foreach ($rows as $r) {
            $by_hk[$r['hoc_ky']][] = $r;
            $tc_by_loai[$r['loai']] = ($tc_by_loai[$r['loai']] ?? 0) + $r['so_tin_chi'];
            $tc_total += $r['so_tin_chi'];
        }

        return [
            'by_hk' => $by_hk,
            'tc_by_loai' => $tc_by_loai,
            'tc_total' => $tc_total
        ];
    }

    public function getGrades($studentId, $nh_filter = '') {
        $list_nh = $this->db->fetchAll("SELECT DISTINCT nam_hoc FROM diem_hoc_tap WHERE sinh_vien_id=:sid ORDER BY nam_hoc DESC", ['sid' => $studentId]);
        
        $params = ['sid' => $studentId];
        $where_nh = '';
        if ($nh_filter) {
            $where_nh = "AND d.nam_hoc=:nh";
            $params['nh'] = $nh_filter;
        }

        $diem_list = $this->db->fetchAll("
            SELECT d.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.loai
            FROM diem_hoc_tap d
            JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
            WHERE d.sinh_vien_id = :sid $where_nh
            ORDER BY d.nam_hoc, d.hoc_ky, hp.ten_hp
        ", $params);

        $by_nh_hk = [];
        foreach ($diem_list as $d) {
            $by_nh_hk[$d['nam_hoc']][$d['hoc_ky']][] = $d;
        }

        $r_cpa = $this->db->fetch("
            SELECT SUM(d.diem_he4 * hp.so_tin_chi) / SUM(hp.so_tin_chi) AS cpa,
                   SUM(hp.so_tin_chi) AS tc_tich_luy,
                   COUNT(*) AS so_mon
            FROM diem_hoc_tap d
            JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
            WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL
        ", ['sid' => $studentId]);

        $r_f = $this->db->fetch("SELECT COUNT(*) AS cnt FROM diem_hoc_tap WHERE sinh_vien_id=:sid AND diem_he4 IS NOT NULL AND diem_he4 < 1.0", ['sid' => $studentId]);

        return [
            'list_nh' => $list_nh,
            'diem_list' => $diem_list,
            'by_nh_hk' => $by_nh_hk,
            'cpa' => round((float)($r_cpa['cpa'] ?? 0), 2),
            'tc_tich_luy' => (int)($r_cpa['tc_tich_luy'] ?? 0),
            'so_mon' => (int)($r_cpa['so_mon'] ?? 0),
            'so_mon_F' => (int)($r_f['cnt'] ?? 0)
        ];
    }

    public function getSchedule($studentId, $hk_filter, $nh_filter) {
        $list_nh = $this->db->fetchAll("SELECT DISTINCT nam_hoc FROM thoi_khoa_bieu WHERE sinh_vien_id=:sid ORDER BY nam_hoc DESC", ['sid' => $studentId]);
        
        $tkb = $this->db->fetchAll("
            SELECT t.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi
            FROM thoi_khoa_bieu t
            JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
            WHERE t.sinh_vien_id = :sid
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
            ORDER BY t.thu, t.tiet_bat_dau
        ", ['sid' => $studentId, 'hk' => $hk_filter, 'nh' => $nh_filter]);

        $grid = [];
        foreach ($tkb as $row) {
            for ($t = $row['tiet_bat_dau']; $t < $row['tiet_bat_dau'] + $row['so_tiet']; $t++) {
                $grid[$row['thu']][$t] = $row;
            }
        }

        $tong_so_tiet = array_sum(array_map(static fn($row) => (int)$row['so_tiet'], $tkb));

        return [
            'list_nh' => $list_nh,
            'tkb' => $tkb,
            'grid' => $grid,
            'tong_so_tiet' => $tong_so_tiet
        ];
    }

    public function getRegisteredCourses($studentId, $hk, $nh) {
        return $this->db->fetchAll("
            SELECT dk.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.thu, hp.tiet_bat_dau, hp.so_tiet, hp.phong_hoc, hp.giang_vien, hp.si_so_toi_da, hp.si_so_hien_tai, hp.ma_hp_tien_quyet
            FROM dang_ky_hp dk
            JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
            WHERE dk.sinh_vien_id = :sid AND dk.hoc_ky = :hk AND dk.nam_hoc = :nh
            ORDER BY dk.ngay_dang_ky DESC
        ", ['sid' => $studentId, 'hk' => $hk, 'nh' => $nh]);
    }

    public function getAvailableCourses($studentId, $nganh, $registeredIds) {
        $id_str = empty($registeredIds) ? '0' : implode(',', $registeredIds);
        
        $sql = "
            SELECT hp.id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai, hp.thu, hp.tiet_bat_dau, hp.so_tiet, hp.phong_hoc, hp.giang_vien, hp.si_so_toi_da, hp.si_so_hien_tai, hp.ma_hp_tien_quyet
            FROM ctdt_chi_tiet c
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            WHERE c.nganh = :nganh
              AND hp.id NOT IN ($id_str)
              AND NOT EXISTS (
                  SELECT 1 FROM diem_hoc_tap d
                  WHERE d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid AND d.diem_he4 >= 1.0
              )
            ORDER BY c.hoc_ky, hp.ten_hp
        ";
        return $this->db->fetchAll($sql, ['nganh' => $nganh, 'sid' => $studentId]);
    }

    public function registerCourse($studentId, $hpId, $hk, $nh) {
        $course = $this->db->fetch("SELECT ma_hp, ten_hp, thu, tiet_bat_dau, so_tiet, si_so_toi_da, si_so_hien_tai, ma_hp_tien_quyet FROM hoc_phan WHERE id = :id", ['id' => $hpId]);
        if (!$course) return ['type' => 'danger', 'text' => 'Học phần không tồn tại.'];

        $ma_hp = $course['ma_hp'];
        $target_thu = $course['thu'];
        $target_start = $course['tiet_bat_dau'];
        $target_count = $course['so_tiet'];
        $si_so_toi_da = (int)$course['si_so_toi_da'];
        $si_so_hien_tai = (int)$course['si_so_hien_tai'];
        $prereq_ma = $course['ma_hp_tien_quyet'];

        // 1. Kiểm tra đã đăng ký
        $chk = $this->db->fetch("SELECT id FROM dang_ky_hp WHERE sinh_vien_id=:sid AND hoc_phan_id=:hpId AND hoc_ky=:hk AND nam_hoc=:nh AND trang_thai IN ('Chờ duyệt', 'Đã duyệt')", 
            ['sid' => $studentId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
        if ($chk) return ['type' => 'warning', 'text' => 'Bạn đã đăng ký học phần này.'];

        // 2. Kiểm tra sĩ số
        if ($si_so_hien_tai >= $si_so_toi_da) return ['type' => 'danger', 'text' => 'Học phần đã đủ số lượng.'];

        // 3. Kiểm tra môn tiên quyết
        if (!empty($prereq_ma)) {
            $prereq = $this->db->fetch("SELECT id FROM hoc_phan WHERE ma_hp = :ma", ['ma' => $prereq_ma]);
            if ($prereq) {
                $passed = $this->db->fetch("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = :sid AND hoc_phan_id = :pid AND diem_he4 >= 1.0", 
                    ['sid' => $studentId, 'pid' => $prereq['id']]);
                if (!$passed) return ['type' => 'danger', 'text' => 'Không đủ điều kiện đăng ký. Bạn chưa học đạt học phần tiên quyết.'];
            }
        }

        // 4. Kiểm tra trùng lịch
        $activeCourses = $this->db->fetchAll("
            SELECT hp.ten_hp, hp.thu, hp.tiet_bat_dau, hp.so_tiet
            FROM dang_ky_hp dk
            JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
            WHERE dk.sinh_vien_id = :sid AND dk.hoc_ky = :hk AND dk.nam_hoc = :nh AND dk.trang_thai IN ('Chờ duyệt', 'Đã duyệt')
        ", ['sid' => $studentId, 'hk' => (string)$hk, 'nh' => $nh]);

        foreach ($activeCourses as $row) {
            $act_thu = $row['thu'];
            $act_start = $row['tiet_bat_dau'];
            $act_count = $row['so_tiet'];

            if ($act_thu == $target_thu && $act_thu !== null && $target_thu !== null) {
                if ($target_start < $act_start + $act_count && $act_start < $target_start + $target_count) {
                    return ['type' => 'danger', 'text' => 'Trùng lịch học với học phần: ' . $row['ten_hp']];
                }
            }
        }

        // Đăng ký
        try {
            $this->db->query("INSERT INTO dang_ky_hp (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, trang_thai) VALUES (:sid, :hpId, :hk, :nh, 'Chờ duyệt')", 
                ['sid' => $studentId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
            
            $this->db->query("UPDATE hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = :hpId", ['hpId' => $hpId]);

            return ['type' => 'success', 'text' => 'Đăng ký thành công!'];
        } catch (\Exception $e) {
            return ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau.'];
        }
    }

    public function cancelCourse($studentId, $hpId, $hk, $nh) {
        try {
            // Chỉ xóa dòng nào đang 'Chờ duyệt'
            $sql = "DELETE FROM dang_ky_hp WHERE sinh_vien_id=:sid AND hoc_phan_id=:hpId AND hoc_ky=:hk AND nam_hoc=:nh AND trang_thai='Chờ duyệt'";
            $result = $this->db->query($sql, ['sid' => $studentId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
            
            // Check if any row was affected
            // We can check if row exists before deleting, or use PDO rowCount, but Database class might not expose rowCount.
            // Let's do a select before delete to ensure it exists and is 'Chờ duyệt'
            $chk = $this->db->fetch("SELECT id FROM dang_ky_hp WHERE sinh_vien_id=:sid AND hoc_phan_id=:hpId AND hoc_ky=:hk AND nam_hoc=:nh AND trang_thai='Chờ duyệt'", 
                ['sid' => $studentId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
                
            if ($chk) {
                 $this->db->query("DELETE FROM dang_ky_hp WHERE id=:id", ['id' => $chk['id']]);
                 $this->db->query("UPDATE hoc_phan SET si_so_hien_tai = GREATEST(0, si_so_hien_tai - 1) WHERE id = :hpId", ['hpId' => $hpId]);
                 return ['type' => 'success', 'text' => 'Đã hủy đăng ký học phần.'];
            } else {
                 return ['type' => 'warning', 'text' => 'Không thể hủy. Học phần đã được duyệt hoặc đã bị hủy.'];
            }
        } catch (\Exception $e) {
            return ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau.'];
        }
    }
}
