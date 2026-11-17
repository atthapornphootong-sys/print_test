<?php
require_once '../../config/auth.php'; // ตรวจสอบ Login
$page_title = "จัดการพอยต์ - โหมดแคชเชียร์";
require_once '../../static/header.php';
?>
<!-- PIN Code Overlay -->
<div id="pinOverlay" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,30,87,.95); align-items:center; justify-content:center; flex-direction:column;">
    <div style="background:white; border-radius:20px; padding:40px 36px; width:340px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.4);">
        <div style="font-size:50px; margin-bottom:10px;">🔐</div>
        <h2 style="color:#003087; margin-bottom:6px;">ยืนยัน PIN</h2>
        <p style="color:#888; font-size:14px; margin-bottom:24px;">กรอก PIN เพื่อเข้าโหมดแคชเชียร์</p>
        <input type="password" id="pinInput" maxlength="8" placeholder="• • • • • •"
            style="width:100%; padding:16px; font-size:24px; text-align:center; border:2px solid #e0e0e0; border-radius:12px; outline:none; letter-spacing:8px; margin-bottom:16px;"
            onkeypress="if(event.key===\'Enter\') submitPin()">
        <div id="pinError" style="color:#c53030; font-size:13px; margin-bottom:12px; display:none;">❌ PIN ไม่ถูกต้อง กรุณาลองใหม่</div>
        <button onclick="submitPin()" style="width:100%; padding:14px; background:#003087; color:white; border:none; border-radius:12px; font-size:16px; cursor:pointer; font-weight:bold;">
            ✓ ยืนยัน
        </button>
        <div style="margin-top:12px;">
            <a href="dashboard.php" style="color:#888; font-size:13px; text-decoration:none;">← กลับหน้าหลัก</a>
        </div>
    </div>
</div>
<script>
(function() {
    const unlocked = sessionStorage.getItem("cashier_pin_ok");
    if (!unlocked) {
        const overlay = document.getElementById("pinOverlay");
        overlay.style.display = "flex";
        document.getElementById("pinInput").focus();
    }
})();

async function submitPin() {
    const pin = document.getElementById("pinInput").value.trim();
    if (!pin) return;
    try {
        const res = await fetch("../../config/controller.php?action=get_settings").then(r => r.json());
        const savedPin = res.success ? (res.data.cashier_pin || "") : "";
        if (!savedPin || pin === savedPin) {
            document.getElementById("pinOverlay").style.display = "none";
            sessionStorage.setItem("cashier_pin_ok", "1");
        } else {
            document.getElementById("pinError").style.display = "block";
            document.getElementById("pinInput").value = "";
            document.getElementById("pinInput").style.borderColor = "#c53030";
            document.getElementById("pinInput").focus();
        }
    } catch(e) {
        document.getElementById("pinOverlay").style.display = "none";
        sessionStorage.setItem("cashier_pin_ok", "1");
    }
}
</script>

<div style="padding: 10px 20px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="pulse-dot"></span>
        <strong>ระบบอัพเดทอัตโนมัติ (Real-time)</strong>
    </div>
    <div style="font-size: 14px;">
        เวลาปัจจุบัน: <strong id="lastUpdate">--:--:--</strong>
    </div>
</div>

<div class="cashier-container">
    <div>
        <div class="cashier-scan">
            <h3 style="margin-bottom: 20px; color: #003087;">🔍 สแกนบัตรนักศึกษา</h3>
            <div class="scan-input-group">
                <label for="student_id">รหัสนักศึกษา</label>
                <input type="text" id="student_id" class="scan-input" placeholder="กรอกรหัส หรือ สแกนบัตร" autofocus>
            </div>
            <button onclick="searchStudent()" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">
                🔍 ค้นหา
            </button>
        </div>
        
        <div class="student-info-display" style="margin-top: 20px;">
            <h3>ข้อมูลนักศึกษา</h3>
            <div class="student-name-display" id="studentName">กรุณาสแกนบัตร</div>
            <div style="font-size: 16px; opacity: 0.9;" id="studentIdDisplay">-</div>
            <div class="point-display">
                <div class="point-display-label">พอยต์คงเหลือ</div>
                <div class="point-display-value" id="currentPoints">0</div>
                <div style="font-size: 14px; opacity: 0.9; margin-top: 5px;">
                    ≈ <span id="currentBaht">0.00</span> บาท
                </div>
            </div>
        </div>
    </div>
    
    <div class="cashier-actions">
        <h3 style="margin-bottom: 20px; color: #003087;">💰 ดำเนินการ</h3>
        <div class="action-buttons">
            <button class="action-btn add"    onclick="showAction('add')">➕ เติมพอยต์</button>
            <button class="action-btn refund" onclick="showAction('refund')">💵 ถอนคืนเงิน</button>
        </div>
        <button class="action-btn bulk" onclick="showBulkModal()" style="width:100%;margin-top:12px;">👥 เติมพอยต์หลายคน</button>
        
        <div id="actionForm" style="display:none; margin-top:20px; padding:20px; background:#f8f9fa; border-radius:10px;">
            <h4 id="actionTitle" style="margin-bottom:15px; color:#333;">ดำเนินการ</h4>
            <div class="form-group">
                <label for="points">จำนวนพอยต์</label>
                <input type="number" id="points" class="form-control" placeholder="0" min="1" style="font-size:24px; text-align:center;">
            </div>
            <div class="form-group">
                <label for="reason">เหตุผล/หมายเหตุ</label>
                <textarea id="reason" class="form-control" rows="2" placeholder="ระบุเหตุผล..."></textarea>
            </div>
            <button onclick="confirmTransaction()" class="btn btn-primary" style="width:100%; padding:15px;">✓ ยืนยันรายการ</button>
        </div>
    </div>
