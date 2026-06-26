<?php
namespace App\Models;

use App\Core\Database;

class PhongHocModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getPhongHocs($search = '', $limit = 15, $offset = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (ten_phong LIKE :search1 OR loai_phong LIKE :search2)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
        }
        $sql = "SELECT * FROM phong_hoc WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalPhongHocs($search = '') {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (ten_phong LIKE :search1 OR loai_phong LIKE :search2)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
        }
        $sql = "SELECT COUNT(*) as total FROM phong_hoc WHERE $where";
        return (int)$this->db->fetch($sql, $params)['total'];
    }

    public function getAllPhongHocs() {
        return $this->db->fetchAll("SELECT * FROM phong_hoc ORDER BY ten_phong ASC");
    }

    public function getPhongHocById($id) {
        return $this->db->fetch("SELECT * FROM phong_hoc WHERE id = :id", ['id' => $id]);
    }

    public function getPhongHocByTen($ten_phong) {
        return $this->db->fetch("SELECT * FROM phong_hoc WHERE ten_phong = :ten_phong", ['ten_phong' => $ten_phong]);
    }

    public function addPhongHoc($data) {
        $sql = "INSERT INTO phong_hoc (ten_phong, loai_phong, suc_chua) 
                VALUES (:ten_phong, :loai_phong, :suc_chua)";
        return $this->db->query($sql, $data);
    }

    public function updatePhongHoc($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE phong_hoc 
                SET ten_phong = :ten_phong, loai_phong = :loai_phong, suc_chua = :suc_chua 
                WHERE id = :id";
        return $this->db->query($sql, $data);
    }

    public function deletePhongHoc($id) {
        return $this->db->query("DELETE FROM phong_hoc WHERE id = :id", ['id' => $id]);
    }
}
