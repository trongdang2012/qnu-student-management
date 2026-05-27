<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $name = defined('DB_NAME') ? DB_NAME : 'qnu_sms';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die('<div style="padding:20px;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:5px;font-family:Roboto,sans-serif;"><strong>Lỗi kết nối CSDL (PDO):</strong> ' . htmlspecialchars($e->getMessage()) . '</div>');
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Thực thi câu lệnh SQL với tham số an toàn (Chống SQL Injection)
     */
    public function query(string $sql, array $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Lấy 1 bản ghi
     */
    public function fetch(string $sql, array $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Lấy nhiều bản ghi
     */
    public function fetchAll(string $sql, array $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Lấy ID của bản ghi vừa chèn
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
