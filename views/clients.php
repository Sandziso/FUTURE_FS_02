<?php
// views/clients.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';  // Ensures user is logged in
require_once '../includes/functions.php';

// Get all clients with related lead info (optional)
$query = "
    SELECT c.*, l.name as lead_name, l.email as lead_email, l.status as lead_status
    FROM clients c
    LEFT JOIN leads l ON c.lead_id = l.id
    ORDER BY c.created_at DESC
";
$stmt = $pdo->query($query);
$clients = $stmt->fetchAll();

// Generate CSRF token for delete actions
$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Clients</h1>
    <!-- Optional: Add client manually if needed, but typically from leads -->
    <!-- <a href="client_add.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Client</a> -->
</div>

<?php flash('client_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">All Clients</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="clientsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Source Lead</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No clients found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo $client['id']; ?></td>
                            <td><?php echo htmlspecialchars($client['company_name'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>">
                                    <?php echo htmlspecialchars($client['email'] ?: '—'); ?>
                                </a>
                            </td>
                            <td>
                                <a href="tel:<?php echo htmlspecialchars($client['phone']); ?>">
                                    <?php echo htmlspecialchars($client['phone'] ?: '—'); ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($client['lead_id']): ?>
                                    <a href="lead_details.php?id=<?php echo $client['lead_id']; ?>">
                                        <?php echo htmlspecialchars($client['lead_name'] ?: 'Lead #' . $client['lead_id']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($client['created_at'], 'd M Y'); ?></td>
                            <td>
                                <a href="client_details.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="client_edit.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="client_delete.php?id=<?php echo $client['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.')"
                                   title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>