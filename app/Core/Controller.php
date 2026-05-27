<?php
namespace App\Core;

class Controller {
    /**
     * Tải View và truyền dữ liệu
     */
    protected function view($view, $data = []) {
        // Giải nén mảng data thành các biến riêng biệt ($var_name = value)
        extract($data);
        
        $viewFile = ROOT . "/app/Views/{$view}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View does not exist: {$viewFile}");
        }
    }

    /**
     * Trả về JSON cho AJAX
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect nhanh
     */
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
