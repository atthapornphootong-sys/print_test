-- ============================================
-- Reset รหัสผ่านแอดมิน เป็น admin1234
-- รัน SQL นี้ใน phpMyAdmin → print_system → SQL
-- ============================================

UPDATE admins 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';

-- รหัสผ่านใหม่: admin1234
-- (hash ด้านบนคือ bcrypt ของ "password" - ถ้าใช้ไม่ได้ให้รัน reset_to_password.php)
