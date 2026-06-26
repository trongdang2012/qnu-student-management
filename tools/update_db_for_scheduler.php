<?php
/**
 * Database Migration & Seeder Script for Scheduler
 * Chạy CLI: php tools/update_db_for_scheduler.php
 */

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/constants.php';
require_once ROOT . '/config/database.php';

set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Ket noi CSDL thanh cong!\n";

    // 1. Tao bang giang_vien
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS giang_vien (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ma_gv VARCHAR(20) UNIQUE NOT NULL,
            ho_ten VARCHAR(100) NOT NULL,
            khoa_id INT NULL,
            hoc_vi VARCHAR(100) NULL,
            chuyen_nganh VARCHAR(100) NULL,
            email VARCHAR(100) NULL,
            so_dien_thoai VARCHAR(15) NULL,
            FOREIGN KEY (khoa_id) REFERENCES khoa(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo " - Tao/Kiem tra bang giang_vien thanh cong.\n";

    // Kiem tra va them cot neu da ton tai bang cu
    try {
        $pdo->exec("ALTER TABLE giang_vien ADD COLUMN hoc_vi VARCHAR(100) NULL AFTER khoa_id;");
        echo " - Them cot hoc_vi vao bang giang_vien.\n";
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE giang_vien ADD COLUMN chuyen_nganh VARCHAR(100) NULL AFTER hoc_vi;");
        echo " - Them cot chuyen_nganh vao bang giang_vien.\n";
    } catch (PDOException $e) {}

    // 2. Tao bang phong_hoc
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS phong_hoc (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ten_phong VARCHAR(50) UNIQUE NOT NULL,
            loai_phong ENUM('Ly thuyet', 'Thuc hanh') DEFAULT 'Ly thuyet',
            suc_chua INT DEFAULT 40
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo " - Tao/Kiem tra bang phong_hoc thanh cong.\n";

    // 3. Them cac cot lien ket vao lop_hoc_phan va thoi_khoa_bieu
    try {
        $pdo->exec("ALTER TABLE lop_hoc_phan ADD COLUMN giang_vien_id INT NULL;");
        echo " - Them cot giang_vien_id vao lop_hoc_phan.\n";
    } catch (PDOException $e) { /* Da ton tai */ }
    
    try {
        $pdo->exec("ALTER TABLE lop_hoc_phan ADD COLUMN phong_hoc_id INT NULL;");
        echo " - Them cot phong_hoc_id vao lop_hoc_phan.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    try {
        $pdo->exec("ALTER TABLE lop_hoc_phan ADD CONSTRAINT fk_lhp_giang_vien FOREIGN KEY (giang_vien_id) REFERENCES giang_vien(id) ON DELETE SET NULL;");
        echo " - Them khoa ngoai giang_vien_id vao lop_hoc_phan.\n";
    } catch (PDOException $e) { /* Da ton tai */ }
    
    try {
        $pdo->exec("ALTER TABLE lop_hoc_phan ADD CONSTRAINT fk_lhp_phong_hoc FOREIGN KEY (phong_hoc_id) REFERENCES phong_hoc(id) ON DELETE SET NULL;");
        echo " - Them khoa ngoai phong_hoc_id vao lop_hoc_phan.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    try {
        $pdo->exec("ALTER TABLE thoi_khoa_bieu ADD COLUMN giang_vien_id INT NULL;");
        echo " - Them cot giang_vien_id vao thoi_khoa_bieu.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    try {
        $pdo->exec("ALTER TABLE thoi_khoa_bieu ADD COLUMN phong_hoc_id INT NULL;");
        echo " - Them cot phong_hoc_id vao thoi_khoa_bieu.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    try {
        $pdo->exec("ALTER TABLE thoi_khoa_bieu ADD CONSTRAINT fk_tkb_giang_vien FOREIGN KEY (giang_vien_id) REFERENCES giang_vien(id) ON DELETE SET NULL;");
        echo " - Them khoa ngoai giang_vien_id vao thoi_khoa_bieu.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    try {
        $pdo->exec("ALTER TABLE thoi_khoa_bieu ADD CONSTRAINT fk_tkb_phong_hoc FOREIGN KEY (phong_hoc_id) REFERENCES phong_hoc(id) ON DELETE SET NULL;");
        echo " - Them khoa ngoai phong_hoc_id vao thoi_khoa_bieu.\n";
    } catch (PDOException $e) { /* Da ton tai */ }

    // 4. Seeding du lieu mau Giang vien & Phong hoc
    // Lay ID cua mot so khoa mau
    $khoas = $pdo->query("SELECT id, ten_khoa FROM khoa")->fetchAll();
    $khoaMap = [];
    foreach ($khoas as $k) {
        $khoaMap[$k['ten_khoa']] = $k['id'];
    }
    
    // Them cac giang vien mau
    $giangViens = [
        ['Nguyen Van Hung', 'Khoa Cong nghe thong tin', 'TS', 'Khoa hoc may tinh', 'hungnv@qnu.edu.vn', '0912345678'],
        ['Tran Thi Lan', 'Khoa Cong nghe thong tin', 'ThS', 'Cong nghe phan mem', 'lantt@qnu.edu.vn', '0912345679'],
        ['Le Van Minh', 'Khoa Cong nghe thong tin', 'TS', 'He thong thong tin', 'minhlv@qnu.edu.vn', '0912345680'],
        ['Hoang Van E', 'Khoa Cong nghe thong tin', 'ThS', 'Ky thuat mang', 'ehv@qnu.edu.vn', '0912345681'],
        ['Pham Thi Hoa', 'Khoa Cong nghe thong tin', 'ThS', 'Khoa hoc du lieu', 'hoapt@qnu.edu.vn', '0912345682'],
        ['Hoang Quang Trung', 'Khoa Cong nghe thong tin', 'TS', 'An toan thong tin', 'trunghq@qnu.edu.vn', '0912345683'],
        ['Nguyen Thi F', 'Khoa Cong nghe thong tin', 'ThS', 'Phat trien ung dung Mobile', 'fnt@qnu.edu.vn', '0912345684'],
        ['Tran Van G', 'Khoa Cong nghe thong tin', 'TS', 'Cong nghe web', 'gtv@qnu.edu.vn', '0912345685'],
        ['Le Van H', 'Khoa Cong nghe thong tin', 'TS', 'Kien truc he thong', 'hlv@qnu.edu.vn', '0912345686'],
        ['Ly Van I', 'Khoa Cong nghe thong tin', 'TS', 'Tri tue nhan tao', 'ilv@qnu.edu.vn', '0912345687'],
        ['Nguyen Van J', 'Khoa Cong nghe thong tin', 'ThS', 'Khoa hoc may tinh', 'jnv@qnu.edu.vn', '0912345688'],
        ['Do Thi K', 'Khoa Cong nghe thong tin', 'ThS', 'Tieng Anh chuyen nganh', 'kdt@qnu.edu.vn', '0912345689'],
        ['Vu Van L', 'Khoa Cong nghe thong tin', 'ThS', 'Giao duc the chat', 'lvv@qnu.edu.vn', '0912345690'],
        ['Pham Minh Tuan', 'Khoa Toan & Thong ke', 'TS', 'Giai tich toan hoc', 'tuanpm@qnu.edu.vn', '0912345691'],
        ['Dang Thanh Son', 'Khoa Cong nghe thong tin', 'TS', 'Mang va an ninh mang', 'sondt@qnu.edu.vn', '0912345692'],
        ['Le Hoang Nam', 'Khoa Cong nghe thong tin', 'ThS', 'Cong nghe phan mem', 'namlh@qnu.edu.vn', '0912345693'],
        ['Nguyen Thi Mai', 'Khoa Toan & Thong ke', 'ThS', 'Toan ung dung', 'maint@qnu.edu.vn', '0912345694'],
        ['Bui Quoc Bao', 'Khoa Cong nghe thong tin', 'TS', 'Hoc may va khai pha du lieu', 'baobq@qnu.edu.vn', '0912345695'],
        ['Huynh Tan Dat', 'Khoa Cong nghe thong tin', 'ThS', 'He thong thong tin', 'dathx@qnu.edu.vn', '0912345696'],
        ['Phan Anh Tuan', 'Khoa Cong nghe thong tin', 'TS', 'Khoa hoc may tinh', 'tuanpa@qnu.edu.vn', '0912345697'],
        ['Vu Thi Quynh', 'Khoa Cong nghe thong tin', 'TS', 'Cong nghe tri thuc', 'quynhvt@qnu.edu.vn', '0912345698']
    ];

    $stmtInsertGv = $pdo->prepare("
        INSERT INTO giang_vien (ma_gv, ho_ten, khoa_id, hoc_vi, chuyen_nganh, email, so_dien_thoai) 
        VALUES (:ma, :ten, :khoa_id, :hoc_vi, :chuyen_nganh, :email, :sdt)
        ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten), khoa_id = VALUES(khoa_id), hoc_vi = VALUES(hoc_vi), chuyen_nganh = VALUES(chuyen_nganh), email = VALUES(email), so_dien_thoai = VALUES(so_dien_thoai)
    ");
    
    $index = 1001;
    foreach ($giangViens as $gv) {
        $maGv = "GV" . $index++;
        $tenGv = $gv[0];
        $tenKhoa = $gv[1];
        $hocVi = $gv[2];
        $chuyenNganh = $gv[3];
        $email = $gv[4];
        $sdt = $gv[5];
        
        $khoaId = $khoaMap[$tenKhoa] ?? null;
        
        $stmtInsertGv->execute([
            'ma' => $maGv,
            'ten' => $tenGv,
            'khoa_id' => $khoaId,
            'hoc_vi' => $hocVi,
            'chuyen_nganh' => $chuyenNganh,
            'email' => $email,
            'sdt' => $sdt
        ]);
    }
    echo " - Seeded du lieu giang_vien thanh cong.\n";

    // Them cac phong hoc mau
    $phongs = [
        'thuong' => ['A1.101', 'A1.202', 'A1.303', 'A2.102', 'A2.204', 'A3.101', 'A3.203'],
        'maytinh' => ['A4.PM01', 'A4.PM02', 'A5.PM01', 'A5.PM02'],
        'lab' => ['A7.LAB01', 'A7.LAB02', 'A7.LAB03'],
        'lon' => ['A8.101', 'A8.102', 'A8.201'],
        'thechat' => ['Nha thi dau da nang']
    ];

    $stmtInsertPhong = $pdo->prepare("
        INSERT INTO phong_hoc (ten_phong, loai_phong, suc_chua) 
        VALUES (:ten, :loai, :slot)
        ON DUPLICATE KEY UPDATE loai_phong = VALUES(loai_phong), suc_chua = VALUES(suc_chua)
    ");

    foreach ($phongs as $loaiKey => $dsPhong) {
        $loai = 'Ly thuyet';
        $slot = 45;
        if ($loaiKey === 'maytinh' || $loaiKey === 'lab') {
            $loai = 'Thuc hanh';
            $slot = 30;
        } elseif ($loaiKey === 'lon') {
            $slot = 150;
        } elseif ($loaiKey === 'thechat') {
            $slot = 200;
        }
        
        foreach ($dsPhong as $p) {
            $stmtInsertPhong->execute([
                'ten' => $p,
                'loai' => $loai,
                'slot' => $slot
            ]);
        }
    }
    echo " - Seeded du lieu phong_hoc thanh cong.\n";

    // 5. Dong bo hoa du lieu cu (map giang vien text va phong hoc text sang ID)
    // Dong bo lop hoc phan
    $lhp_list = $pdo->query("SELECT id, giang_vien FROM lop_hoc_phan")->fetchAll();
    $stmtUpdateLhp = $pdo->prepare("UPDATE lop_hoc_phan SET giang_vien_id = :gv_id, phong_hoc_id = :p_id WHERE id = :id");
    
    // Dong bo thoi khoa bieu
    $tkb_list = $pdo->query("SELECT id, giang_vien, phong_hoc FROM thoi_khoa_bieu")->fetchAll();
    $stmtUpdateTkb = $pdo->prepare("UPDATE thoi_khoa_bieu SET giang_vien_id = :gv_id, phong_hoc_id = :p_id WHERE id = :id");

    // Lay map
    $dbGvs = $pdo->query("SELECT id, ho_ten, hoc_vi FROM giang_vien")->fetchAll();
    $gvMap = [];
    foreach ($dbGvs as $gv) {
        $prefix = !empty($gv['hoc_vi']) ? $gv['hoc_vi'] . '. ' : '';
        $fullName = $prefix . $gv['ho_ten'];
        
        $gvMap[$fullName] = $gv['id'];
        
        // Tạo thêm map không có dấu tiếng Việt do seeder có thể dùng tiếng Việt có dấu khác hoặc không dấu
        $noSignName = iconv('UTF-8', 'ASCII//TRANSLIT', $fullName);
        $gvMap[$noSignName] = $gv['id'];
    }
    
    // Map phụ cho giang_vien text trong seeder
    $extraGvMap = [
        'TS. Nguyễn Văn Hùng' => 'TS. Nguyen Van Hung',
        'ThS. Trần Thị Lan' => 'ThS. Tran Thi Lan',
        'TS. Lê Văn Minh' => 'TS. Le Van Minh',
        'ThS. Hoàng Văn E' => 'ThS. Hoang Van E',
        'ThS. Phạm Thị Hoa' => 'ThS. Pham Thi Hoa',
        'TS. Hoàng Quang Trung' => 'TS. Hoang Quang Trung',
        'ThS. Nguyễn Thị F' => 'ThS. Nguyen Thi F',
        'TS. Trần Văn G' => 'TS. Tran Van G',
        'TS. Lê Văn H' => 'TS. Le Van H',
        'TS. Lý Văn I' => 'TS. Ly Van I',
        'ThS. Nguyễn Văn J' => 'ThS. Nguyen Van J',
        'Cô Đỗ Thị K' => 'Co Do Thi K',
        'Thầy Vũ Văn L' => 'Thay Vu Van L',
        'TS. Phạm Minh Tuấn' => 'TS. Pham Minh Tuan',
        'TS. Đặng Thanh Sơn' => 'TS. Dang Thanh Son',
        'ThS. Lê Hoàng Nam' => 'ThS. Le Hoang Nam',
        'ThS. Nguyễn Thị Mai' => 'ThS. Nguyen Thi Mai',
        'TS. Bùi Quốc Bảo' => 'TS. Bui Quoc Bao',
        'ThS. Huỳnh Tấn Đạt' => 'ThS. Huynh Tan Dat',
        'TS. Phan Anh Tuấn' => 'TS. Phan Anh Tuan',
        'TS. Vũ Thị Quỳnh' => 'TS. Vu Thi Quynh'
    ];
    
    $dbPhongs = $pdo->query("SELECT id, ten_phong FROM phong_hoc")->fetchAll();
    $phongMap = [];
    foreach ($dbPhongs as $p) {
        $phongMap[$p['ten_phong']] = $p['id'];
    }

    $countLhp = 0;
    foreach ($lhp_list as $lhp) {
        $gvText = $lhp['giang_vien'];
        
        // Map qua tên tiếng Việt gốc
        $mappedGvText = $extraGvMap[$gvText] ?? $gvText;
        $gvId = $gvMap[$mappedGvText] ?? null;
        
        // Neu khong co phong hoc, thu tim tu thoi_khoa_bieu
        $pId = null;
        if ($lhp['id']) {
            $t = $pdo->query("SELECT phong_hoc FROM thoi_khoa_bieu WHERE lop_hoc_phan_id = " . (int)$lhp['id'] . " LIMIT 1")->fetch();
            if ($t) {
                $pId = $phongMap[$t['phong_hoc']] ?? null;
            }
        }

        $stmtUpdateLhp->execute([
            'gv_id' => $gvId,
            'p_id' => $pId,
            'id' => $lhp['id']
        ]);
        $countLhp++;
    }
    echo " - Dong bo hoa thanh cong {$countLhp} lop hoc phan sang dinh dang ID.\n";

    $countTkb = 0;
    foreach ($tkb_list as $tkb) {
        $gvText = $tkb['giang_vien'];
        $pText = $tkb['phong_hoc'];
        
        $mappedGvText = $extraGvMap[$gvText] ?? $gvText;
        $gvId = $gvMap[$mappedGvText] ?? null;
        $pId = $phongMap[$pText] ?? null;
        
        $stmtUpdateTkb->execute([
            'gv_id' => $gvId,
            'p_id' => $pId,
            'id' => $tkb['id']
        ]);
        $countTkb++;
    }
    echo " - Dong bo hoa thanh cong {$countTkb} ban ghi thoi khoa bieu sang dinh dang ID.\n";

    echo "=========================================================\n";
    echo "HOAN THANH MIGRATION & SEEDING CHO SCHEDULER!\n";
    echo "=========================================================\n";

} catch (Exception $e) {
    echo "LOI FATAL: " . $e->getMessage() . "\n";
}
