<?php
//settings.php
require_once '../../config/auth.php';
$page_title = "ตั้งค่าระบบ";
require_once '../../static/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>⚙️ ตั้งค่าระบบ</h2>
    </div>

    <div style="padding: 30px;">
        <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">

            <!-- 1) พอยต์ต่อ 1 หน้า -->
            <div class="card" style="margin:0;">
                <div class="card-header">
                    <h3>💰 ค่าพอยต์การพิมพ์</h3>
                </div>
                <div style="padding: 20px;">
                    <div class="form-group">
                        <label>พอยต์ต่อ 1 หน้า</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="number" id="points_per_page" class="form-control"
                                min="1" max="100" value="1" style="width:120px;">
                            <span>พอยต์</span>
                        </div>
                        <small style="color:#888; margin-top:5px; display:block;">
                            เช่น ตั้งค่า 3 = พิมพ์ 1 หน้า หัก 3 พอยต์
                        </small>
                    </div>
                    <button onclick="saveSetting('points_per_page')" class="btn btn-primary" style="margin-top:15px;">
                        💾 บันทึก
                    </button>
                    <div id="points_per_page_status" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- 2) PIN Code แคชเชียร์ -->
            <div class="card" style="margin:0;">
                <div class="card-header">
                    <h3>🔐 PIN Code แคชเชียร์</h3>
                </div>
                <div style="padding: 20px;">
                    <div class="form-group">
                        <label>PIN สำหรับเข้าหน้าจัดการพอยต์</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="password" id="cashier_pin" class="form-control"
                                maxlength="8" placeholder="ตั้ง PIN 4-8 หลัก" style="width:160px; letter-spacing:4px; font-size:18px;">
                        </div>
                        <small style="color:#888; margin-top:5px; display:block;">
                            ปล่อยว่าง = ไม่ใช้ PIN (เข้าได้เลย) | PIN จะขอทุกครั้งที่เปิด Browser ใหม่
                        </small>
                    </div>
                    <div style="display:flex; gap:8px; margin-top:15px; align-items:center;">
                        <button onclick="savePinSetting()" class="btn btn-primary" style="background:#555;">
                            💾 บันทึก PIN
                        </button>
                        <button onclick="clearPin()" class="btn" style="background:#ddd; color:#333;">
                            🗑️ ล้าง PIN
                        </button>
                    </div>
                    <div id="cashier_pin_status" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- 3) ระบบติดหนี้ -->
            <div class="card" style="margin:0;">
                <div class="card-header">
                    <h3>🧾 ระบบติดหนี้</h3>
                </div>
                <div style="padding: 20px;">
                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" id="allow_debt" style="width:18px; height:18px; cursor:pointer;">
                            <span>เปิดใช้งานระบบติดหนี้</span>
                        </label>
                        <small style="color:#888; margin-top:5px; display:block;">
                            ปิด = ผู้ใช้ต้องมีพอยต์เพียงพอเสมอ ไม่อนุญาตติดลบ
                        </small>
                    </div>

                    <div class="form-group" style="margin-top:15px;">
                        <label>วงเงินติดหนี้สูงสุด</label>
                        <div style="display:flex; gap:10px; align-items:center;">
                            <input type="number" id="debt_limit" class="form-control"
                                min="0" max="9999" value="0" style="width:120px;">
                            <span>พอยต์</span>
                        </div>
                        <small style="color:#888; margin-top:5px; display:block;">
                            0 = ไม่อนุญาตติดหนี้ (แม้จะเปิดสวิตช์ด้านบนก็ตาม)
                        </small>
                    </div>

                    <button onclick="saveDebtSettings()" class="btn btn-primary" style="margin-top:15px;">
                        💾 บันทึกการตั้งค่าหนี้
                    </button>
                    <div id="debt_status" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- 4) อัตราแลกเปลี่ยนพอยต์ -->
            <div class="card" style="margin:0;">
                <div class="card-header">
                    <h3>💱 อัตราแลกเปลี่ยนพอยต์</h3>
                </div>
                <div style="padding: 20px;">
                    <div class="form-group">
                        <label>อัตราแลกเปลี่ยน</label>
                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <input type="number" id="exchange_rate_baht" class="form-control"
                                min="1" max="9999" value="1" style="width:100px;">
                            <span>บาท</span>
                            <span style="font-size:18px; color:#888;">=</span>
                            <input type="number" id="exchange_rate_points" class="form-control"
                                min="1" max="9999" value="1" style="width:100px;">
                            <span>พอยต์</span>
                        </div>
                        <small style="color:#888; margin-top:5px; display:block;">
                            เช่น 10 บาท = 1 พอยต์ → ลูกค้าจ่าย 10 บาท ได้ 1 พอยต์
                        </small>
                    </div>

                    <button onclick="saveExchangeSettings()" class="btn btn-primary" style="margin-top:15px;">
                        💾 บันทึกอัตราแลกเปลี่ยน
                    </button>
                    <div id="exchange_status" style="margin-top:10px;"></div>
                </div>
            </div>

            <!-- 5) เปลี่ยนรหัสผ่านผู้ดูแลระบบ -->
            <div class="card" style="margin:0; grid-column: 1 / -1;">
                <div class="card-header">
                    <h3>🔑 เปลี่ยนรหัสผ่านผู้ดูแลระบบ</h3>
                </div>
                <div style="padding: 20px;">
                    <div id="admin_info_box" style="background:#f7f8fc; border:1px solid #e2e6f0; border-radius:8px; padding:12px 14px; margin-bottom:18px; font-size:14px; color:#333;">
                        <span style="color:#888;">กำลังโหลดข้อมูลบัญชี...</span>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px;">
                        <div class="form-group" style="margin:0;">
                            <label>รหัสผ่านปัจจุบัน</label>
                            <input type="password" id="current_password" class="form-control"
                                placeholder="รหัสผ่านปัจจุบัน" autocomplete="current-password">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>รหัสผ่านใหม่</label>
                            <input type="password" id="new_password" class="form-control"
                                placeholder="อย่างน้อย 6 ตัวอักษร" autocomplete="new-password">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" id="confirm_password" class="form-control"
                                placeholder="ยืนยันรหัสผ่านใหม่" autocomplete="new-password">
                        </div>
                    </div>

                    <small style="color:#888; margin-top:10px; display:block;">
                        ⚠️ หลังเปลี่ยนรหัสผ่าน คุณจะต้องใช้รหัสผ่านใหม่ในการล็อกอินครั้งถัดไป
                    </small>

                    <div style="display:flex; gap:8px; margin-top:15px; align-items:center;">
                        <button onclick="changeAdminPassword()" class="btn btn-primary">
                            🔐 เปลี่ยนรหัสผ่าน
                        </button>
                        <button onclick="clearPasswordFields()" class="btn" style="background:#ddd; color:#333;">
                            ล้างฟอร์ม
                        </button>
                    </div>
                    <div id="admin_password_status" style="margin-top:10px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>
