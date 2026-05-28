<?php
namespace App\Models;
use App\Core\Database;

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $sql = "SELECT id, username, password, role, email, two_factor_auth FROM users WHERE username = :username LIMIT 1";
        return $this->db->fetch($sql, ['username' => $username]);
    }

    public function findById($id) {
        $sql = "SELECT id, username, password, role, email, two_factor_auth FROM users WHERE id = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    public function updatePassword($id, $newPasswordHash) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        return $this->db->query($sql, ['password' => $newPasswordHash, 'id' => $id]);
    }

    public function updateTwoFactorAuth($id, $status) {
        $sql = "UPDATE users SET two_factor_auth = :status WHERE id = :id";
        return $this->db->query($sql, ['status' => $status, 'id' => $id]);
    }
}
