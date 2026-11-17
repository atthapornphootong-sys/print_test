<?php
require_once '../../config/auth.php';
$page_title = "Dashboard";
require_once '../../static/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

.dash-root {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: calc(100vh - 110px);
  overflow: hidden;
  font-family: 'Sarabun', sans-serif;
  padding: 4px 2px;
}

/* ── KPI Cards ── */
.kpi-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  flex-shrink: 0;
}

.kpi-card {
  position: relative;
  overflow: hidden;
  border-radius: 20px;
  padding: 20px 22px 18px;
  transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
  cursor: default;
}
.kpi-card:hover {
  transform: translateY(-5px) scale(1.01);
  box-shadow: 0 20px 40px rgba(0,0,0,.18);
}

.kpi-card.kpi-blue   { background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 60%, #60a5fa 100%); }
.kpi-card.kpi-gold   { background: linear-gradient(135deg, #92400e 0%, #b45309 60%, #d97706 100%); }
.kpi-card.kpi-orange { background: linear-gradient(135deg, #9a3412 0%, #ea580c 60%, #fb923c 100%); }
.kpi-card.kpi-green  { background: linear-gradient(135deg, #065f46 0%, #059669 60%, #34d399 100%); }

.kpi-card::after {
  content: '';
  position: absolute;
  right: -28px; bottom: -28px;
  width: 120px; height: 120px;
  border-radius: 50%;
  background: rgba(255,255,255,.12);
  pointer-events: none;
}
.kpi-card::before {
  content: '';
  position: absolute;
  right: 30px; bottom: 20px;
  width: 60px; height: 60px;
  border-radius: 50%;
  background: rgba(255,255,255,.07);
  pointer-events: none;
}

.kpi-inner { position: relative; z-index: 1; }

.kpi-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
}

.kpi-icon-wrap {
  width: 42px; height: 42px;
  border-radius: 12px;
  background: rgba(255,255,255,.22);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  backdrop-filter: blur(6px);
}

.kpi-badge {
  font-size: 10px;
  font-family: 'Kanit', sans-serif;
  font-weight: 500;
  color: rgba(255,255,255,.8);
  background: rgba(255,255,255,.2);
  padding: 3px 10px;
  border-radius: 50px;
  letter-spacing: .3px;
}

.kpi-value {
  font-family: 'Kanit', sans-serif;
  font-size: 34px;
  font-weight: 800;
  color: #fff;
  line-height: 1;
  letter-spacing: -1px;
}

.kpi-label {
  font-family: 'Sarabun', sans-serif;
  font-size: 12px;
  font-weight: 400;
  color: rgba(255,255,255,.72);
  margin-top: 6px;
}

/* ── Main Grid ── */
.main-row {
  display: grid;
  grid-template-columns: 1fr 1fr 290px;
  gap: 16px;
  flex: 1;
  min-height: 0;
}

/* ── Chart Cards ── */
.chart-card {
  background: var(--white);
  border-radius: 20px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 20px rgba(0,0,0,.04);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: box-shadow .2s;
}
.chart-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.09); }

.chart-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 18px;
  border-bottom: 1px solid var(--border-soft);
  flex-shrink: 0;
}

.chart-card-header h3 {
  font-family: 'Kanit', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: var(--navy);
}

.chart-badge {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-3);
  background: var(--surface-2);
  padding: 3px 10px;
  border-radius: 50px;
  border: 1px solid var(--border);
}

.chart-body {
  flex: 1;
  padding: 14px 16px;
  min-height: 0;
  position: relative;
}

/* ── Right Column ── */
.right-col {
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-height: 0;
}

.side-card {
  background: var(--white);
  border-radius: 20px;
  border: 1px solid var(--border);
  box-shadow: 0 4px 20px rgba(0,0,0,.04);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  flex: 1;
  min-height: 0;
  transition: box-shadow .2s;
}
.side-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.09); }

.side-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-soft);
  flex-shrink: 0;
}

.side-card-header h3 {
  font-family: 'Kanit', sans-serif;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--navy);
}

.side-card-body { flex: 1; overflow-y: auto; }
.side-card-body::-webkit-scrollbar { width: 3px; }
.side-card-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

