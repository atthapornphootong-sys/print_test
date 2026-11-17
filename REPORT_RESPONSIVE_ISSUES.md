# รายงานปัญหา Responsive ที่พบ (Responsive Issues Report)
รอบงาน: 2026-06-01 — ตรวจด้วยการวิเคราะห์โค้ด (static review)

## ขนาดหน้าจอเป้าหมาย
Mobile 320–768px · Tablet 768–1024px · Laptop 1024–1440px · Desktop 1440px+

## ปัญหาที่พบ และการแก้ไข
| # | ปัญหา | จุดที่พบ | สถานะ |
|---|-------|---------|--------|
| 1 | ไม่มี hamburger menu บนมือถือ — sidebar ย่อเหลือแถบไอคอน 62px ยังกินพื้นที่และซ่อน label | admin ทุกหน้า | ✅ แก้แล้ว — เพิ่ม off-canvas drawer + ปุ่ม hamburger ที่ ≤720px |
| 2 | เสี่ยง horizontal scroll ของทั้งหน้า | body | ✅ แก้แล้ว — `html,body{max-width:100%;overflow-x:hidden}` |
| 3 | รูปอาจล้นกรอบ | ทั้งระบบ | ✅ แก้แล้ว — `img{max-width:100%;height:auto}` |
| 4 | หัวข้อ `h1` ใหญ่เกินบนจอแคบ | `.header h1` | ✅ แก้แล้ว — `clamp(15px,4.2vw,20px)` |
| 5 | stat cards ไม่ wrap ดีบนจอเล็กสุด | `.stats-grid` | ✅ แก้แล้ว — `auto-fit minmax` + single col ที่ ≤560px |
| 6 | card-header / pagination ล้นบนจอแคบ | `.card-header`, `.pagination` | ✅ แก้แล้ว — `flex-wrap:wrap` ที่ ≤560px |
| 7 | breadcrumb/label admin เบียดบนมือถือ | `.header` | ✅ แก้แล้ว — ซ่อน breadcrumb + `admin-info small` ที่ ≤720px |

## ปัญหาคงเหลือ / ข้อแนะนำ (Pending)
| # | ปัญหา | ข้อเสนอ |
|---|-------|---------|
| A | ตารางข้อมูลกว้าง บนมือถือยังต้องเลื่อนแนวนอน | มี `.table-container{overflow-x:auto}` รองรับแล้ว (ตามข้อกำหนด "horizontal scroll หรือ card") — หากต้องการ ควรทำ card layout ด้วย `data-label` + `::before` ที่ ≤560px |
| B | `login.php` และ `portal.php` ใช้ inline CSS แยก | ทำงานได้ (มี media query ของตัวเอง) แต่ควรรวมเข้าระบบ style เดียวเพื่อ maintainability |
| C | ยังไม่มี Dark Mode | เพิ่ม `prefers-color-scheme` + ชุดตัวแปรสีใน `:root` ภายหลัง |
| D | ยังไม่ได้ทดสอบบนอุปกรณ์/เบราว์เซอร์จริง | ควรเปิด DevTools device toolbar ทดสอบ 320 / 375 / 768 / 1024 / 1440px และ rotate |

## วิธีทดสอบที่แนะนำ
1. เปิดหน้า admin ใด ๆ → ย่อหน้าต่าง < 720px → ปุ่ม ☰ ต้องปรากฏ, แตะแล้ว sidebar เลื่อนเข้า, overlay มืด, แตะ overlay/เมนูเพื่อปิด
2. ตรวจว่าไม่มี scroll แนวนอนของทั้งหน้า ทุกขนาด
3. ตรวจ stat cards, ตาราง, ฟอร์ม, ปุ่ม ไม่ล้นขอบ
4. ทดสอบที่ 320 / 375 / 414 / 768 / 1024 / 1440px
