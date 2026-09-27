<?php
$page_title = 'Dashboard - Admin';
$current_page = 'dashboard';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Statistics queries
$stats = [
    'users_total' => 0, 'users_active' => 0, 'users_disabled' => 0,
    'vehicles_total' => 0, 'vehicles_available' => 0, 'vehicles_rented' => 0, 'vehicles_maintenance' => 0,
    'bookings_total' => 0, 'bookings_pending' => 0, 'bookings_confirmed' => 0, 'bookings_completed' => 0, 'bookings_cancelled' => 0
];

// Users stats
$result = $conn->query("SELECT status, COUNT(*) as count FROM users WHERE role = 'user' GROUP BY status");
while($row = $result->fetch_assoc()) {
    $stats['users_total'] += $row['count'];
    if($row['status'] == 'active') $stats['users_active'] = $row['count'];
    if($row['status'] == 'disabled') $stats['users_disabled'] = $row['count'];
}

// Vehicles stats
$result = $conn->query("SELECT status, COUNT(*) as count FROM vehicles GROUP BY status");
while($row = $result->fetch_assoc()) {
    $stats['vehicles_total'] += $row['count'];
    if($row['status'] == 'available') $stats['vehicles_available'] = $row['count'];
    if($row['status'] == 'rented') $stats['vehicles_rented'] = $row['count'];
    if($row['status'] == 'maintenance') $stats['vehicles_maintenance'] = $row['count'];
}

// Bookings stats
$result = $conn->query("SELECT booking_status, COUNT(*) as count FROM bookings GROUP BY booking_status");
while($row = $result->fetch_assoc()) {
    $stats['bookings_total'] += $row['count'];
    if($row['booking_status'] == 'pending') $stats['bookings_pending'] = $row['count'];
    if($row['booking_status'] == 'confirmed') $stats['bookings_confirmed'] = $row['count'];
    if($row['booking_status'] == 'completed') $stats['bookings_completed'] = $row['count'];
    if($row['booking_status'] == 'cancelled') $stats['bookings_cancelled'] = $row['count'];
}

// Recent Bookings
$recent_bookings = $conn->query("
    SELECT b.*, u.full_name as user_name, v.name as vehicle_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN vehicles v ON b.vehicle_id = v.id 
    ORDER BY b.created_at DESC LIMIT 10
");

// Recent Users
$recent_users = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");

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
                <h1>Dashboard Overview</h1>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <h3>Total Users</h3>
                    <p class="stat-value"><?php echo $stats['users_total']; ?></p>
                    <p class="stat-desc">Active: <?php echo $stats['users_active']; ?> | Disabled: <?php echo $stats['users_disabled']; ?></p>
                </div>
                <div class="stat-card stat-warning">
                    <h3>Vehicles</h3>
                    <p class="stat-value"><?php echo $stats['vehicles_total']; ?></p>
                    <p class="stat-desc">Avail: <?php echo $stats['vehicles_available']; ?> | Rented: <?php echo $stats['vehicles_rented']; ?> | Maint: <?php echo $stats['vehicles_maintenance']; ?></p>
                </div>
                <div class="stat-card stat-info">
                    <h3>Bookings</h3>
                    <p class="stat-value"><?php echo $stats['bookings_total']; ?></p>
                    <p class="stat-desc">Pend: <?php echo $stats['bookings_pending']; ?> | Appr: <?php echo $stats['bookings_confirmed']; ?> | Comp: <?php echo $stats['bookings_completed']; ?></p>
                </div>
            </div>

            <div class="dashboard-tables mt-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h2>Recent Bookings</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Vehicle</th>
                                        <th>Dates</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($b = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo sanitize($b['id']); ?></td>
                                        <td><?php echo sanitize($b['user_name']); ?></td>
                                        <td><?php echo sanitize($b['vehicle_name']); ?></td>
                                        <td><?php echo formatDate($b['start_date']) . ' - ' . formatDate($b['end_date']); ?></td>
                                        <td><?php echo formatCurrency($b['total_amount']); ?></td>
                                        <td><span class="badge badge-<?php echo getStatusBadgeClass($b['booking_status']); ?>"><?php echo $b['booking_status'] === 'confirmed' ? 'Approved' : ucfirst(sanitize($b['booking_status'])); ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Recent Users</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($u = $recent_users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo sanitize($u['full_name']); ?></td>
                                        <td><?php echo sanitize($u['email']); ?></td>
                                        <td><?php echo formatDate($u['created_at']); ?></td>
                                        <td><span class="badge badge-<?php echo getStatusBadgeClass($u['status']); ?>"><?php echo ucfirst(sanitize($u['status'])); ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
