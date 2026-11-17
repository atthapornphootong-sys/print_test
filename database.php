<?php
error_reporting(0);
ini_set('display_errors', 0);

// โหลด DB credentials จาก db_config.php

require_once __DIR__ . '/db_config.php';


class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" .  DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }
    
    public function getConnection() { return $this->connection; }
    private function __clone() {}
    public function __wakeup() { throw new Exception("Cannot unserialize singleton"); }
}

function getDB() { return Database::getInstance()->getConnection(); }
function testConnection() { try { getDB()->query("SELECT 1"); return true; } catch (PDOException $e) { return false; } }
function executeQuery($sql, $params = []) { try { $db = getDB(); $stmt = $db->prepare($sql); $stmt->execute($params); $result = $stmt->fetchAll(); $stmt->closeCursor(); return $result; } catch (PDOException $e) { return false; } }
function executeNonQuery($sql, $params = []) { try { $db = getDB(); $stmt = $db->prepare($sql); return $stmt->execute($params); } catch (PDOException $e) { return false; } }
function fetchSingle($sql, $params = []) { try { $db = getDB(); $stmt = $db->prepare($sql); $stmt->execute($params); $result = $stmt->fetch(); $stmt->closeCursor(); return $result; } catch (PDOException $e) { return false; } }
function countRows($table, $where = '', $params = []) { try { $sql = "SELECT COUNT(*) as count FROM {$table}"; if (!empty($where)) $sql .= " WHERE {$where}"; $result = fetchSingle($sql, $params); return $result ? (int)$result['count'] : 0; } catch (Exception $e) { return 0; } }
function sanitizeInput($data) { if (is_array($data)) return array_map('sanitizeInput', $data); $data = trim($data); $data = stripslashes($data); $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); return $data; }
?>