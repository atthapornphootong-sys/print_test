<?php
// ============================================
// ไฟล์ช่วย Reset รหัสผ่าน Admin
// เปิด: http://localhost/print_test/reset_admin.php
// ลบทิ้งหลังใช้งานแล้ว!
// ============================================
require_once 'config/database.php';

$new_password = 'admin1234';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$db = getDB();
$stmt = $db->prepare("UPDATE admins SET password = :p WHERE username = 'admin'");
$stmt->execute([':p' => $hash]);

echo "<h2 style='font-family:sans-serif;color:green;padding:20px;'>✅ Reset สำเร็จ!</h2>";
echo "<p style='font-family:sans-serif;padding:0 20px;'>Username: <strong>admin</strong><br>Password: <strong>admin1234</strong></p>";
echo "<p style='font-family:sans-serif;padding:0 20px;color:red;margin-top:10px;'>⚠️ ลบไฟล์นี้ทิ้งหลังใช้งานแล้ว!</p>";
echo "<p style='font-family:sans-serif;padding:20px;'><a href='login.php'>→ ไปหน้า Login</a></p>";
?>
