<?php
$page_title = 'Vehicles Management - Admin';
$current_page = 'vehicles';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    
    // Check for active bookings
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE vehicle_id = ? AND booking_status IN ('pending', 'confirmed')");
    $check->bind_param("i", $delete_id);
    $check->execute();
    $active = $check->get_result()->fetch_assoc()['cnt'];
    $check->close();
    
    if ($active > 0) {
        setFlash('error', 'Cannot delete vehicle. It has active bookings.');
    } else {
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            setFlash('success', 'Vehicle deleted successfully.');
        } else {
            setFlash('error', 'Failed to delete vehicle.');
        }
        $stmt->close();
    }
    redirect('vehicles.php');
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = "1=1";
if ($status_filter) {
    $where .= " AND v.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query = "SELECT v.*, c.name as category_name 
          FROM vehicles v 
          LEFT JOIN categories c ON v.category_id = c.id 
          WHERE $where 
          ORDER BY v.created_at DESC";
$vehicles = $conn->query($query);
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
                <h1>Manage Vehicles</h1>
                <a href="vehicle-form.php" class="btn btn-primary">Add Vehicle</a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="vehicles.php" class="d-flex gap-2 align-items-center">
                        <label for="status">Filter by Status:</label>
                        <select name="status" id="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="available" <?php echo $status_filter=='available'?'selected':''; ?>>Available</option>
                            <option value="rented" <?php echo $status_filter=='rented'?'selected':''; ?>>Rented</option>
                            <option value="maintenance" <?php echo $status_filter=='maintenance'?'selected':''; ?>>Maintenance</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Number</th>
                                    <th>Category</th>
                                    <th>Price/Day</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($vehicles->num_rows > 0): while($v = $vehicles->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo sanitize($v['id']); ?></td>
                                    <td>
                                        <?php if($v['image']): ?>
                                            <img src="../uploads/<?php echo sanitize($v['image']); ?>" alt="vehicle" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No Img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo sanitize($v['name']); ?></td>
                                    <td><?php echo sanitize($v['vehicle_number']); ?></td>
                                    <td><?php echo sanitize($v['category_name']); ?></td>
                                    <td><?php echo formatCurrency($v['price_per_day']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadgeClass($v['status']); ?>"><?php echo ucfirst(sanitize($v['status'])); ?></span></td>
                                    <td>
                                        <div class="action-buttons d-flex gap-2">
                                            <a href="vehicle-form.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="vehicles.php" class="d-inline" onsubmit="return confirm('Delete this vehicle?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $v['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="8">No vehicles found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
