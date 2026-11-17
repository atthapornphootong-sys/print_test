<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // 1. Add money_amount column if not exists
    $check = $db->query("SHOW COLUMNS FROM activities LIKE 'money_amount'")->fetch();
    if (!$check) {
        $db->exec("ALTER TABLE activities ADD money_amount DECIMAL(10,2) DEFAULT 0.00 AFTER points_after");
        // Retroactively calculate money_amount for old data
        // For old data, we assume 1 point = 1 baht, and we use ABS to keep the amount positive for cost tracking.
        // Wait, for withdraw/refund, it's cash out. For add_points, cash in. Print is service usage.
        // Let's store positive numbers for money_amount and determine the "flow" using activity_type.
        $db->exec("UPDATE activities SET money_amount = ABS(points) WHERE points IS NOT NULL");
        echo "Added money_amount column successfully.\n";
    } else {
        echo "Column money_amount already exists.\n";
    }

    // 2. Add exchange rate settings
    $settings = [
        ['exchange_rate_baht', '1', 'อัตราแลกเปลี่ยน (บาท)'],
        ['exchange_rate_points', '1', 'อัตราแลกเปลี่ยน (พอยต์ที่ได้)']
    ];
    
    $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `description` = VALUES(`description`)");
    foreach ($settings as $s) {
        $stmt->execute($s);
    }
    echo "Settings updated successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
