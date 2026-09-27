<?php
$page_title = 'Users Management - Admin';
$current_page = 'users';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = (int)$_POST['delete_user_id'];
    
    $conn->begin_transaction();
    try {
        // 1. Delete transactions linked to this user
        $stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete payments linked to this user
        $stmt = $conn->prepare("DELETE FROM payments WHERE user_id = ?");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        $stmt->close();

        // 3. Reset vehicles that were rented by this user back to available
        $stmt = $conn->prepare("UPDATE vehicles SET status = 'available', updated_at = NOW() WHERE id IN (SELECT vehicle_id FROM bookings WHERE user_id = ? AND booking_status IN ('pending', 'confirmed'))");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        $stmt->close();

        // 4. Delete bookings linked to this user
        $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        $stmt->close();

        // 5. Delete the user (but not admins)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $delete_user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            setFlash('success', "User and all associated records deleted successfully.");
        } else {
            $conn->rollback();
            setFlash('error', "Failed to delete user. Admin accounts cannot be deleted.");
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        setFlash('error', "Failed to delete user: " . $e->getMessage());
    }
    redirect('users.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if (in_array($action, ['active', 'disabled'])) {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("si", $action, $user_id);
        
        if ($stmt->execute()) {
            setFlash('success', "User status updated to " . ucfirst($action) . ".");
        } else {
            setFlash('error', "Failed to update user status.");
        }
        $stmt->close();
    }
    redirect('users.php');
}

$users = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <?php require_once 'includes/admin-sidebar.php'; ?>
        <main class="admin-content">
            <?php $flash = getFlash(); if($flash): ?>
            <div class="alert alert-<?php echo sanitize($flash['type']); ?>">
                <?php echo sanitize($flash['message']); ?>
                <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
            <?php endif; ?>
            
            <div class="page-header d-flex justify-content-between align-items-center">
                <h1>Manage Users</h1>
                <div class="search-box">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search users..." onkeyup="filterTable('usersTable', this.value)">
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>S.N.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sn = 1; while($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo sanitize($u['full_name']); ?></td>
                                    <td><?php echo sanitize($u['email']); ?></td>
                                    <td><?php echo sanitize($u['phone']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadgeClass($u['status']); ?>"><?php echo ucfirst(sanitize($u['status'])); ?></span></td>
                                    <td><?php echo formatDate($u['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons d-flex gap-2">
                                            <a href="user-details.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <form method="POST" action="users.php" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <?php if($u['status'] === 'active'): ?>
                                                    <input type="hidden" name="action" value="disabled">
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Disable this user?');">Disable</button>
                                                <?php else: ?>
                                                    <input type="hidden" name="action" value="active">
                                                    <button type="submit" class="btn btn-sm btn-success">Enable</button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" action="users.php" class="d-inline" onsubmit="return confirm('Delete this user permanently?');">
                                                <input type="hidden" name="delete_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
    <script src="../js/script.js"></script>
    <script>
        function filterTable(tableId, query) {
            query = query.toLowerCase();
            const rows = document.getElementById(tableId).querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
