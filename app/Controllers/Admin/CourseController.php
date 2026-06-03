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
        $khoa = trim($_GET['khoa'] ?? '');
        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);
        
        // Phân trang
        $limit = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $item = null;
        if ($action === 'edit' && $id > 0) {
            $item = $this->courseModel->getCourseById($id);
            if (!$item) {
                setFlash('danger', 'Không tìm thấy học phần cần sửa.');
                $this->redirect('/admin/hoc-phan');
            }
        }

        $list = $this->courseModel->getCourses($search, $hoc_ky, $loai, $khoa, $limit, $offset);
        $totalItems = $this->courseModel->countCourses($search, $hoc_ky, $loai, $khoa);
        $totalPages = (int)ceil($totalItems / $limit);

        // Lấy danh sách các môn học để làm môn tiên quyết gợi ý
        $allCoursesForPrereq = $this->courseModel->getCourses('', 0, '', '', 1000, 0);
        $nganhList = $this->courseModel->getNganhListInCtdt();

        $this->view('admin/course/index', [
            'list' => $list,
            'allCoursesForPrereq' => $allCoursesForPrereq,
            'nganhList' => $nganhList,
            'search' => $search,
            'hocKyFilter' => $hoc_ky,
            'loaiFilter' => $loai,
            'khoaFilter' => $khoa,
            'action' => $action,
            'item' => $item,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
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
        $so_tin_chi = (int)($_POST['so_tin_chi'] ?? 3);
        $so_tiet_ly_thuyet = (int)($_POST['so_tiet_ly_thuyet'] ?? 0);
        $so_tiet_thuc_hanh = (int)($_POST['so_tiet_thuc_hanh'] ?? 0);
        $khoa_phu_trach = trim($_POST['khoa_phu_trach'] ?? '');
        $ma_hp_tien_quyet = trim($_POST['ma_hp_tien_quyet'] ?? '');
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $trang_thai_hoat_dong = isset($_POST['trang_thai_hoat_dong']) ? 1 : 0;
        
        $loai = trim($_POST['loai'] ?? 'Bắt buộc');
        $hoc_ky = max(1, min(8, (int)($_POST['hoc_ky'] ?? 1)));
        $nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
        $search_keep = $_POST['search_keep'] ?? '';

        $validLoai = ['Bắt buộc', 'Tự chọn', 'Đại cương'];
        if (!in_array($loai, $validLoai, true)) {
            $loai = 'Bắt buộc';
        }

        if ($ma_hp === '' || $ten_hp === '' || $khoa_phu_trach === '') {
            setFlash('danger', 'Các trường bắt buộc: Mã học phần, Tên học phần, Khoa/Bộ môn phụ trách không được để trống.');
        } elseif ($so_tin_chi <= 0) {
            setFlash('danger', 'Số tín chỉ phải lớn hơn 0.');
        } else {
            if ($id > 0) {
                // Đang sửa học phần
                $oldItem = $this->courseModel->getCourseById($id);
                $hasClasses = $this->courseModel->hasClasses($id);
                
                if ($hasClasses && $oldItem['ma_hp'] !== $ma_hp) {
                    setFlash('danger', 'Không được phép sửa Mã học phần nếu đã có lớp học phần được tạo.');
                } else {
                    $exists = $this->courseModel->getCourseByCodeExceptId($ma_hp, $id);
                    if ($exists) {
                        setFlash('danger', 'Mã học phần đã tồn tại trên một học phần khác. Vui lòng dùng mã khác.');
                    } else {
                        $this->courseModel->updateCourse($id, [
                            'ma_hp' => $ma_hp, 
                            'ten_hp' => $ten_hp, 
                            'so_tin_chi' => $so_tin_chi,
                            'loai' => $loai, 
                            'hoc_ky' => $hoc_ky, 
                            'nien_khoa' => $nien_khoa,
                            'so_tiet_ly_thuyet' => $so_tiet_ly_thuyet,
                            'so_tiet_thuc_hanh' => $so_tiet_thuc_hanh,
                            'khoa_phu_trach' => $khoa_phu_trach,
                            'ma_hp_tien_quyet' => $ma_hp_tien_quyet !== '' ? $ma_hp_tien_quyet : null,
                            'mo_ta' => $mo_ta,
                            'trang_thai_hoat_dong' => $trang_thai_hoat_dong
                        ]);
                        setFlash('success', 'Cập nhật học phần thành công.');
                    }
                }
            } else {
                // Thêm học phần mới
                $exists = $this->courseModel->getCourseByCode($ma_hp);
                if ($exists) {
                    setFlash('danger', 'Mã học phần đã tồn tại. Vui lòng dùng mã khác.');
                } else {
                    $this->courseModel->addCourse([
                        'ma_hp' => $ma_hp, 
                        'ten_hp' => $ten_hp, 
                        'so_tin_chi' => $so_tin_chi,
                        'loai' => $loai, 
                        'hoc_ky' => $hoc_ky, 
                        'nien_khoa' => $nien_khoa,
                        'so_tiet_ly_thuyet' => $so_tiet_ly_thuyet,
                        'so_tiet_thuc_hanh' => $so_tiet_thuc_hanh,
                        'khoa_phu_trach' => $khoa_phu_trach,
                        'ma_hp_tien_quyet' => $ma_hp_tien_quyet !== '' ? $ma_hp_tien_quyet : null,
                        'mo_ta' => $mo_ta,
                        'trang_thai_hoat_dong' => $trang_thai_hoat_dong
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
            if ($this->courseModel->hasClasses($id)) {
                setFlash('danger', 'Không thể xóa học phần này vì đã tồn tại lớp học phần liên kết.');
            } elseif ($this->courseModel->deleteCourse($id)) {
                setFlash('success', 'Xóa học phần thành công.');
            } else {
                setFlash('danger', 'Có lỗi xảy ra khi xóa học phần.');
            }
        }

        $url = '/admin/hoc-phan';
        if ($search_keep !== '') {
            $url .= '?search=' . urlencode($search_keep);
        }
        $this->redirect($url);
    }

    public function duplicateCtdt() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hoc-phan');
        }

        $nganhNguon = trim($_POST['nganh_nguon'] ?? '');
        $nganhDich = trim($_POST['nganh_dich'] ?? '');

        if (empty($nganhNguon) || empty($nganhDich)) {
            setFlash('danger', 'Vui lòng chọn ngành nguồn và nhập tên ngành đích.');
        } else {
            $result = $this->courseModel->duplicateCtdt($nganhNguon, $nganhDich);
            if ($result) {
                setFlash('success', "Nhân bản CTĐT từ ngành \"$nganhNguon\" sang \"$nganhDich\" thành công.");
            } else {
                setFlash('danger', "Có lỗi xảy ra hoặc ngành nguồn không có dữ liệu CTĐT.");
            }
        }
        $this->redirect('/admin/hoc-phan');
    }
}
