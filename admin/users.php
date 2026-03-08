<?php
// admin/users.php
require_once 'includes/header.php';

// Get all users
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Generate CSRF token for delete operations
$csrf_token = generateCSRFToken();
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
    <a href="user_add.php" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add New User
    </a>
</div>

<!-- Flash Messages -->
<?php flash('user_message'); ?>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($user['role'] == 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Staff</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($user['created_at'], 'd M Y, H:i'); ?></td>
                        <td>
                            <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): // Prevent self-deletion ?>
                                <a href="user_delete.php?id=<?php echo $user['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                                   title="Delete">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables for sorting/searching -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>

<?php include 'includes/footer.php'; ?>