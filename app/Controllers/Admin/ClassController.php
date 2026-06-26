<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminCourseModel;
use App\Models\AdminScheduleModel;
use App\Models\FacultyModel;
use App\Models\MajorModel;
use App\Models\GiangVienModel;
use App\Models\PhongHocModel;
use App\Models\NotificationModel;

class ClassController extends Controller {
    private $courseModel;
    private $scheduleModel;
    private $facultyModel;
    private $majorModel;
    private $giangVienModel;
    private $phongHocModel;
    private $notificationModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->courseModel = new AdminCourseModel();
        $this->scheduleModel = new AdminScheduleModel();
        $this->facultyModel = new FacultyModel();
        $this->majorModel = new MajorModel();
        $this->giangVienModel = new GiangVienModel();
        $this->phongHocModel = new PhongHocModel();
        $this->notificationModel = new NotificationModel();
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

        // Nạp thêm danh sách để chọn
        $allCourses = $this->courseModel->getCourses('', 0, '', '', 1000, 0);
        $faculties = $this->facultyModel->getAllFaculties();
        $majors = $this->majorModel->getAllMajors();
        $giangViens = $this->giangVienModel->getAllGiangViens();
        $phongHocs = $this->phongHocModel->getAllPhongHocs();
        $nganhList = $this->courseModel->getNganhListInCtdt();
        $classStats = $this->courseModel->getClassDashboardStats($hoc_ky, NAM_HOC_HIEN_TAI);
        $classAlerts = $this->courseModel->getClassOperationalAlerts($hoc_ky, NAM_HOC_HIEN_TAI);

