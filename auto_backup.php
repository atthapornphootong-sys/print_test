<?php
/**
 * auto_backup.php — สำรองฐานข้อมูลอัตโนมัติ (รันผ่าน CLI เท่านั้น)
 * ใช้กับ Windows Task Scheduler: ทุก 7 วัน เวลา 06:00
 * รันด้วย:  php auto_backup.php
 */

// อนุญาตเฉพาะการรันจาก Command Line เท่านั้น (กันเรียกผ่านเว็บ)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden: CLI only');
}

require_once __DIR__ . '/../config/database.php';

$backup_dir = __DIR__;
$keep       = 12;   // เก็บไฟล์ล่าสุดกี่ไฟล์ (ที่เหลือลบทิ้ง)
$log_file   = $backup_dir . '/backup.log';

function logmsg($f, $m) {
    file_put_contents($f, '[' . date('Y-m-d H:i:s') . "] $m\n", FILE_APPEND);
    echo $m . "\n";
}

try {
    $db = getDB();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $out  = "-- Auto Backup ระบบจัดการพอยต์พิมพ์\n";
    $out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $out .= "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";
    foreach ($tables as $t) {
        $create = $db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_ASSOC);
        $ddl = $create['Create Table'] ?? ($create['Create View'] ?? '');
        $out .= "DROP TABLE IF EXISTS `$t`;\n" . $ddl . ";\n\n";
        $rows = $db->query("SELECT * FROM `$t`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $cols = array_map(fn($c) => "`$c`", array_keys($row));
            $vals = array_map(fn($v) => is_null($v) ? "NULL" : $db->quote($v), array_values($row));
            $out .= "INSERT INTO `$t` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        $out .= "\n";
    }
    $out .= "SET FOREIGN_KEY_CHECKS=1;\n";

    $fname = 'backup_' . date('Y-m-d_His') . '.sql';
    file_put_contents("$backup_dir/$fname", $out);
    logmsg($log_file, "OK: สร้าง $fname (" . round(strlen($out)/1024, 1) . " KB)");

    // หมุนเวียนไฟล์: เก็บล่าสุด $keep ไฟล์
    $all = glob("$backup_dir/backup_*.sql");
    usort($all, fn($a, $b) => filemtime($b) - filemtime($a));
    foreach (array_slice($all, $keep) as $old) {
        unlink($old);
        logmsg($log_file, "ลบไฟล์เก่า: " . basename($old));
    }
    exit(0);
} catch (Exception $e) {
    logmsg($log_file, "ERROR: " . $e->getMessage());
    exit(1);
}
