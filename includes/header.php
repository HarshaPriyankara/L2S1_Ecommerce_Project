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
            <form class="nav-search" method="GET" action="index.php#products">
                <label class="sr-only" for="nav-search-input">Search products</label>
                <i class="fas fa-search"></i>
                <input id="nav-search-input" type="search" name="search" placeholder="Search products" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" aria-label="Search products"><i class="fas fa-arrow-right"></i></button>
            </form>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#products">Products</a></li> 
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="admin-menu-item">
                            <details class="admin-menu">
                                <summary>
                                    <i class="fas fa-user-shield"></i>
                                    <span>Admin</span>
                                    <i class="fas fa-chevron-down"></i>
                                </summary>
                                <div class="admin-menu-panel">
                                    <a href="admin.php"><i class="fas fa-gauge-high"></i> Dashboard</a>
                                    <a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a>
                                    <a href="admin_orders.php"><i class="fas fa-box"></i> Manage Orders</a>
                                    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
                                </div>
                            </details>
                        </li>
                    <?php endif; ?>
                    <li class="nav-group account-actions">
                        <a href="profile.php">Profile</a>
                        <a href="order_history.php">Orders</a>
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
        <?php if (!empty($_GET['cart_error'])): ?>
            <div class="cart-toast cart-toast-error" role="status" aria-live="polite">
                <div>
                    <strong>Cart not updated</strong>
                    <span><?php echo htmlspecialchars($_GET['cart_error']); ?></span>
                </div>
            </div>
        <?php endif; ?>
