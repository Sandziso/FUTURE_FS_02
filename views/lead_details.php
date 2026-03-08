<?php
// views/lead_details.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$lead_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$lead_id) {
    redirect('leads.php');
}

// Fetch lead details
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$lead_id]);
$lead = $stmt->fetch();
if (!$lead) {
    $_SESSION['flash_message'] = 'Lead not found.';
    $_SESSION['flash_class'] = 'alert alert-danger';
    redirect('leads.php');
}

// Handle adding a note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $note = trim($_POST['note'] ?? '');
        if (!empty($note)) {
            $stmt = $pdo->prepare("INSERT INTO notes (lead_id, note, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$lead_id, $note]);
            $_SESSION['flash_message'] = 'Note added successfully.';
            $_SESSION['flash_class'] = 'alert alert-success';
            redirect("lead_details.php?id=$lead_id");
        } else {
            $error = 'Note cannot be empty.';
        }
    }
}

// Handle status update from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $new_status = $_POST['status'] ?? '';
        $allowed = ['new', 'contacted', 'converted'];
        if (in_array($new_status, $allowed)) {
            $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $lead_id]);
            $_SESSION['flash_message'] = 'Status updated.';
            $_SESSION['flash_class'] = 'alert alert-success';
            redirect("lead_details.php?id=$lead_id");
        }
    }
}

// Fetch all notes for this lead
$stmt = $pdo->prepare("SELECT id, note, created_at FROM notes WHERE lead_id = ? ORDER BY created_at DESC");
$stmt->execute([$lead_id]);
$notes = $stmt->fetchAll();

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Lead Details Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Lead Details</h6>
                <a href="leads.php" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Leads
                </a>
            </div>
            <div class="card-body">
                <?php flash('flash_message'); ?>

                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Name:</div>
                    <div class="col-sm-9"><?php echo htmlspecialchars($lead['name']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Email:</div>
                    <div class="col-sm-9"><?php echo htmlspecialchars($lead['email']) ?: '—'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Phone:</div>
                    <div class="col-sm-9"><?php echo htmlspecialchars($lead['phone']) ?: '—'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Source:</div>
                    <div class="col-sm-9"><?php echo htmlspecialchars($lead['source']) ?: '—'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Status:</div>
                    <div class="col-sm-9">
                        <form method="post" action="" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <div class="input-group input-group-sm w-auto">
                                <select name="status" class="form-select">
                                    <option value="new" <?php echo $lead['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="contacted" <?php echo $lead['status'] == 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                                    <option value="converted" <?php echo $lead['status'] == 'converted' ? 'selected' : ''; ?>>Converted</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Created:</div>
                    <div class="col-sm-9"><?php echo formatDate($lead['created_at'], 'd M Y H:i'); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Last Updated:</div>
                    <div class="col-sm-9"><?php echo formatDate($lead['updated_at'], 'd M Y H:i'); ?></div>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Follow-up Notes</h6>
            </div>
            <div class="card-body">
                <!-- Add Note Form -->
                <form method="post" action="" class="mb-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="add_note" value="1">
                    <div class="mb-3">
                        <label for="note" class="form-label">Add a note</label>
                        <textarea class="form-control" id="note" name="note" rows="2" placeholder="Enter your follow-up note..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Note
                    </button>
                </form>

                <!-- Notes List -->
                <?php if (count($notes) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($notes as $note): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <p class="mb-1"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                                    <small class="text-muted"><?php echo formatDate($note['created_at'], 'd M Y H:i'); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No notes yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>