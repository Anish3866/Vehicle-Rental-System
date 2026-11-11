<?php
$page_title = 'User Details - Admin';
$current_page = 'users';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlash('error', 'Invalid User ID.');
    redirect('users.php');
}

$user_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    setFlash('error', 'User not found.');
    redirect('users.php');
}

// User Bookings
$stmt = $conn->prepare("
    SELECT b.*, v.name as vehicle_name 
    FROM bookings b 
    JOIN vehicles v ON b.vehicle_id = v.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();

// User Transactions
$stmt = $conn->prepare("
    SELECT * FROM transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();

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
                <h1>User Details: <?php echo sanitize($user['full_name']); ?></h1>
                <a href="users.php" class="btn btn-secondary">Back to Users</a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Profile Information</h2>
                </div>
                <div class="card-body">
                    <div class="user-profile-grid">
                        <p><strong>Name:</strong> <?php echo sanitize($user['full_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo sanitize($user['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo sanitize($user['address']); ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-<?php echo getStatusBadgeClass($user['status']); ?>"><?php echo ucfirst(sanitize($user['status'])); ?></span></p>
                        <p><strong>Registered On:</strong> <?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2>Bookings History</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($bookings->num_rows > 0): while($b = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><a href="bookings.php?id=<?php echo $b['id']; ?>">#<?php echo sanitize($b['id']); ?></a></td>
                                    <td><?php echo sanitize($b['vehicle_name']); ?></td>
                                    <td><?php echo formatDate($b['start_date']); ?></td>
                                    <td><?php echo formatDate($b['end_date']); ?></td>
                                    <td><?php echo sanitize($b['rental_days']); ?></td>
                                    <td><?php echo formatCurrency($b['total_amount']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadgeClass($b['booking_status']); ?>"><?php echo ucfirst(sanitize($b['booking_status'])); ?></span></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="7">No bookings found for this user.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Transactions History</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($transactions->num_rows > 0): while($t = $transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo sanitize($t['transaction_code']); ?></td>
                                    <td><?php echo ucfirst(sanitize($t['type'])); ?></td>
                                    <td><?php echo formatCurrency($t['amount']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadgeClass($t['status']); ?>"><?php echo ucfirst(sanitize($t['status'])); ?></span></td>
                                    <td><?php echo formatDate($t['created_at']); ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5">No transactions found for this user.</td></tr>
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
