<?php
namespace App\Models;

use App\Core\Database;

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        return $this->db->fetchAll("SELECT t.*, u.username as nguoi_gui_ten 
                                   FROM thong_bao t 
                                   LEFT JOIN users u ON t.nguoi_gui_id = u.id 
                                   ORDER BY t.ngay_tao DESC");
    }

    public function getById($id) {
        return $this->db->fetch("SELECT * FROM thong_bao WHERE id = ?", [$id]);
    }

    public function createNotification($data, $adminId) {
        $sql = "INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui_id) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$data['tieu_de'], $data['noi_dung'], $data['loai'], $adminId]);
        $notificationId = $this->db->lastInsertId();

        $targetType = $data['target_type'];
        $targetValue = $data['target_value'] ?? '';

        $studentIds = [];

        if ($targetType === 'all') {
            $students = $this->db->fetchAll("SELECT id FROM sinh_vien");
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        } elseif ($targetType === 'sinh_vien') {
            $student = $this->db->fetch("SELECT id FROM sinh_vien WHERE ma_sv = ?", [$targetValue]);
            if ($student) { $studentIds[] = $student['id']; }
        } elseif ($targetType === 'khoa') {
            $students = $this->db->fetchAll("SELECT id FROM sinh_vien WHERE khoa = ?", [$targetValue]);
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        } elseif ($targetType === 'lop') {
            $students = $this->db->fetchAll("SELECT id FROM sinh_vien WHERE lop = ?", [$targetValue]);
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        } elseif ($targetType === 'canh_bao') {
            $students = $this->db->fetchAll("SELECT DISTINCT sinh_vien_id as id FROM diem_hoc_tap WHERE diem_tong < 4.0");
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        } elseif ($targetType === 'no_hoc_phi') {
            $students = $this->db->fetchAll("SELECT DISTINCT sinh_vien_id as id FROM hoc_phi WHERE trang_thai IN ('Chưa nộp', 'Nợ')");
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        } elseif ($targetType === 'ren_luyen') {
            $students = $this->db->fetchAll("
                SELECT DISTINCT sinh_vien_id as id 
                FROM diem_ren_luyen 
                WHERE id IN (SELECT MAX(id) FROM diem_ren_luyen GROUP BY sinh_vien_id) AND diem < 65
            ");
            foreach ($students as $s) { $studentIds[] = $s['id']; }
        }

        // Loại bỏ trùng lặp nếu có
        $studentIds = array_unique($studentIds);

        // Insert vào bảng trung gian
        if (!empty($studentIds)) {
            // Chia nhỏ mảng nếu quá lớn (ví dụ 500 records mỗi lần insert)
            $chunks = array_chunk($studentIds, 500);
            foreach ($chunks as $chunk) {
                $values = [];
                $params = [];
                foreach ($chunk as $sId) {
                    $values[] = "(?, ?)";
                    $params[] = $notificationId;
                    $params[] = $sId;
                }
                $sqlInsert = "INSERT INTO thong_bao_sinh_vien (thong_bao_id, sinh_vien_id) VALUES " . implode(", ", $values);
                $this->db->query($sqlInsert, $params);
            }
        }

        return $notificationId;
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM thong_bao WHERE id = ?", [$id]);
    }

    public function getFaculties() {
        return $this->db->fetchAll("SELECT DISTINCT khoa FROM sinh_vien WHERE khoa IS NOT NULL AND khoa != ''");
    }

    public function getClasses() {
        return $this->db->fetchAll("SELECT DISTINCT lop FROM sinh_vien WHERE lop IS NOT NULL AND lop != ''");
    }

    public function getWarningStudents() {
        $sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, sv.lop, sv.nganh, temp.so_mon_f,
                       (SELECT COUNT(*) FROM thong_bao_sinh_vien tbsv WHERE tbsv.sinh_vien_id = sv.id) as so_lan_gui
                FROM sinh_vien sv
                JOIN (
                    SELECT d.sinh_vien_id, COUNT(*) as so_mon_f
                    FROM (
                        SELECT sinh_vien_id, hoc_phan_id, MAX(diem_tong) as max_diem
                        FROM diem_hoc_tap
                        WHERE diem_tong IS NOT NULL
                        GROUP BY sinh_vien_id, hoc_phan_id
                        HAVING max_diem < 4.0
                    ) d
                    GROUP BY d.sinh_vien_id
                ) temp ON temp.sinh_vien_id = sv.id
                ORDER BY so_mon_f DESC";
        return $this->db->fetchAll($sql);
    }

    public function getTuitionWarningStudents() {
        $sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, sv.lop, sv.nganh, SUM(hp.so_tien - hp.da_nop) as tong_no,
                       (SELECT COUNT(*) FROM thong_bao_sinh_vien tbsv WHERE tbsv.sinh_vien_id = sv.id) as so_lan_gui
                FROM sinh_vien sv 
                JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id 
                WHERE hp.trang_thai IN ('Nợ', 'Chưa nộp') 
                GROUP BY sv.id, sv.ma_sv, sv.ho_ten, sv.lop, sv.nganh
                HAVING tong_no > 0
                ORDER BY tong_no DESC";
        return $this->db->fetchAll($sql);
    }

    public function getTrainingPointWarningStudents() {
        $sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, sv.lop, sv.nganh, drl.diem, drl.xep_loai, drl.nam_hoc, drl.hoc_ky,
                       (SELECT COUNT(*) FROM thong_bao_sinh_vien tbsv WHERE tbsv.sinh_vien_id = sv.id) as so_lan_gui
                FROM sinh_vien sv
                JOIN diem_ren_luyen drl ON drl.sinh_vien_id = sv.id
                WHERE drl.id IN (
                    SELECT MAX(id) FROM diem_ren_luyen GROUP BY sinh_vien_id
                ) AND drl.diem < 65
                ORDER BY drl.diem ASC";
        return $this->db->fetchAll($sql);
    }
}
