<?php
$page_title = 'Categories Management - Admin';
$current_page = 'categories';
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    
    $check = $conn->prepare("SELECT COUNT(*) as cnt FROM vehicles WHERE category_id = ?");
    $check->bind_param("i", $delete_id);
    $check->execute();
    $vehicles_count = $check->get_result()->fetch_assoc()['cnt'];
    $check->close();
    
    if ($vehicles_count > 0) {
        setFlash('error', 'Cannot delete category. It has associated vehicles.');
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            setFlash('success', 'Category deleted successfully.');
        } else {
            setFlash('error', 'Failed to delete category.');
        }
        $stmt->close();
    }
    redirect('categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $type = trim($_POST['type']);
    $slug = strtolower(str_replace(' ', '-', $name));
    
    if (empty($name) || empty($type)) {
        setFlash('error', 'Name and Type are required.');
    } else {
        if ($cat_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=?, type=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $slug, $description, $type, $cat_id);
            $msg = 'Category updated successfully.';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, type, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $slug, $description, $type);
            $msg = 'Category added successfully.';
        }
        
        if ($stmt->execute()) {
            setFlash('success', $msg);
            redirect('categories.php');
        } else {
            setFlash('error', 'Database error: ' . $conn->error);
        }
        $stmt->close();
    }
}

$edit_cat = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_cat = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = $conn->query("
    SELECT c.*, COUNT(v.id) as vehicle_count 
    FROM categories c 
    LEFT JOIN vehicles v ON c.id = v.category_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
");
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
                <h1>Manage Categories</h1>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2><?php echo $edit_cat ? 'Edit Category' : 'Add New Category'; ?></h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="categories.php">
                        <input type="hidden" name="save_category" value="1">
                        <?php if($edit_cat): ?>
                        <input type="hidden" name="cat_id" value="<?php echo $edit_cat['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row" style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $edit_cat ? sanitize($edit_cat['name']) : ''; ?>" required>
                            </div>
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-control" required>
                                    <option value="two-wheeler" <?php echo ($edit_cat && $edit_cat['type'] == 'two-wheeler') ? 'selected' : ''; ?>>Two Wheeler</option>
                                    <option value="four-wheeler" <?php echo ($edit_cat && $edit_cat['type'] == 'four-wheeler') ? 'selected' : ''; ?>>Four Wheeler</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 2; min-width: 300px;">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control" value="<?php echo $edit_cat ? sanitize($edit_cat['description']) : ''; ?>">
                            </div>
                            <div class="form-group d-flex align-items-end" style="margin-top: auto; margin-bottom: 3px;">
                                <button type="submit" class="btn btn-primary"><?php echo $edit_cat ? 'Update' : 'Add'; ?> Category</button>
                                <?php if($edit_cat): ?>
                                <a href="categories.php" class="btn btn-secondary ms-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
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
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Vehicles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($categories->num_rows > 0): while($c = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo sanitize($c['id']); ?></td>
                                    <td><?php echo sanitize($c['name']); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst(sanitize($c['type'])); ?></span></td>
                                    <td><?php echo sanitize($c['description']); ?></td>
                                    <td><?php echo sanitize($c['vehicle_count']); ?></td>
                                    <td>
                                        <div class="action-buttons d-flex gap-2">
                                            <a href="categories.php?edit=<?php echo $c['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                                            <form method="POST" action="categories.php" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="6">No categories found.</td></tr>
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
