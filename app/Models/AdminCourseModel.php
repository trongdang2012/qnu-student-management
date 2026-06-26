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
        $sql = 'SELECT l.*, h.ten_hp, h.ma_hp, h.so_tin_chi, h.khoa_phu_trach,
                       t.thu, t.tiet_bat_dau, t.so_tiet, t.phong_hoc, t.id as schedule_id
                FROM lop_hoc_phan l
                JOIN hoc_phan h ON l.hoc_phan_id = h.id
                LEFT JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
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
            SELECT l.*, h.ten_hp, h.ma_hp, h.so_tin_chi, h.khoa_phu_trach,
                   t.thu, t.tiet_bat_dau, t.so_tiet, t.phong_hoc, t.id as schedule_id
            FROM lop_hoc_phan l
            JOIN hoc_phan h ON l.hoc_phan_id = h.id
            LEFT JOIN thoi_khoa_bieu t ON t.lop_hoc_phan_id = l.id
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
        $sql = 'INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, giang_vien_id, phong_hoc_id, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop, ngay_bat_dau_dk, ngay_ket_thuc_dk)
                VALUES (:ma_lop_hp, :hoc_phan_id, :giang_vien, :giang_vien_id, :phong_hoc_id, :hoc_ky, :nam_hoc, :si_so_toi_da, 0, :ngay_bat_dau, :ngay_ket_thuc, :trang_thai_mo_lop, :ngay_bat_dau_dk, :ngay_ket_thuc_dk)';
        return $this->db->query($sql, $data);
    }

    public function updateClass($id, $data) {
        $data['id'] = $id;
        $sql = 'UPDATE lop_hoc_phan SET
                giang_vien = :giang_vien,
                giang_vien_id = :giang_vien_id,
                phong_hoc_id = :phong_hoc_id,
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

    public function getCoursesByNganh($nganhId) {
        $sql = "SELECT hp.*, ctdt.hoc_ky as hoc_ky_ctdt
                FROM ctdt_chi_tiet ctdt
                JOIN hoc_phan hp ON ctdt.hoc_phan_id = hp.id
                WHERE ctdt.nganh_id = :nganh_id
                ORDER BY ctdt.hoc_ky ASC, hp.ma_hp ASC";
        return $this->db->fetchAll($sql, ['nganh_id' => $nganhId]);
    }

    public function autoGenerateAndSchedule($khoaId, $nganhId, $hocKyHocVu, $namHoc) {
        // 1. Xác định danh sách học phần cần mở
        // Quy tắc: học kỳ học vụ 1 -> học kỳ CTĐT lẻ (1, 3, 5, 7); học kỳ học vụ 2 -> học kỳ CTĐT chẵn (2, 4, 6, 8); học kỳ học vụ 3 -> tất cả
        $hkCtdtFilter = [];
        if ($hocKyHocVu == 1) {
            $hkCtdtFilter = [1, 3, 5, 7];
        } elseif ($hocKyHocVu == 2) {
            $hkCtdtFilter = [2, 4, 6, 8];
        } else {
            $hkCtdtFilter = [1, 2, 3, 4, 5, 6, 7, 8];
        }
        
        $placeholders = implode(',', array_fill(0, count($hkCtdtFilter), '?'));
        
        $sql = "SELECT hp.*, ctdt.hoc_ky as hoc_ky_ctdt
                FROM ctdt_chi_tiet ctdt
                JOIN hoc_phan hp ON ctdt.hoc_phan_id = hp.id
                WHERE ctdt.nganh_id = ? AND ctdt.hoc_ky IN ($placeholders) AND hp.trang_thai_hoat_dong = 1";
        
        $params = array_merge([$nganhId], $hkCtdtFilter);
        $courses = $this->db->fetchAll($sql, $params);
        
        if (empty($courses)) {
            return [
                'status' => 'danger',
                'message' => 'Không tìm thấy học phần nào trong CTĐT của ngành học phù hợp với học kỳ học vụ này.'
            ];
        }
        
        // Sắp xếp học phần theo thứ tự ưu tiên: thực hành trước, nhiều tín chỉ trước
        usort($courses, function($a, $b) {
            $aThucHanh = (int)$a['so_tiet_thuc_hanh'] > 0 ? 1 : 0;
            $bThucHanh = (int)$b['so_tiet_thuc_hanh'] > 0 ? 1 : 0;
            if ($aThucHanh !== $bThucHanh) {
                return $bThucHanh - $aThucHanh; // Thực hành xếp trước
            }
            return (int)$b['so_tin_chi'] - (int)$a['so_tin_chi']; // Nhiều tín chỉ xếp trước
        });
        
        // 2. Lấy danh sách giảng viên thuộc Khoa
        $giangViens = $this->db->fetchAll("SELECT * FROM giang_vien WHERE khoa_id = :khoa_id", ['khoa_id' => $khoaId]);
        if (empty($giangViens)) {
            // Nếu khoa không có giảng viên, lấy giảng viên toàn trường
            $giangViens = $this->db->fetchAll("SELECT * FROM giang_vien LIMIT 100");
        }
        
        // 3. Lấy danh sách phòng học
        $allRooms = $this->db->fetchAll("SELECT * FROM phong_hoc");
        $roomLyThuyet = [];
        $roomThucHanh = [];
        foreach ($allRooms as $r) {
            if ($r['loai_phong'] === 'Thực hành') {
                $roomThucHanh[] = $r;
            } else {
                $roomLyThuyet[] = $r;
            }
        }
        // Nếu thiếu phòng thực hành, lấy phòng lý thuyết dùng tạm
        if (empty($roomThucHanh)) {
            $roomThucHanh = $roomLyThuyet;
        }
        
        // Tính toán ngày học dựa trên năm học & học kỳ học vụ
        $years = explode('-', $namHoc);
        $yearStart = (int)($years[0] ?? date('Y'));
        switch ($hocKyHocVu) {
            case 2:
                $ngay_bat_dau = ($yearStart + 1) . '-01-15';
                $ngay_ket_thuc = ($yearStart + 1) . '-05-30';
                break;
            case 3:
                $ngay_bat_dau = ($yearStart + 1) . '-05-15';
                $ngay_ket_thuc = ($yearStart + 1) . '-09-30';
                break;
            default:
                $ngay_bat_dau = $yearStart . '-09-05';
                $ngay_ket_thuc = ($yearStart + 1) . '-01-15';
        }
        
        $successCount = 0;
        $failedCount = 0;
        $failedDetails = [];
        
        // Duyệt từng học phần để tạo lớp và xếp lịch
        foreach ($courses as $c) {
            $hpId = $c['id'];
            $maHp = $c['ma_hp'];
            $soTinChi = (int)$c['so_tin_chi'];
            $soTiet = max(2, min($soTinChi, 5)); // số tiết từ 2 đến 5
            $isThucHanh = (int)$c['so_tiet_thuc_hanh'] > 0;
            $hkCtdt = (int)$c['hoc_ky_ctdt']; // khóa học
            
            // Tạo mã lớp tự động
            $nextClassNumber = $this->getNextClassNumber($maHp);
            $maLopHp = $maHp . '-L' . str_pad($nextClassNumber, 2, '0', STR_PAD_LEFT);
            
            // Danh sách phòng học phù hợp loại môn
            $suitableRooms = $isThucHanh ? $roomThucHanh : $roomLyThuyet;
            if (empty($suitableRooms)) {
                $suitableRooms = $allRooms;
            }
            
            $placed = false;
            $placedGvId = null;
            $placedRoomId = null;
            $placedGvName = '';
            $placedRoomName = '';
            $placedThu = 0;
            $placedTietBd = 0;
            
            // Quét tìm khung giờ chung (Thứ 2 đến Thứ 7, ca Sáng: tiết 1-5, ca Chiều: tiết 6-10)
            $timeSlots = [];
            for ($thu = 2; $thu <= 7; $thu++) {
                // Ca sáng: bắt đầu từ tiết 1 hoặc tiết 2
                $timeSlots[] = ['thu' => $thu, 'tiet_bd' => 1, 'ca' => 'sáng'];
                $timeSlots[] = ['thu' => $thu, 'tiet_bd' => 2, 'ca' => 'sáng'];
                // Ca chiều: bắt đầu từ tiết 6 hoặc tiết 7
                $timeSlots[] = ['thu' => $thu, 'tiet_bd' => 6, 'ca' => 'chiều'];
                $timeSlots[] = ['thu' => $thu, 'tiet_bd' => 7, 'ca' => 'chiều'];
            }
            
            // Tráo ngẫu nhiên để phân bố đều lịch
            shuffle($timeSlots);
            
            foreach ($timeSlots as $slot) {
                $thu = $slot['thu'];
                $tietBd = $slot['tiet_bd'];
                
                // Duyệt qua giảng viên và phòng học
                foreach ($giangViens as $gv) {
                    $gvId = $gv['id'];
                    $gvName = $gv['ho_ten'];
                    
                    // Kiểm tra giảng viên rảnh giờ này chưa
                    $gvConflict = $this->db->fetch("
                        SELECT id FROM thoi_khoa_bieu 
                        WHERE giang_vien_id = :gv_id 
                          AND thu = :thu 
                          AND hoc_ky = :hk 
                          AND nam_hoc = :nh
                          AND tiet_bat_dau <= :tiet_kt
                          AND (tiet_bat_dau + so_tiet - 1) >= :tiet_bd
                        LIMIT 1
                    ", [
                        'gv_id' => $gvId, 'thu' => $thu, 'hk' => $hocKyHocVu, 'nh' => $namHoc,
                        'tiet_kt' => $tietBd + $soTiet - 1, 'tiet_bd' => $tietBd
                    ]);
                    
                    if ($gvConflict) continue;
                    
                    // Kiểm tra xem khóa sinh viên của ngành này đã bị trùng lịch môn khác chưa
                    $cohortConflict = $this->db->fetch("
                        SELECT t.id 
                        FROM thoi_khoa_bieu t
                        JOIN lop_hoc_phan l ON t.lop_hoc_phan_id = l.id
                        JOIN ctdt_chi_tiet ctdt_sub ON l.hoc_phan_id = ctdt_sub.hoc_phan_id
                        WHERE ctdt_sub.nganh_id = :nganh_id
                          AND ctdt_sub.hoc_ky = :hk_ctdt
                          AND t.thu = :thu
                          AND t.hoc_ky = :hk
                          AND t.nam_hoc = :nh
                          AND t.tiet_bat_dau <= :tiet_kt
                          AND (t.tiet_bat_dau + t.so_tiet - 1) >= :tiet_bd
                        LIMIT 1
                    ", [
                        'nganh_id' => $nganhId, 'hk_ctdt' => $hkCtdt, 'thu' => $thu, 
                        'hk' => $hocKyHocVu, 'nh' => $namHoc,
                        'tiet_kt' => $tietBd + $soTiet - 1, 'tiet_bd' => $tietBd
                    ]);
                    
                    if ($cohortConflict) continue;
                    
                    // Tìm phòng học trống
                    foreach ($suitableRooms as $room) {
                        $roomId = $room['id'];
                        $roomName = $room['ten_phong'];
                        
                        $roomConflict = $this->db->fetch("
                            SELECT id FROM thoi_khoa_bieu 
                            WHERE phong_hoc_id = :room_id 
                              AND thu = :thu 
                              AND hoc_ky = :hk 
                              AND nam_hoc = :nh
                              AND tiet_bat_dau <= :tiet_kt
                              AND (tiet_bat_dau + so_tiet - 1) >= :tiet_bd
                            LIMIT 1
                        ", [
                            'room_id' => $roomId, 'thu' => $thu, 'hk' => $hocKyHocVu, 'nh' => $namHoc,
                            'tiet_kt' => $tietBd + $soTiet - 1, 'tiet_bd' => $tietBd
                        ]);
                        
                        if (!$roomConflict) {
                            // Cả giảng viên, phòng học và khóa sinh viên đều rảnh! Chọn slot này
                            $placed = true;
                            $placedGvId = $gvId;
                            $placedRoomId = $roomId;
                            $placedGvName = $gvName;
                            $placedRoomName = $roomName;
                            $placedThu = $thu;
                            $placedTietBd = $tietBd;
                            break 3; // Thoát cả 3 vòng lặp
                        }
                    }
                }
            }
            
            // Thêm lớp học phần
            if ($placed) {
                // Tạo lớp thành công với lịch học (Trạng thái ban đầu: Lên kế hoạch)
                $this->db->query("
                    INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, giang_vien_id, phong_hoc_id, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop)
                    VALUES (:ma_lop, :hp_id, :gv_name, :gv_id, :room_id, :hk, :nh, 80, 0, :bd, :kt, 'Lên kế hoạch')
                ", [
                    'ma_lop' => $maLopHp, 'hp_id' => $hpId, 'gv_name' => $placedGvName,
                    'gv_id' => $placedGvId, 'room_id' => $placedRoomId, 'hk' => $hocKyHocVu,
                    'nh' => $namHoc, 'bd' => $ngay_bat_dau, 'kt' => $ngay_ket_thuc
                ]);
                $classId = $this->db->lastInsertId();
                
                // Tạo TKB
                $this->db->query("
                    INSERT INTO thoi_khoa_bieu (lop_hoc_phan_id, thu, tiet_bat_dau, so_tiet, phong_hoc, phong_hoc_id, giang_vien, giang_vien_id, hoc_ky, nam_hoc, ngay_bat_dau, ngay_ket_thuc)
                    VALUES (:class_id, :thu, :tiet_bd, :so_tiet, :room_name, :room_id, :gv_name, :gv_id, :hk, :nh, :bd, :kt)
                ", [
                    'class_id' => $classId, 'thu' => $placedThu, 'tiet_bd' => $placedTietBd,
                    'so_tiet' => $soTiet, 'room_name' => $placedRoomName, 'room_id' => $placedRoomId,
                    'gv_name' => $placedGvName, 'gv_id' => $placedGvId, 'hk' => $hocKyHocVu,
                    'nh' => $namHoc, 'bd' => $ngay_bat_dau, 'kt' => $ngay_ket_thuc
                ]);
                
                $successCount++;
            } else {
                // Xếp lỗi (Tạo lớp nhưng để trống giảng viên và phòng học)
                $this->db->query("
                    INSERT INTO lop_hoc_phan (ma_lop_hp, hoc_phan_id, giang_vien, giang_vien_id, phong_hoc_id, hoc_ky, nam_hoc, si_so_toi_da, si_so_hien_tai, ngay_bat_dau, ngay_ket_thuc, trang_thai_mo_lop)
                    VALUES (:ma_lop, :hp_id, '', NULL, NULL, :hk, :nh, 80, 0, :bd, :kt, 'Lên kế hoạch')
                ", [
                    'ma_lop' => $maLopHp, 'hp_id' => $hpId, 'hk' => $hocKyHocVu,
                    'nh' => $namHoc, 'bd' => $ngay_bat_dau, 'kt' => $ngay_ket_thuc
                ]);
                
                $failedCount++;
                $failedDetails[] = "$maLopHp";
            }
        }
        
        if ($failedCount === 0) {
            return [
                'status' => 'success',
                'message' => "✓ Xếp lớp tự động thành công! Đã tạo và xếp lịch cho <strong>$successCount lớp học phần</strong> không bị trùng lịch."
            ];
        } else {
            return [
                'status' => 'warning',
                'message' => "⚠️ Đã tự động xếp thành công <strong>$successCount lớp</strong>. Có <strong>$failedCount lớp bận tài nguyên không xếp được lịch</strong>, vui lòng chỉnh sửa bằng tay:<br><em>" . implode(', ', $failedDetails) . "</em>"
            ];
        }
    }

    public function scanAndCancelRegistration($adminId = 1) {
        $hk_hien_tai = defined('HOC_KY_HIEN_TAI') ? HOC_KY_HIEN_TAI : 2;
        $nh_hien_tai = defined('NAM_HOC_HIEN_TAI') ? NAM_HOC_HIEN_TAI : '2025-2026';

        $pdo = $this->db->getConnection();
        $pdo->beginTransaction();

        try {
            $canceledClasses = [];
            $creditsViolationStudents = [];

            // 1. Quét các lớp sĩ số < 15 SV trong kỳ học vụ hiện tại
            $lowEnrollmentClasses = $this->db->fetchAll("
                SELECT id, ma_lop_hp, giang_vien, si_so_hien_tai, hoc_phan_id
                FROM lop_hoc_phan
                WHERE hoc_ky = :hk AND nam_hoc = :nh AND si_so_hien_tai < 15 AND trang_thai_mo_lop = 'Đang mở'
            ", ['hk' => $hk_hien_tai, 'nh' => $nh_hien_tai]);

            foreach ($lowEnrollmentClasses as $lhp) {
                $lhpId = $lhp['id'];
                $maLhp = $lhp['ma_lop_hp'];
                $siSo = $lhp['si_so_hien_tai'];
                
                // Lấy tên môn học
                $course = $this->db->fetch("SELECT ten_hp FROM hoc_phan WHERE id = :id", ['id' => $lhp['hoc_phan_id']]);
                $tenHp = $course ? $course['ten_hp'] : '';

                // Lấy danh sách sinh viên đăng ký lớp này
                $registeredStudents = $this->db->fetchAll("
                    SELECT sinh_vien_id FROM dang_ky_hp 
                    WHERE lop_hoc_phan_id = :lhpId AND trang_thai IN ('Chờ duyệt', 'Đã duyệt')
                ", ['lhpId' => $lhpId]);

                if (!empty($registeredStudents)) {
                    // Hủy đăng ký của sinh viên
                    $this->db->query("
                        UPDATE dang_ky_hp 
                        SET trang_thai = 'Đã hủy' 
                        WHERE lop_hoc_phan_id = :lhpId AND trang_thai IN ('Chờ duyệt', 'Đã duyệt')
                    ", ['lhpId' => $lhpId]);

                    // Gửi thông báo đến từng sinh viên
                    foreach ($registeredStudents as $sv) {
                        $this->sendSystemNotification(
                            $sv['sinh_vien_id'],
                            "Hủy lớp học phần do sĩ số thấp (< 15 SV)",
                            "Lớp học phần $maLhp ($tenHp) đã bị hủy tự động do sĩ số đăng ký hiện tại ($siSo SV) không đạt mức tối thiểu (15 SV). Vui lòng chọn đăng ký lớp học phần khác để bổ sung học tập.",
                            $adminId
                        );
                    }
                }

                // Cập nhật trạng thái lớp học phần thành Đã đóng và sĩ số hiện tại = 0
                $this->db->query("
                    UPDATE lop_hoc_phan 
                    SET trang_thai_mo_lop = 'Đã đóng', si_so_hien_tai = 0 
                    WHERE id = :id
                ", ['id' => $lhpId]);

                $canceledClasses[] = "$maLhp ($siSo SV)";
            }

            // 2. Quét số lượng tín chỉ của từng sinh viên (Chỉ áp dụng cho kỳ chính 1 hoặc 2)
            if ($hk_hien_tai != 3) {
                // Lấy tất cả sinh viên đang học
                $students = $this->db->fetchAll("SELECT s.id, s.ma_sv, s.ho_ten, s.nien_khoa, n.ten_nganh AS nganh FROM sinh_vien s LEFT JOIN lop_sinh_hoat l ON l.id = s.lop_sinh_hoat_id LEFT JOIN nganh n ON n.id = l.nganh_id WHERE s.trang_thai = 'Đang học'");
                
                foreach ($students as $sv) {
                    $svId = $sv['id'];
                    $nganh = $sv['nganh'];
                    $nien_khoa = $sv['nien_khoa'];
                    if (empty($nien_khoa) || empty($nganh)) continue;

                    // Tính học kỳ tiến độ hiện tại của sinh viên
                    $parts = explode('-', $nien_khoa);
                    $start_year = (int)$parts[0];
                    $parts_nh = explode('-', $nh_hien_tai);
                    $current_year_start = (int)$parts_nh[0];
                    $diff_years = $current_year_start - $start_year;
                    $student_hk = ($diff_years * 2) + (int)$hk_hien_tai;

                    // Tính tổng số tín chỉ của các môn bắt buộc + đại cương trong kế hoạch chuẩn của học kỳ tiến độ đó trong CTĐT
                    $standardCourses = $this->db->fetchAll("
                        SELECT hp.id, hp.so_tin_chi 
                        FROM ctdt_chi_tiet c
                        JOIN nganh n ON n.id = c.nganh_id
                        JOIN hoc_phan hp ON hp.id = c.hoc_phan_id
                        WHERE n.ten_nganh = :nganh AND c.hoc_ky = :hk AND hp.loai IN ('Bắt buộc', 'Đại cương')
                    ", ['nganh' => $nganh, 'hk' => $student_hk]);

                    $standard_credits = 0;
                    foreach ($standardCourses as $sc) {
                        $standard_credits += (int)$sc['so_tin_chi'];
                    }

                    if ($standard_credits <= 0) {
                        continue;
                    }

                    // Tính khoảng hợp lệ
                    $min_credits = ceil($standard_credits * (2/3));
                    $max_credits = floor($standard_credits * (3/2));

                    // Lấy danh sách các lớp đã đăng ký thành công (Đã duyệt)
                    $registered = $this->db->fetchAll("
                        SELECT dk.id, dk.lop_hoc_phan_id, dk.hoc_phan_id, hp.so_tin_chi, hp.ten_hp, hp.ma_hp, c.hoc_ky AS ctdt_hk
                        FROM dang_ky_hp dk
                        JOIN lop_hoc_phan l ON l.id = dk.lop_hoc_phan_id
                        JOIN hoc_phan hp ON hp.id = l.hoc_phan_id
                        JOIN ctdt_chi_tiet c ON hp.id = c.hoc_phan_id AND c.nganh_id = (SELECT id FROM nganh WHERE ten_nganh = :nganh LIMIT 1)
                        WHERE dk.sinh_vien_id = :sv_id AND dk.hoc_ky = :hk AND dk.nam_hoc = :nh AND dk.trang_thai = 'Đã duyệt'
                    ", ['sv_id' => $svId, 'hk' => $hk_hien_tai, 'nh' => $nh_hien_tai, 'nganh' => $nganh]);

                    $total_registered_credits = 0;
                    foreach ($registered as $reg) {
                        $total_registered_credits += (int)$reg['so_tin_chi'];
                    }

                    if ($total_registered_credits < $min_credits) {
                        // Đăng ký quá ít: Hủy toàn bộ đăng ký và gửi thông báo
                        if (!empty($registered)) {
                            foreach ($registered as $reg) {
                                $this->db->query("UPDATE dang_ky_hp SET trang_thai = 'Đã hủy' WHERE id = :id", ['id' => $reg['id']]);
                                $this->db->query("UPDATE lop_hoc_phan SET si_so_hien_tai = GREATEST(0, si_so_hien_tai - 1) WHERE id = :lhpId", ['lhpId' => $reg['lop_hoc_phan_id']]);
                            }

                            $this->sendSystemNotification(
                                $svId,
                                "Hủy kết quả đăng ký học phần do vi phạm số tín chỉ tối thiểu",
                                "Hệ thống đã tự động hủy toàn bộ kết quả đăng ký học phần học kỳ hiện tại của bạn do tổng số tín chỉ đăng ký ($total_registered_credits TC) dưới mức quy định tối thiểu ($min_credits TC - 2/3 tín chỉ kế hoạch chuẩn $standard_credits TC). Vui lòng liên hệ Phòng Đào tạo để được hỗ trợ đăng ký bổ sung học tập.",
                                $adminId
                            );

                            $creditsViolationStudents[] = "SV {$sv['ma_sv']} ({$sv['ho_ten']}) - ĐK quá ít ($total_registered_credits/$min_credits TC) -> Đã hủy toàn bộ";
                        }
                    } 
                    elseif ($total_registered_credits > $max_credits) {
                        // Đăng ký quá nhiều: Hủy bớt các môn học vượt hoặc học lại trước, sau cùng mới đến môn kế hoạch
                        $m_vuot = [];
                        $m_lai = [];
                        $m_ke_hoach = [];

                        foreach ($registered as $reg) {
                            $ctdt_hk = (int)$reg['ctdt_hk'];
                            if ($ctdt_hk > $student_hk) {
                                $m_vuot[] = $reg;
                            } elseif ($ctdt_hk < $student_hk) {
                                $m_lai[] = $reg;
                            } else {
                                $m_ke_hoach[] = $reg;
                            }
                        }

                        $cancel_list = array_merge($m_vuot, $m_lai, $m_ke_hoach);
                        $removed_courses_info = [];
                        $current_credits = $total_registered_credits;

                        foreach ($cancel_list as $reg) {
                            if ($current_credits <= $max_credits) {
                                break;
                            }

                            $this->db->query("UPDATE dang_ky_hp SET trang_thai = 'Đã hủy' WHERE id = :id", ['id' => $reg['id']]);
                            $this->db->query("UPDATE lop_hoc_phan SET si_so_hien_tai = GREATEST(0, si_so_hien_tai - 1) WHERE id = :lhpId", ['lhpId' => $reg['lop_hoc_phan_id']]);
                            
                            $current_credits -= (int)$reg['so_tin_chi'];
                            $removed_courses_info[] = "{$reg['ten_hp']} ({$reg['so_tin_chi']} TC)";
                        }

                        if (!empty($removed_courses_info)) {
                            $removed_str = implode(', ', $removed_courses_info);
                            $this->sendSystemNotification(
                                $svId,
                                "Hủy bớt học phần đăng ký do vượt quá số tín chỉ tối đa",
                                "Hệ thống đã tự động hủy bớt các học phần đã đăng ký của bạn gồm: $removed_str do tổng số tín chỉ đăng ký ban đầu ($total_registered_credits TC) vượt quá mức quy định tối đa ($max_credits TC - 3/2 tín chỉ kế hoạch chuẩn $standard_credits TC). Số tín chỉ còn lại sau điều chỉnh: $current_credits TC.",
                                $adminId
                            );

                            $creditsViolationStudents[] = "SV {$sv['ma_sv']} ({$sv['ho_ten']}) - ĐK quá nhiều ($total_registered_credits/$max_credits TC) -> Đã hủy bớt: $removed_str";
                        }
                    }
                }
            }

            $pdo->commit();

            return [
                'status' => 'success',
                'message' => "✓ Đã hoàn thành quét hệ thống!<br>" .
                             " - Đã hủy <strong>" . count($canceledClasses) . " lớp học phần</strong> sĩ số thấp (< 15 SV): " . (empty($canceledClasses) ? 'Không có' : implode(', ', $canceledClasses)) . "<br>" .
                             " - Đã xử lý điều chỉnh đăng ký cho <strong>" . count($creditsViolationStudents) . " sinh viên</strong> vi phạm giới hạn tín chỉ."
            ];

        } catch (\Exception $e) {
            $pdo->rollBack();
            return [
                'status' => 'error',
                'message' => "❌ Lỗi hệ thống khi quét và hủy: " . $e->getMessage()
            ];
        }
    }

    private function sendSystemNotification($studentId, $title, $content, $adminId = 1) {
        $this->db->query("
            INSERT INTO thong_bao (tieu_de, noi_dung, loai, nguoi_gui_id) 
            VALUES (:title, :content, 'Hệ thống', :admin_id)
        ", [
            'title' => $title,
            'content' => $content,
            'admin_id' => $adminId
        ]);
        $notificationId = $this->db->lastInsertId();
        
        $this->db->query("
            INSERT INTO thong_bao_sinh_vien (thong_bao_id, sinh_vien_id) 
            VALUES (:notif_id, :sv_id)
        ", [
            'notif_id' => $notificationId,
            'sv_id' => $studentId
        ]);
    }
}
