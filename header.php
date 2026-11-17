<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? $page_title : 'Admin' ?> — ระบบพอยต์พิมพ์ วิทยาศาสตร์และเทคโนโลยี</title>
  <link rel="stylesheet" href="../../static/css/style.css?v=4">
  <link rel="stylesheet" href="../../static/css/responsive.css?v=4">
</head>
<body>
<div class="admin-container">
  <?php require_once '../../static/sidebar.php'; ?>
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
  <div class="main-content">
    <div class="header">
      <div class="header-left">
        <button type="button" class="menu-toggle" id="menuToggle" onclick="toggleSidebar()" aria-label="menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
        <div>
          <div class="header-breadcrumb">วิทยาศาสตร์และเทคโนโลยี › ระบบจัดการพอยต์พิมพ์</div>
          <h1><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
        </div>
      </div>
      <div class="header-right">
        <div class="admin-info">
          <div class="admin-avatar"><?= isset($_SESSION['admin_username']) ? strtoupper(substr($_SESSION['admin_username'],0,1)) : 'A' ?></div>
          <div>
            <strong><?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin' ?></strong>
            <small>ผู้ดูแลระบบ</small>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
<script>
window.toggleSidebar = function() {
  var sb = document.querySelector('.sidebar');
  var ov = document.getElementById('sidebarOverlay');
  var bt = document.getElementById('menuToggle');
  if (!sb) return;
  var open = sb.classList.toggle('open');
  if (ov) ov.classList.toggle('show', open);
  if (bt) bt.setAttribute('aria-expanded', open ? 'true' : 'false');
  document.body.classList.toggle('sidebar-locked', open);
};
window.closeSidebar = function() {
  var sb = document.querySelector('.sidebar');
  var ov = document.getElementById('sidebarOverlay');
  var bt = document.getElementById('menuToggle');
  if (sb) sb.classList.remove('open');
  if (ov) ov.classList.remove('show');
  if (bt) bt.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('sidebar-locked');
};
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.sidebar-menu a, .sidebar-footer a').forEach(function(a) {
    a.addEventListener('click', function() {
      if (window.innerWidth <= 960) window.closeSidebar();
    });
  });
});
window.animateCount = function(el, target, duration) {
  duration = duration || 800;
  if (!el || isNaN(target)) return;
  var startTime = null;
  function step(ts) {
    if (!startTime) startTime = ts;
    var progress = Math.min((ts - startTime) / duration, 1);
    var ease = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.floor(target * ease).toLocaleString();
    if (progress < 1) requestAnimationFrame(step);
    else el.textContent = Number(target).toLocaleString();
  }
  requestAnimationFrame(step);
};
window.reAnimateRows = function(tbodyId) {
  document.querySelectorAll('#' + tbodyId + ' tr').forEach(function(row, i) {
    row.style.animation = 'none';
    void row.offsetHeight;
    row.style.cssText += ';animation:gFadeUp .35s ' + (i*0.045) + 's ease both';
  });
};
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.stat-value').forEach(function(el) {
    var obs = new MutationObserver(function() {
      var n = parseInt(String(el.textContent).replace(/,/g,''));
      if (!isNaN(n) && n > 0) { animateCount(el, n, 700); obs.disconnect(); }
    });
    obs.observe(el, { childList:true, characterData:true, subtree:true });
  });
});
</script>
