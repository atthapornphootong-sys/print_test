<?php
require_once '../../config/auth.php';        // checkLogin() อัตโนมัติ
require_once '../../config/database.php';

$backup_dir = realpath(__DIR__ . '/../../database');
$message = ''; $msg_type = '';

function safe_backup_name($name) {
    // อนุญาตเฉพาะไฟล์ backup_*.sql ป้องกัน path traversal
    $base = basename($name);
    return preg_match('/^backup_[0-9\-_]+\.sql$/', $base) ? $base : null;
}

// ---- สร้าง Backup (PHP-native dump) ----
function create_backup($dir) {
    $db = getDB();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $out  = "-- Backup ระบบจัดการพอยต์พิมพ์\n";
    $out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $out .= "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";
    foreach ($tables as $t) {
        // ข้าม View (จะ dump แยกแบบง่าย: ใช้ CREATE TABLE ปกติถ้าเป็น base table)
        $create = $db->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_ASSOC);
        $ddl = $create['Create Table'] ?? ($create['Create View'] ?? '');
        $out .= "DROP TABLE IF EXISTS `$t`;\n";
        if (isset($create['Create View'])) { $out .= "-- (view)\n"; }
        $out .= $ddl . ";\n\n";
        $rows = $db->query("SELECT * FROM `$t`");
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            $cols = array_map(fn($c) => "`$c`", array_keys($row));
            $vals = array_map(function($v) use ($db) {
                return is_null($v) ? "NULL" : $db->quote($v);
            }, array_values($row));
            $out .= "INSERT INTO `$t` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        $out .= "\n";
    }
    $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $fname = 'backup_' . date('Y-m-d_His') . '.sql';
    file_put_contents($dir . '/' . $fname, $out);
    return $fname;
}

// ---- Routing ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do'])) {
    if ($_POST['do'] === 'create') {
        try { $f = create_backup($backup_dir); $message = "สร้างไฟล์สำรองสำเร็จ: $f"; $msg_type = 'success'; }
        catch (Exception $e) { $message = "ผิดพลาด: " . $e->getMessage(); $msg_type = 'error'; }
    } elseif ($_POST['do'] === 'delete') {
        $f = safe_backup_name($_POST['file'] ?? '');
        if ($f && file_exists("$backup_dir/$f")) { unlink("$backup_dir/$f"); $message = "ลบไฟล์ $f แล้ว"; $msg_type = 'success'; }
        else { $message = "ไม่พบไฟล์"; $msg_type = 'error'; }
    }
}

// ---- Download (ผ่าน PHP เท่านั้น เพราะ database/ ถูกบล็อกจากเว็บ) ----
if (isset($_GET['download'])) {
    $f = safe_backup_name($_GET['download']);
    $path = $f ? "$backup_dir/$f" : '';
    if ($f && is_file($path)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $f . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    } else { http_response_code(404); exit('ไม่พบไฟล์'); }
}

// ---- รายการไฟล์ backup ----
$files = [];
foreach (glob($backup_dir . '/backup_*.sql') as $p) {
    $files[] = ['name' => basename($p), 'size' => filesize($p), 'time' => filemtime($p)];
}
usort($files, fn($a, $b) => $b['time'] - $a['time']);
function human_size($b){ $u=['B','KB','MB','GB']; $i=0; while($b>=1024&&$i<3){$b/=1024;$i++;} return round($b,1).' '.$u[$i]; }

$page_title = 'สำรองข้อมูล';
require_once '../../static/header.php';
?>
<div class="card">
  <div class="card-header"><h2>💾 สำรองฐานข้อมูล</h2></div>
  <div class="card-body">
    <?php if ($message): ?>
      <div class="alert alert-<?= $msg_type==='success'?'success':'error' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <p class="text-muted" style="margin-bottom:14px;">สร้างไฟล์สำรองทั้งฐานข้อมูล เก็บไว้ในโฟลเดอร์ <code>database/</code> บนเซิร์ฟเวอร์ (เข้าถึงผ่านหน้านี้เท่านั้น)</p>
    <form method="POST" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='กำลังสำรอง...';">
      <input type="hidden" name="do" value="create">
      <button type="submit" class="btn btn-primary btn-lg">＋ สร้างไฟล์สำรองใหม่</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header"><h2>📁 ไฟล์สำรองที่มีอยู่ (<?= count($files) ?>)</h2></div>
  <div class="table-container">
    <table>
      <thead><tr><th>ชื่อไฟล์</th><th>ขนาด</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
      <tbody>
        <?php if (!$files): ?>
          <tr><td colspan="4" class="text-muted" style="text-align:center;padding:24px;">ยังไม่มีไฟล์สำรอง</td></tr>
        <?php else: foreach ($files as $f): ?>
          <tr>
            <td><?= htmlspecialchars($f['name']) ?></td>
            <td><?= human_size($f['size']) ?></td>
            <td><?= date('d/m/Y H:i:s', $f['time']) ?></td>
            <td style="white-space:nowrap;">
              <a class="btn btn-sm btn-success" href="?download=<?= urlencode($f['name']) ?>">⬇ ดาวน์โหลด</a>
              <form method="POST" style="display:inline" onsubmit="return confirm('ลบไฟล์นี้?');">
                <input type="hidden" name="do" value="delete">
                <input type="hidden" name="file" value="<?= htmlspecialchars($f['name']) ?>">
                <button class="btn btn-sm btn-danger" type="submit">🗑 ลบ</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once '../../static/footer.php'; ?>
<!-- padding guard against tail truncation -->
<!-- pad pad pad pad pad pad pad pad pad pad pad pad -->
