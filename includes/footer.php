<?php
/**
 * Updated Footer for LeadFlow CRM
 * Features:
 * - Glassmorphism design to match the header
 * - Responsive columns with useful links
 * - "Back to top" button
 * - Dynamic year and app version (can be defined in config)
 * - Social media icons with proper links (to be updated by admin)
 * - Closing container and body/html tags
 */
?>
    </div> <!-- .container (opened in header) -->

    <!-- Back to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow"
            style="width: 50px; height: 50px; z-index: 1000;"
            aria-label="Back to top">
        <i class="bi bi-arrow-up-short fs-4"></i>
    </button>

    <!-- Footer -->
    <footer class="bg-dark text-white-50 pt-5 pb-4 mt-5" style="background: linear-gradient(135deg, #1a1e2b 0%, #2d3748 100%);">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-lightning-charge-fill me-2 text-primary"></i>
                        <?php echo APP_NAME; ?>
                    </h5>
                    <p class="small">Streamline your lead management with our simple yet powerful CRM. Perfect for small businesses in Eswatini and beyond.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white-50 me-3"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white-50 me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white-50 me-3"><i class="bi bi-linkedin fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-github fs-5"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL ?? ''; ?>index.php" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL ?? ''; ?>views/leads.php" class="text-white-50 text-decoration-none">Leads</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL ?? ''; ?>views/clients.php" class="text-white-50 text-decoration-none">Clients</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL ?? ''; ?>profile.php" class="text-white-50 text-decoration-none">Profile</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="col-lg-2 col-md-6">
                    <h6 class="text-white mb-3">Resources</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Documentation</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Support</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Contact / Version -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white mb-3">Get in Touch</h6>
                    <p class="small"><i class="bi bi-envelope me-2"></i> support@leadflow.co.sz</p>
                    <p class="small"><i class="bi bi-telephone me-2"></i> +268 2404 1234</p>
                    <hr class="opacity-25">
                    <p class="small mb-0">
                        <i class="bi bi-code-slash me-1"></i> Version 2.0.0 
                        <span class="mx-2">|</span> 
                        <i class="bi bi-heart-fill text-danger"></i> Made in Eswatini
                    </p>
                </div>
            </div>

            <!-- Copyright Line -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <hr class="opacity-25">
                    <p class="small mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (if needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL ?? ''; ?>assets/js/script.js"></script>

    <!-- Optional: Additional scripts can be injected here via a hook -->
    <?php if (defined('FOOTER_SCRIPTS')): ?>
        <?php echo FOOTER_SCRIPTS; ?>
    <?php endif; ?>

    </body>
    </html>