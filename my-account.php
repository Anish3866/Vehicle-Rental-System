<?php
$page_title = 'My Account - Vehicle Rental';
$current_page = 'my-account';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        setFlash('error', 'Full name is required.');
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            setFlash('success', 'Profile updated successfully.');
        } else {
            setFlash('error', 'Failed to update profile.');
        }
        $stmt->close();
        redirect('my-account.php');
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    setFlash('error', 'User not found.');
    redirect('index.php');
}

$stmt = $conn->prepare("SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_spent FROM bookings WHERE user_id = ? AND booking_status != 'cancelled'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_bookings = $summary['total_bookings'] ?? 0;
$total_spent = $summary['total_spent'] ?? 0;

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="page-header mb-4">
        <h1>My Account</h1>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4>Account Summary</h4>
                </div>
                <div class="card-body">
                    <p><strong>Member Since:</strong><br> <?php echo formatDateTime($user['created_at']); ?></p>
                    <p><strong>Total Bookings:</strong><br> <?php echo $total_bookings; ?></p>
                    <p><strong>Total Spent:</strong><br> <?php echo formatCurrency($total_spent); ?></p>
                    <hr>
                    <p><strong>Email:</strong><br> <?php echo sanitize($user['email']); ?></p>
                    <p><strong>Status:</strong><br> <span class="badge <?php echo getStatusBadgeClass($user['status']); ?>"><?php echo ucfirst($user['status']); ?></span></p>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4>Edit Profile</h4>
                </div>
                <div class="card-body">
                    <form action="my-account.php" method="POST">
                        <div class="form-group mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo sanitize($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email (Cannot be changed)</label>
                            <input type="email" id="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="<?php echo sanitize($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="3"><?php echo sanitize($user['address']); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
