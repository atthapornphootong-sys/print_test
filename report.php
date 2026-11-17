<?php
require_once '../../config/auth.php';
$page_title = "รายงานและสรุปบัญชี";
require_once '../../static/header.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<!-- Report Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>พอยต์ทั้งหมด</h3>
            <div class="stat-icon orange">💰</div>
        </div>
        <div class="stat-value" id="lifetimeAdded">-</div>
        <div class="stat-label">พอยต์ (ยอดเติมสะสม)</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>พอยต์ที่ใช้แล้ว</h3>
            <div class="stat-icon green">💸</div>
        </div>
        <div class="stat-value" id="lifetimeUsed">-</div>
        <div class="stat-label">พอยต์ (ยอดพิมพ์/ถอน)</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>พอยต์ที่ยังไม่ใช้</h3>
            <div class="stat-icon blue">📊</div>
        </div>
        <div class="stat-value" id="currentPoints">-</div>
        <div class="stat-label">พอยต์ (ค้างในระบบ)</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>ยอดเงินคงเหลือ</h3>
            <div class="stat-icon purple">฿</div>
        </div>
        <div class="stat-value" id="currentBaht">-</div>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div class="stat-label">บาท (แปลงจากพอยต์)</div>
            <div id="currentPointsSmall" style="font-size:13px; color:#666; font-weight:500;">- พอยต์</div>
        </div>
    </div>
</div>

<!-- Auto-refresh indicator -->
<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;padding:0 4px;">
    <span style="display:flex;align-items:center;gap:6px;font-size:13px;color:#666;">
        <span id="refreshDot" style="width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite;"></span>
        อัปเดตอัตโนมัติทุก 10 วินาที — อัปเดตล่าสุด: <strong id="lastRefreshTime">-</strong>
    </span>
    <button onclick="forceRefresh()" style="padding:4px 12px;border:1px solid #ddd;border-radius:6px;background:white;cursor:pointer;font-size:12px;">🔄 รีเฟรชเดี๋ยวนี้</button>
</div>

<!-- Report Type Tabs -->
<div class="card">
    <div class="card-header">
        <h2>📊 เลือกประเภทรายงาน</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;padding:18px;">
        <button class="report-menu-btn active" onclick="showReport('print')" id="btn-print">
            <span style="font-size:24px;">📄</span>
            <div style="margin-top:8px;">รายงานการพิมพ์</div>
        </button>
        <button class="report-menu-btn" onclick="showReport('add')" id="btn-add">
            <span style="font-size:24px;">➕</span>
            <div style="margin-top:8px;">รายงานการเติมพอยต์</div>
        </button>
        <button class="report-menu-btn" onclick="showReport('deduct')" id="btn-deduct">
            <span style="font-size:24px;">➖</span>
            <div style="margin-top:8px;">รายงานการตัดพอยต์</div>
        </button>
        <button class="report-menu-btn" onclick="showReport('refund')" id="btn-refund">
            <span style="font-size:24px;">💵</span>
            <div style="margin-top:8px;">รายงานการถอนคืน</div>
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header"><h2>🔍 ตัวกรองและส่งออกรายงาน</h2></div>
    <div class="filter-section">
        <div class="filter-row">
            <div class="form-group">
                <label for="start_date">วันที่เริ่มต้น</label>
                <input type="date" id="start_date" class="form-control" onchange="fetchReportData()">
            </div>
            <div class="form-group">
                <label for="end_date">วันที่สิ้นสุด</label>
                <input type="date" id="end_date" class="form-control" onchange="fetchReportData()">
            </div>
            <div class="form-group">
                <label for="items_per_page">แสดงต่อหน้า</label>
                <select id="items_per_page" class="form-control" onchange="updatePagination()">
                    <option value="5">5 รายการ</option>
                    <option value="10" selected>10 รายการ</option>
                    <option value="25">25 รายการ</option>
                    <option value="50">50 รายการ</option>
                    <option value="100">100 รายการ</option>
                </select>
            </div>
        </div>
        <!-- Search — real-time -->
        <div style="margin-top:15px;display:flex;gap:10px;align-items:center;">
            <div class="form-group" style="flex:1;margin:0;position:relative;">
                <input type="text" id="student_search" class="form-control"
                    placeholder="🔍 ค้นหาชื่อนักศึกษา... (พิมพ์แล้วผลขึ้นทันที)"
                    oninput="searchStudent()"
                    onkeydown="if(event.key==='Escape'){this.value='';searchStudent();}">
                <span id="clearSearchBtn" onclick="clearSearch()"
                    style="display:none;position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;color:#999;font-size:18px;">×</span>
            </div>
            <button onclick="exportExcel()" class="btn btn-success" style="padding:12px 20px;">📥 Excel</button>
            <button onclick="exportPDF()"   class="btn btn-danger"  style="padding:12px 20px;">📄 PDF</button>
        </div>
    </div>
</div>