</div>

<!-- ตารางประวัติ -->
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2>📋 ประวัติการทำรายการล่าสุด</h2>
        <span id="txCount" style="font-size:13px; color:#999;"></span>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>เวลา</th>
                    <th>รหัสนักศึกษา</th>
                    <th>ชื่อ-สกุล</th>
                    <th>ประเภท</th>
                    <th>พอยต์</th>
                    <th>รายละเอียด</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody id="transactionBody">
                <tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: ยืนยันรายการ (แทน browser confirm) -->
<div id="confirmModal" class="modal">
    <div class="modal-content" style="max-width:420px; text-align:center;">
        <div id="confirmIcon" style="font-size:60px; margin-bottom:10px;">❓</div>
        <h2 id="confirmTitle" style="margin-bottom:5px;">ยืนยันรายการ</h2>
        <div style="background:#f8f9fa; border-radius:10px; padding:15px; margin:15px 0; text-align:left;">
            <table style="width:100%; border:none; font-size:15px;">
                <tr>
                    <td style="color:#666; padding:4px 0;">👤 นักศึกษา</td>
                    <td style="font-weight:bold;" id="cfmName">-</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">🆔 รหัส</td>
                    <td style="font-weight:bold;" id="cfmId">-</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">💰 จำนวน</td>
                    <td style="font-weight:bold; font-size:20px;" id="cfmPoints">-</td>
                </tr>
                <tr id="cfmBahtRow">
                    <td style="color:#666; padding:4px 0;">💵 คิดเป็นเงิน</td>
                    <td style="font-weight:bold; font-size:20px; color:#14764e;" id="cfmBaht">฿0.00</td>
                </tr>
                <tr>
                    <td style="color:#666; padding:4px 0;">📝 หมายเหตุ</td>
                    <td style="font-weight:bold;" id="cfmReason">-</td>
                </tr>
            </table>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="closeConfirmModal()" class="btn" style="flex:1; padding:12px; background:#ddd; color:#333; border-radius:8px;">ยกเลิก</button>
            <button onclick="doTransaction()"    id="cfmBtn" class="btn btn-primary" style="flex:1; padding:12px; border-radius:8px;">✓ ยืนยัน</button>
        </div>
    </div>
</div>

<!-- Modal: เติมพอยต์หลายคน -->
<div id="bulkModal" class="modal">
    <div class="modal-content" style="max-width:760px;">
        <span class="close-modal" onclick="closeBulkModal()">&times;</span>
        <h2 style="margin-bottom:6px;">👥 เติมพอยต์หลายคน</h2>
        <div style="font-size:13px;color:#666;margin-bottom:14px;">สแกนบัตรหรือกรอกรหัสนักศึกษาแล้วกด Enter เพื่อเพิ่มเข้ารายการ — แก้จำนวนพอยต์รายคนได้</div>

        <div style="display:grid;grid-template-columns:1fr 150px 110px;gap:8px;margin-bottom:12px;">
            <input id="bulkScanInput" type="text" class="form-control scan-input" placeholder="🔍 รหัสนักศึกษา + Enter"
                   onkeypress="if(event.key==='Enter')bulkAddStudent()" autocomplete="off">
            <input id="bulkDefaultPts" type="number" class="form-control" min="1" placeholder="พอยต์ต่อคน">
            <button onclick="bulkApplyDefault()" class="btn btn-sm" style="background:#e8ecff;color:#003087;" title="ใช้จำนวนนี้กับทุกคนในรายการ">↧ ใช้ทุกคน</button>
        </div>

        <div style="display:grid;grid-template-columns:110px 1fr 90px 110px 36px;gap:8px;font-size:13px;font-weight:bold;color:#555;padding:0 2px;margin-bottom:6px;">
            <div>รหัส</div><div>ชื่อ-สกุล</div><div>คงเหลือ</div><div>เติม (พอยต์)</div><div></div>
        </div>
        <div id="bulkRows" style="max-height:40vh;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding:2px;">
            <div id="bulkEmpty" style="text-align:center;color:#999;padding:20px;font-size:14px;">ยังไม่มีรายการ — สแกนบัตรเพื่อเพิ่ม</div>
        </div>

        <div id="bulkSummary" style="display:none;background:#f0f7f3;border:1px solid #c3e6cb;border-radius:8px;padding:10px 14px;margin-top:12px;font-size:14px;color:#155724;"></div>
        <div id="bulkResult" style="display:none;border-radius:8px;padding:12px;margin-top:12px;font-size:14px;"></div>

        <div style="display:flex;gap:10px;margin-top:18px;">
            <button onclick="closeBulkModal()" class="btn" style="flex:1;background:#ddd;color:#333;">ปิด</button>
            <button onclick="doBulkTransaction()" id="bulkConfirmBtn" class="btn" style="flex:1;background:#1a8c5a;color:white;">✓ เติมพอยต์ทั้งหมด</button>
        </div>
    </div>
