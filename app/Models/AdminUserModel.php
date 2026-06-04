<?php
namespace App\Models;

use App\Core\Database;

class AdminUserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function countUsers($search = '', $khoa = '', $nganh = '', $lop = '') {
        $sql = "SELECT COUNT(u.id) as total FROM users u";
        $params = [];
        $where = "1=1";
        
        if (!empty($khoa) || !empty($nganh) || !empty($lop)) {
            $sql .= " LEFT JOIN sinh_vien sv ON sv.user_id = u.id
                      LEFT JOIN lop_sinh_hoat l ON l.id = sv.lop_sinh_hoat_id
                      LEFT JOIN nganh n ON n.id = l.nganh_id
                      LEFT JOIN khoa k ON k.id = n.khoa_id";
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

    public function getUsers($offset, $limit, $search = '', $khoa = '', $nganh = '', $lop = '', $sort_by = 'created_at', $sort_dir = 'desc') {
        $sql = "SELECT u.* FROM users u";
        $params = [];
        $where = "1=1";
        
        if (!empty($khoa) || !empty($nganh) || !empty($lop)) {
            $sql .= " LEFT JOIN sinh_vien sv ON sv.user_id = u.id
                      LEFT JOIN lop_sinh_hoat l ON l.id = sv.lop_sinh_hoat_id
                      LEFT JOIN nganh n ON n.id = l.nganh_id
                      LEFT JOIN khoa k ON k.id = n.khoa_id";
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
        $sql = "SELECT k.ten_khoa as khoa, n.ten_nganh as nganh, l.ten_lop as lop 
                FROM khoa k
                LEFT JOIN nganh n ON n.khoa_id = k.id 
                LEFT JOIN lop_sinh_hoat l ON l.nganh_id = n.id 
                ORDER BY k.ten_khoa, n.ten_nganh, l.ten_lop";
        $rows = $this->db->fetchAll($sql);
        
        $tree = [];
        foreach ($rows as $row) {
            $k = $row['khoa'];
            $n = $row['nganh'];
            $l = $row['lop'];
            
            if ($k) {
                if (!isset($tree[$k])) {
                    $tree[$k] = [];
                }
                if ($n) {
                    if (!isset($tree[$k][$n])) {
                        $tree[$k][$n] = [];
                    }
                    if ($l) {
                        $tree[$k][$n][] = $l;
                    }
                }
            }
        }
        return $tree;
    }

    public function getUserById($id) {
        return $this->db->fetch('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    public function getUserByUsername($username) {
        return $this->db->fetch('SELECT id FROM users WHERE username = :un', ['un' => $username]);
    }

    public function insertUser($username, $hashed_password, $role, $email) {
        $sql = "INSERT INTO users (username, password, role, email) VALUES (:un, :pw, :role, :email)";
        return $this->db->query($sql, ['un' => $username, 'pw' => $hashed_password, 'role' => $role, 'email' => $email]);
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
        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();
        try {
            // 1. Kiểm tra xem user này có sinh viên liên kết không
            $sv = $this->db->fetch("SELECT id FROM sinh_vien WHERE user_id = :id LIMIT 1", ['id' => $id]);
            if ($sv) {
                $sid = $sv['id'];
                
                // 1.1 Xóa file vật lý của tài liệu chia sẻ trước khi xóa trong database
                $docs = $this->db->fetchAll("SELECT duong_dan FROM tai_lieu WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                foreach ($docs as $doc) {
                    if (!empty($doc['duong_dan'])) {
                        $file_path = (defined('UPLOAD_DIR') ? UPLOAD_DIR : (defined('ROOT') ? ROOT : dirname(dirname(__DIR__))) . '/uploads/') . $doc['duong_dan'];
                        if (file_exists($file_path)) {
                            @unlink($file_path);
                        }
                    }
                }
                
                // 1.2 Xóa các bảng phụ liên quan đến sinh viên
                $this->db->query("DELETE FROM diem_hoc_tap WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM dang_ky_hp WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM thoi_khoa_bieu WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM hoc_phi WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM diem_ren_luyen WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM thong_bao_sinh_vien WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                $this->db->query("DELETE FROM tai_lieu WHERE sinh_vien_id = :sid", ['sid' => $sid]);
                
                // 1.3 Xóa bản ghi trong bảng sinh_vien
                $this->db->query("DELETE FROM sinh_vien WHERE id = :sid", ['sid' => $sid]);
            }
            
            // 2. Xóa bản ghi trong bảng users
            $res = $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
            
            $pdo->commit();
            return $res;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
