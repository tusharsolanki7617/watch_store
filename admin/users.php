<?php
require_once '../includes/config.php';

if (!isAdminLoggedIn()) {
    redirect('../admin/login.php');
}

$admin = getCurrentAdmin($conn);

// Handle Actions (Activate/Deactivate/Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['toggle_status'])) {
        $userId = (int)$_POST['user_id'];
        $newStatus = (int)$_POST['new_status'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            
            $statusMsg = $newStatus ? 'activated' : 'deactivated';
            setFlashMessage('success', "User successfully $statusMsg.");
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error updating status: ' . $e->getMessage());
        }
        redirect('users.php');
    }
    
    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        
        try {
            // Check if user has orders
            $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
            $orderCount = $stmt->fetchColumn();
            
            if ($orderCount > 0) {
                 setFlashMessage('warning', 'Cannot delete user with existing orders. Deactivate them instead.');
            } else {
                // Delete
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                setFlashMessage('success', 'User deleted successfully.');
            }
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error deleting user: ' . $e->getMessage());
        }
        redirect('users.php');
    }
}

// Get Users
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sql = "SELECT * FROM users";
$params = [];

if ($search) {
    $sql .= " WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
include 'includes/header.php';
?>

<div class="card border-0 shadow-lg">
    <div class="card-header py-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary-purple small text-uppercase"><i class="bi bi-people me-2"></i>Client Intelligence</h5>
        <form class="d-flex" method="GET">
            <input class="form-control me-2" type="search" name="search" placeholder="Search users..." value="<?php echo escapeOutput($search); ?>">
            <button class="btn btn-outline-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Joined At</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        // Get basic stats
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                        $stmt->execute([$user['id']]);
                        $ordersCount = $stmt->fetchColumn();
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/uploads/profiles/<?php echo $user['profile_image']; ?>" class="rounded-circle me-2 border border-purple" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-dark d-flex align-items-center justify-content-center me-2 border border-purple" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-primary-purple"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-white"><?php echo escapeOutput($user['full_name']); ?></div>
                                    <div class="small text-muted">ID: #<?php echo $user['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><i class="bi bi-envelope small me-1"></i> <?php echo escapeOutput($user['email']); ?></div>
                            <?php if ($user['phone']): ?>
                            <div><i class="bi bi-telephone small me-1"></i> <?php echo escapeOutput($user['phone']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-primary-purple rounded-pill"><?php echo $ordersCount; ?></span>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                             <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <?php if ($user['is_active']): ?>
                                        <input type="hidden" name="new_status" value="0">
                                        <button type="submit" name="toggle_status" class="btn btn-dark btn-sm px-3" title="Deactivate" onclick="return confirm('Deactivate this user?')">
                                            <i class="bi bi-slash-circle text-warning"></i>
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="1">
                                        <button type="submit" name="toggle_status" class="btn btn-dark btn-sm px-3" title="Activate">
                                            <i class="bi bi-check-circle text-success"></i>
                                        </button>
                                    <?php endif; ?>
                                </form>
                                
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-dark btn-sm px-3 border-start" style="border-color: var(--border-color) !important;" title="Delete">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
