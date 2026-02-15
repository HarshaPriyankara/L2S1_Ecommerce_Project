<?php
include 'includes/db.php';
include 'includes/header.php';

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Redirect to prevent form resubmission
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// Handle Remove from Cart
if (isset($_GET['remove'])) {
    $id_to_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_to_remove]);
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

// Handle Update Quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $pid => $qty) {
        if ($qty == 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $qty;
        }
    }
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}
?>

<div class="container">
    <h2 class="section-title">Your Shopping Cart</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-error">Your cart is empty. <a href="index.php">Go Shop!</a></div>
    <?php else: ?>
        <form action="cart.php" method="POST">
            <div class="table-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_price = 0;
                        $cart_items = $_SESSION['cart'];
                        
                        if (count($cart_items) > 0) {
                            // Create a comma separated list of IDs for query
                            $ids = implode(',', array_keys($cart_items));
                            $sql = "SELECT * FROM products WHERE id IN ($ids)";
                            $result = $conn->query($sql);
                            
                            while ($row = $result->fetch_assoc()) {
                                $qty = $cart_items[$row['id']];
                                $subtotal = $row['price'] * $qty;
                                $total_price += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="cart-item-img">
                                            <span><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo substr(htmlspecialchars($row['description']), 0, 50) . '...'; ?></td>
                                    <td>LKR <?php echo number_format($row['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="qty[<?php echo $row['id']; ?>]" value="<?php echo $qty; ?>" min="1" style="width: 60px; padding: 5px;">
                                    </td>
                                    <td>LKR <?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="cart.php?remove=<?php echo $row['id']; ?>" class="btn-outline" style="border-color: var(--danger); color: var(--danger); padding: 0.3rem 0.8rem; border-radius: 5px;">Remove</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-total">
                Total: LKR <?php echo number_format($total_price, 2); ?>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button type="submit" name="update_cart" class="btn btn-outline">Update Cart</button>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login to Checkout</a>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
