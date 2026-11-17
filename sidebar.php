<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar" id="appSidebar">
  <button type="button" class="sidebar-close" onclick="closeSidebar()" aria-label="ปิดเมนู">&times;</button>
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <img src="../../static/img/logo.png" alt="Logo" style="width:100%; height:100%; object-fit:contain;">
    </div>
    <h2 style="margin-top:10px; font-size:18px;">ระบบจัดการพอยต์พิมพ์</h2>
    <div class="sidebar-university">วิทยาศาสตร์และเทคโนโลยี</div>
  </div>
  <div class="sidebar-section-label">เมนูหลัก</div>
  <ul class="sidebar-menu">
    <li><a href="dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>"><span class="icon">📊</span><span>Dashboard</span></a></li>
    <li><a href="manage_point.php" class="<?= $current_page=='manage_point.php'?'active':'' ?>"><span class="icon">💰</span><span>จัดการพอยต์</span></a></li>
    <li><a href="users.php" class="<?= $current_page=='users.php'?'active':'' ?>"><span class="icon">👥</span><span>นักศึกษา</span></a></li>
    <li><a href="user_detail.php" class="<?= $current_page=='user_detail.php'?'active':'' ?>"><span class="icon">👤</span><span>รายละเอียดผู้ใช้</span></a></li>
    <li><a href="report.php" class="<?= $current_page=='report.php'?'active':'' ?>"><span class="icon">📈</span><span>รายงาน</span></a></li>
    <li><a href="backup.php" class="<?= $current_page=='backup.php'?'active':'' ?>"><span class="icon">💾</span><span>สำรองข้อมูล</span></a></li>
    <li><a href="settings.php" class="<?= $current_page=='settings.php'?'active':'' ?>"><span class="icon">⚙️</span><span>ตั้งค่าระบบ</span></a></li>
  </ul>
  <div class="sidebar-footer">
    <a href="../../logout.php"><span class="icon" style="font-size:15px;">🚪</span><span>ออกจากระบบ</span></a>
  </div>
</div>
<!-- padding to guard against tail truncation -->
<!-- pad pad pad pad pad pad pad pad pad pad pad -->
