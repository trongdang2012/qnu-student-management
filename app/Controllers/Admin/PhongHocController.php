<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\PhongHocModel;

class PhongHocController extends Controller {
    private $phongHocModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->phongHocModel = new PhongHocModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->phongHocModel->getTotalPhongHocs($search);
        $total_pages = ceil($total / $per_page);
        $phong_hocs = $this->phongHocModel->getPhongHocs($search, $per_page, $offset);

        $this->view('admin/phong_hoc/index', [
            'phong_hocs' => $phong_hocs,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'page_title' => 'Quản lý Phòng học',
            'active_menu' => 'dao_tao'
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/phong-hoc');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $ten_phong = trim($_POST['ten_phong'] ?? '');
        $loai_phong = trim($_POST['loai_phong'] ?? 'Lý thuyết');
        $suc_chua = isset($_POST['suc_chua']) ? (int)$_POST['suc_chua'] : 40;

        if (empty($ten_phong)) {
            setFlash('danger', 'Tên phòng không được để trống.');
            $this->redirect('/admin/phong-hoc');
        }

        // Kiểm tra trùng tên phòng
        $existing = $this->phongHocModel->getPhongHocByTen($ten_phong);
        if ($existing && ($id === 0 || $existing['id'] !== $id)) {
            setFlash('danger', 'Tên phòng này đã tồn tại trong hệ thống.');
            $this->redirect('/admin/phong-hoc');
        }

        $data = [
            'ten_phong' => $ten_phong,
            'loai_phong' => $loai_phong,
            'suc_chua' => $suc_chua
        ];

        if ($id > 0) {
            $res = $this->phongHocModel->updatePhongHoc($id, $data);
            if ($res) {
                setFlash('success', 'Cập nhật phòng học thành công.');
            } else {
                setFlash('danger', 'Cập nhật phòng học thất bại.');
            }
        } else {
            $res = $this->phongHocModel->addPhongHoc($data);
            if ($res) {
                setFlash('success', 'Thêm phòng học mới thành công.');
            } else {
                setFlash('danger', 'Thêm phòng học mới thất bại.');
            }
        }

        $this->redirect('/admin/phong-hoc');
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/phong-hoc');
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            setFlash('danger', 'Yêu cầu không hợp lệ.');
            $this->redirect('/admin/phong-hoc');
        }

        $res = $this->phongHocModel->deletePhongHoc($id);
        if ($res) {
            setFlash('success', 'Xóa phòng học thành công.');
        } else {
            setFlash('danger', 'Xóa phòng học thất bại.');
        }

        $this->redirect('/admin/phong-hoc');
    }
}
