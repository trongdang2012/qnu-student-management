<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminCourseModel;
use App\Models\AdminScheduleModel;

class ClassController extends Controller {
    private $courseModel;
    private $scheduleModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->courseModel = new AdminCourseModel();
        $this->scheduleModel = new AdminScheduleModel();
    }

    public function index() {
        $search = trim($_GET['search'] ?? '');
        $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
        $giang_vien = trim($_GET['giang_vien'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        // Phân trang
        $limit = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $item = null;
        if ($action === 'edit' && $id > 0) {
            $item = $this->courseModel->getClassById($id);
            if (!$item) {
                setFlash('danger', 'Không tìm thấy lớp học phần cần sửa.');
                $this->redirect('/admin/lop-hoc-phan');
            }
        }

        $list = $this->courseModel->getClasses($search, $hoc_ky, $giang_vien, $khoa, $limit, $offset);
        $totalItems = $this->courseModel->countClasses($search, $hoc_ky, $giang_vien, $khoa);
        $totalPages = (int)ceil($totalItems / $limit);

        // Lấy danh sách các học phần hoạt động để làm dropdown chọn
        $allCourses = $this->courseModel->getCourses('', 0, '', '', 1000, 0);

        $this->view('admin/class/index', [
            'list' => $list,
            'allCourses' => $allCourses,
            'search' => $search,
            'hocKyFilter' => $hoc_ky,
            'giangVienFilter' => $giang_vien,
            'khoaFilter' => $khoa,
            'action' => $action,
            'item' => $item,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'page_title' => 'Quản lý Lớp học phần',
            'active_menu' => 'lop_hoc_phan'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ma_lop_hp = strtoupper(trim($_POST['ma_lop_hp'] ?? ''));
        $hoc_phan_id = (int)($_POST['hoc_phan_id'] ?? 0);
        $giang_vien = trim($_POST['giang_vien'] ?? '');
        $hoc_ky = max(1, min(8, (int)($_POST['hoc_ky'] ?? 1)));
        $nam_hoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $si_so_toi_da = (int)($_POST['si_so_toi_da'] ?? 80);
        $ngay_bat_dau = trim($_POST['ngay_bat_dau'] ?? '');
        $ngay_ket_thuc = trim($_POST['ngay_ket_thuc'] ?? '');
        $trang_thai_mo_lop = trim($_POST['trang_thai_mo_lop'] ?? 'Đang mở');
        
        $search_keep = $_POST['search_keep'] ?? '';

        if ($ma_lop_hp === '' || $hoc_phan_id <= 0 || $giang_vien === '' || $ngay_bat_dau === '' || $ngay_ket_thuc === '') {
            setFlash('danger', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
        } elseif ($si_so_toi_da <= 0) {
            setFlash('danger', 'Sĩ số tối đa phải lớn hơn 0.');
        } else {
            if ($id > 0) {
                // Sửa thông tin lớp học phần
                $exists = $this->courseModel->getClassByCodeExceptId($ma_lop_hp, $id);
                if ($exists) {
                    setFlash('danger', 'Mã lớp học phần đã tồn tại trên một lớp khác. Vui lòng dùng mã khác.');
                } else {
                    // Đối với chỉnh sửa, chỉ cho phép chỉnh sửa: giảng viên, sĩ số tối đa, thời gian học, trạng thái lớp theo đúng yêu cầu
                    $this->courseModel->updateClass($id, [
                        'giang_vien' => $giang_vien,
                        'si_so_toi_da' => $si_so_toi_da,
                        'ngay_bat_dau' => $ngay_bat_dau,
                        'ngay_ket_thuc' => $ngay_ket_thuc,
                        'trang_thai_mo_lop' => $trang_thai_mo_lop
                    ]);
                    setFlash('success', 'Cập nhật thông tin lớp học phần thành công.');
                }
            } else {
                // Tạo lớp học phần mới
                $exists = $this->courseModel->getClassByCode($ma_lop_hp);
                if ($exists) {
                    setFlash('danger', 'Mã lớp học phần đã tồn tại. Vui lòng chọn mã khác.');
                } else {
                    $this->courseModel->addClass([
                        'ma_lop_hp' => $ma_lop_hp,
                        'hoc_phan_id' => $hoc_phan_id,
                        'giang_vien' => $giang_vien,
                        'hoc_ky' => $hoc_ky,
                        'nam_hoc' => $nam_hoc,
                        'si_so_toi_da' => $si_so_toi_da,
                        'ngay_bat_dau' => $ngay_bat_dau,
                        'ngay_ket_thuc' => $ngay_ket_thuc,
                        'trang_thai_mo_lop' => $trang_thai_mo_lop
                    ]);
                    setFlash('success', 'Tạo lớp học phần mới thành công.');
                }
            }
        }

        $url = '/admin/lop-hoc-phan';
        if ($search_keep !== '') {
            $url .= '?search=' . urlencode($search_keep);
        }
        $this->redirect($url);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $id = (int)($_POST['id'] ?? 0);
        $search_keep = $_POST['search_keep'] ?? '';

        if ($id > 0) {
            if ($this->courseModel->hasStudentsRegistered($id)) {
                setFlash('danger', 'Không thể xóa lớp học phần này vì đã có sinh viên đăng ký học.');
            } elseif ($this->courseModel->deleteClass($id)) {
                setFlash('success', 'Xóa lớp học phần thành công.');
            } else {
                setFlash('danger', 'Lỗi hệ thống khi xóa lớp học phần.');
            }
        }

        $url = '/admin/lop-hoc-phan';
        if ($search_keep !== '') {
            $url .= '?search=' . urlencode($search_keep);
        }
        $this->redirect($url);
    }

    public function optimize() {
        $hk = (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI);
        $nh = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        $res = $this->scheduleModel->optimizeSchedules($hk, $nh);
        setFlash($res['status'], $res['message']);
        
        $this->redirect('/admin/lop-hoc-phan');
    }
}
