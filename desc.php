<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();
$stmt = $db->query("DESCRIBE activities");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $db->query("DESCRIBE settings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