</div>

<!-- Modal: รายละเอียดนักศึกษา -->
<div id="userDetailModal" class="modal">
    <div class="modal-content" style="max-width:600px;">
        <span class="close-modal" onclick="closeUserDetailModal()">&times;</span>
        <div style="text-align:center;">
            <div style="font-size:50px; margin-bottom:10px;">👤</div>
            <h2 id="modalStudentName" style="color:#333; margin-bottom:5px;">-</h2>
            <p id="modalStudentId" style="color:#666; margin-bottom:20px;">รหัส: -</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px; text-align:left;">
                <div style="background:#f0f4ff; padding:15px; border-radius:10px;">
                    <label style="color:#003087; font-weight:bold; font-size:14px;">พอยต์คงเหลือ</label>
                    <div id="modalPoints" style="font-size:24px; font-weight:bold;">0</div>
                </div>
                <div style="background:#fff0f0; padding:15px; border-radius:10px;">
                    <label style="color:#f5576c; font-weight:bold; font-size:14px;">สถานะผู้ใช้</label>
                    <div id="modalStatus" style="font-size:20px; font-weight:bold;">-</div>
                </div>
            </div>
            <div style="text-align:left; background:#eefaf3; padding:15px; border-radius:10px; margin-bottom:15px;">
                <h4 style="margin-bottom:10px; color:#14764e; border-bottom:1px solid #c3e6cb; padding-bottom:5px;">📅 ข้อมูลเวลา</h4>
                <p>วันที่ลงทะเบียน: <strong id="modalCreatedAt">-</strong></p>
                <p>การใช้งานล่าสุด: <strong id="modalLastActive">-</strong></p>
            </div>
            <div style="text-align:left; background:#f8f9fa; padding:15px; border-radius:10px;">
                <h4 style="margin-bottom:10px; border-bottom:1px solid #ddd; padding-bottom:5px;">📊 สถิติการพิมพ์</h4>
                <p>พิมพ์ทั้งหมด: <strong id="modalTotalPrints">0</strong> ครั้ง</p>
                <p>รวมหน้าที่พิมพ์: <strong id="modalTotalPages">0</strong> หน้า</p>
                <p>พอยต์ที่ใช้สะสม: <strong id="modalTotalSpent">0</strong> พอยต์</p>
            </div>
            <button onclick="closeUserDetailModal()" class="btn btn-primary" style="width:100%; padding:12px; margin-top:20px;">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>


