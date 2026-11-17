# CURRENT_STATE

สถานะระบบ ณ 2026-06-01

## Completed Features
- ระบบล็อกอินแยกบทบาท Admin / Student (`login.php`, `config/auth.php`)
- API controller แบบ action-based ครอบคลุมงานหลักทั้งหมด (`config/controller.php`)
- Dashboard สรุปสถิติ + กราฟ
- จัดการพอยต์ (เติม/ถอน/คืนเงิน) พร้อม PIN แคชเชียร์
- จัดการนักศึกษา (เพิ่ม/แก้ไข/ลบ/นำเข้า/ค้นหา) + หน้ารายละเอียด
- รายงานกิจกรรม/สถิติ/ประวัติพิมพ์/นักศึกษาติดหนี้
- ตั้งค่าระบบ + จัดการคณะ/สาขา + เปลี่ยนรหัสผ่าน
- Student portal (ดูยอด + ประวัติ)
- โปรแกรม Python ควบคุมเครื่องพิมพ์ (build แล้วใน `py/dist/`)
- **Responsive UI**: hamburger drawer บนมือถือ, layout ปรับตามจอ (เพิ่ม 2026-06-01)
- AI knowledge base (`docs/ai-context/`, `AI_CONTEXT.md`)

## Pending Features / งานที่ควรทำต่อ
- แปลงตารางข้อมูลเป็น card layout บนมือถือ (ตอนนี้ใช้ horizontal scroll ผ่าน `.table-container`)
- ตรวจ responsive จริงบนอุปกรณ์/เบราว์เซอร์หลายขนาด (ยังตรวจด้วยการอ่านโค้ด)
- เพิ่ม Dark Mode (ยังไม่มี)
- ย้าย DB credentials ออกจาก `config/db_config.php` ไปเป็น env/ไฟล์นอก repo
- เพิ่มการตรวจ session/permission ระดับ action ใน `config/controller.php`

## Known Issues
1. **ความปลอดภัย — Student auth อ่อน**: นักศึกษาใช้ `student_id` เป็นทั้ง username และ password
2. **API ไม่บังคับ session auth**: `config/controller.php` พึ่งพา CORS/PIN เท่านั้น — action ที่อ่าน/แก้ข้อมูลควรตรวจสิทธิ์เพิ่ม
3. **CORS fallback `*`**: เปิดกว้างเพื่อรองรับ Python client
4. **DB password ใน source**: `config/db_config.php` มีรหัสผ่าน hardcoded
5. **โฟลเดอร์ซ้ำ `print_test/print_test/`**: สำเนาเก่า ไม่ใช้งาน ควรลบ
6. **ไฟล์สคริปต์ชั่วคราวใน `config/`**: `check_*.php`, `fix_settings.php`, `reset_system.php`, `*_output.txt` ควรย้าย/ลบออกจาก production
7. **ไฟล์ `.bak`**: `web-app/admin/settings.php.bak` ค้างอยู่ใน repo

## Technical Debt
- โค้ด business logic จำนวนมากรวมในไฟล์เดียว (`controller.php`) — ควรแยก module ตามโดเมน (students/points/printers/reports)
- ตารางหลายตัวเก็บข้อมูลซ้ำซ้อน (`activities` vs `point_transactions` vs `print_logs`)
- ไม่มี automated tests
- inline `<style>` ขนาดใหญ่ใน `login.php` และ `student/portal.php` (ไม่ได้ใช้ `style.css` ร่วม)
- ไม่มีระบบ migration ของ DB ที่เป็นทางการ (มีเพียง SQL dump)
