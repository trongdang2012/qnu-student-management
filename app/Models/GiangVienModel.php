<?php
namespace App\Models;

use App\Core\Database;

class GiangVienModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getGiangViens($search = '', $limit = 15, $offset = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (gv.ho_ten LIKE :search1 OR gv.ma_gv LIKE :search2 OR k.ten_khoa LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        $sql = "SELECT gv.*, k.ten_khoa 
                FROM giang_vien gv
                LEFT JOIN khoa k ON gv.khoa_id = k.id 
                WHERE $where 
                ORDER BY gv.id DESC 
                LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalGiangViens($search = '') {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (gv.ho_ten LIKE :search1 OR gv.ma_gv LIKE :search2 OR k.ten_khoa LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        $sql = "SELECT COUNT(*) as total 
                FROM giang_vien gv
                LEFT JOIN khoa k ON gv.khoa_id = k.id 
                WHERE $where";
        return (int)$this->db->fetch($sql, $params)['total'];
    }

    public function getAllGiangViens() {
        return $this->db->fetchAll("SELECT * FROM giang_vien ORDER BY ho_ten ASC");
    }

    public function getGiangVienById($id) {
        return $this->db->fetch("SELECT * FROM giang_vien WHERE id = :id", ['id' => $id]);
    }

    public function getGiangVienByMa($ma_gv) {
        return $this->db->fetch("SELECT * FROM giang_vien WHERE ma_gv = :ma_gv", ['ma_gv' => $ma_gv]);
    }

    public function addGiangVien($data) {
        $sql = "INSERT INTO giang_vien (ma_gv, ho_ten, khoa_id, hoc_vi, chuyen_nganh, email, so_dien_thoai) 
                VALUES (:ma_gv, :ho_ten, :khoa_id, :hoc_vi, :chuyen_nganh, :email, :so_dien_thoai)";
        return $this->db->query($sql, $data);
    }

    public function updateGiangVien($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE giang_vien 
                SET ma_gv = :ma_gv, ho_ten = :ho_ten, khoa_id = :khoa_id, hoc_vi = :hoc_vi, chuyen_nganh = :chuyen_nganh, email = :email, so_dien_thoai = :so_dien_thoai 
                WHERE id = :id";
        return $this->db->query($sql, $data);
    }

    public function deleteGiangVien($id) {
        return $this->db->query("DELETE FROM giang_vien WHERE id = :id", ['id' => $id]);
    }
}
