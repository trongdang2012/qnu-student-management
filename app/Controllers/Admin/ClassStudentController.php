<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\ClassStudentModel;
use App\Models\MajorModel;

class ClassStudentController extends Controller {
    private $classStudentModel;
    private $majorModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->classStudentModel = new ClassStudentModel();
        $this->majorModel = new MajorModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $filter_khoa = (int)($_GET['khoa_id'] ?? 0);
        $filter_nganh = (int)($_GET['nganh_id'] ?? 0);
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->classStudentModel->getTotalClasses($search, $filter_khoa, $filter_nganh);
        $total_pages = ceil($total / $per_page);
        $classes = $this->classStudentModel->getClasses($search, $per_page, $offset, $filter_khoa, $filter_nganh);

        // Lấy danh sách ngành và khoa để chọn
        $majors = $this->majorModel->getAllMajors();
        $faculties = (new \App\Models\FacultyModel())->getAllFaculties();

        $this->view('admin/class_student/index', [
            'classes' => $classes,
            'majors' => $majors,
            'faculties' => $faculties,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'filter_khoa' => $filter_khoa,
            'filter_nganh' => $filter_nganh,
            'page_title' => 'Quản lý Lớp sinh hoạt',
            'active_menu' => 'dao_tao'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-sinh-hoat');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ten_lop = trim($_POST['ten_lop'] ?? '');
        $nganh_id = isset($_POST['nganh_id']) ? (int)$_POST['nganh_id'] : 0;

        if (empty($ten_lop)) {
            setFlash('danger', 'Tên lớp không được để trống.');
            $this->redirect('/admin/lop-sinh-hoat');
        }

        if ($nganh_id <= 0) {
            setFlash('danger', 'Vui lòng chọn Ngành học.');
            $this->redirect('/admin/lop-sinh-hoat');
        }

        // Kiểm tra trùng tên lớp sinh hoạt
        $existing = $this->classStudentModel->getClassByName($ten_lop);
        if ($existing && ($id === 0 || $existing['id'] !== $id)) {
            setFlash('danger', 'Tên lớp sinh hoạt này đã tồn tại.');
            $this->redirect('/admin/lop-sinh-hoat');
        }

        if ($id > 0) {
            $res = $this->classStudentModel->updateClass($id, $ten_lop, $nganh_id);
            if ($res) {
                setFlash('success', 'Cập nhật lớp sinh hoạt thành công.');
            } else {
                setFlash('danger', 'Cập nhật lớp sinh hoạt thất bại.');
            }
        } else {
            $res = $this->classStudentModel->addClass($ten_lop, $nganh_id);
            if ($res) {
                setFlash('success', 'Thêm lớp sinh hoạt mới thành công.');
            } else {
                setFlash('danger', 'Thêm lớp sinh hoạt mới thất bại.');
            }
        }

        $this->redirect('/admin/lop-sinh-hoat');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-sinh-hoat');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/lop-sinh-hoat');
        }

        $res = $this->classStudentModel->deleteClass($id);
        if ($res) {
            setFlash('success', 'Xóa lớp sinh hoạt thành công.');
        } else {
            setFlash('danger', 'Xóa lớp sinh hoạt thất bại.');
        }

        $this->redirect('/admin/lop-sinh-hoat');
    }
}
