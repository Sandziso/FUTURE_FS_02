<?php
// admin/views/projects.php
require_once '../includes/header.php';

// Get all projects with client name
$stmt = $pdo->query("
    SELECT p.*, c.company_name, c.contact_person as client_name
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    ORDER BY p.created_at DESC
");
$projects = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Projects</h1>
    <a href="project_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Project
    </a>
</div>

<?php flash('project_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="projectsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Budget</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo $project['id']; ?></td>
                        <td><?php echo htmlspecialchars($project['name']); ?></td>
                        <td><?php echo htmlspecialchars($project['client_name'] ?: '—'); ?></td>
                        <td>
                            <?php
                            $status_colors = [
                                'planning' => 'secondary',
                                'active' => 'primary',
                                'on_hold' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $color = $status_colors[$project['status']] ?? 'secondary';
                            echo "<span class=\"badge bg-{$color}\">" . ucfirst($project['status']) . "</span>";
                            ?>
                        </td>
                        <td><?php echo $project['deadline'] ? formatDate($project['deadline'], 'd M Y') : '—'; ?></td>
                        <td><?php echo formatCurrency($project['estimated_budget']); ?></td>
                        <td>
                            <a href="project_details.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="project_edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="project_delete.php?id=<?php echo $project['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this project? This will orphan related tasks and expenses.')">
                                <i class="bi bi-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script>
    $(document).ready(function() {
        $('#projectsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>

<?php include '../includes/footer.php'; ?>