<!-- Print Report -->
<div class="card report-section" id="printReport">
    <div class="card-header">
        <h2>📄 รายงานการพิมพ์</h2>
        <span class="badge badge-info">วันที่: <span id="printReportDate">-</span></span>
    </div>
    <div class="table-container">
        <table id="printTable">
            <thead>
                <tr>
                    <th>เวลา</th>
                    <th>ชื่อ-สกุล</th>
                    <th>เครื่องพิมพ์</th>
                    <th>จำนวนหน้า</th>
                    <th>พอยต์คงเหลือ</th>
                    <th>รายละเอียด</th>
                </tr>
            </thead>
            <tbody id="printTableBody">
                <tr><td colspan="7" style="text-align:center;color:#aaa;">กำลังโหลด...</td></tr>
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td colspan="3">รวมทั้งหมด (<span id="printTotalCount">0</span> รายการ)</td>
                    <td><span id="printTotalPages">0</span> หน้า</td>
                    <td colspan="2">-</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="pagination">
        <button onclick="prevPage('print')" id="printPrevBtn">« หน้าก่อน</button>
        <span>หน้า <strong id="printCurrentPage">1</strong> จาก <strong id="printTotalPage">1</strong></span>
        <button onclick="nextPage('print')" id="printNextBtn">หน้าถัดไป »</button>
    </div>
</div>

<!-- Add Report -->
<div class="card report-section" id="addReport" style="display:none;">
    <div class="card-header">
        <h2>➕ รายงานการเติมพอยต์</h2>
        <span class="badge badge-info">วันที่: <span id="addReportDate">-</span></span>
    </div>
    <div class="table-container">
        <table id="addTable">
            <thead>
                <tr><th>เวลา</th><th>ชื่อ-สกุล</th><th>พอยต์ที่เติม</th><th>คงเหลือหลังเติม</th><th>รายละเอียด</th></tr>
            </thead>
            <tbody id="addTableBody">
                <tr><td colspan="5" style="text-align:center;color:#aaa;">กำลังโหลด...</td></tr>
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td colspan="2">รวมทั้งหมด (<span id="addTotalCount">0</span> รายการ)</td>
                    <td style="color:#14764e;font-size:18px;">+<span id="addTotalPoints">0</span> พอยต์</td>
                    <td colspan="2">เฉลี่ย: <span id="addAverage">0</span> พอยต์/ครั้ง</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="pagination">
        <button onclick="prevPage('add')" id="addPrevBtn">« หน้าก่อน</button>
        <span>หน้า <strong id="addCurrentPage">1</strong> จาก <strong id="addTotalPage">1</strong></span>
        <button onclick="nextPage('add')" id="addNextBtn">หน้าถัดไป »</button>
    </div>
</div>

<!-- Deduct Report -->
<div class="card report-section" id="deductReport" style="display:none;">
    <div class="card-header">
        <h2>➖ รายงานการตัดพอยต์</h2>
        <span class="badge badge-info">วันที่: <span id="deductReportDate">-</span></span>
    </div>
    <div class="table-container">
        <table id="deductTable">
            <thead>
                <tr><th>เวลา</th><th>ชื่อ-สกุล</th><th>พอยต์ที่ตัด</th><th>คงเหลือหลังตัด</th><th>รายละเอียด</th></tr>
            </thead>
            <tbody id="deductTableBody">
                <tr><td colspan="5" style="text-align:center;color:#aaa;">กำลังโหลด...</td></tr>
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td colspan="2">รวมทั้งหมด (<span id="deductTotalCount">0</span> รายการ)</td>
                    <td style="color:#b43030;font-size:18px;">-<span id="deductTotalPoints">0</span> พอยต์</td>
                    <td colspan="2">เฉลี่ย: <span id="deductAverage">0</span> พอยต์/ครั้ง</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="pagination">
        <button onclick="prevPage('deduct')" id="deductPrevBtn">« หน้าก่อน</button>
        <span>หน้า <strong id="deductCurrentPage">1</strong> จาก <strong id="deductTotalPage">1</strong></span>
        <button onclick="nextPage('deduct')" id="deductNextBtn">หน้าถัดไป »</button>
    </div>
</div>

<!-- Refund Report -->
<div class="card report-section" id="refundReport" style="display:none;">
    <div class="card-header">
        <h2>💵 รายงานการถอนคืนเงิน</h2>
        <span class="badge badge-info">วันที่: <span id="refundReportDate">-</span></span>
    </div>
    <div class="table-container">
        <table id="refundTable">
            <thead>
                <tr><th>เวลา</th><th>ชื่อ-สกุล</th><th>พอยต์ที่ถอน</th><th>คงเหลือหลังถอน</th><th>รายละเอียด</th></tr>
            </thead>
            <tbody id="refundTableBody">
                <tr><td colspan="5" style="text-align:center;color:#aaa;">กำลังโหลด...</td></tr>
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;font-weight:bold;">
                    <td colspan="2">รวมทั้งหมด (<span id="refundTotalCount">0</span> รายการ)</td>
                    <td style="color:#17a2b8;font-size:18px;">-<span id="refundTotalPoints">0</span> พอยต์</td>
                    <td colspan="2">เฉลี่ย: <span id="refundAverage">0</span> พอยต์/ครั้ง</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="pagination">
        <button onclick="prevPage('refund')" id="refundPrevBtn">« หน้าก่อน</button>
        <span>หน้า <strong id="refundCurrentPage">1</strong> จาก <strong id="refundTotalPage">1</strong></span>
        <button onclick="nextPage('refund')" id="refundNextBtn">หน้าถัดไป »</button>
    </div>
