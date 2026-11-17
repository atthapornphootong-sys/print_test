<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: web-app/admin/dashboard.php"); exit;
}
if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header("Location: web-app/student/portal.php"); exit;
}

require_once 'config/database.php';
$error_message = '';
$active_tab = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_user = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (empty($login_user) || empty($password)) {
        $error_message = 'กรุณากรอกข้อมูลให้ครบ';
    } else {
        $db = getDB();
        
        // 1. ตรวจสอบว่าเป็น Admin หรือไม่
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $login_user]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['admin_id'];
            $_SESSION['admin_username']  = $admin['username'];
            $_SESSION['admin_name']      = $admin['full_name'];
            $_SESSION['admin_role']      = $admin['role'];
            header("Location: web-app/admin/dashboard.php"); exit;
        } else {
            // 2. ถ้าไม่ใช่ Admin ตรวจสอบว่าเป็นนักศึกษาหรือไม่
            $stmt = $db->prepare("SELECT * FROM students WHERE student_id = :id LIMIT 1");
            $stmt->execute([':id' => $login_user]);
            $student = $stmt->fetch();
            
            // รหัสผ่านของนักศึกษาคือรหัสนักศึกษาเอง
            if ($student && $password === $student['student_id']) {
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_id']        = $student['student_id'];
                $_SESSION['student_name']      = $student['student_name'];
                header("Location: web-app/student/portal.php"); exit;
            } else {
                $error_message = 'ชื่อผู้ใช้งาน หรือ รหัสผ่าน ไม่ถูกต้อง';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ — ระบบพอยต์พิมพ์ มหาวิทยาลัยธนบุรี</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Prompt:wght@500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --navy: #002366; --navy-deep: #001540; --navy-soft: #0a3080;
  --gold: #b8943a; --gold-light: #d4ae5e;
  --border: #e2e6f0; --text-1: #18243e; --text-3: #8896b4; --bg: #f0f2f8;
}
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
body {
  font-family: 'Sarabun', sans-serif;
  min-height: 100vh; display: flex; background: var(--bg);
  overflow: hidden;
}

/* ===== BRAND PANEL ===== */
.login-brand {
  flex: 1.1;
  background: var(--navy-deep);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 60px 52px; position: relative; overflow: hidden;
}
/* Decorative circles */
.brand-ring {
  position: absolute; border-radius: 50%;
  border: 1px solid rgba(184,148,58,.08); pointer-events: none;
}
.brand-ring.r1 { width: 500px; height: 500px; bottom: -150px; left: -150px; }
.brand-ring.r2 { width: 300px; height: 300px; top: -80px; right: -80px; border-color: rgba(255,255,255,.03); }
.brand-ring.r3 { width: 200px; height: 200px; bottom: 60px; right: 40px; border-color: rgba(184,148,58,.05); }

/* Gold top bar */
.login-brand::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
}

.brand-emblem {
  width: 110px; height: 110px; border-radius: 50%;
  background: white;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 28px;
  box-shadow: 0 8px 28px rgba(0,0,0,.15), 0 0 0 12px rgba(255,255,255,.1);
  position: relative; z-index: 1;
  animation: pulse-ring 3s infinite;
  padding: 10px;
}
.brand-emblem img { width: 100%; height: 100%; object-fit: contain; }
@keyframes pulse-ring {
  0%,100% { box-shadow: 0 8px 28px rgba(184,148,58,.35), 0 0 0 12px rgba(184,148,58,.08); }
  50%      { box-shadow: 0 8px 28px rgba(184,148,58,.45), 0 0 0 18px rgba(184,148,58,.04); }
}

.brand-title {
  font-family: 'Prompt', sans-serif; font-size: 28px; font-weight: 700;
  color: #fff; text-align: center; margin-bottom: 6px; line-height: 1.35;
  position: relative; z-index: 1;
}
.brand-sub   { font-size: 13px; color: rgba(255,255,255,.45); text-align: center; margin-bottom: 4px; position: relative; z-index: 1; }
.brand-univ  { font-size: 32px; color: var(--gold-light); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; text-align: center; position: relative; z-index: 1; }
.brand-divider { width: 40px; height: 2px; background: linear-gradient(90deg, var(--gold), transparent); margin: 22px auto; position: relative; z-index: 1; }

.brand-features { list-style: none; position: relative; z-index: 1; width: 100%; max-width: 260px; }
.brand-features li {
  display: flex; align-items: center; gap: 12px;
  color: rgba(255,255,255,.55); font-size: 13.5px;
  padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.05);
}
.brand-features li:last-child { border-bottom: none; }
.feat-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--gold); flex-shrink: 0;
}

