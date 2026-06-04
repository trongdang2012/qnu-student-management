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
                SET trang_thai_mo_lop = 'ÄÃ£ Ä‘Ã³ng'
                WHERE trang_thai_mo_lop = 'Äang má»Ÿ'
                  AND ngay_ket_thuc_dk IS NOT NULL
                  AND ngay_ket_thuc_dk < NOW()
            ");
        } catch (\Exception $e) {
            // Bá» qua lá»—i náº¿u cÃ³
        }
    }

    // ==========================================
    // 1. QUáº¢N LÃ Há»ŒC PHáº¦N (COURSE)
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

        if (in_array($loai, ['Báº¯t buá»™c', 'Tá»± chá»n', 'Äáº¡i cÆ°Æ¡ng'], true)) {
            $sql .= ' AND loai = :loai';
            $params['loai'] = $loai;
        }

        if ($khoa !== '') {
            $sql .= ' AND khoa_phu_trach = :khoa';
            $params['khoa'] = $khoa;
        }

        $sql .= ' ORDER BY hoc_ky ASC, ma_hp ASC';

        if ($limit > 0) {
            $sql = str_replace('1 = 1', '1 = 1', $sql); // Chá»‰ Ä‘á»ƒ giá»¯ an toÃ n
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

        if (in_array($loai, ['Báº¯t buá»™c', 'Tá»± chá»n', 'Äáº¡i cÆ°Æ¡ng'], true)) {
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

    public function getCourseDashboardStats() {
        $stats = $this->db->fetch("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN trang_thai_hoat_dong = 1 THEN 1 ELSE 0 END) AS active_total,
                SUM(CASE WHEN trang_thai_hoat_dong = 0 THEN 1 ELSE 0 END) AS inactive_total,
                SUM(CASE WHEN ma_hp_tien_quyet IS NOT NULL AND ma_hp_tien_quyet <> '' THEN 1 ELSE 0 END) AS prerequisite_total,
                SUM(so_tin_chi) AS credit_total
            FROM hoc_phan
        ");

        $withoutClasses = $this->db->fetch("
            SELECT COUNT(*) AS total
            FROM hoc_phan h
            LEFT JOIN lop_hoc_phan l ON l.hoc_phan_id = h.id
            WHERE l.id IS NULL
        ");

        $byType = $this->db->fetchAll("
            SELECT loai, COUNT(*) AS total
            FROM hoc_phan
            GROUP BY loai
            ORDER BY total DESC
        ");

        return [
            'total' => (int)($stats['total'] ?? 0),
            'active_total' => (int)($stats['active_total'] ?? 0),
            'inactive_total' => (int)($stats['inactive_total'] ?? 0),
            'prerequisite_total' => (int)($stats['prerequisite_total'] ?? 0),
            'credit_total' => (int)($stats['credit_total'] ?? 0),
            'without_classes' => (int)($withoutClasses['total'] ?? 0),
            'by_type' => $byType ?: []
        ];
    }

    public function getCoursesWithoutClasses($limit = 8) {
        return $this->db->fetchAll("
            SELECT h.id, h.ma_hp, h.ten_hp, h.hoc_ky, h.khoa_phu_trach
            FROM hoc_phan h
            LEFT JOIN lop_hoc_phan l ON l.hoc_phan_id = h.id
            WHERE l.id IS NULL AND h.trang_thai_hoat_dong = 1
            ORDER BY h.hoc_ky ASC, h.ma_hp ASC
            LIMIT " . (int)$limit
        );
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

    public function getClassDashboardStats($hoc_ky = 0, $nam_hoc = '') {
        $sql = "
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN trang_thai_mo_lop = 'Đang mở' THEN 1 ELSE 0 END) AS open_total,
                SUM(CASE WHEN trang_thai_mo_lop = 'Lên kế hoạch' THEN 1 ELSE 0 END) AS planning_total,
                SUM(CASE WHEN trang_thai_mo_lop = 'Đã đóng' THEN 1 ELSE 0 END) AS closed_total,
                SUM(CASE WHEN COALESCE(giang_vien, '') = '' THEN 1 ELSE 0 END) AS missing_teacher_total,
                SUM(CASE WHEN si_so_hien_tai >= si_so_toi_da THEN 1 ELSE 0 END) AS full_total,
                SUM(si_so_hien_tai) AS enrolled_total,
                SUM(si_so_toi_da) AS capacity_total
            FROM lop_hoc_phan
            WHERE 1 = 1
        ";
        $params = [];

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= " AND hoc_ky = :hoc_ky";
            $params['hoc_ky'] = $hoc_ky;
        }

        if ($nam_hoc !== '') {
            $sql .= " AND nam_hoc = :nam_hoc";
            $params['nam_hoc'] = $nam_hoc;
        }

        $stats = $this->db->fetch($sql, $params);

        return [
            'total' => (int)($stats['total'] ?? 0),
            'open_total' => (int)($stats['open_total'] ?? 0),
            'planning_total' => (int)($stats['planning_total'] ?? 0),
            'closed_total' => (int)($stats['closed_total'] ?? 0),
            'missing_teacher_total' => (int)($stats['missing_teacher_total'] ?? 0),
            'full_total' => (int)($stats['full_total'] ?? 0),
            'enrolled_total' => (int)($stats['enrolled_total'] ?? 0),
            'capacity_total' => (int)($stats['capacity_total'] ?? 0)
        ];
    }

    public function getClassOperationalAlerts($hoc_ky = 0, $nam_hoc = '', $limit = 8) {
        $sql = "
            SELECT l.id, l.ma_lop_hp, l.giang_vien, l.si_so_toi_da, l.si_so_hien_tai,
                   l.trang_thai_mo_lop, l.hoc_ky, l.nam_hoc, h.ma_hp, h.ten_hp
            FROM lop_hoc_phan l
            JOIN hoc_phan h ON l.hoc_phan_id = h.id
            WHERE (
                COALESCE(l.giang_vien, '') = ''
                OR l.si_so_hien_tai >= l.si_so_toi_da
                OR l.ngay_bat_dau_dk IS NULL
                OR l.ngay_ket_thuc_dk IS NULL
            )
        ";
        $params = [];

        if ($hoc_ky >= 1 && $hoc_ky <= 8) {
            $sql .= " AND l.hoc_ky = :hoc_ky";
            $params['hoc_ky'] = $hoc_ky;
        }

        if ($nam_hoc !== '') {
            $sql .= " AND l.nam_hoc = :nam_hoc";
            $params['nam_hoc'] = $nam_hoc;
        }

        $sql .= " ORDER BY l.nam_hoc DESC, l.hoc_ky DESC, l.ma_lop_hp ASC LIMIT " . (int)$limit;

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
        return $this->db->fetchAll("SELECT DISTINCT n.ten_nganh as nganh FROM ctdt_chi_tiet c JOIN nganh n ON c.nganh_id = n.id ORDER BY n.ten_nganh ASC");
    }

    public function duplicateCtdt($nganhNguon, $nganhDich) {
        $sourceNganh = $this->db->fetch("SELECT id FROM nganh WHERE ten_nganh = :name", ['name' => $nganhNguon]);
        if (!$sourceNganh) {
            return false;
        }
        $sourceId = $sourceNganh['id'];

        $destNganh = $this->db->fetch("SELECT id FROM nganh WHERE ten_nganh = :name", ['name' => $nganhDich]);
        if (!$destNganh) {
            $sourceNganhDetail = $this->db->fetch("SELECT khoa_id FROM nganh WHERE id = :id", ['id' => $sourceId]);
            if (!$sourceNganhDetail) {
                return false;
            }
            $khoaId = $sourceNganhDetail['khoa_id'];
            
            $this->db->query("INSERT INTO nganh (ten_nganh, khoa_id) VALUES (:ten_nganh, :khoa_id)", [
                'ten_nganh' => $nganhDich,
                'khoa_id' => $khoaId
            ]);
            $destId = $this->db->lastInsertId();
        } else {
            $destId = $destNganh['id'];
        }

        $checkSource = $this->db->fetch("SELECT COUNT(*) as total FROM ctdt_chi_tiet WHERE nganh_id = :nganh_id", ['nganh_id' => $sourceId]);
        if (!$checkSource || (int)$checkSource['total'] === 0) {
            return false;
        }
        $sql = "INSERT INTO ctdt_chi_tiet (nganh_id, hoc_phan_id, hoc_ky)
                SELECT :nganh_dich_id, hoc_phan_id, hoc_ky
                FROM ctdt_chi_tiet
                WHERE nganh_id = :nganh_nguon_id
                  AND hoc_phan_id NOT IN (
                      SELECT hoc_phan_id FROM ctdt_chi_tiet WHERE nganh_id = :nganh_dich_id_sub
                  )";
        return $this->db->query($sql, [
            'nganh_dich_id' => $destId,
            'nganh_nguon_id' => $sourceId,
            'nganh_dich_id_sub' => $destId
        ]);
    }

    /**
     * Mở lớp học phần hàng loạt cho một ngành/chuyên ngành
     *
     * @param string $nganh - Tên ngành từ CTDT
     * @param int $hocKyCtdt - Học kỳ theo CTDT (1-8) để lấy danh sách môn học
     * @param int $hocKyHocVu - Học kỳ học vụ hiện tại (1-3) để lưu vào lop_hoc_phan
     * @param string $namHoc - Năm học (vd: 2025-2026)
     * @param string|null $ngayBatDauDk - Ngày bắt đầu đăng ký (mặc định: hôm nay)
     * @param string|null $ngayKetThucDk - Ngày kết thúc đăng ký (mặc định: ngày bắt đầu học)
     * @return int Số lớp được tạo thành công
     */
    public function batchOpenClasses($nganh, $hocKyCtdt, $hocKyHocVu, $namHoc, $ngayBatDauDk = null, $ngayKetThucDk = null) {
        // Tính toán ngày bắt đầu và kết thúc dựa trên học kỳ học vụ
        $years = explode('-', $namHoc);
        $yearStart = (int)($years[0] ?? date('Y'));

        // Học kỳ học vụ 1: tháng 9 (năm trước) - tháng 1 (năm sau)
        // Học kỳ học vụ 2: tháng 1 - tháng 5 (năm sau)
        // Học kỳ học vụ 3: tháng 5 - tháng 9 (năm sau)
        switch ($hocKyHocVu) {
            case 2:
                $ngay_bat_dau = ($yearStart + 1) . '-01-15';
                $ngay_ket_thuc = ($yearStart + 1) . '-05-30';
                break;
            case 3:
                $ngay_bat_dau = ($yearStart + 1) . '-05-15';
                $ngay_ket_thuc = ($yearStart + 1) . '-09-30';
                break;
            default: // HK 1
                $ngay_bat_dau = $yearStart . '-09-05';
                $ngay_ket_thuc = ($yearStart + 1) . '-01-15';
        }

        // Lấy danh sách học phần từ chương trình đào tạo theo học kỳ CTDT
        $sql = "SELECT hp.id, hp.ma_hp, hp.so_tin_chi
                FROM ctdt_chi_tiet ctdt
                JOIN nganh n ON ctdt.nganh_id = n.id
                JOIN hoc_phan hp ON ctdt.hoc_phan_id = hp.id
                WHERE n.ten_nganh = :nganh
                  AND ctdt.hoc_ky = :hk_ctdt
                  AND hp.trang_thai_hoat_dong = 1
                ORDER BY hp.ma_hp ASC";

        $courses = $this->db->fetchAll($sql, [
            'nganh' => $nganh,
            'hk_ctdt' => $hocKyCtdt
        ]);

        if (empty($courses)) {
            return 0;
        }

        $successCount = 0;

        foreach ($courses as $course) {
            $hpId = $course['id'];
            $maHp = $course['ma_hp'];
            $soTinChi = (int)$course['so_tin_chi'];

            // Sinh mã lớp tự động: tìm số hiệu tiếp theo (-L01, -L02, ...)
            $nextClassNumber = $this->getNextClassNumber($maHp);
            $maLopHp = $maHp . '-L' . str_pad($nextClassNumber, 2, '0', STR_PAD_LEFT);

            // Xác định sĩ số tối đa dựa trên loại môn
            $si_so_toi_da = 80; // Mặc định 80 cho môn ngành

            // Nếu là môn đại cương, tăng sĩ số lên 120-150
            $course_type = $this->db->fetch(
                "SELECT loai FROM hoc_phan WHERE id = :id",
                ['id' => $hpId]
            );
            if ($course_type && $course_type['loai'] === 'Đại cương') {
                $si_so_toi_da = 120;
            }

            // Để trống tên giảng viên - Trưởng khoa/Admin sẽ phân công sau
            $giang_vien = '';

            // Sử dụng thời gian được chọn hoặc gán mặc định
            $ngay_bat_dau_dk = ($ngayBatDauDk !== null) ? $ngayBatDauDk : date('Y-m-d H:i:s');
            $ngay_ket_thuc_dk = ($ngayKetThucDk !== null) ? $ngayKetThucDk : ($ngay_bat_dau . ' 23:59:59');

            try {
                $this->addClass([
                    'ma_lop_hp' => $maLopHp,
                    'hoc_phan_id' => $hpId,
                    'giang_vien' => $giang_vien,
                    'hoc_ky' => $hocKyHocVu,  // LƯU Ý: Lưu học kỳ học vụ, KHÔNG phải học kỳ CTDT
                    'nam_hoc' => $namHoc,
                    'si_so_toi_da' => $si_so_toi_da,
                    'ngay_bat_dau' => $ngay_bat_dau,
                    'ngay_ket_thuc' => $ngay_ket_thuc,
                    'trang_thai_mo_lop' => 'Đang mở',
                    'ngay_bat_dau_dk' => $ngay_bat_dau_dk,
                    'ngay_ket_thuc_dk' => $ngay_ket_thuc_dk
                ]);
                $successCount++;
            } catch (\Exception $e) {
                // Bỏ qua lỗi nếu có (vd: duplicate key)
                continue;
            }
        }

        return $successCount;
    }

    /**
     * Lấy số hiệu lớp tiếp theo cho một môn học
     * VD: Nếu đã có CNTT001-L01, CNTT001-L02, sẽ trả về 3 (để tạo L03)
     *
     * @param string $maHp - Mã học phần
     * @return int Số hiệu lớp tiếp theo
     */
    private function getNextClassNumber($maHp) {
        $sql = "SELECT CAST(SUBSTRING(ma_lop_hp, LENGTH(:ma_hp) + 3) AS UNSIGNED) as class_num
                FROM lop_hoc_phan
                WHERE ma_lop_hp LIKE :pattern
                ORDER BY class_num DESC
                LIMIT 1";

        $pattern = $maHp . '-L%';

        $result = $this->db->fetch($sql, [
            'ma_hp' => $maHp,
            'pattern' => $pattern
        ]);

        return $result && $result['class_num'] ? ((int)$result['class_num'] + 1) : 1;
    }
}
