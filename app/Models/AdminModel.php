<?php
namespace App\Models;

use App\Core\Database;

class AdminModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getDashboardStats() {
        $stats = [];
        $stats['total_students'] = $this->db->fetch("SELECT COUNT(*) as cnt FROM sinh_vien")['cnt'];
        $stats['total_hoc_phan'] = $this->db->fetch("SELECT COUNT(*) as cnt FROM hoc_phan")['cnt'];
        $stats['total_schedule'] = $this->db->fetch("SELECT COUNT(*) as cnt FROM thoi_khoa_bieu")['cnt'];
        $stats['total_registrations'] = $this->db->fetch("SELECT COUNT(*) as cnt FROM dang_ky_hp WHERE trang_thai='Đã duyệt'")['cnt'];
        
        // Thống kê mới
        $stats['total_khoa'] = $this->db->fetch("SELECT COUNT(DISTINCT khoa) as cnt FROM sinh_vien WHERE khoa IS NOT NULL AND khoa != ''")['cnt'];
        $stats['total_lop'] = $this->db->fetch("SELECT COUNT(DISTINCT lop) as cnt FROM sinh_vien WHERE lop IS NOT NULL AND lop != ''")['cnt'];
        $stats['total_giang_vien'] = $this->db->fetch("SELECT COUNT(DISTINCT giang_vien) as cnt FROM thoi_khoa_bieu WHERE giang_vien IS NOT NULL AND giang_vien != ''")['cnt'];
        
        return $stats;
    }
}
