<?php
/**
 * =====================================================
 * Index Page - Redirect to Login
 * =====================================================
 */

session_start();

// ถ้า Login แล้ว ให้ไปหน้า Dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: web-app/admin/dashboard.php");
} else {
    // ถ้ายังไม่ Login ให้ไปหน้า Login
    header("Location: login.php");
}
exit;
