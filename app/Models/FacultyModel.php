<?php
namespace App\Models;

use App\Core\Database;

class FacultyModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFaculties($search = '', $limit = 15, $offset = 0) {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND ten_khoa LIKE :search";
            $params['search'] = "%$search%";
        }
        $sql = "SELECT * FROM khoa WHERE $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalFaculties($search = '') {
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND ten_khoa LIKE :search";
            $params['search'] = "%$search%";
        }
        $sql = "SELECT COUNT(*) as total FROM khoa WHERE $where";
        return (int)$this->db->fetch($sql, $params)['total'];
    }

    public function getAllFaculties() {
        return $this->db->fetchAll("SELECT * FROM khoa ORDER BY ten_khoa ASC");
    }

    public function getFacultyById($id) {
        return $this->db->fetch("SELECT * FROM khoa WHERE id = :id", ['id' => $id]);
    }

    public function getFacultyByName($name) {
        return $this->db->fetch("SELECT * FROM khoa WHERE ten_khoa = :name", ['name' => $name]);
    }

    public function addFaculty($ten_khoa) {
        return $this->db->query("INSERT INTO khoa (ten_khoa) VALUES (:ten)", ['ten' => $ten_khoa]);
    }

    public function updateFaculty($id, $ten_khoa) {
        return $this->db->query("UPDATE khoa SET ten_khoa = :ten WHERE id = :id", ['ten' => $ten_khoa, 'id' => $id]);
    }

    public function deleteFaculty($id) {
        return $this->db->query("DELETE FROM khoa WHERE id = :id", ['id' => $id]);
    }
}
