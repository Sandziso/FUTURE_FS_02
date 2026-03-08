<?php
// admin/views/client_add.php
require_once '../includes/header.php';

$error = '';
$csrf_token = generateCSRFToken();

// Get leads that are converted but not yet clients (optional: allow all leads)
$leads = $pdo->query("SELECT id, name, email FROM leads WHERE status = 'converted' AND converted_to_client = 0 ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $lead_id       = (int)($_POST['lead_id'] ?? 0);
        $company_name  = trim($_POST['company_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $address       = trim($_POST['address'] ?? '');

        if (empty($contact_person)) {
            $error = 'Contact person is required.';
        } elseif ($lead_id <= 0) {
            $error = 'Please select a lead.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $error = 'Invalid email format.';
        } else {
            // Check if lead is already a client
            $check = $pdo->prepare("SELECT id FROM clients WHERE lead_id = ?");
            $check->execute([$lead_id]);
            if ($check->fetch()) {
                $error = 'This lead is already a client.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO clients (lead_id, company_name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$lead_id, $company_name, $contact_person, $email, $phone, $address])) {
                    // Mark lead as converted_to_client
                    $pdo->prepare("UPDATE leads SET converted_to_client = 1 WHERE id = ?")->execute([$lead_id]);
                    logActivity($_SESSION['user_id'], 'Created client', "Client: $contact_person, Lead ID: $lead_id");
                    $_SESSION['client_message'] = 'Client added successfully.';
                    $_SESSION['client_message_class'] = 'alert alert-success';
                    redirect('clients.php');
                } else {
                    $error = 'Failed to add client.';
                }
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add New Client</h1>
    <a href="clients.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to Clients</a>
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
                        <label for="lead_id" class="form-label">Select Lead <span class="text-danger">*</span></label>
                        <select class="form-select" id="lead_id" name="lead_id" required>
                            <option value="">-- Choose a converted lead --</option>
                            <?php foreach ($leads as $lead): ?>
                                <option value="<?php echo $lead['id']; ?>"><?php echo htmlspecialchars($lead['name'] . ' (' . $lead['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Client</button>
                    <a href="clients.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>