<?php
// ============================================================
// Header Component (HTML Head, Navbar, Session Status)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compute relative path to root depending on whether script is in subfolder
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$root_path = in_array($current_dir, ['auth', 'tasks', 'includes', 'config']) ? '../' : './';

$page_title = isset($page_title) ? $page_title . ' - RTIH Task Tracker' : 'RTIH Task Tracker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo $root_path; ?>assets/css/style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo isset($_SESSION['user_id']) ? $root_path . 'tasks/index.php' : $root_path . 'auth/login.php'; ?>">
                <i class="bi bi-check2-square text-primary fs-4"></i>
                <span>RTIH Task Tracker</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Links for Logged-In Users -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>tasks/index.php">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>tasks/create.php">
                                <i class="bi bi-plus-circle me-1"></i> Add Task
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item me-3 text-light">
                            <span class="text-white-50">Welcome,</span> 
                            <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-light btn-sm" href="<?php echo $root_path; ?>auth/logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                <?php else: ?>
                    <!-- Links for Guests -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $root_path; ?>auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm text-white ms-lg-2" href="<?php echo $root_path; ?>auth/register.php">
                                <i class="bi bi-person-plus me-1"></i> Register
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-4">

