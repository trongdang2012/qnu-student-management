<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminUserModel;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->userModel = new AdminUserModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $nganh = trim($_GET['nganh'] ?? '');
        $lop = trim($_GET['lop'] ?? '');
        $sort = trim($_GET['sort'] ?? 'created_at');
        $order = trim($_GET['order'] ?? 'desc');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->userModel->countUsers($search, $khoa, $nganh, $lop);
        $total_pages = ceil($total / $per_page);

        $users = $this->userModel->getUsers($offset, $per_page, $search, $khoa, $nganh, $lop, $sort, $order);
        $facultiesClassesTree = $this->userModel->getFacultiesAndClasses();

        $this->view('admin/users/index', [
            'users' => $users,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'search' => $search,
            'khoa' => $khoa,
            'nganh' => $nganh,
            'lop' => $lop,
            'sort' => $sort,
            'order' => $order,
            'facultiesClassesTree' => $facultiesClassesTree,
            'page_title' => 'Quản lý Tài khoản',
            'active_menu' => 'users'
        ]);
    }

    public function add() {
        $this->view('admin/users/add', [
            'page_title' => 'Thêm Tài khoản',
            'active_menu' => 'users'
        ]);
    }

    public function processAdd() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = trim($_POST['role'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($username)) {
            $errors[] = 'Username không được để trống';
        }
        if (strlen($username) < 3) {
            $errors[] = 'Username phải có ít nhất 3 ký tự';
        }
        if (empty($password)) {
            $errors[] = 'Mật khẩu không được để trống';
        } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
            $errors[] = 'Mật khẩu phải có tối thiểu 6 ký tự, bao gồm chữ in hoa, số và ký tự đặc biệt';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }
        if (!in_array($role, ['admin', 'student'])) {
            $errors[] = 'Role không hợp lệ';
        }
        if (empty($email)) {
            $errors[] = 'Email không được để trống';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không đúng định dạng';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/admin/users/add');
        }

        if ($this->userModel->getUserByUsername($username)) {
            $_SESSION['errors'] = ['Username đã tồn tại trong hệ thống'];
            $this->redirect('/admin/users/add');
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        if ($this->userModel->insertUser($username, $hashed_password, $role, $email)) {
            setFlash('success', 'Thêm tài khoản thành công!');
            $this->redirect('/admin/users');
        } else {
            $_SESSION['errors'] = ['Lỗi khi thêm tài khoản'];
            $this->redirect('/admin/users/add');
        }
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/users');
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['errors'] = ['Tài khoản không tồn tại'];
            $this->redirect('/admin/users');
        }

        $this->view('admin/users/edit', [
            'user' => $user,
            'page_title' => 'Sửa Tài khoản',
            'active_menu' => 'users'
        ]);
    }

    public function processEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
        }

        $id = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $role = trim($_POST['role'] ?? '');

        $errors = [];

        if ($id <= 0) {
            $errors[] = 'ID không hợp lệ';
        }

        if (!empty($password) || !empty($password_confirm)) {
            if (strlen($password) < 6 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
                $errors[] = 'Mật khẩu phải có tối thiểu 6 ký tự, bao gồm chữ in hoa, số và ký tự đặc biệt';
            }
            if ($password !== $password_confirm) {
                $errors[] = 'Mật khẩu xác nhận không khớp';
            }
        }

        if (!in_array($role, ['admin', 'student'])) {
            $errors[] = 'Role không hợp lệ';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/admin/users/edit?id=' . $id);
        }

        if (!$this->userModel->getUserById($id)) {
            $_SESSION['errors'] = ['Tài khoản không tồn tại'];
            $this->redirect('/admin/users');
        }

        $success = false;
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $success = $this->userModel->updateUserPasswordAndRole($id, $hashed_password, $role);
        } else {
            $success = $this->userModel->updateUserRole($id, $role);
        }

        if ($success) {
            setFlash('success', 'Cập nhật tài khoản thành công!');
            $this->redirect('/admin/users');
        } else {
            $_SESSION['errors'] = ['Lỗi khi cập nhật tài khoản'];
            $this->redirect('/admin/users/edit?id=' . $id);
        }
    }

    public function processDelete() {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['errors'] = ['ID không hợp lệ'];
            $this->redirect('/admin/users');
        }

        if (!$this->userModel->getUserById($id)) {
            $_SESSION['errors'] = ['Tài khoản không tồn tại'];
            $this->redirect('/admin/users');
        }

        if ($this->userModel->deleteUser($id)) {
            setFlash('success', 'Xóa tài khoản thành công!');
        } else {
            $_SESSION['errors'] = ['Lỗi khi xóa tài khoản'];
        }
        
        $this->redirect('/admin/users');
    }
}
