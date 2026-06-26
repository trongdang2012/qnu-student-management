<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudentModel;
use App\Models\DocumentModel;

class DocumentController extends Controller {
    private function requireStudent() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
            $this->redirect('/auth/logout');
        }
    }

    public function index() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $documentModel = new DocumentModel();
        $msg = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'upload') {
                $tieu_de  = trim($_POST['tieu_de'] ?? '');
                $mo_ta    = trim($_POST['mo_ta']   ?? '');
                $hp_id    = (int)($_POST['hoc_phan_id'] ?? 0) ?: null;
                $is_public = isset($_POST['cong_khai']) && $_POST['cong_khai'] === '1' ? 1 : 0;

                if (empty($tieu_de) || empty($_FILES['file_upload']['name'])) {
                    $msg = ['type'=>'danger','text'=>'Vui lòng nhập đầy đủ thông tin'];
                } else {
                    $msg = $documentModel->uploadDocument($sv['id'], $hp_id, $tieu_de, $mo_ta, $_FILES['file_upload'], $is_public);
                    if ($msg['type'] === 'success') {
                        setFlash('success', 'Chia sẻ tài liệu thành công');
                        $this->redirect('/student/tai-lieu');
                    }
                }
            } elseif ($action === 'edit') {
                $tl_id    = (int)($_POST['tl_id'] ?? 0);
                $tieu_de  = trim($_POST['tieu_de'] ?? '');
                $mo_ta    = trim($_POST['mo_ta']   ?? '');
                $hp_id    = (int)($_POST['hoc_phan_id'] ?? 0) ?: null;
                $is_public = isset($_POST['cong_khai']) && $_POST['cong_khai'] === '1' ? 1 : 0;
                $file = !empty($_FILES['file_upload']['name']) ? $_FILES['file_upload'] : null;

                if (empty($tieu_de)) {
                    $msg = ['type'=>'danger','text'=>'Vui lòng nhập tiêu đề tài liệu'];
                } else {
                    $msg = $documentModel->updateDocument($sv['id'], $tl_id, $hp_id, $tieu_de, $mo_ta, $file, $is_public);
                    if ($msg['type'] === 'success') {
                        setFlash('success', 'Cập nhật tài liệu thành công');
                        $this->redirect('/student/tai-lieu');
                    }
                }
            } elseif ($action === 'xoa') {
                $tl_id = (int)($_POST['tl_id'] ?? 0);
                if ($documentModel->deleteDocument($sv['id'], $tl_id)) {
                    setFlash('success', 'Đã xóa tài liệu.');
                }
                $this->redirect('/student/tai-lieu');
            }
        }

        $filter_hp = (int)($_GET['hp'] ?? 0);
        $search_query = trim($_GET['q'] ?? '');

        // Lấy riêng danh sách tài liệu của chính sinh viên đăng (để hiển thị bên trái)
        $my_list = $documentModel->getDocuments($sv['id'], 0, 'cua_toi');
        
        // Lấy danh sách tài liệu chia sẻ chung (để hiển thị bên phải, có lọc học phần và tìm kiếm nếu chọn)
        $tl_list = $documentModel->getDocuments($sv['id'], $filter_hp, 'tat_ca', $search_query);
        $hp_list = $documentModel->getCoursesByMajor($sv['nganh']);

        $this->view('student/documents', [
            'sv' => $sv,
            'my_list' => $my_list,
            'tl_list' => $tl_list,
            'hp_list' => $hp_list,
            'filter_hp' => $filter_hp,
            'search_query' => $search_query,
            'msg' => $msg,
            'page_title' => 'Tài liệu chia sẻ',
            'active_menu' => 'truc_tuyen'
        ]);
    }

    public function download() {
        $this->requireStudent();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->redirect('/student/tai-lieu'); }

        $documentModel = new DocumentModel();
        $tl = $documentModel->getDocumentById($id);
        if (!$tl) { die('Tài liệu không tồn tại.'); }

        $file_path = UPLOAD_DIR . $tl['duong_dan'];
        if (!file_exists($file_path)) { die('File không tồn tại trên máy chủ.'); }

        $documentModel->incrementDownloadCount($id);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($tl['ten_file']) . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($file_path);
        exit;
    }

    public function preview() {
        $this->requireStudent();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { exit; }

        $documentModel = new DocumentModel();
        $tl = $documentModel->getDocumentById($id);
        if (!$tl) { die('Tài liệu không tồn tại.'); }

        $file_path = UPLOAD_DIR . $tl['duong_dan'];
        if (!file_exists($file_path)) { die('File không tồn tại trên máy chủ.'); }

        $ext = strtolower(pathinfo($tl['ten_file'], PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        if ($ext === 'pdf') {
            $mime = 'application/pdf';
        } elseif ($ext === 'png') {
            $mime = 'image/png';
        } elseif (in_array($ext, ['jpg', 'jpeg'])) {
            $mime = 'image/jpeg';
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . addslashes($tl['ten_file']) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}