        $this->view('admin/class/index', [
            'list' => $list,
            'allCourses' => $allCourses,
            'faculties' => $faculties,
            'majors' => $majors,
            'giangViens' => $giangViens,
            'phongHocs' => $phongHocs,
            'nganhList' => $nganhList,
            'classStats' => $classStats,
            'classAlerts' => $classAlerts,
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
        $giang_vien_id = isset($_POST['giang_vien_id']) && $_POST['giang_vien_id'] !== '' ? (int)$_POST['giang_vien_id'] : null;
        $phong_hoc_id = isset($_POST['phong_hoc_id']) && $_POST['phong_hoc_id'] !== '' ? (int)$_POST['phong_hoc_id'] : null;
        
        $hoc_ky = max(1, min(8, (int)($_POST['hoc_ky'] ?? 1)));
        $nam_hoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $si_so_toi_da = (int)($_POST['si_so_toi_da'] ?? 80);
        $ngay_bat_dau = trim($_POST['ngay_bat_dau'] ?? '');
        $ngay_ket_thuc = trim($_POST['ngay_ket_thuc'] ?? '');
        $trang_thai_mo_lop = trim($_POST['trang_thai_mo_lop'] ?? 'Đang mở');

        $ngay_bat_dau_dk = trim($_POST['ngay_bat_dau_dk'] ?? '');
        $ngay_ket_thuc_dk = trim($_POST['ngay_ket_thuc_dk'] ?? '');

        $ngay_bat_dau_dk = $ngay_bat_dau_dk !== '' ? $ngay_bat_dau_dk : null;
        $ngay_ket_thuc_dk = $ngay_ket_thuc_dk !== '' ? $ngay_ket_thuc_dk : null;

        // Lịch học
        $thu = isset($_POST['thu']) && $_POST['thu'] !== '' ? (int)$_POST['thu'] : 0;
        $tiet_bat_dau = isset($_POST['tiet_bat_dau']) && $_POST['tiet_bat_dau'] !== '' ? (int)$_POST['tiet_bat_dau'] : 0;
        $so_tiet = isset($_POST['so_tiet']) && $_POST['so_tiet'] !== '' ? (int)$_POST['so_tiet'] : 0;

        $search_keep = $_POST['search_keep'] ?? '';

        if ($ma_lop_hp === '' || $hoc_phan_id <= 0 || $ngay_bat_dau === '' || $ngay_ket_thuc === '') {
            setFlash('danger', 'Vui lòng điền đầy đủ các thông tin bắt buộc.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        // Lấy tên giảng viên và phòng học để tương thích dữ liệu cũ
        $giangVienTen = '';
        if ($giang_vien_id > 0) {
            $gv = $this->giangVienModel->getGiangVienById($giang_vien_id);
            if ($gv) $giangVienTen = $gv['ho_ten'];
        }

        $phongHocTen = '';
        if ($phong_hoc_id > 0) {
            $ph = $this->phongHocModel->getPhongHocById($phong_hoc_id);
            if ($ph) $phongHocTen = $ph['ten_phong'];
        }

        // Kiểm tra lịch trùng nếu xếp lịch học
        if ($thu > 0 && $tiet_bat_dau > 0 && $so_tiet > 0 && ($giang_vien_id > 0 || $phong_hoc_id > 0)) {
            // Lấy schedule_id cũ nếu có để loại trừ
            $schedId = 0;
            if ($id > 0) {
                $oldClass = $this->courseModel->getClassById($id);
                if ($oldClass && isset($oldClass['schedule_id'])) {
                    $schedId = (int)$oldClass['schedule_id'];
                }
            }
            
            $conflictMsg = $this->scheduleModel->checkClassScheduleConflict($schedId, $phong_hoc_id, $giang_vien_id, $thu, $tiet_bat_dau, $so_tiet, $hoc_ky, $nam_hoc);
            if ($conflictMsg) {
                setFlash('danger', '<strong>Lỗi xếp lịch học:</strong> ' . $conflictMsg);
                $this->redirect('/admin/lop-hoc-phan');
            }
        }

        $classData = [
            'ma_lop_hp' => $ma_lop_hp,
            'hoc_phan_id' => $hoc_phan_id,
            'giang_vien' => $giangVienTen,
            'giang_vien_id' => $giang_vien_id,
            'phong_hoc_id' => $phong_hoc_id,
            'hoc_ky' => $hoc_ky,
            'nam_hoc' => $nam_hoc,
            'si_so_toi_da' => $si_so_toi_da,
            'ngay_bat_dau' => $ngay_bat_dau,
            'ngay_ket_thuc' => $ngay_ket_thuc,
            'trang_thai_mo_lop' => $trang_thai_mo_lop,
            'ngay_bat_dau_dk' => $ngay_bat_dau_dk,
            'ngay_ket_thuc_dk' => $ngay_ket_thuc_dk
        ];

        if ($id > 0) {
            $exists = $this->courseModel->getClassByCodeExceptId($ma_lop_hp, $id);
            if ($exists) {
                setFlash('danger', 'Mã lớp học phần đã tồn tại trên một lớp khác.');
            } else {
                $this->courseModel->updateClass($id, $classData);
                
                // Đồng bộ cập nhật hoặc tạo mới thời khóa biểu
                $oldClass = $this->courseModel->getClassById($id);
                if ($thu > 0 && $tiet_bat_dau > 0 && $so_tiet > 0) {
                    $schedData = [
                        'lop_hoc_phan_id' => $id,
                        'thu' => $thu,
                        'tiet_bd' => $tiet_bat_dau,
                        'so_tiet' => $so_tiet,
                        'phong' => $phongHocTen,
                        'gv' => $giangVienTen,
                        'hk' => $hoc_ky,
                        'nh' => $nam_hoc,
                        'ngay_bat_dau' => $ngay_bat_dau,
                        'ngay_ket_thuc' => $ngay_ket_thuc
                    ];
                    
                    // Cập nhật cả giang_vien_id và phong_hoc_id vào thoi_khoa_bieu
                    $db = \App\Core\Database::getInstance();
                    
                    if (isset($oldClass['schedule_id']) && $oldClass['schedule_id'] > 0) {
                        $this->scheduleModel->updateSchedule($oldClass['schedule_id'], $schedData);
                        $db->query("UPDATE thoi_khoa_bieu SET giang_vien_id = :gv_id, phong_hoc_id = :room_id WHERE id = :id", [
                            'gv_id' => $giang_vien_id, 'room_id' => $phong_hoc_id, 'id' => $oldClass['schedule_id']
                        ]);
                    } else {
                        $this->scheduleModel->insertSchedule($schedData);
                        $newSchedId = $db->lastInsertId();
                        $db->query("UPDATE thoi_khoa_bieu SET giang_vien_id = :gv_id, phong_hoc_id = :room_id WHERE id = :id", [
                            'gv_id' => $giang_vien_id, 'room_id' => $phong_hoc_id, 'id' => $newSchedId
                        ]);
                    }
                } else {
                    // Nếu admin xóa lịch (để trống thứ/tiết) thì xóa lịch TKB
                    if (isset($oldClass['schedule_id']) && $oldClass['schedule_id'] > 0) {
                        $this->scheduleModel->deleteSchedule($oldClass['schedule_id']);
                    }
                }
                setFlash('success', 'Cập nhật thông tin lớp học phần thành công.');
            }
        } else {
            $exists = $this->courseModel->getClassByCode($ma_lop_hp);
            if ($exists) {
                setFlash('danger', 'Mã lớp học phần đã tồn tại.');
            } else {
                $this->courseModel->addClass($classData);
                $newClassId = \App\Core\Database::getInstance()->lastInsertId();
                
                // Nếu có lịch học, tạo TKB
                if ($thu > 0 && $tiet_bat_dau > 0 && $so_tiet > 0) {
                    $schedData = [
                        'lop_hoc_phan_id' => $newClassId,
                        'thu' => $thu,
                        'tiet_bd' => $tiet_bat_dau,
                        'so_tiet' => $so_tiet,
                        'phong' => $phongHocTen,
                        'gv' => $giangVienTen,
                        'hk' => $hoc_ky,
                        'nh' => $nam_hoc,
                        'ngay_bat_dau' => $ngay_bat_dau,
                        'ngay_ket_thuc' => $ngay_ket_thuc
                    ];
                    $this->scheduleModel->insertSchedule($schedData);
                    $newSchedId = \App\Core\Database::getInstance()->lastInsertId();
                    \App\Core\Database::getInstance()->query("UPDATE thoi_khoa_bieu SET giang_vien_id = :gv_id, phong_hoc_id = :room_id WHERE id = :id", [
                        'gv_id' => $giang_vien_id, 'room_id' => $phong_hoc_id, 'id' => $newSchedId
                    ]);
                }
                setFlash('success', 'Tạo lớp học phần mới thành công.');
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

    public function autoGenerate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $khoaId = (int)($_POST['khoa_id'] ?? 0);
        $nganhId = (int)($_POST['nganh_id'] ?? 0);
        $hocKyHocVu = max(1, min(3, (int)($_POST['hoc_ky_hoc_vu'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        if ($khoaId <= 0 || $nganhId <= 0) {
            setFlash('danger', 'Vui lòng chọn đầy đủ Khoa và Ngành để sinh lớp.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        $res = $this->courseModel->autoGenerateAndSchedule($khoaId, $nganhId, $hocKyHocVu, $namHoc);
        setFlash($res['status'], $res['message']);

        $this->redirect('/admin/lop-hoc-phan');
    }

    public function batchOpen() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $khoaId = (int)($_POST['khoa_id'] ?? 0);
        $nganhId = (int)($_POST['nganh_id'] ?? 0);
        $ngay_bat_dau_dk = trim($_POST['ngay_bat_dau_dk'] ?? '');
        $ngay_ket_thuc_dk = trim($_POST['ngay_ket_thuc_dk'] ?? '');

        if ($nganhId <= 0) {
            setFlash('danger', 'Vui lòng chọn ngành để mở đợt đăng ký.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        if (empty($ngay_bat_dau_dk) || empty($ngay_ket_thuc_dk)) {
            setFlash('danger', 'Vui lòng nhập đầy đủ thời gian bắt đầu và kết thúc đăng ký.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        $db = \App\Core\Database::getInstance();
        
        // Cập nhật tất cả các lớp học phần thuộc ngành này (thông qua học phần của ngành đó)
        $sql = "UPDATE lop_hoc_phan l
                JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
                JOIN ctdt_chi_tiet ctdt ON hp.id = ctdt.hoc_phan_id
                SET l.ngay_bat_dau_dk = :bd, l.ngay_ket_thuc_dk = :kt, l.trang_thai_mo_lop = 'Đang mở'
                WHERE ctdt.nganh_id = :nganh_id AND l.nam_hoc = :nam_hoc";
        
        $res = $db->query($sql, [
            'bd' => $ngay_bat_dau_dk,
            'kt' => $ngay_ket_thuc_dk,
            'nganh_id' => $nganhId,
            'nam_hoc' => NAM_HOC_HIEN_TAI
        ]);

        // Gửi thông báo đến toàn bộ sinh viên trong ngành
        $nganh = $this->majorModel->getMajorById($nganhId);
        $nganhTen = $nganh ? $nganh['ten_nganh'] : '';
        
        $students = $db->fetchAll("SELECT s.id FROM sinh_vien s JOIN lop_sinh_hoat lsh ON s.lop_sinh_hoat_id = lsh.id WHERE lsh.nganh_id = :nganh_id", ['nganh_id' => $nganhId]);
        
        if (!empty($students)) {
            $notificationTitle = "Mở đợt đăng ký học phần mới - Ngành " . $nganhTen;
            $notificationContent = "Thông báo: Hệ thống đã mở cổng đăng ký học phần cho Ngành " . $nganhTen . ". Thời gian đăng ký từ " . date('d/m/Y H:i', strtotime($ngay_bat_dau_dk)) . " đến " . date('d/m/Y H:i', strtotime($ngay_ket_thuc_dk)) . ". Vui lòng vào cổng Đăng ký học phần để đăng ký môn học.";
            
            foreach ($students as $s) {
                // Tạo thông báo cho từng sinh viên
                $db->query("INSERT INTO thong_bao (tieu_de, noi_dung, target_type, target_id, is_read, created_at)
                            VALUES (:title, :content, 'student', :sv_id, 0, NOW())", [
                    'title' => $notificationTitle,
                    'content' => $notificationContent,
                    'sv_id' => $s['id']
                ]);
            }
        }

        setFlash('success', '✓ Đã mở đợt đăng ký học phần hàng loạt cho ngành ' . $nganhTen . ' và gửi thông báo đến các sinh viên thành công.');
        $this->redirect('/admin/lop-hoc-phan');
    }

    public function batchOpenSelected() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $classIds = $_POST['class_ids'] ?? [];
        $ngay_bat_dau_dk = trim($_POST['ngay_bat_dau_dk_selected'] ?? '');
        $ngay_ket_thuc_dk = trim($_POST['ngay_ket_thuc_dk_selected'] ?? '');

        if (empty($classIds)) {
            setFlash('danger', 'Vui lòng tích chọn ít nhất một lớp học phần.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        if (empty($ngay_bat_dau_dk) || empty($ngay_ket_thuc_dk)) {
            setFlash('danger', 'Vui lòng nhập đầy đủ thời gian bắt đầu và kết thúc đăng ký.');
            $this->redirect('/admin/lop-hoc-phan');
        }

        $db = \App\Core\Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        
        // Cập nhật các lớp được chọn
        $sql = "UPDATE lop_hoc_phan 
                SET ngay_bat_dau_dk = ?, ngay_ket_thuc_dk = ?, trang_thai_mo_lop = 'Đang mở'
                WHERE id IN ($placeholders)";
        
        $params = array_merge([$ngay_bat_dau_dk, $ngay_ket_thuc_dk], $classIds);
        $db->query($sql, $params);

        // Gửi thông báo đến sinh viên đã được định danh thuộc các ngành liên quan đến lớp
        // Lấy danh sách ngành của các lớp này để thông báo
        $nganhs = $db->fetchAll("
            SELECT DISTINCT ctdt.nganh_id, n.ten_nganh
            FROM lop_hoc_phan l
            JOIN hoc_phan hp ON l.hoc_phan_id = hp.id
            JOIN ctdt_chi_tiet ctdt ON hp.id = ctdt.hoc_phan_id
            JOIN nganh n ON ctdt.nganh_id = n.id
            WHERE l.id IN ($placeholders)
        ", $classIds);

        foreach ($nganhs as $ng) {
            $students = $db->fetchAll("SELECT s.id FROM sinh_vien s JOIN lop_sinh_hoat lsh ON s.lop_sinh_hoat_id = lsh.id WHERE lsh.nganh_id = :nganh_id", ['nganh_id' => $ng['nganh_id']]);
            $notificationTitle = "Mở đăng ký lớp học phần đặc biệt";
            $notificationContent = "Thông báo: Admin đã mở đăng ký một số lớp học phần đặc biệt thuộc Ngành " . $ng['ten_nganh'] . ". Hạn đăng ký từ " . date('d/m H:i', strtotime($ngay_bat_dau_dk)) . " đến " . date('d/m H:i', strtotime($ngay_ket_thuc_dk)) . ".";
            
            foreach ($students as $s) {
                $db->query("INSERT INTO thong_bao (tieu_de, noi_dung, target_type, target_id, is_read, created_at)
                            VALUES (:title, :content, 'student', :sv_id, 0, NOW())", [
                    'title' => $notificationTitle,
                    'content' => $notificationContent,
                    'sv_id' => $s['id']
                ]);
            }
        }
        setFlash('success', '✓ Đã mở đợt đăng ký và hẹn giờ thành công cho ' . count($classIds) . ' lớp học phần được chọn.');
        $this->redirect('/admin/lop-hoc-phan');
    }

    public function scanAndCancel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $adminId = $_SESSION['user_id'] ?? 1;
        $res = $this->courseModel->scanAndCancelRegistration($adminId);
        
        setFlash($res['status'], $res['message']);
        $this->redirect('/admin/lop-hoc-phan');
    }
}
