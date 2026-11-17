# PROJECT_OVERVIEW

## Project Name
ระบบจัดการพอยต์พิมพ์ (Print Point Management System) — คณะวิทยาศาสตร์และเทคโนโลยี, มหาวิทยาลัยธนบุรี

## System Purpose
ระบบบริหารจัดการ "พอยต์การพิมพ์" สำหรับนักศึกษา ใช้แทนการจ่ายเงินสดหน้าเครื่องพิมพ์ นักศึกษาเติมพอยต์ที่เคาน์เตอร์ (แคชเชียร์) แล้วใช้พอยต์หักลดเมื่อสั่งพิมพ์ผ่านเครื่องพิมพ์ที่ควบคุมด้วยโปรแกรม Python ระบบบันทึกทุกกิจกรรม (เติม/ถอน/คืนเงิน/พิมพ์) เพื่อรายงานและตรวจสอบย้อนหลัง

## Main Features
- **Authentication**: ล็อกอินแยก 2 บทบาท — Admin (username/password แบบ hash) และ Student (รหัสนักศึกษาเป็นทั้ง username และ password)
- **Dashboard**: สรุปสถิติระบบ (นักศึกษา, พอยต์รวม, การพิมพ์วันนี้, สถานะเครื่องพิมพ์) พร้อมกราฟ
- **จัดการพอยต์ (Cashier)**: เติม / ถอน / คืนเงินพอยต์ ป้องกันด้วย PIN แคชเชียร์
- **จัดการนักศึกษา**: เพิ่ม/แก้ไข/ลบ/นำเข้า (import) รายชื่อ ค้นหา และดูรายละเอียดรายบุคคล
- **รายงาน**: รายงานกิจกรรม สถิตินักศึกษา ประวัติการพิมพ์ และนักศึกษาที่ติดหนี้
- **ตั้งค่าระบบ**: อัตราแลกเปลี่ยน, พอยต์ต่อหน้า, วงเงินติดหนี้, PIN แคชเชียร์, จัดการคณะ/สาขา, เปลี่ยนรหัสผ่าน
- **Print control**: โปรแกรม Python (`py/printer_control.py`, `print_queue_manager.py`) สื่อสารกับ API เพื่อตรวจสอบพอยต์ก่อนพิมพ์และบันทึกผลการพิมพ์
- **Student portal**: นักศึกษาดูยอดพอยต์คงเหลือและประวัติการใช้งานของตนเอง

## Technology Stack
- **Backend**: PHP 8.1 (procedural + action-based API controller), PDO
- **Database**: MariaDB 10.6 / MySQL (database ชื่อ `print_system`), utf8mb4
- **Frontend**: PHP server-rendered templates, vanilla JavaScript (fetch/AJAX), CSS เดี่ยว (`static/css/style.css`) ไม่มี framework
- **Fonts/Charts**: Google Fonts (Sarabun, Prompt); กราฟเป็น JS ฝั่ง client
- **Printer client**: Python (PyInstaller build ใน `py/dist/`)
- **Server**: XAMPP (Apache) บน Windows; htdocs

## Architecture Diagram
```
+-------------------+        +-------------------+        +------------------+
|  Browser (Admin)  |        | Browser (Student) |        | Python Printer   |
|  web-app/admin/*  |        | web-app/student/  |        | client (py/)     |
+---------+---------+        +---------+---------+        +---------+--------+
          |  HTML + fetch(JSON)        |  HTML               | HTTP JSON
          v                            v                     v
     +------------------------------------------------------------+
     |             config/controller.php  (JSON API)              |
     |   action-based switch -> functions -> PDO prepared stmts   |
     +-----------------------------+------------------------------+
                                   |
                          config/database.php (PDO)
                                   |
                                   v
                       +-----------------------+
                       |  MariaDB: print_system |
                       +-----------------------+
```
Auth (`config/auth.php`) ครอบหน้า admin ทุกหน้า; `login.php` จัดการเซสชัน.

## Folder Structure
```
print_test/
├── AI_CONTEXT.md                # entry point สำหรับ AI
├── index.php                    # redirect -> login
├── login.php                    # ล็อกอิน admin + student (แท็บ)
├── logout.php / logout_student.php
├── config/
│   ├── auth.php                 # checkLogin / checkRole / getAdminInfo
│   ├── controller.php           # API หลัก (action router)
│   ├── database.php             # PDO + helper (fetchSingle, countRows, sanitizeInput)
│   ├── db_config.php            # ค่าคงที่ DB (credentials)
│   └── (สคริปต์ตรวจสอบ/แก้ข้อมูลชั่วคราว: check_*, fix_settings, reset_system ...)
├── api/get_student_detail.php
├── static/
│   ├── css/style.css            # สไตล์รวมทั้งระบบ (responsive)
│   ├── header.php / footer.php / sidebar.php   # template ส่วนกลาง admin
│   └── img/logo.png
├── web-app/
│   ├── admin/ (dashboard, manage_point, users, user_detail, report, settings, logout)
│   └── student/portal.php
├── py/                          # โปรแกรมควบคุมเครื่องพิมพ์ (Python)
├── database/print_system.sql    # โครงสร้าง + ข้อมูลตัวอย่าง
└── docs/ai-context/             # เอกสารชุดนี้
```
> หมายเหตุ: มีโฟลเดอร์ซ้ำซ้อน `print_test/print_test/` (สำเนาเก่า) — ไม่ใช้งานจริง ควรลบในการ refactor ครั้งถัดไป

## Environment Variables
ระบบนี้ไม่ได้ใช้ไฟล์ `.env` — ค่าตั้งต้นฝังอยู่ใน `config/db_config.php`:

| Constant | ความหมาย | ค่าเริ่มต้น |
|----------|----------|------------|
| `DB_HOST` | โฮสต์ฐานข้อมูล | localhost |
| `DB_PORT` | พอร์ต | 3306 |
| `DB_NAME` | ชื่อ database | print_system |
| `DB_USER` | ผู้ใช้ | root |
| `DB_PASS` | รหัสผ่าน | *(เก็บใน db_config.php — ควรย้ายออกเป็น env)* |
| `DB_CHARSET` | charset | utf8 |

ค่าทางธุรกิจ (พอยต์ต่อหน้า, อัตราแลกเปลี่ยน, PIN, วงเงินหนี้) เก็บใน **ตาราง `settings`** ไม่ใช่ env — ดู BUSINESS_RULES.md
