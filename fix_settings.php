<?php
require_once __DIR__ . '/database.php';
try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE settings SET value = '1' WHERE `key` = 'exchange_rate_baht'");
    $stmt->execute();
    echo "Updated Baht Rate: " . $stmt->rowCount() . "\n";

    $stmt = $db->prepare("UPDATE settings SET value = '1' WHERE `key` = 'exchange_rate_points'");
    $stmt->execute();
    echo "Updated Points Rate: " . $stmt->rowCount() . "\n";
    
    $stmt = $db->prepare("UPDATE settings SET value = '1' WHERE `key` = 'points_per_page'");
    $stmt->execute();
    echo "Updated Points Per Page: " . $stmt->rowCount() . "\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
