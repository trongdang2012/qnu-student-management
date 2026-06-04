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

        // PhÃ¢n trang
        $limit = 15;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $item = null;
        if ($action === 'edit' && $id > 0) {
            $item = $this->courseModel->getClassById($id);
            if (!$item) {
                setFlash('danger', 'KhÃ´ng tÃ¬m tháº¥y lá»›p há»c pháº§n cáº§n sá»­a.');
                $this->redirect('/admin/lop-hoc-phan');
            }
        }

        $list = $this->courseModel->getClasses($search, $hoc_ky, $giang_vien, $khoa, $limit, $offset);
        $totalItems = $this->courseModel->countClasses($search, $hoc_ky, $giang_vien, $khoa);
        $totalPages = (int)ceil($totalItems / $limit);

        // Láº¥y danh sÃ¡ch cÃ¡c há»c pháº§n hoáº¡t Ä‘á»™ng Ä‘á»ƒ lÃ m dropdown chá»n
        $allCourses = $this->courseModel->getCourses('', 0, '', '', 1000, 0);
        $nganhList = $this->courseModel->getNganhListInCtdt();
        $classStats = $this->courseModel->getClassDashboardStats($hoc_ky, NAM_HOC_HIEN_TAI);
        $classAlerts = $this->courseModel->getClassOperationalAlerts($hoc_ky, NAM_HOC_HIEN_TAI);

        $this->view('admin/class/index', [
            'list' => $list,
            'allCourses' => $allCourses,
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
            'page_title' => 'Quáº£n lÃ½ Lá»›p há»c pháº§n',
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
        $trang_thai_mo_lop = trim($_POST['trang_thai_mo_lop'] ?? 'Äang má»Ÿ');

        $ngay_bat_dau_dk = trim($_POST['ngay_bat_dau_dk'] ?? '');
        $ngay_ket_thuc_dk = trim($_POST['ngay_ket_thuc_dk'] ?? '');

        $ngay_bat_dau_dk = $ngay_bat_dau_dk !== '' ? $ngay_bat_dau_dk : null;
        $ngay_ket_thuc_dk = $ngay_ket_thuc_dk !== '' ? $ngay_ket_thuc_dk : null;

        $search_keep = $_POST['search_keep'] ?? '';

        if ($ma_lop_hp === '' || $hoc_phan_id <= 0 || $giang_vien === '' || $ngay_bat_dau === '' || $ngay_ket_thuc === '') {
            setFlash('danger', 'Vui lÃ²ng Ä‘iá»n Ä‘áº§y Ä‘á»§ cÃ¡c thÃ´ng tin báº¯t buá»™c.');
        } elseif ($si_so_toi_da <= 0) {
            setFlash('danger', 'SÄ© sá»‘ tá»‘i Ä‘a pháº£i lá»›n hÆ¡n 0.');
        } elseif ($ngay_bat_dau_dk !== null && $ngay_ket_thuc_dk !== null && $ngay_bat_dau_dk > $ngay_ket_thuc_dk) {
            setFlash('danger', 'NgÃ y báº¯t Ä‘áº§u Ä‘Äƒng kÃ½ khÃ´ng Ä‘Æ°á»£c lá»›n hÆ¡n ngÃ y káº¿t thÃºc Ä‘Äƒng kÃ½.');
        } else {
            if ($id > 0) {
                // Sá»­a thÃ´ng tin lá»›p há»c pháº§n
                $exists = $this->courseModel->getClassByCodeExceptId($ma_lop_hp, $id);
                if ($exists) {
                    setFlash('danger', 'MÃ£ lá»›p há»c pháº§n Ä‘Ã£ tá»“n táº¡i trÃªn má»™t lá»›p khÃ¡c. Vui lÃ²ng dÃ¹ng mÃ£ khÃ¡c.');
                } else {
                    // Äá»‘i vá»›i chá»‰nh sá»­a, cho phÃ©p chá»‰nh sá»­a cÃ¡c trÆ°á»ng bao gá»“m cáº£ thá»i gian Ä‘Äƒng kÃ½
                    $this->courseModel->updateClass($id, [
                        'giang_vien' => $giang_vien,
                        'si_so_toi_da' => $si_so_toi_da,
                        'ngay_bat_dau' => $ngay_bat_dau,
                        'ngay_ket_thuc' => $ngay_ket_thuc,
                        'trang_thai_mo_lop' => $trang_thai_mo_lop,
                        'ngay_bat_dau_dk' => $ngay_bat_dau_dk,
                        'ngay_ket_thuc_dk' => $ngay_ket_thuc_dk
                    ]);
                    setFlash('success', 'Cáº­p nháº­t thÃ´ng tin lá»›p há»c pháº§n thÃ nh cÃ´ng.');
                }
            } else {
                // Táº¡o lá»›p há»c pháº§n má»›i
                $exists = $this->courseModel->getClassByCode($ma_lop_hp);
                if ($exists) {
                    setFlash('danger', 'MÃ£ lá»›p há»c pháº§n Ä‘Ã£ tá»“n táº¡i. Vui lÃ²ng chá»n mÃ£ khÃ¡c.');
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
                        'trang_thai_mo_lop' => $trang_thai_mo_lop,
                        'ngay_bat_dau_dk' => $ngay_bat_dau_dk,
                        'ngay_ket_thuc_dk' => $ngay_ket_thuc_dk
                    ]);
                    setFlash('success', 'Táº¡o lá»›p há»c pháº§n má»›i thÃ nh cÃ´ng.');
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
                setFlash('danger', 'KhÃ´ng thá»ƒ xÃ³a lá»›p há»c pháº§n nÃ y vÃ¬ Ä‘Ã£ cÃ³ sinh viÃªn Ä‘Äƒng kÃ½ há»c.');
            } elseif ($this->courseModel->deleteClass($id)) {
                setFlash('success', 'XÃ³a lá»›p há»c pháº§n thÃ nh cÃ´ng.');
            } else {
                setFlash('danger', 'Lá»—i há»‡ thá»‘ng khi xÃ³a lá»›p há»c pháº§n.');
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

    public function batchOpen() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/lop-hoc-phan');
        }

        $nganh = trim($_POST['nganh'] ?? '');
        $hoc_ky_ctdt = max(1, min(8, (int)($_POST['hoc_ky_ctdt'] ?? 1))); // Há»c ká»³ tá»« CTDT
        $hoc_ky_hoc_vu = max(1, min(3, (int)($_POST['hoc_ky_hoc_vu'] ?? HOC_KY_HIEN_TAI))); // Há»c ká»³ há»c vá»¥
        $namHoc = trim($_POST['nam_hoc'] ?? NAM_HOC_HIEN_TAI);

        $ngay_bat_dau_dk = trim($_POST['ngay_bat_dau_dk'] ?? '');
        $ngay_ket_thuc_dk = trim($_POST['ngay_ket_thuc_dk'] ?? '');

        $ngay_bat_dau_dk = $ngay_bat_dau_dk !== '' ? $ngay_bat_dau_dk : null;
        $ngay_ket_thuc_dk = $ngay_ket_thuc_dk !== '' ? $ngay_ket_thuc_dk : null;

        if (empty($nganh)) {
            setFlash('danger', 'Vui lÃ²ng chá»n ngÃ nh Ä‘á»ƒ má»Ÿ lá»›p.');
        } else {
            $result = $this->courseModel->batchOpenClasses($nganh, $hoc_ky_ctdt, $hoc_ky_hoc_vu, $namHoc, $ngay_bat_dau_dk, $ngay_ket_thuc_dk);
            if ($result > 0) {
                setFlash('success', "âœ“ Má»Ÿ thÃ nh cÃ´ng <strong>$result lá»›p há»c pháº§n</strong> cho ngÃ nh <strong>$nganh</strong> (HK$hoc_ky_ctdt CTDT â†’ HK$hoc_ky_hoc_vu há»c vá»¥).
                    <br>âš ï¸ LÆ°u Ã½: Giáº£ng viÃªn chÆ°a Ä‘Æ°á»£c phÃ¢n cÃ´ng. Vui lÃ²ng vÃ o cÃ¡c lá»›p Ä‘á»ƒ phÃ¢n cÃ´ng giáº£ng viÃªn.");
            } else {
                setFlash('warning', "KhÃ´ng cÃ³ lá»›p há»c pháº§n má»›i nÃ o Ä‘Æ°á»£c táº¡o. CÃ³ thá»ƒ Ä‘Ã£ Ä‘Æ°á»£c má»Ÿ trÆ°á»›c Ä‘Ã³ hoáº·c ngÃ nh khÃ´ng cÃ³ mÃ´n há»c nÃ o thuá»™c HK$hoc_ky_ctdt CTDT.");
            }
        }

        $this->redirect('/admin/lop-hoc-phan');
    }
}