/* ── Activity ── */
.act-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 16px;
  border-bottom: 1px solid var(--border-soft);
  font-size: 12px;
  transition: background .15s;
}
.act-item:last-child { border-bottom: none; }
.act-item:hover { background: var(--surface-2); }

.act-dot-wrap {
  width: 30px; height: 30px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 14px;
}
.dot-print  { background: #eff6ff; }
.dot-add    { background: #ecfdf5; }
.dot-deduct { background: #fff7ed; }
.dot-refund { background: #f5f3ff; }

.act-name {
  font-family: 'Kanit', sans-serif;
  font-weight: 600;
  font-size: 12px;
  color: var(--text-1);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 105px;
}
.act-desc { color: var(--text-3); font-size: 10.5px; margin-top: 1px; }
.act-pts {
  font-family: 'Kanit', sans-serif;
  font-weight: 700;
  font-size: 12px;
  margin-left: auto;
  flex-shrink: 0;
  padding: 2px 8px;
  border-radius: 20px;
}
.pts-neg { color: #ef4444; background: #fef2f2; }
.pts-pos { color: #10b981; background: #ecfdf5; }

/* ── Printers ── */
.printer-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 16px;
  border-bottom: 1px solid var(--border-soft);
  font-size: 12px;
}
.printer-item:last-child { border-bottom: none; }

.p-dot-wrap {
  width: 32px; height: 32px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px;
  flex-shrink: 0;
}
.p-ready-wrap   { background: #dcfce7; }
.p-low-wrap     { background: #fff7ed; }
.p-offline-wrap { background: #fef2f2; }

.p-name {
  font-family: 'Kanit', sans-serif;
  font-weight: 600;
  font-size: 12px;
  color: var(--navy);
}
.p-loc  { font-size: 10.5px; color: var(--text-3); }

.p-paper-pill {
  font-size: 10px;
  font-weight: 700;
  margin-left: auto;
  flex-shrink: 0;
  padding: 3px 9px;
  border-radius: 20px;
}
.p-paper-ok      { background: #dcfce7; color: #166534; }
.p-paper-low     { background: #fff7ed; color: #9a3412; }
.p-paper-offline { background: #fef2f2; color: #991b1b; }

/* ── Shimmer ── */
@keyframes shimmer { 0%{background-position:-600px 0} 100%{background-position:600px 0} }
.shimmer {
  background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
  background-size: 600px 100%;
  animation: shimmer 1.6s infinite;
  border-radius: 8px;
  height: 13px;
  margin: 8px 0;
}

/* ── Animations ── */
@keyframes fadeUp {
  from { opacity:0; transform:translateY(22px); }
  to   { opacity:1; transform:translateY(0); }
}
@keyframes fadeIn {
  from { opacity:0; }
  to   { opacity:1; }
}
@keyframes slideRight {
  from { opacity:0; transform:translateX(-16px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes popIn {
  0%   { opacity:0; transform:scale(.82); }
  70%  { transform:scale(1.04); }
  100% { opacity:1; transform:scale(1); }
}
@keyframes countUp {
  from { opacity:0; transform:translateY(8px) scale(.9); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
@keyframes pulseGlow {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,255,255,.15); }
  50%     { box-shadow: 0 0 0 10px rgba(255,255,255,0); }
}
@keyframes floatOrb {
  0%,100% { transform: translate(0,0) scale(1); }
  33%     { transform: translate(6px,-8px) scale(1.05); }
  66%     { transform: translate(-4px,5px) scale(.97); }
}
@keyframes shimmerKPI {
  0%   { left:-100%; }
  100% { left:200%; }
}
@keyframes actSlide {
  from { opacity:0; transform:translateX(14px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes barGrow {
  from { transform: scaleY(0); transform-origin: bottom; }
  to   { transform: scaleY(1); transform-origin: bottom; }
}
@keyframes spinOnce {
  from { transform: rotate(-15deg) scale(.8); opacity:0; }
  to   { transform: rotate(0deg) scale(1);   opacity:1; }
}

/* KPI cards stagger */
.kpi-card {
  animation: fadeUp .5s cubic-bezier(.34,1.56,.64,1) both;
}
.kpi-card:nth-child(1) { animation-delay:.04s }
.kpi-card:nth-child(2) { animation-delay:.12s }
.kpi-card:nth-child(3) { animation-delay:.20s }
.kpi-card:nth-child(4) { animation-delay:.28s }

/* KPI shimmer streak on load */
.kpi-card::after {
  animation: pulseGlow 3s 1s ease infinite;
}

/* KPI icon pop */
.kpi-icon-wrap {
  animation: spinOnce .6s cubic-bezier(.34,1.56,.64,1) both;
}
.kpi-card:nth-child(1) .kpi-icon-wrap { animation-delay:.2s }
.kpi-card:nth-child(2) .kpi-icon-wrap { animation-delay:.28s }
.kpi-card:nth-child(3) .kpi-icon-wrap { animation-delay:.36s }
.kpi-card:nth-child(4) .kpi-icon-wrap { animation-delay:.44s }

/* KPI value count-up effect */
.kpi-value {
  animation: countUp .5s cubic-bezier(.34,1.56,.64,1) both;
}
.kpi-card:nth-child(1) .kpi-value { animation-delay:.25s }
.kpi-card:nth-child(2) .kpi-value { animation-delay:.33s }
.kpi-card:nth-child(3) .kpi-value { animation-delay:.41s }
.kpi-card:nth-child(4) .kpi-value { animation-delay:.49s }

/* KPI badge slide */
.kpi-badge {
  animation: slideRight .4s ease both;
}
.kpi-card:nth-child(1) .kpi-badge { animation-delay:.3s }
.kpi-card:nth-child(2) .kpi-badge { animation-delay:.38s }
.kpi-card:nth-child(3) .kpi-badge { animation-delay:.46s }
.kpi-card:nth-child(4) .kpi-badge { animation-delay:.54s }

/* Orb decoration float */
.kpi-card::before {
  animation: floatOrb 6s ease-in-out infinite;
}

/* Chart & side cards stagger */
.chart-card:nth-child(1) { animation: fadeUp .55s .32s cubic-bezier(.34,1.4,.64,1) both; }
.chart-card:nth-child(2) { animation: fadeUp .55s .42s cubic-bezier(.34,1.4,.64,1) both; }
.right-col              { animation: fadeUp .55s .52s cubic-bezier(.34,1.4,.64,1) both; }
.side-card              { animation: none; }

/* Chart card header badge pop */
.chart-badge {
  animation: popIn .5s .7s cubic-bezier(.34,1.56,.64,1) both;
}

/* Activity items slide in */
.act-item {
  animation: actSlide .35s ease both;
}
.act-item:nth-child(1)  { animation-delay:.6s }
.act-item:nth-child(2)  { animation-delay:.66s }
.act-item:nth-child(3)  { animation-delay:.72s }
.act-item:nth-child(4)  { animation-delay:.78s }
.act-item:nth-child(5)  { animation-delay:.84s }
.act-item:nth-child(6)  { animation-delay:.90s }
.act-item:nth-child(7)  { animation-delay:.96s }
.act-item:nth-child(8)  { animation-delay:1.02s }
.act-item:nth-child(9)  { animation-delay:1.08s }
.act-item:nth-child(10) { animation-delay:1.14s }

/* Printer items pop */
.printer-item {
  animation: actSlide .35s ease both;
}
.printer-item:nth-child(1) { animation-delay:.7s }
.printer-item:nth-child(2) { animation-delay:.78s }
.printer-item:nth-child(3) { animation-delay:.86s }
.printer-item:nth-child(4) { animation-delay:.94s }

/* Hover pulse on KPI cards */
.kpi-card:hover .kpi-icon-wrap {
  animation: pulseGlow .6s ease infinite;
}

/* Points pill pop on hover */
.act-pts {
  transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
}
.act-item:hover .act-pts {
  transform: scale(1.15);
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
}

/* Paper pill hover */
.p-paper-pill {
  transition: transform .2s cubic-bezier(.34,1.56,.64,1);
}
.printer-item:hover .p-paper-pill {
  transform: scale(1.12);
}

/* Dot wrap hover spin */
.act-dot-wrap, .p-dot-wrap {
  transition: transform .25s cubic-bezier(.34,1.56,.64,1);
}
.act-item:hover .act-dot-wrap,
.printer-item:hover .p-dot-wrap {
  transform: rotate(12deg) scale(1.15);
}

/* Side card header link */
.side-card-header a {
  transition: transform .2s ease, color .2s;
  display: inline-block;
}
.side-card-header a:hover {
  transform: translateX(3px);
  color: #2563eb !important;
}
</style>

<div class="dash-root">

  <div class="kpi-row">
    <div class="kpi-card kpi-blue">
      <div class="kpi-inner">
        <div class="kpi-top">
          <div class="kpi-icon-wrap">👥</div>
          <span class="kpi-badge">นักศึกษา</span>
        </div>
        <div class="kpi-value" id="kpi_students">--</div>
        <div class="kpi-label">คนในระบบ</div>
      </div>
    </div>
    <div class="kpi-card kpi-gold">
      <div class="kpi-inner">
        <div class="kpi-top">
          <div class="kpi-icon-wrap">💎</div>
          <span class="kpi-badge">พอยต์</span>
        </div>
        <div class="kpi-value" id="kpi_points">--</div>
        <div class="kpi-label" id="kpi_avg">เฉลี่ย -- พอยต์/คน</div>
      </div>
    </div>
    <div class="kpi-card kpi-orange">
      <div class="kpi-inner">
        <div class="kpi-top">
          <div class="kpi-icon-wrap">🖨️</div>
          <span class="kpi-badge">เครื่องพิมพ์</span>
        </div>
        <div class="kpi-value" id="kpi_printers">--/--</div>
        <div class="kpi-label">พร้อมใช้งาน</div>
      </div>
    </div>
    <div class="kpi-card kpi-green">
      <div class="kpi-inner">
        <div class="kpi-top">
          <div class="kpi-icon-wrap">📄</div>
          <span class="kpi-badge">วันนี้</span>
        </div>
        <div class="kpi-value" id="kpi_today">--</div>
        <div class="kpi-label">ครั้งที่พิมพ์</div>
      </div>
    </div>
  </div>

  <div class="main-row">
    <div class="chart-card">
      <div class="chart-card-header">
        <h3>📊 พอยต์ที่ใช้รายวัน</h3>
        <span class="chart-badge">7 วันล่าสุด</span>
      </div>
      <div class="chart-body">
        <canvas id="chartDaily"></canvas>
      </div>
    </div>

    <div class="chart-card">
      <div class="chart-card-header">
        <h3>📈 เติม vs ตัดพอยต์</h3>
        <span class="chart-badge">30 วันล่าสุด</span>
      </div>
      <div class="chart-body">
        <canvas id="chartTxn"></canvas>
      </div>
    </div>

    <div class="right-col">
      <div class="side-card" style="flex:1.6;">
        <div class="side-card-header">
          <h3>⚡ กิจกรรมล่าสุด</h3>
          <a href="report.php" style="font-size:11px;color:#3b82f6;font-weight:600;text-decoration:none;">ดูทั้งหมด →</a>
        </div>
        <div class="side-card-body" id="act_list">
          <div style="padding:16px;">
            <div class="shimmer"></div><div class="shimmer" style="width:70%;"></div>
            <div class="shimmer" style="width:85%;"></div><div class="shimmer" style="width:60%;"></div>
          </div>
        </div>
      </div>
      <div class="side-card" style="flex:1;">
        <div class="side-card-header">
          <h3>🖨️ สถานะเครื่องพิมพ์</h3>
          <span style="font-size:10px;color:#94a3b8;">
            <span style="color:#22c55e;">●</span> OK &nbsp;
            <span style="color:#f97316;">●</span> น้อย &nbsp;
            <span style="color:#ef4444;">●</span> ออฟไลน์
          </span>
        </div>
        <div class="side-card-body" id="printer_list">
          <div style="padding:16px;">
            <div class="shimmer"></div><div class="shimmer" style="width:60%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
Chart.defaults.font.family = "'Sarabun', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#94a3b8';

let chartDaily = null, chartTxn = null;

async function loadKPI() {
  try {
    const res = await fetch('../../config/controller.php?action=get_dashboard_stats').then(r => r.json());
    if (!res.success) return;
    const d = res.data;
    const elS = document.getElementById('kpi_students');
    const elP = document.getElementById('kpi_points');
    const elT = document.getElementById('kpi_today');
    animateCount(elS, Number(d.total_students));
    animateCount(elP, Number(d.total_points));
    animateCount(elT, Number(d.today_prints));
    document.getElementById('kpi_avg').textContent      = `เฉลี่ย ${d.avg_points} พอยต์/คน`;
    document.getElementById('kpi_printers').textContent = `${d.ready_printers}/${d.total_printers}`;
  } catch(e) {}
}

async function loadChartDaily() {
  try {
    const res = await fetch('../../config/controller.php?action=get_point_usage_chart').then(r => r.json());
    if (!res.success) return;
    const dayMap = {};
    (res.data || []).forEach(r => { dayMap[r.date] = Number(r.points_used); });
    const labels = [], values = [];
    for (let i = 6; i >= 0; i--) {
      const d = new Date(); d.setDate(d.getDate() - i);
      const key = d.toISOString().split('T')[0];
      labels.push(d.toLocaleDateString('th-TH', { weekday: 'short', month: 'short', day: 'numeric' }));
      values.push(dayMap[key] || 0);
    }
    const ctx = document.getElementById('chartDaily').getContext('2d');
    if (chartDaily) chartDaily.destroy();
    const grad = ctx.createLinearGradient(0, 0, 0, 200);
    grad.addColorStop(0, 'rgba(59,130,246,.85)');
    grad.addColorStop(1, 'rgba(59,130,246,.3)');
    chartDaily = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'พอยต์ที่ใช้',
          data: values,
          backgroundColor: values.map((v, i) => i === values.length-1 ? grad : 'rgba(59,130,246,.22)'),
          hoverBackgroundColor: 'rgba(59,130,246,.75)',
          borderRadius: 10,
          borderSkipped: false,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor:'#0f172a', padding:10, cornerRadius:10, callbacks:{ label: ctx => ` ${ctx.parsed.y.toLocaleString()} พอยต์` } }
        },
        scales: {
          x: { grid:{display:false}, border:{display:false} },
          y: { grid:{color:'rgba(0,0,0,.04)',drawTicks:false}, border:{display:false}, ticks:{callback:v=>v.toLocaleString()} }
        }
      }
    });
  } catch(e) { console.error(e); }
}

async function loadChartTxn() {
  try {
    const res = await fetch('../../config/controller.php?action=get_transaction_chart').then(r => r.json());
    if (!res.success) return;
    const addMap = {}, wdMap = {};
    (res.data || []).forEach(r => { addMap[r.month] = Number(r.added)||0; wdMap[r.month] = Number(r.withdrawn)||0; });
    const labels = [], addVals = [], wdVals = [];
    for (let i = 29; i >= 0; i--) {
      const d = new Date(); d.setDate(d.getDate() - i);
      const key = d.toISOString().split('T')[0];
      labels.push(i % 5 === 0 ? d.toLocaleDateString('th-TH', { month:'short', day:'numeric' }) : '');
      addVals.push(addMap[key] || 0);
      wdVals.push(wdMap[key]  || 0);
    }
    const ctx = document.getElementById('chartTxn').getContext('2d');
    if (chartTxn) chartTxn.destroy();
    chartTxn = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label:'เติมพอยต์', data:addVals, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.08)', fill:true, tension:.4, pointRadius:2, pointHoverRadius:6, borderWidth:2.5, pointBackgroundColor:'#10b981' },
          { label:'ตัดพอยต์', data:wdVals, borderColor:'#f97316', backgroundColor:'rgba(249,115,22,.06)', fill:true, tension:.4, pointRadius:2, pointHoverRadius:6, borderWidth:2.5, pointBackgroundColor:'#f97316' }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode:'index', intersect:false },
        plugins: {
          legend: { position:'top', labels:{ boxWidth:10, padding:16, font:{size:11}, usePointStyle:true } },
          tooltip: { backgroundColor:'#0f172a', padding:10, cornerRadius:10, callbacks:{ label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()} พอยต์` } }
        },
        scales: {
          x: { grid:{display:false}, border:{display:false} },
          y: { grid:{color:'rgba(0,0,0,.04)',drawTicks:false}, border:{display:false}, ticks:{callback:v=>v.toLocaleString()} }
        }
      }
    });
  } catch(e) { console.error(e); }
}

async function loadActivity() {
  try {
    const res = await fetch('../../config/controller.php?action=get_today_activities&limit=15').then(r => r.json());
    const el = document.getElementById('act_list');
    if (!res.success || !res.data.length) {
      el.innerHTML = '<div style="padding:24px;text-align:center;color:#cbd5e1;font-size:13px;">ยังไม่มีกิจกรรมวันนี้</div>';
      return;
    }
    const typeMap = {
      print:          { cls:'dot-print',  emoji:'🖨️', label:'พิมพ์งาน',  neg:true  },
      add_points:     { cls:'dot-add',    emoji:'💚', label:'เติมพอยต์', neg:false },
      withdraw_points:{ cls:'dot-deduct', emoji:'🔸', label:'ตัดพอยต์',  neg:true  },
      refund_money:   { cls:'dot-refund', emoji:'🔄', label:'ถอนคืน',    neg:true  },
    };
    el.innerHTML = res.data.map((a, idx) => {
      const t   = typeMap[a.activity_type] || { cls:'dot-add', emoji:'•', label:a.activity_type, neg:false };
      const pts = Math.abs(parseInt(a.points) || 0);
      const time = new Date(a.created_at).toLocaleTimeString('th-TH', { hour:'2-digit', minute:'2-digit' });
      return `<div class="act-item" style="animation-delay:${.05 * idx}s">
        <div class="act-dot-wrap ${t.cls}">${t.emoji}</div>
        <div style="flex:1;min-width:0;">
          <div class="act-name">${a.student_name || '-'}</div>
          <div class="act-desc">${t.label} · ${time}</div>
        </div>
        <div class="act-pts ${t.neg?'pts-neg':'pts-pos'}">${t.neg?'-':'+'}${pts}</div>
      </div>`;
    }).join('');
  } catch(e) {}
}

async function loadPrinters() {
  try {
    const res = await fetch('../../config/controller.php?action=get_printer_status').then(r => r.json());
    const el  = document.getElementById('printer_list');
    if (!res.success || !res.data.length) {
      el.innerHTML = '<div style="padding:16px;text-align:center;color:#cbd5e1;font-size:12px;">ไม่พบเครื่องพิมพ์</div>';
      return;
    }
    el.innerHTML = res.data.map((p, idx) => {
      const isReady = p.status==='ready', isLow = p.status==='low_paper';
      const wrapCls = isReady?'p-ready-wrap':isLow?'p-low-wrap':'p-offline-wrap';
      const paperCls = isReady?'p-paper-ok':isLow?'p-paper-low':'p-paper-offline';
      const emoji = isReady?'🟢':isLow?'🟠':'🔴';
      return `<div class="printer-item" style="animation-delay:${.06 * idx}s">
        <div class="p-dot-wrap ${wrapCls}">${emoji}</div>
        <div style="flex:1;min-width:0;">
          <div class="p-name">${p.printer_name}</div>
          <div class="p-loc">${p.location}</div>
        </div>
        <div class="p-paper-pill ${paperCls}">${p.paper_current}/${p.paper_max}</div>
      </div>`;
    }).join('');
  } catch(e) {}
}

/* ── Animated number counter ── */
function animateCount(el, target, duration = 900) {
  if (isNaN(target)) return;
  const startTime = performance.now();
  const isRatio = String(el.id).includes('printers');
  function step(now) {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3);
    const current = Math.floor(target * ease);
    if (!isRatio) el.textContent = current.toLocaleString();
    if (progress < 1) requestAnimationFrame(step);
    else if (!isRatio) el.textContent = Number(target).toLocaleString();
  }
  requestAnimationFrame(step);
}

document.addEventListener('DOMContentLoaded', () => {
  loadKPI(); loadChartDaily(); loadChartTxn(); loadActivity(); loadPrinters();
});
setInterval(() => { loadKPI(); loadActivity(); loadPrinters(); }, 60000);
setInterval(() => { loadChartDaily(); loadChartTxn(); }, 300000);
</script>

<?php require_once '../../static/footer.php'; ?>
