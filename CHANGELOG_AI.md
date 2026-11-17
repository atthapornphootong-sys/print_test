# CHANGELOG_AI

บันทึกการแก้ไขที่ทำโดย AI ทุกครั้ง (ใหม่สุดอยู่บน)

---

## 2026-06-01 — Responsive Design + AI Knowledge Base
**Modified Files:**
- `static/header.php` — เพิ่มปุ่ม hamburger (`.menu-toggle`), overlay, wrapper `.header-left`, และ JS `toggleSidebar()/closeSidebar()` + ปิด drawer อัตโนมัติเมื่อแตะเมนูบนมือถือ
- `static/css/style.css` — เพิ่ม `overflow-x:hidden` กัน scroll แนวนอน, `img{max-width:100%}`, สไตล์ hamburger/overlay, แยก breakpoint: 960–721px (icon rail), ≤720px (off-canvas drawer + hamburger), ≤560px (single column), typography แบบ `clamp()`, header/card/pagination ปรับ wrap
- `docs/ai-context/*` — สร้างเอกสารชุด AI knowledge base ทั้งหมด
- `AI_CONTEXT.md` — สร้าง entry point สำหรับ AI

**Reason:** ทำให้ระบบรองรับทุกขนาดหน้าจอ (mobile/tablet/laptop/desktop) ตามข้อกำหนด และสร้างฐานความรู้ให้ AI/ผู้พัฒนาทำงานต่อได้โดยใช้ token น้อยลง

**Impact:**
- ทุกหน้า admin ได้เมนู hamburger บนจอ ≤720px โดยอัตโนมัติ (ผ่าน header/sidebar ส่วนกลาง) — ไม่กระทบ desktop
- การเปลี่ยนแปลงเป็น **additive** ล้วน (เพิ่ม CSS/HTML/JS) ไม่แตะ PHP business logic หรือ DB
- หน้า `login.php` และ `student/portal.php` มี responsive ของตัวเองอยู่แล้ว — ไม่แก้
- ความเสี่ยง: ต่ำ

**Rollback Method:**
- คืนค่า `static/header.php` และ `static/css/style.css` จาก version control (git checkout ของไฟล์ทั้งสอง) หรือ
- ลบบล็อก `.menu-toggle`, `.sidebar-overlay`, `@media(max-width:720px)` ออกจาก CSS และลบ wrapper/JS ที่เพิ่มใน header.php
- ลบโฟลเดอร์ `docs/ai-context/` และ `AI_CONTEXT.md` ได้โดยไม่กระทบระบบ

---

> รูปแบบบันทึก: Date / Modified Files / Reason / Impact / Rollback Method

---

## 2026-06-01 (รอบ 2) — Backup + Security + Mobile fixes
**Modified/Created Files:**
- `web-app/admin/backup.php` (ใหม่) — หน้าสำรองฐานข้อมูล: dump ด้วย PHP ล้วน (ไม่พึ่ง mysqldump) เก็บไฟล์ `backup_YYYY-MM-DD_His.sql` ใน `database/`, มีดาวน์โหลด/ลบ, ป้องกันด้วย `checkLogin()`
- `static/sidebar.php` — เพิ่มเมนู "สำรองข้อมูล" + ปุ่มปิด (✕) สำหรับมือถือ
- `static/css/responsive.css` — รวมโหมดมือถือเป็น ≤960px (hamburger+drawer เดียว), overlay ปล่อยคลิกผ่านเมื่อปิด, สไตล์ปุ่มปิด
- `static/header.php` — cache-bust `?v=4`, auto-close drawer ≤960px
- `web-app/admin/report.php` — แก้ `updateSummaryCards()` ให้ null-safe
- `database/.htaccess` (ใหม่) — บล็อกการเข้าถึงไฟล์ใน `database/` ผ่านเว็บโดยตรง (กันดาวน์โหลด .sql)
- `config/.htaccess` (ใหม่) — บล็อกการเปิด .txt/.sql/.bak/.md/.log ตรง ๆ (กันข้อมูลรั่ว)

**Reason:** เพิ่มความสามารถสำรองข้อมูล + อุดช่องโหว่ไฟล์สำรอง/ไฟล์ข้อมูลที่เข้าถึงผ่านเว็บได้ + แก้เมนูมือถือเปิด/ปิดไม่ได้

**Impact:** เพิ่มไฟล์ใหม่เป็นหลัก ความเสี่ยงต่ำ; .htaccess ต้องการ Apache `AllowOverride` (XAMPP เปิดโดยค่าเริ่มต้น)

**Rollback:** ลบ `backup.php`, `database/.htaccess`, `config/.htaccess`; คืน sidebar/header/responsive จาก git

**หมายเหตุ Chart.js:** คำเตือน "Tracking Prevention" จาก cdnjs ไม่อันตราย (กราฟยังทำงาน) — หากต้องการลบ ให้ดาวน์โหลด chart.umd.min.js ไปไว้ `static/js/` แล้วแก้ src ใน dashboard.php/report.php (ดาวน์โหลดในสภาพแวดล้อมนี้ไม่ได้)
