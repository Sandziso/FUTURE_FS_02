<?php
// admin/views/client_edit.php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['client_message'] = 'Invalid client ID.';
    $_SESSION['client_message_class'] = 'alert alert-danger';
    redirect('clients.php');
}

$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) {
    $_SESSION['client_message'] = 'Client not found.';
    $_SESSION['client_message_class'] = 'alert alert-danger';
    redirect('clients.php');
}

$error = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $company_name  = trim($_POST['company_name'] ?? '');
        $contact_person = trim($_POST['contact_person'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $address       = trim($_POST['address'] ?? '');

        if (empty($contact_person)) {
            $error = 'Contact person is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $error = 'Invalid email format.';
        } else {
            $stmt = $pdo->prepare("UPDATE clients SET company_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            if ($stmt->execute([$company_name, $contact_person, $email, $phone, $address, $id])) {
                logActivity($_SESSION['user_id'], 'Updated client', "Client ID: $id");
                $_SESSION['client_message'] = 'Client updated successfully.';
                $_SESSION['client_message_class'] = 'alert alert-success';
                redirect('clients.php');
            } else {
                $error = 'Failed to update client.';
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Client</h1>
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
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($client['company_name']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($client['contact_person']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($client['address']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Client</button>
                    <a href="clients.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>