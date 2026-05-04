<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect('contact-messages.php');
}

// Mark as read when viewing
$conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$id]);

// Fetch message
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    redirect('contact-messages.php');
}

// Handle actions (delete, mark unread)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $conn->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Message deleted');
        redirect('contact-messages.php');
    } elseif ($_POST['action'] == 'unread') {
        $conn->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Message marked as unread');
        redirect('contact-messages.php');
    }
}

$pageTitle = 'View Message';
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-3">
            <a href="contact-messages.php" class="btn btn-dark rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Back to Messages
            </a>
        </div>
        
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Message from <?php echo escapeOutput($message['name']); ?></h5>
                <div class="btn-group btn-group-sm">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="unread">
                        <button type="submit" class="btn btn-outline-warning" title="Mark as Unread">
                            <i class="bi bi-envelope"></i> Mark Unread
                        </button>
                    </form>
                    <form method="POST" class="d-inline ms-2" onsubmit="return confirm('Delete this message?')">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4 bg-dark p-4 rounded-4 mx-0 border border-purple">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="text-muted small text-uppercase fw-bold mb-1">From</div>
                        <div class="fw-bold text-white fs-5"><?php echo escapeOutput($message['name']); ?></div>
                        <div><a href="mailto:<?php echo $message['email']; ?>" class="text-decoration-none" style="color: var(--accent-purple);"><?php echo escapeOutput($message['email']); ?></a></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Received</div>
                        <div class="fw-bold text-white"><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></div>
                        <div class="badge mt-2">ID: #<?php echo $message['id']; ?></div>
                    </div>
                </div>
                
                <h6 class="text-primary-purple mb-3 fw-bold">Subject: <?php echo escapeOutput($message['subject']); ?></h6>
                
                <div class="p-4 border border-purple rounded-4 bg-dark mt-4 text-white" style="min-height: 250px; white-space: pre-wrap; font-size: 1.1rem; line-height: 1.6;">
                    <?php echo escapeOutput($message['message']); ?>
                </div>
            </div>
            <div class="card-footer py-3 border-0">
                <div class="d-flex justify-content-between">
                    <a href="mailto:<?php echo $message['email']; ?>?subject=Re: <?php echo rawurlencode($message['subject']); ?>" class="btn btn-primary">
                        <i class="bi bi-reply"></i> Reply via Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
