<?php
// ============================================================
// Application Entry Point / Root Router
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already authenticated, redirect to the task dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: tasks/index.php');
    exit;
}

// Otherwise, redirect to the login page
header('Location: auth/login.php');
exit;

