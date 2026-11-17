<?php
/**
 * =====================================================
 * ไฟล์ตรวจสอบการ Login
 * Authentication Check File
 * =====================================================
 * ใส่ไฟล์นี้ที่ด้านบนสุดของทุกหน้าที่ต้องการป้องกัน
 */

// เริ่ม Session (ถ้ายังไม่ได้เริ่ม)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ตรวจสอบว่า Login แล้วหรือไม่
 */
function checkLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // ยังไม่ได้ Login - redirect ไปหน้า Login
        header("Location: ../../login.php");
        exit;
    }
}

/**
 * ตรวจสอบ Role ของผู้ใช้
 */
function checkRole($required_role) {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== $required_role) {
        die('Access Denied: You do not have permission to access this page.');
    }
}

/**
 * ดึงข้อมูล Admin ที่ Login
 */
function getAdminInfo() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'name' => $_SESSION['admin_name'] ?? 'Admin',
        'role' => $_SESSION['admin_role'] ?? null
    ];
}

/**
 * Logout Function
 */
function logout() {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

// เรียกใช้ฟังก์ชันตรวจสอบ Login อัตโนมัติ
checkLogin();
?>
