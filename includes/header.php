<?php
// Start the session
session_start();

// Database connection and other includes can go here
// include_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paws & Hearts - <?php echo isset($pageTitle) ? $pageTitle : 'Find Your Forever Friend'; ?></title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Any additional page-specific styles can be included here -->
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo-container">
                <a href="index.php" class="logo">
                 <!-- logo will be added here !-->
                    <span>Paws & Hearts</span>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="pets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pets.php') ? 'active' : ''; ?>">Pets</a></li>
                    <li><a href="about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about-us.php') ? 'active' : ''; ?>">About Us</a></li>
                    <li><a href="contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            
            <div class="user-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="account-link">My Account</a>
                    <a href="logout.php" class="btn btn-outline">Log Out</a>
                <?php else: ?>
                    <a href="login.php" class="account-link">Log In</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <ul>
            <li><a href="index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="pets.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'pets.php') ? 'active' : ''; ?>">Pets</a></li>
            <li><a href="about-us.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about-us.php') ? 'active' : ''; ?>">About Us</a></li>
            <li><a href="contact.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="account.php">My Account</a></li>
                <li><a href="logout.php">Log Out</a></li>
            <?php else: ?>
                <li><a href="login.php">Log In</a></li>
                <li><a href="register.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <main>