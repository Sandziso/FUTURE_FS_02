<?php
// admin/views/client_details.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    redirect('clients.php');
}

$stmt = $pdo->prepare("
    SELECT c.*, l.name as lead_name, l.email as lead_email, l.phone as lead_phone
    FROM clients c
    LEFT JOIN leads l ON c.lead_id = l.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) {
    $_SESSION['client_message'] = 'Client not found.';
    $_SESSION['client_message_class'] = 'alert alert-danger';
    redirect('clients.php');
}

// Fetch related projects
$projects = $pdo->prepare("SELECT * FROM projects WHERE client_id = ? ORDER BY created_at DESC");
$projects->execute([$id]);
$projects = $projects->fetchAll();

// Fetch related invoices
$invoices = $pdo->prepare("SELECT * FROM invoices WHERE client_id = ? ORDER BY issue_date DESC");
$invoices->execute([$id]);
$invoices = $invoices->fetchAll();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Client Details</h1>
    <a href="clients.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Clients</a>
</div>

<?php flash('client_message'); ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Client Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Client Information</h6>
                <a href="client_edit.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            </div>
            <div class="card-body">
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Company:</div><div class="col-sm-9"><?php echo htmlspecialchars($client['company_name']) ?: '—'; ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Contact Person:</div><div class="col-sm-9"><?php echo htmlspecialchars($client['contact_person']); ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Email:</div><div class="col-sm-9"><?php echo htmlspecialchars($client['email']) ?: '—'; ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Phone:</div><div class="col-sm-9"><?php echo htmlspecialchars($client['phone']) ?: '—'; ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Address:</div><div class="col-sm-9"><?php echo nl2br(htmlspecialchars($client['address'] ?: '—')); ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Originated from Lead:</div><div class="col-sm-9">
                    <?php if ($client['lead_id']): ?>
                        <a href="lead_details.php?id=<?php echo $client['lead_id']; ?>"><?php echo htmlspecialchars($client['lead_name']); ?></a>
                    <?php else: ?>—<?php endif; ?>
                </div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Created:</div><div class="col-sm-9"><?php echo formatDate($client['created_at'], 'd M Y H:i'); ?></div></div>
                <div class="row mb-3"><div class="col-sm-3 fw-bold">Last Updated:</div><div class="col-sm-9"><?php echo formatDate($client['updated_at'], 'd M Y H:i'); ?></div></div>
            </div>
        </div>

        <!-- Projects Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
            </div>
            <div class="card-body p-0">
                <?php if (count($projects) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Name</th><th>Status</th><th>Deadline</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td><?php echo ucfirst($p['status']); ?></td>
                                    <td><?php echo $p['deadline'] ? formatDate($p['deadline'], 'd M Y') : '—'; ?></td>
                                    <td><a href="project_details.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No projects for this client.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoices Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Invoices</h6>
            </div>
            <div class="card-body p-0">
                <?php if (count($invoices) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Invoice #</th><th>Amount</th><th>Status</th><th>Due Date</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inv['invoice_no']); ?></td>
                                    <td><?php echo formatCurrency($inv['total_amount']); ?></td>
                                    <td><?php echo ucfirst($inv['status']); ?></td>
                                    <td><?php echo formatDate($inv['due_date'], 'd M Y'); ?></td>
                                    <td><a href="invoice_details.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No invoices for this client.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>