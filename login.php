<?php
/**
 * login.php - User Login Page
 * 
 * Handles authentication for LeadFlow CRM.
 * Features: CSRF protection, remember me, role-based redirect, activity logging.
 * Styling matches the overall site design with gradient background and glass card.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and helper functions
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect('admin/dashboard.php');
    } else {
        redirect('staff/dashboard.php');
    }
}

$error = '';
$success = '';

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) ? true : false;

        // Basic validation
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            // Fetch user from database
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                session_regenerate_id(true); // Prevent session fixation

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];

                // Log the activity (requires activity_log table)
                if (function_exists('logActivity')) {
                    logActivity($user['id'], 'LOGIN', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                }

                // Handle "Remember Me" – set a cookie (valid for 30 days)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (86400 * 30); // 30 days

                    // Store hashed token in database
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([password_hash($token, PASSWORD_DEFAULT), $user['id']]);

                    // Set cookie (HttpOnly, Secure if HTTPS)
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }

                // Set success flash message and redirect
                $_SESSION['flash_message'] = 'Welcome back, ' . htmlspecialchars($user['username']) . '!';
                $_SESSION['flash_class'] = 'alert alert-success';

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('staff/dashboard.php');
                }
            } else {
                $error = 'Invalid username or password.';
                // Optional: log failed attempt
                if (function_exists('logActivity')) {
                    logActivity(null, 'LOGIN_FAILED', "Failed login attempt for username: $username");
                }
            }
        }
    }
}

// Include the header (navbar and opening HTML tags)
include 'includes/header.php';
?>

<style>
    /* Additional inline styling to create a gradient background for the login page */
    .login-wrapper {
        min-height: calc(100vh - 160px); /* Adjust based on header/footer height */
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
    }
    .login-card {
        border: none;
        border-radius: 1.5rem;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .login-header {
        background: transparent;
        border-bottom: none;
        padding-top: 2rem;
    }
    .login-footer {
        background: transparent;
        border-top: none;
        padding-bottom: 2rem;
    }
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }
    .form-control:focus {
        border-color: #764ba2;
        box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
    }
    .text-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }
</style>

<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card login-card">
                    <div class="card-header login-header text-center">
                        <h3 class="fw-bold text-gradient">
                            <i class="bi bi-lightning-charge-fill me-2" style="color: #667eea;"></i><?php echo APP_NAME; ?>
                        </h3>
                        <p class="text-muted">Sign in to your account</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Display flash messages -->
                        <?php flash('flash_message'); ?>

                        <!-- Display error message (if any) -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="post" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Enter your username" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                           required autofocus>
                                    <div class="invalid-feedback">
                                        Please enter your username.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required>
                                    <div class="invalid-feedback">
                                        Please enter your password.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="forgot_password.php" class="text-decoration-none small">Forgot password?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-login btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer login-footer text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-semibold">Register here</a></p>
                        <hr class="my-3">
                        <a href="index.php" class="text-decoration-none small">
                            <i class="bi bi-arrow-left me-1"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer (closes container, includes scripts)
include 'includes/footer.php';
?>