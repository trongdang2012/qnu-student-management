<?php
namespace App\Models;

use App\Core\Database;

class ClassStudentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getClasses($search = '', $limit = 15, $offset = 0, $khoa_id = 0, $nganh_id = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (l.ten_lop LIKE :search OR n.ten_nganh LIKE :search2 OR k.ten_khoa LIKE :search3)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        if ($khoa_id > 0) {
            $where .= " AND k.id = :khoa_id";
            $params['khoa_id'] = $khoa_id;
        }
        if ($nganh_id > 0) {
            $where .= " AND n.id = :nganh_id";
            $params['nganh_id'] = $nganh_id;
        }
        $sql = "SELECT l.*, n.ten_nganh, k.ten_khoa 
                FROM lop_sinh_hoat l 
                LEFT JOIN nganh n ON n.id = l.nganh_id 
                LEFT JOIN khoa k ON k.id = n.khoa_id
                WHERE $where 
                ORDER BY l.id DESC 
                LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalClasses($search = '', $khoa_id = 0, $nganh_id = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (l.ten_lop LIKE :search OR n.ten_nganh LIKE :search2 OR k.ten_khoa LIKE :search3)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        if ($khoa_id > 0) {
            $where .= " AND k.id = :khoa_id";
            $params['khoa_id'] = $khoa_id;
        }
        if ($nganh_id > 0) {
            $where .= " AND n.id = :nganh_id";
            $params['nganh_id'] = $nganh_id;
        }
        $sql = "SELECT COUNT(*) as total 
                FROM lop_sinh_hoat l 
                LEFT JOIN nganh n ON n.id = l.nganh_id 
                LEFT JOIN khoa k ON k.id = n.khoa_id
                WHERE $where";
        return (int)$this->db->fetch($sql, $params)['total'];
    }

    public function getAllClasses() {
        return $this->db->fetchAll("
            SELECT l.*, n.ten_nganh, k.ten_khoa 
            FROM lop_sinh_hoat l 
            LEFT JOIN nganh n ON n.id = l.nganh_id 
            LEFT JOIN khoa k ON k.id = n.khoa_id
            ORDER BY l.ten_lop ASC
        ");
    }

    public function getClassById($id) {
        return $this->db->fetch("SELECT * FROM lop_sinh_hoat WHERE id = :id", ['id' => $id]);
    }

    public function getClassByName($name) {
        return $this->db->fetch("SELECT * FROM lop_sinh_hoat WHERE ten_lop = :name", ['name' => $name]);
    }

    public function getClassesByMajorId($nganh_id) {
        return $this->db->fetchAll("SELECT * FROM lop_sinh_hoat WHERE nganh_id = :nganh_id ORDER BY ten_lop ASC", ['nganh_id' => $nganh_id]);
    }

    public function addClass($ten_lop, $nganh_id) {
        return $this->db->query("INSERT INTO lop_sinh_hoat (ten_lop, nganh_id) VALUES (:ten, :nganh_id)", [
            'ten' => $ten_lop,
            'nganh_id' => $nganh_id
        ]);
    }

    public function updateClass($id, $ten_lop, $nganh_id) {
        return $this->db->query("UPDATE lop_sinh_hoat SET ten_lop = :ten, nganh_id = :nganh_id WHERE id = :id", [
            'ten' => $ten_lop,
            'nganh_id' => $nganh_id,
            'id' => $id
        ]);
    }

    public function deleteClass($id) {
        return $this->db->query("DELETE FROM lop_sinh_hoat WHERE id = :id", ['id' => $id]);
    }
}
