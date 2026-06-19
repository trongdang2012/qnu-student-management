<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\StudentModel;
use App\Models\UserModel;

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
            die('<div style="padding:24px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:12px;font-family:\'Roboto\',sans-serif;margin:60px auto;max-width:600px;box-shadow:0 10px 30px rgba(0,0,0,0.05);">
                <h3 style="margin-top:0;color:#b02a37;font-size:20px;display:flex;align-items:center;gap:10px;"><i class="fas fa-exclamation-triangle"></i> Lỗi Liên Kết Hồ Sơ</h3>
                <p style="font-size:14.5px;line-height:1.6;">Tài khoản của bạn (ID: <strong>' . htmlspecialchars($_SESSION['user_id']) . '</strong>, Username: <strong>' . htmlspecialchars($_SESSION['username']) . '</strong>) đã đăng nhập thành công vào hệ thống. Tuy nhiên, <strong>hồ sơ sinh viên tương ứng của tài khoản này chưa được thiết lập</strong> trong bảng dữ liệu <code>sinh_vien</code>!</p>
                <p style="font-size:14px;color:#5c636a;margin-bottom:20px;">Vui lòng liên hệ Admin hệ thống để liên kết tài khoản này với mã số sinh viên tương ứng.</p>
                <hr style="border:none;border-top:1px dashed #f5c6cb;margin:18px 0;">
                <div style="text-align:right;">
                    <a href="' . BASE_URL . '/auth/logout" style="display:inline-block;padding:8px 20px;background:#dc3545;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:13.5px;box-shadow:0 4px 10px rgba(220,53,69,0.15);transition:background 0.2s;">Quay lại đăng nhập</a>
                </div>
            </div>');
        }

        $stats = $studentModel->getDashboardStats($sv['id'], $sv['nganh']);
        $drl = $studentModel->getRecentDrl($sv['id']);
        $diem_recent = $studentModel->getRecentGrades($sv['id'], 4);
        $latestNotices = $studentModel->getNotifications($sv['id']);

        $this->view('student/dashboard', [
            'sv' => $sv,
            'stats' => $stats,
            'drl' => $drl,
            'diem_recent' => $diem_recent,
            'latestNotices' => $latestNotices,
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

        // Lấy trạng thái 2FA của user từ bảng users
        $userModel = new UserModel();
        $user = $userModel->findById($_SESSION['user_id']);
        $two_factor_auth = $user['two_factor_auth'] ?? 1;

        $this->view('student/update_profile', [
            'sv' => $sv,
            'two_factor_auth' => $two_factor_auth,
            'page_title' => 'Cập nhật thông tin',
            'active_menu' => 'ca_nhan'
        ]);
    }

    public function processUpdateProfile() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }

        // Kiểm tra CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            return $this->json(['success' => false, 'message' => 'Lỗi bảo mật: CSRF Token không hợp lệ.']);
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

        // Kiểm tra trùng lặp Email và Số điện thoại
        $db = \App\Core\Database::getInstance();
        if (empty($errors['email']) && !empty($email)) {
            $checkEmail = $db->fetch("SELECT id FROM sinh_vien WHERE email = ? AND id != ?", [$email, $sid]);
            if ($checkEmail) {
                $errors['email'] = 'Email này đã được sử dụng bởi một tài khoản khác.';
            }
        }

        if (empty($errors['sdt']) && !empty($sdt)) {
            $checkPhone = $db->fetch("SELECT id FROM sinh_vien WHERE so_dien_thoai = ? AND id != ?", [$sdt, $sid]);
            if ($checkPhone) {
                $errors['sdt'] = 'Số điện thoại này đã được sử dụng bởi một tài khoản khác.';
            }
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

                // Cập nhật trạng thái 2FA
                $two_factor_auth = isset($_POST['two_factor_auth']) ? (int)$_POST['two_factor_auth'] : 0;
                $userModel = new UserModel();
                $userModel->updateTwoFactorAuth($_SESSION['user_id'], $two_factor_auth);

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

        // Kiểm tra CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            return $this->json(['success' => false, 'message' => 'Lỗi bảo mật: CSRF Token không hợp lệ.']);
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

        // Phân tích GPA mục tiêu
        $analysis = null;
        $target = $progressInfo['gpa_muc_tieu'];
        if ($target) {
            $cpa = $progressInfo['cpa'];
            $tc_tich_luy = $progressInfo['tc_tich_luy'];
            $tc_total = $progressInfo['tc_total'];
            $tc_dat = $progressInfo['tc_dat_total'];
            $tc_remain = max(0, $tc_total - $tc_dat);

            $points_current = $cpa * $tc_tich_luy;
            $points_target = $target * $tc_total;
            $points_need = $points_target - $points_current;

            if ($tc_remain > 0) {
                $cpa_remain_avg = $points_need / $tc_remain;
            } else {
                $cpa_remain_avg = 0;
            }

            $status = 'possible'; // 'possible', 'impossible', 'achieved'
            if ($cpa_remain_avg > 4.0) {
                $status = 'impossible';
            } elseif ($cpa_remain_avg <= 1.0) {
                $status = 'achieved';
            }

            // Tính toán số môn học còn lại
            $avg_credit_per_course = 3.0;
            $estimated_courses = ceil($tc_remain / $avg_credit_per_course);

            // Gợi ý tổ hợp môn học còn lại
            $scenarios = [];
            if ($status === 'possible') {
                // Scenario 1: Đạt A và B
                // A (4.0), B (3.0)
                // x * 4.0 + y * 3.0 = points_need, x + y = tc_remain => x = points_need - 3*tc_remain
                $x_A = $points_need - 3.0 * $tc_remain;
                $y_B = $tc_remain - $x_A;

                if ($x_A >= 0 && $x_A <= $tc_remain) {
                    $m_A = round($x_A / 3.0);
                    $m_B = max(0, $estimated_courses - $m_A);
                    $scenarios[] = [
                        'title' => 'Tập trung đạt kết quả Xuất sắc (Điểm A/A+ & B)',
                        'detail' => "Cần khoảng <strong>{$m_A} môn</strong> đạt điểm <strong>A/A+</strong> (hệ 4 là 4.0, hoặc từ 8.5 trở lên) và <strong>{$m_B} môn</strong> đạt điểm <strong>B/B+</strong> (hệ 4 là 3.0, hoặc từ 7.0 trở lên)."
                    ];
                } else if ($x_A < 0) {
                    // Cần điểm trung bình thấp hơn B
                    // x * 3.0 + y * 2.0 = points_need => x = points_need - 2*tc_remain
                    $x_B = $points_need - 2.0 * $tc_remain;
                    $y_C = $tc_remain - $x_B;
                    $m_B = round($x_B / 3.0);
                    $m_C = max(0, $estimated_courses - $m_B);
                    $scenarios[] = [
                        'title' => 'Tự tin đạt kết quả Khá (Điểm B/B+ & C/C+)',
                        'detail' => "Cần khoảng <strong>{$m_B} môn</strong> đạt điểm <strong>B/B+</strong> (từ 7.0 trở lên) và <strong>{$m_C} môn</strong> đạt điểm <strong>C/C+</strong> (từ 5.5 trở lên)."
                    ];
                } else {
                    // Cần điểm trung bình cao hơn B (B+ và A)
                    // x * 4.0 + y * 3.5 = points_need => 0.5x = points_need - 3.5*tc_remain => x = 2 * (points_need - 3.5*tc_remain)
                    $x_A2 = 2.0 * ($points_need - 3.5 * $tc_remain);
                    $y_Bplus = $tc_remain - $x_A2;
                    $m_A = round($x_A2 / 3.0);
                    $m_Bplus = max(0, $estimated_courses - $m_A);
                    $scenarios[] = [
                        'title' => 'Nỗ lực đạt tối đa điểm số (Điểm A/A+ & B+)',
                        'detail' => "Cần khoảng <strong>{$m_A} môn</strong> đạt điểm <strong>A/A+</strong> (từ 8.5 trở lên) và <strong>{$m_Bplus} môn</strong> đạt điểm <strong>B+</strong> (từ 7.8 trở lên)."
                    ];
                }

                // Scenario 2: Đồng đều
                $needed_grade_chu = 'A/A+';
                $needed_grade_so = '8.5 - 10';
                if ($cpa_remain_avg <= 1.5) {
                    $needed_grade_chu = 'D+';
                    $needed_grade_so = '4.8 - 5.4';
                } elseif ($cpa_remain_avg <= 2.0) {
                    $needed_grade_chu = 'C';
                    $needed_grade_so = '5.5 - 6.2';
                } elseif ($cpa_remain_avg <= 2.5) {
                    $needed_grade_chu = 'C+';
                    $needed_grade_so = '6.3 - 6.9';
                } elseif ($cpa_remain_avg <= 3.0) {
                    $needed_grade_chu = 'B';
                    $needed_grade_so = '7.0 - 7.7';
                } elseif ($cpa_remain_avg <= 3.5) {
                    $needed_grade_chu = 'B+';
                    $needed_grade_so = '7.8 - 8.4';
                }
                $scenarios[] = [
                    'title' => 'Phân bổ học lực đồng đều',
                    'detail' => "Tất cả các môn còn lại cần đạt điểm trung bình tối thiểu là <strong>" . number_format($cpa_remain_avg, 2) . "</strong> (tương đương điểm chữ <strong>{$needed_grade_chu}</strong>, từ <strong>{$needed_grade_so}</strong> hệ 10)."
                ];
            }

            // Gợi ý cải thiện điểm
            $improves = $studentModel->getImprovementSuggestions($sv['id']);
            $improvement_list = [];
            foreach ($improves as $imp) {
                // Giả lập nếu cải thiện môn học này lên A (4.0)
                $cur_val = (float)$imp['max_he4'];
                $tc_imp = (int)$imp['so_tin_chi'];
                $points_new = $points_current - ($cur_val * $tc_imp) + (4.0 * $tc_imp);
                $cpa_new = round($points_new / $tc_tich_luy, 2);
                $cpa_remain_avg_new = ($tc_remain > 0) ? ($points_target - $points_new) / $tc_remain : 0;

                $improvement_list[] = [
                    'ma_hp' => $imp['ma_hp'],
                    'ten_hp' => $imp['ten_hp'],
                    'so_tin_chi' => $tc_imp,
                    'diem_chu' => $imp['max_chu'],
                    'diem_he4' => $cur_val,
                    'cpa_new' => $cpa_new,
                    'cpa_remain_avg_new' => max(1.0, $cpa_remain_avg_new)
                ];
            }

            $analysis = [
                'target' => $target,
                'cpa_remain_avg' => $cpa_remain_avg,
                'status' => $status,
                'estimated_courses' => $estimated_courses,
                'scenarios' => $scenarios,
                'improvements' => $improvement_list,
                'tc_remain' => $tc_remain
            ];
        }

        $this->view('student/progress', [
            'sv' => $sv,
            'progressInfo' => $progressInfo,
            'analysis' => $analysis,
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

    public function payTuition() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/student/hoc-phi');
        }

        // Kiểm tra CSRF Token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!validateCsrfToken($csrfToken)) {
            setFlash('danger', 'Lỗi bảo mật: CSRF Token không hợp lệ.');
            $this->redirect('/student/hoc-phi');
        }

        $tuitionId = (int)($_POST['tuition_id'] ?? 0);
        if ($tuitionId <= 0) {
            setFlash('danger', 'ID khoản học phí không hợp lệ.');
            $this->redirect('/student/hoc-phi');
        }

        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) {
            $this->redirect('/auth/logout');
        }

        $result = $studentModel->payTuition($sv['id'], $tuitionId);
        if ($result['success']) {
            setFlash('success', $result['message']);
        } else {
            setFlash('danger', $result['message']);
        }

        $this->redirect('/student/hoc-phi');
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

    public function saveGpaTarget() {
        $this->requireStudent();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        }
        
        $studentModel = new StudentModel();
        $sv = $studentModel->getStudentInfo($_SESSION['user_id']);
        if (!$sv) return $this->json(['success' => false, 'message' => 'Lỗi xác thực.']);

        $gpaTarget = isset($_POST['gpa_muc_tieu']) && $_POST['gpa_muc_tieu'] !== '' ? (float)$_POST['gpa_muc_tieu'] : null;
        
        if ($gpaTarget !== null && ($gpaTarget < 1.0 || $gpaTarget > 4.0)) {
            return $this->json(['success' => false, 'message' => 'Điểm GPA mục tiêu hệ 4 phải nằm trong khoảng từ 1.0 đến 4.0.']);
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("UPDATE sinh_vien SET gpa_muc_tieu = :gpa WHERE id = :sid", [
            'gpa' => $gpaTarget,
            'sid' => $sv['id']
        ]);

        if ($stmt) {
            return $this->json(['success' => true, 'message' => 'Đã cập nhật mục tiêu học tập thành công!']);
        }

        return $this->json(['success' => false, 'message' => 'Lỗi lưu dữ liệu.']);
    }
}
