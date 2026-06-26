<?php
/**
 * QNU SMS - MVC Front Controller
 */
session_start();

define('ROOT', __DIR__);

// Tải file hằng số
require_once ROOT . '/config/constants.php';
// Tải cấu hình cơ sở dữ liệu
require_once ROOT . '/config/database.php';
// Tải các hàm session (giữ tạm thời để tương thích)
require_once ROOT . '/includes/session.php';

// Autoloader cơ bản cho chuẩn PSR-4
spl_autoload_register(function ($class) {
    // Prefix namespace: App\
    $prefix = 'App\\';
    $base_dir = ROOT . '/app/';

    // Kiểm tra xem class có dùng prefix này không
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Lấy tên class tương đối
    $relative_class = substr($class, $len);

    // Xây dựng đường dẫn file
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Khởi tạo Router
$router = new \App\Core\Router();

// Định nghĩa các route cơ bản
$router->get('/', function () {
    if (isLoggedIn()) {
        header('Location: ' . BASE_URL . '/student/dashboard');
    } else {
        header('Location: ' . BASE_URL . '/auth/login');
    }
    exit;
});

// Route của Auth
$router->get('/auth/login', 'AuthController@index');
$router->post('/auth/login', 'AuthController@processLogin');
$router->get('/auth/otp', 'AuthController@otp');
$router->post('/auth/otp', 'AuthController@processOtp');
$router->get('/auth/logout', 'AuthController@logout');

// Route Quên mật khẩu
$router->get('/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/auth/forgot-password', 'AuthController@processForgotPassword');
$router->get('/auth/verify-passcode', 'AuthController@verifyPasscode');
$router->post('/auth/verify-passcode', 'AuthController@processVerifyPasscode');
$router->get('/auth/reset-password', 'AuthController@resetPassword');
$router->post('/auth/reset-password', 'AuthController@processResetPassword');

// Route của Sinh viên
$router->get('/student/dashboard', 'StudentController@dashboard');
$router->get('/student/ho-so', 'StudentController@profile');
$router->get('/student/cap-nhat', 'StudentController@updateProfile');
$router->post('/student/cap-nhat', 'StudentController@processUpdateProfile');
$router->post('/student/doi-mat-khau', 'StudentController@processChangePassword');
$router->get('/student/tien-do', 'StudentController@progress');
$router->post('/student/tien-do/dat-muc-tieu', 'StudentController@saveGpaTarget');

$router->get('/student/thong-bao', 'StudentController@notifications');
$router->post('/student/thong-bao/doc', 'StudentController@markNotificationRead');

$router->get('/student/chuong-trinh', 'CourseController@program');
$router->get('/student/diem-hoc-tap', 'CourseController@grades');
$router->get('/student/thoi-khoa-bieu', 'CourseController@schedule');
$router->get('/student/dang-ky-hoc-phan', 'CourseController@register');
$router->post('/student/dang-ky-hoc-phan', 'CourseController@processRegister');

$router->get('/student/tai-lieu', 'DocumentController@index');
$router->post('/student/tai-lieu', 'DocumentController@index');
$router->get('/student/download', 'DocumentController@download');
$router->get('/student/preview-tai-lieu', 'DocumentController@preview');

$router->get('/student/diem-ren-luyen', 'StudentController@trainingPoints');
$router->get('/student/hoc-phi', 'StudentController@tuitionFees');
$router->post('/student/hoc-phi/nop', 'StudentController@payTuition');

// Route của Admin
$router->get('/admin/dashboard', 'Admin\DashboardController@index');
$router->get('/admin/sinh-vien', 'Admin\StudentController@index');
$router->get('/admin/sinh-vien/export-template', 'Admin\StudentController@exportTemplate');
$router->get('/admin/sinh-vien/edit', 'Admin\StudentController@edit');
$router->post('/admin/sinh-vien/edit', 'Admin\StudentController@processEdit');
$router->get('/admin/sinh-vien/delete', 'Admin\StudentController@delete');
$router->post('/admin/sinh-vien/import', 'Admin\StudentController@import');

$router->get('/admin/hoc-phan', 'Admin\CourseController@index');
$router->post('/admin/hoc-phan/save', 'Admin\CourseController@save');
$router->post('/admin/hoc-phan/delete', 'Admin\CourseController@delete');
$router->post('/admin/hoc-phan/duplicate-ctdt', 'Admin\CourseController@duplicateCtdt');

$router->get('/admin/lop-hoc-phan', 'Admin\ClassController@index');
$router->post('/admin/lop-hoc-phan/save', 'Admin\ClassController@save');
$router->post('/admin/lop-hoc-phan/delete', 'Admin\ClassController@delete');
$router->post('/admin/lop-hoc-phan/auto-generate', 'Admin\ClassController@autoGenerate');
$router->post('/admin/lop-hoc-phan/batch-open', 'Admin\ClassController@batchOpen');
$router->post('/admin/lop-hoc-phan/batch-open-selected', 'Admin\ClassController@batchOpenSelected');
$router->post('/admin/lop-hoc-phan/scan-and-cancel', 'Admin\ClassController@scanAndCancel');

$router->get('/admin/khoa', 'Admin\FacultyController@index');
$router->post('/admin/khoa/save', 'Admin\FacultyController@save');
$router->post('/admin/khoa/delete', 'Admin\FacultyController@delete');

$router->get('/admin/giang-vien', 'Admin\GiangVienController@index');
$router->post('/admin/giang-vien/save', 'Admin\GiangVienController@save');
$router->post('/admin/giang-vien/delete', 'Admin\GiangVienController@delete');

$router->get('/admin/phong-hoc', 'Admin\PhongHocController@index');
$router->post('/admin/phong-hoc/save', 'Admin\PhongHocController@save');
$router->post('/admin/phong-hoc/delete', 'Admin\PhongHocController@delete');

$router->get('/admin/nganh', 'Admin\MajorController@index');
$router->post('/admin/nganh/save', 'Admin\MajorController@save');
$router->post('/admin/nganh/delete', 'Admin\MajorController@delete');

$router->get('/admin/lop-sinh-hoat', 'Admin\ClassStudentController@index');
$router->post('/admin/lop-sinh-hoat/save', 'Admin\ClassStudentController@save');
$router->post('/admin/lop-sinh-hoat/delete', 'Admin\ClassStudentController@delete');

$router->get('/admin/diem/hoc-tap', 'Admin\GradeController@academic');
$router->post('/admin/diem/hoc-tap/save', 'Admin\GradeController@academicSave');
$router->get('/admin/diem/hoc-tap/export-template', 'Admin\GradeController@academicExportTemplate');
$router->post('/admin/diem/hoc-tap/import', 'Admin\GradeController@academicImport');
$router->get('/admin/diem/ren-luyen', 'Admin\GradeController@training');
$router->post('/admin/diem/ren-luyen/save', 'Admin\GradeController@trainingSave');
$router->get('/admin/diem/ren-luyen/departments', 'Admin\GradeController@getDepartments');
$router->get('/admin/diem/ren-luyen/classes', 'Admin\GradeController@getClasses');
$router->get('/admin/diem/ren-luyen/export-template', 'Admin\GradeController@trainingExportTemplate');
$router->post('/admin/diem/ren-luyen/import', 'Admin\GradeController@trainingImport');

$router->get('/admin/thoi-khoa-bieu', 'Admin\ScheduleController@index');
$router->post('/admin/thoi-khoa-bieu/save', 'Admin\ScheduleController@save');
$router->post('/admin/thoi-khoa-bieu/delete', 'Admin\ScheduleController@delete');
$router->get('/admin/thoi-khoa-bieu/optimize', 'Admin\ScheduleController@optimize');
$router->post('/admin/thoi-khoa-bieu/optimize/save', 'Admin\ScheduleController@processOptimize');

$router->get('/admin/users', 'Admin\UserController@index');
$router->get('/admin/users/add', 'Admin\UserController@add');
$router->post('/admin/users/process-add', 'Admin\UserController@processAdd');
$router->get('/admin/users/edit', 'Admin\UserController@edit');
$router->post('/admin/users/process-edit', 'Admin\UserController@processEdit');
$router->get('/admin/users/delete', 'Admin\UserController@processDelete');

$router->get('/admin/tai-lieu', 'Admin\DocumentController@index');
$router->get('/admin/tai-lieu/add', 'Admin\DocumentController@add');
$router->post('/admin/tai-lieu/process-add', 'Admin\DocumentController@processAdd');
$router->get('/admin/tai-lieu/edit', 'Admin\DocumentController@edit');
$router->post('/admin/tai-lieu/process-edit', 'Admin\DocumentController@processEdit');
$router->post('/admin/tai-lieu/delete', 'Admin\DocumentController@delete');
$router->get('/admin/tai-lieu/download', 'Admin\DocumentController@download');

$router->get('/admin/hoc-phi', 'Admin\TuitionController@index');
$router->get('/admin/hoc-phi/bao-cao', 'Admin\TuitionController@report');
$router->get('/admin/hoc-phi/cap-nhat', 'Admin\TuitionController@update');
$router->post('/admin/hoc-phi/cap-nhat/save', 'Admin\TuitionController@saveUpdate');
$router->get('/admin/hoc-phi/xac-nhan', 'Admin\TuitionController@confirm');
$router->post('/admin/hoc-phi/xac-nhan/save', 'Admin\TuitionController@processConfirm');
$router->post('/admin/hoc-phi/tinh-tu-dong', 'Admin\TuitionController@autoCalculate');

$router->get('/admin/data-sync', 'Admin\DataSyncController@index');
$router->get('/admin/data-sync/export', 'Admin\DataSyncController@export');
$router->post('/admin/data-sync/import', 'Admin\DataSyncController@import');

$router->get('/admin/thong-bao', 'Admin\NotificationController@index');
$router->get('/admin/thong-bao/tao-moi', 'Admin\NotificationController@create');
$router->post('/admin/thong-bao/tao-moi', 'Admin\NotificationController@processCreate');
$router->get('/admin/thong-bao/delete', 'Admin\NotificationController@delete');

// Phân giải Route hiện tại
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
