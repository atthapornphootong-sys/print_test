<?php
/**
 * =====================================================
 * สคริปต์ตรวจสอบและแก้ไขปัญหา Admin
 * Admin Diagnostic & Fix Script
 * =====================================================
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='th'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>ตรวจสอบและแก้ไขปัญหา Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        h1 { color: #333; margin-bottom: 30px; }
        h2 { color: #667eea; margin: 30px 0 15px 0; font-size: 20px; }
        .box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
        .success { background: #d4edda; border: 2px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border: 2px solid #ffc107; color: #856404; }
        .info { background: #d1ecf1; border: 2px solid #17a2b8; color: #0c5460; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 ตรวจสอบและแก้ไขปัญหา Admin</h1>";

try {
    $db = getDB();
    
    echo "<h2>1️⃣ ตรวจสอบการเชื่อมต่อฐานข้อมูล</h2>";
    echo "<div class='box success'>✅ เชื่อมต่อฐานข้อมูลสำเร็จ</div>";
    
    echo "<h2>2️⃣ ตรวจสอบตาราง admins</h2>";
    
    // ตรวจสอบว่าตาราง admins มีอยู่หรือไม่
    $check_table = $db->query("SHOW TABLES LIKE 'admins'");
    if ($check_table->rowCount() == 0) {
        echo "<div class='box error'>❌ ไม่พบตาราง 'admins' กรุณารัน SQL Schema ก่อน</div>";
        exit;
    }
    
    echo "<div class='box success'>✅ พบตาราง 'admins'</div>";
    
    echo "<h2>3️⃣ รายการ Admin ในระบบ</h2>";
    
    $stmt = $db->query("SELECT admin_id, username, full_name, email, role, created_at FROM admins");
    $admins = $stmt->fetchAll();
    
    if (count($admins) == 0) {
        echo "<div class='box warning'>⚠️ ไม่มี Admin ในระบบ</div>";
        echo "<div class='box info'>
            <strong>📝 วิธีแก้ไข:</strong><br>
            รันคำสั่ง SQL นี้:<br><br>
            <code>
            INSERT INTO admins (username, password, full_name, email, role) VALUES<br>
            ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin@example.com', 'super_admin');
            </code>
        </div>";
    } else {
        echo "<div class='box success'>✅ พบ Admin " . count($admins) . " คน</div>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>ชื่อเต็ม</th><th>Role</th><th>Email</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['admin_id']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($admin['username']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($admin['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['role']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>4️⃣ ตรวจสอบรหัสผ่าน</h2>";
    
    // ตรวจสอบ password hash
    $stmt = $db->query("SELECT username, password FROM admins WHERE username = 'admin'");
    $admin_check = $stmt->fetch();
    
    if ($admin_check) {
        echo "<div class='box info'>";
        echo "<strong>Username:</strong> " . htmlspecialchars($admin_check['username']) . "<br>";
        echo "<strong>Password Hash:</strong> <code style='font-size: 11px;'>" . htmlspecialchars(substr($admin_check['password'], 0, 50)) . "...</code><br><br>";
        
        // ทดสอบ password_verify
        $test_password = 'admin123';
        $is_valid = password_verify($test_password, $admin_check['password']);
        
        if ($is_valid) {
            echo "✅ <strong style='color: green;'>Password 'admin123' ถูกต้อง!</strong>";
        } else {
            echo "❌ <strong style='color: red;'>Password 'admin123' ไม่ถูกต้อง!</strong>";
        }
        echo "</div>";
        
        if (!$is_valid) {
            echo "<h2>5️⃣ แก้ไขปัญหา - รีเซ็ต Password</h2>";
            
            // สร้าง password hash ใหม่
            $new_password = 'admin123';
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            echo "<div class='box warning'>";
            echo "<strong>🔧 กำลังรีเซ็ต Password...</strong><br><br>";
            
            $update_stmt = $db->prepare("UPDATE admins SET password = :password WHERE username = 'admin'");
            $result = $update_stmt->execute([':password' => $new_hash]);
            
            if ($result) {
                echo "✅ <strong style='color: green;'>รีเซ็ต Password สำเร็จ!</strong><br><br>";
                echo "ข้อมูล Login ใหม่:<br>";
                echo "<code>Username: admin</code><br>";
                echo "<code>Password: admin123</code>";
            } else {
                echo "❌ <strong style='color: red;'>ไม่สามารถรีเซ็ต Password ได้</strong>";
            }
            echo "</div>";
        }
    } else {
        echo "<div class='box error'>❌ ไม่พบ username 'admin' ในระบบ</div>";
        
        echo "<h2>5️⃣ สร้าง Admin ใหม่</h2>";
        
        $new_password = 'admin123';
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $insert_stmt = $db->prepare("
            INSERT INTO admins (username, password, full_name, email, role) 
            VALUES ('admin', :password, 'ผู้ดูแลระบบ', 'admin@example.com', 'super_admin')
        ");
        
        $result = $insert_stmt->execute([':password' => $new_hash]);
        
        if ($result) {
            echo "<div class='box success'>";
            echo "✅ <strong>สร้าง Admin สำเร็จ!</strong><br><br>";
            echo "ข้อมูล Login:<br>";
            echo "<code>Username: admin</code><br>";
            echo "<code>Password: admin123</code>";
            echo "</div>";
        } else {
            echo "<div class='box error'>❌ ไม่สามารถสร้าง Admin ได้</div>";
        }
    }
    
    echo "<h2>6️⃣ สรุป</h2>";
    echo "<div class='box info'>";
    echo "<strong>✅ การตรวจสอบเสร็จสิ้น</strong><br><br>";
    echo "คุณสามารถ Login ได้แล้วด้วย:<br>";
    echo "<code>Username: admin</code><br>";
    echo "<code>Password: admin123</code><br><br>";
    echo "<strong>⚠️ อย่าลืมลบไฟล์ check_admin.php หลังใช้งาน!</strong>";
    echo "</div>";
    
    echo "<a href='login.php' class='btn'>🔐 ไปหน้า Login</a>";
    
} catch (PDOException $e) {
    echo "<div class='box error'>";
    echo "<strong>❌ Database Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</div></body></html>";
?>
