<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminTuitionModel;

class TuitionController extends Controller {
    private $tuitionModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->tuitionModel = new AdminTuitionModel();
    }

    public function index() {
        $selectedKhoa = trim($_GET['khoa'] ?? '');
        $selectedNganh = trim($_GET['nganh'] ?? '');
        $selectedLop = trim($_GET['lop'] ?? '');

        $khoaList = $this->tuitionModel->getKhoaList();
        $nganhList = $this->tuitionModel->getNganhList($selectedKhoa);
        $lopList = $this->tuitionModel->getLopList($selectedKhoa, $selectedNganh);

        $students = $this->tuitionModel->getTuitionSummaryByStudents($selectedKhoa, $selectedNganh, $selectedLop);

        $summary = ['total' => 0, 'paid' => 0, 'unpaid' => 0, 'owing' => 0, 'fee' => 0, 'paid_amount' => 0, 'owed_amount' => 0];
        foreach ($students as $row) {
            $summary['total']++;
            $summary['fee'] += (float)$row['total_fee'];
            $summary['paid_amount'] += (float)$row['total_paid'];
            $summary['owed_amount'] += max(0, (float)$row['total_owed']);
            if ((float)$row['total_fee'] === 0.0) {
                $summary['unpaid']++;
            } elseif ((float)$row['total_paid'] >= (float)$row['total_fee']) {
                $summary['paid']++;
            } elseif ((float)$row['total_paid'] <= 0.0) {
                $summary['unpaid']++;
            } else {
                $summary['owing']++;
            }
        }

        $this->view('admin/tuition/index', [
            'khoaList' => $khoaList,
            'nganhList' => $nganhList,
            'lopList' => $lopList,
            'selectedKhoa' => $selectedKhoa,
            'selectedNganh' => $selectedNganh,
            'selectedLop' => $selectedLop,
            'students' => $students,
            'summary' => $summary,
            'page_title' => 'Quản lý học phí',
            'active_menu' => 'hoc_phi'
        ]);
    }

    public function report() {
        $totals = $this->tuitionModel->getTotals();
        $statusCounts = $this->tuitionModel->getStatusCounts();
        $byKhoa = $this->tuitionModel->getByKhoa();

        $this->view('admin/tuition/bao_cao', [
            'totals' => $totals,
            'statusCounts' => $statusCounts,
            'byKhoa' => $byKhoa,
            'page_title' => 'Báo cáo học phí',
            'active_menu' => 'hoc_phi'
        ]);
    }

    public function update() {
        $id = (int)($_GET['id'] ?? 0);
        $action = $_GET['action'] ?? '';

        $editRecord = null;
        if ($action === 'edit' && $id > 0) {
            $editRecord = $this->tuitionModel->getTuitionRecord($id);
            if (!$editRecord) {
                setFlash('danger', 'Không tìm thấy học phí cần sửa.');
                $this->redirect('/admin/hoc-phi/cap-nhat');
            }
        }

        $courseList = $this->tuitionModel->getCourseList();
        $fees = $this->tuitionModel->getFilteredTuitionRecords('', '', '');

        $this->view('admin/tuition/cap_nhat', [
            'editRecord' => $editRecord,
            'fees' => $fees,
            'courseList' => $courseList,
            'page_title' => 'Cập nhật học phí',
            'active_menu' => 'hoc_phi'
        ]);
    }

    public function saveUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hoc-phi/cap-nhat');
        }

        $actionType = $_POST['action'] ?? 'save';
        if ($actionType === 'apply_course_rate') {
            $courseId = (int)($_POST['course_id'] ?? 0);
            $hocKy = max(1, min(8, (int)($_POST['hoc_ky'] ?? 0)));
            $namHoc = trim($_POST['nam_hoc'] ?? '');
            $donGia = max(0, (float)($_POST['don_gia'] ?? 0));
            $han_nop = trim($_POST['han_nop'] ?? '');

            if ($courseId <= 0 || $donGia <= 0 || $hocKy <= 0 || $namHoc === '') {
                setFlash('danger', 'Vui lòng chọn học phần, học kỳ, nhập năm học và đơn giá hợp lệ.');
            } else {
                $result = $this->tuitionModel->applyCourseTuitionRate($courseId, $hocKy, $namHoc, $donGia, $han_nop);
                if ($result) {
                    setFlash('success', 'Áp dụng mức học phí theo học phần thành công.');
                } else {
                    setFlash('danger', 'Không có sinh viên đăng ký học phần này trong học kỳ/năm học đã chọn hoặc lỗi khi áp dụng học phí.');
                }
            }

            $this->redirect('/admin/hoc-phi/cap-nhat');
            return;
        }

        if ($actionType !== 'save') {
            $this->redirect('/admin/hoc-phi/cap-nhat');
        }

        $id = (int)($_POST['id'] ?? 0);
        $so_tien = max(0, (float)($_POST['so_tien'] ?? 0));
        $han_nop = trim($_POST['han_nop'] ?? '');

        $record = $this->tuitionModel->getTuitionById($id);

        if (!$record) {
            setFlash('danger', 'Không tìm thấy bản ghi học phí cần cập nhật.');
        } else {
            $da_nop = (float)$record['da_nop'];
            if ($da_nop >= $so_tien && $so_tien > 0) {
                $trang_thai = 'Đã nộp';
            } elseif ($da_nop > 0) {
                $trang_thai = 'Nợ';
            } else {
                $trang_thai = 'Chưa nộp';
            }

            if ($this->tuitionModel->updateTuition($id, $so_tien, $han_nop, $trang_thai)) {
                setFlash('success', 'Cập nhật học phí thành công.');
            } else {
                setFlash('danger', 'Lỗi khi cập nhật học phí.');
            }
        }

        $this->redirect('/admin/hoc-phi/cap-nhat');
    }

    public function confirm() {
        $id = (int)($_GET['id'] ?? 0);
        $maSvFilter = trim($_GET['ma_sv'] ?? '');

        if ($id > 0 && ($_GET['action'] ?? '') === 'mark') {
            $affected = $this->tuitionModel->confirmTuitionSingle($id);
            setFlash('success', $affected > 0 ? 'Đã xác nhận nộp học phí cho bản ghi.' : 'Không tìm thấy bản ghi để xác nhận.');
            $redirectUrl = '/admin/hoc-phi/xac-nhan';
            if ($maSvFilter !== '') {
                $redirectUrl .= '?ma_sv=' . urlencode($maSvFilter);
            }
            $this->redirect($redirectUrl);
        }

        $pendingFees = $this->tuitionModel->getPendingFees($maSvFilter);

        $this->view('admin/tuition/xac_nhan', [
            'pendingFees' => $pendingFees,
            'filterMSV' => $maSvFilter,
            'page_title' => 'Xác nhận học phí',
            'active_menu' => 'hoc_phi'
        ]);
    }

    public function processConfirm() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'confirm') {
            $this->redirect('/admin/hoc-phi/xac-nhan');
        }

        $singleId = (int)($_POST['single_id'] ?? 0);
        $selected = $singleId > 0 ? [$singleId] : array_map('intval', $_POST['selected'] ?? []);
        if (empty($selected)) {
            setFlash('danger', 'Vui lòng chọn ít nhất một bản ghi để xác nhận.');
        } else {
            $affected = $this->tuitionModel->confirmTuitionArray($selected);
            if ($affected !== false) {
                setFlash('success', 'Xác nhận đã nộp học phí cho ' . $affected . ' bản ghi.');
            } else {
                setFlash('danger', 'Lỗi khi xác nhận.');
            }
        }
        $this->redirect('/admin/hoc-phi/xac-nhan');
    }

    public function autoCalculate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/hoc-phi/cap-nhat');
        }

        $hocKy = max(1, min(8, (int)($_POST['hoc_ky'] ?? 0)));
        $namHoc = trim($_POST['nam_hoc'] ?? '');
        $donGia = max(0, (float)($_POST['don_gia'] ?? 0));
        $hanNop = trim($_POST['han_nop'] ?? '');

        if ($donGia <= 0 || $hocKy <= 0 || $namHoc === '') {
            setFlash('danger', 'Vui lòng chọn học kỳ, nhập năm học và đơn giá hợp lệ.');
        } else {
            $result = $this->tuitionModel->autoCalculateTuition($hocKy, $namHoc, $donGia, $hanNop);
            if ($result > 0) {
                setFlash('success', "Tính học phí tự động thành công cho $result đăng ký học phần thuộc HK $hocKy năm học $namHoc.");
            } else {
                setFlash('warning', "Không tìm thấy đăng ký học phần đã duyệt nào phù hợp trong học kỳ/năm học đã chọn.");
            }
        }

        $this->redirect('/admin/hoc-phi/cap-nhat');
    }
}
