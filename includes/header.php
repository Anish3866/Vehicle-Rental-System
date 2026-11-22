<?php
if (!isset($page_title)) $page_title = SITE_NAME;
if (!isset($current_page)) $current_page = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vehicle Rental System - Rent two-wheelers and four-wheelers at affordable prices in Nepal.">
    <title><?php echo sanitize($page_title); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">
                <svg class="brand-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44" fill="none">
                    <circle cx="22" cy="22" r="19" stroke="white" stroke-width="3" fill="none"/>
                    <circle cx="22" cy="22" r="5.5" stroke="white" stroke-width="2" fill="none"/>
                    <line x1="22" y1="3" x2="22" y2="16.5" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="5.5" y1="31.5" x2="17" y2="24.5" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="38.5" y1="31.5" x2="27" y2="24.5" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                <span class="brand-text">Rent<span class="brand-ride">Ride</span></span>
            </a>
            
            <button class="navbar-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
                <span class="toggle-bar"></span>
            </button>
            
            <ul class="navbar-menu" id="navMenu">
                <li><a href="<?php echo SITE_URL; ?>/index.php" class="<?php echo $current_page === 'home' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/about.php" class="<?php echo $current_page === 'about' ? 'active' : ''; ?>">About</a></li>
                <li class="dropdown">
                    <a href="<?php echo SITE_URL; ?>/vehicles.php" class="dropdown-toggle <?php echo $current_page === 'vehicles' ? 'active' : ''; ?>">
                        Vehicles <span class="dropdown-arrow">&#9662;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo SITE_URL; ?>/vehicles.php?type=two-wheeler">Two-Wheelers</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/vehicles.php?type=four-wheeler">Four-Wheelers</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/vehicles.php">All Vehicles</a></li>
                    </ul>
                </li>
                <li><a href="<?php echo SITE_URL; ?>/contact.php" class="<?php echo $current_page === 'contact' ? 'active' : ''; ?>">Contact</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle <?php echo in_array($current_page, ['account', 'bookings', 'history']) ? 'active' : ''; ?>">
                            <?php echo sanitize($_SESSION['user_name']); ?> <span class="dropdown-arrow">&#9662;</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo SITE_URL; ?>/my-account.php">My Account</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/my-bookings.php">My Bookings</a></li>
                            <?php if (isAdmin()): ?>
                                <li class="dropdown-divider"></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/index.php"><strong>Admin Dashboard</strong></a></li>
                            <?php endif; ?>
                            <li class="dropdown-divider"></li>
                            <li><a href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php" class="<?php echo $current_page === 'login' ? 'active' : ''; ?>">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary btn-sm <?php echo $current_page === 'register' ? 'active' : ''; ?>">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?php echo sanitize($flash['type']); ?>" id="flashMessage">
                <?php echo sanitize($flash['message']); ?>
                <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <main class="main-content">
