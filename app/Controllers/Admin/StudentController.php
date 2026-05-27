<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminStudentModel;

class StudentController extends Controller {
    private $studentModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->studentModel = new AdminStudentModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $lop = trim($_GET['lop'] ?? '');
        $sort = trim($_GET['sort'] ?? 'ma_sv');
        $order = trim($_GET['order'] ?? 'asc');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->studentModel->getTotalStudents($search, $khoa, $lop);
        $total_pages = ceil($total / $per_page);
        $students = $this->studentModel->getStudents($search, $per_page, $offset, $khoa, $lop, $sort, $order);
        $facultiesClassesTree = $this->studentModel->getFacultiesAndClasses();

        $this->view('admin/student/index', [
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'total_pages' => $total_pages,
            'search' => $search,
            'khoa' => $khoa,
            'lop' => $lop,
            'sort' => $sort,
            'order' => $order,
            'facultiesClassesTree' => $facultiesClassesTree,
            'page_title' => 'Quản lý Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function add() {
        $this->view('admin/student/add', [
            'page_title' => 'Thêm Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function processAdd() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sinh-vien/add');
        }

        $ma_sv = trim($_POST['ma_sv'] ?? '');
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
        $gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');
        $khoa = trim($_POST['khoa'] ?? '');
        $nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
        $dia_chi = trim($_POST['dia_chi'] ?? '');

        if (empty($ma_sv) || empty($ho_ten) || empty($nganh) || empty($lop)) {
            setFlash('danger', 'Vui lòng điền các trường bắt buộc');
            $this->redirect('/admin/sinh-vien/add');
        }

        if ($this->studentModel->getStudentByMaSv($ma_sv)) {
            setFlash('danger', 'Mã sinh viên đã tồn tại');
            $this->redirect('/admin/sinh-vien/add');
        }

        $ngay_sinh_db = !empty($ngay_sinh) ? $ngay_sinh : null;

        $data = [
            'user_id' => 0,
            'ma_sv' => $ma_sv,
            'ho_ten' => $ho_ten,
            'ngay_sinh' => $ngay_sinh_db,
            'gioi_tinh' => $gioi_tinh,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'nganh' => $nganh,
            'lop' => $lop,
            'khoa' => $khoa,
            'nien_khoa' => $nien_khoa,
            'dia_chi' => $dia_chi,
            'trang_thai' => 'Đang học'
        ];

        try {
            $this->studentModel->addStudent($data);
            setFlash('success', 'Thêm sinh viên thành công!');
            $this->redirect('/admin/sinh-vien');
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/sinh-vien/add');
        }
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $student = $this->studentModel->getStudentById($id);

        if (!$student) {
            setFlash('danger', 'Sinh viên không tồn tại');
            $this->redirect('/admin/sinh-vien');
        }

        $this->view('admin/student/edit', [
            'student' => $student,
            'page_title' => 'Sửa Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function processEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sinh-vien');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
        $gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');
        $khoa = trim($_POST['khoa'] ?? '');
        $nien_khoa = trim($_POST['nien_khoa'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? '');
        $trang_thai = trim($_POST['trang_thai'] ?? 'Đang học');

        if (empty($ho_ten) || empty($nganh) || empty($lop) || empty($trang_thai)) {
            setFlash('danger', 'Vui lòng điền các trường bắt buộc');
            $this->redirect("/admin/sinh-vien/edit?id=$id");
        }

        $data = [
            'ho_ten' => $ho_ten,
            'ngay_sinh' => !empty($ngay_sinh) ? $ngay_sinh : null,
            'gioi_tinh' => $gioi_tinh,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'nganh' => $nganh,
            'lop' => $lop,
            'khoa' => $khoa,
            'nien_khoa' => $nien_khoa,
            'dia_chi' => $dia_chi,
            'trang_thai' => $trang_thai
        ];

        try {
            $this->studentModel->updateStudent($id, $data);
            setFlash('success', 'Cập nhật sinh viên thành công!');
            $this->redirect('/admin/sinh-vien');
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect("/admin/sinh-vien/edit?id=$id");
        }
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            if ($this->studentModel->deleteStudent($id)) {
                setFlash('success', 'Xóa sinh viên thành công!');
            } else {
                setFlash('danger', 'Sinh viên không tồn tại!');
            }
        }
        $this->redirect('/admin/sinh-vien');
    }
}
