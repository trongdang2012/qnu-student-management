<?php
namespace App\Models;

use App\Core\Database;

class AdminStudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTotalStudents($search = '', $khoa = '', $nganh = '', $lop = '') {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (sv.ma_sv LIKE :search1 OR sv.ho_ten LIKE :search2 OR sv.email LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        if (!empty($khoa)) {
            $where .= " AND k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if (!empty($nganh)) {
            $where .= " AND n.ten_nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        if (!empty($lop)) {
            $where .= " AND l.ten_lop = :lop";
            $params['lop'] = $lop;
        }
        $sql = "SELECT COUNT(*) as total 
                FROM sinh_vien sv
                LEFT JOIN lop_sinh_hoat l ON l.id = sv.lop_sinh_hoat_id
                LEFT JOIN nganh n ON n.id = l.nganh_id
                LEFT JOIN khoa k ON k.id = n.khoa_id
                WHERE " . $where;
        return $this->db->fetch($sql, $params)['total'];
    }

    public function getStudents($search = '', $limit = 15, $offset = 0, $khoa = '', $nganh = '', $lop = '', $sort_by = 'ma_sv', $sort_dir = 'asc') {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (sv.ma_sv LIKE :search1 OR sv.ho_ten LIKE :search2 OR sv.email LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        if (!empty($khoa)) {
            $where .= " AND k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if (!empty($nganh)) {
            $where .= " AND n.ten_nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        if (!empty($lop)) {
            $where .= " AND l.ten_lop = :lop";
            $params['lop'] = $lop;
        }

        // Whitelist sắp xếp an toàn
        $allowed_sort = ['ma_sv', 'ho_ten', 'lop', 'nganh', 'trang_thai'];
        $sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'ma_sv';
        $sort_dir = strtolower($sort_dir) === 'desc' ? 'DESC' : 'ASC';

        // Ánh xạ trường sort cho cột alias
        $sort_field = "sv.ma_sv";
        if ($sort_by === 'ho_ten') $sort_field = "sv.ho_ten";
        elseif ($sort_by === 'lop') $sort_field = "l.ten_lop";
        elseif ($sort_by === 'nganh') $sort_field = "n.ten_nganh";
        elseif ($sort_by === 'trang_thai') $sort_field = "sv.trang_thai";

        $sql = "SELECT sv.*, u.username, l.ten_lop as lop, n.ten_nganh as nganh, k.ten_khoa as khoa 
                FROM sinh_vien sv 
                LEFT JOIN users u ON u.id = sv.user_id
                LEFT JOIN lop_sinh_hoat l ON l.id = sv.lop_sinh_hoat_id
                LEFT JOIN nganh n ON n.id = l.nganh_id
                LEFT JOIN khoa k ON k.id = n.khoa_id
                WHERE " . $where . " 
                ORDER BY $sort_field $sort_dir LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getFacultiesAndClasses() {
        $sql = "SELECT k.ten_khoa as khoa, n.ten_nganh as nganh, l.ten_lop as lop 
                FROM lop_sinh_hoat l 
                LEFT JOIN nganh n ON n.id = l.nganh_id 
                LEFT JOIN khoa k ON k.id = n.khoa_id 
                ORDER BY k.ten_khoa, n.ten_nganh, l.ten_lop";
        $rows = $this->db->fetchAll($sql);
        
        $tree = [];
        foreach ($rows as $row) {
            if ($row['khoa'] && $row['nganh'] && $row['lop']) {
                $tree[$row['khoa']][$row['nganh']][] = $row['lop'];
            }
        }
        return $tree;
    }

    public function getStudentById($id) {
        return $this->db->fetch("
            SELECT sv.*, l.ten_lop as lop, n.ten_nganh as nganh, k.ten_khoa as khoa 
            FROM sinh_vien sv 
            LEFT JOIN lop_sinh_hoat l ON l.id = sv.lop_sinh_hoat_id
            LEFT JOIN nganh n ON n.id = l.nganh_id
            LEFT JOIN khoa k ON k.id = n.khoa_id
            WHERE sv.id = :id
        ", ['id' => $id]);
    }

    public function getStudentByMaSv($ma_sv) {
        return $this->db->fetch("SELECT id FROM sinh_vien WHERE ma_sv = :ma_sv", ['ma_sv' => $ma_sv]);
    }

    public function addStudent($data) {
        $sql = "INSERT INTO sinh_vien 
            (user_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, email, so_dien_thoai, lop_sinh_hoat_id, nien_khoa, dia_chi, trang_thai)
            VALUES (:user_id, :ma_sv, :ho_ten, :ngay_sinh, :gioi_tinh, :email, :so_dien_thoai, :lop_sinh_hoat_id, :nien_khoa, :dia_chi, :trang_thai)";
        return $this->db->query($sql, $data);
    }

    public function updateStudent($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE sinh_vien SET 
            ho_ten = :ho_ten, ngay_sinh = :ngay_sinh, gioi_tinh = :gioi_tinh, email = :email,
            so_dien_thoai = :so_dien_thoai, lop_sinh_hoat_id = :lop_sinh_hoat_id, 
            nien_khoa = :nien_khoa, dia_chi = :dia_chi, trang_thai = :trang_thai
            WHERE id = :id";
        return $this->db->query($sql, $data);
    }

    public function deleteStudent($id) {
        $sv = $this->getStudentById($id);
        if ($sv) {
            $this->db->query("DELETE FROM sinh_vien WHERE id = :id", ['id' => $id]);
            if ($sv['user_id'] > 0) {
                $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $sv['user_id']]);
            }
            return true;
        }
        return false;
    }
}
