<?php
/**
 * register.php - User Registration
 * 
 * Allows new users to create an account.
 * Features: CSRF protection, password hashing, validation, unique username check.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and helper functions
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// If user is already logged in, redirect to dashboard
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

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = 'Username must be between 3 and 50 characters.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already taken. Please choose another.';
            } else {
                // Hash password and insert new user (default role = 'staff')
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, 'staff', NOW())");
                if ($stmt->execute([$username, $hashed_password])) {
                    // Log activity (optional)
                    if (function_exists('logActivity')) {
                        $new_user_id = $pdo->lastInsertId();
                        logActivity($new_user_id, 'REGISTER', 'New user registered');
                    }

                    // Set success message and redirect to login
                    $_SESSION['flash_message'] = 'Registration successful! You can now log in.';
                    $_SESSION['flash_class'] = 'alert alert-success';
                    redirect('login.php');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

// Include the header
include 'includes/header.php';
?>

<style>
    /* Reuse the same gradient background as login page */
    .register-wrapper {
        min-height: calc(100vh - 160px);
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
    }
    .register-card {
        border: none;
        border-radius: 1.5rem;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .register-header {
        background: transparent;
        border-bottom: none;
        padding-top: 2rem;
    }
    .register-footer {
        background: transparent;
        border-top: none;
        padding-bottom: 2rem;
    }
    .btn-register {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    .btn-register:hover {
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

<div class="register-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card register-card">
                    <div class="card-header register-header text-center">
                        <h3 class="fw-bold text-gradient">
                            <i class="bi bi-lightning-charge-fill me-2" style="color: #667eea;"></i><?php echo APP_NAME; ?>
                        </h3>
                        <p class="text-muted">Create a new account</p>
                    </div>

                    <div class="card-body p-4">
                        <!-- Display flash messages (if any) -->
                        <?php flash('flash_message'); ?>

                        <!-- Display error message (if any) -->
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="post" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Choose a username (3-50 chars)" 
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                           required minlength="3" maxlength="50">
                                    <div class="invalid-feedback">
                                        Please choose a username between 3 and 50 characters.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="At least 6 characters" required minlength="6">
                                    <div class="invalid-feedback">
                                        Password must be at least 6 characters.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Re-enter your password" required>
                                    <div class="invalid-feedback">
                                        Please confirm your password.
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-register btn-lg">
                                    <i class="bi bi-person-plus me-2"></i>Register
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-footer register-footer text-center">
                        <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Login here</a></p>
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
// Include the footer
include 'includes/footer.php';
?>