<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\FacultyModel;

class FacultyController extends Controller {
    private $facultyModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->facultyModel = new FacultyModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->facultyModel->getTotalFaculties($search);
        $total_pages = ceil($total / $per_page);
        $faculties = $this->facultyModel->getFaculties($search, $per_page, $offset);

        $this->view('admin/faculty/index', [
            'faculties' => $faculties,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'page_title' => 'Quản lý Khoa',
            'active_menu' => 'dao_tao'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/khoa');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ten_khoa = trim($_POST['ten_khoa'] ?? '');

        if (empty($ten_khoa)) {
            setFlash('danger', 'Tên khoa không được để trống.');
            $this->redirect('/admin/khoa');
        }

        // Kiểm tra trùng tên
        $existing = $this->facultyModel->getFacultyByName($ten_khoa);
        if ($existing && ($id === 0 || $existing['id'] !== $id)) {
            setFlash('danger', 'Tên khoa này đã tồn tại trong hệ thống.');
            $this->redirect('/admin/khoa');
        }

        if ($id > 0) {
            $res = $this->facultyModel->updateFaculty($id, $ten_khoa);
            if ($res) {
                setFlash('success', 'Cập nhật khoa thành công.');
            } else {
                setFlash('danger', 'Cập nhật khoa thất bại.');
            }
        } else {
            $res = $this->facultyModel->addFaculty($ten_khoa);
            if ($res) {
                setFlash('success', 'Thêm khoa mới thành công.');
            } else {
                setFlash('danger', 'Thêm khoa mới thất bại.');
            }
        }

        $this->redirect('/admin/khoa');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/khoa');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/khoa');
        }

        $res = $this->facultyModel->deleteFaculty($id);
        if ($res) {
            setFlash('success', 'Xóa khoa thành công.');
        } else {
            setFlash('danger', 'Xóa khoa thất bại.');
        }

        $this->redirect('/admin/khoa');
    }
}
