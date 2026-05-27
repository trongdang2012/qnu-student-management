<?php
namespace App\Models;

use App\Core\Database;

class AdminTuitionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getKhoaList() {
        return $this->db->fetchAll("SELECT DISTINCT khoa FROM sinh_vien WHERE COALESCE(khoa,'') <> '' ORDER BY khoa ASC");
    }

    public function getNganhList($khoa) {
        $sql = "SELECT DISTINCT nganh FROM sinh_vien WHERE COALESCE(nganh,'') <> ''";
        $params = [];
        if ($khoa !== '') {
            $sql .= " AND khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        $sql .= " ORDER BY nganh ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getLopList($khoa, $nganh) {
        $sql = "SELECT DISTINCT lop FROM sinh_vien WHERE COALESCE(lop,'') <> ''";
        $params = [];
        if ($khoa !== '') {
            $sql .= " AND khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $sql .= " AND nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        $sql .= " ORDER BY lop ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getTuitionSummaryByStudents($khoa, $nganh, $lop) {
        $sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop,
                   COALESCE(SUM(hp.so_tien), 0) AS total_fee,
                   COALESCE(SUM(hp.da_nop), 0) AS total_paid,
                   COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
                FROM sinh_vien sv
                LEFT JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
                WHERE 1 = 1";
        $params = [];
        if ($khoa !== '') {
            $sql .= ' AND sv.khoa = :khoa';
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $sql .= ' AND sv.nganh = :nganh';
            $params['nganh'] = $nganh;
        }
        if ($lop !== '') {
            $sql .= ' AND sv.lop = :lop';
            $params['lop'] = $lop;
        }
        $sql .= ' GROUP BY sv.id ORDER BY sv.khoa, sv.nganh, sv.lop, sv.ho_ten';
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotals() {
        return $this->db->fetch("SELECT COUNT(DISTINCT sv.id) AS students,
          COALESCE(SUM(hp.so_tien), 0) AS total_fee,
          COALESCE(SUM(hp.da_nop), 0) AS total_paid,
          COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
          FROM sinh_vien sv
          JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id");
    }

    public function getStatusCounts() {
        return $this->db->fetch("SELECT
          SUM(CASE WHEN hp.da_nop >= hp.so_tien AND hp.so_tien > 0 THEN 1 ELSE 0 END) AS paid_count,
          SUM(CASE WHEN hp.da_nop = 0 AND hp.so_tien > 0 THEN 1 ELSE 0 END) AS unpaid_count,
          SUM(CASE WHEN hp.da_nop > 0 AND hp.da_nop < hp.so_tien THEN 1 ELSE 0 END) AS owing_count
          FROM hoc_phi hp");
    }

    public function getByKhoa() {
        return $this->db->fetchAll("SELECT sv.khoa,
          COUNT(DISTINCT sv.id) AS students,
          COALESCE(SUM(hp.so_tien), 0) AS total_fee,
          COALESCE(SUM(hp.da_nop), 0) AS total_paid,
          COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
          FROM sinh_vien sv
          JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
          GROUP BY sv.khoa
          ORDER BY total_owed DESC, sv.khoa ASC");
    }

    public function getTuitionRecord($id) {
        return $this->db->fetch('SELECT hf.*, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop FROM hoc_phi hf JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id WHERE hf.id = :id LIMIT 1', ['id' => $id]);
    }

    public function getTuitionById($id) {
        return $this->db->fetch('SELECT da_nop FROM hoc_phi WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function updateTuition($id, $so_tien, $han_nop, $trang_thai) {
        return $this->db->query('UPDATE hoc_phi SET so_tien = :so_tien, han_nop = :han_nop, trang_thai = :trang_thai WHERE id = :id', [
            'so_tien' => $so_tien, 'han_nop' => $han_nop, 'trang_thai' => $trang_thai, 'id' => $id
        ]);
    }

    public function getAllFees() {
        return $this->db->fetchAll('SELECT hf.*, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop FROM hoc_phi hf JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id ORDER BY hf.nam_hoc DESC, hf.hoc_ky DESC, sv.khoa, sv.nganh, sv.lop');
    }

    public function confirmTuitionArray($ids) {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $mysqli = $this->db->getConnection();
        $stmt = $mysqli->prepare("UPDATE hoc_phi SET da_nop = so_tien, trang_thai = 'Đã nộp' WHERE id IN ($placeholders)");
        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function confirmTuitionSingle($id) {
        $this->db->query("UPDATE hoc_phi SET da_nop = so_tien, trang_thai = 'Đã nộp' WHERE id = :id", ['id' => $id]);
        $mysqli = $this->db->getConnection();
        return $mysqli->affected_rows;
    }

    public function getPendingFees() {
        return $this->db->fetchAll("SELECT hf.*, sv.ma_sv, sv.ho_ten, sv.khoa, sv.nganh, sv.lop FROM hoc_phi hf JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id WHERE hf.trang_thai IN ('Chưa nộp', 'Nợ') ORDER BY sv.khoa, sv.nganh, sv.lop, hf.nam_hoc DESC, hf.hoc_ky DESC");
    }
}
