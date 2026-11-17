# AI_CONTEXT — Entry Point

> อ่านไฟล์นี้ไฟล์เดียวก่อนเริ่มงาน เพื่อลด token usage
> **Reading Priority:** 1) AI_CONTEXT.md → 2) docs/ai-context/CURRENT_STATE.md → 3) docs/ai-context/PROJECT_OVERVIEW.md → 4) docs/ai-context/CHANGELOG_AI.md

## Project Summary
**ระบบจัดการพอยต์พิมพ์** (Print Point Management System) — คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยธนบุรี

## System Overview (≈300 คำ)
ระบบบริหารจัดการ "พอยต์การพิมพ์" สำหรับนักศึกษา แทนการชำระเงินสดหน้าเครื่องพิมพ์ นักศึกษาเติมพอยต์ที่เคาน์เตอร์แคชเชียร์ตามอัตราแลกเปลี่ยนที่ตั้งไว้ แล้วใช้พอยต์หักลดเมื่อสั่งพิมพ์ โดย 1 หน้า = `points_per_page` พอยต์ (ค่าเริ่มต้น 2) โปรแกรม Python ที่ติดตั้งกับเครื่องพิมพ์จะเรียก API เพื่อตรวจสอบยอดพอยต์ก่อนอนุญาตให้พิมพ์ และบันทึกผลหลังพิมพ์เสร็จ

สถาปัตยกรรมเป็นเว็บ PHP server-rendered โดยมี **API เดี่ยวแบบ action-based** ที่ `config/controller.php` ทุกคำขอส่ง field `action` เลือกฟังก์ชัน และคืน JSON ฐานข้อมูลเป็น MariaDB ชื่อ `print_system` เข้าถึงผ่าน PDO prepared statements ทั้งหมด มีตารางหลัก: `students`, `activities` (audit log หลัก), `point_transactions`, `print_logs`, `printers`, `admins`, `settings`, `faculties`, `majors` พร้อม views สรุป (`stats_summary`, `printer_stats`, `print_dashboard`)

ผู้ใช้แบ่งเป็น 3 บทบาท: `super_admin` และ `admin` (จัดการระบบผ่าน `web-app/admin/`) และ `student` (ดูยอด/ประวัติของตนผ่าน `web-app/student/portal.php`) หน้า admin ทุกหน้าใช้ template ส่วนกลาง `static/header.php` / `footer.php` / `sidebar.php` และสไตล์รวมที่ `static/css/style.css`

ค่าทางธุรกิจ (พอยต์ต่อหน้า, อัตราแลกเปลี่ยน, PIN แคชเชียร์, วงเงินติดหนี้, สวิตช์อนุญาตหนี้) เก็บในตาราง `settings` แบบ key/value ปรับได้จากหน้า "ตั้งค่าระบบ" ธุรกรรมการเงินทุกครั้งต้องยืนยันด้วย `cashier_pin` ระบบรองรับการ "ติดหนี้" ได้เมื่อ admin เปิด `allow_debt` และอยู่ในวง `debt_limit`

UI รองรับ responsive: เมนู hamburger แบบ off-canvas บนมือถือ และ layout ปรับตามขนาดจอ

## Important Files
- `config/controller.php` — API หลัก (action router + business logic ทั้งหมด)
- `config/database.php` — PDO + helper (`fetchSingle`, `countRows`, `sanitizeInput`)
- `config/db_config.php` — ค่าคงที่ DB (มี credentials — ควรย้ายออก)
- `config/auth.php` — `checkLogin()` / `checkRole()` / session
- `login.php` — ล็อกอิน admin + student (แท็บ)
- `static/header.php` · `footer.php` · `sidebar.php` — template ส่วนกลาง admin
- `static/css/style.css` — สไตล์รวม + responsive ทั้งระบบ
- `web-app/admin/{dashboard,manage_point,users,user_detail,report,settings}.php`
- `web-app/student/portal.php` — หน้านักศึกษา
- `py/printer_control.py` · `print_queue_manager.py` — โปรแกรมควบคุมเครื่องพิมพ์
- `database/print_system.sql` — โครงสร้าง + ข้อมูลตัวอย่าง

## Development Rules (สรุป — รายละเอียดใน docs/ai-context/CODING_GUIDELINES.md)
- ใช้ PDO prepared statements + `sanitizeInput()` เสมอ; ห้ามต่อ SQL จาก input
- ฟังก์ชัน PHP = camelCase, ตัวแปร = snake_case, API action = snake_case, CSS = kebab-case
- หน้า admin ต้อง include `config/auth.php` และใช้ header/footer/sidebar ส่วนกลาง
- สไตล์ไว้ใน `style.css` เท่านั้น (เลี่ยง inline ใหม่); ตารางห่อด้วย `.table-container`
- **วิเคราะห์ Impact ก่อนแก้ทุกครั้ง และอัปเดต CHANGELOG_AI.md หลังแก้เสร็จ**

## Current Status
ระบบใช้งานได้ครบฟีเจอร์หลัก เพิ่ง (2026-06-01) เพิ่ม responsive UI (hamburger drawer) และ AI knowledge base

## Recent Changes (5 ล่าสุด)
1. 2026-06-01 เพิ่ม hamburger menu + off-canvas drawer สำหรับมือถือ (`header.php`, `style.css`)
2. 2026-06-01 เพิ่ม overflow guard + responsive breakpoints (720px/560px) + typography `clamp()`
3. 2026-06-01 สร้างชุดเอกสาร `docs/ai-context/` ทั้งหมด
4. 2026-06-01 สร้าง `AI_CONTEXT.md` (ไฟล์นี้)
5. (ก่อนหน้า) ปรับ settings `allow_debt`/`debt_limit`, `points_per_page=2`

## Known Issues (ดูเต็มใน CURRENT_STATE.md)
- Student ใช้ `student_id` เป็นรหัสผ่าน (auth อ่อน)
- API ไม่บังคับ session auth ระดับ action
- DB password hardcoded ใน `config/db_config.php`
- โฟลเดอร์ซ้ำ `print_test/print_test/` และไฟล์สคริปต์ชั่วคราว/`.bak` ค้างใน repo

## Next Tasks
- แปลงตารางเป็น card layout บนมือถือ + ทดสอบ responsive จริงบนอุปกรณ์
- ย้าย DB credentials ออกเป็น env; เพิ่ม auth ระดับ action ใน controller
- ลบโฟลเดอร์ซ้ำและไฟล์ชั่วคราว; พิจารณาเพิ่ม Dark Mode
- แยก `controller.php` เป็น module ตามโดเมน
