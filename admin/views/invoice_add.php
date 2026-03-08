<?php
// admin/views/invoice_add.php
require_once '../includes/header.php';

$error = '';
$csrf_token = generateCSRFToken();

// Fetch clients for dropdown
$clients = $pdo->query("SELECT id, company_name, contact_person FROM clients ORDER BY company_name")->fetchAll();
// Fetch projects (optional) for linking
$projects = $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll();

// Generate a unique invoice number (simple example: INV-YYYY-XXXX)
$year = date('Y');
$last = $pdo->query("SELECT invoice_no FROM invoices WHERE invoice_no LIKE 'INV-$year-%' ORDER BY id DESC LIMIT 1")->fetchColumn();
if ($last) {
    $lastNum = (int)substr($last, strrpos($last, '-') + 1);
    $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
} else {
    $nextNum = '0001';
}
$suggested_invoice_no = "INV-$year-$nextNum";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $invoice_no = trim($_POST['invoice_no'] ?? '');
        $client_id = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
        $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
        $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
        $due_date = $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $total_amount = (float)($_POST['total_amount'] ?? 0);
        $status = $_POST['status'] ?? 'draft';
        $notes = trim($_POST['notes'] ?? '');

        if (empty($invoice_no)) {
            $error = 'Invoice number is required.';
        } elseif ($client_id === null && $project_id === null) {
            $error = 'Either a client or a project must be selected.';
        } elseif (!in_array($status, ['draft','sent','paid','overdue','cancelled'])) {
            $error = 'Invalid status.';
        } else {
            // Check if invoice number already exists
            $check = $pdo->prepare("SELECT id FROM invoices WHERE invoice_no = ?");
            $check->execute([$invoice_no]);
            if ($check->fetch()) {
                $error = 'Invoice number already exists. Please use a different number.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO invoices (invoice_no, client_id, project_id, issue_date, due_date, total_amount, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($stmt->execute([$invoice_no, $client_id, $project_id, $issue_date, $due_date, $total_amount, $status, $notes])) {
                    $invoice_id = $pdo->lastInsertId();
                    logActivity($_SESSION['user_id'], 'Created invoice', "Invoice ID: $invoice_id, Number: $invoice_no");
                    $_SESSION['invoice_message'] = 'Invoice created successfully.';
                    $_SESSION['invoice_message_class'] = 'alert alert-success';
                    redirect('invoices.php');
                } else {
                    $error = 'Failed to create invoice.';
                }
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Invoice</h1>
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
                        <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="<?php echo htmlspecialchars($_POST['invoice_no'] ?? $suggested_invoice_no); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">-- Select Client (if not linked to project) --</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['company_name'] ?: $c['contact_person']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project (optional)</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="issue_date" class="form-label">Issue Date</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars($_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days'))); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo CURRENCY_SYMBOL ?? 'R'; ?></span>
                            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="<?php echo htmlspecialchars($_POST['total_amount'] ?? '0.00'); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" <?php echo (($_POST['status'] ?? 'draft') == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="sent" <?php echo (($_POST['status'] ?? '') == 'sent') ? 'selected' : ''; ?>>Sent</option>
                            <option value="paid" <?php echo (($_POST['status'] ?? '') == 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="overdue" <?php echo (($_POST['status'] ?? '') == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                            <option value="cancelled" <?php echo (($_POST['status'] ?? '') == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                    <a href="invoices.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>