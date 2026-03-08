<?php
// admin/views/settings.php
require_once '../includes/header.php';

$active_tab = $_GET['tab'] ?? 'email_templates';
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Settings</h1>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $active_tab == 'email_templates' ? 'active' : ''; ?>" id="email-templates-tab" data-bs-toggle="tab" data-bs-target="#emailTemplates" type="button" role="tab">Email Templates</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?php echo $active_tab == 'general' ? 'active' : ''; ?>" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
    </li>
    <!-- Add more tabs as needed -->
</ul>

<div class="tab-content" id="settingsTabContent">
    <!-- Email Templates Tab -->
    <div class="tab-pane fade <?php echo $active_tab == 'email_templates' ? 'show active' : ''; ?>" id="emailTemplates" role="tabpanel">
        <?php include 'email_templates_list.php'; ?>
    </div>
    <!-- General Tab (placeholder) -->
    <div class="tab-pane fade <?php echo $active_tab == 'general' ? 'show active' : ''; ?>" id="general" role="tabpanel">
        <div class="card shadow">
            <div class="card-body">
                <p class="text-muted">General settings (company name, timezone, etc.) can be added here.</p>
                <!-- You can expand this later with a settings table -->
            </div>
        </div>
    </div>
</div>

<script>
    // Preserve tab on page reload via URL hash
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('shown.bs.tab', (event) => {
            const tab = event.target.id.replace('-tab', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState(null, null, url);
        });
    });
</script>

<?php include '../includes/footer.php'; ?>