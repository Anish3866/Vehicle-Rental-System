<?php
$page_title = 'My Bookings - Vehicle Rental';
$current_page = 'my-bookings';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Handle Cancel Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancel_id = (int)$_POST['cancel_booking_id'];
    
    // Verify booking belongs to user and is pending/unpaid
    $stmt = $conn->prepare("SELECT vehicle_id, booking_status, payment_status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cancel_id, $user_id);
    $stmt->execute();
    $b_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($b_result && in_array($b_result['booking_status'], ['pending', 'confirmed'])) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $cancel_id);
            $stmt->execute();
            $stmt->close();
            
            // Also revert vehicle status if needed (though it should be available if booking is just pending, but let's ensure)
            $stmt = $conn->prepare("UPDATE vehicles SET status = 'available', updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $b_result['vehicle_id']);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            setFlash('success', 'Booking cancelled successfully.');
        } catch (Exception $e) {
            $conn->rollback();
            setFlash('error', 'Failed to cancel booking.');
        }
    } else {
        setFlash('error', 'Booking cannot be cancelled.');
    }
    redirect('my-bookings.php');
}

// Fetch bookings
$stmt = $conn->prepare("SELECT b.*, v.name as vehicle_name FROM bookings b JOIN vehicles v ON b.vehicle_id = v.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="page-header mb-4">
        <h1>My Bookings</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>Vehicle</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sn = 1; foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><?php echo sanitize($b['vehicle_name']); ?></td>
                                    <td><?php echo formatDate($b['start_date']); ?></td>
                                    <td><?php echo formatDate($b['end_date']); ?></td>
                                    <td><?php echo $b['rental_days']; ?></td>
                                    <td><?php echo formatCurrency($b['total_amount']); ?></td>
                                    <td><span class="badge <?php echo getStatusBadgeClass($b['booking_status']); ?>"><?php echo $b['booking_status'] === 'confirmed' ? 'Approved' : ucfirst($b['booking_status']); ?></span></td>
                                    <td>
                                        <?php if ($b['booking_status'] === 'pending' || $b['booking_status'] === 'confirmed'): ?>
                                            <form action="my-bookings.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                <input type="hidden" name="cancel_booking_id" value="<?php echo $b['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger mb-1">Cancel</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">You have no bookings yet.</p>
                    <a href="index.php" class="btn btn-primary">Browse Vehicles</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
