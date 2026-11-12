<?php
// ============================================================
// Create New Task (Create)
// ============================================================

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

$errors = [];
$title = '';
$description = '';
$status = 'Pending'; // Default status as required

// Allowed status options
$allowed_statuses = ['Pending', 'In Progress', 'Completed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // 2. Retrieve and sanitize input
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = trim($_POST['status'] ?? 'Pending');

        // 3. Backend Validation (Level 2)
        if (empty($title)) {
            $errors[] = 'Task title is required.';
        } elseif (strlen($title) > 200) {
            $errors[] = 'Task title cannot exceed 200 characters.';
        }

        if (!in_array($status, $allowed_statuses, true)) {
            $errors[] = 'Invalid task status selected.';
        }

        // 4. Insert into database if validation passes
        if (empty($errors)) {
            try {
                // Prepared statement binding user_id to session
                $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $title,
                    $description !== '' ? $description : null,
                    $status
                ]);

                // Redirect to dashboard with success message
                header('Location: index.php?msg=added');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'A database error occurred while saving the task. Please try again.';
            }
        }
    }
}

$page_title = 'Add New Task';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Task</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="mb-0 text-primary fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Create New Task
                    </h4>
                </div>
                <div class="card-body p-4">

                    <!-- Validation Errors Display -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Please fix the following:</strong>
                            </div>
                            <ul class="mb-0 ps-3 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Task Creation Form -->
                    <form action="create.php" method="POST" novalidate>
                        <!-- CSRF Field (Level 3 Security) -->
                        <?php echo csrf_field(); ?>

                        <!-- Title Field -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">
                                Task Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   placeholder="e.g. Complete RTIH internship documentation" 
                                   value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" 
                                   maxlength="200" 
                                   required>
                            <div class="form-text">Maximum 200 characters.</div>
                        </div>

                        <!-- Description Field -->
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Provide more details, checklist items, or notes..."><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="form-text">Optional details regarding this task.</div>
                        </div>

                        <!-- Status Field -->
                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Pending" <?php echo ($status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" <?php echo ($status === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Completed" <?php echo ($status === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="index.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Save Task
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

