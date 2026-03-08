<?php
// Start session to check login status
session_start();

// Include configuration and helper functions
require_once 'config/config.php';
require_once 'includes/functions.php';

// Include the header (navbar and opening HTML tags)
include 'includes/header.php';
?>

<!-- Additional inline styles to keep the landing page look (can also be moved to style.css) -->
<style>
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .feature-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 1rem;
    }
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 1rem 2rem rgba(0,0,0,0.15);
    }
    .btn-light {
        background-color: white;
        color: #667eea;
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        border-radius: 2rem;
        transition: all 0.3s;
    }
    .btn-light:hover {
        background-color: #f8f9fa;
        color: #5a67d8;
        transform: scale(1.05);
    }
</style>

<!-- Hero Section -->
<section class="gradient-bg min-vh-100 d-flex align-items-center">
    <div class="container text-center py-5">
        <h1 class="display-3 fw-bold mb-4">Streamline Your Lead Management</h1>
        <p class="lead fs-3 mb-5">Track, nurture, and convert more leads with <?php echo APP_NAME; ?> – the simple CRM built for small businesses.</p>
        <a href="login.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 me-2 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Get Started
        </a>
        <a href="#features" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 mb-3">
            <i class="bi bi-arrow-down-circle me-2"></i>Learn More
        </a>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose <?php echo APP_NAME; ?>?</h2>
            <p class="lead text-muted">Everything you need to manage your leads effectively.</p>
        </div>
        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Centralized Leads</h5>
                        <p class="card-text text-muted">All your leads in one place – name, email, source, and status at a glance.</p>
                    </div>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-repeat text-success" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Status Tracking</h5>
                        <p class="card-text text-muted">Easily move leads from New → Contacted → Converted with a single click.</p>
                    </div>
                </div>
            </div>
            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-chat-dots-fill text-warning" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Follow‑up Notes</h5>
                        <p class="card-text text-muted">Keep a history of every interaction – never miss a follow‑up again.</p>
                    </div>
                </div>
            </div>
            <!-- Feature 4 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Secure & Private</h5>
                        <p class="card-text text-muted">Your data is protected with secure authentication and encrypted passwords.</p>
                    </div>
                </div>
            </div>
            <!-- Feature 5 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-bar-chart-line-fill text-info" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Quick Insights</h5>
                        <p class="card-text text-muted">Dashboard shows total leads, new leads, and conversions at a glance.</p>
                    </div>
                </div>
            </div>
            <!-- Feature 6 -->
            <div class="col-md-4">
                <div class="card feature-card h-100 p-4">
                    <div class="card-body text-center">
                        <i class="bi bi-phone-fill text-secondary" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 fw-bold">Mobile Friendly</h5>
                        <p class="card-text text-muted">Manage your leads on the go – fully responsive design.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="gradient-bg py-5">
    <div class="container text-center py-4">
        <h2 class="display-5 fw-bold mb-4">Ready to take control of your leads?</h2>
        <p class="lead fs-4 mb-4">Join hundreds of small businesses using <?php echo APP_NAME; ?> to grow.</p>
        <a href="login.php" class="btn btn-light btn-lg rounded-pill px-5 py-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login Now
        </a>
    </div>
</section>

<?php
// Include the footer (closing tags and scripts)
include 'includes/footer.php';
?>