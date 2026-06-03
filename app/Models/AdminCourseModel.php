<?php
namespace App\Models;

use App\Core\Database;

class AdminCourseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->autoCloseExpiredClasses();
    }

    private function autoCloseExpiredClasses() {
        try {
            $this->db->query("
                UPDATE lop_hoc_phan 
                SET trang_thai_mo_lop = 'Đã đóng' 
                WHERE trang_thai_mo_lop = 'Đang mở' 
                  AND ngay_ket_thuc_dk IS NOT NULL 
                  AND ngay_ket_thuc_dk < NOW()
            ");
        } catch (\Exception $e) {
            // Bỏ qua lỗi nếu có
        }
    }

    // ==========================================
    // 1. QUẢN LÝ HỌC PHẦN (COURSE)
    // ==========================================

    public function getCourses($search = '', $hoc_ky = 0, $loai = '', $khoa = '', $limit = 0, $offset = 0) {
        $sql = 'SELECT * FROM hoc_phan WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (ma_hp LIKE :search1 OR ten_hp LIKE :search2)';
            $params['search1'] = $like;
            $params['search2'] = $like;
        }

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= ' AND hoc_ky = :hoc_ky';
            $params['hoc_ky'] = $hoc_ky;
        }

        if (in_array($loai, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
            $sql .= ' AND loai = :loai';
            $params['loai'] = $loai;
        }

        if ($khoa !== '') {
            $sql .= ' AND khoa_phu_trach = :khoa';
            $params['khoa'] = $khoa;
        }

        $sql .= ' ORDER BY hoc_ky ASC, ma_hp ASC';

        if ($limit > 0) {
            $sql = str_replace('1 = 1', '1 = 1', $sql); // Chỉ để giữ an toàn
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    public function countCourses($search = '', $hoc_ky = 0, $loai = '', $khoa = '') {
        $sql = 'SELECT COUNT(*) as total FROM hoc_phan WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (ma_hp LIKE :search1 OR ten_hp LIKE :search2)';
            $params['search1'] = $like;
            $params['search2'] = $like;
        }

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= ' AND hoc_ky = :hoc_ky';
            $params['hoc_ky'] = $hoc_ky;
        }

        if (in_array($loai, ['Bắt buộc', 'Tự chọn', 'Đại cương'], true)) {
            $sql .= ' AND loai = :loai';
            $params['loai'] = $loai;
        }

        if ($khoa !== '') {
            $sql .= ' AND khoa_phu_trach = :khoa';
            $params['khoa'] = $khoa;
        }

        $res = $this->db->fetch($sql, $params);
        return $res ? (int)$res['total'] : 0;
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
        $sql = 'INSERT INTO hoc_phan (ma_hp, ten_hp, so_tin_chi, loai, hoc_ky, nien_khoa, so_tiet_ly_thuyet, so_tiet_thuc_hanh, khoa_phu_trach, ma_hp_tien_quyet, mo_ta, trang_thai_hoat_dong) 
                VALUES (:ma_hp, :ten_hp, :so_tin_chi, :loai, :hoc_ky, :nien_khoa, :so_tiet_ly_thuyet, :so_tiet_thuc_hanh, :khoa_phu_trach, :ma_hp_tien_quyet, :mo_ta, :trang_thai_hoat_dong)';
        return $this->db->query($sql, $data);
    }

    public function updateCourse($id, $data) {
        $data['id'] = $id;
        $sql = 'UPDATE hoc_phan SET 
                ma_hp = :ma_hp, 
                ten_hp = :ten_hp, 
                so_tin_chi = :so_tin_chi, 
                loai = :loai, 
                hoc_ky = :hoc_ky, 
                nien_khoa = :nien_khoa,
                so_tiet_ly_thuyet = :so_tiet_ly_thuyet,
                so_tiet_thuc_hanh = :so_tiet_thuc_hanh,
                khoa_phu_trach = :khoa_phu_trach,
                ma_hp_tien_quyet = :ma_hp_tien_quyet,
                mo_ta = :mo_ta,
                trang_thai_hoat_dong = :trang_thai_hoat_dong
                WHERE id = :id';
        return $this->db->query($sql, $data);
    }

    public function hasClasses($hpId) {
        $res = $this->db->fetch('SELECT COUNT(*) as total FROM lop_hoc_phan WHERE hoc_phan_id = :hp_id', ['hp_id' => $hpId]);
        return $res && (int)$res['total'] > 0;
    }

    public function deleteCourse($id) {
        if ($this->hasClasses($id)) {
            return false; // Không cho xóa nếu đã có lớp học phần liên kết
        }
        try {
            return $this->db->query('DELETE FROM hoc_phan WHERE id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    // ==========================================
    // 2. QUẢN LÝ LỚP HỌC PHẦN (CLASS)
    // ==========================================

    public function getClasses($search = '', $hoc_ky = 0, $giang_vien = '', $khoa = '', $limit = 0, $offset = 0) {
        $sql = 'SELECT l.*, h.ten_hp, h.ma_hp, h.so_tin_chi, h.khoa_phu_trach 
                FROM lop_hoc_phan l 
                JOIN hoc_phan h ON l.hoc_phan_id = h.id 
                WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (l.ma_lop_hp LIKE :search1 OR h.ten_hp LIKE :search2 OR h.ma_hp LIKE :search3)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= ' AND l.hoc_ky = :hoc_ky';
            $params['hoc_ky'] = $hoc_ky;
        }

        if ($giang_vien !== '') {
            $likeGv = '%' . $giang_vien . '%';
            $sql .= ' AND l.giang_vien LIKE :giang_vien';
            $params['giang_vien'] = $likeGv;
        }

        if ($khoa !== '') {
            $sql .= ' AND h.khoa_phu_trach = :khoa';
            $params['khoa'] = $khoa;
        }

        $sql .= ' ORDER BY l.nam_hoc DESC, l.hoc_ky DESC, l.ma_lop_hp ASC';

        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }

        return $this->db->fetchAll($sql, $params);
    }

    public function countClasses($search = '', $hoc_ky = 0, $giang_vien = '', $khoa = '') {
        $sql = 'SELECT COUNT(*) as total 
                FROM lop_hoc_phan l 
                JOIN hoc_phan h ON l.hoc_phan_id = h.id 
                WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= ' AND (l.ma_lop_hp LIKE :search1 OR h.ten_hp LIKE :search2 OR h.ma_hp LIKE :search3)';
            $params['search1'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= ' AND l.hoc_ky = :hoc_ky';
            $params['hoc_ky'] = $hoc_ky;
        }

        if ($giang_vien !== '') {
            $likeGv = '%' . $giang_vien . '%';
            $sql .= ' AND l.giang_vien LIKE :giang_vien';
            $params['giang_vien'] = $likeGv;
        }

        if ($khoa !== '') {
            $sql .= ' AND h.khoa_phu_trach = :khoa';
            $params['khoa'] = $khoa;
        }

        $res = $this->db->fetch($sql, $params);
        return $res ? (int)$res['total'] : 0;
    }

    public function getClassById($id) {
        return $this->db->fetch('
            SELECT l.*, h.ten_hp, h.ma_hp, h.so_tin_chi, h.khoa_phu_trach 
            FROM lop_hoc_phan l 
            JOIN hoc_phan h ON l.hoc_phan_id = h.id 
            WHERE l.id = :id
        ', ['id' => $id]);
    }

    public function getClassByCode($ma_lop_hp) {
        return $this->db->fetch('SELECT id FROM lop_hoc_phan WHERE ma_lop_hp = :ma_lop_hp LIMIT 1', ['ma_lop_hp' => $ma_lop_hp]);
    }

    public function getClassByCodeExceptId($ma_lop_hp, $id) {
        return $this->db->fetch('SELECT id FROM lop_hoc_phan WHERE ma_lop_hp = :ma_lop_hp AND id <> :id LIMIT 1', ['ma_lop_hp' => $ma_lop_hp, 'id' => $id]);
    }

    public function addClass($data) {
        $sql = 'INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop, ngay_bat_dau_dk, ngay_ket_thuc_dk) 
                VALUES (:ma_lop_hp, :hoc_phan_id, :giang_vien, :hoc_ky, :nam_hoc, :si_so_toi_da, 0, :ngay_bat_dau, :ngay_ket_thuc, :trang_thai_mo_lop, :ngay_bat_dau_dk, :ngay_ket_thuc_dk)';
        return $this->db->query($sql, $data);
    }

    public function updateClass($id, $data) {
        $data['id'] = $id;
        $sql = 'UPDATE lop_hoc_phan SET 
                giang_vien = :giang_vien, 
                si_so_toi_da = :si_so_toi_da, 
                ngay_bat_dau = :ngay_bat_dau, 
                ngay_ket_thuc = :ngay_ket_thuc, 
                trang_thai_mo_lop = :trang_thai_mo_lop,
                ngay_bat_dau_dk = :ngay_bat_dau_dk,
                ngay_ket_thuc_dk = :ngay_ket_thuc_dk
                WHERE id = :id';
        return $this->db->query($sql, $data);
    }

    public function hasStudentsRegistered($classId) {
        $res = $this->db->fetch('SELECT COUNT(*) as total FROM dang_ky_hp WHERE lop_hoc_phan_id = :class_id', ['class_id' => $classId]);
        return $res && (int)$res['total'] > 0;
    }

    public function deleteClass($id) {
        if ($this->hasStudentsRegistered($id)) {
            return false;
        }
        try {
            $this->db->query('DELETE FROM thoi_khoa_bieu WHERE lop_hoc_phan_id = :class_id', ['class_id' => $id]);
            return $this->db->query('DELETE FROM lop_hoc_phan WHERE id = :id', ['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getNganhListInCtdt() {
        return $this->db->fetchAll("SELECT DISTINCT nganh FROM ctdt_chi_tiet WHERE COALESCE(nganh,'') <> '' ORDER BY nganh ASC");
    }

    public function duplicateCtdt($nganhNguon, $nganhDich) {
        $checkSource = $this->db->fetch("SELECT COUNT(*) as total FROM ctdt_chi_tiet WHERE nganh = :nganh", ['nganh' => $nganhNguon]);
        if (!$checkSource || (int)$checkSource['total'] === 0) {
            return false;
        }
        
        $sql = "INSERT INTO ctdt_chi_tiet (nganh, hoc_phan_id, hoc_ky)
                SELECT :nganh_dich, hoc_phan_id, hoc_ky
                FROM ctdt_chi_tiet
                WHERE nganh = :nganh_nguon
                  AND hoc_phan_id NOT IN (
                      SELECT hoc_phan_id FROM ctdt_chi_tiet WHERE nganh = :nganh_dich_sub
                  )";
        return $this->db->query($sql, [
            'nganh_dich' => $nganhDich,
            'nganh_nguon' => $nganhNguon,
            'nganh_dich_sub' => $nganhDich
        ]);
    }

    public function batchOpenClasses($nganh, $hocKy, $namHoc, $ngayBatDauDk = null, $ngayKetThucDk = null) {
        $years = explode('-', $namHoc);
        $yearStart = (int)($years[0] ?? date('Y'));
        
        if ($hocKy % 2 !== 0) {
            $ngay_bat_dau = $yearStart . '-09-05';
            $ngay_ket_thuc = ($yearStart + 1) . '-01-15';
        } else {
            $ngay_bat_dau = ($yearStart + 1) . '-01-15';
            $ngay_ket_thuc = ($yearStart + 1) . '-05-30';
        }

        $sql = "SELECT hp.id, hp.ma_hp
                FROM ctdt_chi_tiet ctdt
                JOIN hoc_phan hp ON ctdt.hoc_phan_id = hp.id
                WHERE ctdt.nganh = :nganh AND ctdt.hoc_ky = :hk AND hp.trang_thai_hoat_dong = 1";
        $courses = $this->db->fetchAll($sql, ['nganh' => $nganh, 'hk' => $hocKy]);

        if (empty($courses)) {
            return 0;
        }

        $successCount = 0;
        foreach ($courses as $course) {
            $hpId = $course['id'];
            $maHp = $course['ma_hp'];
            $maLopHp = $maHp . '-L01';

            // Chỉ kiểm tra sự tồn tại của ma_lop_hp trên toàn bảng vì nó là UNIQUE
            $exists = $this->db->fetch(
                "SELECT id FROM lop_hoc_phan WHERE ma_lop_hp = :ma_lop_hp LIMIT 1",
                ['ma_lop_hp' => $maLopHp]
            );

            if ($exists) {
                continue;
            }

            $giangViens = [
                'TS. Nguyễn Văn Hùng', 'ThS. Trần Thị Lan', 'TS. Lê Văn Minh', 'ThS. Hoàng Văn E', 
                'ThS. Phạm Thị Hoa', 'TS. Hoàng Quang Trung', 'ThS. Nguyễn Thị F', 'TS. Trần Văn G'
            ];
            $gv = $giangViens[array_rand($giangViens)];

            // Sử dụng thời gian được chọn hoặc gán mặc định
            $ngay_bat_dau_dk = ($ngayBatDauDk !== null) ? $ngayBatDauDk : date('Y-m-d H:i:s');
            $ngay_ket_thuc_dk = ($ngayKetThucDk !== null) ? $ngayKetThucDk : ($ngay_bat_dau . ' 00:00:00');

            $this->addClass([
                'ma_lop_hp' => $maLopHp,
                'hoc_phan_id' => $hpId,
                'giang_vien' => $gv,
                'hoc_ky' => $hocKy,
                'nam_hoc' => $namHoc,
                'si_so_toi_da' => 80,
                'ngay_bat_dau' => $ngay_bat_dau,
                'ngay_ket_thuc' => $ngay_ket_thuc,
                'trang_thai_mo_lop' => 'Đang mở',
                'ngay_bat_dau_dk' => $ngay_bat_dau_dk,
                'ngay_ket_thuc_dk' => $ngay_ket_thuc_dk
            ]);
            $successCount++;
        }

        return $successCount;
    }
}
