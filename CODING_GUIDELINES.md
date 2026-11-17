# CODING_GUIDELINES

แนวทางสำหรับรักษาความสม่ำเสมอของโค้ดในระบบ (สรุปจากโค้ดที่มีอยู่ + แนวทางที่แนะนำ)

## Coding Standard
- **PHP**: สไตล์ procedural + ฟังก์ชันต่อ action ใน `config/controller.php` ทุกฟังก์ชัน 1 action
- ใช้ **PDO prepared statements เสมอ** — ห้ามต่อ string ค่าจาก input เข้า SQL
- ผ่าน input ทุกตัวด้วย `sanitizeInput()` ก่อนใช้งาน
- คืนค่า API เป็น JSON ด้วย `json_encode([...], JSON_UNESCAPED_UNICODE)` รูปแบบ `{success, message|data}`
- จัดการ error ด้วย `throw new Exception('ข้อความภาษาไทย')` ให้ controller จับและคืน 400
- **JS**: vanilla, ใช้ `fetch` + `async/await` หรือ `.then`; helper ส่วนกลางผูกที่ `window.*` (เช่น `animateCount`, `toggleSidebar`)
- เขียนคอมเมนต์ภาษาไทยอธิบาย business logic ได้ (เป็นแนวที่ใช้อยู่)

## Naming Convention
| สิ่งที่ตั้งชื่อ | รูปแบบ | ตัวอย่าง |
|----------------|--------|----------|
| ฟังก์ชัน PHP | camelCase | `getStudentDetail()`, `addPoints()` |
| ตัวแปร PHP | snake_case | `$student_id`, `$points_needed` |
| API action | snake_case | `check_student`, `get_dashboard_stats` |
| ตาราง/คอลัมน์ DB | snake_case | `point_transactions`, `points_balance` |
| CSS class | kebab-case | `.stat-card`, `.menu-toggle` |
| CSS variable | kebab-case + prefix | `--navy`, `--sidebar-w`, `--r-md` |
| JS global helper | camelCase บน window | `window.toggleSidebar` |
| ไฟล์ PHP | snake_case.php | `manage_point.php`, `user_detail.php` |

## Folder Convention
- `config/` — โค้ด backend ที่ใช้ร่วม (auth, controller, database, db_config)
- `static/` — สินทรัพย์ frontend + template ส่วนกลาง (header/footer/sidebar) + `css/` + `img/`
- `web-app/admin/` — หน้า admin (ต้อง include `config/auth.php`)
- `web-app/student/` — หน้านักศึกษา
- `api/` — endpoint เฉพาะกิจที่แยกจาก controller หลัก
- `py/` — โปรแกรมควบคุมเครื่องพิมพ์ (build artifacts อยู่ใน `py/build`, `py/dist`)
- `database/` — SQL dump / migration scripts
- `docs/ai-context/` — เอกสารสำหรับ AI/ผู้พัฒนา
- **ห้ามสร้างสำเนาโฟลเดอร์ทั้งระบบซ้อนกัน** (เช่น `print_test/print_test/` ที่มีอยู่ — ควรลบ)

## Component Convention (frontend)
- ใช้ template ส่วนกลางทุกหน้า admin: `require_once '../../static/header.php'` ต้นไฟล์ และ `footer.php` ท้ายไฟล์
- เมนูจัดการรวมที่ `static/sidebar.php` (ไฮไลต์หน้าปัจจุบันด้วย `basename($_SERVER['PHP_SELF'])`)
- ตั้ง `$page_title` ก่อน include header
- สไตล์ทั้งหมดอยู่ใน `static/css/style.css` — หลีกเลี่ยง inline style ใหม่; ใช้ utility/`:root` variables ที่มี
- ตารางต้องห่อด้วย `.table-container` (มี horizontal scroll อัตโนมัติบนจอเล็ก)
- responsive: ใช้ relative units (rem/%/vw) และ breakpoints ที่มีอยู่ (960px tablet, 720px phone-drawer, 560px)

## Security Rules
- **อย่า** echo ค่า credential หรือ stack trace ออกสู่ผู้ใช้
- รหัสผ่าน admin เก็บเป็น bcrypt (`password_hash` / `password_verify`) เท่านั้น
- ธุรกรรมการเงินต้องตรวจ `cashier_pin`
- ทุกหน้า admin ต้องเรียก `checkLogin()` (ผ่าน auth.php)
- หลีกเลี่ยงการ commit ค่า DB password ลง repo — ย้ายไป env/ไฟล์นอก version control (งานที่ค้าง)
- ตั้ง CORS ให้แคบที่สุดเท่าที่ใช้งานได้ (ปัจจุบัน fallback `*` สำหรับ Python client)
- ตรวจสิทธิ์ระดับ action ใน controller สำหรับงานที่กระทบข้อมูล (ค้าง — ดู CURRENT_STATE.md)