<style>
/* ── Settings cards stagger ── */
.stats-grid .card:nth-child(1) { animation: gFadeUp .45s .08s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .card:nth-child(2) { animation: gFadeUp .45s .18s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .card:nth-child(3) { animation: gFadeUp .45s .28s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .card:nth-child(4) { animation: gFadeUp .45s .38s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .card:nth-child(5) { animation: gFadeUp .45s .48s cubic-bezier(.34,1.4,.64,1) both; }
/* ── Input spin-in ── */
#points_per_page, #cashier_pin, #debt_limit, #exchange_rate_baht, #exchange_rate_points,
#current_password, #new_password, #confirm_password {
  animation: gSlideR .4s .3s ease both;
}
/* ── Save button bounce on click ── */
.btn-primary:active { animation: gBounce .3s ease; }
/* ── Status message pop ── */
#points_per_page_status > *, #cashier_pin_status > *,
#debt_status > *, #exchange_status > *, #admin_password_status > * {
  animation: gPopIn .35s cubic-bezier(.34,1.56,.64,1) both;
}
/* ── Card header fade ── */
.card-header h2, .card-header h3 {
  animation: gSlideR .4s .12s ease both;
}
</style>
<script>
// โหลดค่าปัจจุบันทั้งหมดจาก DB
async function loadSettings() {
    try {
        const res = await fetch('../../config/controller.php?action=get_settings');
        const data = await res.json();
        if (data.success) {
            const s = data.data;

            // points_per_page
            if (s.points_per_page !== undefined) {
                document.getElementById('points_per_page').value = s.points_per_page;
            }

            // cashier_pin (แสดงแค่ placeholder ว่ามีหรือไม่)
            if (s.cashier_pin !== undefined) {
                document.getElementById('cashier_pin').placeholder = s.cashier_pin ? '••••••••' : 'ตั้ง PIN 4-8 หลัก';
            }

            // allow_debt (toggle)
            if (s.allow_debt !== undefined) {
                document.getElementById('allow_debt').checked = (s.allow_debt === '1' || s.allow_debt === 1);
            }

            // debt_limit
            if (s.debt_limit !== undefined) {
                document.getElementById('debt_limit').value = s.debt_limit;
            }

            // exchange_rate_baht / exchange_rate_points
            if (s.exchange_rate_baht !== undefined) {
                document.getElementById('exchange_rate_baht').value = s.exchange_rate_baht;
            }
            if (s.exchange_rate_points !== undefined) {
                document.getElementById('exchange_rate_points').value = s.exchange_rate_points;
            }
        }
    } catch(e) { console.error(e); }
}

// บันทึก setting ทั่วไป (key เดียว)
async function saveSetting(key) {
    const value = document.getElementById(key).value;
    const statusEl = document.getElementById(key + '_status');

    try {
        const res = await fetch('../../config/controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_setting', key: key, value: value })
        });
        const data = await res.json();

        if (data.success) {
            statusEl.innerHTML = '<span style="color:#14764e;">✅ บันทึกสำเร็จ</span>';
        } else {
            statusEl.innerHTML = '<span style="color:#c53030;">❌ ' + data.message + '</span>';
        }
    } catch(e) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ เกิดข้อผิดพลาด</span>';
    }

    setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
}

