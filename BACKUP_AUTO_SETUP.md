# ตั้งค่าสำรองฐานข้อมูลอัตโนมัติ (ทุก 7 วัน เวลา 06:00)

ระบบสำรองอัตโนมัติทำงานผ่าน **Windows Task Scheduler** ของเครื่องเซิร์ฟเวอร์ (XAMPP)
โดยเรียกสคริปต์ `database/auto_backup.php` ผ่านไฟล์ `backup_db.bat`

ไฟล์สำรองเก็บใน `print_test/database/` ชื่อ `backup_YYYY-MM-DD_His.sql`
(เก็บล่าสุด 12 ไฟล์ ที่เก่ากว่านั้นลบอัตโนมัติ) และมี log ที่ `database/backup.log`

---

## วิธีที่ 1 — คำสั่งเดียว (เร็วสุด)
เปิด **Command Prompt แบบ Run as administrator** แล้ววาง:

```cmd
schtasks /create /tn "PrintSystemBackup" /tr "C:\xampp\htdocs\print_test\backup_db.bat" /sc DAILY /mo 7 /st 06:00 /ru SYSTEM
```

- `/sc DAILY /mo 7` = ทุก ๆ 7 วัน
- `/st 06:00` = เวลา 06:00 น.

ตรวจสอบ/ลบภายหลัง:
```cmd
schtasks /query /tn "PrintSystemBackup"
schtasks /delete /tn "PrintSystemBackup" /f
```

---

## วิธีที่ 2 — ผ่านหน้าจอ Task Scheduler
1. เปิด **Task Scheduler** -> Create Basic Task
2. Name: `PrintSystemBackup`
3. Trigger: **Daily** -> Recur every **7** days -> Start time **06:00:00**
4. Action: **Start a program** -> Program/script:
   `C:\xampp\htdocs\print_test\backup_db.bat`
5. Finish -> (แนะนำติ๊ก "Run whether user is logged on or not")

---

## ทดสอบทันที (ไม่ต้องรอ)
```cmd
C:\xampp\php\php.exe C:\xampp\htdocs\print_test\database\auto_backup.php
```
ถ้าสำเร็จจะเห็น `OK: สร้าง backup_....sql` และมีไฟล์ใหม่ในโฟลเดอร์ database

> หมายเหตุ: ปรับ path `C:\xampp\php\php.exe` และตำแหน่งโปรเจกต์ในไฟล์ `backup_db.bat`
> หากติดตั้ง XAMPP/โปรเจกต์ไว้คนละที่
