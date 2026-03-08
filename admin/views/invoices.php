<?php
// admin/views/invoices.php
require_once '../includes/header.php';

// Get all invoices with client name
$stmt = $pdo->query("
    SELECT i.*, c.company_name, c.contact_person as client_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    ORDER BY i.issue_date DESC
");
$invoices = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Invoices</h1>
    <a href="invoice_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Create New Invoice
    </a>
</div>

<?php flash('invoice_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Invoices</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="invoicesTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($inv['invoice_no']); ?></td>
                        <td><?php echo htmlspecialchars($inv['client_name'] ?: '—'); ?></td>
                        <td><?php echo formatDate($inv['issue_date'], 'd M Y'); ?></td>
                        <td><?php echo formatDate($inv['due_date'], 'd M Y'); ?></td>
                        <td><?php echo formatCurrency($inv['total_amount']); ?></td>
                        <td>
                            <?php
                            $status_colors = [
                                'draft' => 'secondary',
                                'sent' => 'primary',
                                'paid' => 'success',
                                'overdue' => 'danger',
                                'cancelled' => 'secondary'
                            ];
                            $color = $status_colors[$inv['status']] ?? 'secondary';
                            echo "<span class=\"badge bg-{$color}\">" . ucfirst($inv['status']) . "</span>";
                            ?>
                        </td>
                        <td>
                            <a href="invoice_details.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="invoice_edit.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="invoice_delete.php?id=<?php echo $inv['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this invoice? This action cannot be undone.')">
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
        $('#invoicesTable').DataTable({
            order: [[2, 'desc']], // order by issue date
            pageLength: 10
        });
    });
</script>

<?php include '../includes/footer.php'; ?>