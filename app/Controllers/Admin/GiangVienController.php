<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\GiangVienModel;
use App\Models\FacultyModel;

class GiangVienController extends Controller {
    private $giangVienModel;
    private $facultyModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->giangVienModel = new GiangVienModel();
        $this->facultyModel = new FacultyModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->giangVienModel->getTotalGiangViens($search);
        $total_pages = ceil($total / $per_page);
        $giang_viens = $this->giangVienModel->getGiangViens($search, $per_page, $offset);
        $faculties = $this->facultyModel->getAllFaculties();

        $this->view('admin/giang_vien/index', [
            'giang_viens' => $giang_viens,
            'faculties' => $faculties,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'page_title' => 'Quản lý Giảng viên',
            'active_menu' => 'dao_tao'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/giang-vien');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ma_gv = trim($_POST['ma_gv'] ?? '');
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $khoa_id = isset($_POST['khoa_id']) && $_POST['khoa_id'] !== '' ? (int)$_POST['khoa_id'] : null;
        $hoc_vi = trim($_POST['hoc_vi'] ?? '');
        $chuyen_nganh = trim($_POST['chuyen_nganh'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');

        if (empty($ma_gv) || empty($ho_ten)) {
            setFlash('danger', 'Mã giảng viên và Họ tên không được để trống.');
            $this->redirect('/admin/giang-vien');
        }

        // Kiểm tra trùng mã giảng viên
        $existing = $this->giangVienModel->getGiangVienByMa($ma_gv);
        if ($existing && ($id === 0 || $existing['id'] !== $id)) {
            setFlash('danger', 'Mã giảng viên này đã tồn tại trong hệ thống.');
            $this->redirect('/admin/giang-vien');
        }

        $data = [
            'ma_gv' => $ma_gv,
            'ho_ten' => $ho_ten,
            'khoa_id' => $khoa_id,
            'hoc_vi' => $hoc_vi,
            'chuyen_nganh' => $chuyen_nganh,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai
        ];

        if ($id > 0) {
            $res = $this->giangVienModel->updateGiangVien($id, $data);
            if ($res) {
                setFlash('success', 'Cập nhật giảng viên thành công.');
            } else {
                setFlash('danger', 'Cập nhật giảng viên thất bại.');
            }
        } else {
            $res = $this->giangVienModel->addGiangVien($data);
            if ($res) {
                setFlash('success', 'Thêm giảng viên mới thành công.');
            } else {
                setFlash('danger', 'Thêm giảng viên mới thất bại.');
            }
        }

        $this->redirect('/admin/giang-vien');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/giang-vien');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/giang-vien');
        }

        $res = $this->giangVienModel->deleteGiangVien($id);
        if ($res) {
            setFlash('success', 'Xóa giảng viên thành công.');
        } else {
            setFlash('danger', 'Xóa giảng viên thất bại.');
        }

        $this->redirect('/admin/giang-vien');
    }
}
