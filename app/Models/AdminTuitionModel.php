<?php
namespace App\Models;

use App\Core\Database;

class AdminTuitionModel {
    private $db;
    private $hocPhanIdColumnExists = null;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureHocPhanIdColumn();
    }

    private function hasHocPhanIdColumn() {
        if ($this->hocPhanIdColumnExists !== null) {
            return $this->hocPhanIdColumnExists;
        }

        $row = $this->db->fetch("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hoc_phi' AND COLUMN_NAME = 'hoc_phan_id'");
        $this->hocPhanIdColumnExists = ((int)($row['cnt'] ?? 0) > 0);
        return $this->hocPhanIdColumnExists;
    }

    private function ensureHocPhanIdColumn() {
        if ($this->hasHocPhanIdColumn()) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE hoc_phi ADD COLUMN hoc_phan_id INT DEFAULT NULL');
            $this->hocPhanIdColumnExists = true;
        } catch (\Throwable $e) {
            // Nếu không thể thêm cột tự động thì để trạng thái false và tiếp tục chạy với fallback không join
            $this->hocPhanIdColumnExists = false;
        }
    }

    public function getKhoaList() {
        return $this->db->fetchAll("SELECT ten_khoa as khoa FROM khoa ORDER BY ten_khoa ASC");
    }

    public function getNganhList($khoa) {
        $sql = "SELECT DISTINCT n.ten_nganh as nganh FROM nganh n JOIN khoa k ON n.khoa_id = k.id";
        $params = [];
        if ($khoa !== '') {
            $sql .= " WHERE k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        $sql .= " ORDER BY n.ten_nganh ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getLopList($khoa, $nganh) {
        $sql = "SELECT DISTINCT l.ten_lop as lop FROM lop_sinh_hoat l JOIN nganh n ON l.nganh_id = n.id JOIN khoa k ON n.khoa_id = k.id";
        $params = [];
        $wheres = [];
        if ($khoa !== '') {
            $wheres[] = "k.ten_khoa = :khoa";
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $wheres[] = "n.ten_nganh = :nganh";
            $params['nganh'] = $nganh;
        }
        if (!empty($wheres)) {
            $sql .= " WHERE " . implode(" AND ", $wheres);
        }
        $sql .= " ORDER BY l.ten_lop ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getCourseList() {
        $sql = "SELECT hp.* FROM hoc_phan hp ORDER BY hp.hoc_ky, hp.ten_hp";
        return $this->db->fetchAll($sql);
    }

    public function getFilteredTuitionRecords($khoa, $nganh, $lop) {
        $sql = "SELECT hf.*, sv.ma_sv, sv.ho_ten, k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop";
        if ($this->hasHocPhanIdColumn()) {
            $sql .= ", hp.ma_hp, hp.ten_hp, hp.so_tin_chi";
        }
        $sql .= " FROM hoc_phi hf
                JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id
                LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
                LEFT JOIN nganh n ON l.nganh_id = n.id
                LEFT JOIN khoa k ON n.khoa_id = k.id";
        if ($this->hasHocPhanIdColumn()) {
            $sql .= " LEFT JOIN hoc_phan hp ON hp.id = hf.hoc_phan_id";
        }
        $sql .= " WHERE 1 = 1";

        $params = [];
        if ($khoa !== '') {
            $sql .= ' AND k.ten_khoa = :khoa';
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $sql .= ' AND n.ten_nganh = :nganh';
            $params['nganh'] = $nganh;
        }
        if ($lop !== '') {
            $sql .= ' AND l.ten_lop = :lop';
            $params['lop'] = $lop;
        }
        $sql .= ' ORDER BY hf.nam_hoc DESC, hf.hoc_ky DESC, k.ten_khoa, n.ten_nganh, l.ten_lop, sv.ho_ten';
        return $this->db->fetchAll($sql, $params);
    }

    public function applyCourseTuitionRate($courseId, $hocKy, $namHoc, $pricePerCredit, $han_nop) {
        $course = $this->db->fetch('SELECT * FROM hoc_phan WHERE id = :id LIMIT 1', ['id' => $courseId]);
        if (!$course || $pricePerCredit <= 0 || $hocKy <= 0 || $namHoc === '') {
            return false;
        }

        $feeAmount = (float)$course['so_tin_chi'] * (float)$pricePerCredit;
        $status = 'Chưa nộp';
        $studentsSql = "SELECT DISTINCT sv.id AS sinh_vien_id, dk.hoc_ky, dk.nam_hoc
                        FROM dang_ky_hp dk
                        JOIN sinh_vien sv ON sv.id = dk.sinh_vien_id
                        WHERE dk.hoc_phan_id = :hp
                          AND dk.trang_thai = 'Đã duyệt'
                          AND dk.hoc_ky = :hk
                          AND dk.nam_hoc = :nh";
        $params = ['hp' => $courseId, 'hk' => $hocKy, 'nh' => $namHoc];

        $students = $this->db->fetchAll($studentsSql, $params);
        if (empty($students)) {
            return false;
        }

        foreach ($students as $student) {
            $studentId = (int)$student['sinh_vien_id'];
            $hocKy = $student['hoc_ky'];
            $namHoc = $student['nam_hoc'];
            $existing = $this->db->fetch('SELECT id, da_nop FROM hoc_phi WHERE sinh_vien_id = :sid AND hoc_ky = :hk AND nam_hoc = :nh AND hoc_phan_id = :hp LIMIT 1', [
                'sid' => $studentId,
                'hk' => $hocKy,
                'nh' => $namHoc,
                'hp' => $courseId
            ]);

            $status = 'Chưa nộp';
            $paidAmount = $existing ? (float)$existing['da_nop'] : 0;
            if ($paidAmount >= $feeAmount && $feeAmount > 0) {
                $status = 'Đã nộp';
            } elseif ($paidAmount > 0) {
                $status = 'Nợ';
            }

            if ($existing) {
                $this->db->query('UPDATE hoc_phi SET so_tien = :so_tien, han_nop = :han_nop, trang_thai = :trang_thai WHERE id = :id', [
                    'so_tien' => $feeAmount,
                    'han_nop' => $han_nop,
                    'trang_thai' => $status,
                    'id' => $existing['id']
                ]);
            } else {
                $this->db->query('INSERT INTO hoc_phi (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, so_tien, da_nop, han_nop, trang_thai) VALUES (:sid, :hp, :hk, :nh, :so_tien, 0, :han_nop, :trang_thai)', [
                    'sid' => $studentId,
                    'hp' => $courseId,
                    'hk' => $hocKy,
                    'nh' => $namHoc,
                    'so_tien' => $feeAmount,
                    'han_nop' => $han_nop,
                    'trang_thai' => $status
                ]);
            }
        }

        return true;
    }

    public function getTuitionSummaryByStudents($khoa, $nganh, $lop) {
        $sql = "SELECT sv.id, sv.ma_sv, sv.ho_ten, k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop,
                   COALESCE(SUM(hp.so_tien), 0) AS total_fee,
                   COALESCE(SUM(hp.da_nop), 0) AS total_paid,
                   COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
                FROM sinh_vien sv
                LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
                LEFT JOIN nganh n ON l.nganh_id = n.id
                LEFT JOIN khoa k ON n.khoa_id = k.id
                LEFT JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
                WHERE 1 = 1";
        $params = [];
        if ($khoa !== '') {
            $sql .= ' AND k.ten_khoa = :khoa';
            $params['khoa'] = $khoa;
        }
        if ($nganh !== '') {
            $sql .= ' AND n.ten_nganh = :nganh';
            $params['nganh'] = $nganh;
        }
        if ($lop !== '') {
            $sql .= ' AND l.ten_lop = :lop';
            $params['lop'] = $lop;
        }
        $sql .= ' GROUP BY sv.id, sv.ma_sv, sv.ho_ten, k.ten_khoa, n.ten_nganh, l.ten_lop ORDER BY k.ten_khoa, n.ten_nganh, l.ten_lop, sv.ho_ten';
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
        return $this->db->fetchAll("SELECT k.ten_khoa AS khoa,
          COUNT(DISTINCT sv.id) AS students,
          COALESCE(SUM(hp.so_tien), 0) AS total_fee,
          COALESCE(SUM(hp.da_nop), 0) AS total_paid,
          COALESCE(SUM(hp.so_tien - hp.da_nop), 0) AS total_owed
          FROM sinh_vien sv
          LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
          LEFT JOIN nganh n ON l.nganh_id = n.id
          LEFT JOIN khoa k ON n.khoa_id = k.id
          JOIN hoc_phi hp ON hp.sinh_vien_id = sv.id
          GROUP BY k.ten_khoa
          ORDER BY total_owed DESC, k.ten_khoa ASC");
    }

    public function getTuitionRecord($id) {
        $sql = 'SELECT hf.*, sv.ma_sv, sv.ho_ten, k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop';
        if ($this->hasHocPhanIdColumn()) {
            $sql .= ', hp.ma_hp, hp.ten_hp, hp.so_tin_chi';
        }
        $sql .= ' FROM hoc_phi hf 
                  JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id
                  LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
                  LEFT JOIN nganh n ON l.nganh_id = n.id
                  LEFT JOIN khoa k ON n.khoa_id = k.id';
        if ($this->hasHocPhanIdColumn()) {
            $sql .= ' LEFT JOIN hoc_phan hp ON hp.id = hf.hoc_phan_id';
        }
        $sql .= ' WHERE hf.id = :id LIMIT 1';

        return $this->db->fetch($sql, ['id' => $id]);
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
        $sql = 'SELECT hf.*, sv.ma_sv, sv.ho_ten, k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop';
        if ($this->hasHocPhanIdColumn()) {
            $sql .= ', hp.ma_hp, hp.ten_hp, hp.so_tin_chi';
        }
        $sql .= ' FROM hoc_phi hf 
                  JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id
                  LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
                  LEFT JOIN nganh n ON l.nganh_id = n.id
                  LEFT JOIN khoa k ON n.khoa_id = k.id';
        if ($this->hasHocPhanIdColumn()) {
            $sql .= ' LEFT JOIN hoc_phan hp ON hp.id = hf.hoc_phan_id';
        }
        $sql .= ' ORDER BY hf.nam_hoc DESC, hf.hoc_ky DESC, k.ten_khoa, n.ten_nganh, l.ten_lop';
        return $this->db->fetchAll($sql);
    }

    private function getDefaultAdminId() {
        $admin = $this->db->fetch('SELECT id FROM users WHERE role = ? ORDER BY id ASC LIMIT 1', ['admin']);
        return $admin['id'] ?? null;
    }

    private function sendTuitionConfirmationNotification(array $record) {
        $adminId = $this->getDefaultAdminId();
        $title = 'Xác nhận nộp học phí';
        $content = sprintf(
            'Hệ thống đã xác nhận học phí HK %d năm học %s của bạn đã được thanh toán đầy đủ (%s đ).',
            (int)$record['hoc_ky'],
            $record['nam_hoc'],
            number_format((float)$record['so_tien'], 0, ',', '.')
        );

        $this->db->query(
            'INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui_id) VALUES (:title, :content, :type, :admin_id)',
            [
                'title' => $title,
                'content' => $content,
                'type' => 'success',
                'admin_id' => $adminId,
            ]
        );

        $notificationId = $this->db->lastInsertId();
        if ($notificationId) {
            $this->db->query(
                'INSERT INTO thong_bao_sinh_vien (thong_bao_id, sinh_vien_id) VALUES (:notification_id, :student_id)',
                ['notification_id' => $notificationId, 'student_id' => $record['sinh_vien_id']]
            );
        }
    }

    public function confirmTuitionArray($ids) {
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tuitionRecords = $this->db->fetchAll(
            "SELECT hf.id, hf.sinh_vien_id, hf.hoc_ky, hf.nam_hoc, hf.so_tien FROM hoc_phi hf WHERE hf.id IN ($placeholders)",
            $ids
        );

        $stmt = $this->db->query(
            "UPDATE hoc_phi SET da_nop = so_tien, trang_thai = 'Đã nộp' WHERE id IN ($placeholders)",
            $ids
        );

        foreach ($tuitionRecords as $record) {
            $this->sendTuitionConfirmationNotification($record);
        }

        return $stmt->rowCount();
    }

    public function confirmTuitionSingle($id) {
        $record = $this->db->fetch(
            'SELECT hf.id, hf.sinh_vien_id, hf.hoc_ky, hf.nam_hoc, hf.so_tien FROM hoc_phi hf WHERE hf.id = :id LIMIT 1',
            ['id' => $id]
        );
        if (!$record) {
            return 0;
        }

        $stmt = $this->db->query(
            "UPDATE hoc_phi SET da_nop = so_tien, trang_thai = 'Đã nộp' WHERE id = :id",
            ['id' => $id]
        );

        if ($stmt->rowCount() > 0) {
            $this->sendTuitionConfirmationNotification($record);
        }

        return $stmt->rowCount();
    }

    public function getPendingFees(string $maSv = '') {
        $sql = "SELECT hf.*, sv.ma_sv, sv.ho_ten, k.ten_khoa AS khoa, n.ten_nganh AS nganh, l.ten_lop AS lop
                FROM hoc_phi hf
                JOIN sinh_vien sv ON sv.id = hf.sinh_vien_id
                LEFT JOIN lop_sinh_hoat l ON sv.lop_sinh_hoat_id = l.id
                LEFT JOIN nganh n ON l.nganh_id = n.id
                LEFT JOIN khoa k ON n.khoa_id = k.id
                WHERE hf.trang_thai IN ('Chưa nộp', 'Nợ')";

        $params = [];
        if ($maSv !== '') {
            $sql .= ' AND sv.ma_sv = :ma_sv';
            $params['ma_sv'] = $maSv;
        }

        $sql .= ' ORDER BY k.ten_khoa, n.ten_nganh, l.ten_lop, hf.nam_hoc DESC, hf.hoc_ky DESC';
        return $this->db->fetchAll($sql, $params);
    }

    public function autoCalculateTuition($hocKy, $namHoc, $donGia, $hanNop) {
        $sql = "SELECT dk.sinh_vien_id, lhp.hoc_phan_id, hp.so_tin_chi
                FROM dang_ky_hp dk
                JOIN lop_hoc_phan lhp ON dk.lop_hoc_phan_id = lhp.id
                JOIN hoc_phan hp ON lhp.hoc_phan_id = hp.id
                WHERE dk.trang_thai = 'Đã duyệt'
                  AND dk.hoc_ky = :hk
                  AND dk.nam_hoc = :nh";
        $registrations = $this->db->fetchAll($sql, ['hk' => $hocKy, 'nh' => $namHoc]);
        
        if (empty($registrations)) {
            return 0;
        }

        $successCount = 0;
        foreach ($registrations as $reg) {
            $studentId = (int)$reg['sinh_vien_id'];
            $courseId = (int)$reg['hoc_phan_id'];
            $soTinChi = (int)$reg['so_tin_chi'];
            $feeAmount = (float)$soTinChi * (float)$donGia;

            $existing = $this->db->fetch(
                'SELECT id, da_nop FROM hoc_phi WHERE sinh_vien_id = :sid AND hoc_ky = :hk AND nam_hoc = :nh AND hoc_phan_id = :hp LIMIT 1',
                ['sid' => $studentId, 'hk' => $hocKy, 'nh' => $namHoc, 'hp' => $courseId]
            );

            $status = 'Chưa nộp';
            $paidAmount = $existing ? (float)$existing['da_nop'] : 0;
            if ($paidAmount >= $feeAmount && $feeAmount > 0) {
                $status = 'Đã nộp';
            } elseif ($paidAmount > 0) {
                $status = 'Nợ';
            }

            if ($existing) {
                $this->db->query(
                    'UPDATE hoc_phi SET so_tien = :so_tien, han_nop = :han_nop, trang_thai = :trang_thai WHERE id = :id',
                    [
                        'so_tien' => $feeAmount,
                        'han_nop' => $han_nop,
                        'trang_thai' => $status,
                        'id' => $existing['id']
                    ]
                );
            } else {
                $this->db->query(
                    'INSERT INTO hoc_phi (sinh_vien_id, hoc_phan_id, hoc_ky, nam_hoc, so_tien, da_nop, han_nop, trang_thai) VALUES (:sid, :hp, :hk, :nh, :so_tien, 0, :han_nop, :trang_thai)',
                    [
                        'sid' => $studentId,
                        'hp' => $courseId,
                        'hk' => $hocKy,
                        'nh' => $namHoc,
                        'so_tien' => $feeAmount,
                        'han_nop' => $han_nop,
                        'trang_thai' => $status
                    ]
                );
            }
            $successCount++;
        }

        return $successCount;
    }
}
