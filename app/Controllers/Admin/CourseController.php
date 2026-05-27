<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminCourseModel;

class CourseController extends Controller {
    private $courseModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->courseModel = new AdminCourseModel();
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
        $loai = trim($_GET['loai'] ?? '');
        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        $item = null;
        if ($action === 'edit' && $id > 0) {
            $item = $this->courseModel->getCourseById($id);
            if (!$item) {
                setFlash('danger', 'Không tìm thấy học phần cần sửa.');
                $this->redirect('/admin/hoc-phan');
            }
        }

        $list = $this->courseModel->getCourses($search, $hoc_ky, $loai);
        $totalCredits = array_sum(array_map(static fn($row) => (int)$row['so_tin_chi'], $list));

        $this->view('admin/course/index', [
            'list' => $list,
            'totalCredits' => $totalCredits,
            'search' => $search,
            'hocKyFilter' => $hoc_ky,
            'loaiFilter' => $loai,
            'action' => $action,
            'item' => $item,
            'page_title' => 'Quản lý Học phần',
            'active_menu' => 'hoc_phan'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hoc-phan');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ma_hp = strtoupper(trim($_POST['ma_hp'] ?? ''));
        $ten_hp = trim($_POST['ten_hp'] ?? '');
        $so_tin_chi = max(1, min(10, (int)($_POST['so_tin_chi'] ?? 3)));
        $loai = trim($_POST['loai'] ?? 'Bắt buộc');
        $hoc_ky = max(1, min(8, (int)($_POST['hoc_ky'] ?? 1)));
        $nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
        $search_keep = $_POST['search_keep'] ?? '';

        $validLoai = ['Bắt buộc', 'Tự chọn', 'Đại cương'];
        if (!in_array($loai, $validLoai, true)) {
            $loai = 'Bắt buộc';
        }

        if ($ma_hp === '' || $ten_hp === '') {
            setFlash('danger', 'Mã học phần và tên học phần không được để trống.');
        } else {
            if ($id > 0) {
                $exists = $this->courseModel->getCourseByCodeExceptId($ma_hp, $id);
                if ($exists) {
                    setFlash('danger', 'Mã học phần đã tồn tại. Vui lòng dùng mã khác.');
                } else {
                    $this->courseModel->updateCourse($id, [
                        'ma_hp' => $ma_hp, 'ten_hp' => $ten_hp, 'so_tin_chi' => $so_tin_chi,
                        'loai' => $loai, 'hoc_ky' => $hoc_ky, 'nien_khoa' => $nien_khoa
                    ]);
                    setFlash('success', 'Cập nhật học phần thành công.');
                }
            } else {
                $exists = $this->courseModel->getCourseByCode($ma_hp);
                if ($exists) {
                    setFlash('danger', 'Mã học phần đã tồn tại. Vui lòng dùng mã khác.');
                } else {
                    $this->courseModel->addCourse([
                        'ma_hp' => $ma_hp, 'ten_hp' => $ten_hp, 'so_tin_chi' => $so_tin_chi,
                        'loai' => $loai, 'hoc_ky' => $hoc_ky, 'nien_khoa' => $nien_khoa
                    ]);
                    setFlash('success', 'Thêm học phần thành công.');
                }
            }
        }

        $url = '/admin/hoc-phan';
        if ($search_keep !== '') {
            $url .= '?search=' . urlencode($search_keep);
        }
        $this->redirect($url);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hoc-phan');
        }

        $id = (int)($_POST['id'] ?? 0);
        $search_keep = $_POST['search_keep'] ?? '';

        if ($id > 0) {
            if ($this->courseModel->deleteCourse($id)) {
                setFlash('success', 'Xóa học phần thành công.');
            } else {
                setFlash('danger', 'Không thể xóa học phần đang được dùng trong dữ liệu khác.');
            }
        }

        $url = '/admin/hoc-phan';
        if ($search_keep !== '') {
            $url .= '?search=' . urlencode($search_keep);
        }
        $this->redirect($url);
    }
}
