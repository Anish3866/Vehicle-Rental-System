<aside class="admin-sidebar">
    <div class="sidebar-brand">
        Admin Panel
    </div>
    <nav class="sidebar-menu">
        <ul>
            <li><a href="index.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="users.php" class="<?php echo ($current_page == 'users') ? 'active' : ''; ?>">Users</a></li>
            <li><a href="vehicles.php" class="<?php echo ($current_page == 'vehicles') ? 'active' : ''; ?>">Vehicles</a></li>
            <li><a href="categories.php" class="<?php echo ($current_page == 'categories') ? 'active' : ''; ?>">Categories</a></li>
            <li><a href="bookings.php" class="<?php echo ($current_page == 'bookings') ? 'active' : ''; ?>">Bookings</a></li>
            <li><a href="reports.php" class="<?php echo ($current_page == 'reports') ? 'active' : ''; ?>">Reports</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="<?php echo SITE_URL ?? '..'; ?>/index.php" target="_blank">View Site</a>
        <a href="<?php echo SITE_URL ?? '..'; ?>/logout.php">Logout</a>
    </div>
</aside>
