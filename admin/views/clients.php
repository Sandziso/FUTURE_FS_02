<?php
// admin/views/clients.php
require_once '../includes/header.php';

// Get all clients with lead info
$stmt = $pdo->query("
    SELECT c.*, l.name as lead_name, l.email as lead_email
    FROM clients c
    LEFT JOIN leads l ON c.lead_id = l.id
    ORDER BY c.created_at DESC
");
$clients = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Clients</h1>
    <a href="client_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Client
    </a>
</div>

<?php flash('client_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Clients</h6>
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
                        <th>Lead Source</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo $client['id']; ?></td>
                        <td><?php echo htmlspecialchars($client['company_name'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone']); ?></td>
                        <td><?php echo htmlspecialchars($client['lead_name'] ?: 'Direct'); ?></td>
                        <td><?php echo formatDate($client['created_at'], 'd M Y'); ?></td>
                        <td>
                            <a href="client_details.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="client_edit.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="client_delete.php?id=<?php echo $client['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this client? Associated projects/invoices will be orphaned.')">
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
        $('#clientsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>

<?php include '../includes/footer.php'; ?>