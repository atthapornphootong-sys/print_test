# BUSINESS_RULES

## Core Concept
นักศึกษามี "พอยต์" ใช้แทนเงินในการพิมพ์ 1 หน้า = `points_per_page` พอยต์ (ค่าเริ่มต้น 2) เติมพอยต์ที่เคาน์เตอร์แคชเชียร์ตามอัตราแลกเปลี่ยน

## Configurable Settings (ตาราง `settings`)
| key | ค่าเริ่มต้น | ความหมาย |
|-----|------------|----------|
| `points_per_page` | 2 | พอยต์ที่หักต่อการพิมพ์ 1 หน้า |
| `exchange_rate_baht` | 1 | จำนวนบาทต่อหนึ่งหน่วยแลก |
| `exchange_rate_points` | 1 | พอยต์ที่ได้ต่อหนึ่งหน่วยแลก |
| `cashier_pin` | 1234 | PIN ยืนยันธุรกรรมการเงิน |
| `allow_debt` | 0 | 1 = อนุญาตให้ยอดติดลบได้ |
| `debt_limit` | 0 | วงเงินติดหนี้สูงสุด (0 = ห้ามติดหนี้) |

## Workflows

### 1. เติมพอยต์ (Add Points)
1. แคชเชียร์สแกน/กรอกรหัสนักศึกษา → ระบบแสดงชื่อ + ยอดคงเหลือ
2. กรอกจำนวน + ยืนยันด้วย `cashier_pin`
3. คำนวณพอยต์จากเงิน × อัตราแลกเปลี่ยน
4. อัปเดต `students.points_balance`, บันทึก `activities` (type=add_points, points_before/after) และ `point_transactions` (type=add)

### 2. ถอนพอยต์ / คืนเงิน (Withdraw / Refund)
- ต้องใส่ PIN; ลด `points_balance`; บันทึก activity (`withdraw_points` / `refund_money`) พร้อม money_amount
- คืนเงินใช้เมื่อยกเลิก/คืนพอยต์ที่เติมผิด

### 3. การพิมพ์ (Print) — ตรวจสอบก่อนตัด
1. Python client เรียก `check_student` พร้อมจำนวนหน้า
2. `points_needed = pages × points_per_page`
3. ถ้า `points_balance >= points_needed` → อนุญาต
4. ถ้าไม่พอ:
   - ถ้า `allow_debt = 1` และยอดติดลบหลังพิมพ์ยังไม่เกิน `debt_limit` → อนุญาต (ติดหนี้)
   - มิฉะนั้น → ปฏิเสธ พร้อมแจ้งจำนวนพอยต์ที่ขาด (shortage)
5. เมื่อพิมพ์จริง → `record_print`: หักพอยต์, อัปเดต total_prints/total_pages/total_points_spent, ลด `printers.paper_current`, บันทึก `activities` (type=print, points ติดลบ) และ `print_logs`

### 4. สถานะนักศึกษา (status auto)
- `active` ปกติ · `low` พอยต์ใกล้หมด · `debt` ยอดติดลบ · `inactive` ไม่ใช้งาน

### 5. สถานะเครื่องพิมพ์
- `ready` · `low_paper` (กระดาษต่ำ) · `offline` — แสดงผลผ่าน view `printer_stats`

## Validation Rules
- `student_id` ห้ามว่าง (ทุก action ที่เกี่ยวกับนักศึกษา) — throw exception ถ้าว่าง
- ธุรกรรมการเงิน (add/withdraw/refund) ต้องมี `cashier_pin` ตรงกับ settings
- จำนวนพอยต์/หน้า cast เป็น int; เงินเป็น decimal(10,2)
- การพิมพ์ต้องผ่านเงื่อนไขพอยต์/วงเงินหนี้ก่อน
- input ทุกตัวผ่าน `sanitizeInput()` (กัน XSS) + PDO prepared (กัน SQL injection)
- ลบนักศึกษา → CASCADE ลบ activities/transactions/print_logs ที่ผูกอยู่

## User Permissions
| บทบาท | สิทธิ์ |
|-------|--------|
| `super_admin` | เข้าถึงทุกหน้า รวมตั้งค่าระบบ/จัดการ admin |
| `admin` | จัดการนักศึกษา/พอยต์/รายงาน (จำกัดบางส่วนผ่าน `checkRole`) |
| `student` | ดูเฉพาะยอดและประวัติของตนเอง (`web-app/student/portal.php`) |

> Student login ใช้ `student_id` เป็นทั้ง username และ password — ดูเป็น Known Issue ด้านความปลอดภัย (CURRENT_STATE.md)

## Business Logic Notes
- ยอดพอยต์ติดลบได้เฉพาะเมื่อ admin เปิด `allow_debt=1` และอยู่ในวง `debt_limit`
- `activities` เป็น single source of truth ของ audit; `point_transactions` และ `print_logs` เป็นบันทึกเสริมเฉพาะทาง
- `stats_summary.active_today` นับนักศึกษาที่มี activity (distinct) ในวันปัจจุบัน
