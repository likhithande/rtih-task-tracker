<?php
// ============================================================
// Tasks Dashboard - View, Search & Filter Tasks (Read)
// ============================================================

require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/csrf.php';

$user_id = $_SESSION['user_id'];

// Retrieve and sanitize search and filter query parameters
$search        = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

// Allowed status values for filtering
$valid_statuses = ['Pending', 'In Progress', 'Completed'];

// Build the prepared query dynamically based on search & filter inputs
$sql = "SELECT id, title, description, status, created_at FROM tasks WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

// Search filter by title
if ($search !== '') {
    $sql .= " AND title LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Status filter
if (in_array($status_filter, $valid_statuses, true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

// Order newest tasks first
$sql .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving tasks. Please try again later.");
}

// Handle notification messages
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

$success_message = '';
if ($msg === 'added') {
    $success_message = 'Task added successfully.';
} elseif ($msg === 'updated') {
    $success_message = 'Task updated successfully.';
} elseif ($msg === 'deleted') {
    $success_message = 'Task deleted successfully.';
}

$error_message = '';
if ($error === 'unauthorized') {
    $error_message = 'You are not authorized to perform this action.';
} elseif ($error === 'not_found') {
    $error_message = 'Task not found or has already been removed.';
} elseif ($error === 'invalid_csrf') {
    $error_message = 'Invalid request.';
}

$page_title = 'Task Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

    <!-- Top Greeting and Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-clipboard2-check text-primary me-2"></i>My Tasks
            </h2>
            <p class="text-muted mb-0">Manage, organize, and track your daily activities.</p>
        </div>
        <div>
            <a href="create.php" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                <i class="bi bi-plus-lg fs-6"></i>
                <span>Add New Task</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Form (Level 2) -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="index.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label fw-semibold small text-muted">Search by Title</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               placeholder="e.g. Documentation, Project review..." 
                               value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label fw-semibold small text-muted">Filter by Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo ($status_filter === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo ($status_filter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-funnel me-1"></i> Apply
                    </button>
                    <?php if ($search !== '' || $status_filter !== ''): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Clear search and filters">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table (Level 1) -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">
                <i class="bi bi-list-task me-1 text-primary"></i> Task List
            </span>
            <span class="badge bg-secondary">
                Total: <?php echo count($tasks); ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tasks)): ?>
                <div class="text-center py-5">
                    <div class="display-6 text-muted mb-3">
                        <i class="bi bi-clipboard-x"></i>
                    </div>
                    <?php if ($search !== '' || $status_filter !== ''): ?>
                        <h5 class="fw-semibold text-secondary">No matching tasks found</h5>
                        <p class="text-muted small">Try modifying your search keywords or status filter.</p>
                        <a href="index.php" class="btn btn-outline-primary btn-sm">Clear Filters</a>
                    <?php else: ?>
                        <h5 class="fw-semibold text-secondary">No tasks available yet</h5>
                        <p class="text-muted small">You have not created any tasks. Start organizing your work today!</p>
                        <a href="create.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Add Your First Task
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">#</th>
                                <th scope="col" style="min-width: 200px;">Title</th>
                                <th scope="col" style="min-width: 250px;">Description</th>
                                <th scope="col" style="width: 140px;">Status</th>
                                <th scope="col" style="width: 170px;">Created Date</th>
                                <th scope="col" class="text-center" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?php echo $counter++; ?></td>
                                    
                                    <!-- Task Title (XSS Protected) -->
                                    <td class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <!-- Task Description (XSS Protected) -->
                                    <td class="text-secondary small">
                                        <?php if (!empty($task['description'])): ?>
                                            <?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">No description provided</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Task Status Badge -->
                                    <td>
                                        <?php 
                                            $badge_class = 'bg-secondary';
                                            if ($task['status'] === 'Pending') {
                                                $badge_class = 'badge-pending';
                                            } elseif ($task['status'] === 'In Progress') {
                                                $badge_class = 'badge-in-progress';
                                            } elseif ($task['status'] === 'Completed') {
                                                $badge_class = 'badge-completed';
                                            }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> px-2 py-1 fw-semibold">
                                            <?php echo htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>

                                    <!-- Created Date -->
                                    <td class="text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($task['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- Edit Button -->
                                            <a href="edit.php?id=<?php echo (int)$task['id']; ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Edit Task">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <!-- Delete Button (leads to confirmation) -->
                                            <a href="delete.php?id=<?php echo (int)$task['id']; ?>" 
                                               class="btn btn-outline-danger" 
                                               title="Delete Task">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

