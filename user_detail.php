<?php
require_once '../../config/auth.php';
$page_title = "User Detail";
require_once '../../static/header.php';
?>

<style>
.quick-links { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px; }
.quick-link-btn { display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;cursor:pointer;border:none;transition:all .22s cubic-bezier(.34,1.56,.64,1); }
.quick-link-btn:hover { opacity:.9;transform:translateY(-3px) scale(1.04); box-shadow:0 8px 18px rgba(0,0,0,.15); }
.ql-dashboard { background:#003087;color:#fff; }
.ql-point     { background:#43e97b;color:#fff; }
.ql-report    { background:#fa8231;color:#fff; }
.ql-users     { background:#6c757d;color:#fff; }

.profile-card { background:linear-gradient(135deg,#003087 0%,#002060 100%);border-radius:14px;padding:28px 30px;color:#fff;display:flex;align-items:center;gap:24px;margin-bottom:24px;box-shadow:0 6px 20px rgba(102,126,234,.35);flex-wrap:wrap; }
.profile-avatar { width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0; }
.profile-info { flex:1;min-width:200px; }
.profile-name  { font-size:22px;font-weight:700;margin-bottom:4px; }
.profile-id    { font-size:14px;opacity:.85;margin-bottom:8px; }
.profile-badges { display:flex;gap:8px;flex-wrap:wrap; }
.profile-badge { background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600; }
.profile-points-box { text-align:center;background:rgba(255,255,255,.15);border-radius:12px;padding:16px 24px; }
.profile-points-label { font-size:12px;opacity:.8;margin-bottom:4px; }
.profile-points-val   { font-size:38px;font-weight:800;line-height:1; }
.profile-points-unit  { font-size:13px;opacity:.8; }

.action-shortcuts { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px; }
.shortcut-btn { flex:1;min-width:140px;padding:13px 18px;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all .22s cubic-bezier(.34,1.56,.64,1);color:#fff;display:flex;align-items:center;justify-content:center;gap:7px; }
.shortcut-btn:hover { opacity:.9;transform:translateY(-3px) scale(1.04); box-shadow:0 8px 18px rgba(0,0,0,.15); }
.shortcut-btn:active { transform:scale(.96); }
.sc-add    { background:linear-gradient(135deg,#43e97b,#38f9d7); }
.sc-deduct { background:linear-gradient(135deg,#fa8231,#f7b731); }
.sc-report { background:linear-gradient(135deg,#003087,#002060); }

.status-active   { background:#14764e;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
.status-inactive { background:#6c757d;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }
.status-low      { background:#ffc107;color:#333;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold; }

/* ── Quick link buttons stagger in ── */
.quick-link-btn:nth-child(1){animation:gSlideR .35s .05s cubic-bezier(.34,1.56,.64,1) both}
.quick-link-btn:nth-child(2){animation:gSlideR .35s .12s cubic-bezier(.34,1.56,.64,1) both}
.quick-link-btn:nth-child(3){animation:gSlideR .35s .19s cubic-bezier(.34,1.56,.64,1) both}
.quick-link-btn:nth-child(4){animation:gSlideR .35s .26s cubic-bezier(.34,1.56,.64,1) both}

/* ── Search card ── */
.card { animation: gFadeUp .45s .1s cubic-bezier(.34,1.3,.64,1) both; }

/* ── Profile card entrance ── */
#profileCard .profile-card {
  animation: gFadeUp .5s cubic-bezier(.34,1.4,.64,1) both;
}
#profileCard .profile-avatar {
  animation: gPopIn .55s .12s cubic-bezier(.34,1.56,.64,1) both;
}
#profileCard .profile-name {
  animation: gSlideR .4s .18s ease both;
}
#profileCard .profile-points-val {
  animation: gCountUp .6s .25s cubic-bezier(.34,1.56,.64,1) both;
  display: inline-block;
}

/* ── Shortcut buttons stagger ── */
.sc-add    { animation: gPopIn .4s .28s cubic-bezier(.34,1.56,.64,1) both; }
.sc-deduct { animation: gPopIn .4s .36s cubic-bezier(.34,1.56,.64,1) both; }
.sc-report { animation: gPopIn .4s .44s cubic-bezier(.34,1.56,.64,1) both; }

/* ── Detail grid cards ── */
.detail-grid .card:nth-child(1) { animation: gFadeUp .45s .3s ease both; }
.detail-grid .card:nth-child(2) { animation: gFadeUp .45s .4s ease both; }

/* ── History table rows ── */
#historyBody tr {
  animation: gFadeUp .3s ease both;
}
#historyBody tr:nth-child(odd)  { animation-delay:.05s }
#historyBody tr:nth-child(even) { animation-delay:.10s }
</style>

<!-- Quick Links -->
<div class="quick-links">
    <a href="dashboard.php"    class="quick-link-btn ql-dashboard">📊 Dashboard</a>
    <a href="manage_point.php" class="quick-link-btn ql-point">💰 จัดการพอยต์</a>
    <a href="report.php"       class="quick-link-btn ql-report">📋 รายงาน</a>
    <a href="users.php"        class="quick-link-btn ql-users">👥 รายชื่อนักศึกษา</a>
</div>

<!-- Search -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><h2>🔍 ค้นหานักศึกษา</h2></div>
    <div style="display:flex;gap:12px;align-items:center;">
        <input type="text" id="searchStudentId" class="form-control"
               placeholder="กรอกรหัสนักศึกษา เช่น 6601103071011"
               style="flex:1;" autocomplete="off"
               onkeydown="if(event.key==='Enter') loadStudentDetail();">
        <button onclick="loadStudentDetail()" class="btn btn-primary"
                style="padding:12px 28px;white-space:nowrap;">🔍 ค้นหา</button>
    </div>
</div>

<!-- Loading -->
<div id="loadingSpinner" style="display:none;text-align:center;padding:50px;">
    <div style="font-size:40px;">⏳</div>
    <p style="color:#003087;margin-top:10px;font-size:16px;">กำลังโหลดข้อมูล...</p>
</div>

<!-- Error -->
<div id="errorMsg" style="display:none;" class="card">
    <div style="text-align:center;padding:30px;color:#b43030;">
        <div style="font-size:48px;">❌</div>
        <p id="errorText" style="margin-top:12px;font-size:18px;font-weight:600;"></p>
    </div>
</div>

<!-- Profile Card -->
<div id="profileCard" style="display:none;">
    <div class="profile-card">
        <div class="profile-avatar">👤</div>
        <div class="profile-info">
            <div class="profile-name"   id="pcName">-</div>
            <div class="profile-id"     id="pcId">-</div>
            <div class="profile-badges">
                <span class="profile-badge" id="pcMajor"   style="display:none;"></span>
                <span class="profile-badge" id="pcFaculty" style="display:none;"></span>
                <span id="pcStatus"></span>
            </div>
        </div>
        <div class="profile-points-box">
            <div class="profile-points-label">พอยต์คงเหลือ</div>
            <div class="profile-points-val"  id="pcPoints">0</div>
            <div class="profile-points-unit">พอยต์</div>
        </div>
    </div>
    <div class="action-shortcuts">
        <button class="shortcut-btn sc-add"    onclick="goManagePoint()">➕ เติม / ตัดพอยต์</button>
        <button class="shortcut-btn sc-report" onclick="goReport()">📋 ดูรายงาน</button>
    </div>
</div>

<!-- Stats -->
<div class="card" id="usageStats" style="display:none;">
    <div class="card-header"><h2>📊 สถิติการใช้งาน</h2></div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header"><h3>การพิมพ์ทั้งหมด</h3><div class="stat-icon blue">🖨️</div></div>
            <div class="stat-value" id="statTotalPrints">0</div>
            <div class="stat-label">ครั้ง</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header"><h3>พอยต์ที่ใช้ไป</h3><div class="stat-icon green">💸</div></div>
            <div class="stat-value" id="statUsedPoints">0</div>
            <div class="stat-label">พอยต์</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header"><h3>พอยต์ที่เติม</h3><div class="stat-icon orange">💰</div></div>
            <div class="stat-value" id="statAddedPoints">0</div>
            <div class="stat-label">พอยต์</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header"><h3>ใช้งานล่าสุด</h3><div class="stat-icon purple">⏰</div></div>
            <div class="stat-value" id="statLastUsed" style="font-size:18px;">-</div>
            <div class="stat-label">วันที่</div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="chart-container" id="printPattern" style="display:none;">
    <h3 style="margin-bottom:20px;color:#333;">📈 รูปแบบการพิมพ์ (7 วันล่าสุด)</h3>
    <div class="chart-wrapper">
        <canvas id="userPrintChart"></canvas>
    </div>
</div>

<!-- Activity History -->
<div class="card" id="activityHistory" style="display:none;">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h2>📋 ประวัติการใช้งาน</h2>
        <select class="form-control" style="width:200px;" id="filterActivity" onchange="filterActivity()">
            <option value="all">ทั้งหมด</option>
            <option value="print">การพิมพ์</option>
            <option value="add">เติมพอยต์</option>
            <option value="deduct">ตัดพอยต์</option>
            <option value="refund">ถอนคืน</option>
        </select>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>วันที่/เวลา</th><th>กิจกรรม</th><th>รายละเอียด</th>
                    <th>พอยต์</th><th>คงเหลือ</th><th>สถานะ</th>
                </tr>
            </thead>
            <tbody id="activityTableBody"></tbody>
        </table>
    </div>
    <div class="pagination">
        <button onclick="prevActivityPage()" id="btnPrev">« หน้าก่อน</button>
        <span>หน้า <strong id="currentPageLabel">1</strong> จาก <strong id="totalPagesLabel">1</strong></span>
        <button onclick="nextActivityPage()" id="btnNext">หน้าถัดไป »</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const API = '../../config/controller.php';
let chart = null, allActivities = [], currentPage = 1;
const PER_PAGE = 10;
let currentStudentId = '';

async function loadStudentDetail() {
    const sid = document.getElementById('searchStudentId').value.trim();
    if (!sid) { alert('⚠️ กรุณากรอกรหัสนักศึกษา'); return; }
    hideAll();
    show('loadingSpinner'); hide('errorMsg');
    try {
        const res  = await fetch(`${API}?action=get_student_detail&student_id=${encodeURIComponent(sid)}`);
        const data = await res.json();
        hide('loadingSpinner');
        if (!data.success) { show('errorMsg'); document.getElementById('errorText').textContent = data.message; return; }
        currentStudentId = sid;
        // ✅ ล้างช่องและโฟกัสพร้อมค้นหาคนต่อไป
        document.getElementById('searchStudentId').value = '';
        document.getElementById('searchStudentId').focus();
        renderStudent(data);
    } catch(err) {
        hide('loadingSpinner'); show('errorMsg');
        document.getElementById('errorText').textContent = 'เกิดข้อผิดพลาด: ' + err.message;
    }
}

function renderStudent(data) {
    const { student, stats, activities, chart: cd } = data.data;

    document.getElementById('pcName').textContent   = student.student_name;
    document.getElementById('pcId').textContent     = '🆔 ' + student.student_id;
    const ptEl = document.getElementById('pcPoints');
    if (window.animateCount) animateCount(ptEl, parseInt(student.points_balance), 800);
    else ptEl.textContent = student.points_balance;

    const majorEl = document.getElementById('pcMajor');
    const facEl   = document.getElementById('pcFaculty');
    if (student.major_name)   { majorEl.textContent = '📚 ' + (student.major_abbr ? student.major_abbr+' · ' : '') + student.major_name; majorEl.style.display = ''; }
    if (student.faculty_name) { facEl.textContent   = '🏫 ' + student.faculty_name; facEl.style.display = ''; }

    const pts = parseInt(student.points_balance) || 0;
    let badgeHtml = '';
    if (pts < 0) {
        badgeHtml = '<span style="background:#b43030;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:bold;">🔴 ติดหนี้</span>';
    } else if (pts <= 10) {
        badgeHtml = '<span class="status-low">⚠️ พอยต์ต่ำ</span>';
    } else {
        badgeHtml = '<span class="status-active">ปกติ</span>';
    }
    document.getElementById('pcStatus').innerHTML = badgeHtml;

    document.getElementById('statTotalPrints').textContent = stats.total_prints;
    document.getElementById('statUsedPoints').textContent  = stats.used_points;
    document.getElementById('statAddedPoints').textContent = stats.added_points;
    document.getElementById('statLastUsed').textContent    = stats.last_used;

    allActivities = activities; currentPage = 1;
    renderActivityTable();
    renderChart(cd.labels, cd.data);
    ['profileCard','usageStats','activityHistory','printPattern'].forEach(id => show(id));
    document.getElementById('profileCard').scrollIntoView({ behavior:'smooth', block:'start' });
}

function renderActivityTable() {
    const filter   = document.getElementById('filterActivity').value;
    const filtered = filter === 'all' ? allActivities : allActivities.filter(a => a.type === filter);
    const total    = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
    if (currentPage > total) currentPage = total;
    document.getElementById('currentPageLabel').textContent = currentPage;
    document.getElementById('totalPagesLabel').textContent  = total;
    document.getElementById('btnPrev').disabled = currentPage <= 1;
    document.getElementById('btnNext').disabled = currentPage >= total;
    const slice = filtered.slice((currentPage-1)*PER_PAGE, currentPage*PER_PAGE);
    const bm = {
        print:  '<span class="badge" style="background:#ffc107;color:#333;padding:3px 8px;border-radius:12px;font-size:12px;">🖨️ พิมพ์</span>',
        add:    '<span class="badge" style="background:#14764e;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">➕ เติมพอยต์</span>',
        deduct: '<span class="badge" style="background:#b43030;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">➖ ตัดพอยต์</span>',
        refund: '<span class="badge" style="background:#17a2b8;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">💵 ถอนคืน</span>',
    };
    document.getElementById('activityTableBody').innerHTML = !slice.length
        ? '<tr><td colspan="6" style="text-align:center;padding:30px;color:#999;">ไม่มีข้อมูล</td></tr>'
        : slice.map(a => {
            const pts   = parseInt(a.points);
            const color = pts > 0 ? '#1a8c5a' : (pts < 0 ? '#c53030' : '#666');
            const disp  = pts === 0 ? '-' : (pts > 0 ? '+' : '') + pts;
            const st    = a.status === 'success'
                ? '<span style="background:#14764e;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">สำเร็จ</span>'
                : '<span style="background:#b43030;color:#fff;padding:3px 8px;border-radius:12px;font-size:12px;">พอยต์ไม่พอ</span>';
            return `<tr>
                <td style="font-size:13px;">${a.date}</td>
                <td>${bm[a.type]||a.type}</td>
                <td style="font-size:13px;color:#555;">${a.detail||'-'}</td>
                <td style="color:${color};font-weight:700;font-size:16px;">${disp}</td>
                <td style="font-weight:600;">${a.balance}</td>
                <td>${st}</td>
            </tr>`;
        }).join('');
    if (window.reAnimateRows) reAnimateRows('activityTableBody');
}

function filterActivity() { currentPage = 1; renderActivityTable(); }
function prevActivityPage() { if (currentPage > 1) { currentPage--; renderActivityTable(); } }
function nextActivityPage() { currentPage++; renderActivityTable(); }

function renderChart(labels, data) {
    const ctx = document.getElementById('userPrintChart');
    if (!ctx) return;
    if (chart) chart.destroy();
    chart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: { labels, datasets: [{ label:'จำนวนหน้าที่พิมพ์', data, backgroundColor:'rgba(102,126,234,.75)', borderColor:'#003087', borderWidth:2, borderRadius:5 }] },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:true } },
            scales:{ y:{ beginAtZero:true, title:{ display:true, text:'จำนวนหน้า' } } } }
    });
}

function goManagePoint() {
    if (currentStudentId) sessionStorage.setItem('mp_student_id', currentStudentId);
    window.location.href = 'manage_point.php';
}
function goReport() {
    if (currentStudentId) sessionStorage.setItem('rp_student_id', currentStudentId);
    window.location.href = 'report.php';
}

function show(id) { document.getElementById(id).style.display = ''; }
function hide(id) { document.getElementById(id).style.display = 'none'; }
function hideAll() { ['profileCard','usageStats','activityHistory','printPattern'].forEach(hide); }

document.getElementById('searchStudentId').addEventListener('keypress', e => {
    if (e.key === 'Enter') loadStudentDetail();
});
</script>

<?php require_once '../../static/footer.php'; ?>