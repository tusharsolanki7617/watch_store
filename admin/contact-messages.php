<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['bulk_action']) && isset($_POST['message_ids'])) {
        $action = $_POST['bulk_action'];
        $ids = $_POST['message_ids'];
        
        if (!empty($ids)) {
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            
            if ($action == 'mark_read') {
                $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                setFlashMessage('success', count($ids) . ' messages marked as read');
            } elseif ($action == 'mark_unread') {
                $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 0 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                setFlashMessage('success', count($ids) . ' messages marked as unread');
            } elseif ($action == 'delete') {
                $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                setFlashMessage('success', count($ids) . ' messages deleted');
            }
        }
    }
    redirect('contact-messages.php');
}

// Filters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$query = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($status == 'unread') {
    $query .= " AND is_read = 0";
} elseif ($status == 'read') {
    $query .= " AND is_read = 1";
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$pageTitle = 'Contact Messages';
include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="btn-group">
            <a href="?status=all" class="btn btn-outline-primary <?php echo $status == 'all' ? 'active' : ''; ?>">All</a>
            <a href="?status=unread" class="btn btn-outline-primary <?php echo $status == 'unread' ? 'active' : ''; ?>">
                Unread 
                <?php
                $uCountStmt = $conn->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
                $uCount = $uCountStmt->fetchColumn();
                if ($uCount > 0) echo "($uCount)";
                ?>
            </a>
            <a href="?status=read" class="btn btn-outline-primary <?php echo $status == 'read' ? 'active' : ''; ?>">Read</a>
        </div>
    </div>
    <div class="col-md-4">
        <form method="GET" class="d-flex">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?php echo escapeOutput($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<form method="POST" id="bulkActionForm">
<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <h2 class="fw-bold">Contact <span class="text-primary-purple">Terminal</span></h2>
    </div>

    <div class="card border-0 shadow-lg">
        <div class="card-header py-4 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold small text-uppercase text-primary-purple"><i class="bi bi-envelope me-2"></i>Inbound Transmissions</h5>
                <div class="d-flex gap-2">
                    <select name="bulk_action" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Bulk Actions</option>
                        <option value="mark_read">Mark as Read</option>
                        <option value="mark_unread">Mark as Unread</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirmAction()">Apply</button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th width="100">Status</th>
                            <th width="100" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No messages found.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                            <tr class="<?php echo $msg['is_read'] ? '' : 'bg-dark fw-bold'; ?>" style="<?php echo $msg['is_read'] ? '' : 'background: rgba(157, 78, 221, 0.05) !important;'; ?>">
                                <td class="text-center">
                                    <input type="checkbox" name="message_ids[]" value="<?php echo $msg['id']; ?>" class="form-check-input msg-check">
                                </td>
                                <td>
                                    <div><?php echo escapeOutput($msg['name']); ?></div>
                                    <small class="text-muted"><?php echo escapeOutput($msg['email']); ?></small>
                                </td>
                                <td>
                                    <a href="view-message.php?id=<?php echo $msg['id']; ?>" class="text-decoration-none text-white fw-bold">
                                        <?php echo escapeOutput($msg['subject']); ?>
                                    </a>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($msg['is_read']): ?>
                                        <span class="badge bg-secondary rounded-pill px-3">Read</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-purple rounded-pill px-3 shadow-glow">New</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="view-message.php?id=<?php echo $msg['id']; ?>" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteMessage(<?php echo $msg['id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('checkAll').addEventListener('change', function() {
    const checks = document.querySelectorAll('.msg-check');
    checks.forEach(check => check.checked = this.checked);
});

function confirmAction() {
    const action = document.querySelector('select[name="bulk_action"]').value;
    const checked = document.querySelectorAll('.msg-check:checked').length;
    
    if (checked === 0) {
        alert('Please select at least one message.');
        return false;
    }
    
    if (action === '') {
        alert('Please select an action.');
        return false;
    }
    
    if (action === 'delete') {
        return confirm('Are you sure you want to delete ' + checked + ' messages? This cannot be undone.');
    }
    
    return true;
}

function deleteMessage(id) {
    if (confirm('Are you sure you want to delete this message?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="bulk_action" value="delete">
            <input type="hidden" name="message_ids[]" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
