<?php
// ============================================================
// Authentication Protection Middleware / Check
// ============================================================

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If the user ID is not set in the session, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    // Relative path to auth/login.php from inside tasks/ directory
    header('Location: ../auth/login.php?error=unauthorized');
    exit;
}

