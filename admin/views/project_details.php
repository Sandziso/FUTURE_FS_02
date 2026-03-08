<?php
// admin/views/project_details.php
require_once '../includes/header.php';

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$project_id) {
    $_SESSION['project_message'] = 'Invalid project ID.';
    $_SESSION['project_message_class'] = 'alert alert-danger';
    redirect('projects.php'); // You may not have projects list yet, adjust as needed
}

// Fetch project with client and lead info
$stmt = $pdo->prepare("
    SELECT p.*,
           c.company_name, c.contact_person as client_contact, c.email as client_email, c.phone as client_phone,
           l.name as lead_name, l.email as lead_email
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    LEFT JOIN leads l ON p.lead_id = l.id
    WHERE p.id = ?
");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['project_message'] = 'Project not found.';
    $_SESSION['project_message_class'] = 'alert alert-danger';
    redirect('projects.php');
}

// Fetch related tasks
$tasks = $pdo->prepare("
    SELECT t.*, u.username as assigned_to
    FROM tasks t
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.project_id = ?
    ORDER BY t.due_date ASC
");
$tasks->execute([$project_id]);
$tasks = $tasks->fetchAll();

// Fetch related expenses
$expenses = $pdo->prepare("
    SELECT e.*, u.username as created_by_name
    FROM expenses e
    LEFT JOIN users u ON e.created_by = u.id
    WHERE e.project_id = ?
    ORDER BY e.expense_date DESC
");
$expenses->execute([$project_id]);
$expenses = $expenses->fetchAll();

// Fetch related invoices (if any)
$invoices = $pdo->prepare("
    SELECT id, invoice_no, issue_date, due_date, total_amount, status
    FROM invoices
    WHERE project_id = ?
    ORDER BY issue_date DESC
");
$invoices->execute([$project_id]);
$invoices = $invoices->fetchAll();

$csrf_token = generateCSRFToken();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Project Details</h1>
    <div>
        <a href="project_edit.php?id=<?php echo $project['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit Project
        </a>
        <a href="projects.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Projects
        </a>
    </div>
</div>

<?php flash('project_message'); ?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Project Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Project Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Project Name:</div>
                    <div class="col-sm-9"><?php echo htmlspecialchars($project['name']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Description:</div>
                    <div class="col-sm-9"><?php echo nl2br(htmlspecialchars($project['description'] ?: '—')); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Status:</div>
                    <div class="col-sm-9">
                        <?php
                        $status_classes = [
                            'planning' => 'secondary',
                            'active' => 'primary',
                            'on_hold' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $class = $status_classes[$project['status']] ?? 'secondary';
                        echo "<span class=\"badge bg-{$class}\">" . ucfirst($project['status']) . "</span>";
                        ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Client:</div>
                    <div class="col-sm-9">
                        <?php if ($project['client_id']): ?>
                            <a href="client_details.php?id=<?php echo $project['client_id']; ?>">
                                <?php echo htmlspecialchars($project['company_name'] ?: $project['client_contact']); ?>
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Originated from Lead:</div>
                    <div class="col-sm-9">
                        <?php if ($project['lead_id']): ?>
                            <a href="lead_details.php?id=<?php echo $project['lead_id']; ?>">
                                <?php echo htmlspecialchars($project['lead_name']); ?>
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Timeline:</div>
                    <div class="col-sm-9">
                        <?php echo $project['start_date'] ? formatDate($project['start_date'], 'd M Y') : '—'; ?>
                        to
                        <?php echo $project['deadline'] ? formatDate($project['deadline'], 'd M Y') : '—'; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Budget:</div>
                    <div class="col-sm-9">
                        Estimated: <?php echo formatCurrency($project['estimated_budget']); ?><br>
                        Actual: <?php echo formatCurrency($project['actual_cost']); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Created:</div>
                    <div class="col-sm-9"><?php echo formatDate($project['created_at'], 'd M Y H:i'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Last Updated:</div>
                    <div class="col-sm-9"><?php echo formatDate($project['updated_at'], 'd M Y H:i'); ?></div>
                </div>
            </div>
        </div>

        <!-- Tasks Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Tasks</h6>
                <a href="task_add.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Task
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (count($tasks) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                                    <td><?php echo htmlspecialchars($task['assigned_to'] ?: 'Unassigned'); ?></td>
                                    <td><?php echo formatDate($task['due_date'], 'd M Y'); ?></td>
                                    <td>
                                        <?php if ($task['status'] == 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="task_details.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No tasks for this project.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Expenses Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Expenses</h6>
                <a href="expense_add.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Expense
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (count($expenses) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expenses as $exp): ?>
                                <tr>
                                    <td><?php echo formatDate($exp['expense_date'], 'd M Y'); ?></td>
                                    <td><?php echo htmlspecialchars($exp['category']); ?></td>
                                    <td><?php echo htmlspecialchars($exp['description'] ?: '—'); ?></td>
                                    <td><?php echo formatCurrency($exp['amount']); ?></td>
                                    <td>
                                        <a href="expense_details.php?id=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No expenses recorded for this project.</p>
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
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inv['invoice_no']); ?></td>
                                    <td><?php echo formatDate($inv['issue_date'], 'd M Y'); ?></td>
                                    <td><?php echo formatDate($inv['due_date'], 'd M Y'); ?></td>
                                    <td><?php echo formatCurrency($inv['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $status_colors = ['draft' => 'secondary', 'sent' => 'primary', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                                        $color = $status_colors[$inv['status']] ?? 'secondary';
                                        echo "<span class=\"badge bg-{$color}\">" . ucfirst($inv['status']) . "</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <a href="invoice_details.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="p-3 text-muted">No invoices linked to this project.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>