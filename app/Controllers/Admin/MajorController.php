<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\MajorModel;
use App\Models\FacultyModel;

class MajorController extends Controller {
    private $majorModel;
    private $facultyModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->majorModel = new MajorModel();
        $this->facultyModel = new FacultyModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $filter_khoa = (int)($_GET['khoa_id'] ?? 0);
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->majorModel->getTotalMajors($search, $filter_khoa);
        $total_pages = ceil($total / $per_page);
        $majors = $this->majorModel->getMajors($search, $per_page, $offset, $filter_khoa);

        // Lấy toàn bộ danh sách khoa để chọn khi thêm/sửa và lọc
        $faculties = $this->facultyModel->getAllFaculties();

        $this->view('admin/major/index', [
            'majors' => $majors,
            'faculties' => $faculties,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'filter_khoa' => $filter_khoa,
            'page_title' => 'Quản lý Ngành học',
            'active_menu' => 'dao_tao'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/nganh');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ten_nganh = trim($_POST['ten_nganh'] ?? '');
        $khoa_id = isset($_POST['khoa_id']) ? (int)$_POST['khoa_id'] : 0;

        if (empty($ten_nganh)) {
            setFlash('danger', 'Tên ngành không được để trống.');
            $this->redirect('/admin/nganh');
        }

        if ($khoa_id <= 0) {
            setFlash('danger', 'Vui lòng chọn Khoa trực thuộc.');
            $this->redirect('/admin/nganh');
        }

        // Kiểm tra trùng tên ngành trong cùng khoa
        $existing = $this->majorModel->getMajorByNameAndFaculty($ten_nganh, $khoa_id);
        if ($existing && ($id === 0 || $existing['id'] !== $id)) {
            setFlash('danger', 'Tên ngành này đã tồn tại trong Khoa đã chọn.');
            $this->redirect('/admin/nganh');
        }

        if ($id > 0) {
            $res = $this->majorModel->updateMajor($id, $ten_nganh, $khoa_id);
            if ($res) {
                setFlash('success', 'Cập nhật ngành thành công.');
            } else {
                setFlash('danger', 'Cập nhật ngành thất bại.');
            }
        } else {
            $res = $this->majorModel->addMajor($ten_nganh, $khoa_id);
            if ($res) {
                setFlash('success', 'Thêm ngành mới thành công.');
            } else {
                setFlash('danger', 'Thêm ngành mới thất bại.');
            }
        }

        $this->redirect('/admin/nganh');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/nganh');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/nganh');
        }

        $res = $this->majorModel->deleteMajor($id);
        if ($res) {
            setFlash('success', 'Xóa ngành thành công.');
        } else {
            setFlash('danger', 'Xóa ngành thất bại.');
        }

        $this->redirect('/admin/nganh');
    }
}
