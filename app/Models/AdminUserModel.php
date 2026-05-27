<?php
namespace App\Models;

use App\Core\Database;

class AdminUserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function countUsers($search = '', $khoa = '', $lop = '') {
        $sql = "SELECT COUNT(u.id) as total FROM users u";
        $params = [];
        $where = "1=1";
        
        if (!empty($khoa) || !empty($lop)) {
            $sql .= " LEFT JOIN sinh_vien sv ON sv.user_id = u.id";
            if (!empty($khoa)) {
                $where .= " AND sv.khoa = :khoa";
                $params['khoa'] = $khoa;
            }
            if (!empty($lop)) {
                $where .= " AND sv.lop = :lop";
                $params['lop'] = $lop;
            }
        }
        
        if (!empty($search)) {
            $where .= " AND (u.username LIKE :search OR u.id LIKE :searchId)";
            $params['search'] = "%$search%";
            $params['searchId'] = "%$search%";
        }
        
        $sql .= " WHERE " . $where;
        $row = $this->db->fetch($sql, $params);
        return (int)$row['total'];
    }

    public function getUsers($offset, $limit, $search = '', $khoa = '', $lop = '', $sort_by = 'created_at', $sort_dir = 'desc') {
        $sql = "SELECT u.* FROM users u";
        $params = [];
        $where = "1=1";
        
        if (!empty($khoa) || !empty($lop)) {
            $sql .= " LEFT JOIN sinh_vien sv ON sv.user_id = u.id";
            if (!empty($khoa)) {
                $where .= " AND sv.khoa = :khoa";
                $params['khoa'] = $khoa;
            }
            if (!empty($lop)) {
                $where .= " AND sv.lop = :lop";
                $params['lop'] = $lop;
            }
        }
        
        if (!empty($search)) {
            $where .= " AND (u.username LIKE :search OR u.id LIKE :searchId)";
            $params['search'] = "%$search%";
            $params['searchId'] = "%$search%";
        }

        // Whitelist sắp xếp an toàn
        $allowed_sort = ['id', 'username', 'role', 'created_at'];
        $sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'created_at';
        $sort_dir = strtolower($sort_dir) === 'desc' ? 'DESC' : 'ASC';
        
        $sql .= " WHERE " . $where . " ORDER BY u.$sort_by $sort_dir LIMIT $limit OFFSET $offset";
        return $this->db->fetchAll($sql, $params);
    }

    public function getFacultiesAndClasses() {
        $sql = "SELECT DISTINCT khoa, lop FROM sinh_vien WHERE khoa IS NOT NULL AND khoa != '' AND lop IS NOT NULL AND lop != '' ORDER BY khoa, lop";
        $rows = $this->db->fetchAll($sql);
        
        $tree = [];
        foreach ($rows as $row) {
            $tree[$row['khoa']][] = $row['lop'];
        }
        return $tree;
    }

    public function getUserById($id) {
        return $this->db->fetch('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public function getUserByUsername($username) {
        return $this->db->fetch('SELECT id FROM users WHERE username = :un', ['un' => $username]);
    }

    public function insertUser($username, $hashed_password, $role) {
        $sql = "INSERT INTO users (username, password, role) VALUES (:un, :pw, :role)";
        return $this->db->query($sql, ['un' => $username, 'pw' => $hashed_password, 'role' => $role]);
    }

    public function updateUserPasswordAndRole($id, $hashed_password, $role) {
        $sql = "UPDATE users SET password = :pw, role = :role WHERE id = :id";
        return $this->db->query($sql, ['pw' => $hashed_password, 'role' => $role, 'id' => $id]);
    }

    public function updateUserRole($id, $role) {
        $sql = "UPDATE users SET role = :role WHERE id = :id";
        return $this->db->query($sql, ['role' => $role, 'id' => $id]);
    }

    public function deleteUser($id) {
        return $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
    }
}
