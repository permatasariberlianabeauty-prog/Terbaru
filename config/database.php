<?php
// ============================================================
// NOXARA - config/database.php
// ============================================================
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        if ($this->conn->connect_error) {
            error_log('DB Connection Error: ' . $this->conn->connect_error);
            die(json_encode(['error' => 'Database connection failed']));
        }
        $this->conn->set_charset('utf8mb4');
        $this->conn->query("SET time_zone = '+07:00'");
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConn(): mysqli {
        return $this->conn;
    }

    public function query(string $sql): mysqli_result|bool {
        $result = $this->conn->query($sql);
        if ($this->conn->error) {
            error_log('DB Query Error: ' . $this->conn->error . ' | SQL: ' . $sql);
        }
        return $result;
    }

    public function prepare(string $sql): mysqli_stmt|false {
        return $this->conn->prepare($sql);
    }

    public function escape(string $value): string {
        return $this->conn->real_escape_string($value);
    }

    public function lastInsertId(): int {
        return $this->conn->insert_id;
    }

    public function affectedRows(): int {
        return $this->conn->affected_rows;
    }
}

function db(): mysqli {
    return Database::getInstance()->getConn();
}

function dbQuery(string $sql): mysqli_result|bool {
    return Database::getInstance()->query($sql);
}

function dbEscape(string $val): string {
    return Database::getInstance()->escape($val);
}

function dbLastId(): int {
    return Database::getInstance()->lastInsertId();
}
