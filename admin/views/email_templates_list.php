<?php
// admin/views/email_templates_list.php
$templates = $pdo->query("SELECT * FROM email_templates ORDER BY created_at DESC")->fetchAll();
$csrf_token = generateCSRFToken();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Email Templates</h5>
    <a href="email_template_add.php" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> New Template</a>
</div>

<?php flash('template_message'); ?>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Created By</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $tpl): ?>
            <tr>
                <td><?php echo $tpl['id']; ?></td>
                <td><?php echo htmlspecialchars($tpl['name']); ?></td>
                <td><?php echo htmlspecialchars($tpl['subject']); ?></td>
                <td><?php echo $tpl['created_by']; // you can join with users if needed ?></td>
                <td><?php echo formatDate($tpl['created_at'], 'd M Y'); ?></td>
                <td>
                    <a href="email_template_edit.php?id=<?php echo $tpl['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                    <a href="email_template_delete.php?id=<?php echo $tpl['id']; ?>&csrf_token=<?php echo urlencode($csrf_token); ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this template?')"><i class="bi bi-trash"></i> Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>