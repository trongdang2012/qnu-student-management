<?php
namespace App\Models;

use App\Core\Database;

class AdminScheduleModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getSchedules($hocKy, $namHoc, $search = '', $phongFilter = '', $gvFilter = '', $lhpFilter = '') {
        $sql = '
            SELECT t.*, l.ma_lop_hp, hp.ma_hp, hp.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            WHERE t.hoc_ky = :hk AND t.nam_hoc = :nh
        ';
        $params = ['hk' => $hocKy, 'nh' => $namHoc];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (l.ma_lop_hp LIKE :search1 OR hp.ma_hp LIKE :search2 OR hp.ten_hp LIKE :search3 OR t.phong_hoc LIKE :search4 OR t.giang_vien LIKE :search5)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
            $params['search4'] = $like;
            $params['search5'] = $like;
        }

        if ($phongFilter !== '') {
            $sql .= ' AND t.phong_hoc = :phongFilter';
            $params['phongFilter'] = $phongFilter;
        }

        if ($gvFilter !== '') {
            $sql .= ' AND t.giang_vien = :gvFilter';
            $params['gvFilter'] = $gvFilter;
        }

        if ($lhpFilter !== '') {
            $sql .= ' AND t.lop_hoc_phan_id = :lhpFilter';
            $params['lhpFilter'] = $lhpFilter;
        }

        $sql .= ' ORDER BY t.thu ASC, t.tiet_bat_dau ASC, l.ma_lop_hp ASC';
        return $this->db->fetchAll($sql, $params);
    }

    public function getDistinctYears() {
        return $this->db->fetchAll("SELECT DISTINCT nam_hoc FROM lop_hoc_phan ORDER BY nam_hoc DESC LIMIT 8");
    }

    public function getScheduleById($id) {
        return $this->db->fetch('
            SELECT t.*, l.ma_lop_hp, hp.ten_hp, hp.ma_hp 
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
            WHERE t.id = :id
        ', ['id' => $id]);
    }

    public function getAllClasses($hk, $nh) {
        return $this->db->fetchAll('
            SELECT l.id, l.ma_lop_hp, hp.ten_hp 
            FROM lop_hoc_phan l
            JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
            WHERE l.hoc_ky = :hk AND l.nam_hoc = :nh
            ORDER BY l.ma_lop_hp
        ', ['hk' => $hk, 'nh' => $nh]);
    }

    public function checkConflict($excludeId, $phong, $giangVien, $lopHpId, $thu, $tietBd, $soTiet, $hk, $nh) {
        $tietKt = $tietBd + $soTiet - 1;

        // 1. Kiểm tra trùng phòng học
        $sqlRoom = '
            SELECT t.*, l.ma_lop_hp, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.phong_hoc = :phong
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictRoom = $this->db->fetch($sqlRoom, [
            'excludeId' => $excludeId, 'phong' => $phong, 'thu' => $thu, 
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictRoom) {
            return "Trùng phòng học {$phong} với lớp {$conflictRoom['ma_lop_hp']} ({$conflictRoom['ten_hp']}) tại tiết {$conflictRoom['tiet_bat_dau']}-" . ($conflictRoom['tiet_bat_dau'] + $conflictRoom['so_tiet'] - 1);
        }

        // 2. Kiểm tra trùng giảng viên
        $sqlGv = '
            SELECT t.*, l.ma_lop_hp, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.giang_vien = :gv
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictGv = $this->db->fetch($sqlGv, [
            'excludeId' => $excludeId, 'gv' => $giangVien, 'thu' => $thu, 
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictGv) {
            return "Giảng viên {$giangVien} bị trùng lịch dạy lớp {$conflictGv['ma_lop_hp']} ({$conflictGv['ten_hp']}) tại tiết {$conflictGv['tiet_bat_dau']}-" . ($conflictGv['tiet_bat_dau'] + $conflictGv['so_tiet'] - 1);
        }

        // 3. Kiểm tra trùng lịch học của chính lớp học phần này
        $sqlClass = '
            SELECT t.*, h.ten_hp
            FROM thoi_khoa_bieu t
            JOIN lop_hoc_phan l ON l.id = t.lop_hoc_phan_id
            JOIN hoc_phan h ON h.id = l.hoc_phan_id
            WHERE t.id <> :excludeId
              AND t.lop_hoc_phan_id = :lopHpId
              AND t.thu = :thu
              AND t.hoc_ky = :hk
              AND t.nam_hoc = :nh
              AND t.tiet_bat_dau <= :tk
              AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tb
            LIMIT 1
        ';
        $conflictClass = $this->db->fetch($sqlClass, [
            'excludeId' => $excludeId, 'lopHpId' => $lopHpId, 'thu' => $thu, 
            'hk' => $hk, 'nh' => $nh, 'tk' => $tietKt, 'tb' => $tietBd
        ]);
        if ($conflictClass) {
            return "Lớp học phần này đã có lịch học vào Thứ {$thu}, tiết {$conflictClass['tiet_bat_dau']}-" . ($conflictClass['tiet_bat_dau'] + $conflictClass['so_tiet'] - 1);
        }

        return false;
    }

    public function insertSchedule($data) {
        $sql = '
            INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc)
            VALUES (:lop_hoc_phan_id, :thu, :tiet_bd, :so_tiet, :phong, :gv, :hk, :nh, :ngay_bat_dau, :ngay_ket_thuc)
        ';
        return $this->db->query($sql, $data);
    }

    public function updateSchedule($id, $data) {
        $sql = '
            UPDATE thoi_khoa_bieu
            SET lop_hoc_phan_id = :lop_hoc_phan_id, thu = :thu, tiet_bat_dau = :tiet_bd, so_tiet = :so_tiet,
                phong_hoc = :phong, giang_vien = :gv, hoc_ky = :hk, nam_hoc = :nh, ngay_bat_dau = :ngay_bat_dau, ngay_ket_thuc = :ngay_ket_thuc
            WHERE id = :id
        ';
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }

    public function deleteSchedule($id) {
        return $this->db->query('DELETE FROM thoi_khoa_bieu WHERE id = :id', ['id' => $id]);
    }

    public function optimizeSchedules($hk, $nh) {
        $classes = $this->db->fetchAll('
            SELECT l.*, h.so_tin_chi, h.ten_hp, h.ma_hp
            FROM lop_hoc_phan l
            JOIN hoc_phan h ON l.hoc_phan_id = h.id
            WHERE l.hoc_ky = :hk AND l.nam_hoc = :nh
        ', ['hk' => $hk, 'nh' => $nh]);

        if (empty($classes)) {
            return ['status' => 'warning', 'message' => 'Không có lớp học phần nào được tìm thấy trong học kỳ này để xếp lịch.'];
        }

        $this->db->query('DELETE FROM thoi_khoa_bieu WHERE hoc_ky = :hk AND nam_hoc = :nh', ['hk' => $hk, 'nh' => $nh]);

        $phongs = ['A101', 'A102', 'A201', 'A301', 'B101', 'B201', 'B202', 'B302', 'B303', 'B304', 'B305', 'Lab IT', 'Lab Điện'];
        
        $slots = [
            ['thu' => 2, 'tiet' => 1], ['thu' => 2, 'tiet' => 5],
            ['thu' => 3, 'tiet' => 1], ['thu' => 3, 'tiet' => 5],
            ['thu' => 4, 'tiet' => 1], ['thu' => 4, 'tiet' => 5],
            ['thu' => 5, 'tiet' => 1], ['thu' => 5, 'tiet' => 5],
            ['thu' => 6, 'tiet' => 1], ['thu' => 6, 'tiet' => 5],
            ['thu' => 7, 'tiet' => 1], ['thu' => 7, 'tiet' => 5]
        ];

        $successCount = 0;
        $failedClasses = [];

        foreach ($classes as $class) {
            $lopHpId = $class['id'];
            $giangVien = $class['giang_vien'];
            $soTinChi = (int)$class['so_tin_chi'];
            $soTiet = $soTinChi;
            if ($soTiet <= 0) $soTiet = 3;

            $placed = false;

            shuffle($slots);
            shuffle($phongs);

            foreach ($slots as $slot) {
                $thu = $slot['thu'];
                $tietBd = $slot['tiet'];

                foreach ($phongs as $phong) {
                    $conflict = $this->checkConflict(0, $phong, $giangVien, $lopHpId, $thu, $tietBd, $soTiet, $hk, $nh);
                    if ($conflict === false) {
                        $this->db->query('
                            INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, giang_vien, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc)
                            VALUES (:lop_hp_id, :thu, :tiet_bd, :so_tiet, :phong, :gv, :hk, :nh, :ngay_bd, :ngay_kt)
                        ', [
                            'lop_hp_id' => $lopHpId,
                            'thu' => $thu,
                            'tiet_bd' => $tietBd,
                            'so_tiet' => $soTiet,
                            'phong' => $phong,
                            'gv' => $giangVien,
                            'hk' => $hk,
                            'nh' => $nh,
                            'ngay_bd' => $class['ngay_bat_dau'],
                            'ngay_kt' => $class['ngay_ket_thuc']
                        ]);
                        $successCount++;
                        $placed = true;
                        break 2;
                    }
                }
            }

            if (!$placed) {
                $failedClasses[] = $class['ma_lop_hp'] . ' (' . $class['ten_hp'] . ')';
            }
        }

        if (empty($failedClasses)) {
            return [
                'status' => 'success',
                'message' => "Đã xếp lịch tự động thành công cho {$successCount}/" . count($classes) . " lớp học phần mà không xảy ra bất kỳ xung đột nào!"
            ];
        } else {
            return [
                'status' => 'warning',
                'message' => "Đã xếp lịch thành công cho {$successCount}/" . count($classes) . " lớp học phần. Không thể xếp lịch cho các lớp sau do xung đột tài nguyên: " . implode(', ', $failedClasses)
            ];
        }
    }
}
