<?php
// admin/user_edit.php
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['user_message'] = 'Invalid user ID.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

// Fetch user data
$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['user_message'] = 'User not found.';
    $_SESSION['user_message_class'] = 'alert alert-danger';
    redirect('users.php');
}

$error = '';
$success = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';

        // Validation
        if (empty($username)) {
            $error = 'Username is required.';
        } elseif (!in_array($role, ['admin', 'staff'])) {
            $error = 'Invalid role selected.';
        } else {
            // Check if username already exists (excluding current user)
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->execute([$username, $id]);
            if ($check->fetch()) {
                $error = 'Username already exists. Please choose another.';
            } else {
                // Build update query
                if (!empty($password)) {
                    // Change password
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                    $params = [$username, $hashed, $role, $id];
                } else {
                    // No password change
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                    $params = [$username, $role, $id];
                }

                if ($stmt->execute($params)) {
                    logActivity($_SESSION['user_id'], 'Updated user', "User ID: $id, Username: $username");
                    $_SESSION['user_message'] = 'User updated successfully.';
                    $_SESSION['user_message_class'] = 'alert alert-success';
                    redirect('users.php');
                } else {
                    $error = 'Failed to update user.';
                }
            }
        }
    }
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
    <a href="users.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<!-- Edit User Form -->
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
                               value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>