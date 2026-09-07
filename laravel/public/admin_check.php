<?php

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header("Location: login.php");
    exit;
}

// Check if user has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Akses ditolak! Anda tidak memiliki hak akses sebagai administrator.';
    header("Location: index.php");
    exit;
}

// User is verified as admin, continue to page
// Optional: Update last activity
$user_id = $_SESSION['user_id'];
$update_activity = $conn->query("UPDATE users SET updated_at = NOW() WHERE id = $user_id");
?>
