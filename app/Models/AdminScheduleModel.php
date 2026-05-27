<?php
namespace App\Models;

use App\Core\Database;

class AdminScheduleModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getSchedules($hocKy, $namHoc, $search = '') {
        $sql = '
            SELECT t.*, sv.ma_sv, sv.ho_ten, hp.ma_hp, hp.ten_hp
            FROM thoi_khoa_bieu t
            JOIN sinh_vien sv ON sv.id = t.sinh_vien_id
            JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
            WHERE t.hoc_ky = :hk AND t.nam_hoc = :nh
        ';
        $params = ['hk' => $hocKy, 'nh' => $namHoc];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (sv.ma_sv LIKE :search1 OR sv.ho_ten LIKE :search2 OR hp.ma_hp LIKE :search3 OR hp.ten_hp LIKE :search4 OR t.phong_hoc LIKE :search5 OR t.giang_vien LIKE :search6)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
            $params['search4'] = $like;
            $params['search5'] = $like;
            $params['search6'] = $like;
        }

        $sql .= ' ORDER BY t.thu ASC, t.tiet_bat_dau ASC, sv.ho_ten ASC';
        return $this->db->fetchAll($sql, $params);
    }

    public function getAllStudents() {
        return $this->db->fetchAll('SELECT id, ma_sv, ho_ten, lop FROM sinh_vien ORDER BY lop, ho_ten');
    }

    public function getAllCourses() {
        return $this->db->fetchAll('SELECT id, ma_hp, ten_hp, so_tin_chi FROM hoc_phan ORDER BY hoc_ky, ma_hp');
    }

    public function getDistinctYears() {
        return $this->db->fetchAll("SELECT DISTINCT nam_hoc FROM thoi_khoa_bieu ORDER BY nam_hoc DESC LIMIT 8");
    }

    public function getScheduleById($id) {
        return $this->db->fetch('SELECT * FROM thoi_khoa_bieu WHERE id = :id', ['id' => $id]);
    }

    public function getStudentConflict($excludeId, $sinhVienId, $thu, $tietBatDau, $tietKetThuc, $hocKy, $namHoc) {
        $sql = '
            SELECT hp.ma_hp, hp.ten_hp, t.tiet_bat_dau, t.so_tiet
            FROM thoi_khoa_bieu t
            JOIN hoc_phan hp ON hp.id = t.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.sinh_vien_id = :svId
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.thu = :thu
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        return $this->db->fetch($sql, [
            'excludeId' => $excludeId, 'svId' => $sinhVienId, 'hk' => $hocKy, 'nh' => $namHoc,
            'thu' => $thu, 'tk' => $tietKetThuc, 'tb' => $tietBatDau
        ]);
    }

    public function getRoomConflict($excludeId, $phongHoc, $thu, $tietBatDau, $tietKetThuc, $hocKy, $namHoc) {
        $sql = '
            SELECT sv.ma_sv, sv.ho_ten, t.tiet_bat_dau, t.so_tiet
            FROM thoi_khoa_bieu t
            JOIN sinh_vien sv ON sv.id = t.sinh_vien_id
            WHERE t.id <> :excludeId
              AND t.phong_hoc = :phong
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.thu = :thu
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        return $this->db->fetch($sql, [
            'excludeId' => $excludeId, 'phong' => $phongHoc, 'hk' => $hocKy, 'nh' => $namHoc,
            'thu' => $thu, 'tk' => $tietKetThuc, 'tb' => $tietBatDau
        ]);
    }

    public function updateSchedule($id, $data) {
        $sql = '
            UPDATE thoi_khoa_bieu
            SET sinh_vien_id = :sv_id, hoc_phan_id = :hp_id, thu = :thu, tiet_bat_dau = :tiet_bd, so_tiet = :so_tiet,
                phong_hoc = :phong, giang_vien = :gv, hoc_ky = :hk, nam_hoc = :nh
            WHERE id = :id
        ';
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }

    public function insertSchedule($data) {
        $sql = '
            INSERT INTO thoi_khoa_bieu
                (sinh_vien_id, hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc)
            VALUES (:sv_id, :hp_id, :thu, :tiet_bd, :so_tiet, :phong, :gv, :hk, :nh)
        ';
        return $this->db->query($sql, $data);
    }

    public function deleteSchedule($id) {
        return $this->db->query('DELETE FROM thoi_khoa_bieu WHERE id = :id', ['id' => $id]);
    }

    public function getCourseGroupsForOptimization($hocKy, $namHoc) {
        $sql = "
            SELECT
                hp.id AS hoc_phan_id,
                hp.ma_hp,
                hp.ten_hp,
                hp.so_tin_chi,
                GROUP_CONCAT(dk.sinh_vien_id ORDER BY dk.sinh_vien_id) AS student_ids,
                COUNT(*) AS total_students
            FROM dang_ky_hp dk
            JOIN hoc_phan hp ON hp.id = dk.hoc_phan_id
            WHERE dk.hoc_ky = :hk
              AND dk.nam_hoc = :nh
              AND dk.trang_thai = 'Đã duyệt'
            GROUP BY hp.id, hp.ma_hp, hp.ten_hp, hp.so_tin_chi
            ORDER BY total_students DESC, hp.so_tin_chi DESC, hp.ma_hp ASC
        ";
        return $this->db->fetchAll($sql, ['hk' => $hocKy, 'nh' => $namHoc]);
    }

    public function countExistingSchedules($hocKy, $namHoc) {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM thoi_khoa_bieu WHERE hoc_ky = :hk AND nam_hoc = :nh', ['hk' => $hocKy, 'nh' => $namHoc]);
        return (int)$row['total'];
    }

    public function countRegisteredStudents($hocKy, $namHoc) {
        $sql = "
            SELECT COUNT(DISTINCT sinh_vien_id) AS total
            FROM dang_ky_hp
            WHERE hoc_ky = :hk AND nam_hoc = :nh AND trang_thai = 'Đã duyệt'
        ";
        $row = $this->db->fetch($sql, ['hk' => $hocKy, 'nh' => $namHoc]);
        return (int)$row['total'];
    }

    public function beginTransaction() {
        $this->db->query("START TRANSACTION");
    }

    public function commit() {
        $this->db->query("COMMIT");
    }

    public function rollback() {
        $this->db->query("ROLLBACK");
    }

    public function deleteAllSchedulesBySemester($hocKy, $namHoc) {
        return $this->db->query('DELETE FROM thoi_khoa_bieu WHERE hoc_ky = :hk AND nam_hoc = :nh', ['hk' => $hocKy, 'nh' => $namHoc]);
    }

    public function getSchedulesToOptimize($hocKy, $namHoc) {
        return $this->db->fetchAll('
            SELECT sinh_vien_id, thu, tiet_bat_dau, so_tiet, phong_hoc
            FROM thoi_khoa_bieu
            WHERE hoc_ky = :hk AND nam_hoc = :nh
        ', ['hk' => $hocKy, 'nh' => $namHoc]);
    }

    public function checkExistingPair($svId, $hpId, $hocKy, $namHoc) {
        return $this->db->fetch('
            SELECT 1
            FROM thoi_khoa_bieu
            WHERE sinh_vien_id = :sv AND hoc_phan_id = :hp AND hoc_ky = :hk AND nam_hoc = :nh
            LIMIT 1
        ', ['sv' => $svId, 'hp' => $hpId, 'hk' => $hocKy, 'nh' => $namHoc]);
    }
}
