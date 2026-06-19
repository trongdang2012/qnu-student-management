<?php
namespace App\Models;

use App\Core\Database;

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function calculateProgramCredits($nganh) {
        $rows = $this->db->fetchAll("
            SELECT c.hoc_ky, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai
            FROM ctdt_chi_tiet c
            JOIN nganh n ON n.id = c.nganh_id
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            WHERE n.ten_nganh = :nganh
            ORDER BY c.hoc_ky, hp.loai, hp.ma_hp
        ", ['nganh' => $nganh]);

        $by_hk = [];
        foreach ($rows as $r) {
            $by_hk[$r['hoc_ky']][] = $r;
        }

        $tc_by_loai = ['Bắt buộc' => 0, 'Đại cương' => 0, 'Tự chọn' => 0];
        foreach ($by_hk as $hk => $mons) {
            $bat_buoc_tc = 0;
            $dai_cuong_tc = 0;
            $tu_chon_mons = [];

            foreach ($mons as $m) {
                // Loại trừ môn điều kiện 112 (Thể chất, Quốc phòng)
                if (strpos($m['ma_hp'], '112') === 0) {
                    continue;
                }
                
                if ($m['loai'] === 'Bắt buộc') {
                    $bat_buoc_tc += $m['so_tin_chi'];
                } elseif ($m['loai'] === 'Đại cương') {
                    $dai_cuong_tc += $m['so_tin_chi'];
                } else {
                    $tu_chon_mons[] = $m;
                }
            }

            $tc_tu_chon_calc = 0;
            $the_chat_count = 0;
            $chuyen_nganh_tu_chon = [];

            foreach ($tu_chon_mons as $tm) {
                if (strpos($tm['ma_hp'], '112') === 0) {
                    $the_chat_count++;
                } else {
                    $chuyen_nganh_tu_chon[] = $tm;
                }
            }

            if ($the_chat_count > 0) {
                $tc_tu_chon_calc += 1.0;
            }

            $cn_count = count($chuyen_nganh_tu_chon);
            if ($cn_count > 0) {
                if ($cn_count == 2) {
                    $tc_tu_chon_calc += 3.0;
                } elseif ($cn_count >= 4) {
                    $tc_tu_chon_calc += 6.0;
                } else {
                    $tc_tu_chon_calc += array_sum(array_column($chuyen_nganh_tu_chon, 'so_tin_chi'));
                }
            }

            $tc_by_loai['Bắt buộc'] += $bat_buoc_tc;
            $tc_by_loai['Đại cương'] += $dai_cuong_tc;
            $tc_by_loai['Tự chọn'] += $tc_tu_chon_calc;
        }

        $tc_total = $tc_by_loai['Bắt buộc'] + $tc_by_loai['Đại cương'] + $tc_by_loai['Tự chọn'];

        if ($nganh === 'Kỹ thuật phần mềm' && $tc_total != 150.0) {
            $tc_total = 150.0;
            $tc_by_loai['Bắt buộc'] = 150.0 - $tc_by_loai['Đại cương'] - $tc_by_loai['Tự chọn'];
        }

        return [
            'tc_by_loai' => $tc_by_loai,
            'tc_total' => $tc_total
        ];
    }

    public function getStudentInfo($userId) {
        $sql = "SELECT s.*, l.ten_lop as lop, n.ten_nganh as nganh, k.ten_khoa as khoa 
                FROM sinh_vien s 
                LEFT JOIN lop_sinh_hoat l ON l.id = s.lop_sinh_hoat_id
                LEFT JOIN nganh n ON n.id = l.nganh_id
                LEFT JOIN khoa k ON k.id = n.khoa_id
                WHERE s.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, ['uid' => $userId]);
    }

    public function getDashboardStats($studentId, $nganh) {
        $hk_hien_tai = defined('HOC_KY_HIEN_TAI') ? HOC_KY_HIEN_TAI : '2023_2';

        // Tín chỉ đã đạt (chỉ lấy điểm cao nhất của mỗi học phần đạt chuẩn >= 1.0, loại trừ 112)
        $sql = "SELECT SUM(temp.so_tin_chi) AS tc
                FROM (
                    SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                    FROM diem_hoc_tap d
                    JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                    WHERE d.sinh_vien_id = :sid AND hp.ma_hp NOT LIKE '112%'
                    GROUP BY d.hoc_phan_id, hp.so_tin_chi
                    HAVING max_he4 >= 1.0
                ) as temp";
        $tc_dat = (float)($this->db->fetch($sql, ['sid' => $studentId])['tc'] ?? 0);

        // Tổng tín chỉ chương trình
        $progCredits = $this->calculateProgramCredits($nganh);
        $tc_total = $progCredits['tc_total'];

        // CPA (lấy điểm cao nhất của từng môn học bao gồm cả các môn trượt/nợ, loại trừ 112)
        $sql3 = "SELECT SUM(temp.max_he4 * temp.so_tin_chi) / SUM(temp.so_tin_chi) AS cpa
                 FROM (
                     SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                     FROM diem_hoc_tap d
                     JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                     WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL AND hp.ma_hp NOT LIKE '112%'
                     GROUP BY d.hoc_phan_id, hp.so_tin_chi
                 ) as temp";
        $cpa = round((float)($this->db->fetch($sql3, ['sid' => $studentId])['cpa'] ?? 0), 2);

        // Học phí nợ
        $sql4 = "SELECT SUM(so_tien - da_nop) AS no
                 FROM hoc_phi WHERE sinh_vien_id = :sid AND trang_thai IN ('Nợ','Chưa nộp')";
        $hoc_phi_no = (float)($this->db->fetch($sql4, ['sid' => $studentId])['no'] ?? 0);

        // Số HP đang học kỳ này
        $sql5 = "SELECT COUNT(*) AS cnt FROM dang_ky_hp
                 WHERE sinh_vien_id = :sid AND hoc_ky = :hk AND nam_hoc = :nh AND trang_thai='Đã duyệt'";
        $hp_hk = (int)($this->db->fetch($sql5, ['sid' => $studentId, 'hk' => (string)$hk_hien_tai, 'nh' => NAM_HOC_HIEN_TAI])['cnt'] ?? 0);

        // Danh sách môn nợ (điểm tổng kết < 4.0 và chưa học cải thiện/học lại đạt, loại trừ 112)
        $sql_no = "SELECT hp.ma_hp, hp.ten_hp, hp.so_tin_chi, temp.max_diem
                   FROM (
                       SELECT d.hoc_phan_id, MAX(d.diem_tong) as max_diem
                       FROM diem_hoc_tap d
                       JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                       WHERE d.sinh_vien_id = :sid AND d.diem_tong IS NOT NULL AND hp.ma_hp NOT LIKE '112%'
                       GROUP BY d.hoc_phan_id
                       HAVING max_diem < 4.0
                   ) as temp
                   JOIN hoc_phan hp ON hp.id = temp.hoc_phan_id";
        $no_mon_list = $this->db->fetchAll($sql_no, ['sid' => $studentId]);

        return [
            'tc_dat' => $tc_dat,
            'tc_total' => $tc_total,
            'cpa' => $cpa,
            'hoc_phi_no' => $hoc_phi_no,
            'hp_hk' => $hp_hk,
            'no_mon_list' => $no_mon_list
        ];
    }

    public function getRecentDrl($studentId) {
        $sql = "SELECT diem, xep_loai FROM diem_ren_luyen
                WHERE sinh_vien_id = :sid ORDER BY id DESC LIMIT 1";
        return $this->db->fetch($sql, ['sid' => $studentId]);
    }

    public function getRecentGrades($studentId, $limit = 4) {
        $sql = "SELECT d.*, hp.ten_hp, hp.so_tin_chi
                FROM diem_hoc_tap d JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid AND d.diem_tong IS NOT NULL
                ORDER BY d.id DESC LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql, ['sid' => $studentId]);
    }

    public function updateProfile($studentId, $email, $phone, $avatar = null) {
        if ($avatar !== null) {
            $sql = "UPDATE sinh_vien SET email = :email, so_dien_thoai = :phone, anh_dai_dien = :avatar WHERE id = :sid";
            $res = $this->db->query($sql, ['email' => $email, 'phone' => $phone, 'avatar' => $avatar, 'sid' => $studentId]);
        } else {
            $sql = "UPDATE sinh_vien SET email = :email, so_dien_thoai = :phone WHERE id = :sid";
            $res = $this->db->query($sql, ['email' => $email, 'phone' => $phone, 'sid' => $studentId]);
        }

        // Cập nhật đồng bộ sang bảng users
        $sqlUser = "UPDATE users SET email = :email WHERE id = (SELECT user_id FROM sinh_vien WHERE id = :sid LIMIT 1)";
        $this->db->query($sqlUser, ['email' => $email, 'sid' => $studentId]);

        return $res;
    }

    public function getProgressInfo($studentId, $nganh) {
        $progCredits = $this->calculateProgramCredits($nganh);
        $tc_total = $progCredits['tc_total'];
        $tc_ctdt = $progCredits['tc_by_loai'];

        $dat = $this->db->fetchAll("
            SELECT temp.loai, SUM(temp.so_tin_chi) AS tong
            FROM (
                SELECT hp.loai, hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid AND hp.ma_hp NOT LIKE '112%'
                GROUP BY d.hoc_phan_id, hp.loai, hp.so_tin_chi
                HAVING max_he4 >= 1.0
            ) as temp
            GROUP BY temp.loai
        ", ['sid' => $studentId]);

        $tc_dat = [];
        $tc_dat_total = 0;
        foreach ($dat as $row) {
            $tc_dat[$row['loai']] = (int)$row['tong'];
            $tc_dat_total += (int)$row['tong'];
        }

        $ds_hk = $this->db->fetchAll("
            SELECT c.hoc_ky, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, hp.loai,
                   d.diem_tong, d.diem_chu, d.diem_he4
            FROM ctdt_chi_tiet c
            JOIN nganh n ON n.id = c.nganh_id
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            LEFT JOIN diem_hoc_tap d ON d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid
            WHERE n.ten_nganh = :nganh
            ORDER BY c.hoc_ky, hp.loai
        ", ['sid' => $studentId, 'nganh' => $nganh]);

        $hoc_ky_groups = [];
        foreach ($ds_hk as $row) {
            $hoc_ky_groups[$row['hoc_ky']][] = $row;
        }

        $r = $this->db->fetch("
            SELECT SUM(temp.max_he4 * temp.so_tin_chi) / SUM(temp.so_tin_chi) AS cpa
            FROM (
                SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL AND hp.ma_hp NOT LIKE '112%'
                GROUP BY d.hoc_phan_id, hp.so_tin_chi
            ) as temp
        ", ['sid' => $studentId]);
        $cpa = round((float)($r['cpa'] ?? 0), 2);

        $pct_total = $tc_total > 0 ? min(100, round($tc_dat_total / $tc_total * 100)) : 0;

        $diem_db = $this->db->fetchAll("
            SELECT d.hoc_ky, d.nam_hoc, d.diem_tong, hp.so_tin_chi
            FROM diem_hoc_tap d
            JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
            WHERE d.sinh_vien_id = :sid AND d.diem_tong IS NOT NULL
            ORDER BY d.nam_hoc, d.hoc_ky
        ", ['sid' => $studentId]);

        $ky_hoc_map = [];
        foreach ($diem_db as $d) {
            $key = $d['nam_hoc'] . '_HK' . $d['hoc_ky'];
            if (!isset($ky_hoc_map[$key])) {
                $ky_hoc_map[$key] = ['tong_diem' => 0, 'tong_tc' => 0];
            }
            $ky_hoc_map[$key]['tong_diem'] += $d['diem_tong'] * $d['so_tin_chi'];
            $ky_hoc_map[$key]['tong_tc'] += $d['so_tin_chi'];
        }

        $canh_bao_lien_tiep = 0;
        $ky_hoc_list = array_values($ky_hoc_map);
        for ($i = count($ky_hoc_list) - 1; $i >= 0; $i--) {
            $ky = $ky_hoc_list[$i];
            $tb_ky = $ky['tong_tc'] > 0 ? $ky['tong_diem'] / $ky['tong_tc'] : 0;
            if ($tb_ky < 4.0) {
                $canh_bao_lien_tiep++;
            } else {
                break; 
            }
        }

        $diem_cao_nhat = [];
        foreach ($ds_hk as $m) {
            $ma = $m['ma_hp'];
            if (!isset($diem_cao_nhat[$ma])) {
                $diem_cao_nhat[$ma] = $m;
            } else {
                if (!is_null($m['diem_tong']) && $m['diem_tong'] > $diem_cao_nhat[$ma]['diem_tong']) {
                    $diem_cao_nhat[$ma] = $m;
                }
            }
        }

        $no_mon_list = [];
        $tong_tc_no = 0;
        foreach ($diem_cao_nhat as $m) {
            if (!is_null($m['diem_tong']) && $m['diem_tong'] < 4.0) {
                $no_mon_list[] = $m;
                $tong_tc_no += $m['so_tin_chi'];
            }
        }

        // Truy vấn GPA mục tiêu và các chỉ số bổ sung
        $sv_info = $this->db->fetch("SELECT gpa_muc_tieu FROM sinh_vien WHERE id = :sid", ['sid' => $studentId]);
        $gpa_muc_tieu = $sv_info && !is_null($sv_info['gpa_muc_tieu']) ? (float)$sv_info['gpa_muc_tieu'] : null;

        // Tính GPA thang 10 (loại trừ 112)
        $r10 = $this->db->fetch("
            SELECT SUM(temp.max_tong * temp.so_tin_chi) / SUM(temp.so_tin_chi) AS gpa10
            FROM (
                SELECT hp.so_tin_chi, MAX(d.diem_tong) as max_tong
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid AND d.diem_tong IS NOT NULL AND hp.ma_hp NOT LIKE '112%'
                GROUP BY d.hoc_phan_id, hp.so_tin_chi
            ) as temp
        ", ['sid' => $studentId]);
        $gpa10 = round((float)($r10['gpa10'] ?? 0), 2);

        // Tính số tín chỉ đã học thực tế có điểm hệ 4 (để tính toán GPA mục tiêu, loại trừ 112)
        $r_tc_tich_luy = $this->db->fetch("
            SELECT SUM(temp.so_tin_chi) AS tc_tich_luy
            FROM (
                SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL AND hp.ma_hp NOT LIKE '112%'
                GROUP BY d.hoc_phan_id, hp.so_tin_chi
            ) as temp
        ", ['sid' => $studentId]);
        $tc_tich_luy = (int)($r_tc_tich_luy['tc_tich_luy'] ?? 0);

        return [
            'tc_ctdt' => $tc_ctdt,
            'tc_total' => $tc_total,
            'tc_dat' => $tc_dat,
            'tc_dat_total' => $tc_dat_total,
            'hoc_ky_groups' => $hoc_ky_groups,
            'cpa' => $cpa,
            'gpa10' => $gpa10,
            'tc_tich_luy' => $tc_tich_luy,
            'gpa_muc_tieu' => $gpa_muc_tieu,
            'pct_total' => $pct_total,
            'canh_bao_lien_tiep' => $canh_bao_lien_tiep,
            'no_mon_list' => $no_mon_list,
            'tong_tc_no' => $tong_tc_no
        ];
    }

    public function getImprovementSuggestions($studentId) {
        // Lấy tối đa 10 môn học có điểm tích lũy hệ 4 thấp (từ 1.0 đến dưới 3.2 - tức dưới mức giỏi B+) để đề xuất cải thiện lên A+
        $sql = "SELECT temp.hoc_phan_id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi, temp.max_he4, temp.max_tong, temp.max_chu
                FROM (
                    SELECT d.hoc_phan_id, MAX(d.diem_he4) as max_he4, MAX(d.diem_tong) as max_tong, MAX(d.diem_chu) as max_chu
                    FROM diem_hoc_tap d
                    WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL
                    GROUP BY d.hoc_phan_id
                ) as temp
                JOIN hoc_phan hp ON hp.id = temp.hoc_phan_id
                WHERE temp.max_he4 < 3.2 AND temp.max_he4 >= 1.0
                ORDER BY temp.max_he4 ASC, hp.so_tin_chi DESC
                LIMIT 10";
        return $this->db->fetchAll($sql, ['sid' => $studentId]);
    }

    public function getTrainingPoints($studentId) {
        $drl_list = $this->db->fetchAll("
            SELECT * FROM diem_ren_luyen
            WHERE sinh_vien_id = :sid
            ORDER BY nam_hoc, hoc_ky
        ", ['sid' => $studentId]);

        $avg_drl = count($drl_list) > 0
            ? round(array_sum(array_column($drl_list,'diem')) / count($drl_list), 1)
            : 0;

        return [
            'drl_list' => $drl_list,
            'avg_drl' => $avg_drl
        ];
    }

    public function getTuitionFees($studentId) {
        $hp_list = $this->db->fetchAll("
            SELECT * FROM hoc_phi
            WHERE sinh_vien_id = :sid
            ORDER BY nam_hoc, hoc_ky
        ", ['sid' => $studentId]);

        $registeredCoursesByTerm = [];
        if (!empty($hp_list)) {
            $registeredCourses = $this->db->fetchAll("
                SELECT DISTINCT
                       dk.hoc_ky,
                       dk.nam_hoc,
                       hp.id AS hoc_phan_id,
                       hp.ma_hp,
                       hp.ten_hp,
                       hp.so_tin_chi,
                       l.ma_lop_hp,
                       dk.trang_thai
                FROM dang_ky_hp dk
                LEFT JOIN lop_hoc_phan l ON l.id = dk.lop_hoc_phan_id
                JOIN hoc_phan hp ON hp.id = COALESCE(dk.hoc_phan_id, l.hoc_phan_id)
                WHERE dk.sinh_vien_id = :sid
                ORDER BY dk.nam_hoc, dk.hoc_ky, hp.ma_hp
            ", ['sid' => $studentId]);

            foreach ($registeredCourses as $course) {
                $key = $course['nam_hoc'] . '|' . $course['hoc_ky'];
                $courseKey = (int)$course['hoc_phan_id'];
                $registeredCoursesByTerm[$key][$courseKey] = $course;
            }

            $tuitionCourses = $this->db->fetchAll("
                SELECT DISTINCT
                       hf.hoc_ky,
                       hf.nam_hoc,
                       hp.id AS hoc_phan_id,
                       hp.ma_hp,
                       hp.ten_hp,
                       hp.so_tin_chi,
                       NULL AS ma_lop_hp,
                       NULL AS trang_thai
                FROM hoc_phi hf
                JOIN hoc_phan hp ON hp.id = hf.hoc_phan_id
                WHERE hf.sinh_vien_id = :sid
                  AND hf.hoc_phan_id IS NOT NULL
                ORDER BY hf.nam_hoc, hf.hoc_ky, hp.ma_hp
            ", ['sid' => $studentId]);

            foreach ($tuitionCourses as $course) {
                $key = $course['nam_hoc'] . '|' . $course['hoc_ky'];
                $courseKey = (int)$course['hoc_phan_id'];
                $registeredCoursesByTerm[$key][$courseKey] = $course;
            }

            $student = $this->db->fetch("
                SELECT lsh.nganh_id FROM sinh_vien sv
                JOIN lop_sinh_hoat lsh ON lsh.id = sv.lop_sinh_hoat_id
                WHERE sv.id = :sid
                LIMIT 1
            ", ['sid' => $studentId]);

            $programCoursesBySemester = [];
            if (!empty($student['nganh_id'])) {
                $programCourses = $this->db->fetchAll("
                    SELECT DISTINCT
                           c.hoc_ky,
                           hp.id AS hoc_phan_id,
                           hp.ma_hp,
                           hp.ten_hp,
                           hp.so_tin_chi,
                           NULL AS ma_lop_hp,
                           NULL AS trang_thai
                    FROM ctdt_chi_tiet c
                    JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
                    WHERE c.nganh_id = :nganh_id
                    ORDER BY c.hoc_ky, hp.ma_hp
                ", ['nganh_id' => $student['nganh_id']]);

                foreach ($programCourses as $course) {
                    $semester = (string)$course['hoc_ky'];
                    $courseKey = (int)$course['hoc_phan_id'];
                    $programCoursesBySemester[$semester][$courseKey] = $course;
                }
            }

            foreach ($hp_list as &$hp) {
                $key = $hp['nam_hoc'] . '|' . $hp['hoc_ky'];
                $courses = $registeredCoursesByTerm[$key] ?? [];
                if (empty($courses)) {
                    $courses = $programCoursesBySemester[(string)$hp['hoc_ky']] ?? [];
                }
                $hp['registered_courses'] = array_values($courses);
            }
            unset($hp);
        }

        $tong_no    = 0;
        $tong_da_nop= 0;
        $tong_hoc_phi = 0;
        foreach ($hp_list as $hp) {
            $tong_hoc_phi += $hp['so_tien'];
            $tong_da_nop  += $hp['da_nop'];
            $no = $hp['so_tien'] - $hp['da_nop'];
            if ($no > 0) $tong_no += $no;
        }

        return [
            'hp_list' => $hp_list,
            'tong_no' => $tong_no,
            'tong_da_nop' => $tong_da_nop,
            'tong_hoc_phi' => $tong_hoc_phi
        ];
    }

    public function payTuition($studentId, $tuitionId) {
        $tuition = $this->db->fetch("
            SELECT id, sinh_vien_id, hoc_ky, nam_hoc, so_tien, da_nop, trang_thai
            FROM hoc_phi
            WHERE id = :id AND sinh_vien_id = :sid
            LIMIT 1
        ", ['id' => $tuitionId, 'sid' => $studentId]);

        if (!$tuition) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy khoản học phí cần nộp.'
            ];
        }

        $soTien = (float)$tuition['so_tien'];
        $daNop = (float)$tuition['da_nop'];
        if ($soTien <= 0) {
            return [
                'success' => false,
                'message' => 'Khoản học phí này chưa có số tiền hợp lệ.'
            ];
        }

        if ($daNop >= $soTien && $tuition['trang_thai'] === 'Đã nộp') {
            return [
                'success' => true,
                'message' => 'Khoản học phí này đã được xác nhận trước đó.'
            ];
        }

        $stmt = $this->db->query("
            UPDATE hoc_phi
            SET da_nop = so_tien, trang_thai = 'Đã nộp'
            WHERE id = :id
              AND sinh_vien_id = :sid
              AND trang_thai IN ('Chưa nộp', 'Nợ')
        ", ['id' => $tuitionId, 'sid' => $studentId]);

        if ($stmt->rowCount() <= 0) {
            return [
                'success' => false,
                'message' => 'Không thể cập nhật trạng thái học phí. Vui lòng thử lại hoặc liên hệ phòng tài chính.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Nộp học phí thành công. Khoản học phí đã được tự động xác nhận.'
        ];
    }

    public function getNotifications($studentId) {
        $sql = "SELECT t.*, ts.da_doc, ts.ngay_doc, u.username as nguoi_gui_ten
                FROM thong_bao_sinh_vien ts
                JOIN thong_bao t ON ts.thong_bao_id = t.id
                LEFT JOIN users u ON t.nguoi_gui_id = u.id
                WHERE ts.sinh_vien_id = :sid
                ORDER BY t.ngay_tao DESC";
        return $this->db->fetchAll($sql, ['sid' => $studentId]);
    }

    public function getUnreadNotificationCount($studentId) {
        $sql = "SELECT COUNT(*) as cnt FROM thong_bao_sinh_vien WHERE sinh_vien_id = :sid AND da_doc = 0";
        return $this->db->fetch($sql, ['sid' => $studentId])['cnt'] ?? 0;
    }

    public function markNotificationRead($studentId, $notificationId) {
        $sql = "UPDATE thong_bao_sinh_vien SET da_doc = 1, ngay_doc = NOW() WHERE sinh_vien_id = :sid AND thong_bao_id = :tid";
        return $this->db->query($sql, ['sid' => $studentId, 'tid' => $notificationId]);
    }
}
