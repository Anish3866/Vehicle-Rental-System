<?php
$page_title = 'Bookings Management - Admin';
$current_page = 'bookings';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($booking) {
        $vehicle_id = $booking['vehicle_id'];
        
        if ($action === 'confirm') {
            $upd = $conn->prepare("UPDATE bookings SET booking_status='confirmed', updated_at=NOW() WHERE id=?");
            $upd->bind_param("i", $booking_id);
            $upd->execute();
            $upd->close();
            setFlash('success', 'Booking approved.');
        } 
        elseif ($action === 'complete') {
            $upd = $conn->prepare("UPDATE bookings SET booking_status='completed', updated_at=NOW() WHERE id=?");
            $upd->bind_param("i", $booking_id);
            $upd->execute();
            $upd->close();
            
            $updVeh = $conn->prepare("UPDATE vehicles SET status='available' WHERE id=?");
            $updVeh->bind_param("i", $vehicle_id);
            $updVeh->execute();
            $updVeh->close();
            
            setFlash('success', 'Booking marked as completed.');
        }
        elseif ($action === 'cancel') {
            $upd = $conn->prepare("UPDATE bookings SET booking_status='cancelled', updated_at=NOW() WHERE id=?");
            $upd->bind_param("i", $booking_id);
            $upd->execute();
            $upd->close();
            
            $updVeh = $conn->prepare("UPDATE vehicles SET status='available' WHERE id=?");
            $updVeh->bind_param("i", $vehicle_id);
            $updVeh->execute();
            $updVeh->close();
            
            setFlash('success', 'Booking cancelled.');
        }
    }
    redirect('bookings.php');
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where = "1=1";
if ($status_filter) {
    $where .= " AND b.booking_status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query = "
    SELECT b.*, u.full_name as user_name, v.name as vehicle_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN vehicles v ON b.vehicle_id = v.id 
    WHERE $where 
    ORDER BY b.created_at DESC
";
$bookings = $conn->query($query);
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
            
            <div class="page-header">
                <h1>Manage Bookings</h1>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="bookings.php" class="d-flex gap-2 align-items-center">
                        <label for="status">Filter by Status:</label>
                        <select name="status" id="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="pending" <?php echo $status_filter=='pending'?'selected':''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter=='confirmed'?'selected':''; ?>>Approved</option>
                            <option value="completed" <?php echo $status_filter=='completed'?'selected':''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter=='cancelled'?'selected':''; ?>>Cancelled</option>
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
                                    <th>S.N.</th>
                                    <th>User</th>
                                    <th>Vehicle</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Amount</th>
                                    <th>Booking Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sn = 1; if($bookings->num_rows > 0): while($b = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><a href="user-details.php?id=<?php echo $b['user_id']; ?>"><?php echo sanitize($b['user_name']); ?></a></td>
                                    <td><?php echo sanitize($b['vehicle_name']); ?></td>
                                    <td><?php echo formatDate($b['start_date']) . ' <br>to<br> ' . formatDate($b['end_date']); ?></td>
                                    <td><?php echo sanitize($b['rental_days']); ?></td>
                                    <td><?php echo formatCurrency($b['total_amount']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadgeClass($b['booking_status']); ?>"><?php echo $b['booking_status'] === 'confirmed' ? 'Approved' : ucfirst(sanitize($b['booking_status'])); ?></span></td>
                                    <td>
                                        <?php if(in_array($b['booking_status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" action="bookings.php" class="d-inline-block">
                                            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                            <select name="action" class="form-control form-control-sm d-inline-block w-auto" onchange="if(confirm('Are you sure?')) this.form.submit(); else this.selectedIndex = 0;">
                                                <option value="">Update...</option>
                                                <?php if($b['booking_status'] == 'pending'): ?>
                                                <option value="confirm">Approve</option>
                                                <?php endif; ?>
                                                <?php if($b['booking_status'] == 'confirmed'): ?>
                                                <option value="complete">Mark Completed</option>
                                                <?php endif; ?>
                                                <option value="cancel">Cancel Booking</option>
                                            </select>
                                        </form>
                                        <?php else: ?>
                                            <span class="text-muted">No actions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="9">No bookings found.</td></tr>
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