<style>
.badge { padding:5px 12px; border-radius:50px; font-size:12px; font-weight:bold; color:white; display:inline-block; }
.badge-success   { background:#14764e; }
.badge-danger    { background:#b43030; }
.badge-warning   { background:#fd7e14; }
.badge-info      { background:#003087; }
.badge-secondary { background:#6c757d; }
.pulse-dot { width:12px; height:12px; background:#fff; border-radius:50%; animation:gPulse 2s infinite; display:inline-block; }
.modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,.5); align-items:center; justify-content:center; }
.modal.show { display:flex !important; }
.modal-content { background:white; padding:30px; border-radius:15px; max-width:450px; width:90%; position:relative; }
.close-modal { position:absolute; top:10px; right:15px; font-size:30px; cursor:pointer; color:#aaa; }

/* ── PIN overlay entrance ── */
#pinOverlay > div {
  animation: gModalIn .45s cubic-bezier(.34,1.4,.64,1) both;
}

/* ── Real-time bar fade ── */
.cashier-container > div:first-child { animation: gFadeUp .5s .06s cubic-bezier(.34,1.3,.64,1) both; }
.cashier-container > div:last-child  { animation: gFadeUp .5s .16s cubic-bezier(.34,1.3,.64,1) both; }

/* ── Scan input glow on focus ── */
.scan-input:focus {
  border-color: #003087 !important;
  box-shadow: 0 0 0 4px rgba(0,48,135,.12) !important;
  transform: scale(1.008);
}

/* ── Student info card entrance ── */
.student-info-display {
  animation: gFadeUp .45s .25s cubic-bezier(.34,1.4,.64,1) both;
}

/* ── Point display value pop on update ── */
@keyframes ptsBounce {
  0%  { transform: scale(.85); opacity:.4; }
  60% { transform: scale(1.12); }
  100%{ transform: scale(1);   opacity:1; }
}
.point-display-value.updated {
  animation: ptsBounce .45s cubic-bezier(.34,1.56,.64,1) both;
}

/* ── Action form slide down ── */
#actionForm {
  animation: gFadeUp .35s ease both;
}

/* ── Transaction table rows ── */
#transactionBody tr {
  animation: gFadeUp .3s ease both;
}
#transactionBody tr:nth-child(1){animation-delay:.25s}
#transactionBody tr:nth-child(2){animation-delay:.30s}
#transactionBody tr:nth-child(3){animation-delay:.35s}
#transactionBody tr:nth-child(4){animation-delay:.40s}
#transactionBody tr:nth-child(5){animation-delay:.45s}
#transactionBody tr:nth-child(n+6){animation-delay:.50s}

/* ── Action buttons spring ── */
.action-btn.add    { animation: gSlideR .4s .3s cubic-bezier(.34,1.56,.64,1) both; }
.action-btn.refund { animation: gSlideL .4s .38s cubic-bezier(.34,1.56,.64,1) both; }
.action-btn.bulk   { background:linear-gradient(135deg,#1a8c5a,#43e97b); color:white; animation: gFadeUp .4s .46s cubic-bezier(.34,1.56,.64,1) both; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>
<script>
let currentAction      = '';
let currentStudentName = '';
let currentStudentId   = '';
let pendingApiAction   = '';
let pendingPts         = 0;
let pendingReason      = '';
let exchangeRateBaht   = 1;
let exchangeRatePoints = 1;

function pointsToBaht(pts) {
    if (exchangeRatePoints <= 0) return pts;
    return (pts * (exchangeRateBaht / exchangeRatePoints)).toFixed(2);
}

// ─── ฟังก์ชันจัดรูปแบบวันที่ ───────────────────────────────────────────────
function formatDateTime(s) {
    if (!s) return 'ไม่ระบุ';
    return new Date(s).toLocaleDateString('th-TH', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit', second:'2-digit'
    });
}

// ─── 1. โหลดประวัติทุกรายการ ───────────────────────────────────────────────
async function loadRecentTransactions() {
    const tbody = document.getElementById('transactionBody');
    try {
        const res = await fetch('../../config/controller.php?action=get_today_activities&limit=50').then(r => r.json());

        if (!res.success || !res.data || res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;padding:24px;">ยังไม่มีรายการ</td></tr>';
            document.getElementById('txCount').textContent = '';
            return;
        }

        document.getElementById('txCount').textContent = `แสดง ${res.data.length} รายการ`;

        tbody.innerHTML = res.data.map(t => {
            let badgeClass, typeText, prefix, amountColor;
            switch (t.activity_type) {
                case 'add_points':
                    badgeClass  = 'badge-success'; typeText = '➕ เติมพอยต์';
                    prefix = '+'; amountColor = '#1a8c5a'; break;
                case 'withdraw_points':
                    badgeClass  = 'badge-danger';  typeText = '➖ ตัดพอยต์';
                    prefix = '-'; amountColor = '#c53030'; break;
                case 'refund_money':
                    badgeClass  = 'badge-info';    typeText = '💵 ถอนคืนเงิน';
                    prefix = '-'; amountColor = '#003087'; break;
                case 'print':
                    badgeClass  = 'badge-warning'; typeText = '🖨️ พิมพ์';
                    prefix = '-'; amountColor = '#fd7e14'; break;
                default:
                    badgeClass  = 'badge-secondary'; typeText = t.activity_type;
                    prefix = ''; amountColor = '#666';
            }
            const pts = Math.abs(t.points);
            return `<tr>
                <td style="white-space:nowrap;">${new Date(t.created_at).toLocaleString('th-TH',{hour:'2-digit',minute:'2-digit',second:'2-digit',day:'2-digit',month:'2-digit'})}</td>
                <td>${t.student_id}</td>
                <td><strong>${t.student_name}</strong></td>
                <td><span class="badge ${badgeClass}">${typeText}</span></td>
                <td style="color:${amountColor}; font-weight:bold; font-size:16px;">${prefix}${pts}</td>
                <td style="color:#555;">${t.activity_detail || '-'}</td>
                <td><button onclick="showUserDetail('${t.student_id}')" class="btn btn-info btn-sm">👁️ ดูข้อมูล</button></td>
            </tr>`;
        }).join('');
        if (window.reAnimateRows) reAnimateRows('transactionBody');

    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:red;padding:20px;">❌ โหลดข้อมูลไม่สำเร็จ</td></tr>';
    }
}

// ─── 2. ค้นหานักศึกษา ────────────────────────────────────────────────────────
async function searchStudent() {
    const id = document.getElementById('student_id').value.trim();
    if (!id) {
        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณากรอกรหัสนักศึกษา', confirmButtonText: 'ตกลง' });
        return;
    }
    try {
        const res = await fetch(`../../config/controller.php?action=get_student_detail&student_id=${encodeURIComponent(id)}`).then(r=>r.json());
        if (res.success) {
            const s = res.data.student;
            currentStudentName = s.student_name;
            currentStudentId   = s.student_id;
            document.getElementById('studentName').textContent      = s.student_name;
            document.getElementById('studentIdDisplay').textContent = 'รหัส: ' + s.student_id;
            const ptEl = document.getElementById('currentPoints');
            ptEl.classList.remove('updated');
            void ptEl.offsetHeight;
            if (window.animateCount) animateCount(ptEl, parseInt(s.points_balance), 600);
            else ptEl.textContent = s.points_balance;
            ptEl.classList.add('updated');

            document.getElementById('currentBaht').textContent = pointsToBaht(parseInt(s.points_balance));

            // แจ้งเตือนถ้าติดหนี้
            const pts = parseInt(s.points_balance);
            const pointDisplay = document.querySelector('.point-display');
            const existingWarn = document.getElementById('debtWarningBox');
            if (existingWarn) existingWarn.remove();
            if (pts < 0) {
                pointDisplay.style.background = 'linear-gradient(135deg,#c53030,#e53e3e)';
                const warn = document.createElement('div');
                warn.id = 'debtWarningBox';
                warn.style.cssText = 'background:#fff5f5;border:2px solid #c53030;border-radius:10px;padding:12px 16px;margin-top:12px;text-align:center;animation:fadeIn .3s;';
                warn.innerHTML = `<strong style="color:#c53030;">🔴 นักศึกษาติดหนี้ ${Math.abs(pts)} พอยต์</strong><br><small style="color:#888;">กรุณาเติมพอยต์เพื่อชำระหนี้</small>`;
                pointDisplay.parentElement.appendChild(warn);
            } else {
                pointDisplay.style.background = '';
            }

            // ✅ ล้างช่องทันที พร้อมค้นหาคนต่อไปได้เลย แต่ currentStudentName ยังคงอยู่
            document.getElementById('student_id').value = '';
            document.getElementById('student_id').focus();
        } else {
            Swal.fire({ icon: 'error', title: 'ไม่พบข้อมูล', text: 'ไม่พบรหัสนักศึกษา: "' + id + '"', confirmButtonText: 'ตกลง' });
            currentStudentName = ''; currentStudentId = '';
            resetDisplay();
            document.getElementById('student_id').value = '';
            document.getElementById('student_id').focus();
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'เชื่อมต่อไม่ได้', text: 'ไม่สามารถเชื่อมต่อได้', confirmButtonText: 'ตกลง' });
    }
}

// ─── 3. แสดง Modal รายละเอียดนักศึกษา ─────────────────────────────────────
async function showUserDetail(studentId) {
    try {
        const res = await fetch(`../../config/controller.php?action=get_student_detail&student_id=${studentId}`).then(r=>r.json());
        if (res.success) {
            const s = res.data.student;
            document.getElementById('modalStudentName').textContent = s.student_name;
            document.getElementById('modalStudentId').textContent   = 'รหัส: ' + s.student_id;
            document.getElementById('modalPoints').textContent      = s.points_balance + ' พอยต์';
            document.getElementById('modalStatus').textContent      = s.status === 'active' ? 'ปกติ' : (s.status === 'low' ? '⚠️ พอยต์ต่ำ' : 'ไม่ได้ใช้งาน');
            document.getElementById('modalCreatedAt').textContent   = formatDateTime(s.created_at);
            document.getElementById('modalLastActive').textContent  = formatDateTime(s.last_active);
            document.getElementById('modalTotalPrints').textContent = s.total_prints;
            document.getElementById('modalTotalPages').textContent  = s.total_pages;
            document.getElementById('modalTotalSpent').textContent  = s.total_points_spent;
            document.getElementById('userDetailModal').classList.add('show');
        }
    } catch (e) { console.error(e); }
}

// ─── 4. เตรียม confirm modal ก่อนทำรายการ ─────────────────────────────────
function confirmTransaction() {
    const id     = currentStudentId;
    const pts    = parseInt(document.getElementById('points').value) || 0;
    const reason = document.getElementById('reason').value.trim() || 'ไม่ระบุ';

    if (!id || !currentStudentName) { Swal.fire({ icon:'warning', title:'แจ้งเตือน', text:'กรุณาค้นหานักศึกษาก่อน', confirmButtonText:'ตกลง' }); return; }
    if (pts <= 0) { Swal.fire({ icon:'warning', title:'แจ้งเตือน', text:'กรุณากรอกจำนวนพอยต์', confirmButtonText:'ตกลง' }); return; }
    if (pts > 100000) { Swal.fire({ icon:'warning', title:'แจ้งเตือน', text:'⚠️ จำนวนพอยต์ต่อรายการต้องไม่เกิน 100,000 พอยต์', confirmButtonText:'ตกลง' }); return; }

    let apiAction, actionLabel, icon, btnColor;
    if (currentAction === 'add') {
        apiAction = 'add_points'; actionLabel = 'เติมพอยต์';
        icon = '➕'; btnColor = '#1a8c5a';
    } else if (currentAction === 'refund') {
        apiAction = 'refund_money'; actionLabel = 'ถอนคืนเงิน';
        icon = '💵'; btnColor = '#003087';
    } else {
        Swal.fire({ icon:'warning', title:'แจ้งเตือน', text:'กรุณาเลือกประเภทรายการ', confirmButtonText:'ตกลง' });
        return;
    }

    // เก็บค่าไว้รอยืนยัน
    pendingApiAction = apiAction;
    pendingPts       = pts;
    pendingReason    = reason;

    // ตั้งค่า confirm modal
    document.getElementById('confirmIcon').textContent         = icon;
    document.getElementById('confirmTitle').textContent        = `ยืนยันการ${actionLabel}`;
    document.getElementById('cfmName').textContent             = currentStudentName;
    document.getElementById('cfmId').textContent               = id;
    document.getElementById('cfmPoints').textContent           = `${pts} พอยต์`;
    document.getElementById('cfmPoints').style.color           = btnColor;
    
    const bahtVal = pointsToBaht(pts);
    document.getElementById('cfmBaht').textContent            = `฿${parseFloat(bahtVal).toLocaleString('en-US', {minimumFractionDigits:2})}`;
    document.getElementById('cfmBaht').style.color            = btnColor;
    document.getElementById('cfmReason').textContent           = reason;
    document.getElementById('cfmBtn').textContent              = `✓ ยืนยัน${actionLabel}`;
    document.getElementById('cfmBtn').style.background         = btnColor;
    document.getElementById('confirmModal').classList.add('show');
}

// ─── 5. ทำรายการจริงหลังยืนยัน ────────────────────────────────────────────
async function doTransaction() {
    const id = currentStudentId;
    closeConfirmModal();

    try {
        const res = await fetch(`../../config/controller.php?action=${pendingApiAction}`, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ student_id: id, points: pendingPts, reason: pendingReason })
        }).then(r => r.json());

        if (res.success) {
            const label = pendingApiAction === 'add_points'      ? 'เติมพอยต์'   :
                          pendingApiAction === 'withdraw_points'  ? 'ตัดพอยต์'    : 'ถอนคืนเงิน';
            const badgeClass  = pendingApiAction === 'add_points'     ? 'badge-success' :
                                pendingApiAction === 'withdraw_points' ? 'badge-danger'  : 'badge-info';
            const typeText    = pendingApiAction === 'add_points'     ? '➕ เติมพอยต์'  :
                                pendingApiAction === 'withdraw_points' ? '➖ ตัดพอยต์'   : '💵 ถอนคืนเงิน';
            const prefix      = pendingApiAction === 'add_points'     ? '+' : '-';
            const amountColor = pendingApiAction === 'add_points'     ? '#1a8c5a' :
                                pendingApiAction === 'withdraw_points' ? '#c53030' : '#003087';
            const nowStr      = new Date().toLocaleString('th-TH', {
                hour:'2-digit', minute:'2-digit', second:'2-digit',
                day:'2-digit', month:'2-digit'
            });

            // ✅ เพิ่มแถวใหม่ขึ้นหัวตาราง
            const tbody   = document.getElementById('transactionBody');
            const newRow  = document.createElement('tr');
            newRow.style.background   = '#fffde7';
            newRow.style.fontWeight   = 'bold';
            newRow.style.animation    = 'fadeIn .4s ease';
            newRow.innerHTML = `
                <td style="white-space:nowrap;">${nowStr}</td>
                <td>${id}</td>
                <td><strong>${currentStudentName}</strong></td>
                <td><span class="badge ${badgeClass}">${typeText}</span></td>
                <td style="color:${amountColor}; font-weight:bold; font-size:16px;">${prefix}${pendingPts}</td>
                <td style="color:#555;">${pendingReason}</td>
                <td><button onclick="showUserDetail('${id}')" class="btn btn-info btn-sm">👁️ ดูข้อมูล</button></td>
            `;
            if (tbody.querySelector('td[colspan]')) tbody.innerHTML = '';
            tbody.insertBefore(newRow, tbody.firstChild);

            const countEl = document.getElementById('txCount');
            const cur = parseInt(countEl.textContent) || 0;
            countEl.textContent = `แสดง ${cur + 1} รายการ`;
            setTimeout(() => { newRow.style.background = ''; newRow.style.fontWeight = ''; }, 3000);
            setTimeout(() => loadRecentTransactions(), 800);

            // ✅ SweetAlert แทน success modal
            Swal.fire({
                icon: 'success',
                title: `${label}สำเร็จ!`,
                html: `<b>${currentStudentName}</b><br>${pendingPts} พอยต์` +
                      (res.new_balance !== undefined ? `<br>พอยต์คงเหลือ: <b>${res.new_balance}</b>` : ''),
                confirmButtonText: 'ตกลง',
                timer: 2500,
                timerProgressBar: true
            });

            // ✅ reset ทุกอย่างหลังทำรายการสำเร็จ พร้อมค้นหาคนใหม่
            currentStudentName = '';
            currentStudentId   = '';
            document.getElementById('student_id').value = '';
            document.getElementById('student_id').focus();
            resetDisplay();
            document.getElementById('actionForm').style.display = 'none';
            document.getElementById('reason').value = '';
            document.getElementById('points').value = '';

        } else {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message, confirmButtonText: 'ตกลง' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'เชื่อมต่อไม่ได้', text: 'ไม่สามารถเชื่อมต่อได้', confirmButtonText: 'ตกลง' });
    }
}

