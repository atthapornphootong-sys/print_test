<?php
require_once __DIR__ . '/database.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM students WHERE points_balance != 0 OR total_prints > 0");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('students_check.txt', print_r($res, true));
} catch (Exception $e) {
    file_put_contents('students_check.txt', $e->getMessage());
}
