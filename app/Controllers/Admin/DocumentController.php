<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminDocumentModel;

class DocumentController extends Controller {
    private $documentModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->documentModel = new AdminDocumentModel();
    }

    public function index() {
        $items = $this->documentModel->getAllDocuments();
        $this->view('admin/document/index', [
            'items' => $items,
            'page_title' => 'Tài liệu',
            'active_menu' => 'tai_lieu'
        ]);
    }

    public function add() {
        $this->view('admin/document/add', [
            'page_title' => 'Thêm tài liệu',
            'active_menu' => 'tai_lieu'
        ]);
    }

    public function processAdd() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tai-lieu');
        }

        // Kiểm tra CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            setFlash('danger', 'Lỗi bảo mật: CSRF Token không hợp lệ.');
            $this->redirect('/admin/tai-lieu');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            setFlash('danger', 'Tiêu đề là bắt buộc.');
            $this->redirect('/admin/tai-lieu/add');
        }

        $filePath = '';
        if (!empty($_FILES['file']['name'])) {
            $up = $_FILES['file'];
            if ($up['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ALLOWED_FILE_TYPES, true)) {
                    setFlash('danger', 'Định dạng file không được phép tải lên.');
                    $this->redirect('/admin/tai-lieu/add');
                }

                $base = time() . '_' . bin2hex(random_bytes(6));
                $fname = $base . ($ext ? '.' . $ext : '');
                $target = $this->documentModel->getUploadsDir() . DIRECTORY_SEPARATOR . $fname;
                if (move_uploaded_file($up['tmp_name'], $target)) {
                    $filePath = 'uploads/' . $fname;
                }
            }
        }

        $isPublic = isset($_POST['is_public']) && $_POST['is_public'] === '1' ? 1 : 0;
        if ($this->documentModel->insertDocument($title, $description, $filePath, $isPublic)) {
            setFlash('success', 'Thêm tài liệu thành công.');
        } else {
            setFlash('danger', 'Lỗi lưu tài liệu.');
        }
        $this->redirect('/admin/tai-lieu');
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlash('danger', 'ID không hợp lệ.');
            $this->redirect('/admin/tai-lieu');
        }

        $item = $this->documentModel->getDocumentById($id);
        if (!$item) {
            setFlash('danger', 'Không tìm thấy tài liệu.');
            $this->redirect('/admin/tai-lieu');
        }

        $this->view('admin/document/edit', [
            'item' => $item,
            'page_title' => 'Sửa tài liệu',
            'active_menu' => 'tai_lieu'
        ]);
    }

    public function processEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tai-lieu');
        }

        $id = (int)($_POST['id'] ?? 0);

        // Kiểm tra CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            setFlash('danger', 'Lỗi bảo mật: CSRF Token không hợp lệ.');
            $this->redirect('/admin/tai-lieu/edit?id=' . $id);
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            setFlash('danger', 'Tiêu đề là bắt buộc.');
            $this->redirect('/admin/tai-lieu/edit?id=' . $id);
        }

        $item = $this->documentModel->getDocumentById($id);
        if (!$item) {
            setFlash('danger', 'Không tìm thấy tài liệu.');
            $this->redirect('/admin/tai-lieu');
        }

        $filePath = null;
        if (!empty($_FILES['file']['name'])) {
            $up = $_FILES['file'];
            if ($up['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ALLOWED_FILE_TYPES, true)) {
                    setFlash('danger', 'Định dạng file không được phép tải lên.');
                    $this->redirect('/admin/tai-lieu/edit?id=' . $id);
                }

                if (!empty($item['duong_dan'])) {
                    $old = $this->documentModel->getUploadsDir() . basename($item['duong_dan']);
                    if (file_exists($old)) @unlink($old);
                }
                $base = time() . '_' . bin2hex(random_bytes(6));
                $fname = $base . ($ext ? '.' . $ext : '');
                $target = $this->documentModel->getUploadsDir() . DIRECTORY_SEPARATOR . $fname;
                if (move_uploaded_file($up['tmp_name'], $target)) {
                    $filePath = 'uploads/' . $fname;
                }
            }
        }

        $isPublic = isset($_POST['is_public']) && $_POST['is_public'] === '1' ? 1 : 0;
        if ($this->documentModel->updateDocument($id, $title, $description, $filePath, $isPublic)) {
            setFlash('success', 'Cập nhật tài liệu thành công.');
        } else {
            setFlash('danger', 'Lỗi cập nhật tài liệu.');
        }
        $this->redirect('/admin/tai-lieu');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/tai-lieu');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            setFlash('danger', 'ID không hợp lệ.');
            $this->redirect('/admin/tai-lieu');
        }

        if ($this->documentModel->deleteDocument($id)) {
            setFlash('success', 'Xóa tài liệu thành công.');
        } else {
            setFlash('danger', 'Không tìm thấy tài liệu.');
        }
        $this->redirect('/admin/tai-lieu');
    }

    public function download() {
        $file = $_GET['file'] ?? '';
        if (empty($file)) {
            $this->redirect('/admin/tai-lieu');
        }
        
        $path = rtrim(UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($file);
        if (file_exists($path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        } else {
            setFlash('danger', 'File không tồn tại.');
            $this->redirect('/admin/tai-lieu');
        }
    }
}
