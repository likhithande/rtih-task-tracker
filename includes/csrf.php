<?php
// ============================================================
// CSRF (Cross-Site Request Forgery) Protection Helper
// ============================================================

// Ensure session is started before accessing $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generates or retrieves an existing CSRF token for the current session.
 * 
 * @return string 64-character hexadecimal secure token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // Generate a cryptographically secure random 32-byte string and convert to hex
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies that the submitted CSRF token matches the session token.
 * Uses hash_equals to protect against timing attacks.
 * 
 * @param string|null $token The token sent by the form/request
 * @return bool True if valid, false otherwise
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Helper function to render a hidden HTML CSRF input field.
 * 
 * @return string HTML input tag
 */
function csrf_field(): string {
    $token = htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

