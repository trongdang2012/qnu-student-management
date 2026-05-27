<?php
namespace App\Models;

use App\Core\Database;

class StudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getStudentInfo($userId) {
        $sql = "SELECT s.* FROM sinh_vien s WHERE s.user_id = :uid LIMIT 1";
        return $this->db->fetch($sql, ['uid' => $userId]);
    }

    public function getDashboardStats($studentId, $nganh) {
        $hk_hien_tai = defined('HOC_KY_HIEN_TAI') ? HOC_KY_HIEN_TAI : '2023_2';

        // Tín chỉ đã đạt (chỉ lấy điểm cao nhất của mỗi học phần đạt chuẩn >= 1.0)
        $sql = "SELECT SUM(temp.so_tin_chi) AS tc
                FROM (
                    SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                    FROM diem_hoc_tap d
                    JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                    WHERE d.sinh_vien_id = :sid
                    GROUP BY d.hoc_phan_id, hp.so_tin_chi
                    HAVING max_he4 >= 1.0
                ) as temp";
        $tc_dat = (float)($this->db->fetch($sql, ['sid' => $studentId])['tc'] ?? 0);

        // Tổng tín chỉ chương trình
        $sql2 = "SELECT SUM(hp.so_tin_chi) AS tc
                 FROM ctdt_chi_tiet c JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
                 WHERE c.nganh = :nganh";
        $tc_total = (float)($this->db->fetch($sql2, ['nganh' => $nganh])['tc'] ?? 130);

        // CPA (lấy điểm cao nhất của từng môn học bao gồm cả các môn trượt/nợ)
        $sql3 = "SELECT SUM(temp.max_he4 * temp.so_tin_chi) / SUM(temp.so_tin_chi) AS cpa
                 FROM (
                     SELECT hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                     FROM diem_hoc_tap d
                     JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                     WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL
                     GROUP BY d.hoc_phan_id, hp.so_tin_chi
                 ) as temp";
        $cpa = round((float)($this->db->fetch($sql3, ['sid' => $studentId])['cpa'] ?? 0), 2);

        // Học phí nợ
        $sql4 = "SELECT SUM(so_tien - da_nop) AS no
                 FROM hoc_phi WHERE sinh_vien_id = :sid AND trang_thai IN ('Nợ','Chưa nộp')";
        $hoc_phi_no = (float)($this->db->fetch($sql4, ['sid' => $studentId])['no'] ?? 0);

        // Số HP đang học kỳ này
        $sql5 = "SELECT COUNT(*) AS cnt FROM dang_ky_hp
                 WHERE sinh_vien_id = :sid AND hoc_ky = :hk AND trang_thai='Đã duyệt'";
        $hp_hk = (int)($this->db->fetch($sql5, ['sid' => $studentId, 'hk' => $hk_hien_tai])['cnt'] ?? 0);

        // Danh sách môn nợ (điểm tổng kết < 4.0 và chưa học cải thiện/học lại đạt)
        $sql_no = "SELECT hp.ma_hp, hp.ten_hp, hp.so_tin_chi, temp.max_diem
                   FROM (
                       SELECT d.hoc_phan_id, MAX(d.diem_tong) as max_diem
                       FROM diem_hoc_tap d
                       WHERE d.sinh_vien_id = :sid AND d.diem_tong IS NOT NULL
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
            return $this->db->query($sql, ['email' => $email, 'phone' => $phone, 'avatar' => $avatar, 'sid' => $studentId]);
        } else {
            $sql = "UPDATE sinh_vien SET email = :email, so_dien_thoai = :phone WHERE id = :sid";
            return $this->db->query($sql, ['email' => $email, 'phone' => $phone, 'sid' => $studentId]);
        }
    }

    public function getProgressInfo($studentId, $nganh) {
        $ctdt = $this->db->fetchAll("
            SELECT hp.loai, SUM(hp.so_tin_chi) AS tong
            FROM ctdt_chi_tiet c
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            WHERE c.nganh = :nganh
            GROUP BY hp.loai
        ", ['nganh' => $nganh]);

        $tc_ctdt = [];
        $tc_total = 0;
        foreach ($ctdt as $row) {
            $tc_ctdt[$row['loai']] = (int)$row['tong'];
            $tc_total += (int)$row['tong'];
        }
        if ($tc_total == 0) $tc_total = 130;

        $dat = $this->db->fetchAll("
            SELECT temp.loai, SUM(temp.so_tin_chi) AS tong
            FROM (
                SELECT hp.loai, hp.so_tin_chi, MAX(d.diem_he4) as max_he4
                FROM diem_hoc_tap d
                JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
                WHERE d.sinh_vien_id = :sid
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
            JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
            LEFT JOIN diem_hoc_tap d ON d.hoc_phan_id = hp.id AND d.sinh_vien_id = :sid
            WHERE c.nganh = :nganh
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
                WHERE d.sinh_vien_id = :sid AND d.diem_he4 IS NOT NULL
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

        return [
            'tc_ctdt' => $tc_ctdt,
            'tc_total' => $tc_total,
            'tc_dat' => $tc_dat,
            'tc_dat_total' => $tc_dat_total,
            'hoc_ky_groups' => $hoc_ky_groups,
            'cpa' => $cpa,
            'pct_total' => $pct_total,
            'canh_bao_lien_tiep' => $canh_bao_lien_tiep,
            'no_mon_list' => $no_mon_list,
            'tong_tc_no' => $tong_tc_no
        ];
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
