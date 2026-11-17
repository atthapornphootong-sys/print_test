<?php
/**
 * =====================================================
 * API Controller (Updated for Python Printer Control)
 * แก้ไข Error 2014 - Cannot execute queries while there are pending result sets
 * =====================================================
 */

require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
// อนุญาต Python client (non-browser) และ localhost
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['http://localhost', 'http://127.0.0.1'];
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header('Access-Control-Allow-Origin: *'); // Python requests ไม่มี Origin header
}
header('Vary: Origin');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $data = array_merge($_GET, $_POST);
}

$action = isset($data['action']) ? sanitizeInput($data['action']) : (isset($_GET['action']) ? sanitizeInput($_GET['action']) : '');

try {
    switch ($action) {
        case 'check_student':        checkStudent($data);          break;
        case 'record_print':         recordPrint($data);           break;
        case 'get_student':          getStudentInfo($data);        break;
        case 'get_printers':         getAllPrinters();              break;
        case 'update_printer_status':updatePrinterStatus($data);   break;
        case 'get_stats':            getSystemStats();              break;
        case 'get_dashboard_stats':  getDashboardStats();           break;
        case 'get_today_activities': getTodayActivities();          break;
        case 'get_printer_status':   getPrinterStatus();            break;
        case 'get_point_usage_chart':getPointUsageChart();          break;
        case 'get_transaction_chart':getTransactionChart();         break;
        case 'update_activity':      updateActivity($data);        break;
        case 'get_all_students':     getAllStudents();              break;
        case 'get_student_detail':   getStudentDetail();            break;
        case 'search_students':      searchStudents();              break;
        case 'add_student':          addStudent($data);            break;
        case 'import_students':      importStudents($data);        break;
        case 'parse_rtf_students':   parseRtfStudents();           break;
        case 'update_student':       updateStudent($data);         break;
        case 'delete_student':       deleteStudent($data);         break;
        case 'add_points':           addPoints($data);             break;
        case 'withdraw_points':      withdrawPoints($data);        break;
        case 'refund_money':         refundMoney($data);           break;
        case 'print_document':       printDocument($data);         break;
        case 'get_print_history':    getPrintHistory();             break;
        case 'refill_paper':         refillPaper($data);           break;
        case 'get_activities_report':getActivitiesReport();        break;
        case 'get_student_statistics':getStudentStatistics();      break;
        case 'get_settings':         getSettings();                break;
        case 'update_setting':       updateSetting($data);         break;
        case 'change_admin_password':changeAdminPassword($data);   break;
        case 'get_admin_info':       getCurrentAdminInfo();        break;
        case 'get_debt_students':    getDebtStudents();            break;
        case 'get_faculties':        getFaculties();               break;
        case 'get_majors':           getMajors();                  break;
        case 'add_faculty':          addFaculty($data);            break;
        case 'update_faculty':       updateFaculty($data);         break;
        case 'delete_faculty':       deleteFaculty($data);         break;
        case 'add_major':            addMajor($data);              break;
        case 'update_major':         updateMajor($data);           break;
        case 'delete_major':         deleteMajor($data);           break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

function checkStudent($data) {
    $student_id = sanitizeInput($data['student_id'] ?? '');
    $pages = (int)($data['pages'] ?? 0);
    if (empty($student_id)) throw new Exception('กรุณาระบุรหัสนักศึกษา');
    $stmt = getDB()->prepare("SELECT student_id, student_name, points_balance, status FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch();
    $stmt->closeCursor();
    if (!$student) { echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสนักศึกษา: ' . $student_id], JSON_UNESCAPED_UNICODE); return; }

    // ดึงค่าพอยต์ต่อหน้าจาก settings และ debt limit
    $ppp             = fetchSingle("SELECT value FROM settings WHERE `key` = 'points_per_page'");
    $debt_limit      = fetchSingle("SELECT value FROM settings WHERE `key` = 'debt_limit'");
    $allow_debt_row  = fetchSingle("SELECT value FROM settings WHERE `key` = 'allow_debt'");
    $points_needed   = $pages * (int)($ppp['value'] ?? 1);
    $points_balance  = (int)$student['points_balance'];
    $max_debt        = (int)($debt_limit['value'] ?? 0); // 0 = ไม่อนุญาตติดหนี้
    $admin_allow_debt = ($allow_debt_row['value'] ?? '0') === '1'; // Admin เปิดระบบหนี้ไว้ไหม

    if ($points_balance < $points_needed) {
        $shortage    = $points_needed - $points_balance;
        $debt_after  = $points_balance - $points_needed; // จะเป็นค่าติดลบ
        // can_use_debt = Admin เปิดระบบหนี้ AND วงเงินไม่เกิน
        $can_use_debt = ($admin_allow_debt && $max_debt > 0 && abs($debt_after) <= $max_debt);
        echo json_encode([
            'success'        => false,
            'message'        => 'พอยต์ไม่เพียงพอ',
            'student_name'   => $student['student_name'],
            'points_balance' => $points_balance,
            'points_needed'  => $points_needed,
            'points_shortage'=> $shortage,
            'can_use_debt'   => $can_use_debt,
            'debt_limit'     => $max_debt,
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    echo json_encode(['success' => true, 'message' => 'มีพอยต์เพียงพอ', 'student_id' => $student['student_id'], 'student_name' => $student['student_name'], 'points_balance' => $points_balance, 'points_needed' => $points_needed, 'points_remaining' => $points_balance - $points_needed], JSON_UNESCAPED_UNICODE);
}

function recordPrint($data) {
    $student_id  = sanitizeInput($data['student_id'] ?? '');
    $pages       = (int)($data['pages'] ?? 0);
    $printer_id  = (int)($data['printer_id'] ?? 1);
    $job_id      = (int)($data['job_id'] ?? 0);
    $allow_debt  = !empty($data['allow_debt']); // Python client ส่ง flag นี้มาเมื่อผู้ใช้ยืนยันติดหนี้
    if (empty($student_id) || $pages <= 0) throw new Exception('ข้อมูลไม่ครบถ้วน');
    $db = getDB();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT points_balance FROM students WHERE student_id = :student_id FOR UPDATE");
        $stmt->execute(['student_id' => $student_id]);
        $student = $stmt->fetch();
        $stmt->closeCursor();
        if (!$student) throw new Exception('ไม่พบรหัสนักศึกษา');

        $points_before = (int)$student['points_balance'];
        $ppp           = fetchSingle("SELECT value FROM settings WHERE `key` = 'points_per_page'");
        $debt_limit    = fetchSingle("SELECT value FROM settings WHERE `key` = 'debt_limit'");
        $points_needed = $pages * (int)($ppp['value'] ?? 1);
        $points_after  = $points_before - $points_needed;
        $max_debt      = (int)($debt_limit['value'] ?? 0);

        // ตรวจสอบ allow_debt จาก DB (ไม่เชื่อ client อย่างเดียว เพื่อความปลอดภัย)
        $allow_debt_setting = fetchSingle("SELECT value FROM settings WHERE `key` = 'allow_debt'");
        $debt_allowed_by_admin = ($allow_debt_setting['value'] ?? '0') === '1';

        // ตรวจสอบ: ถ้าจะติดลบ ต้องได้รับอนุญาตทั้งจาก Admin (DB) และ client
        if ($points_after < 0) {
            if (!$debt_allowed_by_admin) throw new Exception('พอยต์ไม่เพียงพอ (ระบบติดหนี้ถูกปิดโดย Admin)');
            if (!$allow_debt) throw new Exception('พอยต์ไม่เพียงพอ');
            if ($max_debt > 0 && abs($points_after) > $max_debt) {
                throw new Exception("ยอดติดหนี้จะเกินวงเงินสูงสุด ({$max_debt} พอยต์)");
            }
        }

        // กำหนด status: debt = ติดหนี้, low = ต่ำ, active = ปกติ
        $new_status = 'active';
        if ($points_after < 0)  $new_status = 'debt';
        elseif ($points_after < 20) $new_status = 'low';

        $stmt = $db->prepare("UPDATE students SET points_balance = :points_after, total_prints = total_prints + 1, total_pages = total_pages + :pages, total_points_spent = total_points_spent + :points_spent, last_active = NOW(), status = :status WHERE student_id = :student_id");
        $stmt->execute(['points_after' => $points_after, 'pages' => $pages, 'points_spent' => $points_needed, 'status' => $new_status, 'student_id' => $student_id]);
        $stmt->closeCursor();
        // Calculate money amount based on current exchange rate
        $ex_baht = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_baht'");
        $ex_pts  = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_points'");
        $b = (float)($ex_baht['value'] ?? 1);
        $p = (float)($ex_pts['value'] ?? 1);
        if ($p <= 0) $p = 1;
        $money_amount = round($points_needed * ($b / $p), 2);

        $stmt = $db->prepare("INSERT INTO activities (student_id, activity_type, activity_detail, pages, points, points_before, points_after, printer_id, money_amount) VALUES (:student_id, 'print', :detail, :pages, :points, :points_before, :points_after, :printer_id, :money)");
        $stmt->execute(['student_id' => $student_id, 'detail' => "พิมพ์เอกสาร {$pages} หน้า (Job ID: {$job_id})" . ($points_after < 0 ? ' [ติดหนี้]' : ''), 'pages' => $pages, 'points' => -$points_needed, 'points_before' => $points_before, 'points_after' => $points_after, 'printer_id' => $printer_id, 'money' => $money_amount]);
        $activity_id = $db->lastInsertId();
        $stmt->closeCursor();
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'บันทึกการพิมพ์สำเร็จ', 'points_used' => $points_needed, 'points_remaining' => $points_after, 'is_debt' => $points_after < 0, 'activity_id' => $activity_id], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { $db->rollBack(); throw $e; }
}

function getStudentInfo($data) {
    $student_id = sanitizeInput($data['student_id'] ?? '');
    if (empty($student_id)) throw new Exception('กรุณาระบุรหัสนักศึกษา');
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM students WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $student_id]);
    $student = $stmt->fetch();
    $stmt->closeCursor();
    if (!$student) { echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสนักศึกษา'], JSON_UNESCAPED_UNICODE); return; }
    $stmt = $db->prepare("SELECT * FROM activities WHERE student_id = :student_id ORDER BY created_at DESC LIMIT 10");
    $stmt->execute(['student_id' => $student_id]);
    $activities = $stmt->fetchAll();
    $stmt->closeCursor();
    echo json_encode(['success' => true, 'student' => $student, 'recent_activities' => $activities], JSON_UNESCAPED_UNICODE);
}

function updatePrinterStatus($data) {
    $printer_name = sanitizeInput($data['printer_name'] ?? '');
    $paper_used   = (int)($data['paper_used'] ?? 0);
    $status       = sanitizeInput($data['status'] ?? 'ready');
    $db = getDB();
    $stmt = $db->prepare("SELECT printer_id, paper_current FROM printers WHERE printer_name = :printer_name LIMIT 1");
    $stmt->execute(['printer_name' => $printer_name]);
    $printer = $stmt->fetch();
    $stmt->closeCursor();
    $new_paper_count = 0;
    if ($printer) {
        $new_paper_count = max(0, $printer['paper_current'] - $paper_used);
        if ($new_paper_count <= 0) $status = 'offline';
        elseif ($new_paper_count <= 50) $status = 'low_paper';
        else $status = 'ready';
        $stmt = $db->prepare("UPDATE printers SET paper_current = :paper_current, status = :status, updated_at = NOW() WHERE printer_id = :printer_id");
        $stmt->execute(['paper_current' => $new_paper_count, 'status' => $status, 'printer_id' => $printer['printer_id']]);
        $stmt->closeCursor();
    }
    echo json_encode(['success' => true, 'message' => 'อัพเดทสถานะเครื่องปริ้นสำเร็จ', 'printer_id' => $printer['printer_id'] ?? null, 'paper_remaining' => $new_paper_count, 'status' => $status], JSON_UNESCAPED_UNICODE);
}

function getSystemStats() {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as total_students, SUM(points_balance) as total_points, AVG(points_balance) as avg_points FROM students"); $student_stats = $stmt->fetch(); $stmt->closeCursor();
    $stmt = $db->query("SELECT COUNT(*) as total_printers, SUM(CASE WHEN status='ready' THEN 1 ELSE 0 END) as ready_printers, SUM(CASE WHEN status='low_paper' THEN 1 ELSE 0 END) as low_paper_printers, SUM(CASE WHEN status='offline' THEN 1 ELSE 0 END) as offline_printers FROM printers"); $printer_stats = $stmt->fetch(); $stmt->closeCursor();
    $stmt = $db->query("SELECT COUNT(*) as count, SUM(pages) as total_pages FROM activities WHERE DATE(created_at) = CURDATE() AND activity_type = 'print'"); $today_stats = $stmt->fetch(); $stmt->closeCursor();
    echo json_encode(['success' => true, 'student_stats' => $student_stats, 'printer_stats' => $printer_stats, 'today_stats' => $today_stats], JSON_UNESCAPED_UNICODE);
}

function getDashboardStats() {
    try {
        $db = getDB();
        // 1. Current Balance in Points (Sum of all students)
        $stmt = $db->query("SELECT COUNT(*) as total_students, COALESCE(SUM(points_balance),0) as current_total_points FROM students"); 
        $stats = $stmt->fetch(); $stmt->closeCursor();
        
        // 2. Lifetime Used (Sum of absolute values of negative points)
        $stmt = $db->query("SELECT COALESCE(SUM(ABS(points)),0) as lifetime_used FROM activities WHERE points < 0");
        $used = $stmt->fetch(); $stmt->closeCursor();

        // 3. Lifetime Added (Sum of all positive point transactions)
        $stmt = $db->query("SELECT COALESCE(SUM(points),0) as lifetime_added FROM activities WHERE points > 0");
        $added_row = $stmt->fetch(); $stmt->closeCursor();
        $lifetime_added = $added_row['lifetime_added'];

        // Today's Stats
        $stmt = $db->query("SELECT COUNT(*) as active_today FROM (SELECT DISTINCT student_id FROM activities WHERE DATE(created_at) = CURDATE()) t"); 
        $active = $stmt->fetch(); $stmt->closeCursor();
        $stmt = $db->query("SELECT COUNT(*) as today_prints FROM activities WHERE activity_type = 'print' AND DATE(created_at) = CURDATE()"); 
        $today_prints = $stmt->fetch(); $stmt->closeCursor();

        // Exchange Rate for Baht Calculation
        $ex_baht = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_baht'");
        $ex_pts  = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_points'");
        $b = (float)($ex_baht['value'] ?? 1);
        $p = (float)($ex_pts['value'] ?? 1);
        if ($p <= 0) $p = 1;
        $current_baht = round($stats['current_total_points'] * ($b / $p), 2);

        // Printer Stats
        $stmt = $db->query("SELECT COUNT(*) as total_printers, SUM(CASE WHEN status='ready' THEN 1 ELSE 0 END) as ready_printers FROM printers"); 
        $printer_stats = $stmt->fetch(); $stmt->closeCursor();

        // Avg Points
        $avg_points = $stats['total_students'] > 0 ? round($stats['current_total_points'] / $stats['total_students'], 1) : 0;

        echo json_encode(['success' => true, 'data' => [
            'total_students'      => (int)$stats['total_students'],
            'total_points'        => (int)$stats['current_total_points'], // Aliased for compatibility
            'current_total_points'=> (int)$stats['current_total_points'],
            'current_total_baht'  => $current_baht,
            'lifetime_added'      => (int)$lifetime_added,
            'lifetime_used'       => (int)$used['lifetime_used'],
            'active_today'        => (int)$active['active_today'],
            'today_prints'        => (int)$today_prints['today_prints'],
            'ready_printers'      => (int)$printer_stats['ready_printers'],
            'total_printers'      => (int)$printer_stats['total_printers'],
            'avg_points'          => $avg_points
        ]], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getTodayActivities() {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $stmt = getDB()->prepare("SELECT a.activity_id, a.student_id, s.student_name, a.activity_type, a.activity_detail, a.points, a.created_at FROM activities a JOIN students s ON a.student_id = s.student_id WHERE a.activity_type IN ('add_points', 'withdraw_points', 'refund_money') ORDER BY a.created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll();
        $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $activities], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getPrinterStatus() {
    try { $printers = executeQuery("SELECT * FROM printers ORDER BY printer_id"); echo json_encode(['success' => true, 'data' => $printers], JSON_UNESCAPED_UNICODE); }
    catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getPointUsageChart() {
    try { $data = executeQuery("SELECT DATE(created_at) as date, SUM(ABS(points)) as points_used FROM activities WHERE activity_type = 'print' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date"); echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE); }
    catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getTransactionChart() {
    try {
        // รวม add_points เป็น added, withdraw_points+refund_money เป็น withdrawn — ตรงกับที่ frontend คาดหวัง
        $raw = executeQuery("
            SELECT
                DATE(created_at) as month,
                COALESCE(SUM(CASE WHEN activity_type = 'add_points' THEN ABS(points) ELSE 0 END), 0) as added,
                COALESCE(SUM(CASE WHEN activity_type IN ('withdraw_points','refund_money') THEN ABS(points) ELSE 0 END), 0) as withdrawn
            FROM activities
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY DATE(created_at)
            ORDER BY month ASC
        ");
        echo json_encode(['success' => true, 'data' => $raw], JSON_UNESCAPED_UNICODE);
    }
    catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function updateActivity($data) {
    try {
        $activity_id     = (int)($data['activity_id'] ?? 0);
        $activity_detail = sanitizeInput($data['activity_detail'] ?? '');
        if ($activity_id <= 0) throw new Exception('Invalid Activity ID');
        $success = executeNonQuery("UPDATE activities SET activity_detail = :detail WHERE activity_id = :id", ['detail' => $activity_detail, 'id' => $activity_id]);
        echo json_encode($success ? ['success' => true, 'message' => 'Updated successfully'] : ['success' => false, 'message' => 'Update failed'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getAllStudents() {
    try {
        $db = getDB();
        try {
            $stmt = $db->prepare("SELECT s.*, COALESCE(m.major_name,'') as major_name, COALESCE(m.major_abbr,'') as major_abbr, COALESCE(f.faculty_name,'') as faculty_name FROM students s LEFT JOIN majors m ON s.major_id = m.major_id LEFT JOIN faculties f ON m.faculty_id = f.faculty_id ORDER BY s.student_id");
            $stmt->execute(); $students = $stmt->fetchAll(); $stmt->closeCursor();
        } catch (Exception $ex) {
            $stmt = $db->prepare("SELECT * FROM students ORDER BY student_id");
            $stmt->execute(); $students = $stmt->fetchAll(); $stmt->closeCursor();
        }
        echo json_encode(['success' => true, 'data' => $students ?: []], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getStudentDetail() {
    try {
        $student_id = trim($_GET['student_id'] ?? '');
        if (empty($student_id)) throw new Exception('Missing student_id');
        $db = getDB();

        // ✅ แก้แล้ว: ใช้ major_abbr แทน major_code และลบ faculty_code ออก
        $stmt = $db->prepare("
            SELECT s.*,
                   COALESCE(m.major_name,'')   AS major_name,
                   COALESCE(m.major_abbr,'')   AS major_abbr,
                   COALESCE(f.faculty_name,'') AS faculty_name
            FROM students s
            LEFT JOIN majors    m ON s.major_id   = m.major_id
            LEFT JOIN faculties f ON m.faculty_id = f.faculty_id
            WHERE s.student_id = :id
        ");
        $stmt->execute(['id' => $student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$student) { echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสนักศึกษา: ' . $student_id], JSON_UNESCAPED_UNICODE); return; }

        $stmt = $db->prepare("SELECT COUNT(CASE WHEN activity_type = 'print' THEN 1 END) AS total_prints, COALESCE(SUM(CASE WHEN points < 0 THEN ABS(points) ELSE 0 END), 0) AS used_points, COALESCE(SUM(CASE WHEN points > 0 THEN points ELSE 0 END), 0) AS added_points, MAX(created_at) AS last_used FROM activities WHERE student_id = :id");
        $stmt->execute(['id' => $student_id]); $stats = $stmt->fetch(PDO::FETCH_ASSOC); $stmt->closeCursor();

        $stmt = $db->prepare("SELECT DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i:%s') AS date, a.activity_type AS type, a.activity_detail AS detail, a.points AS points, a.points_after AS balance, COALESCE(a.pages, 0) AS pages, COALESCE(p.printer_name,'') AS printer_name FROM activities a LEFT JOIN printers p ON a.printer_id = p.printer_id WHERE a.student_id = :id ORDER BY a.created_at DESC LIMIT 100");
        $stmt->execute(['id' => $student_id]); $activities = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

        foreach ($activities as &$act) {
            if ($act['type'] === 'add_points')      $act['type'] = 'add';
            if ($act['type'] === 'withdraw_points') $act['type'] = 'deduct';
            if ($act['type'] === 'refund_money')    $act['type'] = 'refund';
            $act['status'] = 'success';
        }
        unset($act);

        $stmt = $db->prepare("SELECT DATE(created_at) AS print_date, COALESCE(SUM(pages),0) AS total_pages FROM activities WHERE student_id = :id AND activity_type = 'print' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at)");
        $stmt->execute(['id' => $student_id]); $chartRaw = $stmt->fetchAll(PDO::FETCH_ASSOC); $stmt->closeCursor();

        $thaiMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        $chartLabels = []; $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = (int)date('j', strtotime($date)) . ' ' . $thaiMonths[(int)date('n', strtotime($date))];
            $found = 0;
            foreach ($chartRaw as $row) { if ($row['print_date'] === $date) { $found = (int)$row['total_pages']; break; } }
            $chartData[] = $found;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'student'    => [
                    'student_id'     => $student['student_id'],
                    'student_name'   => $student['student_name'],
                    'points_balance' => (int)$student['points_balance'],
                    'status'         => $student['status'],
                    'major_name'     => $student['major_name'],
                    'major_abbr'     => $student['major_abbr'],
                    'faculty_name'   => $student['faculty_name'],
                ],
                'stats'      => [
                    'total_prints' => (int)($stats['total_prints'] ?? 0),
                    'used_points'  => (int)($stats['used_points']  ?? 0),
                    'added_points' => (int)($stats['added_points'] ?? 0),
                    'last_used'    => $stats['last_used'] ? date('d/m/Y H:i', strtotime($stats['last_used'])) : '-',
                ],
                'activities' => $activities,
                'chart'      => ['labels' => $chartLabels, 'data' => $chartData],
            ]
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function searchStudents() {
    try {
        $query = sanitizeInput($_GET['q'] ?? '');
        if (empty($query)) throw new Exception('Missing search query');
        $like = "%{$query}%";
        $stmt = getDB()->prepare("SELECT s.*, COALESCE(m.major_name,'') as major_name, COALESCE(m.major_abbr,'') as major_abbr, COALESCE(f.faculty_name,'') as faculty_name FROM students s LEFT JOIN majors m ON s.major_id = m.major_id LEFT JOIN faculties f ON m.faculty_id = f.faculty_id WHERE s.student_id LIKE :q1 OR s.student_name LIKE :q2 OR m.major_name LIKE :q3 OR f.faculty_name LIKE :q4 ORDER BY s.student_id LIMIT 50");
        $stmt->execute([':q1'=>$like, ':q2'=>$like, ':q3'=>$like, ':q4'=>$like]);
        $students = $stmt->fetchAll(); $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $students ?: []], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function addStudent($data) {
    try {
        $student_id     = sanitizeInput($data['student_id'] ?? '');
        $student_name   = sanitizeInput($data['student_name'] ?? '');
        $points_balance = (int)($data['points_balance'] ?? 0);
        $major_id       = !empty($data['major_id']) ? sanitizeInput($data['major_id']) : null;
        if (empty($student_id) || empty($student_name)) throw new Exception('ข้อมูลไม่ครบถ้วน');
        $success = executeNonQuery("INSERT INTO students (student_id, student_name, major_id, points_balance, status) VALUES (:id, :name, :major_id, :points, 'active')", ['id' => $student_id, 'name' => $student_name, 'major_id' => $major_id, 'points' => $points_balance]);
        echo json_encode($success ? ['success' => true, 'message' => 'เพิ่มนักศึกษาสำเร็จ'] : ['success' => false, 'message' => 'เพิ่มนักศึกษาล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function updateStudent($data) {
    try {
        $student_id   = sanitizeInput($data['student_id'] ?? '');
        $student_name = sanitizeInput($data['student_name'] ?? '');
        $major_id     = !empty($data['major_id']) ? sanitizeInput($data['major_id']) : null;
        if (empty($student_id)) throw new Exception('Missing student_id');
        $success = executeNonQuery("UPDATE students SET student_name = :name, major_id = :major_id WHERE student_id = :id", ['name' => $student_name, 'major_id' => $major_id, 'id' => $student_id]);
        echo json_encode($success ? ['success' => true, 'message' => 'อัพเดทข้อมูลสำเร็จ'] : ['success' => false, 'message' => 'อัพเดทข้อมูลล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function deleteStudent($data) {
    try {
        $student_id = sanitizeInput($data['student_id'] ?? '');
        if (empty($student_id)) throw new Exception('Missing student_id');
        $success = executeNonQuery("DELETE FROM students WHERE student_id = :id", ['id' => $student_id]);
        echo json_encode($success ? ['success' => true, 'message' => 'ลบนักศึกษาสำเร็จ'] : ['success' => false, 'message' => 'ลบนักศึกษาล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function addPoints($data) {
    try {
        $student_id = sanitizeInput($data['student_id'] ?? '');
        $points     = (int)($data['points'] ?? 0);
        if (empty($student_id) || $points <= 0) throw new Exception('ข้อมูลไม่ครบถ้วน');
        if ($points > 100000) throw new Exception('จำนวนพอยต์ต่อรายการต้องไม่เกิน 100,000 พอยต์');
        $db = getDB(); $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT points_balance FROM students WHERE student_id = :student_id FOR UPDATE");
            $stmt->execute(['student_id' => $student_id]); $student = $stmt->fetch(); $stmt->closeCursor();
            if (!$student) throw new Exception('ไม่พบนักศึกษา');
            $points_before = (int)$student['points_balance']; $points_after = $points_before + $points;
            $stmt = $db->prepare("UPDATE students SET points_balance = :after, last_active = NOW() WHERE student_id = :student_id");
            $stmt->execute(['after' => $points_after, 'student_id' => $student_id]); $stmt->closeCursor();
            // Calculate money amount
            $ex_baht = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_baht'");
            $ex_pts  = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_points'");
            $b = (float)($ex_baht['value'] ?? 1);
            $p = (float)($ex_pts['value'] ?? 1);
            if ($p <= 0) $p = 1;
            $money_amount = round($points * ($b / $p), 2);

            $stmt = $db->prepare("INSERT INTO activities (student_id, activity_type, activity_detail, points, points_before, points_after, money_amount) VALUES (:student_id, 'add_points', 'เติมพอยต์', :change, :before, :after, :money)");
            $stmt->execute(['student_id' => $student_id, 'change' => $points, 'before' => $points_before, 'after' => $points_after, 'money' => $money_amount]); $stmt->closeCursor();
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'เติมพอยต์สำเร็จ', 'new_balance' => $points_after], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) { $db->rollBack(); throw $e; }
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function withdrawPoints($data) {
    try {
        $student_id = sanitizeInput($data['student_id'] ?? '');
        $points     = (int)($data['points'] ?? 0);
        $reason     = sanitizeInput($data['reason'] ?? 'ถอนพอยต์');
        if (empty($student_id) || $points <= 0) throw new Exception('ข้อมูลไม่ครบถ้วน');
        if ($points > 100000) throw new Exception('จำนวนพอยต์ต่อรายการต้องไม่เกิน 100,000 พอยต์');
        $db = getDB(); $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT points_balance FROM students WHERE student_id = :id FOR UPDATE");
            $stmt->execute(['id' => $student_id]); $student = $stmt->fetch(); $stmt->closeCursor();
            if (!$student) throw new Exception('ไม่พบนักศึกษา');
            $points_before = (int)$student['points_balance'];
            if ($points_before < $points) throw new Exception('พอยต์ไม่เพียงพอ');
            $points_after = $points_before - $points;
            $stmt = $db->prepare("UPDATE students SET points_balance = :balance WHERE student_id = :id");
            $stmt->execute(['balance' => $points_after, 'id' => $student_id]); $stmt->closeCursor();
            // Calculate money amount
            $ex_baht = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_baht'");
            $ex_pts  = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_points'");
            $b = (float)($ex_baht['value'] ?? 1);
            $p = (float)($ex_pts['value'] ?? 1);
            if ($p <= 0) $p = 1;
            $money_amount = round($points * ($b / $p), 2);

            $stmt = $db->prepare("INSERT INTO activities (student_id, activity_type, activity_detail, points, points_before, points_after, money_amount) VALUES (:id, 'withdraw_points', :reason, :change, :before, :after, :money)");
            $stmt->execute(['id' => $student_id, 'reason' => $reason, 'change' => -$points, 'before' => $points_before, 'after' => $points_after, 'money' => $money_amount]); $stmt->closeCursor();
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'ถอนพอยต์สำเร็จ', 'new_balance' => $points_after], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) { $db->rollBack(); throw $e; }
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function refundMoney($data) {
    try {
        $student_id = sanitizeInput($data['student_id'] ?? '');
        $points     = (int)($data['points'] ?? 0);
        $reason     = sanitizeInput($data['reason'] ?? 'ถอนคืนเงิน');
        if (empty($student_id) || $points <= 0) throw new Exception('ข้อมูลไม่ครบถ้วน');
        if ($points > 100000) throw new Exception('จำนวนพอยต์ต่อรายการต้องไม่เกิน 100,000 พอยต์');
        $db = getDB(); $db->beginTransaction();
        try {
            $stmt = $db->prepare("SELECT points_balance FROM students WHERE student_id = :id FOR UPDATE");
            $stmt->execute(['id' => $student_id]); $student = $stmt->fetch(); $stmt->closeCursor();
            if (!$student) throw new Exception('ไม่พบนักศึกษา');
            $points_before = (int)$student['points_balance'];
            if ($points_before < $points) throw new Exception('พอยต์ไม่เพียงพอสำหรับการถอนคืนเงิน');
            $points_after = $points_before - $points;
            $stmt = $db->prepare("UPDATE students SET points_balance = :balance, last_active = NOW() WHERE student_id = :id");
            $stmt->execute(['balance' => $points_after, 'id' => $student_id]); $stmt->closeCursor();
            // Calculate money amount
            $ex_baht = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_baht'");
            $ex_pts  = fetchSingle("SELECT value FROM settings WHERE `key` = 'exchange_rate_points'");
            $b = (float)($ex_baht['value'] ?? 1);
            $p = (float)($ex_pts['value'] ?? 1);
            if ($p <= 0) $p = 1;
            $money_amount = round($points * ($b / $p), 2);

            $stmt = $db->prepare("INSERT INTO activities (student_id, activity_type, activity_detail, points, points_before, points_after, money_amount) VALUES (:id, 'refund_money', :reason, :change, :before, :after, :money)");
            $stmt->execute(['id' => $student_id, 'reason' => $reason, 'change' => -$points, 'before' => $points_before, 'after' => $points_after, 'money' => $money_amount]); $stmt->closeCursor();
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'ถอนคืนเงินสำเร็จ', 'new_balance' => $points_after], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) { $db->rollBack(); throw $e; }
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function printDocument($data) {
    try {
        $student_id = sanitizeInput($data['student_id'] ?? '');
        $pages      = (int)($data['pages'] ?? 0);
        $file_name  = sanitizeInput($data['file_name'] ?? 'document.pdf');
        $stmt = getDB()->prepare("SELECT points_balance FROM students WHERE student_id = :id");
        $stmt->execute(['id' => $student_id]); $student = $stmt->fetch(); $stmt->closeCursor();
        if (!$student || $student['points_balance'] < ($pages * 5)) throw new Exception('พอยต์ไม่เพียงพอ');
        echo json_encode(['success' => true, 'message' => 'กำลังพิมพ์เอกสาร...', 'file_name' => $file_name], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getPrintHistory() {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $stmt = getDB()->prepare("SELECT a.*, s.student_name, p.printer_name FROM activities a JOIN students s ON a.student_id = s.student_id LEFT JOIN printers p ON a.printer_id = p.printer_id WHERE a.activity_type = 'print' ORDER BY a.created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT); $stmt->execute(); $history = $stmt->fetchAll(); $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $history], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getAllPrinters() {
    try { $printers = executeQuery("SELECT * FROM printers ORDER BY printer_id"); echo json_encode(['success' => true, 'printers' => $printers], JSON_UNESCAPED_UNICODE); }
    catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function refillPaper($data) {
    try {
        $printer_id = (int)($data['printer_id'] ?? 0);
        if ($printer_id <= 0) throw new Exception('Invalid printer_id');
        $success = executeNonQuery("UPDATE printers SET paper_current = paper_max, status = 'ready', updated_at = NOW() WHERE printer_id = :id", ['id' => $printer_id]);
        echo json_encode($success ? ['success' => true, 'message' => 'เติมกระดาษสำเร็จ'] : ['success' => false, 'message' => 'เติมกระดาษล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getActivitiesReport() {
    try {
        $start_date = sanitizeInput($_GET['start_date'] ?? date('Y-m-01'));
        $end_date   = sanitizeInput($_GET['end_date'] ?? date('Y-m-d'));
        $stmt = getDB()->prepare("SELECT a.*, s.student_name FROM activities a JOIN students s ON a.student_id = s.student_id WHERE DATE(a.created_at) BETWEEN :start AND :end ORDER BY a.created_at DESC");
        $stmt->execute(['start' => $start_date, 'end' => $end_date]); $activities = $stmt->fetchAll(); $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $activities], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getStudentStatistics() {
    try {
        $stats = executeQuery("SELECT s.student_id, s.student_name, s.points_balance, s.total_prints, s.total_pages, s.total_points_spent, COUNT(DISTINCT DATE(a.created_at)) as active_days FROM students s LEFT JOIN activities a ON s.student_id = a.student_id GROUP BY s.student_id ORDER BY s.total_prints DESC");
        echo json_encode(['success' => true, 'data' => $stats], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getFaculties() {
    try {
        $faculties = executeQuery("SELECT f.faculty_id, f.faculty_name, COUNT(m.major_id) as major_count FROM faculties f LEFT JOIN majors m ON f.faculty_id = m.faculty_id GROUP BY f.faculty_id ORDER BY f.faculty_id");
        echo json_encode(['success' => true, 'data' => $faculties], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function addFaculty($data) {
    try {
        $id   = sanitizeInput($data['faculty_id'] ?? '');
        $name = sanitizeInput($data['faculty_name'] ?? '');
        if (empty($id) || empty($name)) throw new Exception('ข้อมูลไม่ครบถ้วน');
        $success = executeNonQuery("INSERT INTO faculties (faculty_id, faculty_name) VALUES (:id, :name)", ['id' => $id, 'name' => $name]);
        echo json_encode($success ? ['success' => true, 'message' => 'เพิ่มคณะสำเร็จ'] : ['success' => false, 'message' => 'เพิ่มคณะล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function updateFaculty($data) {
    try {
        $id   = sanitizeInput($data['faculty_id'] ?? '');
        $name = sanitizeInput($data['faculty_name'] ?? '');
        if (empty($id) || empty($name)) throw new Exception('ข้อมูลไม่ครบถ้วน');
        $success = executeNonQuery("UPDATE faculties SET faculty_name = :name WHERE faculty_id = :id", ['name' => $name, 'id' => $id]);
        echo json_encode($success ? ['success' => true, 'message' => 'แก้ไขคณะสำเร็จ'] : ['success' => false, 'message' => 'แก้ไขคณะล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function deleteFaculty($data) {
    try {
        $id = sanitizeInput($data['faculty_id'] ?? '');
        if (empty($id)) throw new Exception('Missing faculty_id');
        $stmt = getDB()->prepare("SELECT COUNT(*) as cnt FROM students s JOIN majors m ON s.major_id = m.major_id WHERE m.faculty_id = :id");
        $stmt->execute(['id' => $id]); $row = $stmt->fetch(); $stmt->closeCursor();
        if ((int)$row['cnt'] > 0) throw new Exception('ไม่สามารถลบได้ เนื่องจากมีนักศึกษาในคณะนี้ ' . $row['cnt'] . ' คน');
        $success = executeNonQuery("DELETE FROM faculties WHERE faculty_id = :id", ['id' => $id]);
        echo json_encode($success ? ['success' => true, 'message' => 'ลบคณะสำเร็จ'] : ['success' => false, 'message' => 'ลบคณะล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getMajors() {
    try {
        $faculty_id = isset($_GET['faculty_id']) ? sanitizeInput($_GET['faculty_id']) : null;
        if ($faculty_id) {
            $stmt = getDB()->prepare("SELECT m.major_id, m.faculty_id, m.major_abbr, m.major_name, f.faculty_name, COUNT(s.student_id) as student_count FROM majors m JOIN faculties f ON m.faculty_id = f.faculty_id LEFT JOIN students s ON s.major_id = m.major_id WHERE m.faculty_id = :fid GROUP BY m.major_id ORDER BY m.major_id");
            $stmt->execute(['fid' => $faculty_id]);
        } else {
            $stmt = getDB()->prepare("SELECT m.major_id, m.faculty_id, m.major_abbr, m.major_name, f.faculty_name, COUNT(s.student_id) as student_count FROM majors m JOIN faculties f ON m.faculty_id = f.faculty_id LEFT JOIN students s ON s.major_id = m.major_id GROUP BY m.major_id ORDER BY m.faculty_id, m.major_id");
            $stmt->execute();
        }
        $majors = $stmt->fetchAll(); $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $majors], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function addMajor($data) {
    try {
        $major_id   = sanitizeInput($data['major_id'] ?? '');
        $faculty_id = sanitizeInput($data['faculty_id'] ?? '');
        $abbr       = sanitizeInput($data['major_abbr'] ?? '');
        $name       = sanitizeInput($data['major_name'] ?? '');
        if (empty($major_id) || empty($faculty_id) || empty($name)) throw new Exception('ข้อมูลไม่ครบถ้วน');
        $success = executeNonQuery("INSERT INTO majors (major_id, faculty_id, major_abbr, major_name) VALUES (:mid, :fid, :abbr, :name)", ['mid' => $major_id, 'fid' => $faculty_id, 'abbr' => strtoupper($abbr), 'name' => $name]);
        echo json_encode($success ? ['success' => true, 'message' => 'เพิ่มสาขาสำเร็จ'] : ['success' => false, 'message' => 'เพิ่มสาขาล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function updateMajor($data) {
    try {
        $id         = sanitizeInput($data['major_id'] ?? '');
        $faculty_id = sanitizeInput($data['faculty_id'] ?? '');
        $abbr       = sanitizeInput($data['major_abbr'] ?? '');
        $name       = sanitizeInput($data['major_name'] ?? '');
        if (empty($id) || empty($name)) throw new Exception('ข้อมูลไม่ครบถ้วน');
        $success = executeNonQuery("UPDATE majors SET faculty_id = :fid, major_abbr = :abbr, major_name = :name WHERE major_id = :id", ['fid' => $faculty_id, 'abbr' => strtoupper($abbr), 'name' => $name, 'id' => $id]);
        echo json_encode($success ? ['success' => true, 'message' => 'แก้ไขสาขาสำเร็จ'] : ['success' => false, 'message' => 'แก้ไขสาขาล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function deleteMajor($data) {
    try {
        $id = sanitizeInput($data['major_id'] ?? '');
        if (empty($id)) throw new Exception('Missing major_id');
        $stmt = getDB()->prepare("SELECT COUNT(*) as cnt FROM students WHERE major_id = :id");
        $stmt->execute(['id' => $id]); $row = $stmt->fetch(); $stmt->closeCursor();
        if ((int)$row['cnt'] > 0) throw new Exception('ไม่สามารถลบได้ เนื่องจากมีนักศึกษาในสาขานี้ ' . $row['cnt'] . ' คน');
        $success = executeNonQuery("DELETE FROM majors WHERE major_id = :id", ['id' => $id]);
        echo json_encode($success ? ['success' => true, 'message' => 'ลบสาขาสำเร็จ'] : ['success' => false, 'message' => 'ลบสาขาล้มเหลว'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function getSettings() {
    try {
        $rows = executeQuery("SELECT `key`, `value`, `description` FROM settings");
        $data = [];
        foreach ($rows as $row) { $data[$row['key']] = $row['value']; }
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function updateSetting($data) {
    try {
        $key   = sanitizeInput($data['key'] ?? '');
        $value = $data['value'] ?? '';  // อนุญาตค่าว่างสำหรับ PIN
        if (empty($key)) throw new Exception('Missing key');
        // UPSERT — INSERT or UPDATE
        $success = executeNonQuery(
            "INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = :value2",
            ['key' => $key, 'value' => $value, 'value2' => $value]
        );
        echo json_encode(['success' => true, 'message' => 'บันทึกสำเร็จ'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

/**
 * คืนข้อมูล admin ที่กำลังล็อกอิน (สำหรับโชว์ในหน้า settings)
 */
function getCurrentAdminInfo() {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            throw new Exception('ยังไม่ได้เข้าสู่ระบบ');
        }
        $admin_id = (int)($_SESSION['admin_id'] ?? 0);
        $stmt = getDB()->prepare("SELECT admin_id, username, full_name, email, role FROM admins WHERE admin_id = :id LIMIT 1");
        $stmt->execute(['id' => $admin_id]);
        $admin = $stmt->fetch();
        $stmt->closeCursor();
        if (!$admin) throw new Exception('ไม่พบบัญชีผู้ดูแล');
        echo json_encode(['success' => true, 'data' => $admin], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

/**
 * เปลี่ยนรหัสผ่าน admin (ต้องยืนยันรหัสปัจจุบัน)
 */
function changeAdminPassword($data) {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            throw new Exception('ยังไม่ได้เข้าสู่ระบบ');
        }
        $admin_id     = (int)($_SESSION['admin_id'] ?? 0);
        $current_pass = (string)($data['current_password'] ?? '');
        $new_pass     = (string)($data['new_password'] ?? '');
        $confirm_pass = (string)($data['confirm_password'] ?? '');

        if ($admin_id <= 0)            throw new Exception('Session ไม่ถูกต้อง');
        if ($current_pass === '')      throw new Exception('กรุณากรอกรหัสผ่านปัจจุบัน');
        if (strlen($new_pass) < 6)     throw new Exception('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
        if ($new_pass !== $confirm_pass) throw new Exception('รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน');
        if ($new_pass === $current_pass) throw new Exception('รหัสผ่านใหม่ต้องไม่ซ้ำกับรหัสผ่านเดิม');

        // ตรวจสอบรหัสปัจจุบัน
        $stmt = getDB()->prepare("SELECT password FROM admins WHERE admin_id = :id LIMIT 1");
        $stmt->execute(['id' => $admin_id]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if (!$row) throw new Exception('ไม่พบบัญชีผู้ดูแล');
        if (!password_verify($current_pass, $row['password'])) {
            throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
        }

        // อัปเดตรหัสใหม่ (hash)
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $ok = executeNonQuery(
            "UPDATE admins SET password = :pwd WHERE admin_id = :id",
            ['pwd' => $new_hash, 'id' => $admin_id]
        );
        if (!$ok) throw new Exception('อัปเดตรหัสผ่านไม่สำเร็จ');

        echo json_encode(['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ'], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function importStudents($data) {
    try {
        $rows = $data['rows'] ?? [];
        if (empty($rows)) throw new Exception('ไม่มีข้อมูลที่ส่งมา');
        $db = getDB(); $ins = 0; $upd = 0; $errs = [];
        foreach ($rows as $i => $row) {
            try {
                $raw_id = $row['student_id'] ?? '';
                if (is_float($raw_id) || is_int($raw_id)) $raw_id = (string)(int)$raw_id;
                $sid  = sanitizeInput(trim((string)$raw_id));
                // ตัดเฉพาะ ".0" ที่ติดมาจาก Excel (เลขทศนิยม) — ห้ามตัดเลข 0 ท้ายรหัสจริง
                if (strpos($sid, '.') !== false) $sid = preg_replace('/\.0+$/', '', $sid);
                $name = sanitizeInput(trim((string)($row['student_name'] ?? '')));
                $pts  = max(0, (int)($row['points_balance'] ?? 0));
                $mid  = !empty($row['major_id']) ? sanitizeInput((string)$row['major_id']) : null;
                if (empty($sid) || empty($name)) { $errs[] = 'แถว ' . ($i + 1) . ': ข้อมูลไม่ครบ'; continue; }
                $s = $db->prepare('SELECT student_id FROM students WHERE student_id = :id');
                $s->execute(['id' => $sid]); $ex = $s->fetch(); $s->closeCursor();
                if ($ex) {
                    $s = $db->prepare('UPDATE students SET student_name = :n, major_id = :m, updated_at = NOW() WHERE student_id = :id');
                    $s->execute(['n' => $name, 'm' => $mid, 'id' => $sid]); $s->closeCursor(); $upd++;
                } else {
                    $s = $db->prepare("INSERT INTO students (student_id, student_name, major_id, points_balance, status) VALUES (:id, :n, :m, :p, 'active')");
                    $s->execute(['id' => $sid, 'n' => $name, 'm' => $mid, 'p' => $pts]); $s->closeCursor(); $ins++;
                }
            } catch (Exception $e) { $errs[] = 'แถว ' . ($i + 1) . ': ' . $e->getMessage(); }
        }
        echo json_encode(['success' => true, 'inserted' => $ins, 'updated' => $upd, 'errors' => $errs, 'message' => "นำเข้าสำเร็จ: เพิ่มใหม่ {$ins} คน, อัพเดท {$upd} คน"], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}

function parseRtfStudents() {
    try {
        if (!isset($_FILES['file'])) throw new Exception('ไม่พบไฟล์');
        $file    = $_FILES['file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $content = file_get_contents($file['tmp_name']);
        $content = mb_convert_encoding($content, 'UTF-8', 'TIS-620');
        $students = [];
        if ($ext === 'asp' || substr($content, 0, 5) === '{\\rtf') {
            $content = preg_replace_callback('/\\\\u(-?\d+)\?/', function($m) { $code = (int)$m[1]; if ($code < 0) $code += 65536; return mb_chr($code, 'UTF-8'); }, $content);
            $cells = [];
            preg_match_all('/([^\\\\{}]*?)\\\\cell/', $content, $matches);
            foreach ($matches[1] as $raw) {
                $cell = preg_replace('/\\\\[a-zA-Z]+\-?[0-9]*\s?/', '', $raw);
                $cell = preg_replace('/[{}]/', '', $cell);
                $cell = trim($cell);
                if ($cell !== '') $cells[] = $cell;
            }
            $namePattern = '/^(นาย|นาง|นางสาว|Miss|Mr\.|Mrs\.)/u';
            $idPattern   = '/^\d{13}$/';
            for ($i = 0; $i < count($cells); $i++) {
                if (preg_match($idPattern, $cells[$i])) {
                    for ($j = $i+1; $j < min($i+6, count($cells)); $j++) {
                        if (preg_match($namePattern, $cells[$j])) {
                            $students[] = ['student_id' => $cells[$i], 'student_name' => $cells[$j], 'points_balance' => 0];
                            break;
                        }
                    }
                }
            }
        } else { throw new Exception('ไฟล์ไม่ถูกต้อง รองรับเฉพาะไฟล์ RTF (.asp)'); }
        if (empty($students)) throw new Exception('ไม่พบข้อมูลนักศึกษาในไฟล์');
        echo json_encode(['success' => true, 'data' => $students, 'count' => count($students)], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) { echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE); }
}


function getDebtStudents() {
    try {
        $stmt = getDB()->prepare("
            SELECT s.student_id, s.student_name, s.points_balance,
                   COALESCE(m.major_name,'') as major_name,
                   COALESCE(f.faculty_name,'') as faculty_name,
                   s.last_active
            FROM students s
            LEFT JOIN majors m ON s.major_id = m.major_id
            LEFT JOIN faculties f ON m.faculty_id = f.faculty_id
            WHERE s.points_balance < 0
            ORDER BY s.points_balance ASC
        ");
        $stmt->execute();
        $students = $stmt->fetchAll();
        $stmt->closeCursor();
        echo json_encode(['success' => true, 'data' => $students], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
