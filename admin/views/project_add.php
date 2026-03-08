<?php
// admin/views/project_add.php
require_once '../includes/header.php';

$error = '';
$csrf_token = generateCSRFToken();

// Fetch clients for dropdown
$clients = $pdo->query("SELECT id, company_name, contact_person FROM clients ORDER BY company_name")->fetchAll();
// Fetch leads (optional) – could be used to create project from a lead
$leads = $pdo->query("SELECT id, name FROM leads WHERE status = 'converted' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $client_id = !empty($_POST['client_id']) ? (int)$_POST['client_id'] : null;
        $lead_id = !empty($_POST['lead_id']) ? (int)$_POST['lead_id'] : null;
        $status = $_POST['status'] ?? 'planning';
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        $estimated_budget = !empty($_POST['estimated_budget']) ? (float)$_POST['estimated_budget'] : null;

        if (empty($name)) {
            $error = 'Project name is required.';
        } elseif (!in_array($status, ['planning','active','on_hold','completed','cancelled'])) {
            $error = 'Invalid status.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO projects (name, description, client_id, lead_id, status, start_date, deadline, estimated_budget)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$name, $description, $client_id, $lead_id, $status, $start_date, $deadline, $estimated_budget])) {
                $project_id = $pdo->lastInsertId();
                logActivity($_SESSION['user_id'], 'Created project', "Project ID: $project_id, Name: $name");
                $_SESSION['project_message'] = 'Project created successfully.';
                $_SESSION['project_message_class'] = 'alert alert-success';
                redirect('projects.php');
            } else {
                $error = 'Failed to create project.';
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Project</h1>
    <a href="projects.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Projects</a>
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
                        <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client (optional)</label>
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="">-- Select Client --</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['company_name'] ?: $c['contact_person']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="lead_id" class="form-label">Lead (optional, if project originates from a lead)</label>
                        <select class="form-select" id="lead_id" name="lead_id">
                            <option value="">-- Select Lead --</option>
                            <?php foreach ($leads as $l): ?>
                                <option value="<?php echo $l['id']; ?>" <?php echo (isset($_POST['lead_id']) && $_POST['lead_id'] == $l['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($l['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="planning" <?php echo (($_POST['status'] ?? 'planning') == 'planning') ? 'selected' : ''; ?>>Planning</option>
                            <option value="active" <?php echo (($_POST['status'] ?? '') == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="on_hold" <?php echo (($_POST['status'] ?? '') == 'on_hold') ? 'selected' : ''; ?>>On Hold</option>
                            <option value="completed" <?php echo (($_POST['status'] ?? '') == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo (($_POST['status'] ?? '') == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="estimated_budget" class="form-label">Estimated Budget</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo CURRENCY_SYMBOL ?? 'R'; ?></span>
                            <input type="number" step="0.01" class="form-control" id="estimated_budget" name="estimated_budget" value="<?php echo htmlspecialchars($_POST['estimated_budget'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Project</button>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>