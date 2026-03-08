<?php
// admin/includes/sidebar.php
$current_script = $_SERVER['SCRIPT_NAME'];
$admin_position = strpos($current_script, '/admin/');
if ($admin_position !== false) {
    $after_admin = substr($current_script, $admin_position + 7);
    $depth = substr_count($after_admin, '/');
    $base_path = str_repeat('../', $depth);
} else {
    $base_path = '';
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $base_path; ?>dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15"><i class="bi bi-lightning-charge-fill"></i></div>
        <div class="sidebar-brand-text mx-3"><?php echo APP_NAME; ?></div>
    </a>
    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>dashboard.php">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
    </li>

    <!-- Leads -->
    <li class="nav-item <?php echo ($current_page == 'leads.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/leads.php">
            <i class="bi bi-people"></i><span>Leads</span>
        </a>
    </li>

    <!-- Clients -->
    <li class="nav-item <?php echo ($current_page == 'clients.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/clients.php">
            <i class="bi bi-building"></i><span>Clients</span>
        </a>
    </li>

    <!-- Projects (NEW) -->
    <li class="nav-item <?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/projects.php">
            <i class="bi bi-kanban"></i><span>Projects</span>
        </a>
    </li>

    <!-- Invoices (NEW) -->
    <li class="nav-item <?php echo ($current_page == 'invoices.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/invoices.php">
            <i class="bi bi-receipt"></i><span>Invoices</span>
        </a>
    </li>

    <!-- Reports -->
    <li class="nav-item <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/reports.php">
            <i class="bi bi-bar-chart"></i><span>Reports</span>
        </a>
    </li>

    <!-- Users -->
    <li class="nav-item <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>users.php">
            <i class="bi bi-people-fill"></i><span>Users</span>
        </a>
    </li>

    <!-- Settings -->
    <li class="nav-item <?php echo ($current_page == 'settings.php' || strpos($_SERVER['REQUEST_URI'], 'email_templates') !== false) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo $base_path; ?>views/settings.php">
            <i class="bi bi-gear"></i><span>Settings</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <div class="text-center d-none d-md-inline mt-3">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>