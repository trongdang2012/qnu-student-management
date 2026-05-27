<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class DataSyncController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }
    }

    public function index() {
        $this->view('admin/data_sync/index', [
            'page_title' => 'Nhập/Xuất Dữ liệu',
            'active_menu' => 'data_sync'
        ]);
    }

    public function export() {
        $filename = "backup_" . DB_NAME . "_" . date("Y-m-d_H-i-s") . ".sql";
        $dumpPath = sys_get_temp_dir() . '/' . $filename;

        $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';

        if (!file_exists($mysqldumpPath)) {
            $mysqldumpPath = 'mysqldump'; 
        }

        $db_host = DB_HOST;
        $db_user = DB_USER;
        $db_pass = DB_PASS;
        $db_name = DB_NAME;

        $command = "\"$mysqldumpPath\" -h $db_host -u $db_user";
        if (!empty($db_pass)) {
            $command .= " -p$db_pass";
        }
        $command .= " $db_name > \"$dumpPath\"";

        exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($dumpPath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($dumpPath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($dumpPath));
            readfile($dumpPath);
            
            unlink($dumpPath);
            exit;
        } else {
            $_SESSION['errors'] = ["Đã xảy ra lỗi khi tạo bản sao lưu. Mã lỗi: $return_var"];
            $this->redirect('/admin/data-sync');
        }
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['sql_file'])) {
            $this->redirect('/admin/data-sync');
        }

        $file = $_FILES['sql_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] = ["Lỗi khi tải file lên."];
            $this->redirect('/admin/data-sync');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $_SESSION['errors'] = ["Chỉ chấp nhận file định dạng .sql"];
            $this->redirect('/admin/data-sync');
        }

        $mysqlPath = 'C:\xampp\mysql\bin\mysql.exe';

        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysql';
        }

        $db_host = DB_HOST;
        $db_user = DB_USER;
        $db_pass = DB_PASS;
        $db_name = DB_NAME;

        $tmpPath = $file['tmp_name'];

        $mysqlPath = str_replace('/', '\\', $mysqlPath);
        if (!file_exists($mysqlPath)) {
            $mysqlPath = 'mysql';
        }

        $command = '"' . $mysqlPath . '"';
        $command .= ' -h "' . $db_host . '"';
        $command .= ' -u "' . $db_user . '"';
        if ($db_pass !== '') {
            $command .= ' --password="' . $db_pass . '"';
        }
        $command .= ' --default-character-set=utf8mb4';
        $command .= ' "' . $db_name . '"';
        $command .= ' < "' . $tmpPath . '"';
        
        $batFile = sys_get_temp_dir() . '/import_' . time() . '.bat';
        file_put_contents($batFile, "@echo off\n" . $command);
        
        exec('call "' . $batFile . '" 2>&1', $output, $return_var);
        @unlink($batFile);
        
        if ($return_var === 0) {
            setFlash('success', 'Phục hồi cơ sở dữ liệu thành công!');
        } else {
            $stdout = implode("\n", $output);
            $message = "Đã xảy ra lỗi khi phục hồi CSDL. Mã lỗi: $return_var";
            if (!empty($stdout)) {
                $message .= " - Thông tin: " . substr($stdout, 0, 500);
            }
            $_SESSION['errors'] = [$message];
        }

        $this->redirect('/admin/data-sync');
    }
}