// ─── 6. เติมพอยต์หลายคนพร้อมกัน ────────────────────────────────────────────
let bulkList = [];   // { student_id, student_name, balance, points }

function showBulkModal() {
    bulkList = [];
    document.getElementById('bulkScanInput').value  = '';
    document.getElementById('bulkResult').style.display = 'none';
    document.getElementById('bulkConfirmBtn').disabled  = false;
    bulkRender();
    document.getElementById('bulkModal').classList.add('show');
    setTimeout(() => document.getElementById('bulkScanInput').focus(), 100);
}
function closeBulkModal() { document.getElementById('bulkModal').classList.remove('show'); }

async function bulkAddStudent() {
    const input = document.getElementById('bulkScanInput');
    const id = input.value.trim();
    if (!id) return;
    if (bulkList.some(x => x.student_id === id)) {
        Swal.fire({ icon:'warning', title:'ซ้ำ', text:`รหัส ${id} อยู่ในรายการแล้ว`, timer:1500, showConfirmButton:false });
        input.value = ''; input.focus();
        return;
    }
    try {
        const res = await fetch(`../../config/controller.php?action=get_student_detail&student_id=${encodeURIComponent(id)}`).then(r=>r.json());
        if (!res.success) {
            Swal.fire({ icon:'error', title:'ไม่พบข้อมูล', text:'ไม่พบรหัสนักศึกษา: "' + id + '"', confirmButtonText:'ตกลง' });
            input.value = ''; input.focus();
            return;
        }
        const s = res.data.student;
        const def = parseInt(document.getElementById('bulkDefaultPts').value) || 0;
        bulkList.push({ student_id: s.student_id, student_name: s.student_name, balance: parseInt(s.points_balance), points: def });
        bulkRender();
        input.value = ''; input.focus();
    } catch(e) {
        Swal.fire({ icon:'error', title:'เชื่อมต่อไม่ได้', text:'ไม่สามารถเชื่อมต่อได้', confirmButtonText:'ตกลง' });
    }
}

