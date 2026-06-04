<?php
namespace App\Models;

use App\Core\Database;

class MajorModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getMajors($search = '', $limit = 15, $offset = 0, $khoa_id = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (n.ten_nganh LIKE :search OR k.ten_khoa LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }
        if ($khoa_id > 0) {
            $where .= " AND n.khoa_id = :khoa_id";
            $params['khoa_id'] = $khoa_id;
        }
        $sql = "SELECT n.*, k.ten_khoa 
                FROM nganh n 
                LEFT JOIN khoa k ON k.id = n.khoa_id 
                WHERE $where 
                ORDER BY n.id DESC 
                LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalMajors($search = '', $khoa_id = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (n.ten_nganh LIKE :search OR k.ten_khoa LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }
        if ($khoa_id > 0) {
            $where .= " AND n.khoa_id = :khoa_id";
            $params['khoa_id'] = $khoa_id;
        }
        $sql = "SELECT COUNT(*) as total 
                FROM nganh n 
                LEFT JOIN khoa k ON k.id = n.khoa_id 
                WHERE $where";
        return (int)$this->db->fetch($sql, $params)['total'];
    }

    public function getAllMajors() {
        return $this->db->fetchAll("SELECT n.*, k.ten_khoa FROM nganh n LEFT JOIN khoa k ON k.id = n.khoa_id ORDER BY n.ten_nganh ASC");
    }

    public function getMajorById($id) {
        return $this->db->fetch("SELECT * FROM nganh WHERE id = :id", ['id' => $id]);
    }

    public function getMajorByNameAndFaculty($name, $khoa_id) {
        return $this->db->fetch("SELECT * FROM nganh WHERE ten_nganh = :name AND khoa_id = :khoa_id", ['name' => $name, 'khoa_id' => $khoa_id]);
    }

    public function getMajorsByFacultyId($khoa_id) {
        return $this->db->fetchAll("SELECT * FROM nganh WHERE khoa_id = :khoa_id ORDER BY ten_nganh ASC", ['khoa_id' => $khoa_id]);
    }

    public function addMajor($ten_nganh, $khoa_id) {
        return $this->db->query("INSERT INTO nganh (ten_nganh, khoa_id) VALUES (:ten, :khoa_id)", [
            'ten' => $ten_nganh,
            'khoa_id' => $khoa_id
        ]);
    }

    public function updateMajor($id, $ten_nganh, $khoa_id) {
        return $this->db->query("UPDATE nganh SET ten_nganh = :ten, khoa_id = :khoa_id WHERE id = :id", [
            'ten' => $ten_nganh,
            'khoa_id' => $khoa_id,
            'id' => $id
        ]);
    }

    public function deleteMajor($id) {
        return $this->db->query("DELETE FROM nganh WHERE id = :id", ['id' => $id]);
    }
}
