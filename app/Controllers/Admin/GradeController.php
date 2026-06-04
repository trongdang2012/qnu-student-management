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

        $khoa = trim($_GET['khoa'] ?? '');
        $nganh = trim($_GET['nganh'] ?? '');
        $lop = trim($_GET['lop'] ?? '');

        if ($action === 'list') {
            $search = trim($_GET['search'] ?? '');
            $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
            $loai = trim($_GET['loai'] ?? '');

            $list_hp = $this->gradeModel->getCoursesWithGradeStats($search, $hoc_ky, $loai, $khoa, $nganh, $lop);
            $facultiesClassesTree = $this->gradeModel->getFacultiesAndClasses();

            $this->view('admin/grade/academic', [
                'list_hp' => $list_hp,
                'search' => $search,
                'hoc_ky' => $hoc_ky,
                'loai' => $loai,
                'khoa' => $khoa,
                'nganh' => $nganh,
                'lop' => $lop,
                'facultiesClassesTree' => $facultiesClassesTree,
                'page_title' => 'Nhập điểm học tập',
                'active_menu' => 'diem'
            ]);
        } elseif ($action === 'edit' && $hoc_phan_id > 0) {
            $hp = $this->gradeModel->getCourseById($hoc_phan_id);
            if (!$hp) {
                setFlash('danger', 'Không tìm thấy học phần yêu cầu.');
                $this->redirect('/admin/diem/hoc-tap');
            }

            $students = $this->gradeModel->getStudentsForGrade($hoc_phan_id, $khoa, $nganh, $lop);

            $this->view('admin/grade/academic_edit', [
                'hp' => $hp,
                'hoc_phan_id' => $hoc_phan_id,
                'students' => $students,
                'khoa' => $khoa,
                'nganh' => $nganh,
                'lop' => $lop,
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

    public function academicExportTemplate() {
        $hoc_phan_id = (int)($_GET['hoc_phan_id'] ?? 0);
        $hp = $this->gradeModel->getCourseById($hoc_phan_id);
        if (!$hp) {
            setFlash('danger', 'Không tìm thấy học phần.');
            $this->redirect('/admin/diem/hoc-tap');
        }

        $khoa = trim($_GET['khoa'] ?? '');
        $nganh = trim($_GET['nganh'] ?? '');
        $lop = trim($_GET['lop'] ?? '');

        $students = $this->gradeModel->getStudentsForGrade($hoc_phan_id, $khoa, $nganh, $lop);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_diem_' . $hp['ma_hp'] . '.csv"');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        fputcsv($output, ['MSSV', 'Họ và tên', 'Điểm CC (10%)', 'Điểm GK (30%)', 'Điểm CK (60%)'], ",");

        foreach ($students as $sv) {
            fputcsv($output, [
                $sv['ma_sv'],
                $sv['ho_ten'],
                is_null($sv['diem_cc']) ? '' : $sv['diem_cc'],
                is_null($sv['diem_gk']) ? '' : $sv['diem_gk'],
                is_null($sv['diem_ck']) ? '' : $sv['diem_ck']
            ], ",");
        }
        fclose($output);
        exit;
    }

    public function academicImport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/diem/hoc-tap');
        }

        $hoc_phan_id = (int)($_POST['hoc_phan_id'] ?? 0);
        $hp = $this->gradeModel->getCourseById($hoc_phan_id);
        if (!$hp) {
            setFlash('danger', 'Không tìm thấy học phần.');
            $this->redirect('/admin/diem/hoc-tap');
        }

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Lỗi khi tải file lên.');
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
        }

        $fileName = $_FILES['excel_file']['name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($extension !== 'xlsx' && $extension !== 'csv') {
            setFlash('danger', 'File không hợp lệ, vui lòng chọn file Excel (.xlsx) hoặc CSV (.csv)');
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
        }

        $tmpName = $_FILES['excel_file']['tmp_name'];
        try {
            $rows = $this->parseExcelOrCsv($tmpName, $extension);
            if ($rows === false || empty($rows)) {
                setFlash('danger', 'File không hợp lệ hoặc không có dữ liệu.');
                $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
                return;
            }
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
            return;
        }

        // Bỏ dòng tiêu đề
        array_shift($rows);

        $khoa = trim($_POST['khoa'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');

        $students = $this->gradeModel->getStudentsForGrade($hoc_phan_id, $khoa, $nganh, $lop);
        $svMap = [];
        foreach ($students as $sv) {
            $svMap[$sv['ma_sv']] = $sv['sinh_vien_id'];
        }

        $to_save = [];
        $errors = [];
        $rowNum = 2; // Dòng 2 bắt đầu sau tiêu đề

        foreach ($rows as $data) {
            if (count($data) < 5) continue;

            $ma_sv = trim($data[0]);
            $cc_str = str_replace(',', '.', trim($data[2]));
            $gk_str = str_replace(',', '.', trim($data[3]));
            $ck_str = str_replace(',', '.', trim($data[4]));

            if (empty($ma_sv)) continue;

            if (!isset($svMap[$ma_sv])) {
                $errors[] = "Dòng $rowNum: Sinh viên có mã $ma_sv không thuộc lớp học phần này.";
                $rowNum++;
                continue;
            }

            $sv_id = $svMap[$ma_sv];

            if ($cc_str === '' && $gk_str === '' && $ck_str === '') {
                $rowNum++;
                continue;
            }

            if ($cc_str === '' || $gk_str === '' || $ck_str === '') {
                $errors[] = "Dòng $rowNum: Vui lòng nhập đủ 3 cột điểm cho sinh viên $ma_sv.";
                $rowNum++;
                continue;
            }

            $cc = filter_var($cc_str, FILTER_VALIDATE_FLOAT);
            $gk = filter_var($gk_str, FILTER_VALIDATE_FLOAT);
            $ck = filter_var($ck_str, FILTER_VALIDATE_FLOAT);

            if ($cc === false || $cc < 0 || $cc > 10 ||
                $gk === false || $gk < 0 || $gk > 10 ||
                $ck === false || $ck < 0 || $ck > 10) {
                $errors[] = "Dòng $rowNum: Điểm của sinh viên $ma_sv không hợp lệ (0-10).";
                $rowNum++;
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

            $rowNum++;
        }

        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
            return;
        }

        if (empty($to_save)) {
            setFlash('warning', 'Không có dữ liệu điểm nào để cập nhật.');
            $this->redirect("/admin/diem/hoc-tap?action=edit&hoc_phan_id=$hoc_phan_id");
            return;
        }

        try {
            $this->gradeModel->beginTransaction();
            foreach ($to_save as $row) {
                $this->gradeModel->saveAcademicGrade($row, $hoc_phan_id);
            }
            $this->gradeModel->commit();
            setFlash('success', 'Nhập điểm học phần thành công!');
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
        $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
        $nam_hoc = trim($_GET['nam_hoc'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $nganh = trim($_GET['nganh'] ?? '');
        $lop_filter = trim($_GET['lop'] ?? '');

        if ($hoc_ky === 0) {
            $hoc_ky = 1;
        }
        if ($nam_hoc === '') {
            $m = (int)date('n');
            $y = (int)date('Y');
            if ($m >= 8) {
                $nam_hoc = $y . '-' . ($y + 1);
            } else {
                $nam_hoc = ($y - 1) . '-' . $y;
            }
        }

        $list_khoa = $this->gradeModel->getKhoaList();
        $list_nganh = $this->gradeModel->getAllNganhList();
        // Use filtered class list if faculty and department selected, otherwise all classes
        if ($khoa && $nganh) {
            $list_lop = $this->gradeModel->getLopListByKhoaAndNganh($khoa, $nganh);
        } else {
            $list_lop = $this->gradeModel->getLopList();
        }
        $list_sv = [];
        if ($lop_filter !== '') {
            $list_sv = $this->gradeModel->getTrainingGrades($hoc_ky, $nam_hoc, $search, $khoa, $nganh, $lop_filter);
        }

        $this->view('admin/grade/training', [
            'hoc_ky' => $hoc_ky,
            'nam_hoc' => $nam_hoc,
            'search' => $search,
            'lop_filter' => $lop_filter,
            'khoa' => $khoa,
            'nganh' => $nganh,
            'list_khoa' => $list_khoa,
            'list_nganh' => $list_nganh,
            'list_lop' => $list_lop,
            'list_sv' => $list_sv,
            'page_title' => 'Quản lý điểm rèn luyện',
            'active_menu' => 'diem'
        ]);
    }



    // AJAX endpoint to fetch departments for a given faculty
    public function getDepartments() {
        $khoa = $_GET['khoa'] ?? '';
        if ($khoa === '') {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }
        $list = $this->gradeModel->getNganhListByKhoa($khoa);
        header('Content-Type: application/json');
        echo json_encode($list);
    }

    // AJAX endpoint to fetch classes for a given faculty and department
    public function getClasses() {
        $khoa = $_GET['khoa'] ?? '';
        $nganh = $_GET['nganh'] ?? '';
        if ($khoa === '' || $nganh === '') {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }
        $list = $this->gradeModel->getLopListByKhoaAndNganh($khoa, $nganh);
        header('Content-Type: application/json');
        echo json_encode($list);
    }

    // Import training scores from CSV/Excel file (CSV or real XLSX)
    public function trainingImport() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/diem/ren-luyen');
            return;
        }
        $hoc_ky = (int)($_POST['hoc_ky'] ?? 0);
        $nam_hoc = trim($_POST['nam_hoc'] ?? '');
        $khoa = trim($_POST['khoa'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');

        // Validate uploaded file existence
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Lỗi khi tải file lên.');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
            return;
        }

        $fileName = $_FILES['excel_file']['name'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Only allow .xlsx and .csv files
        if ($extension !== 'xlsx' && $extension !== 'csv') {
            setFlash('danger', 'File không hợp lệ, vui lòng chọn file Excel (.xlsx) hoặc CSV (.csv)');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
            return;
        }

        $tmpName = $_FILES['excel_file']['tmp_name'];
        $rows = $this->parseExcelOrCsv($tmpName, $extension);
        if ($rows === false || empty($rows)) {
            setFlash('danger', 'File không hợp lệ, vui lòng chọn file Excel (.xlsx) hoặc CSV (.csv)');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
            return;
        }

        // Skip header line
        $header = array_shift($rows);
        if (!$header) {
            setFlash('danger', 'File Excel thiếu dữ liệu');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
            return;
        }

        $to_save = [];
        $errors = [];
        $rowNum = 2; // start after header

        foreach ($rows as $data) {
            // Expect at least 3 columns: mã sinh viên, họ tên, điểm rèn luyện
            if (count($data) < 3) {
                $errors[] = "Dòng $rowNum: Dữ liệu trong file không đầy đủ";
                $rowNum++;
                continue;
            }

            $ma_sv = trim($data[0]);
            $scoreStr = trim($data[2]);
            $note = $data[3] ?? '';

            if ($ma_sv === '' || $scoreStr === '') {
                $errors[] = "Dòng $rowNum: Dữ liệu trong file không đầy đủ";
                $rowNum++;
                continue;
            }

            $score = filter_var($scoreStr, FILTER_VALIDATE_FLOAT);
            if ($score === false || $score < 0 || $score > 100) {
                $errors[] = "Dòng $rowNum: Điểm không hợp lệ, vui lòng kiểm tra lại file Excel";
                $rowNum++;
                continue;
            }

            // Verify student belongs to selected hierarchy
            $sv = $this->gradeModel->getStudentInfoForGradeErrorByCode($ma_sv, $khoa, $nganh, $lop);
            if (!$sv) {
                $errors[] = "Dòng $rowNum: Không tìm thấy sinh viên trong danh sách lớp";
                $rowNum++;
                continue;
            }

            $sv_id = $sv['sinh_vien_id'];

            // Calculate criteria (same logic as modal)
            $t1 = min(30, round($score * 0.3));
            $t2 = min(25, round($score * 0.25));
            $t3 = min(20, round($score * 0.2));
            $t4 = min(15, round($score * 0.15));
            $t5 = max(0, $score - ($t1 + $t2 + $t3 + $t4));
            $ghi_chu = json_encode([
                't1' => $t1,
                't2' => $t2,
                't3' => $t3,
                't4' => $t4,
                't5' => $t5,
                'user_note' => $note
            ], JSON_UNESCAPED_UNICODE);

            $xep_loai = $this->gradeModel->calculateXepLoai($score);
            $to_save[] = [$sv_id, $hoc_ky, $nam_hoc, $score, $xep_loai, $ghi_chu];
            $rowNum++;
        }

        // If any validation errors occurred, show them and abort the import
        if (!empty($errors)) {
            setFlash('danger', implode('<br>', $errors));
            $this->redirect('/admin/diem/ren-luyen');
            return;
        }

        // Persist data – wrap in transaction to capture system errors
        try {
            $this->gradeModel->beginTransaction();
            foreach ($to_save as $row) {
                list($sv_id, $hk, $nh, $score, $xl, $gc) = $row;
                $this->gradeModel->saveTrainingGrade($sv_id, $hk, $nh, $score, $xl, $gc);
            }
            $this->gradeModel->commit();
            setFlash('success', 'Cập nhật điểm rèn luyện thành công!');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
        } catch (\Exception $e) {
            $this->gradeModel->rollback();
            setFlash('danger', 'Lỗi hệ thống, vui lòng thử lại sau');
            $this->redirect('/admin/diem/ren-luyen?hoc_ky=' . $hoc_ky . '&nam_hoc=' . urlencode($nam_hoc) . '&khoa=' . urlencode($khoa) . '&nganh=' . urlencode($nganh) . '&lop=' . urlencode($lop));
        }
    }

    private function parseExcelOrCsv($tmpName, $extension) {
        if ($extension === 'xlsx') {
            if (!class_exists('ZipArchive')) {
                throw new \Exception('PHP Extension ZipArchive chưa được bật.');
            }

            $zip = new \ZipArchive();
            if ($zip->open($tmpName) === TRUE) {
                // Get shared strings
                $sharedStrings = [];
                $sharedStringsEntry = $zip->getFromName('xl/sharedStrings.xml');
                if ($sharedStringsEntry !== false) {
                    $xml = simplexml_load_string($sharedStringsEntry);
                    if ($xml && isset($xml->si)) {
                        foreach ($xml->si as $si) {
                            $sharedStrings[] = (string)($si->t ?? $si->r->t ?? '');
                        }
                    }
                }

                // Get sheet1 data
                $sheetEntry = $zip->getFromName('xl/worksheets/sheet1.xml');
                if ($sheetEntry !== false) {
                    $rows = [];
                    $xml = simplexml_load_string($sheetEntry);
                    if ($xml && isset($xml->sheetData->row)) {
                        foreach ($xml->sheetData->row as $rowNode) {
                            $row = [];
                            foreach ($rowNode->c as $cellNode) {
                                $r = (string)$cellNode['r'];
                                preg_match('/^[A-Z]+/', $r, $matches);
                                $colName = $matches[0] ?? '';
                                
                                $colIndex = 0;
                                $len = strlen($colName);
                                for ($i = 0; $i < $len; $i++) {
                                    $colIndex = $colIndex * 26 + (ord($colName[$i]) - 64);
                                }
                                $colIndex = $colIndex - 1; // 0-based

                                $value = '';
                                if (isset($cellNode->v)) {
                                    $val = (string)$cellNode->v;
                                    if (isset($cellNode['t']) && (string)$cellNode['t'] === 's') {
                                        $value = $sharedStrings[(int)$val] ?? '';
                                    } else {
                                        $value = $val;
                                    }
                                }
                                $row[$colIndex] = $value;
                            }

                            if (!empty($row)) {
                                $maxIndex = max(array_keys($row));
                                for ($i = 0; $i <= $maxIndex; $i++) {
                                    if (!isset($row[$i])) {
                                        $row[$i] = '';
                                    }
                                }
                                ksort($row);
                                $rows[] = $row;
                            }
                        }
                    }
                    $zip->close();
                    return $rows;
                }
                $zip->close();
            }
            return false;
        }

        // Fallback to CSV parsing (in case it is a CSV renamed to .xlsx or actual CSV)
        $handle = fopen($tmpName, 'r');
        if ($handle === FALSE) {
            return false;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rows = [];
        $header = fgetcsv($handle, 1000, ",");
        $delimiter = ",";
        if ($header && count($header) === 1 && strpos($header[0], ';') !== false) {
            rewind($handle);
            if ($bom === "\xEF\xBB\xBF") fread($handle, 3);
            $delimiter = ";";
        } else {
            if ($header) {
                $rows[] = $header;
            }
        }

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }


            public function trainingSave() {
        // Ensure request is POST and correct action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'save_drl') {
            $this->redirect('/admin/diem/ren-luyen');
            return;
        }

        $sv_id = (int)$_POST['sinh_vien_id'];
        $hk_val = (int)$_POST['hoc_ky'];
        $nh_val = trim($_POST['nam_hoc']);
        $search = trim($_GET['search'] ?? '');
        $lop_filter = trim($_GET['lop'] ?? '');
        // Retrieve faculty and department from POST (hidden fields) or fallback to GET
        $khoa = trim($_POST['khoa'] ?? $_GET['khoa'] ?? '');
        $nganh = trim($_POST['nganh'] ?? $_GET['nganh'] ?? '');
        // $lop_filter already retrieved from GET above
        $search = trim($_GET['search'] ?? '');

        $sv = $this->gradeModel->getStudentInfoForGradeError($sv_id);
        if (!$sv) {
            setFlash('danger', 'Không tìm thấy sinh viên.');
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&khoa=" . urlencode($khoa) . "&nganh=" . urlencode($nganh) . "&lop=" . urlencode($lop_filter));
            return;
        }

        $t1_str = trim($_POST['t1'] ?? '');
        $t2_str = trim($_POST['t2'] ?? '');
        $t3_str = trim($_POST['t3'] ?? '');
        $t4_str = trim($_POST['t4'] ?? '');
        $t5_str = trim($_POST['t5'] ?? '');
        $user_note = trim($_POST['user_note'] ?? '');

        if ($t1_str === '' || $t2_str === '' || $t3_str === '' || $t4_str === '' || $t5_str === '') {
            setFlash('danger', 'Vui lòng nhập đầy đủ thông tin các tiêu chí điểm.');
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&khoa=" . urlencode($khoa) . "&nganh=" . urlencode($nganh) . "&lop=" . urlencode($lop_filter));
            return;
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
            $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&khoa=" . urlencode($khoa) . "&nganh=" . urlencode($nganh) . "&lop=" . urlencode($lop_filter));
            return;
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

        $this->redirect("/admin/diem/ren-luyen?hoc_ky=$hk_val&nam_hoc=" . urlencode($nh_val) . "&search=" . urlencode($search) . "&khoa=" . urlencode($khoa) . "&nganh=" . urlencode($nganh) . "&lop=" . urlencode($lop_filter));
    }




    public function trainingExportTemplate() {
        $lop = trim($_GET['lop'] ?? '');
        $hoc_ky = (int)($_GET['hoc_ky'] ?? 0);
        $nam_hoc = trim($_GET['nam_hoc'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $nganh = trim($_GET['nganh'] ?? '');

        if ($lop === '') {
            setFlash('danger', 'Không tìm thấy thông tin lớp học.');
            $this->redirect('/admin/diem/ren-luyen');
            return;
        }

        $students = $this->gradeModel->getTrainingGrades($hoc_ky, $nam_hoc, '', $khoa, $nganh, $lop);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_ren_luyen_' . $lop . '.csv"');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');

        fputcsv($output, ['MSSV', 'Họ và tên', 'Điểm rèn luyện', 'Ghi chú'], ",");

        foreach ($students as $sv) {
            $note = '';
            if (!empty($sv['ghi_chu'])) {
                $json = json_decode($sv['ghi_chu'], true);
                if (is_array($json) && isset($json['user_note'])) {
                    $note = $json['user_note'];
                } else {
                    $note = $sv['ghi_chu'];
                }
            }
            fputcsv($output, [
                $sv['ma_sv'],
                $sv['ho_ten'],
                is_null($sv['diem']) ? '' : $sv['diem'],
                $note
            ], ",");
        }
        fclose($output);
        exit;
    } 
    // Alias for export template compatibility
    public function exportTemplate() {
        $this->trainingExportTemplate();
    }
    // End alias
    // Close class brace remains unchanged
}
