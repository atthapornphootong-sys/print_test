<?php
require_once __DIR__ . '/database.php';
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 10");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('activities_output.txt', print_r($res, true));
} catch (Exception $e) {
    file_put_contents('activities_output.txt', $e->getMessage());
}
