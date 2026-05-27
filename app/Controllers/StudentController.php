<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudentModel;

class StudentController extends Controller {
    
    private function requireStudent() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
            $this->redirect('/auth/logout');
        }
    }

    public function dashboard() {
        $this->requireStudent();

        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);

        if (!$sv) {
            $this->redirect('/auth/logout');
        }

        $stats = $studentModel->getDashboardStats($sv['id'], $sv['nganh']);
        $drl = $studentModel->getRecentDrl($sv['id']);
        $diem_recent = $studentModel->getRecentGrades($sv['id'], 4);

        $this->view('student/dashboard', [
            'sv' => $sv,
            'stats' => $stats,
            'drl' => $drl,
            'diem_recent' => $diem_recent,
            'page_title' => 'Tổng quan',
            'active_menu' => 'dashboard'
        ]);
    }

    public function profile() {
        $this->requireStudent();
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        
        if (!$sv) {
            $this->redirect('/auth/logout');
        }

        $this->view('student/profile', [
            'sv' => $sv,
            'page_title' => 'Thông tin cá nhân',
            'active_menu' => 'ca_nhan'
        ]);
    }

    public function updateProfile() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $this->view('student/update_profile', [
            'sv' => $sv,
            'page_title' => 'Cập nhật thông tin',
            'active_menu' => 'ca_nhan'
        ]);
    }

    public function processUpdateProfile() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }

        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) return $this->json(['success' => false, 'message' => 'Lỗi xác thực.']);

        $sid = $sv['id'];
        $email = trim($_POST['email'] ?? '');
        $sdt   = trim($_POST['so_dien_thoai'] ?? '');
        $errors = [];

        if (empty($email)) {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Địa chỉ email không hợp lệ.';
        }

        if (!empty($sdt) && !preg_match('/^(0|\+84)[0-9]{9,10}$/', $sdt)) {
            $errors['sdt'] = 'Số điện thoại không hợp lệ.';
        }

        $upload_dir = ROOT . '/uploads/avatars/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0775, true);

        $new_avatar = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['avatar'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['avatar'] = 'Lỗi khi tải file lên.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors['avatar'] = 'Ảnh tối đa 2MB.';
            } elseif (!in_array(mime_content_type($file['tmp_name']), $allowed)) {
                $errors['avatar'] = 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.';
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'avatar_' . $sid . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    if (!empty($sv['anh_dai_dien'])) {
                        $old = ROOT . '/uploads/' . $sv['anh_dai_dien'];
                        if (file_exists($old)) @unlink($old);
                    }
                    $new_avatar = 'avatars/' . $filename;
                } else {
                    $errors['avatar'] = 'Không thể lưu ảnh.';
                }
            }
        }

        if (empty($errors)) {
            try {
                $studentModel->updateProfile($sid, $email, $sdt, $new_avatar);
                return $this->json([
                    'success' => true,
                    'message' => 'Cập nhật thành công!',
                    'avatar_url' => $new_avatar ? BASE_URL . '/uploads/' . $new_avatar : null
                ]);
            } catch (\Exception $e) {
                $errors['db'] = 'Lỗi lưu dữ liệu.';
            }
        }

        return $this->json(['success' => false, 'errors' => $errors]);
    }

    public function processChangePassword() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }

        $userId = $_SESSION['user_id'];
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            return $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ các trường.']);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->findById($userId);

        if (!$user || !password_verify($old_password, $user['password'])) {
            $errors['old_password'] = 'Mật khẩu hiện tại không đúng.';
        }

        if (strlen($new_password) < 6 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[\W_]/', $new_password)) {
            $errors['new_password'] = 'Mật khẩu phải có tối thiểu 6 ký tự, bao gồm chữ in hoa, số và ký tự đặc biệt.';
        }

        if ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors]);
        }

        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        if ($userModel->updatePassword($userId, $hashed)) {
            return $this->json(['success' => true, 'message' => 'Đổi mật khẩu thành công.']);
        }

        return $this->json(['success' => false, 'message' => 'Lỗi cập nhật dữ liệu.']);
    }

    public function progress() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $progressInfo = $studentModel->getProgressInfo($sv['id'], $sv['nganh']);

        $this->view('student/progress', [
            'sv' => $sv,
            'progressInfo' => $progressInfo,
            'page_title' => 'Tiến độ học tập',
            'active_menu' => 'ca_nhan'
        ]);
    }

    public function trainingPoints() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $trainingPointsInfo = $studentModel->getTrainingPoints($sv['id']);

        $this->view('student/training_points', [
            'sv' => $sv,
            'trainingPointsInfo' => $trainingPointsInfo,
            'page_title' => 'Điểm rèn luyện',
            'active_menu' => 'hoc_tap'
        ]);
    }

    public function tuitionFees() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $tuitionFeesInfo = $studentModel->getTuitionFees($sv['id']);

        $this->view('student/tuition_fees', [
            'sv' => $sv,
            'tuitionFeesInfo' => $tuitionFeesInfo,
            'page_title' => 'Học phí',
            'active_menu' => 'hoc_tap'
        ]);
    }
    public function notifications() {
        $this->requireStudent();
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) $this->redirect('/auth/logout');

        $notifications = $studentModel->getNotifications($sv['id']);

        $this->view('student/notifications', [
            'sv' => $sv,
            'notifications' => $notifications,
            'page_title' => 'Thông báo của tôi',
            'active_menu' => 'thong_bao'
        ]);
    }

    public function markNotificationRead() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentModel = new StudentModel();
            $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
            if (!$sv) {
                return $this->json(['success' => false]);
            }
            
            $notificationId = $_POST['id'] ?? 0;
            if ($notificationId) {
                $studentModel->markNotificationRead($sv['id'], $notificationId);
            }
            return $this->json(['success' => true]);
        }
    }
}
