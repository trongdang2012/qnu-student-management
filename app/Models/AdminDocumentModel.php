<?php
namespace App\Models;

class AdminDocumentModel {
    private $dataFile;
    private $uploadsDir;

    public function __construct() {
        $this->dataFile = ROOT . '/storage/documents/data.json';
        $this->uploadsDir = ROOT . '/storage/documents/uploads';
        
        if (!is_dir(ROOT . '/storage/documents')) {
            @mkdir(ROOT . '/storage/documents', 0755, true);
        }
        if (!is_dir($this->uploadsDir)) {
            @mkdir($this->uploadsDir, 0755, true);
        }
    }

    public function getAllDocuments() {
        if (file_exists($this->dataFile)) {
            return json_decode(file_get_contents($this->dataFile), true) ?: [];
        }
        return [];
    }

    public function getDocumentById($id) {
        $items = $this->getAllDocuments();
        foreach ($items as $item) {
            if ((int)$item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    public function insertDocument($title, $description, $filePath) {
        $items = $this->getAllDocuments();
        $id = $items ? (max(array_column($items, 'id')) + 1) : 1;
        $item = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'file' => $filePath,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $items[] = $item;
        return file_put_contents($this->dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    public function updateDocument($id, $title, $description, $filePath = null) {
        $items = $this->getAllDocuments();
        $updated = false;
        foreach ($items as &$item) {
            if ((int)$item['id'] === $id) {
                $item['title'] = $title;
                $item['description'] = $description;
                if ($filePath !== null) {
                    $item['file'] = $filePath;
                }
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            return file_put_contents($this->dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
        }
        return false;
    }

    public function deleteDocument($id) {
        $items = $this->getAllDocuments();
        $found = false;
        foreach ($items as $k => $item) {
            if ((int)$item['id'] === $id) {
                if (!empty($item['file'])) {
                    $path = ROOT . '/storage/documents/' . $item['file'];
                    if (file_exists($path)) @unlink($path);
                }
                unset($items[$k]);
                $found = true;
                break;
            }
        }

        if ($found) {
            $items = array_values($items);
            return file_put_contents($this->dataFile, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
        }
        return false;
    }

    public function getUploadsDir() {
        return $this->uploadsDir;
    }
}
