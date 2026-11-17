# API_DOCUMENTATION

## Overview
ระบบใช้ **single-endpoint, action-based API** ที่ `config/controller.php` ทุกคำขอส่ง field `action` เพื่อเลือกฟังก์ชัน คืนค่าเป็น JSON เสมอ (`Content-Type: application/json; charset=utf-8`)

- **Base URL**: `/config/controller.php` (path สัมพัทธ์ตามตำแหน่งหน้า เช่น `../../config/controller.php`)
- **Method**: รับทั้ง `GET` และ `POST` — body เป็น JSON (`php://input`) หรือ form/query params; ถ้า JSON parse ไม่ได้จะ fallback เป็น `array_merge($_GET, $_POST)`
- **CORS**: อนุญาต origin `http://localhost`, `http://127.0.0.1`; คำขอที่ไม่มี Origin (Python client) ได้ `*`
- **Pre-flight**: `OPTIONS` คืน 200
- **Response สำเร็จ**: `{ "success": true, ... }`
- **Response ผิดพลาด**: `{ "success": false, "message": "..." }` (HTTP 400 เมื่อ throw exception)

## Authentication
- หน้า **Admin** ทุกหน้า include `config/auth.php` → `checkLogin()` ตรวจ `$_SESSION['admin_logged_in']`; ถ้าไม่ผ่าน redirect ไป `login.php`
- `checkRole($role)` ตรวจสิทธิ์ (`super_admin` / `admin`)
- **API controller เองไม่ได้บังคับ session auth** — พึ่งพา network/origin และ PIN แคชเชียร์สำหรับ action การเงิน (ดู Known Issues ใน CURRENT_STATE.md)
- การเข้าสู่ระบบ: `login.php` (POST) — admin ใช้ `password_verify`; student ใช้ `student_id` เป็นรหัสผ่าน

## Endpoints (actions)
ทุกรายการเรียกผ่าน `action=<name>` ที่ `config/controller.php`

### Students
| action | Method | Parameters | คำอธิบาย |
|--------|--------|------------|----------|
| `check_student` | POST | student_id, pages | ตรวจพอยต์พอสำหรับพิมพ์ไหม (เช็ค debt limit) |
| `get_student` / `get_student_detail` | GET/POST | student_id | ข้อมูลนักศึกษา |
| `get_all_students` | GET | - | รายชื่อทั้งหมด |
| `search_students` | GET | q / keyword | ค้นหา |
| `add_student` | POST | student_id, student_name, major_id | เพิ่ม |
| `import_students` / `parse_rtf_students` | POST | payload รายชื่อ | นำเข้าหลายรายการ |
| `update_student` | POST | student_id, ฟิลด์ที่แก้ | แก้ไข |
| `delete_student` | POST | student_id | ลบ |
| `get_student_statistics` | GET | student_id | สถิติรายบุคคล |
| `get_debt_students` | GET | - | นักศึกษาติดหนี้ |

### Points / Cashier
| action | Method | Parameters | คำอธิบาย |
|--------|--------|------------|----------|
| `add_points` | POST | student_id, points/amount, pin | เติมพอยต์ |
| `withdraw_points` | POST | student_id, points, pin | ถอนพอยต์ |
| `refund_money` | POST | student_id, amount, pin | คืนเงิน |

### Printing
| action | Method | Parameters | คำอธิบาย |
|--------|--------|------------|----------|
| `record_print` / `print_document` | POST | student_id, pages, printer_id | บันทึก/สั่งพิมพ์ หักพอยต์ |
| `get_print_history` | GET | student_id? | ประวัติการพิมพ์ |

### Printers
| action | Method | Parameters | คำอธิบาย |
|--------|--------|------------|----------|
| `get_printers` | GET | - | รายการเครื่องพิมพ์ |
| `get_printer_status` | GET | - | สถานะ (view printer_stats) |
| `update_printer_status` | POST | printer_id, status | อัปเดตสถานะ |
| `refill_paper` | POST | printer_id | เติมกระดาษ |

### Dashboard / Reports / Charts
| action | Method | คำอธิบาย |
|--------|--------|----------|
| `get_stats` / `get_dashboard_stats` | GET | สถิติสรุประบบ |
| `get_today_activities` | GET | กิจกรรมวันนี้ |
| `get_point_usage_chart` | GET | ข้อมูลกราฟพอยต์ |
| `get_transaction_chart` | GET | ข้อมูลกราฟธุรกรรม |
| `get_activities_report` | GET | รายงานกิจกรรม |
| `update_activity` | POST | แก้ไขรายการกิจกรรม |

### Settings / Admin
| action | Method | คำอธิบาย |
|--------|--------|----------|
| `get_settings` | GET | อ่านค่าระบบทั้งหมด |
| `update_setting` | POST | key, value |
| `change_admin_password` | POST | old, new |
| `get_admin_info` | GET | ข้อมูล admin ปัจจุบัน |

### Faculties / Majors
`get_faculties`, `add_faculty`, `update_faculty`, `delete_faculty`,
`get_majors`, `add_major`, `update_major`, `delete_major`

## Request Example
```bash
POST /config/controller.php
Content-Type: application/json

{ "action": "check_student", "student_id": "6701103077040", "pages": 1 }
```

## Response Example
```json
// success
{ "success": true, "data": { "student_id": "6701103077040",
  "student_name": "...", "points_balance": 2, "points_needed": 2 } }

// error
{ "success": false, "message": "ไม่พบรหัสนักศึกษา: 0000" }
```

## Notes
- ทุก query ใช้ PDO **prepared statements** + `sanitizeInput()` (trim/stripslashes/htmlspecialchars)
- การพิมพ์/หักพอยต์เป็น transaction-style: อ่าน `points_per_page` และ `debt_limit`/`allow_debt` จาก `settings` ก่อนตัดสินใจ
