<?php
$pageTitle = 'Contact Us';
include 'includes/header.php';
require_once 'includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid request');
    } else {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $subject = sanitizeInput($_POST['subject']);
        $message = sanitizeInput($_POST['message']);
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            setFlashMessage('danger', 'All fields are required');
        } elseif (!isValidEmail($email)) {
            setFlashMessage('danger', 'Invalid email format');
        } else {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $subject, $message])) {
                sendContactNotificationEmail(['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message]);
                setFlashMessage('success', 'Thank you! Your message has been sent.');
                redirect(SITE_URL . '/contact.php');
            } else {
                setFlashMessage('danger', 'Failed to send message. Please try again.');
            }
        }
    }
}
?>

<div class="hero-section text-center py-5 mb-5 overflow-hidden">
    <div class="container fade-in-up">
        <h1 class="hero-title text-white">Get In <span class="text-primary-purple">Touch</span></h1>
        <p class="hero-subtitle mx-auto">We're here to assist with your horological journey</p>
    </div>
</div>

<div class="container my-5 reveal">
    <div class="row g-5">
        <div class="col-lg-4">
            <div class="glass-card p-4 mb-4 h-100 shadow-lg">
                <h5 class="fw-bold mb-4 text-white">Contact Information</h5>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="bi bi-geo-alt h5 mb-0"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 small text-white">Our Boutique</h6>
                        <p class="text-muted small mb-0"><?php echo getSiteSetting($conn, 'site_address'); ?></p>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-4">
                    <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="bi bi-telephone h5 mb-0"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 small text-white">Phone Numbers</h6>
                        <p class="text-muted small mb-0"><?php echo getSiteSetting($conn, 'site_phone'); ?></p>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-dark border border-purple text-primary-purple shadow-glow me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="bi bi-envelope h5 mb-0"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 small text-white">Online Inquiries</h6>
                        <p class="text-muted small mb-0"><?php echo SITE_EMAIL; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="glass-card p-5 border-glass shadow-lg">
                <h4 class="fw-bold mb-4 text-white">Send a Message</h4>
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                            <input type="text" name="name" class="form-control border-glass bg-dark rounded-pill px-3 py-2 text-white" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" class="form-control border-glass bg-dark rounded-pill px-3 py-2 text-white" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Subject</label>
                        <input type="text" name="subject" class="form-control border-glass bg-dark rounded-pill px-3 py-2 text-white" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Your Message</label>
                        <textarea name="message" class="form-control border-glass bg-dark px-3 py-3 rounded-4 text-white" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="btn btn-primary btn-lg w-100 rounded-pill">
                        <i class="bi bi-send-fill me-2"></i>Dispatch Inquiry
                    </button>
                </form>
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
