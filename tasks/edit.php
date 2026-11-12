<?php
// ============================================================
// Edit Existing Task (Update)
// ============================================================

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

$user_id = $_SESSION['user_id'];
$errors  = [];

// 1. Validate task ID parameter
$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$task_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$task_id) {
    header('Location: index.php?error=not_found');
    exit;
}

// 2. Fetch task with STRICT ownership check (Level 3 Authorization)
// Ensures user can only retrieve and edit their OWN tasks
try {
    $stmt = $pdo->prepare("SELECT id, user_id, title, description, status FROM tasks WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        // If task does not exist or belongs to another user, deny access
        header('Location: index.php?error=unauthorized');
        exit;
    }
} catch (PDOException $e) {
    die("Error retrieving task information. Please try again.");
}

$title       = $task['title'];
$description = $task['description'] ?? '';
$status      = $task['status'];

$allowed_statuses = ['Pending', 'In Progress', 'Completed'];

// 3. Process Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = trim($_POST['status'] ?? 'Pending');

        // Backend Validation
        if (empty($title)) {
            $errors[] = 'Task title is required.';
        } elseif (strlen($title) > 200) {
            $errors[] = 'Task title cannot exceed 200 characters.';
        }

        if (!in_array($status, $allowed_statuses, true)) {
            $errors[] = 'Invalid task status selected.';
        }

        // 4. Update task in database with ownership check
        if (empty($errors)) {
            try {
                $update_stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?");
                $update_stmt->execute([
                    $title,
                    $description !== '' ? $description : null,
                    $status,
                    $task_id,
                    $user_id
                ]);

                // Redirect with success message
                header('Location: index.php?msg=updated');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'A database error occurred while updating the task. Please try again.';
            }
        }
    }
}

$page_title = 'Edit Task';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">

            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Task #<?php echo (int)$task['id']; ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="mb-0 text-primary fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Edit Task
                    </h4>
                </div>
                <div class="card-body p-4">

                    <!-- Error Messages -->
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

                    <!-- Edit Form -->
                    <form action="edit.php?id=<?php echo (int)$task['id']; ?>" method="POST" novalidate>
                        <!-- CSRF Field -->
                        <?php echo csrf_field(); ?>

                        <input type="hidden" name="id" value="<?php echo (int)$task['id']; ?>">

                        <!-- Title Field -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">
                                Task Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
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
                                      rows="4"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
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
                                <i class="bi bi-save me-1"></i> Update Task
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

