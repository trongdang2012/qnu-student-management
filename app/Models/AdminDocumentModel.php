<?php
namespace App\Models;

use App\Core\Database;

class AdminDocumentModel {
    private $db;
    private $uploadsDir;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadsDir = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($this->uploadsDir)) {
            @mkdir($this->uploadsDir, 0755, true);
        }
    }

    public function getAllDocuments() {
        return $this->db->fetchAll(
            'SELECT tl.*, sv.ma_sv, sv.ho_ten FROM tai_lieu tl LEFT JOIN sinh_vien sv ON sv.id = tl.sinh_vien_id ORDER BY tl.ngay_dang DESC'
        );
    }

    public function getDocumentById($id) {
        return $this->db->fetch('SELECT * FROM tai_lieu WHERE id = :id', ['id' => $id]);
    }

    public function insertDocument($title, $description, $filePath, $isPublic = 1) {
        $size = 0;
        if (!empty($filePath)) {
            $fullPath = $this->uploadsDir . basename($filePath);
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
            }
        }

        return $this->db->query(
            'INSERT INTO tai_lieu (sinh_vien_id, hoc_phan_id, tieu_de, mo_ta, ten_file, duong_dan, kich_thuoc, loai_file, is_public) VALUES (NULL, NULL, :title, :description, :fileName, :filePath, :size, :type, :isPublic)',
            [
                'title' => $title,
                'description' => $description,
                'fileName' => basename($filePath),
                'filePath' => $filePath,
                'size' => $size,
                'type' => pathinfo($filePath, PATHINFO_EXTENSION),
                'isPublic' => $isPublic ? 1 : 0
            ]
        );
    }

    public function updateDocument($id, $title, $description, $filePath = null, $isPublic = 1) {
        $params = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'isPublic' => $isPublic ? 1 : 0
        ];
        $sql = 'UPDATE tai_lieu SET tieu_de = :title, mo_ta = :description, is_public = :isPublic';
        if ($filePath !== null) {
            $sql .= ', ten_file = :fileName, duong_dan = :filePath, kich_thuoc = :size, loai_file = :type';
            $params['fileName'] = basename($filePath);
            $params['filePath'] = $filePath;
            $params['size'] = 0;
            $params['type'] = pathinfo($filePath, PATHINFO_EXTENSION);
        }
        $sql .= ' WHERE id = :id';
        return $this->db->query($sql, $params);
    }

    public function deleteDocument($id) {
        $row = $this->getDocumentById($id);
        if ($row) {
            if (!empty($row['duong_dan'])) {
                $path = $this->uploadsDir . basename($row['duong_dan']);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            return $this->db->query('DELETE FROM tai_lieu WHERE id = :id', ['id' => $id]);
        }
        return false;
    }

    public function getUploadsDir() {
        return $this->uploadsDir;
    }
}
