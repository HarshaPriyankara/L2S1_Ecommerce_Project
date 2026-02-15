<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$message = '';
$order_placed = false;

if (isset($_POST['place_order'])) {
    $user_id = $_SESSION['user_id'];
    $total_price = $_POST['total_price'];
    
    // 1. Create Order
    $sql = "INSERT INTO orders (user_id, total_price, status) VALUES ('$user_id', '$total_price', 'completed')";
    if ($conn->query($sql) === TRUE) {
        $order_id = $conn->insert_id;
        
        // 2. Insert Order Items
        $cart_items = $_SESSION['cart'];
        $ids = implode(',', array_keys($cart_items));
        $item_sql = "SELECT * FROM products WHERE id IN ($ids)";
        $result = $conn->query($item_sql);
        
        while ($row = $result->fetch_assoc()) {
            $pid = $row['id'];
            $qty = $cart_items[$pid];
            $price = $row['price'];
            
            $insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')";
            $conn->query($insert_item);
        }
        
        // 3. Clear Cart
        unset($_SESSION['cart']);
        $order_placed = true;
    } else {
        $message = "Error placing order: " . $conn->error;
    }
}
?>

<div class="container">
    <?php if ($order_placed): ?>
        <div style="text-align: center; padding: 4rem 2rem; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow);">
            <i class="fas fa-check-circle" style="font-size: 5rem; color: var(--success); margin-bottom: 2rem;"></i>
            <h2 class="section-title">Order Placed Successfully!</h2>
            <p>Thank you for shopping with PosMini Ayurveda. Your healthy life starts here.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 2rem;">Continue Shopping</a>
        </div>
    <?php else: ?>
        <h2 class="section-title">Checkout</h2>
        
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Billing Details</h3>
                <form id="checkout-form" method="POST" action="">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo $_SESSION['name']; ?>" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label>Card Number (Mock Payment)</label>
                        <input type="text" placeholder="XXXX XXXX XXXX XXXX" class="form-control" required>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Expiry</label>
                            <input type="text" placeholder="MM/YY" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>CVV</label>
                            <input type="text" placeholder="123" class="form-control" required>
                        </div>
                    </div>
                    
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $pid => $qty) {
                        $res = $conn->query("SELECT price FROM products WHERE id = $pid");
                        $row = $res->fetch_assoc();
                        $total += $row['price'] * $qty;
                    }
                    ?>
                    <input type="hidden" name="total_price" value="<?php echo $total; ?>">
                    
                    <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Pay LKR <?php echo number_format($total, 2); ?></button>
                </form>
            </div>
            
            <div style="width: 350px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow); height: fit-content;">
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Order Summary</h3>
                <div style="margin-bottom: 1rem;">
                    <?php
                    $cart_items = $_SESSION['cart'];
                     $ids = implode(',', array_keys($cart_items));
                     $sql = "SELECT * FROM products WHERE id IN ($ids)";
                     $result = $conn->query($sql);
                     
                     while ($row = $result->fetch_assoc()) {
                         $qty = $cart_items[$row['id']];
                         echo "<div style='display: flex; justify-content: space-between; margin-bottom: 0.5rem;'>";
                         echo "<span>" . htmlspecialchars($row['name']) . " x $qty</span>";
                         echo "<span>LKR " . number_format($row['price'] * $qty, 2) . "</span>";
                         echo "</div>";
                     }
                    ?>
                </div>
                <hr style="margin: 1rem 0; border: 0; border-top: 1px solid #eee;">
                <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem; color: var(--primary-color);">
                    <span>Total</span>
                    <span>LKR <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
