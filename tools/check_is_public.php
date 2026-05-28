<?php
require __DIR__ . '/../config/constants.php';
require __DIR__ . '/../app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $sql = "SHOW COLUMNS FROM tai_lieu LIKE 'is_public'";
    $stmt = $db->getConnection()->query($sql);
    $col = $stmt->fetch();
    if ($col) {
        echo "COLUMN_EXISTS\n";
    } else {
        echo "COLUMN_MISSING\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
