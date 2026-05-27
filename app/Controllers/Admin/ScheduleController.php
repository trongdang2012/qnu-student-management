<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminScheduleModel;

class ScheduleController extends Controller {
    private $scheduleModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->scheduleModel = new AdminScheduleModel();
    }

    private function tkbOverlap($excludeId, $sinhVienId, $phongHoc, $thu, $tietBatDau, $soTiet, $hocKy, $namHoc) {
        $tietKetThuc = $tietBatDau + $soTiet - 1;

        $studentConflict = $this->scheduleModel->getStudentConflict($excludeId, $sinhVienId, $thu, $tietBatDau, $tietKetThuc, $hocKy, $namHoc);
        if ($studentConflict) {
            return 'Sinh viên đã có lịch ' . $studentConflict['ma_hp'] . ' trùng khoảng tiết này.';
        }

        if ($phongHoc !== '') {
            $roomConflict = $this->scheduleModel->getRoomConflict($excludeId, $phongHoc, $thu, $tietBatDau, $tietKetThuc, $hocKy, $namHoc);
            if ($roomConflict) {
                return 'Phòng ' . $phongHoc . ' đã có lịch trong khoảng tiết này.';
            }
        }

        return null;
    }

    public function index() {
        $action = $_GET['action'] ?? 'list';
        $id = (int)($_GET['id'] ?? 0);

        $hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $search = trim($_GET['search'] ?? '');

        $list = $this->scheduleModel->getSchedules($hocKy, $namHoc, $search);
        
        $allStudents = $this->scheduleModel->getAllStudents();
        $allHocPhan = $this->scheduleModel->getAllCourses();
        $listNamHoc = $this->scheduleModel->getDistinctYears();

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
            'hocKy' => $hocKy,
            'namHoc' => $namHoc,
            'search' => $search,
            'allStudents' => $allStudents,
            'allHocPhan' => $allHocPhan,
            'listNamHoc' => $listNamHoc,
            'action' => $action,
            'item' => $item,
            'page_title' => 'Quản lý thời khóa biểu',
            'active_menu' => 'thoi_khoa_bieu'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/thoi-khoa-bieu');
        }

        $id = (int)($_POST['id'] ?? 0);
        $sinhVienId = (int)($_POST['sinh_vien_id'] ?? 0);
        $hocPhanId = (int)($_POST['hoc_phan_id'] ?? 0);
        $thu = max(2, min(8, (int)($_POST['thu'] ?? 2)));
        $tietBatDau = max(1, min(10, (int)($_POST['tiet_bat_dau'] ?? 1)));
        $soTiet = max(1, min(5, (int)($_POST['so_tiet'] ?? 3)));
        $phongHoc = trim($_POST['phong_hoc'] ?? '');
        $giangVien = trim($_POST['giang_vien'] ?? '');
        $hocKy = max(1, min(8, (int)($_POST['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        
        $keepParams = [
            'hoc_ky' => $hocKy,
            'nam_hoc' => $namHoc,
            'search' => trim($_POST['search_keep'] ?? '')
        ];

        if ($sinhVienId <= 0 || $hocPhanId <= 0) {
            setFlash('danger', 'Vui lòng chọn sinh viên và học phần.');
        } elseif ($tietBatDau + $soTiet - 1 > 10) {
            setFlash('danger', 'Khoảng tiết không hợp lệ. Lịch chỉ hỗ trợ tiết 1 đến tiết 10.');
        } elseif ($conflict = $this->tkbOverlap($id, $sinhVienId, $phongHoc, $thu, $tietBatDau, $soTiet, $hocKy, $namHoc)) {
            setFlash('danger', $conflict);
        } else {
            $data = [
                'sv_id' => $sinhVienId, 'hp_id' => $hocPhanId, 'thu' => $thu, 
                'tiet_bd' => $tietBatDau, 'so_tiet' => $soTiet, 'phong' => $phongHoc, 
                'gv' => $giangVien, 'hk' => $hocKy, 'nh' => $namHoc
            ];
            if ($id > 0) {
                if ($this->scheduleModel->updateSchedule($id, $data)) {
                    setFlash('success', 'Cập nhật lịch học thành công.');
                } else {
                    setFlash('danger', 'Lỗi cập nhật lịch học.');
                }
            } else {
                if ($this->scheduleModel->insertSchedule($data)) {
                    setFlash('success', 'Thêm lịch học thành công.');
                } else {
                    setFlash('danger', 'Lỗi thêm lịch học.');
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
                setFlash('success', 'Xóa lịch học thành công.');
            } else {
                setFlash('danger', 'Lỗi xóa lịch học.');
            }
        }

        $url = '/admin/thoi-khoa-bieu?' . http_build_query($keepParams);
        $this->redirect($url);
    }

    // ==========================================
    // TỐI ƯU LỊCH (Xếp tự động)
    // ==========================================

    public function optimize() {
        $hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        $courseGroups = $this->scheduleModel->getCourseGroupsForOptimization($hocKy, $namHoc);
        $existingCount = $this->scheduleModel->countExistingSchedules($hocKy, $namHoc);
        $studentCount = $this->scheduleModel->countRegisteredStudents($hocKy, $namHoc);

        $preview = [];
        $previewDayLoad = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0];
        foreach ($courseGroups as $i => $course) {
            $len = $this->optBlockLength((int)$course['so_tin_chi']);
            $day = 2 + ($i % 5);
            $start = ($i % 2 === 0) ? 1 : 4;
            $preview[] = [
                'ma_hp' => $course['ma_hp'],
                'ten_hp' => $course['ten_hp'],
                'students' => (int)$course['total_students'],
                'length' => $len,
                'hint' => tenThu($day) . ', T' . $start . '-T' . ($start + $len - 1),
            ];
            $previewDayLoad[$day] += $len;
        }

        $this->view('admin/schedule/optimize', [
            'hocKy' => $hocKy,
            'namHoc' => $namHoc,
            'courseGroups' => $courseGroups,
            'existingCount' => $existingCount,
            'studentCount' => $studentCount,
            'preview' => $preview,
            'page_title' => 'Xếp thời khóa biểu tự động',
            'active_menu' => 'thoi_khoa_bieu'
        ]);
    }

    public function processOptimize() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'optimize') {
            $this->redirect('/admin/thoi-khoa-bieu/optimize');
        }

        $hocKy = max(1, min(8, (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI)));
        $namHoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $replace = !empty($_POST['replace_existing']);

        $courseGroups = $this->scheduleModel->getCourseGroupsForOptimization($hocKy, $namHoc);
        
        $rooms = ['A101', 'A102', 'A201', 'A202', 'B305', 'B306', 'Lab IT', 'Lab PM'];
        $lecturers = [
            'TS. Nguyễn Văn Hùng', 'ThS. Trần Thị Lan', 'TS. Lê Văn Minh',
            'ThS. Phạm Thị Hoa', 'TS. Võ Thành Nam', 'ThS. Nguyễn Thị Linh', 'TS. Phạm Văn Tú',
        ];

        $created = 0; $skipped = 0; $failed = 0; $logs = [];
        $studentOccupied = []; $roomOccupied = [];
        $dayLoad = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0];

        try {
            $this->scheduleModel->beginTransaction();

            if ($replace) {
                $this->scheduleModel->deleteAllSchedulesBySemester($hocKy, $namHoc);
            } else {
                $existingRows = $this->scheduleModel->getSchedulesToOptimize($hocKy, $namHoc);
                foreach ($existingRows as $row) {
                    $sid = (int)$row['sinh_vien_id'];
                    $day = (int)$row['thu'];
                    $start = (int)$row['tiet_bat_dau'];
                    $length = (int)$row['so_tiet'];
                    $room = $row['phong_hoc'] ?: 'NO_ROOM';
                    
                    if (!isset($studentOccupied[$sid])) $studentOccupied[$sid] = [];
                    if (!isset($roomOccupied[$room])) $roomOccupied[$room] = [];
                    
                    $this->optMarkOccupied($studentOccupied[$sid], $day, $start, $length);
                    $this->optMarkOccupied($roomOccupied[$room], $day, $start, $length);
                    $dayLoad[$day] = ($dayLoad[$day] ?? 0) + $length;
                }
            }

            foreach ($courseGroups as $index => $course) {
                $allStudentIds = array_filter(array_map('intval', explode(',', $course['student_ids'])));
                $studentIds = [];

                if (!$replace) {
                    foreach ($allStudentIds as $sid) {
                        if ($this->scheduleModel->checkExistingPair($sid, (int)$course['hoc_phan_id'], $hocKy, $namHoc)) {
                            $skipped++;
                        } else {
                            $studentIds[] = $sid;
                        }
                    }
                } else {
                    $studentIds = $allStudentIds;
                }

                if (!$studentIds) continue;

                $length = $this->optBlockLength((int)$course['so_tin_chi']);
                $slot = $this->optBestSlot($studentIds, $length, $studentOccupied, $roomOccupied, $dayLoad, $rooms);

                if (!$slot) {
                    $failed += count($studentIds);
                    $logs[] = 'Không đủ slot cho ' . $course['ma_hp'] . ' - ' . $course['ten_hp'];
                    continue;
                }

                $hpId = (int)$course['hoc_phan_id'];
                $day = (int)$slot['day'];
                $start = (int)$slot['start'];
                $room = $slot['room'];
                $lecturer = $lecturers[$index % count($lecturers)];

                foreach ($studentIds as $sid) {
                    $data = [
                        'sv_id' => $sid, 'hp_id' => $hpId, 'thu' => $day, 
                        'tiet_bd' => $start, 'so_tiet' => $length, 'phong' => $room, 
                        'gv' => $lecturer, 'hk' => $hocKy, 'nh' => $namHoc
                    ];
                    if ($this->scheduleModel->insertSchedule($data)) {
                        $created++;
                        if (!isset($studentOccupied[$sid])) $studentOccupied[$sid] = [];
                        $this->optMarkOccupied($studentOccupied[$sid], $day, $start, $length);
                    } else {
                        $failed++;
                    }
                }

                if (!isset($roomOccupied[$room])) $roomOccupied[$room] = [];
                $this->optMarkOccupied($roomOccupied[$room], $day, $start, $length);
                $dayLoad[$day] = ($dayLoad[$day] ?? 0) + $length;
                $logs[] = $course['ma_hp'] . ' xếp vào ' . tenThu($day) . ', T' . $start . '-T' . ($start + $length - 1) . ', phòng ' . $room;
            }

            $this->scheduleModel->commit();
            setFlash('success', "Xếp tự động xong: tạo $created lịch, bỏ qua $skipped lịch đã có, $failed mục chưa xếp được.");
            $_SESSION['optimize_log'] = array_slice($logs, 0, 12);
        } catch (\Exception $e) {
            $this->scheduleModel->rollback();
            setFlash('danger', 'Không thể xếp thời khóa biểu: ' . $e->getMessage());
        }

        $this->redirect("/admin/thoi-khoa-bieu?hoc_ky=$hocKy&nam_hoc=" . urlencode($namHoc));
    }

    private function optHasOccupied($occupied, $day, $start, $length) {
        for ($t = $start; $t < $start + $length; $t++) {
            if (!empty($occupied[$day][$t])) return true;
        }
        return false;
    }

    private function optMarkOccupied(&$occupied, $day, $start, $length) {
        for ($t = $start; $t < $start + $length; $t++) {
            $occupied[$day][$t] = true;
        }
    }

    private function optBlockLength($credits) {
        if ($credits >= 5) return 4;
        if ($credits <= 1) return 2;
        return 3;
    }

    private function optBestSlot($studentIds, $length, $studentOccupied, $roomOccupied, $dayLoad, $rooms) {
        $best = null;
        $days = [2, 3, 4, 5, 6, 7, 8];
        $starts = [1, 4, 6, 8, 2, 5, 7];

        foreach ($days as $day) {
            foreach ($starts as $start) {
                if ($start + $length - 1 > 10) continue;

                foreach ($rooms as $room) {
                    if (isset($roomOccupied[$room]) && $this->optHasOccupied($roomOccupied[$room], $day, $start, $length)) {
                        continue;
                    }

                    $conflict = false;
                    foreach ($studentIds as $sid) {
                        if (isset($studentOccupied[$sid]) && $this->optHasOccupied($studentOccupied[$sid], $day, $start, $length)) {
                            $conflict = true;
                            break;
                        }
                    }

                    if ($conflict) continue;

                    $score = 0;
                    $score += ($dayLoad[$day] ?? 0) * 8;
                    $score += $day === 7 ? 20 : 0;
                    $score += $day === 8 ? 45 : 0;
                    $score += $start >= 6 ? 8 : 0;
                    $score += $start === 8 ? 5 : 0;
                    $score += abs(4 - $day) * 2;

                    if ($best === null || $score < $best['score']) {
                        $best = [
                            'day' => $day, 'start' => $start, 'room' => $room, 'score' => $score,
                        ];
                    }
                }
            }
        }

        return $best;
    }
}
