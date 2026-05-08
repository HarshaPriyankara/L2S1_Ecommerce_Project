<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AYURORA | Premium Sri Lankan Healing</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="assets/images/ayurora-logo-small.png" alt="AYURORA logo" class="brand-logo" width="24" height="24">
                <span class="brand-name">AYURORA</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#products">Products</a></li> 
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="add_product.php">Add Product</a></li>
                        <li><a href="admin.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li class="nav-group account-actions">
                        <a href="profile.php">Profile</a>
                        <a href="logout.php" class="btn btn-outline">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="btn btn-primary">Login</a></li>
                    <li><a href="register.php" class="btn btn-outline">Register</a></li>
                <?php endif; ?>
                
                <li class="nav-group shopping-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="wishlist.php">Wishlist</a>
                    <?php endif; ?>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
    </header>
    
    <!-- Main Content Wrapper -->
    <main class="container">
        <?php if (isset($_GET['cart_added']) && $_GET['cart_added'] === '1'): ?>
            <div class="cart-toast" role="status" aria-live="polite">
                <div>
                    <strong>Added to cart</strong>
                    <span>You can keep shopping or view your cart when ready.</span>
                </div>
                <a href="cart.php">View Cart</a>
            </div>
        <?php endif; ?>
