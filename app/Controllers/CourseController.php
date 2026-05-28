<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudentModel;
use App\Models\CourseModel;

class CourseController extends Controller {

    private function requireStudent() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
            $this->redirect('/auth/logout');
        }
    }

    public function program() {
        $this->requireStudent();
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $courseModel = new CourseModel();
        $programData = $courseModel->getProgramDetails($sv['id'], $sv['nganh']);

        $this->view('student/program', [
            'sv' => $sv,
            'programData' => $programData,
            'page_title' => 'Chương trình đào tạo',
            'active_menu' => 'hoc_tap'
        ]);
    }

    public function grades() {
        $this->requireStudent();
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $courseModel = new CourseModel();
        $nh_filter = $_GET['nh'] ?? '';
        $gradesData = $courseModel->getGrades($sv['id'], $nh_filter);

        $this->view('student/grades', [
            'sv' => $sv,
            'gradesData' => $gradesData,
            'nh_filter' => $nh_filter,
            'page_title' => 'Điểm học tập',
            'active_menu' => 'hoc_tap'
        ]);
    }

    public function schedule() {
        $this->requireStudent();
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $courseModel = new CourseModel();
        $hk_filter = (int)($_GET['hk'] ?? HOC_KY_HIEN_TAI);
        $nh_filter = $_GET['nh'] ?? NAM_HOC_HIEN_TAI;

        $scheduleData = $courseModel->getSchedule($sv['id'], $hk_filter, $nh_filter);

        $this->view('student/schedule', [
            'sv' => $sv,
            'scheduleData' => $scheduleData,
            'hk_filter' => $hk_filter,
            'nh_filter' => $nh_filter,
            'page_title' => 'Thời khóa biểu',
            'active_menu' => 'hoc_tap'
        ]);
    }

    public function register() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $hk = HOC_KY_HIEN_TAI;
        $nh = NAM_HOC_HIEN_TAI;
        
        $courseModel = new CourseModel();
        $da_dk = $courseModel->getRegisteredCourses($sv['id'], $hk, $nh);
        
        // Lấy đúng cột lop_hoc_phan_id (ID lớp học phần đã đăng ký) để truyền sang getAvailableCourses loại trừ
        $da_dk_ids = array_column($da_dk, 'lop_hoc_phan_id');
        $co_the_dk = $courseModel->getAvailableCourses($sv['id'], $sv['nganh'], $da_dk_ids);

        $tc_dang_ky = array_sum(array_column($da_dk, 'so_tin_chi'));

        $this->view('student/register_course', [
            'sv' => $sv,
            'hk' => $hk,
            'nh' => $nh,
            'da_dk' => $da_dk,
            'co_the_dk' => $co_the_dk,
            'tc_dang_ky' => $tc_dang_ky,
            'page_title' => 'Đăng ký học phần',
            'active_menu' => 'truc_tuyen'
        ]);
    }

    public function processRegister() {
        $this->requireStudent();
        $courseModel = new CourseModel();
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) {
            return $this->json(['type' => 'danger', 'text' => 'Phiên làm việc hết hạn.']);
        }

        $hk = HOC_KY_HIEN_TAI;
        $nh = NAM_HOC_HIEN_TAI;

        $action = $_POST['action'] ?? '';
        $hpId = (int)($_POST['hoc_phan_id'] ?? 0);

        if ($hpId <= 0) {
            return $this->json(['type' => 'danger', 'text' => 'Dữ liệu không hợp lệ.']);
        }

        if ($action === 'dang_ky') {
            $result = $courseModel->registerCourse($sv['id'], $hpId, $hk, $nh);
            return $this->json($result);
        } elseif ($action === 'huy') {
            $result = $courseModel->cancelCourse($sv['id'], $hpId, $hk, $nh);
            return $this->json($result);
        }

        return $this->json(['type' => 'danger', 'text' => 'Hành động không hợp lệ.']);
    }
}