</div>

<!-- Monthly Summary Sections -->
<div style="display:flex; flex-direction:column; gap:25px; margin-top:20px;">
    
    <!-- Print Summary Card -->
    <div class="card" style="margin:0;">
        <div class="card-header">
            <h2>📄 สรุปบัญชีการพิมพ์ (Monthly Print Revenue)</h2>
        </div>
        <div class="table-container">
            <table class="summary-list-table">
                <thead>
                    <tr>
                        <th style="width:200px;">เดือน / ปี</th>
                        <th>จำนวนครั้งที่พิมพ์</th>
                        <th>จำนวนหน้าทั้งหมด</th>
                        <th style="text-align:right;">รายได้รวม (บาท / พอยต์)</th>
                    </tr>
                </thead>
                <tbody id="printMonthlyBody">
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Print Graph -->
        <div style="padding:20px; height:250px; position:relative; border-top:1px solid #eee;">
            <canvas id="printChart"></canvas>
        </div>
    </div>

    <!-- Cash Summary Card -->
    <div class="card" style="margin:0;">
        <div class="card-header">
            <h2>💰 สรุปรายการฝากถอน (Monthly Cash Flow)</h2>
        </div>
        <div class="table-container">
            <table class="summary-list-table">
                <thead>
                    <tr>
                        <th style="width:200px;">เดือน / ปี</th>
                        <th>เงินเข้า (ฝาก)</th>
                        <th>เงินออก (ถอน)</th>
                        <th style="text-align:right;">ยอดสุทธิ (บาท / พอยต์)</th>
                    </tr>
                </thead>
                <tbody id="cashMonthlyBody">
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Cash Graph -->
        <div style="padding:20px; height:250px; position:relative; border-top:1px solid #eee;">
            <canvas id="cashChart"></canvas>
        </div>
    </div>

</div>



