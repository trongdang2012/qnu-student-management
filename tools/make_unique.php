<?php
/**
 * Script thiết lập ràng buộc duy nhất (UNIQUE) cho email và sđt trong CSDL
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "1. Chuyen chuoi rong thanh NULL de tranh loi trung lap khi tao UNIQUE...\n";
    $conn->exec("UPDATE sinh_vien SET email = NULL WHERE email = '' OR TRIM(email) = '';");
    $conn->exec("UPDATE sinh_vien SET so_dien_thoai = NULL WHERE so_dien_thoai = '' OR TRIM(so_dien_thoai) = '';");
    $conn->exec("UPDATE users SET email = NULL WHERE email = '' OR TRIM(email) = '';");

    echo "2. Kiem tra xem co trung lap thuc te nao khong...\n";
    // Kiem tra trung email sinh_vien
    $stmt = $conn->query("SELECT email, COUNT(*) as cnt FROM sinh_vien WHERE email IS NOT NULL GROUP BY email HAVING cnt > 1");
    $dupEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($dupEmails)) {
        echo "Phat hien email trung lap trong bang sinh_vien. Dang xu ly...\n";
        foreach ($dupEmails as $dup) {
            $email = $dup['email'];
            $stmtSv = $conn->prepare("SELECT id FROM sinh_vien WHERE email = ? ORDER BY id");
            $stmtSv->execute([$email]);
            $svRows = $stmtSv->fetchAll(PDO::FETCH_ASSOC);
            // Giu nguyen ban ghi dau tien, thay doi cac ban ghi sau
            for ($i = 1; $i < count($svRows); $i++) {
                $newEmail = str_replace('@', "_dup" . $i . "@", $email);
                $stmtUp = $conn->prepare("UPDATE sinh_vien SET email = ? WHERE id = ?");
                $stmtUp->execute([$newEmail, $svRows[$i]['id']]);
                echo " - Cap nhat SV ID " . $svRows[$i]['id'] . " thanh email moi: " . $newEmail . "\n";
            }
        }
    }

    // Kiem tra trung so_dien_thoai sinh_vien
    $stmt = $conn->query("SELECT so_dien_thoai, COUNT(*) as cnt FROM sinh_vien WHERE so_dien_thoai IS NOT NULL GROUP BY so_dien_thoai HAVING cnt > 1");
    $dupPhones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($dupPhones)) {
        echo "Phat hien SDT trung lap trong bang sinh_vien. Dang xu ly...\n";
        foreach ($dupPhones as $dup) {
            $sdt = $dup['so_dien_thoai'];
            $stmtSv = $conn->prepare("SELECT id FROM sinh_vien WHERE so_dien_thoai = ? ORDER BY id");
            $stmtSv->execute([$sdt]);
            $svRows = $stmtSv->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 1; $i < count($svRows); $i++) {
                $newSdt = $sdt . "_" . $i;
                $stmtUp = $conn->prepare("UPDATE sinh_vien SET so_dien_thoai = ? WHERE id = ?");
                $stmtUp->execute([$newSdt, $svRows[$i]['id']]);
                echo " - Cap nhat SV ID " . $svRows[$i]['id'] . " thanh SDT moi: " . $newSdt . "\n";
            }
        }
    }

    // Kiem tra trung email users
    $stmt = $conn->query("SELECT email, COUNT(*) as cnt FROM users WHERE email IS NOT NULL GROUP BY email HAVING cnt > 1");
    $dupUserEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($dupUserEmails)) {
        echo "Phat hien email trung lap trong bang users. Dang xu ly...\n";
        foreach ($dupUserEmails as $dup) {
            $email = $dup['email'];
            $stmtUs = $conn->prepare("SELECT id FROM users WHERE email = ? ORDER BY id");
            $stmtUs->execute([$email]);
            $usRows = $stmtUs->fetchAll(PDO::FETCH_ASSOC);
            for ($i = 1; $i < count($usRows); $i++) {
                $newEmail = str_replace('@', "_dup" . $i . "@", $email);
                $stmtUp = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmtUp->execute([$newEmail, $usRows[$i]['id']]);
                echo " - Cap nhat User ID " . $usRows[$i]['id'] . " thanh email moi: " . $newEmail . "\n";
            }
        }
    }

    echo "3. Tien hanh ALTER TABLE de tao khoa UNIQUE...\n";
    
    // Xoa khoa UNIQUE cu neu co de tranh bi loi duplicate key name
    try { $conn->exec("ALTER TABLE sinh_vien DROP KEY unique_email"); } catch (Exception $e) {}
    try { $conn->exec("ALTER TABLE sinh_vien DROP KEY unique_sdt"); } catch (Exception $e) {}
    try { $conn->exec("ALTER TABLE users DROP KEY unique_users_email"); } catch (Exception $e) {}

    // Tao khoa UNIQUE moi
    $conn->exec("ALTER TABLE sinh_vien ADD CONSTRAINT unique_email UNIQUE (email);");
    $conn->exec("ALTER TABLE sinh_vien ADD CONSTRAINT unique_sdt UNIQUE (so_dien_thoai);");
    $conn->exec("ALTER TABLE users ADD CONSTRAINT unique_users_email UNIQUE (email);");

    echo "=== HOAN THANH THIET LAP UNIQUE CSDL THANH CONG! ===\n";
} catch (Exception $e) {
    echo "LOI THUC HIEN: " . $e->getMessage() . "\n";
}
