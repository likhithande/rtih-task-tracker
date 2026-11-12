<?php
// ============================================================
// User Logout
// ============================================================

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Completely destroy the session
session_destroy();

// Redirect to login page with friendly logout message
header('Location: login.php?logout=success');
exit;

