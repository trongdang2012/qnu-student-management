<?php
namespace App\Models;

use App\Core\Database;

class AdminGradeModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Điểm học tập ---

    public function getCoursesWithGradeStats($search = '', $hoc_ky = 0, $loai = '', $khoa = '', $nganh = '', $lop = '') {
        $params = [];
        
        $where_dk = "1 = 1";
        $where_diem = "1 = 1";
        
        if ($khoa !== '') {
            $where_dk .= " AND k.ten_khoa = :khoa_dk";
            $where_diem .= " AND k.ten_khoa = :khoa_diem";
            $params['khoa_dk'] = $khoa;
            $params['khoa_diem'] = $khoa;
        }
        if ($nganh !== '') {
            $where_dk .= " AND n.ten_nganh = :nganh_dk";
            $where_diem .= " AND n.ten_nganh = :nganh_diem";
            $params['nganh_dk'] = $nganh;
            $params['nganh_diem'] = $nganh;
        }
        if ($lop !== '') {
            $where_dk .= " AND l.ten_lop = :lop_dk";
            $where_diem .= " AND l.ten_lop = :lop_diem";
            $params['lop_dk'] = $lop;
            $params['lop_diem'] = $lop;
        }

        // Nếu có bộ lọc Khoa/Ngành/Lớp, chúng ta chỉ lấy những học phần có sinh viên thỏa mãn bộ lọc đăng ký
        $join_filter = "";
        $where_hp_filter = "";
        if ($khoa !== '' || $nganh !== '' || $lop !== '') {
            $join_filter = " JOIN dang_ky_hp dk_filter ON dk_filter.hoc_phan_id = hp.id AND dk_filter.trang_thai = 'Đã duyệt'
                             JOIN sinh_vien sv_filter ON sv_filter.id = dk_filter.sinh_vien_id
                             LEFT JOIN lop_sinh_hoat l_filter ON sv_filter.lop_sinh_hoat_id = l_filter.id
                             LEFT JOIN nganh n_filter ON l_filter.nganh_id = n_filter.id
                             LEFT JOIN khoa k_filter ON n_filter.khoa_id = k_filter.id";
            
            $where_hp_filter = " AND 1=1";
            if ($khoa !== '') {
                $where_hp_filter .= " AND k_filter.ten_khoa = :khoa_hp";
                $params['khoa_hp'] = $khoa;
            }
            if ($nganh !== '') {
                $where_hp_filter .= " AND n_filter.ten_nganh = :nganh_hp";
                $params['nganh_hp'] = $nganh;
            }
            if ($lop !== '') {
                $where_hp_filter .= " AND l_filter.ten_lop = :lop_hp";
                $params['lop_hp'] = $lop;
            }
        }

        $sql = "
            SELECT DISTINCT hp.*,
                   (SELECT COUNT(*) FROM dang_ky_hp dk JOIN sinh_vien sv ON sv.id = dk.sinh_vien_id LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id LEFT JOIN nganh n ON l.nganh_id = n.id LEFT JOIN khoa k ON n.khoa_id = k.id WHERE dk.hoc_phan_id = hp.id AND dk.trang_thai = 'Đã duyệt' AND $where_dk) AS si_so_dk,
                   (SELECT COUNT(DISTINCT d.sinh_vien_id) FROM diem_hoc_tap d JOIN sinh_vien sv ON sv.id = d.sinh_vien_id LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id LEFT JOIN nganh n ON l.nganh_id = n.id LEFT JOIN khoa k ON n.khoa_id = k.id WHERE d.hoc_phan_id = hp.id AND $where_diem) AS so_sv_co_diem
            FROM hoc_phan hp
            $join_filter
            WHERE 1 = 1 $where_hp_filter
        ";
        
        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (hp.ma_hp LIKE :search1 OR hp.ten_hp LIKE :search2 OR hp.nien_khoa LIKE :search3)";
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }
        
        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= " AND hp.hoc_ky = :hoc_ky";
            $params['hoc_ky'] = $hoc_ky;
        }
        
        if (in_array($loai, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
            $sql .= " AND hp.loai = :loai";
            $params['loai'] = $loai;
        }
        
        $sql .= " ORDER BY hp.hoc_ky ASC, hp.ma_hp ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getFacultiesAndClasses() {
        $sql = "SELECT k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop 
                FROM lop_sinh_hoat l 
                JOIN nganh n ON l.nganh_id = n.id 
                JOIN khoa k ON n.khoa_id = k.id 
                ORDER BY k.ten_khoa, n.ten_nganh, l.ten_lop";
        $rows = $this->db->fetchAll($sql);
        
        $tree = [];
        foreach ($rows as $row) {
            $tree[$row['khoa']][$row['nganh']][] = $row['lop'];
        }
        return $tree;
    }

    public function getCourseById($id) {
        return $this->db->fetch("SELECT * FROM hoc_phan WHERE id = :id", ['id' => $id]);
    }

    public function getStudentsForGrade($hoc_phan_id, $khoa = '', $nganh = '', $lop = '') {
        $params = ['hp_id' => $hoc_phan_id];
        $where_sv = "";
        if ($khoa !== '') {
            $where_sv .= " AND k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $where_sv .= " AND n.ten_nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        if ($lop !== '') {
            $where_sv .= " AND l.ten_lop = :lop";
            $params['lop'] = $lop;
        }

        $sql = "
            SELECT sv.id AS sinh_vien_id, sv.ma_sv, sv.ho_ten, l.ten_lop AS lop, dk.hoc_ky, dk.nam_hoc,
                   d.diem_cc, d.diem_gk, d.diem_ck, d.diem_tong, d.diem_chu, d.diem_he4
            FROM dang_ky_hp dk
            JOIN sinh_vien sv ON sv.id = dk.sinh_vien_id
            LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
            LEFT JOIN nganh n ON l.nganh_id = n.id
            LEFT JOIN khoa k ON n.khoa_id = k.id
            LEFT JOIN diem_hoc_tap d ON d.sinh_vien_id = sv.id AND d.hoc_phan_id = dk.hoc_phan_id
            WHERE dk.hoc_phan_id = :hp_id AND dk.trang_thai = 'Đã duyệt' $where_sv
            ORDER BY sv.ma_sv ASC
        ";
        return $this->db->fetchAll($sql, $params);
    }

    public function getStudentInfoForGradeError($sv_id) {
        return $this->db->fetch("SELECT ho_ten, ma_sv FROM sinh_vien WHERE id = :id", ['id' => $sv_id]);
    }

    public function getStudentInfoForGradeErrorByCode($ma_sv, $khoa, $nganh, $lop) {
        return $this->db->fetch(
            "SELECT sv.id AS sinh_vien_id, sv.ho_ten, sv.ma_sv 
             FROM sinh_vien sv 
             LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
             LEFT JOIN nganh n ON l.nganh_id = n.id
             LEFT JOIN khoa k ON n.khoa_id = k.id
             WHERE sv.ma_sv = :ma_sv AND k.ten_khoa = :khoa AND n.ten_nganh = :nganh AND l.ten_lop = :lop",
            [
                'ma_sv' => $ma_sv,
                'khoa' => $khoa,
                'nganh' => $nganh,
                'lop' => $lop
            ]
        );
    }

    public function getRegistrationInfo($sv_id, $hp_id) {
        return $this->db->fetch("SELECT hoc_ky, nam_hoc FROM dang_ky_hp WHERE sinh_vien_id = :sv_id AND hoc_phan_id = :hp_id AND trang_thai = 'Đã duyệt' LIMIT 1", 
            ['sv_id' => $sv_id, 'hp_id' => $hp_id]);
    }

    public function saveAcademicGrade($row, $hoc_phan_id) {
        $exists = $this->db->fetch("SELECT id FROM diem_hoc_tap WHERE sinh_vien_id = :sv_id AND hoc_phan_id = :hp_id", [
            'sv_id' => $row['sinh_vien_id'],
            'hp_id' => $hoc_phan_id
        ]);
        
        if ($exists) {
            $this->db->query("
                UPDATE diem_hoc_tap 
                SET diem_cc = :cc, diem_gk = :gk, diem_ck = :ck, diem_tong = :tong, diem_chu = :chu, diem_he4 = :he4, hoc_ky = :hk, nam_hoc = :nh
                WHERE id = :id
            ", [
                'cc' => $row['diem_cc'], 'gk' => $row['diem_gk'], 'ck' => $row['diem_ck'],
                'tong' => $row['diem_tong'], 'chu' => $row['diem_chu'], 'he4' => $row['diem_he4'],
                'hk' => $row['hoc_ky'], 'nh' => $row['nam_hoc'],
                'id' => $exists['id']
            ]);
        } else {
            $this->db->query("
                INSERT INTO diem_hoc_tap (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, diem_cc, diem_gk, diem_ck, diem_tong, diem_chu, diem_he4)
                VALUES (:sv_id, :hp_id, :hk, :nh, :cc, :gk, :ck, :tong, :chu, :he4)
            ", [
                'sv_id' => $row['sinh_vien_id'], 'hp_id' => $hoc_phan_id,
                'hk' => $row['hoc_ky'], 'nh' => $row['nam_hoc'],
                'cc' => $row['diem_cc'], 'gk' => $row['diem_gk'], 'ck' => $row['diem_ck'],
                'tong' => $row['diem_tong'], 'chu' => $row['diem_chu'], 'he4' => $row['diem_he4']
            ]);
        }
    }

    public function beginTransaction() {
        // Our DB wrapper uses mysqli, we don't have begin_transaction natively exposed in App\Core\Database if it's basic
        // Let's assume we can call query directly
        $this->db->query("START TRANSACTION");
    }

    public function commit() {
        $this->db->query("COMMIT");
    }

    public function rollback() {
        $this->db->query("ROLLBACK");
    }

    // --- Điểm rèn luyện ---

    public function getKhoaList() {
        return $this->db->fetchAll("SELECT ten_khoa as khoa FROM khoa ORDER BY ten_khoa ASC");
    }

    public function getNganhListByKhoa($khoa) {
        return $this->db->fetchAll("SELECT DISTINCT n.ten_nganh as nganh FROM nganh n JOIN khoa k ON n.khoa_id = k.id WHERE k.ten_khoa = :khoa ORDER BY n.ten_nganh ASC", ['khoa' => $khoa]);
    }

    // Get all departments regardless of faculty
    public function getAllNganhList() {
        return $this->db->fetchAll("SELECT ten_nganh as nganh FROM nganh ORDER BY ten_nganh ASC");
    }

    public function getLopList() {
        return $this->db->fetchAll("SELECT ten_lop as lop FROM lop_sinh_hoat ORDER BY ten_lop ASC");
    }

    // Get list of classes filtered by faculty (khoa) and department (nganh)
    public function getLopListByKhoaAndNganh($khoa, $nganh) {
        return $this->db->fetchAll(
            "SELECT DISTINCT l.ten_lop as lop FROM lop_sinh_hoat l JOIN nganh n ON l.nganh_id = n.id JOIN khoa k ON n.khoa_id = k.id WHERE k.ten_khoa = :khoa AND n.ten_nganh = :nganh ORDER BY l.ten_lop ASC",
            ['khoa' => $khoa, 'nganh' => $nganh]
        );
    }



    public function getTrainingGrades($hoc_ky, $nam_hoc, $search = '', $khoa = '', $nganh = '', $lop_filter = '') {
        $sql = "
            SELECT sv.id AS sinh_vien_id, sv.ma_sv, sv.ho_ten, l.ten_lop AS lop, n.ten_nganh AS nganh, k.ten_khoa AS khoa,
                   drl.diem, drl.xep_loai, drl.ghi_chu
            FROM sinh_vien sv
            LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
            LEFT JOIN nganh n ON l.nganh_id = n.id
            LEFT JOIN khoa k ON n.khoa_id = k.id
            LEFT JOIN diem_ren_luyen drl 
                   ON drl.sinh_vien_id = sv.id 
                  AND drl.hoc_ky = :hk 
                  AND drl.nam_hoc = :nh
            WHERE 1 = 1
        ";
        $params = ['hk' => $hoc_ky, 'nh' => $nam_hoc];

        if ($search !== '') {
            $sql .= " AND (sv.ma_sv LIKE :search1 OR sv.ho_ten LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if ($khoa !== '') {
            $sql .= " AND k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $sql .= " AND n.ten_nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        if ($lop_filter !== '') {
            $sql .= " AND l.ten_lop = :lop";
            $params['lop'] = $lop_filter;
        }

        $sql .= " ORDER BY l.ten_lop ASC, sv.ma_sv ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function saveTrainingGrade($sv_id, $hk, $nh, $total_score, $xep_loai, $ghi_chu_json) {
        $exists = $this->db->fetch("SELECT id FROM diem_ren_luyen WHERE sinh_vien_id = :sv_id AND hoc_ky = :hk AND nam_hoc = :nh", [
            'sv_id' => $sv_id, 'hk' => $hk, 'nh' => $nh
        ]);
        
        if ($exists) {
            return $this->db->query("UPDATE diem_ren_luyen SET diem = :diem, xep_loai = :xl, ghi_chu = :gc WHERE id = :id", [
                'diem' => $total_score, 'xl' => $xep_loai, 'gc' => $ghi_chu_json, 'id' => $exists['id']
            ]);
        } else {
            return $this->db->query("INSERT INTO diem_ren_luyen (sinh_vien_id, hoc_ky, nam_hoc, diem, xep_loai, ghi_chu) VALUES (:sv_id, :hk, :nh, :diem, :xl, :gc)", [
                'sv_id' => $sv_id, 'hk' => $hk, 'nh' => $nh, 'diem' => $total_score, 'xl' => $xep_loai, 'gc' => $ghi_chu_json
            ]);
        }
    }

    // --- Báo cáo ---

    public function getStudentByCode($ma_sv) {
        return $this->db->fetch("SELECT * FROM sinh_vien WHERE ma_sv = :ma_sv", ['ma_sv' => $ma_sv]);
    }

    public function getStudentGradesReport($sv_id) {
        $sql = "
            SELECT d.*, hp.ten_hp, hp.ma_hp, hp.so_tin_chi, hp.loai
            FROM diem_hoc_tap d
            JOIN hoc_phan hp ON hp.id = d.hoc_phan_id
            WHERE d.sinh_vien_id = :sv_id
            ORDER BY d.nam_hoc ASC, d.hoc_ky ASC, hp.ten_hp ASC
        ";
        return $this->db->fetchAll($sql, ['sv_id' => $sv_id]);
    }

    public function getTrainingGradeBySemester($sv_id, $hk, $nh) {
        return $this->db->fetch("SELECT diem, xep_loai FROM diem_ren_luyen WHERE sinh_vien_id = :sv_id AND hoc_ky = :hk AND nam_hoc = :nh LIMIT 1", [
            'sv_id' => $sv_id, 'hk' => $hk, 'nh' => $nh
        ]);
    }

    public function calculateXepLoai($score) {
        if ($score >= 90) return 'Xuất sắc';
        if ($score >= 80) return 'Tốt';
        if ($score >= 70) return 'Khá';
        if ($score >= 50) return 'Trung bình';
        if ($score >= 30) return 'Yếu';
        return 'Kém';
    }
}
