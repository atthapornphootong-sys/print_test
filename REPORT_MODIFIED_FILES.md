# รายงานไฟล์ที่ถูกแก้ไข (Modified Files Report)
รอบงาน: 2026-06-01 — Responsive Design + AI Knowledge Base

## ไฟล์ที่แก้ไข (Modified)
| ไฟล์ | การเปลี่ยนแปลง | ความเสี่ยง |
|------|----------------|-----------|
| `static/header.php` | เพิ่มปุ่ม hamburger `.menu-toggle`, `.sidebar-overlay`, wrapper `.header-left`, JS `toggleSidebar()/closeSidebar()` + ปิด drawer เมื่อแตะเมนูบนมือถือ | ต่ำ (additive, ไม่แตะ PHP logic) |
| `static/css/style.css` | `overflow-x:hidden` กัน scroll แนวนอน, `img{max-width:100%}`, สไตล์ hamburger/overlay, แยก breakpoint 960–721px (icon rail) / ≤720px (drawer) / ≤560px (single col), `clamp()` typography, ปรับ wrap ของ header/card/pagination/btn | ต่ำ (เพิ่ม/ปรับ CSS) |

## ไฟล์ที่สร้างใหม่ (Created)
| ไฟล์ | เนื้อหา |
|------|---------|
| `AI_CONTEXT.md` | Entry point สำหรับ AI (summary + reading priority) |
| `docs/ai-context/PROJECT_OVERVIEW.md` | ภาพรวม, stack, architecture, folder, env |
| `docs/ai-context/DATABASE_SCHEMA.md` | ER, ตาราง, คอลัมน์, index, constraint, view |
| `docs/ai-context/API_DOCUMENTATION.md` | endpoint/action, method, params, ตัวอย่าง, auth |
| `docs/ai-context/BUSINESS_RULES.md` | workflow, validation, permission, business logic |
| `docs/ai-context/CODING_GUIDELINES.md` | มาตรฐานโค้ด, naming, folder, security |
| `docs/ai-context/CHANGELOG_AI.md` | บันทึกการแก้ไข |
| `docs/ai-context/CURRENT_STATE.md` | สถานะปัจจุบัน, known issues, technical debt |
| `docs/ai-context/REPORT_MODIFIED_FILES.md` | ไฟล์นี้ |
| `docs/ai-context/REPORT_RESPONSIVE_ISSUES.md` | รายงาน responsive issues |

## ไฟล์ที่ "ไม่" แก้ (โดยตั้งใจ)
- `login.php`, `web-app/student/portal.php` — มี responsive (`@media`) ของตัวเองอยู่แล้ว
- โค้ด backend (`config/*.php`), Python (`py/*`), DB — ไม่แตะ ตามขอบเขต "conservative"
- โฟลเดอร์ซ้ำ `print_test/print_test/` — ปล่อยไว้ตามที่ผู้ใช้เลือก (แนะนำลบภายหลัง)

## วิธี Rollback
แก้เฉพาะ 2 ไฟล์ที่เปลี่ยน — คืนค่าจาก git: `git checkout -- static/header.php static/css/style.css`
ไฟล์เอกสารที่สร้างใหม่ลบได้โดยไม่กระทบระบบ