function bulkRemove(i) { bulkList.splice(i, 1); bulkRender(); }

function bulkApplyDefault() {
    const def = parseInt(document.getElementById('bulkDefaultPts').value) || 0;
    if (def <= 0) return;
    bulkList.forEach(x => x.points = def);
    bulkRender();
}

function bulkRender() {
    const wrap = document.getElementById('bulkRows');
    if (!bulkList.length) {
        wrap.innerHTML = '<div id="bulkEmpty" style="text-align:center;color:#999;padding:20px;font-size:14px;">ยังไม่มีรายการ — สแกนบัตรเพื่อเพิ่ม</div>';
        document.getElementById('bulkSummary').style.display = 'none';
        return;
    }
    wrap.innerHTML = bulkList.map((x, i) => `
        <div style="display:grid;grid-template-columns:110px 1fr 90px 110px 36px;gap:8px;align-items:center;background:#f8f9fa;border-radius:8px;padding:6px 8px;">
            <div style="font-weight:bold;">${x.student_id}</div>
            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${x.student_name}</div>
            <div style="color:${x.balance < 0 ? '#c53030' : '#003087'};font-weight:bold;">${x.balance}</div>
            <input type="number" min="1" value="${x.points || ''}" placeholder="0"
                   oninput="bulkList[${i}].points = parseInt(this.value)||0; bulkSummaryUpdate();"
                   class="form-control" style="padding:6px 8px;text-align:center;">
            <button onclick="bulkRemove(${i})" title="ลบ" style="border:none;background:#fde8e8;color:#b43030;border-radius:6px;height:32px;cursor:pointer;">✕</button>
        </div>`).join('');
    bulkSummaryUpdate();
}

