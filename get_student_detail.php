<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$student_id = trim($_GET['id'] ?? '');

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุรหัสนักศึกษา'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = getDB();

    // ===== 1. ข้อมูลนักศึกษา =====
    $stmt = $db->prepare("SELECT student_id, student_name, points_balance, status FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลนักศึกษารหัส: ' . $student_id], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ===== 2. สถิติ =====
    $stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN activity_type = 'print' THEN 1 END) AS total_prints,
            SUM(CASE WHEN points < 0 THEN ABS(points) ELSE 0 END) AS used_points,
            SUM(CASE WHEN points > 0 THEN points ELSE 0 END) AS added_points,
            MAX(created_at) AS last_used
        FROM activities
        WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // ===== 3. ประวัติการใช้งาน =====
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i:%s') AS date,
            a.activity_type AS type,
            a.activity_detail AS detail,
            a.points AS points,
            a.points_after AS balance,
            a.pages,
            p.printer_name
        FROM activities a
        LEFT JOIN printers p ON a.printer_id = p.printer_id
        WHERE a.student_id = ?
        ORDER BY a.created_at DESC
        LIMIT 100
    ");
    $stmt->execute([$student_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // แปลง activity_type ให้ตรงกับ frontend
    foreach ($activities as &$act) {
        if ($act['type'] === 'add_points')      $act['type'] = 'add';
        if ($act['type'] === 'withdraw_points') $act['type'] = 'deduct';
        if ($act['type'] === 'refund_money')    $act['type'] = 'refund';
        $act['status'] = 'success';
    }
    unset($act);

    // ===== 4. กราฟ 7 วันล่าสุด =====
    $stmt = $db->prepare("
        SELECT DATE(created_at) AS print_date, SUM(pages) AS total_pages
        FROM activities
        WHERE student_id = ? AND activity_type = 'print'
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
    ");
    $stmt->execute([$student_id]);
    $chartRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $thaiMonths  = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $chartLabels = [];
    $chartData   = [];

    for ($i = 6; $i >= 0; $i--) {
        $date  = date('Y-m-d', strtotime("-$i days"));
        $day   = (int)date('j', strtotime($date));
        $month = (int)date('n', strtotime($date));
        $chartLabels[] = $day . ' ' . $thaiMonths[$month];
        $found = 0;
        foreach ($chartRaw as $row) {
            if ($row['print_date'] === $date) { $found = (int)$row['total_pages']; break; }
        }
        $chartData[] = $found;
    }

    echo json_encode([
        'success'    => true,
        'data'       => [
            'student'    => [
                'student_id'   => $student['student_id'],
                'student_name' => $student['student_name'],
                'points_balance' => (int)$student['points_balance'],
                'status'       => $student['status'],
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

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}