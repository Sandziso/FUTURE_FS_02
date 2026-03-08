<?php
// admin/views/invoice_details.php
require_once '../includes/header.php';

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$invoice_id) {
    $_SESSION['invoice_message'] = 'Invalid invoice ID.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php'); // Adjust if you have an invoices list page
}

// Fetch invoice with client and project info
$stmt = $pdo->prepare("
    SELECT i.*,
           c.company_name, c.contact_person as client_contact, c.email as client_email, c.phone as client_phone,
           p.name as project_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    LEFT JOIN projects p ON i.project_id = p.id
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    $_SESSION['invoice_message'] = 'Invoice not found.';
    $_SESSION['invoice_message_class'] = 'alert alert-danger';
    redirect('invoices.php');
}

$csrf_token = generateCSRFToken();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Invoice Details</h1>
    <div>
        <a href="invoice_edit.php?id=<?php echo $invoice['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit Invoice
        </a>
        <a href="invoices.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Invoices
        </a>
    </div>
</div>

<?php flash('invoice_message'); ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Invoice Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Invoice #<?php echo htmlspecialchars($invoice['invoice_no']); ?></h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Invoice Number:</div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($invoice['invoice_no']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Client:</div>
                    <div class="col-sm-8">
                        <?php if ($invoice['client_id']): ?>
                            <a href="client_details.php?id=<?php echo $invoice['client_id']; ?>">
                                <?php echo htmlspecialchars($invoice['company_name'] ?: $invoice['client_contact']); ?>
                            </a>
                            <br>
                            <?php echo htmlspecialchars($invoice['client_email']); ?><br>
                            <?php echo htmlspecialchars($invoice['client_phone']); ?>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Related Project:</div>
                    <div class="col-sm-8">
                        <?php if ($invoice['project_id']): ?>
                            <a href="project_details.php?id=<?php echo $invoice['project_id']; ?>">
                                <?php echo htmlspecialchars($invoice['project_name']); ?>
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Issue Date:</div>
                    <div class="col-sm-8"><?php echo formatDate($invoice['issue_date'], 'd M Y'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Due Date:</div>
                    <div class="col-sm-8"><?php echo formatDate($invoice['due_date'], 'd M Y'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Total Amount:</div>
                    <div class="col-sm-8 h4"><?php echo formatCurrency($invoice['total_amount']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Status:</div>
                    <div class="col-sm-8">
                        <?php
                        $status_colors = [
                            'draft' => 'secondary',
                            'sent' => 'primary',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'secondary'
                        ];
                        $color = $status_colors[$invoice['status']] ?? 'secondary';
                        echo "<span class=\"badge bg-{$color} fs-6\">" . ucfirst($invoice['status']) . "</span>";
                        ?>
                    </div>
                </div>
                <?php if ($invoice['paid_date']): ?>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Paid Date:</div>
                    <div class="col-sm-8"><?php echo formatDate($invoice['paid_date'], 'd M Y'); ?></div>
                </div>
                <?php endif; ?>
                <?php if ($invoice['notes']): ?>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Notes:</div>
                    <div class="col-sm-8"><?php echo nl2br(htmlspecialchars($invoice['notes'])); ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Created:</div>
                    <div class="col-sm-8"><?php echo formatDate($invoice['created_at'], 'd M Y H:i'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Last Updated:</div>
                    <div class="col-sm-8"><?php echo formatDate($invoice['updated_at'], 'd M Y H:i'); ?></div>
                </div>
            </div>
        </div>

        <!-- Action Buttons (Mark as Paid, etc.) -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <?php if ($invoice['status'] != 'paid'): ?>
                    <form method="post" action="invoice_mark_paid.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark this invoice as paid?')">
                            <i class="bi bi-check-circle"></i> Mark as Paid
                        </button>
                    </form>
                <?php endif; ?>
                <?php if ($invoice['status'] == 'draft'): ?>
                    <form method="post" action="invoice_send.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-envelope"></i> Send to Client
                        </button>
                    </form>
                <?php endif; ?>
                <a href="invoice_print.php?id=<?php echo $invoice['id']; ?>" target="_blank" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Print
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>