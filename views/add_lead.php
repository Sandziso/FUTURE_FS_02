<?php
// views/add_lead.php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

// Generate CSRF token
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $status = $_POST['status'] ?? 'new';

        // Basic validation
        if (empty($name)) {
            $error = 'Name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $error = 'Invalid email format.';
        } else {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $email, $phone, $source, $status])) {
                $_SESSION['flash_message'] = 'Lead added successfully.';
                $_SESSION['flash_class'] = 'alert alert-success';
                redirect('leads.php');
            } else {
                $error = 'Failed to add lead. Please try again.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Add New Lead</h6>
                <a href="leads.php" class="btn btn-sm btn-secondary ms-auto">
                    <i class="bi bi-arrow-left"></i> Back to Leads
                </a>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="source" class="form-label">Source</label>
                        <select class="form-select" id="source" name="source">
                            <option value="">-- Select Source --</option>
                            <option value="Website" <?php echo (($_POST['source'] ?? '') == 'Website') ? 'selected' : ''; ?>>Website</option>
                            <option value="Facebook" <?php echo (($_POST['source'] ?? '') == 'Facebook') ? 'selected' : ''; ?>>Facebook</option>
                            <option value="Instagram" <?php echo (($_POST['source'] ?? '') == 'Instagram') ? 'selected' : ''; ?>>Instagram</option>
                            <option value="WhatsApp" <?php echo (($_POST['source'] ?? '') == 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                            <option value="Referral" <?php echo (($_POST['source'] ?? '') == 'Referral') ? 'selected' : ''; ?>>Referral</option>
                            <option value="Walk-in" <?php echo (($_POST['source'] ?? '') == 'Walk-in') ? 'selected' : ''; ?>>Walk-in</option>
                            <option value="Other" <?php echo (($_POST['source'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="new" <?php echo (($_POST['status'] ?? 'new') == 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="contacted" <?php echo (($_POST['status'] ?? '') == 'contacted') ? 'selected' : ''; ?>>Contacted</option>
                            <option value="converted" <?php echo (($_POST['status'] ?? '') == 'converted') ? 'selected' : ''; ?>>Converted</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>