// helper: บันทึกหลาย key พร้อมกัน
async function saveMultiple(pairs, statusEl, successMsg) {
    try {
        const results = await Promise.all(pairs.map(p =>
            fetch('../../config/controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_setting', key: p.key, value: p.value })
            }).then(r => r.json())
        ));

        const failed = results.find(r => !r.success);
        if (failed) {
            statusEl.innerHTML = '<span style="color:#c53030;">❌ ' + (failed.message || 'บันทึกไม่สำเร็จ') + '</span>';
        } else {
            statusEl.innerHTML = '<span style="color:#14764e;">✅ ' + successMsg + '</span>';
        }
    } catch(e) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ เกิดข้อผิดพลาด</span>';
    }
    setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
}

// บันทึก allow_debt + debt_limit พร้อมกัน
async function saveDebtSettings() {
    const allowDebt = document.getElementById('allow_debt').checked ? '1' : '0';
    const debtLimit = document.getElementById('debt_limit').value;
    const statusEl = document.getElementById('debt_status');

    if (parseInt(debtLimit) < 0) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ วงเงินต้องไม่ติดลบ</span>';
        setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
        return;
    }

    await saveMultiple([
        { key: 'allow_debt', value: allowDebt },
        { key: 'debt_limit', value: debtLimit }
    ], statusEl, 'บันทึกการตั้งค่าหนี้สำเร็จ');
}

// บันทึก exchange_rate_baht + exchange_rate_points พร้อมกัน
async function saveExchangeSettings() {
    const baht   = document.getElementById('exchange_rate_baht').value;
    const points = document.getElementById('exchange_rate_points').value;
    const statusEl = document.getElementById('exchange_status');

    if (parseInt(baht) < 1 || parseInt(points) < 1) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ ค่าทั้งสองต้องเป็นจำนวนเต็มบวก</span>';
        setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
        return;
    }

    await saveMultiple([
        { key: 'exchange_rate_baht',   value: baht   },
        { key: 'exchange_rate_points', value: points }
    ], statusEl, 'บันทึกอัตราแลกเปลี่ยนสำเร็จ');
}

// บันทึก PIN
async function savePinSetting() {
    const pin = document.getElementById('cashier_pin').value;
    const statusEl = document.getElementById('cashier_pin_status');
    if (pin && (pin.length < 4)) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ PIN ต้องมีอย่างน้อย 4 หลัก</span>';
        setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
        return;
    }
    try {
        const res = await fetch('../../config/controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_setting', key: 'cashier_pin', value: pin })
        });
        const data = await res.json();
        if (data.success) {
            statusEl.innerHTML = pin
                ? '<span style="color:#14764e;">✅ ตั้ง PIN สำเร็จ</span>'
                : '<span style="color:#14764e;">✅ ล้าง PIN สำเร็จ (ไม่ใช้ PIN)</span>';
            // Reset session ให้ขอ PIN ใหม่ถ้ามีการเปลี่ยน
            sessionStorage.removeItem('cashier_pin_ok');
        } else {
            statusEl.innerHTML = '<span style="color:#c53030;">❌ ' + data.message + '</span>';
        }
    } catch(e) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ เกิดข้อผิดพลาด</span>';
    }
    setTimeout(() => { statusEl.innerHTML = ''; }, 3000);
}