/* ===== FORM PANEL ===== */
.login-form-panel {
  width: 460px; background: white;
  display: flex; flex-direction: column; justify-content: center;
  padding: 52px 46px; position: relative;
  box-shadow: -4px 0 40px rgba(0,0,0,.08);
}
.login-form-panel::before {
  content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 3px;
  background: linear-gradient(180deg, var(--gold), var(--navy), var(--gold));
}

.form-title    { font-family: 'Prompt', sans-serif; font-size: 24px; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
.form-subtitle { font-size: 13px; color: var(--text-3); margin-bottom: 26px; }

/* Tab Toggle */
.tab-toggle {
  display: flex; border: 1.5px solid var(--border);
  border-radius: 10px; overflow: hidden; margin-bottom: 26px;
}
.tab-btn {
  flex: 1; padding: 11px; border: none; background: white;
  cursor: pointer; font-size: 13.5px; font-family: 'Sarabun', sans-serif;
  font-weight: 600; color: var(--text-3); transition: all .2s;
}
.tab-btn:first-child { border-right: 1px solid var(--border); }
.tab-btn.active { background: var(--navy); color: white; }

/* Alert */
.alert-box {
  padding: 11px 14px; border-radius: 8px; background: #fbeaea;
  border: 1px solid #edaead; color: #7e1c1c; font-size: 13.5px;
  display: flex; align-items: center; gap: 9px; margin-bottom: 18px; font-weight: 500;
  animation: slideIn .25s ease;
}
@keyframes slideIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }

/* Form */
.form-group        { margin-bottom: 18px; }
.form-group label  { display: block; margin-bottom: 7px; font-weight: 700; color: var(--text-1); font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.form-group input  {
  width: 100%; padding: 12px 14px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 15px; font-family: 'Sarabun', sans-serif;
  color: var(--text-1); background: var(--bg); transition: border-color .18s, box-shadow .18s, background .18s;
}
.form-group input:focus {
  outline: none; border-color: var(--navy); background: #fff;
  box-shadow: 0 0 0 3px rgba(0,35,102,.08);
}

.btn-login {
  width: 100%; padding: 14px; background: var(--navy); color: #fff; border: none;
  border-radius: 8px; font-size: 15px; font-weight: 700; font-family: 'Prompt', sans-serif;
  cursor: pointer; transition: all .2s; box-shadow: 0 4px 14px rgba(0,35,102,.25); margin-top: 4px;
}
.btn-login:hover { background: var(--navy-soft); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,35,102,.35); }
.btn-login.student-btn { background: linear-gradient(135deg, #14764e, #1a9e68); box-shadow: 0 4px 14px rgba(20,118,78,.3); }
.btn-login.student-btn:hover { background: linear-gradient(135deg, #0f5c3b, #14764e); }


.copyright { margin-top: 16px; font-size: 11px; color: var(--text-3); text-align: center; }

/* ═══ LOGIN PAGE ANIMATIONS ═══ */
@keyframes brandSlideIn {
  from { opacity:0; transform:translateX(-40px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes formSlideIn {
  from { opacity:0; transform:translateX(40px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes emblemPop {
  0%  { opacity:0; transform:scale(.5) rotate(-20deg); }
  65% { transform:scale(1.12) rotate(4deg); }
  100%{ opacity:1; transform:scale(1) rotate(0); }
}
@keyframes featureSlide {
  from { opacity:0; transform:translateX(-12px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes ringExpand {
  from { opacity:0; transform:scale(.8); }
  to   { opacity:1; transform:scale(1); }
}
@keyframes inputShake {
  0%,100%{ transform:translateX(0); }
  20%    { transform:translateX(-6px); }
  40%    { transform:translateX(6px); }
  60%    { transform:translateX(-4px); }
  80%    { transform:translateX(4px); }
}

/* Brand panel */
.login-brand { animation: brandSlideIn .55s cubic-bezier(.34,1.3,.64,1) both; }

/* Form panel */
.login-form-panel { animation: formSlideIn .55s .08s cubic-bezier(.34,1.3,.64,1) both; }

/* Emblem pop */
.brand-emblem { animation: emblemPop .7s .3s cubic-bezier(.34,1.56,.64,1) both; }

/* Brand title & sub stagger */
.brand-title  { animation: featureSlide .45s .55s ease both; }
.brand-sub    { animation: featureSlide .45s .62s ease both; }
.brand-univ   { animation: featureSlide .45s .69s ease both; }
.brand-divider{ animation: featureSlide .4s .74s ease both; }

/* Feature items stagger */
.brand-features li:nth-child(1) { animation: featureSlide .4s .80s ease both; }
.brand-features li:nth-child(2) { animation: featureSlide .4s .88s ease both; }
.brand-features li:nth-child(3) { animation: featureSlide .4s .96s ease both; }
.brand-features li:nth-child(4) { animation: featureSlide .4s 1.04s ease both; }

/* Decorative rings */
.brand-ring.r1 { animation: ringExpand .8s .1s ease both; }
.brand-ring.r2 { animation: ringExpand .8s .2s ease both; }
.brand-ring.r3 { animation: ringExpand .8s .3s ease both; }

/* Form elements stagger */
.form-title    { animation: featureSlide .4s .25s ease both; }
.form-subtitle { animation: featureSlide .4s .32s ease both; }
.tab-toggle    { animation: featureSlide .4s .38s ease both; }
.form-group:nth-child(1) { animation: featureSlide .4s .44s ease both; }
.form-group:nth-child(2) { animation: featureSlide .4s .50s ease both; }
.btn-login     { animation: featureSlide .4s .56s ease both; }
.copyright     { animation: featureSlide .4s .65s ease both; }

/* Tab button spring */
.tab-btn { transition: all .25s cubic-bezier(.34,1.56,.64,1) !important; }

/* Login button spring */
.btn-login { transition: all .25s cubic-bezier(.34,1.56,.64,1) !important; }
.btn-login:hover { transform: translateY(-3px) scale(1.02) !important; }
.btn-login:active { transform: scale(.97) !important; }

/* Shake on error */
.alert-box.shake { animation: inputShake .4s ease; }

@media(max-width:760px){
  body { flex-direction: column; overflow: auto; }
  .login-brand { padding: 36px 28px 28px; flex: none; animation: none; }
  .brand-features, .brand-divider { display: none; }
  .login-form-panel { width: 100%; padding: 36px 28px; animation: none; }
}
</style>
</head>
<body>

<!-- Brand Panel -->
<div class="login-brand">
  <div class="brand-ring r1"></div>
  <div class="brand-ring r2"></div>
  <div class="brand-ring r3"></div>
  <div class="brand-emblem">
    <img src="static/img/logo.png" alt="University Logo">
  </div>
  <div class="brand-title">ระบบจัดการพอยต์พิมพ์</div>
  <div class="brand-sub">Print Point Management System</div>
  <div class="brand-univ">วิทยาศาสตร์และเทคโนโลยี</div>
  <div class="brand-divider"></div>
  <ul class="brand-features">
    <li><span class="feat-dot"></span> นักศึกษา — ตรวจสอบพอยต์ตัวเอง</li>
    <li><span class="feat-dot"></span> แอดมิน — จัดการระบบพิมพ์ทั้งหมด</li>
    <li><span class="feat-dot"></span> เชื่อมต่อโปรแกรมตัดพอยต์อัตโนมัติ</li>
    <li><span class="feat-dot"></span> ดูประวัติและสถิติ Real-time</li>
  </ul>
</div>

<!-- Form Panel -->
<div class="login-form-panel">
  <h2 class="form-title">เข้าสู่ระบบ</h2>


  <?php if (!empty($error_message)): ?>
    <div class="alert-box shake" id="alertBox">⚠️ <?= htmlspecialchars($error_message) ?></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var el = document.getElementById('alertBox');
      if (el) { el.classList.remove('shake'); void el.offsetHeight; el.classList.add('shake'); }
    });
    </script>
  <?php endif; ?>

  <!-- Unified Form -->
  <form method="POST" action="" id="form-login">
    <div class="form-group">
      <label>ชื่อผู้ใช้งาน / รหัสนักศึกษา</label>
      <input type="text" name="username" placeholder="กรอกชื่อผู้ใช้งาน หรือ รหัสนักศึกษา"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
             autocomplete="username" required autofocus>
    </div>
    <div class="form-group">
      <label>รหัสผ่าน</label>
      <input type="password" name="password" placeholder="กรอกรหัสผ่าน" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
  </form>

  <p class="copyright">&copy; <?= date('Y') ?> วิทยาศาสตร์และเทคโนโลยี — Print Point System</p>
</div>

</body>
</html>