<style>
.summary-list-table { width: 100%; border-collapse: collapse; }
.summary-list-table th { background: #f1f5f9; color: #475569; text-align: left; padding: 15px; border-bottom: 2px solid #e2e8f0; font-size: 14px; }
.summary-list-table td { border-bottom: 1px solid #f1f5f9; padding: 15px !important; font-size: 15px; }
.month-label { font-weight: bold; color: #003087; }

.liability-badge { background:#fff9db; border:1px solid #ffec99; border-radius:12px; padding:20px; margin:20px 0; display:flex; justify-content:space-between; align-items:center; }
</style>

<!-- System Liability & Grand Totals -->
<div id="liabilityHeaderContainer" style="margin-top:20px;"></div>





<style>
.filter-section{padding:20px;}
.filter-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;}
.report-menu-btn{padding:20px;border:2px solid #ddd;background:white;border-radius:12px;cursor:pointer;text-align:center;font-size:14px;font-weight:500;}
.report-menu-btn.active{border-color:#003087;background:linear-gradient(135deg,#003087 0%,#002060 100%);color:white;}
.report-section{animation:gFadeUp .35s ease both;}
@keyframes newRow{from{background:#fef9c3;}to{background:transparent;}}
.new-row td{animation:newRow 2.5s ease-out;}

/* ── Menu buttons stagger ── */
.report-menu-btn {
  animation: gPopIn .4s cubic-bezier(.34,1.56,.64,1) both;
}
.report-menu-btn:nth-child(1){animation-delay:.08s}
.report-menu-btn:nth-child(2){animation-delay:.15s}
.report-menu-btn:nth-child(3){animation-delay:.22s}
.report-menu-btn:nth-child(4){animation-delay:.29s}

/* ── Active menu button glow ── */
.report-menu-btn.active {
  box-shadow: 0 6px 20px rgba(0,48,135,.35);
  transform: translateY(-3px);
}

/* ── Refresh dot pulse ── */
#refreshDot { animation: gPulse 2s infinite; }

/* ── Export button hover ── */
button[onclick*="export"], button[onclick*="Export"] {
  transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s !important;
}
button[onclick*="export"]:hover, button[onclick*="Export"]:hover {
  transform: translateY(-3px) scale(1.04);
  box-shadow: 0 8px 18px rgba(0,0,0,.14);
}

/* ── Pagination buttons ── */
.pagination button, button[onclick*="page"], button[onclick*="Page"] {
  transition: transform .18s cubic-bezier(.34,1.56,.64,1), background .18s !important;
}
.pagination button:hover { transform: scale(1.1); }

/* ── Stats header cards ── */
.report-stats, .stat-card {
  animation: gFadeUp .4s ease both;
}

/* ── Filter row entrance ── */
.filter-section {
  animation: gFadeIn .4s .1s ease both;
}
</style>

<script>
let currentReport = 'print';
let currentPage   = { print:1, add:1, deduct:1, refund:1 };
let itemsPerPage  = 10;
let allData       = { print:[], add:[], deduct:[], refund:[] };
let filteredData  = { print:[], add:[], deduct:[], refund:[] };
let autoRefreshTimer = null;
let lastDataSnapshot = '';   // ใช้เปรียบเทียบว่ามีข้อมูลใหม่ไหม

function getTodayTH() {
    const now = new Date();
    const offset = 7*60;
    const local = new Date(now.getTime()+(offset-now.getTimezoneOffset())*60000);
    return local.toISOString().split('T')[0];
}
const today = getTodayTH();
document.getElementById('start_date').value = today;
document.getElementById('end_date').value   = today;

function formatTime(dt) {
    if (!dt) return '-';
    return new Date(dt).toLocaleTimeString('th-TH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
function formatDate(ds) {
    if (!ds) return '-';
    return new Date(ds).toLocaleDateString('th-TH',{year:'numeric',month:'long',day:'numeric'});
}
function formatMonth(y,m) {
    return new Date(y,m-1,1).toLocaleDateString('th-TH',{year:'numeric',month:'long'});
}

// ===== Fetch ข้อมูลหลัก =====
function fetchReportData(silent=false) {
    const s = document.getElementById('start_date').value;
    const e = document.getElementById('end_date').value;
    const dl= formatDate(s)+(s!==e?' - '+formatDate(e):'');
    ['print','add','deduct','refund'].forEach(t=>{
        const el=document.getElementById(t+'ReportDate');
        if(el) el.textContent=dl;
    });

    fetch('../../config/controller.php?action=get_activities_report&start_date='+s+'&end_date='+e)
        .then(r=>r.json()).then(res=>{
            if(!res.success){if(!silent)alert('เกิดข้อผิดพลาด: '+res.message);return;}
            const data=res.data||[];

            // เช็คว่ามีข้อมูลใหม่จริงๆ ไหม (ไม่ re-render ถ้าเหมือนเดิม)
            const snapshot = JSON.stringify(data.map(a=>a.activity_id));
            const hasNew = snapshot !== lastDataSnapshot;
            lastDataSnapshot = snapshot;

            allData.print  = data.filter(a=>a.activity_type==='print');
            allData.add    = data.filter(a=>a.activity_type==='add_points');
            allData.deduct = data.filter(a=>a.activity_type==='withdraw_points' || a.activity_type==='print');
            allData.refund = data.filter(a=>a.activity_type==='refund_money');

            // ถ้ามีการค้นหาอยู่ ให้ filter ใหม่ด้วย
            const q = document.getElementById('student_search').value.toLowerCase();
            if(q) {
                Object.keys(allData).forEach(type=>{
                    filteredData[type]=allData[type].filter(a=>
                        (a.student_id||'').toLowerCase().includes(q)||
                        (a.student_name||'').toLowerCase().includes(q));
                });
            } else {
                filteredData=JSON.parse(JSON.stringify(allData));
            }

            // ถ้ามีข้อมูลใหม่ reset หน้าไปที่ 1 เฉพาะ tab ที่กำลังดูอยู่
            if(hasNew) currentPage[currentReport]=1;

            updateSummaryCards();
            updateTable(currentReport);
            updateLastRefreshTime();
            blinkDot();
        }).catch(err=>{
            if(!silent) console.error(err);
        });
}

function fetchTodayStats() {
    fetch('../../config/controller.php?action=get_activities_report&start_date='+today+'&end_date='+today)
        .then(r=>r.json()).then(res=>{
            if(!res.success)return;
            const data=res.data||[];
            const prints=data.filter(a=>a.activity_type==='print');
            const adds  =data.filter(a=>a.activity_type==='add_points');
            document.getElementById('totalPrints').textContent = prints.length.toLocaleString();
            document.getElementById('totalPages').textContent  = prints.reduce((s,a)=>s+(parseInt(a.pages)||0),0).toLocaleString();
            document.getElementById('totalUsed').textContent   = prints.reduce((s,a)=>s+Math.abs(parseInt(a.points)||0),0).toLocaleString();
            document.getElementById('totalAdded').textContent  = adds.reduce((s,a)=>s+(parseInt(a.points)||0),0).toLocaleString();
            document.getElementById('totalUsers').textContent  = new Set(data.map(a=>a.student_id)).size.toLocaleString();
        });
}

function updateSummaryCards() {
    const prints=allData.print, adds=allData.add;
    // อัปเดต summary card เฉพาะเมื่อดูวันนี้
    const s=document.getElementById('start_date').value;
    const e=document.getElementById('end_date').value;
    // null-safe: set textContent เฉพาะเมื่อ element มีอยู่จริง
    const setTxt=(id,val)=>{const el=document.getElementById(id); if(el) el.textContent=val;};
    if(s===today && e===today) {
        setTxt('totalPrints', prints.length.toLocaleString());
        setTxt('totalPages',  prints.reduce((s,a)=>s+(parseInt(a.pages)||0),0).toLocaleString());
        setTxt('totalUsed',   prints.reduce((s,a)=>s+Math.abs(parseInt(a.points)||0),0).toLocaleString());
        setTxt('totalAdded',  adds.reduce((s,a)=>s+(parseInt(a.points)||0),0).toLocaleString());
        const all=[...allData.print,...allData.add,...allData.deduct,...allData.refund];
        setTxt('totalUsers',  new Set(all.map(a=>a.student_id)).size.toLocaleString());
    }
}

function updateLastRefreshTime() {
    const now = new Date();
    document.getElementById('lastRefreshTime').textContent =
        now.toLocaleTimeString('th-TH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}

function blinkDot() {
    const dot = document.getElementById('refreshDot');
    dot.style.background = '#3b82f6';
    setTimeout(()=>{ dot.style.background='#22c55e'; }, 500);
}

function forceRefresh() {
    fetchReportData(false);
    fetchTodayStats();
    resetAutoRefresh();
}

// ===== Auto-refresh ทุก 10 วินาที =====
function startAutoRefresh() {
    autoRefreshTimer = setInterval(()=>{
        fetchReportData(true);   // silent=true ไม่แสดง alert
        fetchTodayStats();
    }, 10000);
}
function resetAutoRefresh() {
    clearInterval(autoRefreshTimer);
    startAutoRefresh();
}

function showReport(type) {
    currentReport=type;
    document.querySelectorAll('.report-section').forEach(s=>s.style.display='none');
    document.querySelectorAll('.report-menu-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById(type+'Report').style.display='block';
    document.getElementById('btn-'+type).classList.add('active');
    updateTable(type);
}

function updateTable(type) {
    const data=filteredData[type];
    const tbody=document.getElementById(type+'TableBody');
    tbody.innerHTML='';
    const cols={print:7,add:5,deduct:5,refund:5};
    if(!data.length) {
        tbody.innerHTML='<tr><td colspan="'+cols[type]+'" style="text-align:center;color:#aaa;padding:30px;">ไม่มีข้อมูล</td></tr>';
        updatePaginationUI(type,1); updateSummaryFooter(type,[]); return;
    }
    const start=(currentPage[type]-1)*itemsPerPage;
    const pageData=data.slice(start,start+itemsPerPage);
    const totalPages=Math.ceil(data.length/itemsPerPage);
    if(type==='print')  renderPrintRows(pageData,tbody);
    if(type==='add')    renderAddRows(pageData,tbody);
    if(type==='deduct') renderDeductRows(pageData,tbody);
    if(type==='refund') renderRefundRows(pageData,tbody);
    updateSummaryFooter(type,data);
    updatePaginationUI(type,totalPages);
    if(window.reAnimateRows) reAnimateRows(type+'TableBody');
}

// เพิ่มคอลัมน์ พอยต์คงเหลือ
function renderPrintRows(data,tbody) {
    data.forEach(a=>{
        const row=document.createElement('tr');
        const pointsAfter = a.points_after !== undefined && a.points_after !== null
            ? parseInt(a.points_after) : '-';
        const pointsAfterColor = pointsAfter < 0 ? 'color:#dc2626;font-weight:bold;' : '';
        row.innerHTML=
            '<td>'+formatTime(a.created_at)+'</td>'+
            '<td>'+( a.student_name||'-')+'</td>'+
            '<td>'+(a.printer_id?'Printer-'+String(a.printer_id).padStart(2,'0'):'-')+'</td>'+
            '<td style="text-align:center;">'+(a.pages||0)+' หน้า</td>'+
            '<td style="text-align:center;'+pointsAfterColor+'">'+(pointsAfter !== '-' ? pointsAfter+' พอยต์' : '-')+'</td>'+
            '<td style="color:#666;font-size:12.5px;">'+(a.activity_detail||'-')+'</td>';
        tbody.appendChild(row);
    });
}

function renderAddRows(data,tbody) {
    data.forEach(a=>{
        const row=document.createElement('tr');
        row.innerHTML=
            '<td>'+formatTime(a.created_at)+'</td>'+
            '<td>'+(a.student_name||'-')+'</td>'+
            '<td style="color:#14764e;font-weight:bold;">+'+(parseInt(a.points)||0)+'</td>'+
            '<td>'+(a.points_after||0)+'</td>'+
            '<td>'+(a.activity_detail||'-')+'</td>';
        tbody.appendChild(row);
    });
}
function renderDeductRows(data,tbody) {
    data.forEach(a=>{
        const row=document.createElement('tr');
        const detail = a.activity_type==='print' ? 'พิมพ์งาน '+(a.pages||0)+' หน้า' : (a.activity_detail||'-');
        row.innerHTML=
            '<td>'+formatTime(a.created_at)+'</td>'+
            '<td>'+(a.student_name||'-')+'</td>'+
            '<td style="color:#b43030;font-weight:bold;">'+Math.abs(parseInt(a.points)||0)+'</td>'+
            '<td>'+(a.points_after||0)+'</td>'+
            '<td>'+detail+'</td>';
        tbody.appendChild(row);
    });
}
function renderRefundRows(data,tbody) {
    data.forEach(a=>{
        const row=document.createElement('tr');
        row.innerHTML=
            '<td>'+formatTime(a.created_at)+'</td>'+
            '<td>'+(a.student_name||'-')+'</td>'+
            '<td style="color:#17a2b8;font-weight:bold;">'+(parseInt(a.points)||0)+'</td>'+
            '<td>'+(a.points_after||0)+'</td>'+
            '<td>'+(a.activity_detail||'-')+'</td>';
        tbody.appendChild(row);
    });
}

function updateSummaryFooter(type,data) {
    if(type==='print'){
        const pages=data.reduce((s,a)=>s+(parseInt(a.pages)||0),0);
        document.getElementById('printTotalCount').textContent =data.length;
        document.getElementById('printTotalPages').textContent =pages;
    } else if(type==='add'){
        const pts=data.reduce((s,a)=>s+(parseInt(a.points)||0),0);
        document.getElementById('addTotalCount').textContent =data.length;
        document.getElementById('addTotalPoints').textContent=pts;
        document.getElementById('addAverage').textContent   =data.length>0?Math.round(pts/data.length):0;
    } else if(type==='deduct'){
        const pts=data.reduce((s,a)=>s+Math.abs(parseInt(a.points)||0),0);
        document.getElementById('deductTotalCount').textContent =data.length;
        document.getElementById('deductTotalPoints').textContent=pts;
        document.getElementById('deductAverage').textContent   =data.length>0?Math.round(pts/data.length):0;
    } else if(type==='refund'){
        const pts=data.reduce((s,a)=>s+Math.abs(parseInt(a.points)||0),0);
        document.getElementById('refundTotalCount').textContent =data.length;
        document.getElementById('refundTotalPoints').textContent=pts;
        document.getElementById('refundAverage').textContent   =data.length>0?Math.round(pts/data.length):0;
    }
}

function updatePaginationUI(type,totalPages) {
    document.getElementById(type+'CurrentPage').textContent=currentPage[type];
    document.getElementById(type+'TotalPage').textContent  =totalPages||1;
    document.getElementById(type+'PrevBtn').disabled=currentPage[type]===1;
    document.getElementById(type+'NextBtn').disabled=currentPage[type]>=(totalPages||1);
}
function fetchTodayStats(){
    fetch('../../config/controller.php?action=get_dashboard_stats')
        .then(r => r.json())
        .then(res => {
            if(res.success){
                const d = res.data;
                document.getElementById('lifetimeAdded').innerText = d.lifetime_added.toLocaleString();
                document.getElementById('lifetimeUsed').innerText  = d.lifetime_used.toLocaleString();
                document.getElementById('currentPoints').innerText = d.current_total_points.toLocaleString();
                document.getElementById('currentBaht').innerText   = '฿' + d.current_total_baht.toLocaleString(undefined, {minimumFractionDigits: 2});
                document.getElementById('currentPointsSmall').innerText = d.current_total_points.toLocaleString() + ' พอยต์';
                document.getElementById('lastRefreshTime').innerText = new Date().toLocaleTimeString('th-TH');
            }
        });
}
function prevPage(type){if(currentPage[type]>1){currentPage[type]--;updateTable(type);}}
function nextPage(type){const t=Math.ceil(filteredData[type].length/itemsPerPage);if(currentPage[type]<t){currentPage[type]++;updateTable(type);}}

// ===== Real-time search =====
function searchStudent(){
    const q=document.getElementById('student_search').value.toLowerCase();
    document.getElementById('clearSearchBtn').style.display=q?'block':'none';
    Object.keys(allData).forEach(type=>{
        filteredData[type]=q===''?allData[type]:allData[type].filter(a=>
            (a.student_id||'').toLowerCase().includes(q)||
            (a.student_name||'').toLowerCase().includes(q));
        currentPage[type]=1;
    });
    updateTable(currentReport);
}
function clearSearch(){
    document.getElementById('student_search').value='';
    document.getElementById('clearSearchBtn').style.display='none';
    filteredData=JSON.parse(JSON.stringify(allData));
    Object.keys(currentPage).forEach(t=>currentPage[t]=1);
    updateTable(currentReport);
}
function updatePagination(){
    itemsPerPage=parseInt(document.getElementById('items_per_page').value);
    currentPage[currentReport]=1;
    updateTable(currentReport);
}

let printChartObj = null;
let cashChartObj = null;

function fetchMonthlySummary(){
    const printBody = document.getElementById('printMonthlyBody');
    const cashBody = document.getElementById('cashMonthlyBody');
    const liabilityContainer = document.getElementById('liabilityHeaderContainer');
    
    const months = [];
    for(let i=0; i<6; i++){
        const d = new Date();
        d.setMonth(d.getMonth() - i);
        months.push({year: d.getFullYear(), month: d.getMonth() + 1});
    }

    Promise.all(months.map(m => {
        const start = m.year + '-' + String(m.month).padStart(2, '0') + '-01';
        const lastDay = new Date(m.year, m.month, 0).getDate();
        const end = m.year + '-' + String(m.month).padStart(2, '0') + '-' + lastDay;
        return fetch('../../config/controller.php?action=get_activities_report&start_date=' + start + '&end_date=' + end)
            .then(r => r.json()).then(res => ({...m, data: res.success ? res.data : []}));
    })).then(async results => {
        printBody.innerHTML = '';
        cashBody.innerHTML = '';
        
        const settingsRes = await fetch('../../config/controller.php?action=get_settings').then(r => r.json());
        let exBaht = 1, exPts = 1;
        if(settingsRes.success) {
            exBaht = parseFloat(settingsRes.data.exchange_rate_baht || 1);
            exPts  = parseFloat(settingsRes.data.exchange_rate_points || 1);
        }

        fetch('../../config/controller.php?action=get_dashboard_stats')
            .then(r => r.json())
            .then(statsRes => {
                if(statsRes.success) {
                    const totalPts = statsRes.data.total_points;
                    const liabilityBaht = totalPts * (exBaht / exPts);
                    liabilityContainer.innerHTML = `
                        <div class="liability-badge">
                            <div>
                                <div style="color:#856404; font-weight:bold; font-size:16px;">📌 ยอดเงินที่ระบบต้องสำรองไว้ (Liability)</div>
                                <small style="color:#a58c3c;">* คำนวณจากพอยต์คงเหลือของนักศึกษาทุกคน (${totalPts.toLocaleString()} พอยต์)</small>
                            </div>
                            <div style="font-size:24px; font-weight:bold; color:#e67e22;">฿${liabilityBaht.toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                        </div>
                    `;
                }
            });

        const chartData = { labels: [], printRevenue: [], netCashFlow: [] };

        results.reverse().forEach(r => {
            const prints = r.data.filter(a => a.activity_type === 'print');
            const adds   = r.data.filter(a => a.activity_type === 'add_points');
            const deducts = r.data.filter(a => a.activity_type === 'withdraw_points');
            const refunds = r.data.filter(a => a.activity_type === 'refund_money');

            const pages = prints.reduce((s, a) => s + (parseInt(a.pages) || 0), 0);
            const printBaht = prints.reduce((s, a) => s + (parseFloat(a.money_amount) || 0), 0);
            const addBaht   = adds.reduce((s, a) => s + (parseFloat(a.money_amount) || 0), 0);
            const cashOutBaht = (deducts.reduce((s, a) => s + (parseFloat(a.money_amount) || 0), 0) + refunds.reduce((s, a) => s + (parseFloat(a.money_amount) || 0), 0));
            const netCashIn = addBaht - cashOutBaht;

            const monthName = formatMonth(r.year, r.month);
            chartData.labels.push(monthName);
            chartData.printRevenue.push(printBaht);
            chartData.netCashFlow.push(netCashIn);

            // Row for Print Table
            const printPts = prints.reduce((s, a) => s + Math.abs(parseInt(a.points) || 0), 0);
            const trPrint = document.createElement('tr');
            trPrint.innerHTML = `
                <td class="month-label">${monthName}</td>
                <td>${prints.length.toLocaleString()} ครั้ง</td>
                <td>${pages.toLocaleString()} หน้า</td>
                <td style="text-align:right; font-weight:bold; color:#003087;">
                    ฿${printBaht.toLocaleString('en-US',{minimumFractionDigits:2})}<br>
                    <small style="color:#666; font-weight:normal;">(${printPts.toLocaleString()} พอยต์)</small>
                </td>
            `;
            printBody.prepend(trPrint);

            // Row for Cash Table
            const addPts = adds.reduce((s, a) => s + (parseInt(a.points) || 0), 0);
            const cashOutPts = (deducts.reduce((s, a) => s + Math.abs(parseInt(a.points) || 0), 0) + refunds.reduce((s, a) => s + Math.abs(parseInt(a.points) || 0), 0));
            const netCashPts = addPts - cashOutPts;

            const trCash = document.createElement('tr');
            trCash.innerHTML = `
                <td class="month-label">${monthName}</td>
                <td style="color:#14764e; font-weight:bold;">
                    ฿${addBaht.toLocaleString('en-US',{minimumFractionDigits:2})}<br>
                    <small style="color:#666; font-weight:normal;">(${addPts.toLocaleString()} พอยต์)</small>
                </td>
                <td style="color:#b43030;">
                    ฿${cashOutBaht.toLocaleString('en-US',{minimumFractionDigits:2})}<br>
                    <small style="color:#666; font-weight:normal;">(${cashOutPts.toLocaleString()} พอยต์)</small>
                </td>
                <td style="text-align:right; color:${netCashIn >= 0 ? '#14764e' : '#b43030'}; font-weight:bold;">
                    ฿${netCashIn.toLocaleString('en-US',{minimumFractionDigits:2})}<br>
                    <small style="color:#666; font-weight:normal;">(${netCashPts.toLocaleString()} พอยต์)</small>
                </td>
            `;
            cashBody.prepend(trCash);
        });

        renderCharts(chartData);
    });
}

function renderCharts(data) {
    const pCtx = document.getElementById('printChart').getContext('2d');
    const cCtx = document.getElementById('cashChart').getContext('2d');

    if (printChartObj) printChartObj.destroy();
    if (cashChartObj) cashChartObj.destroy();

    printChartObj = new Chart(pCtx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'รายได้จากการพิมพ์ (บาท)',
                data: data.printRevenue,
                borderColor: '#003087',
                backgroundColor: 'rgba(0, 48, 135, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '฿' + v.toLocaleString() } } }
        }
    });

    cashChartObj = new Chart(cCtx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'กระแสเงินสดรับสุทธิ (บาท)',
                data: data.netCashFlow,
                borderColor: '#14764e',
                backgroundColor: 'rgba(20, 118, 78, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '฿' + v.toLocaleString() } } }
        }
    });
}

function exportExcel(){
    const data=filteredData[currentReport];
    if(!data.length)return alert('ไม่มีข้อมูลสำหรับ Export');
    const s=document.getElementById('start_date').value;
    const e=document.getElementById('end_date').value;
    let headers,rows;
    if(currentReport==='print'){
        headers=['เวลา','ชื่อ-สกุล','เครื่องพิมพ์','จำนวนหน้า','พอยต์คงเหลือ','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,a.printer_id?'Printer-'+String(a.printer_id).padStart(2,'0'):'-',a.pages||0,a.points_after??'-',a.activity_detail||'-']);
    } else if(currentReport==='add'){
        headers=['เวลา','ชื่อ-สกุล','พอยต์ที่เติม','คงเหลือหลังเติม','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,'+'+(parseInt(a.points)||0),a.points_after||0,a.activity_detail||'-']);
    } else if(currentReport==='deduct'){
        headers=['เวลา','ชื่อ-สกุล','พอยต์ที่ตัด','คงเหลือหลังตัด','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,parseInt(a.points)||0,a.points_after||0,a.activity_detail||'-']);
    } else {
        headers=['เวลา','ชื่อ-สกุล','พอยต์ที่ถอน','คงเหลือหลังถอน','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,parseInt(a.points)||0,a.points_after||0,a.activity_detail||'-']);
    }
    const titleMap={print:'การพิมพ์',add:'การเติมพอยต์',deduct:'การตัดพอยต์',refund:'การถอนคืน'};
    const BOM='\uFEFF'; let csv=BOM;
    csv+=`รายงาน${titleMap[currentReport]},วันที่ ${s} ถึง ${e}\n`;
    csv+=headers.join(',')+'\n';
    rows.forEach(r=>{csv+=r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')+'\n';});
    const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
    const url=URL.createObjectURL(blob);
    const a=document.createElement('a');a.href=url;a.download=`report_${currentReport}_${s}_${e}.csv`;a.click();
    URL.revokeObjectURL(url);
}

function exportPDF(){
    const data=filteredData[currentReport];
    if(!data.length)return alert('ไม่มีข้อมูลสำหรับ Export');
    const s=document.getElementById('start_date').value;
    const e=document.getElementById('end_date').value;
    const titleMap={print:'รายงานการพิมพ์',add:'รายงานการเติมพอยต์',deduct:'รายงานการตัดพอยต์',refund:'รายงานการถอนคืน'};
    let headers,rows;
    if(currentReport==='print'){
        headers=['เวลา','ชื่อ-สกุล','เครื่องพิมพ์','หน้า','คงเหลือ','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,a.printer_id?'Printer-'+String(a.printer_id).padStart(2,'0'):'-',a.pages||0,a.points_after??'-',a.activity_detail||'-']);
    } else if(currentReport==='add'){
        headers=['เวลา','ชื่อ-สกุล','พอยต์เติม','คงเหลือ','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,'+'+(parseInt(a.points)||0),a.points_after||0,a.activity_detail||'-']);
    } else if(currentReport==='deduct'){
        headers=['เวลา','ชื่อ-สกุล','พอยต์ตัด','คงเหลือ','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,parseInt(a.points)||0,a.points_after||0,a.activity_detail||'-']);
    } else {
        headers=['เวลา','ชื่อ-สกุล','พอยต์ถอน','คงเหลือ','รายละเอียด'];
        rows=data.map(a=>[formatTime(a.created_at),a.student_name,parseInt(a.points)||0,a.points_after||0,a.activity_detail||'-']);
    }
    const tableRows=rows.map(r=>`<tr>${r.map(v=>`<td>${v}</td>`).join('')}</tr>`).join('');
    const html=`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>${titleMap[currentReport]}</title>
<style>
  @page{size:A4 landscape;margin:12mm;}
  body{font-family:'Sarabun','Tahoma',sans-serif;font-size:11px;color:#333;}
  h2{text-align:center;color:#003087;margin-bottom:4px;}
  .sub{text-align:center;color:#888;font-size:11px;margin-bottom:16px;}
  table{width:100%;border-collapse:collapse;}
  th{background:#003087;color:white;padding:7px 6px;text-align:left;font-size:11px;}
  td{padding:6px;border-bottom:1px solid #eee;font-size:11px;}
  tr:nth-child(even) td{background:#f8f9ff;}
  .footer{text-align:right;margin-top:10px;font-size:10px;color:#aaa;}
</style>
</head><body>
<h2>${titleMap[currentReport]}</h2>
<div class="sub">วันที่ ${s} ถึง ${e} | ทั้งหมด ${data.length} รายการ</div>
<table>
  <thead><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead>
  <tbody>${tableRows}</tbody>
</table>
<div class="footer">พิมพ์เมื่อ: ${new Date().toLocaleString('th-TH')}</div>
<script>window.onload=()=>{window.print();}<\/script>
</body></html>`;
    const win=window.open('','_blank');
    win.document.write(html);
    win.document.close();
}

document.addEventListener('DOMContentLoaded',()=>{
    fetchTodayStats();
    fetchReportData();
    fetchMonthlySummary();
    startAutoRefresh();   // เริ่ม auto-refresh
});
</script>

<?php require_once '../../static/footer.php'; ?>