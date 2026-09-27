<?php
$page_title = 'Book Vehicle - Vehicle Rental';
$current_page = 'booking';
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

requireLogin();

$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$vehicle_id) {
    setFlash('error', 'Invalid vehicle selected.');
    redirect('index.php');
}

// Fetch vehicle
$stmt = $conn->prepare("SELECT v.*, c.name as category_name FROM vehicles v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ? AND v.status = 'available'");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$vehicle = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    setFlash('error', 'Vehicle not found or currently unavailable.');
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];

// Check user status
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_status = $stmt->get_result()->fetch_assoc()['status'] ?? '';
$stmt->close();

if ($user_status !== 'active') {
    setFlash('error', 'Your account is not active. You cannot make a booking.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    $today = date('Y-m-d');
    
    if (empty($start_date) || empty($end_date)) {
        setFlash('error', 'Please select start and end dates.');
    } elseif ($start_date < $today) {
        setFlash('error', 'Start date cannot be in the past.');
    } elseif ($end_date <= $start_date) {
        setFlash('error', 'End date must be after the start date.');
    } else {
        // Check overlapping bookings
        $stmt = $conn->prepare("SELECT id FROM bookings WHERE vehicle_id = ? AND booking_status != 'cancelled' AND start_date <= ? AND end_date >= ?");
        $stmt->bind_param("iss", $vehicle_id, $end_date, $start_date);
        $stmt->execute();
        $overlap = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($overlap) {
            setFlash('error', 'The vehicle is already booked for the selected dates.');
        } else {
            // Calculate details
            $rental_days = calculateRentalDays($start_date, $end_date);
            $price_per_day = $vehicle['price_per_day'];
            $total_amount = $rental_days * $price_per_day;

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_id, start_date, end_date, rental_days, price_per_day, total_amount, booking_status, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid', NOW())");
            $stmt->bind_param("iissidd", $user_id, $vehicle_id, $start_date, $end_date, $rental_days, $price_per_day, $total_amount);
            
            if ($stmt->execute()) {
                setFlash('success', 'Booking submitted successfully! Please wait for admin approval.');
                redirect("my-bookings.php");
            } else {
                setFlash('error', 'Failed to create booking. Please try again.');
            }
            $stmt->close();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="page-header">
        <h1>Book Vehicle: <?php echo sanitize($vehicle['name']); ?></h1>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Vehicle Details</h4>
                </div>
                <div class="card-body">
                    <?php if ($vehicle['image']): ?>
                        <img src="<?php echo getVehicleImage($vehicle['image']); ?>" alt="<?php echo sanitize($vehicle['name']); ?>" class="img-fluid rounded mb-3" style="max-height:300px; width:100%; object-fit:cover;">
                    <?php endif; ?>
                    <p><strong>Brand:</strong> <?php echo sanitize($vehicle['brand']); ?></p>
                    <p><strong>Category:</strong> <?php echo sanitize($vehicle['category_name']); ?></p>
                    <p><strong>Model Year:</strong> <?php echo sanitize($vehicle['model_year']); ?></p>
                    <p><strong>Vehicle Number:</strong> <?php echo sanitize($vehicle['vehicle_number']); ?></p>
                    <p><strong>Price Per Day:</strong> <?php echo formatCurrency($vehicle['price_per_day']); ?></p>
                    <p><strong>Description:</strong> <?php echo nl2br(sanitize($vehicle['description'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Booking Form</h4>
                </div>
                <div class="card-body">
                    <form action="booking.php?id=<?php echo $vehicle['id']; ?>" method="POST" id="bookingForm">
                        <input type="hidden" id="price_per_day" value="<?php echo (float)$vehicle['price_per_day']; ?>">
                        <input type="hidden" name="rental_days" id="rental_days" value="0">
                        <input type="hidden" name="total_amount" id="total_amount" value="0">

                        <div class="form-group mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>

                        <div class="alert alert-info" id="priceSummary" style="display:none;">
                            <p class="mb-1">Rental Days: <strong id="display_days">0</strong></p>
                            <p class="mb-0">Total Amount: <strong id="display_total">Rs. 0.00</strong></p>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const pricePerDay = parseFloat(document.getElementById('price_per_day').value);
    const rentalDaysInput = document.getElementById('rental_days');
    const totalAmountInput = document.getElementById('total_amount');
    const priceSummary = document.getElementById('priceSummary');
    const displayDays = document.getElementById('display_days');
    const displayTotal = document.getElementById('display_total');

    function calculateRentalPrice() {
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);

        if (!isNaN(start.getTime()) && !isNaN(end.getTime()) && end > start) {
            const timeDiff = Math.abs(end.getTime() - start.getTime());
            const diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            const total = diffDays * pricePerDay;

            rentalDaysInput.value = diffDays;
            totalAmountInput.value = total;
            
            displayDays.textContent = diffDays;
            displayTotal.textContent = 'Rs. ' + total.toFixed(2);
            priceSummary.style.display = 'block';
        } else {
            rentalDaysInput.value = 0;
            totalAmountInput.value = 0;
            priceSummary.style.display = 'none';
        }
    }

    startDateInput.addEventListener('change', function() {
        if(startDateInput.value) {
            let nextDay = new Date(startDateInput.value);
            nextDay.setDate(nextDay.getDate() + 1);
            let nextDayString = nextDay.toISOString().split('T')[0];
            endDateInput.min = nextDayString;
        }
        calculateRentalPrice();
    });
    
    endDateInput.addEventListener('change', calculateRentalPrice);
});
</script>

<?php require_once 'includes/footer.php'; ?>
