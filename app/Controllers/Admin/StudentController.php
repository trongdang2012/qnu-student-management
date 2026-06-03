<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminStudentModel;

class StudentController extends Controller {
    private $studentModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
        $this->studentModel = new AdminStudentModel();
    }

    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $khoa = trim($_GET['khoa'] ?? '');
        $lop = trim($_GET['lop'] ?? '');
        $sort = trim($_GET['sort'] ?? 'ma_sv');
        $order = trim($_GET['order'] ?? 'asc');
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $total = $this->studentModel->getTotalStudents($search, $khoa, $lop);
        $total_pages = ceil($total / $per_page);
        $students = $this->studentModel->getStudents($search, $per_page, $offset, $khoa, $lop, $sort, $order);
        $facultiesClassesTree = $this->studentModel->getFacultiesAndClasses();

        $this->view('admin/student/index', [
            'students' => $students,
            'total' => $total,
            'page' => $page,
            'total_pages' => $total_pages,
            'search' => $search,
            'khoa' => $khoa,
            'lop' => $lop,
            'sort' => $sort,
            'order' => $order,
            'facultiesClassesTree' => $facultiesClassesTree,
            'page_title' => 'Quản lý Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function add() {
        $this->view('admin/student/add', [
            'page_title' => 'Thêm Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function processAdd() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sinh-vien/add');
        }

        $ma_sv = trim($_POST['ma_sv'] ?? '');
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
        $gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');
        $khoa = trim($_POST['khoa'] ?? '');
        $nien_khoa = trim($_POST['nien_khoa'] ?? NAM_HOC_HIEN_TAI);
        $dia_chi = trim($_POST['dia_chi'] ?? '');

        if (empty($ma_sv) || empty($ho_ten) || empty($nganh) || empty($lop)) {
            setFlash('danger', 'Vui lòng điền các trường bắt buộc');
            $this->redirect('/admin/sinh-vien/add');
        }

        if ($this->studentModel->getStudentByMaSv($ma_sv)) {
            setFlash('danger', 'Mã sinh viên đã tồn tại');
            $this->redirect('/admin/sinh-vien/add');
        }

        $ngay_sinh_db = !empty($ngay_sinh) ? $ngay_sinh : null;

        $data = [
            'user_id' => 0,
            'ma_sv' => $ma_sv,
            'ho_ten' => $ho_ten,
            'ngay_sinh' => $ngay_sinh_db,
            'gioi_tinh' => $gioi_tinh,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'nganh' => $nganh,
            'lop' => $lop,
            'khoa' => $khoa,
            'nien_khoa' => $nien_khoa,
            'dia_chi' => $dia_chi,
            'trang_thai' => 'Đang học'
        ];

        try {
            $this->studentModel->addStudent($data);
            setFlash('success', 'Thêm sinh viên thành công!');
            $this->redirect('/admin/sinh-vien');
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/sinh-vien/add');
        }
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $student = $this->studentModel->getStudentById($id);

        if (!$student) {
            setFlash('danger', 'Sinh viên không tồn tại');
            $this->redirect('/admin/sinh-vien');
        }

        $this->view('admin/student/edit', [
            'student' => $student,
            'page_title' => 'Sửa Sinh viên',
            'active_menu' => 'sinh_vien'
        ]);
    }

    public function processEdit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sinh-vien');
        }

        $id = (int)($_POST['id'] ?? 0);
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
        $gioi_tinh = trim($_POST['gioi_tinh'] ?? 'Nam');
        $email = trim($_POST['email'] ?? '');
        $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
        $nganh = trim($_POST['nganh'] ?? '');
        $lop = trim($_POST['lop'] ?? '');
        $khoa = trim($_POST['khoa'] ?? '');
        $nien_khoa = trim($_POST['nien_khoa'] ?? '');
        $dia_chi = trim($_POST['dia_chi'] ?? '');
        $trang_thai = trim($_POST['trang_thai'] ?? 'Đang học');

        if (empty($ho_ten) || empty($nganh) || empty($lop) || empty($trang_thai)) {
            setFlash('danger', 'Vui lòng điền các trường bắt buộc');
            $this->redirect("/admin/sinh-vien/edit?id=$id");
        }

        $data = [
            'ho_ten' => $ho_ten,
            'ngay_sinh' => !empty($ngay_sinh) ? $ngay_sinh : null,
            'gioi_tinh' => $gioi_tinh,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'nganh' => $nganh,
            'lop' => $lop,
            'khoa' => $khoa,
            'nien_khoa' => $nien_khoa,
            'dia_chi' => $dia_chi,
            'trang_thai' => $trang_thai
        ];

        try {
            $this->studentModel->updateStudent($id, $data);
            setFlash('success', 'Cập nhật sinh viên thành công!');
            $this->redirect('/admin/sinh-vien');
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect("/admin/sinh-vien/edit?id=$id");
        }
    }

    public function delete() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            if ($this->studentModel->deleteStudent($id)) {
                setFlash('success', 'Xóa sinh viên thành công!');
            } else {
                setFlash('danger', 'Sinh viên không tồn tại!');
            }
        }
        $this->redirect('/admin/sinh-vien');
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sinh-vien');
        }

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Vui lòng chọn tệp Excel hợp lệ.');
            $this->redirect('/admin/sinh-vien');
        }

        $file = $_FILES['excel_file']['tmp_name'];
        
        try {
            $rows = $this->parseXlsx($file);
            if ($rows === false || empty($rows)) {
                setFlash('danger', 'Không thể đọc hoặc tệp Excel không chứa dữ liệu.');
                $this->redirect('/admin/sinh-vien');
                return;
            }

            // Kiểm tra cấu trúc cột của dòng tiêu đề
            $headerRow = $rows[0];
            if (count($headerRow) < 11) {
                setFlash('danger', 'Tệp Excel không đủ số lượng cột yêu cầu (cần có ít nhất 11 cột từ cột A đến cột K).');
                $this->redirect('/admin/sinh-vien');
                return;
            }

            $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
            $expectedNames = [
                0 => 'MSSV (Mã sinh viên)',
                1 => 'Họ và tên',
                2 => 'Ngày sinh',
                3 => 'Giới tính',
                4 => 'Email',
                5 => 'Số điện thoại (SDT)',
                6 => 'Ngành học',
                7 => 'Lớp học',
                8 => 'Khoa',
                9 => 'Niên khóa',
                10 => 'Địa chỉ'
            ];
            $expectedKeywords = [
                0 => ['mssv', 'mã sv', 'mã sinh viên', 'mã số sinh viên'],
                1 => ['họ tên', 'họ và tên', 'họ & tên', 'tên'],
                2 => ['ngày sinh', 'sinh nhật', 'ngaysinh'],
                3 => ['giới tính', 'gioitinh', 'nam/nữ', 'giới'],
                4 => ['email', 'thư điện tử', 'hop thu'],
                5 => ['sdt', 'sđt', 'số điện thoại', 'điện thoại', 'so dien thoai'],
                6 => ['ngành', 'ngành học', 'chuyên ngành', 'nganh'],
                7 => ['lớp', 'lớp học', 'mã lớp', 'lop'],
                8 => ['khoa', 'khoa quản lý'],
                9 => ['niên khóa', 'nien khoa', 'khóa học', 'khóa', 'nien_khoa'],
                10 => ['địa chỉ', 'dia chi', 'thường trú', 'nơi ở']
            ];

            for ($i = 0; $i < 11; $i++) {
                $cellVal = mb_strtolower(trim($headerRow[$i] ?? ''));
                $matched = false;
                foreach ($expectedKeywords[$i] as $kw) {
                    if (strpos($cellVal, $kw) !== false) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $actualName = isset($headerRow[$i]) && trim($headerRow[$i]) !== '' ? trim($headerRow[$i]) : 'Trống';
                    setFlash('danger', "Tệp Excel sai định dạng cột hoặc sai thứ tự cột. Cột {$colLetters[$i]} phải là \"{$expectedNames[$i]}\", nhưng hiện tại đang là \"{$actualName}\".");
                    $this->redirect('/admin/sinh-vien');
                    return;
                }
            }
        } catch (\Exception $e) {
            setFlash('danger', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/sinh-vien');
            return;
        }

        // Bỏ dòng tiêu đề đầu tiên sau khi đã xác thực hợp lệ
        array_shift($rows);
        
        $db = \App\Core\Database::getInstance();
        $pdo = $db->getConnection();
        
        $successCount = 0;
        $failCount = 0;

        $defaultPassword = 'Student@123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

        $pdo->beginTransaction();
        try {
            foreach ($rows as $row) {
                if (count($row) < 2) {
                    $failCount++;
                    continue;
                }

                $ma_sv = trim($row[0] ?? '');
                $ho_ten = trim($row[1] ?? '');
                $ngay_sinh = trim($row[2] ?? '');
                $gioi_tinh = trim($row[3] ?? 'Nam');
                $email = trim($row[4] ?? '');
                $so_dien_thoai = trim($row[5] ?? '');
                $nganh = trim($row[6] ?? '');
                $lop = trim($row[7] ?? '');
                $khoa = trim($row[8] ?? '');
                $nien_khoa = trim($row[9] ?? NAM_HOC_HIEN_TAI);
                $dia_chi = trim($row[10] ?? '');

                if (empty($ma_sv) || empty($ho_ten)) {
                    $failCount++;
                    continue;
                }

                // Kiểm tra xem MSSV đã tồn tại chưa
                $check = $this->studentModel->getStudentByMaSv($ma_sv);
                if ($check) {
                    $failCount++;
                    continue;
                }

                if (empty($email)) {
                    $email = strtolower($ma_sv) . '@student.qnu.edu.vn';
                }

                // 1. Tạo tài khoản trong bảng users
                $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'student', ?)");
                $stmtUser->execute([$ma_sv, $hashedPassword, $email]);
                $user_id = $pdo->lastInsertId();

                // 2. Tạo bản ghi sinh viên
                $ngay_sinh_db = !empty($ngay_sinh) ? $ngay_sinh : null;
                $stmtSv = $pdo->prepare("INSERT INTO sinh_vien (user_id, ma_sv, ho_ten, ngay_sinh, gioi_tinh, email, so_dien_thoai, nganh, lop, khoa, nien_khoa, dia_chi, trang_thai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Đang học')");
                $stmtSv->execute([$user_id, $ma_sv, $ho_ten, $ngay_sinh_db, $gioi_tinh, $email, $so_dien_thoai, $nganh, $lop, $khoa, $nien_khoa, $dia_chi]);
                
                $successCount++;
            }
            $pdo->commit();
            setFlash('success', "Nhập thành công $successCount sinh viên từ file Excel (Bỏ qua/Thất bại: $failCount).");
        } catch (\Exception $e) {
            $pdo->rollBack();
            setFlash('danger', 'Lỗi khi nhập danh sách: ' . $e->getMessage());
        }

        $this->redirect('/admin/sinh-vien');
    }

    private function parseXlsx($filePath) {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('PHP Extension ZipArchive chưa được bật.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return false;
        }

        // 1. Đọc shared strings
        $sharedStrings = [];
        $sharedStringsData = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsData) {
            $xml = simplexml_load_string($sharedStringsData);
            if ($xml) {
                foreach ($xml->si as $val) {
                    $sharedStrings[] = (string)($val->t ?? $val->r->t ?? '');
                }
            }
        }

        // 2. Đọc sheet1.xml
        $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetData) {
            $zip->close();
            return false;
        }

        $xml = simplexml_load_string($sheetData);
        if (!$xml) {
            $zip->close();
            return false;
        }

        $rows = [];
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

        $zip->close();
        return $rows;
    }
}