function bulkSummaryUpdate() {
    const el = document.getElementById('bulkSummary');
    if (!bulkList.length) { el.style.display = 'none'; return; }
    const total = bulkList.reduce((s, x) => s + (x.points || 0), 0);
    el.style.display = '';
    el.innerHTML = `รวม <strong>${bulkList.length} คน</strong> · <strong>${total.toLocaleString()} พอยต์</strong> · ≈ <strong>฿${parseFloat(pointsToBaht(total)).toLocaleString('en-US',{minimumFractionDigits:2})}</strong>`;
}

async function doBulkTransaction() {
    if (!bulkList.length) {
        Swal.fire({ icon:'warning', title:'แจ้งเตือน', text:'ยังไม่มีรายการ กรุณาสแกนบัตรก่อน', confirmButtonText:'ตกลง' });
        return;
    }
    const bad = bulkList.filter(x => !x.points || x.points <= 0 || x.points > 100000);
    if (bad.length) {
        Swal.fire({ icon:'warning', title:'แจ้งเตือน', html:`กรอกพอยต์ให้ครบทุกคน (1–100,000)<br>ยังไม่ครบ: <b>${bad.map(x=>x.student_id).join(', ')}</b>`, confirmButtonText:'ตกลง' });
        return;
    }

    const total = bulkList.reduce((s, x) => s + x.points, 0);
    const ok = await Swal.fire({
        icon: 'question',
        title: 'ยืนยันการเติมพอยต์',
        html: `เติมพอยต์ <b>${bulkList.length} คน</b> รวม <b>${total.toLocaleString()} พอยต์</b><br>≈ ฿${parseFloat(pointsToBaht(total)).toLocaleString('en-US',{minimumFractionDigits:2})}`,
        showCancelButton: true,
        confirmButtonText: '✓ ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#1a8c5a'
    });
    if (!ok.isConfirmed) return;

    const btn = document.getElementById('bulkConfirmBtn');
    btn.disabled = true; btn.textContent = '⏳ กำลังเติมพอยต์...';

    let success = 0;
    const errors = [];
    for (const x of bulkList) {
        try {
            const res = await fetch('../../config/controller.php?action=add_points', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ student_id: x.student_id, points: x.points, reason: 'เติมพอยต์ (หลายคน)' })
            }).then(r => r.json());
            if (res.success) success++;
            else errors.push(`${x.student_id}: ${res.message}`);
        } catch(e) {
            errors.push(`${x.student_id}: เชื่อมต่อไม่ได้`);
        }
    }

    btn.disabled = false; btn.textContent = '✓ เติมพอยต์ทั้งหมด';

    const result = document.getElementById('bulkResult');
    if (errors.length) {
        result.style.cssText = 'display:block;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px;color:#856404;font-size:14px;';
        result.innerHTML = `✅ สำเร็จ ${success} คน · ❌ ไม่สำเร็จ ${errors.length} คน<br>${errors.join('<br>')}`;
        // เก็บเฉพาะรายการที่ไม่สำเร็จไว้ให้ลองใหม่
        const failedIds = errors.map(e => e.split(':')[0]);
        bulkList = bulkList.filter(x => failedIds.includes(x.student_id));
        bulkRender();
    } else {
        result.style.display = 'none';
        Swal.fire({
            icon: 'success',
            title: 'เติมพอยต์สำเร็จ!',
            html: `เติมพอยต์ <b>${success} คน</b> รวม <b>${total.toLocaleString()} พอยต์</b>`,
            confirmButtonText: 'ตกลง',
            timer: 2500,
            timerProgressBar: true
        });
        bulkList = [];
        bulkRender();
        closeBulkModal();
    }
    loadRecentTransactions();
}

