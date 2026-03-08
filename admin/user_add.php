<?php
// admin/user_add.php
require_once 'includes/header.php';

$error = '';
$username = '';
$role = 'staff';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        // Validation
        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        } elseif (!in_array($role, ['admin', 'staff'])) {
            $error = 'Invalid role selected.';
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists. Please choose another.';
            } else {
                // Hash password and insert
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashed, $role])) {
                    // Log activity
                    logActivity($_SESSION['user_id'], 'Created user', "Username: $username, Role: $role");
                    $_SESSION['user_message'] = 'User created successfully.';
                    $_SESSION['user_message_class'] = 'alert alert-success';
                    redirect('users.php');
                } else {
                    $error = 'Failed to create user. Please try again.';
                }
            }
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New User</h1>
    <a href="users.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<!-- Add User Form -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Minimum 6 characters recommended.</div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="staff" <?php echo $role == 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>