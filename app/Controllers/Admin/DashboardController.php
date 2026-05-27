<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminModel;

class DashboardController extends Controller {
    public function index() {
        // Yêu cầu quyền admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/auth/login');
        }

        $adminModel = new AdminModel();
        $stats = $adminModel->getDashboardStats();

        $this->view('admin/dashboard', [
            'stats' => $stats,
            'page_title' => 'Dashboard Admin',
            'active_menu' => 'dashboard'
        ]);
    }
}