function clearPin() {
    document.getElementById('cashier_pin').value = '';
    savePinSetting();
}

// ───── Admin Password Management ─────
async function loadAdminInfo() {
    const box = document.getElementById('admin_info_box');
    try {
        const res = await fetch('../../config/controller.php?action=get_admin_info');
        const data = await res.json();
        if (data.success) {
            const a = data.data;
            const roleBadge = a.role === 'super_admin'
                ? '<span style="background:#002366;color:#fff;padding:2px 10px;border-radius:12px;font-size:11px;margin-left:8px;">SUPER ADMIN</span>'
                : '<span style="background:#14764e;color:#fff;padding:2px 10px;border-radius:12px;font-size:11px;margin-left:8px;">ADMIN</span>';
            box.innerHTML =
                '<div><strong>👤 ' + (a.full_name || '-') + '</strong>' + roleBadge + '</div>' +
                '<div style="margin-top:6px;color:#666;font-size:13px;">' +
                'Username: <code style="background:#fff;padding:2px 6px;border-radius:4px;">' + (a.username || '-') + '</code>' +
                (a.email ? ' &nbsp;|&nbsp; ✉️ ' + a.email : '') +
                '</div>';
        } else {
            box.innerHTML = '<span style="color:#c53030;">❌ ' + (data.message || 'โหลดข้อมูลไม่สำเร็จ') + '</span>';
        }
    } catch(e) {
        box.innerHTML = '<span style="color:#c53030;">❌ เกิดข้อผิดพลาดในการโหลดข้อมูล</span>';
    }
}

function clearPasswordFields() {
    document.getElementById('current_password').value = '';
    document.getElementById('new_password').value = '';
    document.getElementById('confirm_password').value = '';
    document.getElementById('admin_password_status').innerHTML = '';
}

async function changeAdminPassword() {
    const cur = document.getElementById('current_password').value;
    const nw  = document.getElementById('new_password').value;
    const cfm = document.getElementById('confirm_password').value;
    const statusEl = document.getElementById('admin_password_status');

    // Client-side validation
    if (!cur) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ กรุณากรอกรหัสผ่านปัจจุบัน</span>';
        return;
    }
    if (nw.length < 6) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร</span>';
        return;
    }
    if (nw !== cfm) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน</span>';
        return;
    }
    if (nw === cur) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ รหัสผ่านใหม่ต้องไม่ซ้ำกับรหัสผ่านเดิม</span>';
        return;
    }

    statusEl.innerHTML = '<span style="color:#888;">⏳ กำลังเปลี่ยนรหัสผ่าน...</span>';

    try {
        const res = await fetch('../../config/controller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'change_admin_password',
                current_password: cur,
                new_password: nw,
                confirm_password: cfm
            })
        });
        const data = await res.json();
        if (data.success) {
            statusEl.innerHTML = '<span style="color:#14764e;">✅ เปลี่ยนรหัสผ่านสำเร็จ — กรุณาใช้รหัสใหม่ในครั้งถัดไป</span>';
            clearPasswordFields();
            // คงข้อความสถานะไว้ให้เห็นนาน 5 วิ
            setTimeout(() => { statusEl.innerHTML = ''; }, 5000);
            return;
        } else {
            statusEl.innerHTML = '<span style="color:#c53030;">❌ ' + (data.message || 'เปลี่ยนรหัสผ่านไม่สำเร็จ') + '</span>';
        }
    } catch(e) {
        statusEl.innerHTML = '<span style="color:#c53030;">❌ เกิดข้อผิดพลาด</span>';
    }
    setTimeout(() => { statusEl.innerHTML = ''; }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    loadSettings();
    loadAdminInfo();
});
</script>
<?php require_once '../../static/footer.php'; ?>
