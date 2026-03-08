<?php
// admin/includes/topbar.php
?>
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
        <i class="bi bi-list fs-4"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ms-auto">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown no-arrow mx-2">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                <span class="badge bg-danger badge-counter">3+</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">Notifications</h6>
                <a class="dropdown-item" href="#"><i class="bi bi-person-plus me-2"></i> New lead: Sipho Dlamini</a>
                <a class="dropdown-item" href="#"><i class="bi bi-calendar-check me-2"></i> Task due: Follow up with Nomsa</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center small" href="#">Show all</a>
            </div>
        </li>

        <!-- User Dropdown -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <i class="bi bi-person-circle fs-5"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow">
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php"><i class="bi bi-person me-2"></i>Profile</a>
                <a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
        </li>
    </ul>
</nav>