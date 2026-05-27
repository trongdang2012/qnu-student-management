<?php
namespace App\Models;

use App\Core\Database;

class DocumentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function uploadDocument($studentId, $hpId, $title, $description, $file) {
        $orig_name= basename($file['name']);
        $ext      = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $size     = $file['size'];
        $tmp      = $file['tmp_name'];

        if (!in_array($ext, ALLOWED_FILE_TYPES)) {
            return ['type'=>'danger','text'=>'Định dạng file không hợp lệ'];
        } elseif ($size > MAX_UPLOAD_SIZE) {
            return ['type'=>'danger','text'=>'File vượt quá dung lượng cho phép'];
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            return ['type'=>'danger','text'=>'Tải lên thất bại, vui lòng thử lại'];
        }

        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

        $new_name = time() . '_' . $studentId . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig_name);
        $dest     = UPLOAD_DIR . $new_name;

        if (move_uploaded_file($tmp, $dest)) {
            $loai = strtoupper($ext);
            try {
                $this->db->query("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file) VALUES (:sid, :hpId, :tieuDe, :moTa, :tenFile, :duongDan, :kichThuoc, :loai)", [
                    'sid' => $studentId,
                    'hpId' => $hpId,
                    'tieuDe' => $title,
                    'moTa' => $description,
                    'tenFile' => $orig_name,
                    'duongDan' => $new_name,
                    'kichThuoc' => $size,
                    'loai' => $loai
                ]);
                return ['type'=>'success','text'=>'Chia sẻ tài liệu thành công'];
            } catch (\Exception $e) {
                unlink($dest);
                return ['type'=>'danger','text'=>'Tải lên thất bại, vui lòng thử lại'];
            }
        } else {
            return ['type'=>'danger','text'=>'Tải lên thất bại, vui lòng thử lại'];
        }
    }

    public function deleteDocument($studentId, $documentId) {
        $row = $this->db->fetch("SELECT duong_dan FROM tai_lieu WHERE id=:id AND sinh_vien_id=:sid", ['id' => $documentId, 'sid' => $studentId]);
        if ($row) {
            $file_path = UPLOAD_DIR . $row['duong_dan'];
            if (file_exists($file_path)) unlink($file_path);
            $this->db->query("DELETE FROM tai_lieu WHERE id=:id AND sinh_vien_id=:sid", ['id' => $documentId, 'sid' => $studentId]);
            return true;
        }
        return false;
    }

    public function getDocuments($studentId, $filterHp = 0, $mode = 'tat_ca', $search = '') {
        $where = "WHERE 1=1";
        $params = [];

        if ($mode === 'cua_toi') {
            $where .= " AND tl.sinh_vien_id = :sid";
            $params['sid'] = $studentId;
        }

        if ($filterHp > 0) {
            $where .= " AND tl.hoc_phan_id = :filterHp";
            $params['filterHp'] = $filterHp;
        }

        if (!empty($search)) {
            // Để tìm kiếm tiếng Việt hoạt động hoàn hảo (không phân biệt dấu/hoa thường), ta dùng COLLATE utf8mb4_general_ci ép kiểu so sánh an toàn
            $where .= " AND (tl.tieu_de COLLATE utf8mb4_general_ci LIKE :search OR tl.mo_ta COLLATE utf8mb4_general_ci LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql = "
            SELECT tl.*, sv.ho_ten, sv.ma_sv, hp.ten_hp
            FROM tai_lieu tl
            JOIN sinh_vien sv ON sv.id = tl.sinh_vien_id
            LEFT JOIN hoc_phan hp ON hp.id = tl.hoc_phan_id
            $where
            ORDER BY tl.ngay_dang DESC
        ";

        return $this->db->fetchAll($sql, $params);
    }

    public function getDocumentById($id) {
        return $this->db->fetch("SELECT * FROM tai_lieu WHERE id=:id", ['id' => $id]);
    }

    public function incrementDownloadCount($id) {
        $this->db->query("UPDATE tai_lieu SET luot_tai = luot_tai + 1 WHERE id=:id", ['id' => $id]);
    }

    public function getCoursesByMajor($nganh) {
        $sql = "
            SELECT hp.id, hp.ten_hp, hp.ma_hp
            FROM ctdt_chi_tiet c JOIN hoc_phan hp ON hp.id=c.hoc_phan_id
            WHERE c.nganh = :nganh
            ORDER BY hp.ten_hp
        ";
        return $this->db->fetchAll($sql, ['nganh' => $nganh]);
    }
}
