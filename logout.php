<?php
require_once '../../config/auth.php';
$page_title = "ออกจากระบบ";
require_once '../../static/header.php';
?>

<!-- Logout Modal — shows automatically -->
<style>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,21,64,.55);
  backdrop-filter: blur(4px); z-index: 1000;
  display: flex; align-items: center; justify-content: center;
  animation: fadeOverlay .25s ease;
}
@keyframes fadeOverlay { from{opacity:0} to{opacity:1} }

.modal-box {
  background: white; border-radius: 18px;
  width: 380px; max-width: calc(100vw - 40px);
  overflow: hidden; box-shadow: 0 24px 60px rgba(0,20,60,.28);
  animation: slideUp .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes slideUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }

.modal-header {
  background: linear-gradient(135deg, #001540 0%, #002366 100%);
  padding: 28px 28px 24px; text-align: center; position: relative;
}
.modal-header::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, #b8943a, #d4ae5e, #b8943a);
}
.logout-icon {
  width: 64px; height: 64px; border-radius: 50%;
  background: rgba(255,255,255,.08); border: 2px solid rgba(255,255,255,.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; margin: 0 auto 14px;
}
.modal-header h2 {
  font-family: 'Prompt', sans-serif; font-size: 20px; font-weight: 700;
  color: white; margin-bottom: 6px;
}
.modal-header p { font-size: 13px; color: rgba(255,255,255,.5); }

.modal-body { padding: 24px 28px 28px; }

.user-chip {
  display: flex; align-items: center; gap: 12px;
  background: #f0f2f8; border: 1px solid #e2e6f0; border-radius: 12px;
  padding: 13px 16px; margin-bottom: 22px;
}
.user-avatar {
  width: 40px; height: 40px; border-radius: 10px;
  background: #002366; display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 16px; flex-shrink: 0;
}
.user-name  { font-weight: 700; font-size: 15px; color: #18243e; }
.user-role  { font-size: 12px; color: #8896b4; }

.modal-question {
  text-align: center; font-size: 15px; color: #445070;
  margin-bottom: 22px; line-height: 1.6;
}

.modal-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.btn-cancel {
  padding: 13px; border: 1.5px solid #e2e6f0; background: white;
  border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600;
  font-family: 'Sarabun', sans-serif; color: #445070;
  transition: all .18s;
}
.btn-cancel:hover { background: #f0f2f8; border-color: #c5cde0; }

.btn-logout {
  padding: 13px; border: none;
  background: linear-gradient(135deg, #b43030, #c94040);
  border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 700;
  font-family: 'Prompt', sans-serif; color: white;
  box-shadow: 0 4px 14px rgba(180,48,48,.32);
  transition: all .18s;
}
.btn-logout:hover { background: linear-gradient(135deg, #922525, #b43030); transform: translateY(-1px); }
</style>

<div class="modal-overlay" id="logoutModal">
  <div class="modal-box">
    <div class="modal-header">
      <div class="logout-icon">🚪</div>
      <h2>ออกจากระบบ</h2>
      <p>คุณต้องการออกจากระบบหรือไม่?</p>
    </div>
    <div class="modal-body">
      <div class="user-chip">
        <div class="user-avatar"><?= mb_substr($_SESSION['admin_name'] ?? 'A', 0, 1) ?></div>
        <div>
          <div class="user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'แอดมิน') ?></div>
          <div class="user-role">ผู้ดูแลระบบ · <?= htmlspecialchars($_SESSION['admin_role'] ?? 'admin') ?></div>
        </div>
      </div>
      <div class="modal-question">
        หากออกจากระบบ คุณจะต้องเข้าสู่ระบบใหม่<br>เพื่อใช้งานฟีเจอร์ทั้งหมด
      </div>
      <div class="modal-actions">
        <button class="btn-cancel" onclick="cancelLogout()">✕ ยกเลิก</button>
        <button class="btn-logout" onclick="confirmLogout()">🚪 ออกจากระบบ</button>
      </div>
    </div>
  </div>
</div>

<script>
function cancelLogout() {
    document.getElementById('logoutModal').style.animation = 'fadeOut .2s ease forwards';
    setTimeout(() => { history.back(); }, 200);
}

function confirmLogout() {
    window.location.href = '../../logout.php';
}

// Close on overlay click
document.getElementById('logoutModal').addEventListener('click', function(e) {
    if (e.target === this) cancelLogout();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cancelLogout();
});
</script>

<style>
@keyframes fadeOut { from{opacity:1} to{opacity:0} }
</style>

<?php require_once '../../static/footer.php'; ?>
