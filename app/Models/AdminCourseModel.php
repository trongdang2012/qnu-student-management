<?php
namespace App\Models;

use App\Core\Database;

class AdminCourseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getCourses($search = '', $hoc_ky = 0, $loai = '') {
        $sql = 'SELECT * FROM hoc_phan WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (ma_hp LIKE :search1 OR ten_hp LIKE :search2 OR nien_khoa LIKE :search3)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= ' AND hoc_ky = :hoc_ky';
            $params['hoc_ky'] = $hoc_ky;
        }

        if (in_array($loai, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
            $sql .= ' AND loai = :loai';
            $params['loai'] = $loai;
        }

        $sql .= ' ORDER BY hoc_ky ASC, ma_hp ASC';
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getCourseById($id) {
        return $this->db->fetch('SELECT * FROM hoc_phan WHERE id = :id', ['id' => $id]);
    }

    public function getCourseByCodeExceptId($ma_hp, $id) {
        return $this->db->fetch('SELECT id FROM hoc_phan WHERE ma_hp = :ma_hp AND id <> :id LIMIT 1', ['ma_hp' => $ma_hp, 'id' => $id]);
    }

    public function getCourseByCode($ma_hp) {
        return $this->db->fetch('SELECT id FROM hoc_phan WHERE ma_hp = :ma_hp LIMIT 1', ['ma_hp' => $ma_hp]);
    }

    public function addCourse($data) {
        $sql = 'INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa) VALUES (:ma_hp, :ten_hp, :so_tin_chi, :loai, :hoc_ky, :nien_khoa)';
        return $this->db->query($sql, $data);
    }

    public function updateCourse($id, $data) {
        $data['id'] = $id;
        $sql = 'UPDATE hoc_phan SET ma_hp = :ma_hp, ten_hp = :ten_hp, so_tin_chi = :so_tin_chi, loai = :loai, hoc_ky = :hoc_ky, nien_khoa = :nien_khoa WHERE id = :id';
        return $this->db->query($sql, $data);
    }

    public function deleteCourse($id) {
        try {
            return $this->db->query('DELETE FROM hoc_phan WHERE id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
