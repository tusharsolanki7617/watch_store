<?php
$pageTitle = 'About Us';
include 'includes/header.php';
?>

<div class="hero-section text-center py-5 mb-5 overflow-hidden">
    <div class="container fade-in-up">
        <h1 class="hero-title text-white">Timeless <span class="text-primary-purple">Vision</span></h1>
        <p class="hero-subtitle mx-auto">Your Trusted Partner in Luxury Timepieces Since 2010</p>
    </div>
</div>

<div class="container my-5">
    <div class="row align-items-center mb-5 reveal">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="section-title">Our <span class="text-primary-purple">Story</span></h2>
            <p class="lead fw-bold text-primary-purple">Crafting Time, Creating Memories</p>
            <p class="text-muted">Founded with a passion for exceptional timepieces, <?php echo SITE_NAME; ?> has been serving watch enthusiasts for years. We believe that a watch is more than just a time-telling device – it's a statement of style, a symbol of achievement, and a companion for life's most precious moments.</p>
            <p class="text-muted">Our carefully curated collection features watches from the world's most prestigious brands, ensuring that every piece meets our rigorous standards of quality, craftsmanship, and design.</p>
        </div>
        <div class="col-lg-6">
            <div class="glass-card p-3 shadow-lg">
                <img src="<?php echo SITE_URL; ?>/assets/images/1.webp" alt="About Us" class="img-fluid rounded hero-watch-img">
            </div>
        </div>
    </div>
    
    <div class="row mb-5 g-4 reveal">
        <div class="col-md-6">
            <div class="glass-card h-100 p-5 text-center">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-bullseye h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Our Mission</h4>
                <p class="text-muted">To provide our customers with the finest selection of watches, exceptional service, and an unparalleled shopping experience. We strive to make luxury accessible and ensure that every customer finds their perfect timepiece.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card h-100 p-5 text-center">
                <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow mx-auto mb-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-eye h2 mb-0"></i>
                </div>
                <h4 class="fw-bold text-white">Our Vision</h4>
                <p class="text-muted">To become the most trusted and preferred destination for watch enthusiasts worldwide, known for our authenticity, expertise, and commitment to customer satisfaction.</p>
            </div>
        </div>
    </div>
    
    <div class="glass-card p-5 mb-5 reveal">
        <h3 class="text-center fw-bold mb-5 text-white">Why Choose <span class="text-primary-purple">Us?</span></h3>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <i class="bi bi-patch-check display-5 text-primary-purple mb-3 d-block shadow-glow"></i>
                <h6 class="fw-bold text-white">100% Authentic</h6>
                <p class="small text-muted mb-0">Certified genuine products</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-shield-check display-5 text-primary-purple mb-3 d-block shadow-glow"></i>
                <h6 class="fw-bold text-white">Warranty Proof</h6>
                <p class="small text-muted mb-0">Protective global coverage</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-truck display-5 text-primary-purple mb-3 d-block shadow-glow"></i>
                <h6 class="fw-bold text-white">Global Shipping</h6>
                <p class="small text-muted mb-0">Fast & secure delivery</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-headset display-5 text-primary-purple mb-3 d-block shadow-glow"></i>
                <h6 class="fw-bold text-white">Expert Guide</h6>
                <p class="small text-muted mb-0">24/7 specialist support</p>
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
