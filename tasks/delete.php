<?php
// ============================================================
// Delete Task (Delete) with Confirmation & Ownership Check
// ============================================================

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

$user_id = $_SESSION['user_id'];

// Retrieve and validate task ID
$task_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$task_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$task_id) {
    header('Location: index.php?error=not_found');
    exit;
}

// ------------------------------------------------------------
// Handle POST Request (Perform Deletion)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token (Level 3 Security)
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        header('Location: index.php?error=invalid_csrf');
        exit;
    }

    try {
        // 2. Delete task ONLY if it belongs to the logged-in user
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            // Task successfully deleted
            header('Location: index.php?msg=deleted');
            exit;
        } else {
            // Task was not found or belonged to someone else
            header('Location: index.php?error=unauthorized');
            exit;
        }
    } catch (PDOException $e) {
        die("Error deleting task. Please try again later.");
    }
}

// ------------------------------------------------------------
// Handle GET Request (Show Confirmation Screen)
// ------------------------------------------------------------
try {
    // Verify task exists and belongs to current user before showing confirmation
    $stmt = $pdo->prepare("SELECT id, title, description, status, created_at FROM tasks WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();

    if (!$task) {
        header('Location: index.php?error=unauthorized');
        exit;
    }
} catch (PDOException $e) {
    die("Error retrieving task. Please try again later.");
}

$page_title = 'Confirm Task Deletion';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-danger border-opacity-25">
                <div class="card-header bg-danger bg-opacity-10 text-danger text-center py-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-2"></i>
                    <h4 class="mb-0 fw-bold">Delete Task Confirmation</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <p class="text-secondary mb-3">
                        Are you sure you want to permanently delete this task?
                    </p>

                    <!-- Task Details Card -->
                    <div class="bg-light p-3 rounded text-start mb-4 border">
                        <div class="mb-2">
                            <span class="text-muted small">Title:</span>
                            <div class="fw-bold text-dark">
                                <?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                            <div class="mb-2">
                                <span class="text-muted small">Description:</span>
                                <div class="small text-secondary">
                                    <?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="text-muted small">Status:</span>
                            <span class="badge bg-secondary ms-1">
                                <?php echo htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    </div>

                    <p class="text-danger small fw-semibold mb-4">
                        <i class="bi bi-info-circle me-1"></i> This action cannot be undone.
                    </p>

                    <!-- Confirmation Form with CSRF Protection -->
                    <form action="delete.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$task['id']; ?>">

                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger px-4 fw-semibold">
                                <i class="bi bi-trash me-1"></i> Yes, Delete Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

