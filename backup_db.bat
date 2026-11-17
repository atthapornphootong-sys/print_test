@echo off
REM ===== สำรองฐานข้อมูลอัตโนมัติ (ทุก 7 วัน 06:00 ผ่าน Task Scheduler) =====
REM ปรับ path php.exe และโปรเจกต์ให้ตรงเครื่องของคุณหากต่างจากนี้
"C:\xampp\php\php.exe" "C:\xampp\htdocs\print_test\database\auto_backup.php"
