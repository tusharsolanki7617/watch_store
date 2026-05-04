    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="footer-title text-primary-purple"><i class="bi bi-watch"></i> AETERNA NEXUS</h5>
                    <p class="text-white">Your trusted destination for premium watches. We offer an exquisite collection of luxury, sports, and smart watches from renowned brands worldwide.</p>
                    <div class="social-icons">
                        <a href="<?php echo getSiteSetting($conn, 'facebook_url'); ?>" class="social-icon text-primary-purple me-3" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="<?php echo getSiteSetting($conn, 'twitter_url'); ?>" class="social-icon text-primary-purple me-3" target="_blank">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="<?php echo getSiteSetting($conn, 'instagram_url'); ?>" class="social-icon text-primary-purple" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/services.php">Services</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Categories</h5>
                    <ul class="footer-links">
                        <?php 
                        $footerCategories = array_slice($categories, 0, 5);
                        foreach ($footerCategories as $category): 
                        ?>
                            <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>"><?php echo escapeOutput($category['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="footer-title">Contact Info</h5>
                    <ul class="footer-links">
                        <li><i class="bi bi-geo-alt"></i> <?php echo getSiteSetting($conn, 'site_address'); ?></li>
                        <li><i class="bi bi-telephone"></i> <?php echo getSiteSetting($conn, 'site_phone'); ?></li>
                        <li><i class="bi bi-envelope"></i> <?php echo SITE_EMAIL; ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. | Designed with <i class="bi bi-heart-fill text-danger"></i></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- jQuery Validation Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/validation.js"></script>
    
    <?php if (isset($customJS)) echo $customJS; ?>
</body>
</html>
