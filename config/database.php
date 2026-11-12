<?php
// ============================================================
// Database Configuration and Connection using PDO
// ============================================================

// Database connection parameters (default for XAMPP)
$db_host = 'localhost';
$db_name = 'rtih_task_tracker';
$db_user = 'root';
$db_pass = ''; // Default XAMPP password is empty
$db_charset = 'utf8mb4';

// Optional local environment override (ignored in git)
if (file_exists(__DIR__ . '/database.local.php')) {
    include __DIR__ . '/database.local.php';
}

// Data Source Name (DSN) specifying the driver, host, database, and charset
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

// PDO configuration options
$options = [
    // Throw PDOException on error so issues are caught cleanly
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Fetch records as associative arrays by default (e.g. $task['title'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Use native prepared statements for enhanced security against SQL injection
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production/presentation, never expose raw database credentials or errors to users
    die("Database connection failed. Please ensure MySQL is running and your database settings in config/database.php are correct.");
}
