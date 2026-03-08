<?php
/**
 * Updated Header for LeadFlow CRM
 * Features:
 * - Glassmorphism design matching the updated footer
 * - Dynamic active link highlighting using current script name
 * - Role-based menu items (admin/staff)
 * - User dropdown with profile and logout
 * - Responsive mobile menu with Bootstrap 5
 * - CSRF token availability (optional, can be used in forms)
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define app name if not defined (should be in config, but fallback)
if (!defined('APP_NAME')) {
    define('APP_NAME', 'LeadFlow');
}

// Get current script name for active link detection
$current_page = basename($_SERVER['PHP_SELF']);

// Helper to determine if a page is active
function isActive($page) {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}

// Generate a CSRF token for forms that might be included in the header (e.g., logout via POST)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Lead Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS (optional) -->
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/style.css">
    <style>
        /* Glassmorphism navbar */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .navbar-brand {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .nav-link {
            border-radius: 50rem;
            padding: 0.5rem 1.2rem !important;
            font-weight: 500;
            transition: all 0.2s;
            color: #2d3748 !important;
            margin: 0 0.2rem;
        }
        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        .navbar-toggler:focus {
            box-shadow: none;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 1rem;
            padding: 0.5rem;
        }
        .dropdown-item {
            border-radius: 2rem;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
        }
        .dropdown-item:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        /* Optional: subtle divider */
        .navbar-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
            margin: 0.5rem 1rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>index.php">
            <i class="bi bi-lightning-charge-fill me-2" style="background: none; -webkit-text-fill-color: initial; color: #667eea;"></i>
            <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    // Determine dashboard link based on role
                    $dashboard_link = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')
                        ? (defined('BASE_URL') ? BASE_URL : '') . 'admin/dashboard.php'
                        : (defined('BASE_URL') ? BASE_URL : '') . 'staff/dashboard.php';
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive(basename($dashboard_link)); ?>" href="<?php echo $dashboard_link; ?>">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('leads.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/views/leads.php">
                            <i class="bi bi-people me-1"></i>Leads
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('add_lead.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/views/add_lead.php">
                            <i class="bi bi-plus-circle me-1"></i>Add Lead
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('clients.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/views/clients.php">
                            <i class="bi bi-briefcase me-1"></i>Clients
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('users.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/admin/users.php">
                            <i class="bi bi-people-fill me-1"></i>Users
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- User Dropdown -->
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1 fs-5"></i>
                            <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                            <?php if (isset($_SESSION['user_role'])): ?>
                                <small class="ms-1 text-muted">(<?php echo $_SESSION['user_role']; ?>)</small>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>logout.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Public navigation for non-logged-in users -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('index.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('login.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('register.php'); ?>" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Optional: Page header with container start (opened here, closed in footer) -->
<div class="container mt-4">
<!-- End of header, content starts here -->