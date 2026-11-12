<?php
// ============================================================
// User Login
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

// If already logged in, redirect directly to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../tasks/index.php');
    exit;
}

$error = '';
$email = '';

// Check query parameter messages
$info_message = '';
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $info_message = 'You have been logged out successfully.';
} elseif (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'Please log in to access your task dashboard.';
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {
        // 2. Retrieve user input
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3. Validation
        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email address and password.';
        } else {
            try {
                // 4. Query user by email using prepared statement
                $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // 5. Verify password hash
                if ($user && password_verify($password, $user['password'])) {
                    // Prevent session fixation attack by regenerating session ID
                    session_regenerate_id(true);

                    // Store user details in session
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['name'];
                    $_SESSION['user_email'] = $user['email'];

                    // Redirect to tasks dashboard
                    header('Location: ../tasks/index.php');
                    exit;
                } else {
                    // Generic friendly message to prevent user enumeration
                    $error = 'Invalid email or password.';
                }
            } catch (PDOException $e) {
                // Never expose database errors directly to the user
                $error = 'A database error occurred. Please try again later.';
            }
        }
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom text-center py-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                        <i class="bi bi-shield-lock fs-2"></i>
                    </div>
                    <h4 class="mb-1 text-primary fw-bold">Sign In</h4>
                    <p class="text-muted small mb-0">RTIH Task Tracker System</p>
                </div>
                <div class="card-body p-4">

                    <!-- Info Alert -->
                    <?php if (!empty($info_message)): ?>
                        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($info_message, ENT_QUOTES, 'UTF-8'); ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Error Alert -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form action="login.php" method="POST" novalidate>
                        <!-- CSRF Field -->
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="name@example.com" 
                                       value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password" 
                                       required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </button>
                        </div>
                    </form>

                </div>
                <div class="card-footer bg-light text-center py-3">
                    <span class="text-muted small">Don't have an account?</span> 
                    <a href="register.php" class="fw-semibold text-primary text-decoration-none">Register here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

