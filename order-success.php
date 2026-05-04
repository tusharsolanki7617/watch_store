<?php
$pageTitle = 'Order Placed';
include 'includes/header.php';

$orderNumber = isset($_GET['id']) ? sanitizeInput($_GET['id']) : '';

if (empty($orderNumber)) {
    redirect(SITE_URL);
}
?>

<div class="container my-5 text-center reveal">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card shadow-lg py-5 border-glass">
                <div class="card-body">
                    <div class="mb-4 animate-float">
                        <i class="bi bi-check-circle-fill text-success-green" style="font-size: 6rem; filter: drop-shadow(0 0 15px rgba(0,255,136,0.4));"></i>
                    </div>
                    <h1 class="mb-3 text-white fw-bold">Order <span class="text-primary-purple">Synchronized</span></h1>
                    <p class="lead text-muted mb-4 fs-4">Your acquisition <strong class="text-white">#<?php echo escapeOutput($orderNumber); ?></strong> has been successfully processed into the Nexus.</p>
                    
                    <div class="glass-card p-4 mb-4 border-glass mx-auto" style="max-width: 500px;">
                        <p class="mb-0 text-muted">A confirmation transcript has been dispatched to your neural link (email).<br>
                        We will alert you when the delivery sequence commences.</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="<?php echo SITE_URL; ?>/my-orders.php" class="btn btn-outline-primary rounded-pill px-4">Review Order Stream</a>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary rounded-pill px-4 shadow-glow">Explore More Artefacts</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observerOptions = {
        threshold: 0.05,
        rootMargin: '0px 0px -20px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    const revealElements = document.querySelectorAll('.reveal, .reveal-stagger');
    revealElements.forEach(el => observer.observe(el));

    // Visibility Safety Fallback
    setTimeout(() => {
        revealElements.forEach(el => {
            if (!el.classList.contains('active')) {
                el.classList.add('active');
            }
        });
    }, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>
