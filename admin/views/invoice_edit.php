<?php
// admin/views/invoice_edit.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['invoice_message'] = 'Invalid invoice ID.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();
if (!$invoice) {
    $_SESSION['invoice_message'] = 'Invoice not found.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$error = '';
$csrf_token = generateCSRFToken();

// Fetch clients and projects for dropdowns
$clients = $pdo->query("SELECT id, company_name, contact_person FROM clients ORDER BY company_name")->fetchAll();
$projects = $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $invoice_no = trim($_POST['invoice_no'] ?? '');
        $client_id = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
        $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
        $issue_date = $_POST['issue_date'] ?? $invoice['issue_date'];
        $due_date = $_POST['due_date'] ?? $invoice['due_date'];
        $total_amount = (float)($_POST['total_amount'] ?? 0);
        $status = $_POST['status'] ?? $invoice['status'];
        $notes = trim($_POST['notes'] ?? '');
        $paid_date = !empty($_POST['paid_date']) ? $_POST['paid_date'] : null;

        if (empty($invoice_no)) {
            $error = 'Invoice number is required.';
        } elseif ($client_id === null && $project_id === null) {
            $error = 'Either a client or a project must be selected.';
        } elseif (!in_array($status, ['draft','sent','paid','overdue','cancelled'])) {
            $error = 'Invalid status.';
        } else {
            // Check if invoice number already exists (excluding current)
            $check = $pdo->prepare("SELECT id FROM invoices WHERE invoice_no = ? AND id != ?");
            $check->execute([$invoice_no, $id]);
            if ($check->fetch()) {
                $error = 'Invoice number already exists. Please use a different number.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE invoices SET
                        invoice_no = ?, client_id = ?, project_id = ?, issue_date = ?, due_date = ?,
                        total_amount = ?, status = ?, notes = ?, paid_date = ?
                    WHERE id = ?
                ");
                if ($stmt->execute([$invoice_no, $client_id, $project_id, $issue_date, $due_date, $total_amount, $status, $notes, $paid_date, $id])) {
                    logActivity($_SESSION['user_id'], 'Updated invoice', "Invoice ID: $id");
                    $_SESSION['invoice_message'] = 'Invoice updated successfully.';
                    $_SESSION['invoice_message_class'] = 'alert alert-success';
                    redirect('invoices.php');
                } else {
                    $error = 'Failed to update invoice.';
                }
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Invoice</h1>
    <a href="invoices.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Invoices</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="invoice_no" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="<?php echo htmlspecialchars($invoice['invoice_no']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">-- None --</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($invoice['client_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['company_name'] ?: $c['contact_person']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">-- None --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($invoice['project_id'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo htmlspecialchars($invoice['issue_date']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars($invoice['due_date']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo CURRENCY_SYMBOL ?? 'R'; ?></span>
                            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="<?php echo htmlspecialchars($invoice['total_amount']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?php echo ($invoice['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="sent" <?php echo ($invoice['status'] == 'sent') ? 'selected' : ''; ?>>Sent</option>
                            <option value="paid" <?php echo ($invoice['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="overdue" <?php echo ($invoice['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                            <option value="cancelled" <?php echo ($invoice['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="paid_date" class="form-label">Paid Date</label>
                        <input type="date" class="form-control" id="paid_date" name="paid_date" value="<?php echo htmlspecialchars($invoice['paid_date']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($invoice['notes']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Invoice</button>
                    <a href="invoices.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>