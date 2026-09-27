<?php
$page_title = 'Vehicle Form - Admin';
$current_page = 'vehicles';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$vehicle = null;

if ($is_edit) {
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $vehicle = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$vehicle) {
        setFlash('error', 'Vehicle not found.');
        redirect('vehicles.php');
    }
}

$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $vehicle_number = trim($_POST['vehicle_number']);
    $category_id = (int)$_POST['category_id'];
    $brand = trim($_POST['brand']);
    $model_year = (int)$_POST['model_year'];
    $price_per_day = (float)$_POST['price_per_day'];
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $stock = (int)$_POST['stock'];
    
    $errors = [];
    
    if (empty($name) || empty($vehicle_number) || empty($category_id) || empty($price_per_day)) {
        $errors[] = "Please fill in all required fields.";
    }
    
    // Handle image upload
    $image_name = $is_edit ? $vehicle['image'] : '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $upload_result = uploadImage($_FILES['image'], $upload_dir);
        if ($upload_result !== false) {
            $image_name = $upload_result;
        } else {
            $errors[] = "Failed to upload image. Ensure it is a valid image under 5MB.";
        }
    }
    
    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $conn->prepare("UPDATE vehicles SET category_id=?, name=?, vehicle_number=?, brand=?, model_year=?, price_per_day=?, description=?, image=?, status=?, stock=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("issssdsssii", $category_id, $name, $vehicle_number, $brand, $model_year, $price_per_day, $description, $image_name, $status, $stock, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO vehicles (category_id, name, vehicle_number, brand, model_year, price_per_day, description, image, status, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issssdsssi", $category_id, $name, $vehicle_number, $brand, $model_year, $price_per_day, $description, $image_name, $status, $stock);
        }
        
        if ($stmt->execute()) {
            setFlash('success', 'Vehicle saved successfully.');
            redirect('vehicles.php');
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
    
    if (!empty($errors)) {
        setFlash('error', implode('<br>', $errors));
    }
}
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
                <h1><?php echo $is_edit ? 'Edit' : 'Add'; ?> Vehicle</h1>
                <a href="vehicles.php" class="btn btn-secondary">Cancel</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label class="form-label">Vehicle Name *</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $is_edit ? sanitize($vehicle['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Vehicle Number (License Plate) *</label>
                            <input type="text" name="vehicle_number" class="form-control" value="<?php echo $is_edit ? sanitize($vehicle['vehicle_number']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php while($c = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo ($is_edit && $vehicle['category_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo sanitize($c['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" value="<?php echo $is_edit ? sanitize($vehicle['brand']) : ''; ?>">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Model Year</label>
                            <input type="number" name="model_year" class="form-control" min="1900" max="2100" value="<?php echo $is_edit ? sanitize($vehicle['model_year']) : date('Y'); ?>">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Price Per Day (Rs) *</label>
                            <input type="number" name="price_per_day" step="0.01" class="form-control" value="<?php echo $is_edit ? sanitize($vehicle['price_per_day']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo $is_edit ? sanitize($vehicle['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Number of Stocks Available *</label>
                            <input type="number" name="stock" class="form-control" min="0" value="<?php echo $is_edit ? sanitize($vehicle['stock']) : '1'; ?>" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" <?php echo !$is_edit ? 'required' : ''; ?>>
                            <?php if($is_edit && $vehicle['image']): ?>
                                <div class="mt-2">
                                    <img src="../uploads/<?php echo sanitize($vehicle['image']); ?>" alt="Current Image" style="height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="available" <?php echo ($is_edit && $vehicle['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                <option value="rented" <?php echo ($is_edit && $vehicle['status'] == 'rented') ? 'selected' : ''; ?>>Rented</option>
                                <option value="maintenance" <?php echo ($is_edit && $vehicle['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Vehicle</button>
                    </form>
                </div>
            </div>

        </main>
    </div>
    <script src="../js/script.js"></script>
</body>
</html>
