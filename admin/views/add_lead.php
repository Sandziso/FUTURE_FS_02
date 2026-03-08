<?php
// admin/views/add_lead.php
require_once '../includes/header.php';

$error = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $source = trim($_POST['source'] ?? '');
        $status = $_POST['status'] ?? 'new';

        if (empty($name)) {
            $error = 'Name is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $error = 'Invalid email format.';
        } else {
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
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Lead</h1>
    <a href="leads.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Leads
    </a>
</div>

<!-- Form Card -->
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
                            <?php
                            $sources = ['Website', 'Facebook', 'Instagram', 'WhatsApp', 'Referral', 'Walk-in', 'Other'];
                            foreach ($sources as $s) {
                                $selected = (($_POST['source'] ?? '') == $s) ? 'selected' : '';
                                echo "<option value=\"$s\" $selected>$s</option>";
                            }
                            ?>
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