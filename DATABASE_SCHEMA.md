# DATABASE_SCHEMA

Database: `print_system` — MariaDB 10.6, ENGINE InnoDB, CHARSET utf8mb4 / utf8mb4_unicode_ci

## ER Diagram
```
faculties (faculty_id) 1───* majors (major_id, faculty_id)
                                    │
                                    * 
                              students (student_id, major_id)
                                    │ 1
            ┌───────────────────────┼───────────────────────┐
            *                       *                        *
       activities            point_transactions          print_logs
       (student_id)            (student_id)               (student_id)

printers   (เครื่องพิมพ์ — อ้างอิงด้วย printer_id ใน activities แบบหลวม ไม่มี FK)
admins     (อิสระ — ผู้ดูแลระบบ)
settings   (อิสระ — key/value ค่าระบบ)

Views: printer_stats, print_dashboard, stats_summary
```

## Tables

### faculties — คณะ
| Column | Type | Note |
|--------|------|------|
| faculty_id (PK) | char(2) | รหัสคณะจริง เช่น '01' |
| faculty_name | varchar(255) | |
| created_at | timestamp | default now |

### majors — สาขา
| Column | Type | Note |
|--------|------|------|
| major_id (PK) | char(2) | รหัสสาขาจริง |
| faculty_id (FK→faculties) | char(2) | ON DELETE CASCADE |
| major_abbr | varchar(10) | ตัวย่อ เช่น IT, ME |
| major_name | varchar(255) | |
| created_at | timestamp | |

### students — นักศึกษา
| Column | Type | Note |
|--------|------|------|
| student_id (PK) | varchar(20) | รหัสนักศึกษา = ใช้เป็นรหัสผ่าน portal |
| student_name | varchar(255) | |
| major_id (FK→majors) | char(2) | ON DELETE SET NULL |
| points_balance | int | ยอดพอยต์คงเหลือ (ติดลบได้ = หนี้) |
| total_prints | int | จำนวนงานพิมพ์สะสม |
| total_pages | int | จำนวนหน้าสะสม |
| total_points_spent | int | พอยต์ใช้ไปสะสม |
| last_active | datetime | |
| status | enum('active','low','debt','inactive') | debt = ยอดติดลบ |
| created_at / updated_at | timestamp | |

### activities — บันทึกกิจกรรมหลัก (audit log)
| Column | Type | Note |
|--------|------|------|
| activity_id (PK, AI) | int | |
| student_id (FK→students) | varchar(20) | ON DELETE CASCADE |
| activity_type | enum('print','add_points','withdraw_points','refund_money') | |
| points | int | +/- พอยต์ของกิจกรรม |
| points_before / points_after | int | ยอดก่อน/หลัง |
| money_amount | decimal(10,2) | จำนวนเงิน (เติม/คืน) |
| printer_id | int | อ้างอิงเครื่องพิมพ์ (ไม่มี FK) |
| activity_detail | varchar(255) | |
| pages | int | |
| created_at | timestamp | |

### point_transactions — ธุรกรรมพอยต์ (สรุปย่อ)
| Column | Type | Note |
|--------|------|------|
| transaction_id (PK, AI) | int | |
| student_id (FK→students) | varchar(20) | ON DELETE CASCADE |
| points | int | |
| transaction_type | enum('add','deduct','refund') default 'add' | |
| created_at | timestamp | |

### print_logs — บันทึกการพิมพ์
| Column | Type | Note |
|--------|------|------|
| log_id (PK, AI) | int | |
| student_id (FK→students) | varchar(20) | ON DELETE CASCADE |
| pages | int default 1 | |
| points_used | int default 0 | |
| print_time | timestamp | |

### printers — เครื่องพิมพ์
| Column | Type | Note |
|--------|------|------|
| printer_id (PK, AI) | int | |
| printer_name | varchar(100) | |
| location | varchar(255) | |
| paper_current | int default 500 | กระดาษคงเหลือ |
| paper_max | int default 500 | |
| status | enum('ready','low_paper','offline') default 'ready' | |
| created_at / updated_at | timestamp | |

### admins — ผู้ดูแลระบบ
| Column | Type | Note |
|--------|------|------|
| admin_id (PK, AI) | int | |
| username (UNIQUE) | varchar(50) | |
| password | varchar(255) | bcrypt hash (`password_hash`) |
| full_name | varchar(255) | |
| email (UNIQUE) | varchar(255) | |
| role | enum('super_admin','admin') default 'admin' | |
| created_at / updated_at | timestamp | |

### settings — ค่าระบบ (key/value)
| Column | Type | Note |
|--------|------|------|
| key (PK) | varchar(50) | |
| value | varchar(255) | |
| description | varchar(255) | |
| updated_at | timestamp | |

คีย์ที่ใช้: `points_per_page`, `exchange_rate_baht`, `exchange_rate_points`, `cashier_pin`, `allow_debt`, `debt_limit`

## Views
- **printer_stats** — นับเครื่องพิมพ์ตามสถานะ (total/ready/low_paper/offline)
- **print_dashboard** — มุมมองสรุปต่อนักศึกษา (name, balance, prints, pages, spent)
- **stats_summary** — total_students, total_points, avg_points, active_today (distinct student ที่มี activity วันนี้)

## Indexes
- PK ทุกตาราง; `admins.username`, `admins.email` UNIQUE
- KEY บน FK: `activities.student_id`, `majors.faculty_id`, `point_transactions.student_id`, `print_logs.student_id`, `students.major_id`

## Constraints (Foreign Keys)
- `majors.faculty_id` → `faculties.faculty_id` (CASCADE)
- `students.major_id` → `majors.major_id` (SET NULL)
- `activities.student_id` → `students.student_id` (CASCADE)
- `point_transactions.student_id` → `students.student_id` (CASCADE)
- `print_logs.student_id` → `students.student_id` (CASCADE)

> หมายเหตุ: `activities.printer_id` ไม่มี FK ผูกกับ `printers` — เป็น soft reference
