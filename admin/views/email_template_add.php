<?php
// admin/views/email_template_add.php
require_once '../includes/header.php';

$error = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');

        if (empty($name) || empty($subject) || empty($body)) {
            $error = 'All fields are required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO email_templates (name, subject, body, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $subject, $body, $_SESSION['user_id']])) {
                logActivity($_SESSION['user_id'], 'Created email template', "Template: $name");
                $_SESSION['template_message'] = 'Template created successfully.';
                $_SESSION['template_message_class'] = 'alert alert-success';
                redirect('settings.php?tab=email_templates');
            } else {
                $error = 'Failed to create template.';
            }
        }
    }
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Add Email Template</h1>
    <a href="settings.php?tab=email_templates" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>

                    <div class="mb-3">
                        <label for="body" class="form-label">Body (HTML allowed)</label>
                        <textarea class="form-control" id="body" name="body" rows="10" required></textarea>
                        <div class="form-text">You can use placeholders like {{lead_name}}, {{company_name}} etc.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="settings.php?tab=email_templates" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>