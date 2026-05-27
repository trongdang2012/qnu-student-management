<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\NotificationModel;

class NotificationController extends Controller {
    private $notificationModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->notificationModel = new NotificationModel();
    }

    public function index() {
        $notifications = $this->notificationModel->getAll();
        $warningStudents = $this->notificationModel->getWarningStudents();
        $tuitionWarnings = $this->notificationModel->getTuitionWarningStudents();
        $drlWarnings = $this->notificationModel->getTrainingPointWarningStudents();
        
        $this->view('admin/notifications/index', [
            'notifications' => $notifications,
            'warningStudents' => $warningStudents,
            'tuitionWarnings' => $tuitionWarnings,
            'drlWarnings' => $drlWarnings,
            'page_title' => 'Quản lý thông báo',
            'active_menu' => 'thong_bao'
        ]);
    }

    public function create() {
        $faculties = $this->notificationModel->getFaculties();
        $classes = $this->notificationModel->getClasses();

        $this->view('admin/notifications/create', [
            'faculties' => $faculties,
            'classes' => $classes,
            'page_title' => 'Gửi thông báo mới',
            'active_menu' => 'thong_bao'
        ]);
    }

    public function processCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tieu_de' => trim($_POST['tieu_de'] ?? ''),
                'noi_dung' => trim($_POST['noi_dung'] ?? ''),
                'loai' => $_POST['loai'] ?? 'info',
                'target_type' => $_POST['target_type'] ?? 'all',
                'target_value' => trim($_POST['target_value'] ?? '')
            ];

            if (empty($data['tieu_de']) || empty($data['noi_dung'])) {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ tiêu đề và nội dung.';
                $this->redirect('/admin/thong-bao/tao-moi');
            }

            try {
                $this->notificationModel->createNotification($data, $_SESSION['user_id']);
                $_SESSION['success'] = 'Gửi thông báo thành công.';
                $this->redirect('/admin/thong-bao');
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
                $this->redirect('/admin/thong-bao/tao-moi');
            }
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            try {
                $this->notificationModel->delete($id);
                $_SESSION['success'] = 'Đã xóa thông báo.';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
            }
        }
        $this->redirect('/admin/thong-bao');
    }
}
