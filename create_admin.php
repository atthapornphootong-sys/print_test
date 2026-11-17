<?php
/**
 * =====================================================
 * สคริปต์สร้าง Admin ใหม่
 * Create New Admin Script
 * =====================================================
 * ⚠️ ลบไฟล์นี้ทันทีหลังสร้าง Admin เสร็จ!
 */

require_once 'config/database.php';

// กำหนดข้อมูล Admin ใหม่ตรงนี้
$new_admin = [
    'username' => 'admin2',           // เปลี่ยนชื่อผู้ใช้
    'password' => 'password123',      // เปลี่ยนรหัสผ่าน
    'full_name' => 'ผู้ดูแลระบบ 2',  // เปลี่ยนชื่อเต็ม
    'email' => 'admin2@example.com',  // เปลี่ยนอีเมล
    'role' => 'admin'                 // admin หรือ super_admin
];

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้าง Admin ใหม่</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }
        .info-value {
            color: #333;
            flex: 1;
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
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 สร้าง Admin ใหม่</h1>
        <p style="color: #666; margin-bottom: 20px;">Create New Administrator Account</p>

        <div class="warning">
            <strong>⚠️ คำเตือน:</strong> ลบไฟล์นี้ทันทีหลังสร้าง Admin เสร็จ!
        </div>

        <?php
        try {
            $db = getDB();
            
            // ตรวจสอบว่า username ซ้ำหรือไม่
            $check_stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
            $check_stmt->execute([$new_admin['username']]);
            $exists = $check_stmt->fetchColumn();
            
            if ($exists > 0) {
                echo '<div class="error">';
                echo '<strong>❌ ไม่สามารถสร้าง Admin ได้</strong><br>';
                echo 'Username <code>' . htmlspecialchars($new_admin['username']) . '</code> มีอยู่ในระบบแล้ว';
                echo '</div>';
            } else {
                // Hash Password
                $hashed_password = password_hash($new_admin['password'], PASSWORD_DEFAULT);
                
                // Insert Admin
                $stmt = $db->prepare("
                    INSERT INTO admins (username, password, full_name, email, role) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $new_admin['username'],
                    $hashed_password,
                    $new_admin['full_name'],
                    $new_admin['email'],
                    $new_admin['role']
                ]);
                
                if ($result) {
                    echo '<div class="success">';
                    echo '<strong>✅ สร้าง Admin สำเร็จ!</strong><br>';
                    echo 'บัญชีผู้ดูแลระบบใหม่ถูกสร้างเรียบร้อยแล้ว';
                    echo '</div>';
                    
                    echo '<div class="info">';
                    echo '<h3 style="margin-bottom: 15px;">📋 ข้อมูล Admin ที่สร้าง</h3>';
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Username:</div>';
                    echo '<div class="info-value"><code>' . htmlspecialchars($new_admin['username']) . '</code></div>';
                    echo '</div>';
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Password:</div>';
                    echo '<div class="info-value"><code>' . htmlspecialchars($new_admin['password']) . '</code></div>';
                    echo '</div>';
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Full Name:</div>';
                    echo '<div class="info-value">' . htmlspecialchars($new_admin['full_name']) . '</div>';
                    echo '</div>';
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Email:</div>';
                    echo '<div class="info-value">' . htmlspecialchars($new_admin['email']) . '</div>';
                    echo '</div>';
                    echo '<div class="info-row">';
                    echo '<div class="info-label">Role:</div>';
                    echo '<div class="info-value">' . htmlspecialchars($new_admin['role']) . '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="warning" style="margin-top: 20px;">';
                    echo '<strong>🔥 สำคัญมาก:</strong><br>';
                    echo '1. บันทึกข้อมูล Username และ Password ไว้<br>';
                    echo '2. <strong>ลบไฟล์ <code>create_admin.php</code> ทันที</strong><br>';
                    echo '3. ไปที่หน้า Login เพื่อทดสอบบัญชีใหม่';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>❌ เกิดข้อผิดพลาด</strong><br>';
                    echo 'ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้';
                    echo '</div>';
                }
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>❌ Database Error</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>

        <a href="login.php" class="btn">🔑 ไปหน้า Login</a>
    </div>
</body>
</html>
