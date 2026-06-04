<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminScheduleModel;
use App\Models\AdminCourseModel;

class ScheduleController extends Controller {
    private $scheduleModel;
    private $courseModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->scheduleModel = new AdminScheduleModel();
        $this->courseModel = new AdminCourseModel();
    }

    public function index() {
        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        $hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $search = trim($_GET['search'] ?? '');
        
        $phongFilter = trim($_GET['phong_hoc'] ?? '');
        $gvFilter = trim($_GET['giang_vien'] ?? '');
        $lhpFilter = trim($_GET['lop_hoc_phan_id'] ?? '');

        // Lấy danh sách lịch học theo bộ lọc
        $list = $this->scheduleModel->getSchedules($hocKy, $namHoc, $search, $phongFilter, $gvFilter, $lhpFilter);
        
        // Tạo cấu trúc thời khóa biểu dạng lưới để View hiển thị chuyên nghiệp
        // Lưới: grid[phong][thu][tiet] = schedule_data
        $grid = [];
        $phongs = [];
        foreach ($list as $s) {
            $phong = $s['phong_hoc'] ?: 'Chưa xếp phòng';
            $phongs[$phong] = true;
            for ($t = $s['tiet_bat_dau']; $t < $s['tiet_bat_dau'] + $s['so_tiet']; $t++) {
                $grid[$phong][$s['thu']][$t] = $s;
            }
        }
        $phongsList = array_keys($phongs);
        sort($phongsList);

        // Lấy danh sách các lớp học phần để chọn
        $allClasses = $this->scheduleModel->getAllClasses($hocKy, $namHoc);
        $listNamHoc = $this->scheduleModel->getDistinctYears();
        $scheduleStats = $this->scheduleModel->getScheduleDashboardStats($hocKy, $namHoc);
        $unscheduledClasses = $this->scheduleModel->getUnscheduledClasses($hocKy, $namHoc);
        $roomUtilization = $this->scheduleModel->getRoomUtilization($hocKy, $namHoc);

        $item = null;
        if ($action === 'edit' && $id > 0) {
            $item = $this->scheduleModel->getScheduleById($id);
            if (!$item) {
                setFlash('danger', 'Không tìm thấy lịch học cần sửa.');
                $this->redirect("/admin/thoi-khoa-bieu?hoc_ky=$hocKy&nam_hoc=" . urlencode($namHoc));
            }
        }

        $this->view('admin/schedule/index', [
            'list' => $list,
            'grid' => $grid,
            'phongsList' => $phongsList,
            'hocKy' => $hocKy,
            'namHoc' => $namHoc,
            'search' => $search,
            'phongFilter' => $phongFilter,
            'gvFilter' => $gvFilter,
            'lhpFilter' => $lhpFilter,
            'allClasses' => $allClasses,
            'listNamHoc' => $listNamHoc,
            'scheduleStats' => $scheduleStats,
            'unscheduledClasses' => $unscheduledClasses,
            'roomUtilization' => $roomUtilization,
            'action' => $action,
            'item' => $item,
            'page_title' => 'Quản lý Thời khóa biểu',
            'active_menu' => 'thoi_khoa_bieu'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/thoi-khoa-bieu');
        }

        $id = (int)($_POST['id'] ?? 0);
        $lopHocPhanId = (int)($_POST['lop_hoc_phan_id'] ?? 0);
        $thu = max(2, min(8, (int)($_POST['thu'] ?? 2)));
        $tietBatDau = max(1, min(10, (int)($_POST['tiet_bat_dau'] ?? 1)));
        $soTiet = max(1, min(5, (int)($_POST['so_tiet'] ?? 3)));
        $phongHoc = trim($_POST['phong_hoc'] ?? '');
        
        $hocKy = max(1, min(8, (int)($_POST['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        
        $keepParams = [
            'hoc_ky' => $hocKy,
            'nam_hoc' => $namHoc,
            'search' => trim($_POST['search_keep'] ?? '')
        ];

        // Lấy thông tin lớp học phần để lấy giảng viên và ngày bắt đầu/kết thúc tự động
        $classInfo = $this->courseModel->getClassById($lopHocPhanId);

        if ($lopHocPhanId <= 0 || !$classInfo) {
            setFlash('danger', 'Vui lòng chọn một Lớp học phần hợp lệ.');
            $this->redirect('/admin/thoi-khoa-bieu?' . http_build_query($keepParams));
        }

        $giangVien = $classInfo['giang_vien'];
        $ngayBatDau = $classInfo['ngay_bat_dau'];
        $ngayKetThuc = $classInfo['ngay_ket_thuc'];

        if ($tietBatDau + $soTiet - 1 > 10) {
            setFlash('danger', 'Khoảng tiết học không hợp lệ. Lịch chỉ hỗ trợ tiết 1 đến tiết 10.');
        } else {
            // Kiểm tra trùng lịch tự động (Trùng phòng, giảng viên, lớp, ca)
            $conflictMsg = $this->scheduleModel->checkConflict($id, $phongHoc, $giangVien, $lopHocPhanId, $thu, $tietBatDau, $soTiet, $hocKy, $namHoc);
            
            if ($conflictMsg !== false) {
                setFlash('danger', '<strong>Không thể lưu dữ liệu! Trùng lịch học:</strong> ' . $conflictMsg);
            } else {
                $data = [
                    'lop_hoc_phan_id' => $lopHocPhanId,
                    'thu' => $thu,
                    'tiet_bd' => $tietBatDau,
                    'so_tiet' => $soTiet,
                    'phong' => $phongHoc,
                    'gv' => $giangVien,
                    'hk' => $hocKy,
                    'nh' => $namHoc,
                    'ngay_bat_dau' => $ngayBatDau,
                    'ngay_ket_thuc' => $ngayKetThuc
                ];

                if ($id > 0) {
                    if ($this->scheduleModel->updateSchedule($id, $data)) {
                        setFlash('success', 'Chỉnh sửa lịch học thành công.');
                    } else {
                        setFlash('danger', 'Lỗi hệ thống, không thể cập nhật lịch học.');
                    }
                } else {
                    if ($this->scheduleModel->insertSchedule($data)) {
                        setFlash('success', 'Thêm lịch học thành công.');
                    } else {
                        setFlash('danger', 'Lỗi hệ thống, không thể thêm lịch học.');
                    }
                }
            }
        }

        $url = '/admin/thoi-khoa-bieu?' . http_build_query($keepParams);
        $this->redirect($url);
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/thoi-khoa-bieu');
        }

        $id = (int)($_POST['id'] ?? 0);
        $keepParams = [
            'hoc_ky' => (int)($_POST['hoc_ky_keep'] ?? HOC_KY_HIEN_TAI),
            'nam_hoc' => trim($_POST['nam_hoc_keep'] ?? NAM_HOC_HIEN_TAI),
            'search' => trim($_POST['search_keep'] ?? '')
        ];

        if ($id > 0) {
            if ($this->scheduleModel->deleteSchedule($id)) {
                setFlash('success', 'Xóa lịch học khỏi hệ thống thành công.');
            } else {
                setFlash('danger', 'Lỗi hệ thống khi xóa lịch học.');
            }
        }

        $url = '/admin/thoi-khoa-bieu?' . http_build_query($keepParams);
        $this->redirect($url);
    }

    public function optimize() {
        $hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        $res = $this->scheduleModel->optimizeSchedules($hocKy, $namHoc);
        setFlash($res['status'], $res['message']);
        
        $this->redirect("/admin/thoi-khoa-bieu?hoc_ky=$hocKy&nam_hoc=" . urlencode($namHoc));
    }
}
