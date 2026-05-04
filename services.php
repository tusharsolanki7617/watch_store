<?php
$pageTitle = 'Services';
include 'includes/header.php';
?>

<div class="hero-section text-center py-5 mb-5 overflow-hidden">
    <div class="container fade-in-up">
        <h1 class="hero-title text-white">Exclusive <span class="text-primary-purple">Services</span></h1>
        <p class="hero-subtitle mx-auto">Premium care for your luxury investment</p>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4 reveal">
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-truck h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Free Shipping</h4>
                <p class="text-muted small mb-0">Enjoy complimentary shipping on all orders over ₹<?php echo getSiteSetting($conn, 'free_shipping_min'); ?>. Secure, insured delivery across India.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-arrow-clockwise h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Easy Returns</h4>
                <p class="text-muted small mb-0">7-day hassle-free return policy. If you aren't completely satisfied, we'll make it right with zero questions asked.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-shield-check h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Safe Payments</h4>
                <p class="text-muted small mb-0">100% encrypted checkout process. We partner with India's leading payment gateways for absolute security.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-headset h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">24/7 Support</h4>
                <p class="text-muted small mb-0">Our horological experts are available round the clock to assist you with selection, tracking, or technical queries.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-award h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Warranty Guarantee</h4>
                <p class="text-muted small mb-0">Official manufacturer warranty on all pieces. We handle the paperwork so you can enjoy your timepiece worry-free.</p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="glass-card h-100 p-5 text-center transition-all">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4 scale-hover" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-patch-check h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">100% Authentic</h4>
                <p class="text-muted small mb-0">We guarantee the authenticity of every single watch. Direct sourcing and certification on every purchase.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>
