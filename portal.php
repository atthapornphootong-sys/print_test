<?php
session_start();
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: ../../login.php"); exit;
}
$student_id   = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>พอยต์ของฉัน — <?= htmlspecialchars($student_name) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Prompt:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Sarabun',sans-serif;background:#f0f2f8;min-height:100vh;}

/* ── Header ── */
.topbar{background:linear-gradient(135deg,#002366,#003fa5);color:white;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.topbar-left{display:flex;align-items:center;gap:12px;}
.topbar-logo{font-size:24px;}
.topbar-title{font-family:'Prompt',sans-serif;font-size:16px;font-weight:700;}
.topbar-sub{font-size:11px;opacity:.65;}
.topbar-right{display:flex;align-items:center;gap:14px;}
.user-chip{background:rgba(255,255,255,.15);padding:6px 14px;border-radius:50px;font-size:13px;}
.btn-logout{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:white;padding:7px 16px;border-radius:8px;cursor:pointer;font-size:13px;font-family:'Sarabun',sans-serif;transition:all .22s cubic-bezier(.34,1.56,.64,1);}
.btn-logout:hover{background:rgba(255,255,255,.28);transform:translateY(-2px);}

/* ── Layout ── */
.container{max-width:900px;margin:32px auto;padding:0 20px;}

/* ── Hero Card ── */
.hero-card{border-radius:20px;padding:36px;color:white;margin-bottom:24px;position:relative;overflow:hidden;}
.hero-card.normal{background:linear-gradient(135deg,#002366 0%,#0056c7 100%);}
.hero-card.debt{background:linear-gradient(135deg,#7f1d1d 0%,#c53030 100%);}
.hero-card.low{background:linear-gradient(135deg,#78350f 0%,#d97706 100%);}
.hero-deco{position:absolute;border-radius:50%;background:rgba(255,255,255,.06);}
.hero-deco.d1{width:260px;height:260px;bottom:-80px;right:-60px;}
.hero-deco.d2{width:140px;height:140px;top:-40px;right:120px;}
.hero-name{font-size:16px;opacity:.8;margin-bottom:4px;}
.hero-id{font-size:13px;opacity:.6;margin-bottom:28px;}
.hero-pts-label{font-size:13px;opacity:.75;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
.hero-pts{font-family:'Prompt',sans-serif;font-size:72px;font-weight:700;line-height:1;}
.hero-pts-unit{font-size:20px;opacity:.8;margin-left:6px;}
.hero-status{display:inline-block;margin-top:16px;padding:6px 18px;border-radius:50px;background:rgba(255,255,255,.18);font-size:13px;font-weight:600;}
.debt-warn{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:12px;padding:14px 18px;margin-top:20px;font-size:14px;}

/* ── Stats Grid ── */
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;}
.stat-card{background:white;border-radius:16px;padding:22px 20px;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.stat-icon{font-size:28px;margin-bottom:10px;}
.stat-label{font-size:12px;color:#8896b4;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px;}
.stat-value{font-family:'Prompt',sans-serif;font-size:28px;font-weight:700;color:#002366;}
.stat-unit{font-size:12px;color:#aaa;margin-left:4px;}

/* ── History ── */
.history-card{background:white;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden;}
.history-head{padding:20px 24px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;}
.history-head h3{font-family:'Prompt',sans-serif;font-size:16px;color:#002366;}
.history-count{font-size:12px;color:#aaa;}
.history-item{display:flex;justify-content:space-between;align-items:center;padding:14px 24px;border-bottom:1px solid #f8f8f8;transition:background .15s;}
.history-item:last-child{border-bottom:none;}
.history-item:hover{background:#fafbff;}
.hi-left{display:flex;align-items:center;gap:12px;}
.hi-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;transition:transform .22s cubic-bezier(.34,1.56,.64,1);}
.history-item:hover .hi-icon{transform:rotate(10deg) scale(1.15);}
.hi-icon.add{background:#dcfce7;}
.hi-icon.print{background:#fff7ed;}
.hi-icon.refund{background:#eff6ff;}
.hi-type{font-weight:600;font-size:14px;color:#1a2340;}
.hi-time{font-size:12px;color:#9ca3af;}
.hi-amt{font-family:'Prompt',sans-serif;font-size:17px;font-weight:700;transition:transform .2s cubic-bezier(.34,1.56,.64,1);}
.history-item:hover .hi-amt{transform:scale(1.1);}
.hi-amt.pos{color:#14764e;}
.hi-amt.neg{color:#c53030;}

.empty-state{text-align:center;padding:48px;color:#bbb;}
.empty-state .e-icon{font-size:48px;margin-bottom:12px;}
.loading{text-align:center;padding:40px;color:#aaa;}

/* ═══ ANIMATIONS ═══ */
@keyframes heroEntrance {
  0%  { opacity:0; transform:translateY(30px) scale(.96); }
  60% { transform:translateY(-4px) scale(1.01); }
  100%{ opacity:1; transform:translateY(0) scale(1); }
}
@keyframes floatOrb {
  0%,100%{ transform:translate(0,0); }
  50%    { transform:translate(8px,-12px); }
}
@keyframes ptsCountUp {
  from{ opacity:0; transform:scale(.7) translateY(20px); }
  to  { opacity:1; transform:scale(1) translateY(0); }
}
@keyframes statusPop {
  0%  { opacity:0; transform:scale(.7); }
  70% { transform:scale(1.08); }
  100%{ opacity:1; transform:scale(1); }
}
@keyframes historySlide {
  from{ opacity:0; transform:translateX(16px); }
  to  { opacity:1; transform:translateX(0); }
}
@keyframes topbarSlide {
  from{ opacity:0; transform:translateY(-100%); }
  to  { opacity:1; transform:translateY(0); }
}

/* Topbar */
.topbar { animation: topbarSlide .4s ease both; }

/* Hero card entrance */
#heroCard { animation: heroEntrance .6s .1s cubic-bezier(.34,1.4,.64,1) both; }

/* Hero orbs float */
.hero-deco.d1 { animation: floatOrb 7s ease-in-out infinite; }
.hero-deco.d2 { animation: floatOrb 5s 1s ease-in-out infinite; }

/* Points big number */
.hero-pts { animation: ptsCountUp .7s .5s cubic-bezier(.34,1.56,.64,1) both; display:inline-block; }

/* Status badge */
#heroStatus { animation: statusPop .5s .7s cubic-bezier(.34,1.56,.64,1) both; }

/* Stats cards stagger */
.stats-grid .stat-card:nth-child(1){ animation: gFadeUp .45s .35s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .stat-card:nth-child(2){ animation: gFadeUp .45s .45s cubic-bezier(.34,1.4,.64,1) both; }
.stats-grid .stat-card:nth-child(3){ animation: gFadeUp .45s .55s cubic-bezier(.34,1.4,.64,1) both; }

/* Stat icons spin */
.stats-grid .stat-card:nth-child(1) .stat-icon{ animation: gSpin1 .5s .5s cubic-bezier(.34,1.56,.64,1) both; }
.stats-grid .stat-card:nth-child(2) .stat-icon{ animation: gSpin1 .5s .6s cubic-bezier(.34,1.56,.64,1) both; }
.stats-grid .stat-card:nth-child(3) .stat-icon{ animation: gSpin1 .5s .7s cubic-bezier(.34,1.56,.64,1) both; }

/* Stat values count up */
.stats-grid .stat-card:nth-child(1) .stat-value{ animation: gCountUp .5s .55s cubic-bezier(.34,1.56,.64,1) both; display:inline-block; }
.stats-grid .stat-card:nth-child(2) .stat-value{ animation: gCountUp .5s .65s cubic-bezier(.34,1.56,.64,1) both; display:inline-block; }
.stats-grid .stat-card:nth-child(3) .stat-value{ animation: gCountUp .5s .75s cubic-bezier(.34,1.56,.64,1) both; display:inline-block; }

/* History card */
.history-card { animation: gFadeUp .5s .6s cubic-bezier(.34,1.3,.64,1) both; }

/* History items stagger */
.history-item { animation: historySlide .35s ease both; }
.history-item:nth-child(1) { animation-delay:.65s }
.history-item:nth-child(2) { animation-delay:.72s }
.history-item:nth-child(3) { animation-delay:.79s }
.history-item:nth-child(4) { animation-delay:.86s }
.history-item:nth-child(5) { animation-delay:.93s }
.history-item:nth-child(n+6){ animation-delay:1.0s }

@media(max-width:600px){
    .stats-grid{grid-template-columns:1fr 1fr;}
    .hero-pts{font-size:52px;}
    .container{margin:16px auto;}
}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <span class="topbar-logo">🖨️</span>
    <div>
      <div class="topbar-title">TRU Print Point</div>
      <div class="topbar-sub">ระบบพอยต์พิมพ์ มหาวิทยาลัยธนบุรี</div>
    </div>
  </div>
  <div class="topbar-right">
    <span class="user-chip">🎓 <?= htmlspecialchars($student_name) ?></span>
    <button class="btn-logout" onclick="logout()">🚪 ออกจากระบบ</button>
  </div>
</div>

<div class="container">

  <!-- Hero Card -->
  <div class="hero-card normal" id="heroCard">
    <div class="hero-deco d1"></div>
    <div class="hero-deco d2"></div>
    <div class="hero-name">สวัสดี, <?= htmlspecialchars($student_name) ?></div>
    <div class="hero-id">รหัสนักศึกษา: <?= htmlspecialchars($student_id) ?></div>
    <div class="hero-pts-label">พอยต์คงเหลือ</div>
    <div>
      <span class="hero-pts" id="heroPts">--</span>
      <span class="hero-pts-unit">พอยต์</span>
    </div>
    <div class="hero-status" id="heroStatus">กำลังโหลด...</div>
    <div class="debt-warn" id="debtWarn" style="display:none;">
      🔴 คุณมียอดติดหนี้ กรุณาติดต่อเจ้าหน้าที่เพื่อเติมพอยต์
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">🖨️</div>
      <div class="stat-label">พิมพ์ทั้งหมด</div>
      <div class="stat-value" id="sTotalPrints">--<span class="stat-unit">ครั้ง</span></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📄</div>
      <div class="stat-label">พอยต์ที่ใช้ไป</div>
      <div class="stat-value" id="sTotalPages">--<span class="stat-unit">หน้า</span></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💎</div>
      <div class="stat-label">จำนวนครั้งที่พิมพ์</div>
      <div class="stat-value" id="sTotalSpent">--<span class="stat-unit">พอยต์</span></div>
    </div>
  </div>

  <!-- History -->
  <div class="history-card">
    <div class="history-head">
      <h3>📋 ประวัติการทำรายการ</h3>
      <span class="history-count" id="histCount"></span>
    </div>
    <div id="historyBody">
      <div class="loading">⏳ กำลังโหลดประวัติ...</div>
    </div>
  </div>

</div>

<!-- Logout Modal -->
<div id="logoutModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;animation:gOverlayIn .2s ease;">
  <div style="background:white;border-radius:20px;padding:36px 32px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);position:relative;">
    <div style="width:64px;height:64px;background:#fff3cd;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 18px;">🚪</div>
    <h3 style="font-family:'Prompt',sans-serif;font-size:20px;color:#002366;margin-bottom:8px;">ออกจากระบบ?</h3>
    <p style="color:#8896b4;font-size:14px;margin-bottom:8px;">สวัสดี, <strong style="color:#002366;"><?= htmlspecialchars($student_name) ?></strong></p>
    <p style="color:#8896b4;font-size:13px;margin-bottom:28px;">คุณต้องการออกจากระบบใช่หรือไม่?</p>
    <div style="display:flex;gap:12px;">
      <button onclick="closeLogoutModal()" style="flex:1;padding:12px;border:2px solid #e2e6f0;background:white;border-radius:10px;font-size:14px;font-family:'Sarabun',sans-serif;font-weight:600;color:#445070;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='#f0f2f8'" onmouseout="this.style.background='white'">ยกเลิก</button>
      <button onclick="confirmLogout()" style="flex:1;padding:12px;border:none;background:linear-gradient(135deg,#c53030,#e53e3e);border-radius:10px;font-size:14px;font-family:'Sarabun',sans-serif;font-weight:700;color:white;cursor:pointer;box-shadow:0 4px 14px rgba(197,48,48,.35);transition:all .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">🚪 ออกจากระบบ</button>
    </div>
  </div>
</div>

<script>
const STUDENT_ID = "<?= htmlspecialchars($student_id) ?>";
const API = "../../config/controller.php";

async function loadData() {
    try {
        const res = await fetch(`${API}?action=get_student_detail&student_id=${encodeURIComponent(STUDENT_ID)}`).then(r => r.json());
        if (!res.success) return;

        const s   = res.data.student;
        const stats = res.data.stats;
        const history = res.data.activities || [];
        const pts = parseInt(s.points_balance);

        // Hero card
        const ptEl = document.getElementById('heroPts');
        ptEl.style.animation = 'none';
        void ptEl.offsetHeight;
        ptEl.style.animation = '';
        if (window.animateCount) animateCount(ptEl, pts < 0 ? pts : Math.abs(pts), 900);
        else ptEl.textContent = pts;

        const card = document.getElementById('heroCard');
        const statusEl = document.getElementById('heroStatus');
        const debtWarn = document.getElementById('debtWarn');

        if (pts < 0) {
            card.className = 'hero-card debt';
            statusEl.textContent = '🔴 ติดหนี้';
            debtWarn.style.display = 'block';
        } else if (s.status === 'low') {
            card.className = 'hero-card low';
            statusEl.textContent = '⚠️ พอยต์ใกล้หมด';
        } else {
            card.className = 'hero-card normal';
            statusEl.textContent = '✅ พอยต์ปกติ';
        }

        // Stats
        document.getElementById('sTotalPrints').innerHTML = `${stats.total_prints||0}<span class="stat-unit">ครั้ง</span>`;
        document.getElementById('sTotalPages').innerHTML  = `${stats.used_points||0}<span class="stat-unit">พอยต์</span>`;
        document.getElementById('sTotalSpent').innerHTML  = `${stats.total_prints||0}<span class="stat-unit">ครั้ง</span>`;

        // History
        const body = document.getElementById('historyBody');
        document.getElementById('histCount').textContent = history.length ? `${history.length} รายการล่าสุด` : '';

        if (!history.length) {
            body.innerHTML = `<div class="empty-state"><div class="e-icon">📭</div><div>ยังไม่มีประวัติการทำรายการ</div></div>`;
            return;
        }

        body.innerHTML = history.map(h => {
            let icon, iconClass, typeText, amtClass, prefix;
            const t = h.type;
            if (t==='add') { icon='➕'; iconClass='add'; typeText='เติมพอยต์'; amtClass='pos'; prefix='+'; }
            else if (t==='refund') { icon='💵'; iconClass='refund'; typeText='ถอนคืนเงิน'; amtClass='neg'; prefix='-'; }
            else if (t==='print')  { icon='🖨️'; iconClass='print'; typeText='พิมพ์งาน'; amtClass='neg'; prefix='-'; }
            else if (t==='deduct') { icon='➖'; iconClass='print'; typeText='ตัดพอยต์'; amtClass='neg'; prefix='-'; }
            else { icon='📝'; iconClass='add'; typeText=t; amtClass=''; prefix=''; }
            const dt = h.date || new Date().toLocaleDateString('th-TH',{
});
            const detail = h.detail ? `<div class="hi-time" style="color:#bbb;margin-top:2px;">${h.detail}</div>` : '';
            return `<div class="history-item">
                <div class="hi-left">
                    <div class="hi-icon ${iconClass}">${icon}</div>
                    <div>
                        <div class="hi-type">${typeText}</div>
                        <div class="hi-time">${dt}</div>
                        ${detail}
                    </div>
                </div>
                <div class="hi-amt ${amtClass}">${prefix}${Math.abs(parseInt(h.points)||0)} พอยต์</div>
            </div>`;
        }).join('');

        // Re-animate history items
        body.querySelectorAll('.history-item').forEach(function(el, i) {
            el.style.animation = 'none';
            void el.offsetHeight;
            el.style.cssText += ';animation:historySlide .35s ' + (i * 0.07) + 's ease both';
        });

    } catch(e) {
        document.getElementById('historyBody').innerHTML =
            `<div class="empty-state"><div class="e-icon">❌</div><div>โหลดข้อมูลไม่สำเร็จ</div></div>`;
    }
}

function logout() {
    document.getElementById('logoutModal').style.display = 'flex';
}
function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}
function confirmLogout() {
    window.location.href = '../../logout_student.php';
}

loadData();
// Refresh ทุก 30 วินาที (กรณีมีคนเติมพอยต์ให้)
setInterval(loadData, 30000);
</script>
</body>
</html>