// ─── ฟังก์ชัน UI ──────────────────────────────────────────────────────────
function showAction(action) {
    currentAction = action;
    const form  = document.getElementById('actionForm');
    const title = document.getElementById('actionTitle');
    form.style.display = 'block';
    if (action === 'add') {
        title.textContent = '➕ เติมพอยต์';  title.style.color = '#1a8c5a';
    } else if (action === 'refund') {
        title.textContent = '💵 ถอนคืนเงิน'; title.style.color = '#003087';
    }
}
function resetDisplay() {
    document.getElementById('studentName').textContent     = 'กรุณาสแกนบัตร';
    document.getElementById('studentIdDisplay').textContent = '-';
    document.getElementById('currentPoints').textContent   = '0';
    document.getElementById('currentBaht').textContent     = '0.00';
}
function closeConfirmModal()    { document.getElementById('confirmModal').classList.remove('show'); }
function closeUserDetailModal() { document.getElementById('userDetailModal').classList.remove('show'); }
function closeSuccessModal()    { document.getElementById('successModal').classList.remove('show'); }
function updateTime()           { document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('th-TH'); }

// ─── เริ่มต้น ──────────────────────────────────────────────────────────────
async function init() {
    try {
        const res = await fetch('../../config/controller.php?action=get_settings').then(r => r.json());
        if (res.success) {
            exchangeRateBaht   = parseFloat(res.data.exchange_rate_baht || 1);
            exchangeRatePoints = parseFloat(res.data.exchange_rate_points || 1);
        }
    } catch(e) {}
    loadRecentTransactions();
    updateTime();
}

document.addEventListener('DOMContentLoaded', init);
setInterval(updateTime, 1000);
setInterval(loadRecentTransactions, 10000); // refresh ทุก 10 วินาที
document.getElementById('student_id').addEventListener('keypress', e => { if (e.key === 'Enter') searchStudent(); });
window.onclick = e => { if (e.target.classList.contains('modal')) e.target.classList.remove('show'); };
</script>

<?php require_once '../../static/footer.php'; ?>