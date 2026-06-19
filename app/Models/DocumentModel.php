<?php
namespace App\Models;

use App\Core\Database;

class DocumentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function uploadDocument($studentId, $hpId, $title, $description, $file, $isPublic = 1) {
        $orig_name= basename($file['name']);
        $ext      = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $size     = $file['size'];
        $tmp      = $file['tmp_name'];

        if (!in_array($ext, ALLOWED_FILE_TYPES)) {
            return ['type'=>'danger','text'=>'Định dạng file không hợp lệ'];
        } elseif ($size > MAX_UPLOAD_SIZE) {
            return ['type'=>'danger','text'=>'File vượt quá dung lượng cho phép'];
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            return ['type'=>'danger','text'=>$this->getUploadErrorMessage($file['error'])];
        }

        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

        $studentId = $studentId ?: 0;
        $new_name = time() . '_' . $studentId . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig_name);
        $dest     = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $new_name;

        if (move_uploaded_file($tmp, $dest)) {
            $loai = strtoupper($ext);
            try {
                $this->db->query("INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file, is_public) VALUES (:sid, :hpId, :tieuDe, :moTa, :tenFile, :duongDan, :kichThuoc, :loai, :isPublic)", [
                    'sid' => $studentId > 0 ? $studentId : null,
                    'hpId' => $hpId,
                    'tieuDe' => $title,
                    'moTa' => $description,
                    'tenFile' => $orig_name,
                    'duongDan' => $new_name,
                    'kichThuoc' => $size,
                    'loai' => $loai,
                    'isPublic' => $isPublic ? 1 : 0
                ]);
                return ['type'=>'success','text'=>'Chia sẻ tài liệu thành công'];
            } catch (\Exception $e) {
                if (file_exists($dest)) unlink($dest);
                return ['type'=>'danger','text'=>'Tải lên thất bại, vui lòng thử lại'];
            }
        } else {
            $errorCode = is_uploaded_file($tmp) ? error_get_last()['message'] ?? '' : '';
            $hint = $errorCode ? ' Lỗi hệ thống: ' . $errorCode : '';
            return ['type'=>'danger','text'=>'Tải lên thất bại: không thể di chuyển file tạm sang thư mục đích. Kiểm tra quyền ghi thư mục uploads.' . $hint];
        }
    }

    private function getUploadErrorMessage(int $errorCode): string {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File vượt quá dung lượng cho phép.';
            case UPLOAD_ERR_PARTIAL:
                return 'File chỉ tải lên một phần, vui lòng thử lại.';
            case UPLOAD_ERR_NO_FILE:
                return 'Chưa chọn file để tải lên.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Thiếu thư mục tạm trên server.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Không thể ghi file lên server.';
            case UPLOAD_ERR_EXTENSION:
                return 'Quá trình tải lên bị dừng bởi extension.';
            default:
                return 'Tải lên thất bại, vui lòng thử lại.';
        }
    }

    public function deleteDocument($studentId, $documentId) {
        $row = $this->db->fetch("SELECT duong_dan FROM tai_lieu WHERE id=:id AND sinh_vien_id=:sid", ['id' => $documentId, 'sid' => $studentId]);
        if ($row) {
            $file_path = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $row['duong_dan'];
            if (file_exists($file_path)) unlink($file_path);
            $this->db->query("DELETE FROM tai_lieu WHERE id=:id AND sinh_vien_id=:sid", ['id' => $documentId, 'sid' => $studentId]);
            return true;
        }
        return false;
    }

    public function deleteDocumentById($documentId) {
        $row = $this->db->fetch("SELECT duong_dan FROM tai_lieu WHERE id=:id", ['id' => $documentId]);
        if ($row) {
            $file_path = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $row['duong_dan'];
            if (file_exists($file_path)) unlink($file_path);
            $this->db->query("DELETE FROM tai_lieu WHERE id=:id", ['id' => $documentId]);
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
            LEFT JOIN sinh_vien sv ON sv.id = tl.sinh_vien_id
            LEFT JOIN hoc_phan hp ON hp.id = tl.hoc_phan_id
            $where
            ORDER BY tl.ngay_dang DESC
        ";

        $rows = $this->db->fetchAll($sql, $params);

        // Nếu chế độ không phải 'cua_toi', chỉ trả về tài liệu công khai.
        if ($mode !== 'cua_toi') {
            $filtered = [];
            foreach ($rows as $r) {
                if (!isset($r['is_public']) || intval($r['is_public']) === 1) {
                    $filtered[] = $r;
                }
            }
            return $filtered;
        }

        return $rows;
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
            FROM ctdt_chi_tiet c 
            JOIN nganh n ON n.id = c.nganh_id
            JOIN hoc_phan hp ON hp.id=c.hoc_phan_id
            WHERE n.ten_nganh = :nganh
            ORDER BY hp.ten_hp
        ";
        return $this->db->fetchAll($sql, ['nganh' => $nganh]);
    }
}
