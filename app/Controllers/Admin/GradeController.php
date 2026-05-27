<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminGradeModel;

class GradeController extends Controller {
    private $gradeModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->gradeModel = new AdminGradeModel();
    }

    // ==========================================
    // ĐIỂM HỌC TẬP
    // ==========================================

    public function academic() {
        $action = $_GET['action'] ?? 'list';
        $hoc_phan_id = (int)($_GET['hoc_phan_id'] ?? 0);

        if ($action === 'list') {
            $search = trim($_GET['search'] ?? '');
            $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
            $loai = trim($_GET['loai'] ?? '');

            $list_hp = $this->gradeModel->getCoursesWithGradeStats($search, $hoc_ky, $loai);

            $this->view('admin/grade/academic', [
                'list_hp' => $list_hp,
                'search' => $search,
                'hoc_ky' => $hoc_ky,
                'loai' => $loai,
                'page_title' => 'Nhập điểm học tập',
                'active_menu' => 'diem'
            ]);
        } elseif ($action === 'edit' && $hoc_phan_id > 0) {
            $hp = $this->gradeModel->getCourseById($hoc_phan_id);
            if (!$hp) {
                setFlash('danger', 'Không tìm thấy học phần yêu cầu.');
                $this->redirect('/admin/diem/hoc-tap');
            }

            $students = $this->gradeModel->getStudentsForGrade($hoc_phan_id);

            $this->view('admin/grade/academic_edit', [
                'hp' => $hp,
                'hoc_phan_id' => $hoc_phan_id,
                'students' => $students,
                'page_title' => 'Nhập điểm học phần: ' . $hp['ten_hp'],
                'active_menu' => 'diem'
            ]);
        } else {
            $this->redirect('/admin/diem/hoc-tap');
        }
    }

    public function academicSave() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'save_grades') {
            $this->redirect('/admin/diem/hoc-tap');
        }

        $hoc_phan_id = (int)($_POST['hoc_phan_id'] ?? 0);
        $hp = $this->gradeModel->getCourseById($hoc_phan_id);
        
        if (!$hp) {
            setFlash('danger', 'Không tìm thấy học phần.');
            $this->redirect('/admin/diem/hoc-tap');
        }

        $diem_data = $_POST['diem'] ?? [];
        $errors = [];
        $to_save = [];

        foreach ($diem_data as $sv_id => $grades) {
            $sv_id = (int)$sv_id;
            $sv_info = $this->gradeModel->getStudentInfoForGradeError($sv_id);
            $sv_name = $sv_info ? $sv_info['ho_ten'] . " (".$sv_info['ma_sv'].")" : "Sinh viên ID $sv_id";

            $cc_str = trim($grades['cc'] ?? '');
            $gk_str = trim($grades['gk'] ?? '');
            $ck_str = trim($grades['ck'] ?? '');

            if ($cc_str === '' && $gk_str === '' && $ck_str === '') continue;

            if ($cc_str === '' || $gk_str === '' || $ck_str === '') {
                $errors[] = "Vui lòng nhập đầy đủ thông tin điểm cho sinh viên $sv_name.";
                continue;
            }

            $cc = filter_var($cc_str, FILTER_VALIDATE_FLOAT);
            $gk = filter_var($gk_str, FILTER_VALIDATE_FLOAT);
            $ck = filter_var($ck_str, FILTER_VALIDATE_FLOAT);

            if ($cc === false || $cc < 0 || $cc > 10 ||
                $gk === false || $gk < 0 || $gk > 10 ||
                $ck === false || $ck < 0 || $ck > 10) {
                $errors[] = "Điểm CC, GK, CK của sinh viên $sv_name không hợp lệ, vui lòng nhập lại (0–10).";
                continue;
            }

            $diem_tong = round($cc * 0.1 + $gk * 0.3 + $ck * 0.6, 2);
            $diem_chu = diemChu($diem_tong);
            $diem_he4 = diemHe4($diem_tong);

            $dk_info = $this->gradeModel->getRegistrationInfo($sv_id, $hoc_phan_id);
            $hk_val = $dk_info ? (int)$dk_info['hoc_ky'] : (int)$hp['hoc_ky'];
            $nh_val = $dk_info ? $dk_info['nam_hoc'] : $hp['nien_khoa'];

            $to_save[] = [
                'sinh_vien_id' => $sv_id,
                'hoc_ky' => $hk_val,
                'nam_hoc' => $nh_val,
                'diem_cc' => $cc,
                'diem_gk' => $gk,
                'diem_ck' => $ck,
                'diem_tong' => $diem_tong,
                'diem_chu' => $diem_chu,
                'diem_he4' => $diem_he4
            ];
        }

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
        }

        try {
            $this->gradeModel->beginTransaction();
            foreach ($to_save as $row) {
                $this->gradeModel->saveAcademicGrade($row, $hoc_phan_id);
            }
            $this->gradeModel->commit();
            setFlash('success', 'Cập nhật điểm học phần thành công!');
        } catch (\Exception $e) {
            $this->gradeModel->rollback();
            setFlash('danger', 'Lỗi hệ thống khi lưu: ' . $e->getMessage());
        }

        $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
    }

    // ==========================================
    // ĐIỂM RÈN LUYỆN
    // ==========================================

    public function training() {
        $hoc_ky = (int)($_GET['hoc_ky'] ?? HOC_KY_HIEN_TAI);
        $nam_hoc = trim($_GET['nam_hoc'] ?? NAM_HOC_HIEN_TAI);
        $search = trim($_GET['search'] ?? '');
        $lop_filter = trim($_GET['lop'] ?? '');

        $list_lop = $this->gradeModel->getLopList();
        $list_sv = $this->gradeModel->getTrainingGrades($hoc_ky, $nam_hoc, $search, $lop_filter);

        $this->view('admin/grade/training', [
            'hoc_ky' => $hoc_ky,
            'nam_hoc' => $nam_hoc,
            'search' => $search,
            'lop_filter' => $lop_filter,
            'list_lop' => $list_lop,
            'list_sv' => $list_sv,
            'page_title' => 'Quản lý điểm rèn luyện',
            'active_menu' => 'diem'
        ]);
    }

    public function trainingSave() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'save_drl') {
            $this->redirect('/admin/diem/ren-luyen');
        }

        $sv_id = (int)$_POST['sinh_vien_id'];
        $hk_val = (int)$_POST['hoc_ky'];
        $nh_val = trim($_POST['nam_hoc']);
        $search = trim($_GET['search'] ?? '');
        $lop_filter = trim($_GET['lop'] ?? '');

        $sv = $this->gradeModel->getStudentInfoForGradeError($sv_id);
        if (!$sv) {
            setFlash('danger', 'Không tìm thấy sinh viên.');
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val));
        }

        $t1_str = trim($_POST['t1'] ?? '');
        $t2_str = trim($_POST['t2'] ?? '');
        $t3_str = trim($_POST['t3'] ?? '');
        $t4_str = trim($_POST['t4'] ?? '');
        $t5_str = trim($_POST['t5'] ?? '');
        $user_note = trim($_POST['user_note'] ?? '');

        if ($t1_str === '' || $t2_str === '' || $t3_str === '' || $t4_str === '' || $t5_str === '') {
            setFlash('danger', 'Vui lòng nhập đầy đủ thông tin các tiêu chí điểm.');
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&lop=" . urlencode($lop_filter));
        }

        $t1 = filter_var($t1_str, FILTER_VALIDATE_INT);
        $t2 = filter_var($t2_str, FILTER_VALIDATE_INT);
        $t3 = filter_var($t3_str, FILTER_VALIDATE_INT);
        $t4 = filter_var($t4_str, FILTER_VALIDATE_INT);
        $t5 = filter_var($t5_str, FILTER_VALIDATE_INT);

        if ($t1 === false || $t1 < 0 || $t1 > 30 ||
            $t2 === false || $t2 < 0 || $t2 > 25 ||
            $t3 === false || $t3 < 0 || $t3 > 20 ||
            $t4 === false || $t4 < 0 || $t4 > 15 ||
            $t5 === false || $t5 < 0 || $t5 > 10) {
            setFlash('danger', 'Điểm không hợp lệ, vui lòng nhập lại.');
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&lop=" . urlencode($lop_filter));
        }

        $total_score = $t1 + $t2 + $t3 + $t4 + $t5;
        $xep_loai = 'Kém';
        if ($total_score >= 90) $xep_loai = 'Xuất sắc';
        elseif ($total_score >= 80) $xep_loai = 'Tốt';
        elseif ($total_score >= 70) $xep_loai = 'Khá';
        elseif ($total_score >= 50) $xep_loai = 'Trung bình';
        elseif ($total_score >= 30) $xep_loai = 'Yếu';

        $ghi_chu_json = json_encode([
            't1' => $t1, 't2' => $t2, 't3' => $t3, 't4' => $t4, 't5' => $t5, 'user_note' => $user_note
        ], JSON_UNESCAPED_UNICODE);

        if ($this->gradeModel->saveTrainingGrade($sv_id, $hk_val, $nh_val, $total_score, $xep_loai, $ghi_chu_json)) {
            setFlash('success', 'Cập nhật điểm rèn luyện thành công!');
        } else {
            setFlash('danger', 'Lỗi hệ thống, vui lòng thử lại sau.');
        }

        $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&lop=" . urlencode($lop_filter));
    }

    // ==========================================
    // BÁO CÁO ĐIỂM
    // ==========================================

    public function report() {
        $ma_sv = isset($_GET['ma_sv']) ? trim($_GET['ma_sv']) : '';
        $action = $_GET['action'] ?? '';

        $student = null;
        $diem_list = [];
        $error_msg = '';
        $info_msg = '';
        $cpa = 0.0;
        $tc_tich_luy = 0;
        $so_mon = 0;
        $so_mon_F = 0;
        $by_nh_hk = [];

        if ($action === 'view' || $action === 'export') {
            if ($ma_sv === '') {
                $error_msg = 'Vui lòng nhập thông tin tìm kiếm (Mã sinh viên).';
            } else {
                $student = $this->gradeModel->getStudentByCode($ma_sv);
                if (!$student) {
                    $error_msg = 'Không tìm thấy sinh viên.';
                } else {
                    $diem_list = $this->gradeModel->getStudentGradesReport($student['id']);
                    if (empty($diem_list)) {
                        $info_msg = 'Chưa có dữ liệu điểm cho sinh viên này.';
                    } else {
                        $sum_tc = 0;
                        $sum_he4 = 0;
                        foreach ($diem_list as $d) {
                            if ($action !== 'export') {
                                $by_nh_hk[$d['nam_hoc']][$d['hoc_ky']][] = $d;
                            }
                            if (!is_null($d['diem_he4'])) {
                                $sum_tc += $d['so_tin_chi'];
                                $sum_he4 += $d['diem_he4'] * $d['so_tin_chi'];
                                $so_mon++;
                                if ($d['diem_he4'] >= 1.0) {
                                    $tc_tich_luy += $d['so_tin_chi'];
                                } else {
                                    $so_mon_F++;
                                }
                            }
                        }
                        $cpa = $sum_tc > 0 ? round($sum_he4 / $sum_tc, 2) : 0;

                        if ($action === 'export') {
                            $this->exportCsv($student, $diem_list, $cpa, $tc_tich_luy, $so_mon_F);
                        }
                    }
                }
            }
        }

        $this->view('admin/grade/report', [
            'ma_sv' => $ma_sv,
            'student' => $student,
            'diem_list' => $diem_list,
            'error_msg' => $error_msg,
            'info_msg' => $info_msg,
            'cpa' => $cpa,
            'tc_tich_luy' => $tc_tich_luy,
            'so_mon' => $so_mon,
            'so_mon_F' => $so_mon_F,
            'by_nh_hk' => $by_nh_hk,
            'page_title' => 'Báo cáo điểm sinh viên',
            'active_menu' => 'diem',
            'gradeModel' => $this->gradeModel // pass model to view to get training grade per semester easily
        ]);
    }

    private function exportCsv($student, $diem_list, $cpa, $tc_tich_luy, $so_mon_F) {
        function xepLoaiCSV($cpa) {
            if ($cpa >= 3.6) return 'Xuất sắc';
            if ($cpa >= 3.2) return 'Giỏi';
            if ($cpa >= 2.5) return 'Khá';
            if ($cpa >= 2.0) return 'Trung bình';
            return 'Yếu';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bang_diem_' . $student['ma_sv'] . '.csv"');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        fputcsv($output, ['BÁO CÁO KẾT QUẢ HỌC TẬP SINH VIÊN'], "\t");
        fputcsv($output, [], "\t");
        fputcsv($output, ['THÔNG TIN SINH VIÊN'], "\t");
        fputcsv($output, ['Mã sinh viên:', $student['ma_sv']], "\t");
        fputcsv($output, ['Họ và tên:', $student['ho_ten']], "\t");
        fputcsv($output, ['Ngày sinh:', $student['ngay_sinh'] ? date('d/m/Y', strtotime($student['ngay_sinh'])) : ''], "\t");
        fputcsv($output, ['Lớp học:', $student['lop']], "\t");
        fputcsv($output, ['Ngành:', $student['nganh']], "\t");
        fputcsv($output, ['Khoa:', $student['khoa']], "\t");
        fputcsv($output, ['Niên khóa:', $student['nien_khoa']], "\t");
        fputcsv($output, [], "\t");

        fputcsv($output, ['TỔNG HỢP TOÀN KHÓA'], "\t");
        fputcsv($output, ['CPA (Hệ 4):', number_format($cpa, 2)], "\t");
        fputcsv($output, ['Tín chỉ tích lũy:', $tc_tich_luy], "\t");
        fputcsv($output, ['Số học phần tích lũy:', count($diem_list)], "\t");
        fputcsv($output, ['Số học phần chưa đạt (F):', $so_mon_F], "\t");
        fputcsv($output, ['Xếp loại học lực:', xepLoaiCSV($cpa)], "\t");
        fputcsv($output, [], "\t");

        fputcsv($output, ['CHI TIẾT BẢNG ĐIỂM'], "\t");
        fputcsv($output, ['Mã HP', 'Tên học phần', 'Số TC', 'Kỳ học', 'Năm học', 'Điểm CC (10%)', 'Điểm GK (30%)', 'Điểm CK (60%)', 'Điểm tổng kết', 'Hệ 4', 'Điểm chữ'], "\t");

        foreach ($diem_list as $d) {
            fputcsv($output, [
                $d['ma_hp'], $d['ten_hp'], $d['so_tin_chi'],
                'HK' . $d['hoc_ky'], $d['nam_hoc'],
                is_null($d['diem_cc']) ? '—' : $d['diem_cc'],
                is_null($d['diem_gk']) ? '—' : $d['diem_gk'],
                is_null($d['diem_ck']) ? '—' : $d['diem_ck'],
                is_null($d['diem_tong']) ? '—' : $d['diem_tong'],
                is_null($d['diem_he4']) ? '—' : $d['diem_he4'],
                is_null($d['diem_chu']) ? '—' : $d['diem_chu']
            ], "\t");
        }

        fclose($output);
        exit;
    }
}
