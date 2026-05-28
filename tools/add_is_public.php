<?php
require __DIR__ . '/../config/constants.php';
require __DIR__ . '/../app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $sql = "ALTER TABLE tai_lieu ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1";
    $db->getConnection()->exec($sql);
    echo "COLUMN_ADDED\n";
} catch (\PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
