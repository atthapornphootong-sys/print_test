<?php
require_once __DIR__ . '/database.php';

try {
    $db = getDB();
    
    // 1. Clear Activities
    $db->exec("TRUNCATE activities");
    echo "1. Cleared all activities.\n";
    
    // 2. Reset Student Points and Stats
    $db->exec("UPDATE students SET points_balance = 0, total_prints = 0, total_pages = 0, total_points_spent = 0");
    echo "2. Reset all student balances and stats to 0.\n";
    
    // 3. Reset Printers to only 'g107'
    $db->exec("DELETE FROM printers");
    $stmt = $db->prepare("INSERT INTO printers (printer_name, location, status, paper_max, paper_current, updated_at) VALUES ('g107', 'Room G107', 'ready', 500, 500, NOW())");
    $stmt->execute();
    echo "3. Reset printers: only 'g107' is available.\n";
    
    echo "\nSystem Reset Successful! You can now start testing from scratch.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
