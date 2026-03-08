<?php
// views/leads.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Handle status update via POST (AJAX or form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    $lead_id = (int)$_POST['lead_id'];
    $new_status = $_POST['status'] ?? '';
    $allowed = ['new', 'contacted', 'converted'];
    if (in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $lead_id]);
        $_SESSION['flash_message'] = 'Status updated.';
        $_SESSION['flash_class'] = 'alert alert-success';
    }
    redirect('leads.php');
}

// Get all leads
$stmt = $pdo->query("SELECT id, name, email, phone, source, status, created_at FROM leads ORDER BY created_at DESC");
$leads = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">All Leads</h1>
    <a href="add_lead.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Lead
    </a>
</div>

<!-- Flash message -->
<?php flash('flash_message'); ?>

<!-- Leads Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Leads</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="leadsTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?php echo $lead['id']; ?></td>
                        <td><?php echo htmlspecialchars($lead['name']); ?></td>
                        <td><?php echo htmlspecialchars($lead['email']); ?></td>
                        <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                        <td><?php echo htmlspecialchars($lead['source']); ?></td>
                        <td>
                            <!-- Inline status update form -->
                            <form method="post" action="" class="d-inline status-form">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="lead_id" value="<?php echo $lead['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="new" <?php echo $lead['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="contacted" <?php echo $lead['status'] == 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                    <option value="converted" <?php echo $lead['status'] == 'converted' ? 'selected' : ''; ?>>Converted</option>
                                </select>
                            </form>
                        </td>
                        <td><?php echo formatDate($lead['created_at'], 'd M Y'); ?></td>
                        <td>
                            <a href="lead_details.php?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <!-- Delete button (optional, with confirmation) -->
                            <a href="delete_lead.php?id=<?php echo $lead['id']; ?>&csrf_token=<?php echo urlencode(generateCSRFToken()); ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this lead? This action cannot be undone.')">
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

<!-- Optional: DataTables for sorting/searching -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script>
    $(document).ready(function() {
        $('#leadsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10
        });
    });
</script>

<?php include '../includes/footer.php'; ?>