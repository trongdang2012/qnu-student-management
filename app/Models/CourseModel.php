<?php
namespace App\Models;

use App\Core\Database;

class CourseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->autoCloseExpiredClasses();
    }

    private function autoCloseExpiredClasses() {
        try {
            $this->db->query("
                UPDATE lop_hoc_phan 
                SET trang_thai_mo_lop = 'Đã đóng' 
                WHERE trang_thai_mo_lop = 'Đang mở' 
                  AND ngay_ket_thuc_dk IS NOT NULL 
                  AND ngay_ket_thuc_dk < NOW()
            ");
        } catch (\Exception $e) {
            // Bỏ qua lỗi nếu có
        }
    }

    public function getProgramDetails($studentId, $nganh) {
        $rows = $this->db->fetchAll("
            SELECT c.hoc_ky, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai,
                   d.diem_tong, d.diem_chu, d.diem_he4,
                   (SELECT dk.trang_thai FROM dang_ky_hp dk 
                    LEFT JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id 
                    WHERE (dk.hoc_phan_id = hp.id OR lhp.hoc_phan_id = hp.id) 
                      AND dk.sinh_vien_id = :sid 
                    ORDER BY dk.ngay_dang_ky DESC LIMIT 1) AS dk_trang_thai
            FROM ctdt_chi_tiet c
            JOIN nganh n ON n.id = c.nganh_id
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            LEFT JOIN diem_hoc_tap d ON d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid2
            WHERE n.ten_nganh = :nganh
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
        $list_nh = $this->db->fetchAll("
            SELECT DISTINCT nam_hoc FROM dang_ky_hp 
            WHERE sinh_vien_id = :sid AND trang_thai = 'Đã duyệt'
            ORDER BY nam_hoc DESC
        ", ['sid' => $studentId]);
        
        $tkb = $this->db->fetchAll("
            SELECT t.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi
            FROM dang_ky_hp dk
            JOIN lop_hoc_phan l ON l.id = dk.lop_hoc_phan_id
            JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            WHERE dk.sinh_vien_id = :sid
              AND dk.hoc_ky = :hk
              AND dk.nam_hoc = :nh
              AND dk.trang_thai = 'Đã duyệt'
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
            SELECT dk.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, l.ma_lop_hp, l.giang_vien, l.si_so_toi_da, l.si_so_hien_tai, hp.ma_hp_tien_quyet,
                   l.trang_thai_mo_lop, l.ngay_bat_dau_dk, l.ngay_ket_thuc_dk,
                   t.thu, t.tiet_bat_dau, t.so_tiet, t.phong_hoc
            FROM dang_ky_hp dk
            JOIN lop_hoc_phan l ON l.id = dk.lop_hoc_phan_id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            LEFT JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
            WHERE dk.sinh_vien_id = :sid AND dk.hoc_ky = :hk AND dk.nam_hoc = :nh
            ORDER BY dk.ngay_dang_ky DESC
        ", ['sid' => $studentId, 'hk' => $hk, 'nh' => $nh]);
    }

    public function getAvailableCourses($studentId, $nganh, $registeredLhpIds) {
        // Lọc sạch mảng, chỉ giữ lại các ID hợp lệ > 0 để tránh lỗi SQL cú pháp khi implode
        $validIds = array_filter(array_map('intval', (array)$registeredLhpIds));
        $id_str = empty($validIds) ? '0' : implode(',', $validIds);
        
        $hk_hien_tai = defined('HOC_KY_HIEN_TAI') ? HOC_KY_HIEN_TAI : 2;
        $nh_hien_tai = defined('NAM_HOC_HIEN_TAI') ? NAM_HOC_HIEN_TAI : '2025-2026';

        // Lấy niên khóa sinh viên
        $student = $this->db->fetch("SELECT nien_khoa FROM sinh_vien WHERE id = :sid", ['sid' => $studentId]);
        $nien_khoa = $student ? $student['nien_khoa'] : '';
        
        $student_hk = 1;
        if ($nien_khoa) {
            $parts = explode('-', $nien_khoa);
            $start_year = (int)$parts[0];
            
            $parts_nh = explode('-', $nh_hien_tai);
            $current_year_start = (int)$parts_nh[0];
            
            $diff_years = $current_year_start - $start_year;
            $student_hk = ($diff_years * 2) + (int)$hk_hien_tai;
        }
        $max_allowed_hk = $student_hk + 1; // Cho phép học vượt tối đa 1 học kỳ

        $sql = "
            SELECT l.id AS lop_hoc_phan_id, l.ma_lop_hp, l.giang_vien, l.si_so_toi_da, l.si_so_hien_tai,
                   hp.id AS hoc_phan_id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai, hp.ma_hp_tien_quyet,
                   t.thu, t.tiet_bat_dau, t.so_tiet, t.phong_hoc
            FROM lop_hoc_phan l
            JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
            JOIN ctdt_chi_tiet c ON hp.id = c.hoc_phan_id
            JOIN nganh n ON n.id = c.nganh_id
            LEFT JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
            WHERE n.ten_nganh = :nganh
              AND l.hoc_ky = :hk_hien_tai
              AND l.nam_hoc = :nh_hien_tai
              AND c.hoc_ky <= :max_allowed_hk
              AND l.trang_thai_mo_lop = 'Đang mở'
              AND (l.ngay_bat_dau_dk IS NULL OR NOW() >= l.ngay_bat_dau_dk)
              AND (l.ngay_ket_thuc_dk IS NULL OR NOW() <= l.ngay_ket_thuc_dk)
              AND l.id NOT IN ($id_str)
              AND NOT EXISTS (
                  SELECT 1 FROM diem_hoc_tap d
                  WHERE d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid AND d.diem_he4 >= 1.0
              )
            ORDER BY hp.ten_hp, l.ma_lop_hp
        ";
        return $this->db->fetchAll($sql, [
            'nganh' => $nganh, 
            'sid' => $studentId,
            'hk_hien_tai' => $hk_hien_tai,
            'nh_hien_tai' => $nh_hien_tai,
            'max_allowed_hk' => $max_allowed_hk
        ]);
    }

    public function registerCourse($studentId, $lhpId, $hk, $nh) {
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();

        try {
            // Lấy thông tin lớp học phần và khóa dòng bằng FOR UPDATE để tránh race condition về sĩ số
            $class = $this->db->fetch("
                SELECT l.*, hp.id AS hoc_phan_id, hp.ma_hp, hp.ten_hp, hp.ma_hp_tien_quyet 
                FROM lop_hoc_phan l
                JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
                WHERE l.id = :id
                FOR UPDATE
            ", ['id' => $lhpId]);

            if (!$class) {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Lớp học phần không tồn tại.'];
            }

            // Kiểm tra thời gian đăng ký
            $now = date('Y-m-d H:i:s');
            if ($class['trang_thai_mo_lop'] !== 'Đang mở') {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Lớp học phần này đã đóng hoặc chưa mở.'];
            }
            if ($class['ngay_bat_dau_dk'] !== null && $now < $class['ngay_bat_dau_dk']) {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Chưa đến thời gian mở đăng ký lớp học phần này (Bắt đầu từ: ' . date('d/m/Y H:i', strtotime($class['ngay_bat_dau_dk'])) . ').'];
            }
            if ($class['ngay_ket_thuc_dk'] !== null && $now > $class['ngay_ket_thuc_dk']) {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Đã hết thời gian đăng ký lớp học phần này (Kết thúc vào: ' . date('d/m/Y H:i', strtotime($class['ngay_ket_thuc_dk'])) . ').'];
            }

            $hpId = $class['hoc_phan_id'];
            $ma_hp = $class['ma_hp'];
            $si_so_toi_da = (int)$class['si_so_toi_da'];
            $si_so_hien_tai = (int)$class['si_so_hien_tai'];
            $prereq_ma = $class['ma_hp_tien_quyet'];

            // Kiểm tra giới hạn học kỳ cho phép đăng ký học phần trong CTDT (chống học vượt quá xa)
            $student = $this->db->fetch("SELECT nien_khoa, nganh FROM sinh_vien WHERE id = :sid", ['sid' => $studentId]);
            $nien_khoa = $student ? $student['nien_khoa'] : '';
            $studentNganh = $student ? $student['nganh'] : '';
            
            $student_hk = 1;
            if ($nien_khoa) {
                $parts = explode('-', $nien_khoa);
                $start_year = (int)$parts[0];
                
                $parts_nh = explode('-', $nh);
                $current_year_start = (int)$parts_nh[0];
                
                $diff_years = $current_year_start - $start_year;
                $student_hk = ($diff_years * 2) + (int)$hk;
            }
            $max_allowed_hk = $student_hk + 1; // Học vượt tối đa 1 kỳ

            $program_course = $this->db->fetch("
                SELECT hoc_ky FROM ctdt_chi_tiet 
                WHERE nganh = :nganh AND hoc_phan_id = :hpId
            ", ['nganh' => $studentNganh, 'hpId' => $hpId]);
            
            if ($program_course) {
                $course_hk = (int)$program_course['hoc_ky'];
                if ($course_hk > $max_allowed_hk) {
                    $pdo->rollBack();
                    return ['type' => 'danger', 'text' => 'Không thể đăng ký. Học phần thuộc học kỳ ' . $course_hk . ' trong CTĐT, vượt quá giới hạn cho phép học vượt của bạn (Tối đa học kỳ ' . $max_allowed_hk . ').'];
                }
            }

            // 0. Kiểm tra môn đã đạt điểm hệ 4 >= 1.0 (D trở lên)
            $passed = $this->db->fetch("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = :sid AND hoc_phan_id = :hpId AND diem_he4 >= 1.0", 
                ['sid' => $studentId, 'hpId' => $hpId]);
            if ($passed) {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Bạn đã hoàn thành đạt học phần này trước đó (không được đăng ký lại).'];
            }

            // 1. Kiểm tra đã đăng ký bất kỳ lớp học phần nào của môn học này trong học kỳ này chưa
            $chk = $this->db->fetch("
                SELECT dk.id FROM dang_ky_hp dk
                JOIN lop_hoc_phan lhp ON lhp.id = dk.lop_hoc_phan_id
                WHERE dk.sinh_vien_id = :sid 
                  AND lhp.hoc_phan_id = :hpId 
                  AND dk.hoc_ky = :hk 
                  AND dk.nam_hoc = :nh 
                  AND dk.trang_thai IN ('Chờ duyệt', 'Đã duyệt')
            ", ['sid' => $studentId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
            if ($chk) {
                $pdo->rollBack();
                return ['type' => 'warning', 'text' => 'Bạn đã đăng ký một lớp học phần của môn này trong học kỳ hiện tại.'];
            }

            // 2. Kiểm tra sĩ số lớp học phần
            if ($si_so_hien_tai >= $si_so_toi_da) {
                $pdo->rollBack();
                return ['type' => 'danger', 'text' => 'Lớp học phần đã đủ sĩ số tối đa.'];
            }

            // 3. Kiểm tra môn tiên quyết
            if (!empty($prereq_ma)) {
                $prereq = $this->db->fetch("SELECT id FROM hoc_phan WHERE ma_hp = :ma", ['ma' => $prereq_ma]);
                if ($prereq) {
                    $passedPrereq = $this->db->fetch("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = :sid AND hoc_phan_id = :pid AND diem_he4 >= 1.0", 
                        ['sid' => $studentId, 'pid' => $prereq['id']]);
                    if (!$passedPrereq) {
                        $pdo->rollBack();
                        return ['type' => 'danger', 'text' => 'Không đủ điều kiện đăng ký. Bạn chưa học đạt học phần tiên quyết: ' . $prereq_ma];
                    }
                }
            }

            // 4. Kiểm tra trùng lịch học cá nhân của sinh viên
            // Lấy lịch học của lớp chuẩn bị đăng ký
            $targetSchedules = $this->db->fetchAll("SELECT thu, tiet_bat_dau, so_tiet FROM thoi_khoa_bieu WHERE lop_hoc_phan_id = :lhpId", ['lhpId' => $lhpId]);
            
            // Lấy lịch học của các lớp đã đăng ký
            $activeSchedules = $this->db->fetchAll("
                SELECT hp.ten_hp, t.thu, t.tiet_bat_dau, t.so_tiet
                FROM dang_ky_hp dk
                JOIN lop_hoc_phan l ON l.id = dk.lop_hoc_phan_id
                JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
                JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
                WHERE dk.sinh_vien_id = :sid AND dk.hoc_ky = :hk AND dk.nam_hoc = :nh AND dk.trang_thai IN ('Chờ duyệt', 'Đã duyệt')
            ", ['sid' => $studentId, 'hk' => (string)$hk, 'nh' => $nh]);

            foreach ($targetSchedules as $tar) {
                $tar_thu = $tar['thu'];
                $tar_start = $tar['tiet_bat_dau'];
                $tar_count = $tar['so_tiet'];

                foreach ($activeSchedules as $act) {
                    $act_thu = $act['thu'];
                    $act_start = $act['tiet_bat_dau'];
                    $act_count = $act['so_tiet'];

                    if ($act_thu == $tar_thu) {
                        // Kiểm tra giao tiết học
                        if ($tar_start < $act_start + $act_count && $act_start < $tar_start + $tar_count) {
                            $pdo->rollBack();
                            return ['type' => 'danger', 'text' => 'Trùng lịch học vào Thứ ' . $tar_thu . ' (tiết ' . $act_start . '-' . ($act_start + $act_count - 1) . ') với lớp: ' . $act['ten_hp']];
                        }
                    }
                }
            }

            // Thực hiện đăng ký
            $this->db->query("
                INSERT INTO dang_ky_hp (sinh_vien_id, lop_hoc_phan_id, hoc_phan_id, hoc_ky, nam_hoc, trang_thai) 
                VALUES (:sid, :lhpId, :hpId, :hk, :nh, 'Đã duyệt')
            ", ['sid' => $studentId, 'lhpId' => $lhpId, 'hpId' => $hpId, 'hk' => (string)$hk, 'nh' => $nh]);
            
            $this->db->query("UPDATE lop_hoc_phan SET si_so_hien_tai = si_so_hien_tai + 1 WHERE id = :lhpId", ['lhpId' => $lhpId]);

            $pdo->commit();
            return ['type' => 'success', 'text' => 'Đăng ký lớp học phần thành công!'];
        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau.'];
        }
    }

    public function cancelCourse($studentId, $lhpId, $hk, $nh) {
        try {
            // Lấy thông tin lớp học phần để kiểm tra thời gian
            $class = $this->db->fetch("SELECT * FROM lop_hoc_phan WHERE id = :id", ['id' => $lhpId]);
            if (!$class) {
                return ['type' => 'danger', 'text' => 'Lớp học phần không tồn tại.'];
            }
            
            $now = date('Y-m-d H:i:s');
            if ($class['trang_thai_mo_lop'] !== 'Đang mở') {
                return ['type' => 'danger', 'text' => 'Lớp học phần đã đóng, không thể hủy đăng ký.'];
            }
            if ($class['ngay_bat_dau_dk'] !== null && $now < $class['ngay_bat_dau_dk']) {
                return ['type' => 'danger', 'text' => 'Chưa đến thời gian đăng ký lớp học phần này, không thể hủy.'];
            }
            if ($class['ngay_ket_thuc_dk'] !== null && $now > $class['ngay_ket_thuc_dk']) {
                return ['type' => 'danger', 'text' => 'Đã hết thời gian đăng ký lớp học phần này, không thể hủy.'];
            }

            // Kiểm tra sự tồn tại của đăng ký ở trạng thái 'Chờ duyệt' hoặc 'Đã duyệt'
            $chk = $this->db->fetch("
                SELECT id FROM dang_ky_hp 
                WHERE sinh_vien_id = :sid AND lop_hoc_phan_id = :lhpId AND hoc_ky = :hk AND nam_hoc = :nh AND trang_thai IN ('Chờ duyệt', 'Đã duyệt')
            ", ['sid' => $studentId, 'lhpId' => $lhpId, 'hk' => (string)$hk, 'nh' => $nh]);
                
            if ($chk) {
                 $this->db->query("DELETE FROM dang_ky_hp WHERE id = :id", ['id' => $chk['id']]);
                 $this->db->query("UPDATE lop_hoc_phan SET si_so_hien_tai = GREATEST(0, si_so_hien_tai - 1) WHERE id = :lhpId", ['lhpId' => $lhpId]);
                 return ['type' => 'success', 'text' => 'Đã hủy đăng ký lớp học phần thành công.'];
            } else {
                 return ['type' => 'warning', 'text' => 'Không thể hủy. Lớp học phần đã bị hủy.'];
            }
        } catch (\Exception $e) {
            return ['type' => 'danger', 'text' => 'Lỗi hệ thống, vui lòng thử lại sau.'];
        }
    }
}
