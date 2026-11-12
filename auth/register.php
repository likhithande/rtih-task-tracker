<?php
// ============================================================
// User Registration
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

// If user is already logged in, redirect directly to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../tasks/index.php');
    exit;
}

$errors = [];
$success = '';
$name = '';
$email = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {
        // 2. Retrieve and sanitize/trim user input
        $name             = trim($_POST['name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // 3. Validation
        if (empty($name)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Name cannot exceed 100 characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 150) {
            $errors[] = 'Email cannot exceed 150 characters.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Password and confirmation password do not match.';
        }

        // 4. If inputs are valid, check uniqueness and insert into database
        if (empty($errors)) {
            try {
                // Prepared statement to check if email already exists
                $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $check_stmt->execute([$email]);

                if ($check_stmt->fetch()) {
                    $errors[] = 'An account with this email address already exists. Please login instead.';
                } else {
                    // Securely hash password using PHP's standard algorithm (BCrypt)
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user record with prepared statement
                    $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    $insert_stmt->execute([$name, $email, $hashed_password]);

                    $success = 'Registration successful! You can now log in to your account.';
                    // Clear inputs after successful registration
                    $name = '';
                    $email = '';
                }
            } catch (PDOException $e) {
                // Friendly error message without exposing database details
                $errors[] = 'A server error occurred while creating your account. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom text-center py-3">
                    <h4 class="mb-1 text-primary fw-bold">Create Account</h4>
                    <p class="text-muted small mb-0">RTIH Task Tracker Registration</p>
                </div>
                <div class="card-body p-4">

                    <!-- Success Alert -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                            <div>
                                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-sm btn-success text-white">Click here to Login</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Alert -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Please correct the following errors:</strong>
                            </div>
                            <ul class="mb-0 ps-3 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form action="register.php" method="POST" novalidate>
                        <!-- CSRF Protection Field -->
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       placeholder="e.g. Rahul Sharma" 
                                       value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" 
                                       maxlength="100" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="e.g. rahul@example.com" 
                                       value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" 
                                       maxlength="150" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="At least 6 characters" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Re-enter your password" 
                                       required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-semibold">
                                <i class="bi bi-person-check me-1"></i> Register Account
                            </button>
                        </div>
                    </form>

                </div>
                <div class="card-footer bg-light text-center py-3">
                    <span class="text-muted small">Already have an account?</span> 
                    <a href="login.php" class="fw-semibold text-primary text-decoration-none">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

