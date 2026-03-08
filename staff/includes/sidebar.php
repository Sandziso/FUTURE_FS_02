<?php
// staff/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
// Determine base path for links (staff area is at same level as admin)
$base_path = '../'; // since staff views are in staff/views/ etc., but we link to /views/
// Actually better to use BASE_URL constant from config
?>
<ul class="navbar-nav bg-gradient-secondary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo BASE_URL; ?>/staff/dashboard.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="bi bi-lightning-charge-fill"></i>
        </div>
        <div class="sidebar-brand-text mx-3"><?php echo APP_NAME; ?> <span class="staff-badge">Staff</span></div>
    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/staff/dashboard.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Leads -->
    <li class="nav-item <?php echo (in_array($current_page, ['leads.php', 'lead_details.php', 'add_lead.php'])) ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/leads.php">
            <i class="bi bi-people"></i>
            <span>Leads</span>
        </a>
    </li>

    <!-- Clients -->
    <li class="nav-item <?php echo ($current_page == 'clients.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/clients.php">
            <i class="bi bi-building"></i>
            <span>Clients</span>
        </a>
    </li>

    <!-- Projects -->
    <li class="nav-item <?php echo ($current_page == 'projects.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/projects.php">
            <i class="bi bi-kanban"></i>
            <span>Projects</span>
        </a>
    </li>

    <!-- Invoices -->
    <li class="nav-item <?php echo ($current_page == 'invoices.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/invoices.php">
            <i class="bi bi-receipt"></i>
            <span>Invoices</span>
        </a>
    </li>

    <!-- Reports (optional) -->
    <li class="nav-item <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
        <a class="nav-link" href="<?php echo BASE_URL; ?>/views/reports.php">
            <i class="bi bi-bar-chart"></i>
            <span>Reports</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
<!-- End of Sidebar -->