<?php
include 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['cart'])) {
    header('Location: index.php#products');
    exit();
}

$message = '';
$shipping_address = trim($_POST['shipping_address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$delivery_notes = trim($_POST['delivery_notes'] ?? '');
$cart_items = $_SESSION['cart'];
$cart_products = [];
$total = 0;
$stock_error = '';

$order_columns = [
    'shipping_address' => "ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER status",
    'phone' => "ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL AFTER shipping_address",
    'delivery_notes' => "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL AFTER phone",
];

foreach ($order_columns as $column => $alter_sql) {
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($column_check && $column_check->num_rows === 0) {
        $conn->query($alter_sql);
    }
}

$ids = array_map('intval', array_keys($cart_items));
$ids = array_filter($ids, function ($id) {
    return $id > 0;
});

if (empty($ids)) {
    unset($_SESSION['cart']);
    header('Location: index.php#products');
    exit();
}

$id_list = implode(',', $ids);
$product_result = $conn->query("SELECT * FROM products WHERE id IN ($id_list) AND is_deleted = 0");

while ($product = $product_result->fetch_assoc()) {
    $product_id = (int) $product['id'];
    $quantity = max(1, (int) ($cart_items[$product_id] ?? 1));
    $available_stock = (int) ($product['stock_quantity'] ?? 0);

    if ($available_stock <= 0) {
        $stock_error = htmlspecialchars($product['name']) . ' is out of stock.';
    } elseif ($quantity > $available_stock) {
        $stock_error = htmlspecialchars($product['name']) . ' only has ' . $available_stock . ' item(s) available.';
        $quantity = $available_stock;
    }

    $subtotal = (float) $product['price'] * $quantity;

    $product['quantity'] = $quantity;
    $product['subtotal'] = $subtotal;
    $cart_products[] = $product;
    $total += $subtotal;
}

if (empty($cart_products)) {
    unset($_SESSION['cart']);
    header('Location: index.php#products');
    exit();
}

if (isset($_POST['place_order'])) {
    $user_id = (int) $_SESSION['user_id'];
    $status = 'completed';

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } elseif ($shipping_address === '' || $phone === '') {
        $message = 'Shipping address and phone number are required.';
    } elseif (strlen($phone) < 7) {
        $message = 'Please enter a valid phone number.';
    } else {
        $conn->begin_transaction();
        $order_stmt = $conn->prepare('INSERT INTO orders (user_id, total_price, status, shipping_address, phone, delivery_notes) VALUES (?, ?, ?, ?, ?, ?)');
        $order_stmt->bind_param('idssss', $user_id, $total, $status, $shipping_address, $phone, $delivery_notes);

        if ($order_stmt->execute()) {
            $order_id = $conn->insert_id;
            $item_stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stock_stmt = $conn->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?');
            $stock_update_ok = true;

            foreach ($cart_products as $product) {
                $product_id = (int) $product['id'];
                $quantity = (int) $product['quantity'];
                $price = (float) $product['price'];
                $item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
                if (!$item_stmt->execute()) {
                    $stock_update_ok = false;
                    break;
                }

                $stock_stmt->bind_param('iii', $quantity, $product_id, $quantity);
                if (!$stock_stmt->execute() || $stock_stmt->affected_rows !== 1) {
                    $stock_update_ok = false;
                    break;
                }
            }

            $item_stmt->close();
            $stock_stmt->close();
            $order_stmt->close();

            if ($stock_update_ok) {
                $conn->commit();
                unset($_SESSION['cart']);

                header('Location: order_confirmation.php?order_id=' . $order_id);
                exit();
            }

            $conn->rollback();
            $message = 'Stock changed while placing your order. Please review your cart and try again.';
        } else {
            $conn->rollback();
            $message = 'Error placing order: ' . $conn->error;
            $order_stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Checkout</h2>

    <?php if ($message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($stock_error): ?>
        <div class="alert alert-error">
            <?php echo $stock_error; ?> <a href="cart.php" class="auth-link">Update your cart</a>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Shipping Details</h3>
            <form id="checkout-form" method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Shipping Address</label>
                    <textarea name="shipping_address" class="form-control" rows="4" placeholder="House number, street, city, postal code" required><?php echo htmlspecialchars($shipping_address); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="+94 77 123 4567" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Delivery Notes</label>
                    <textarea name="delivery_notes" class="form-control" rows="3" placeholder="Optional delivery instructions"><?php echo htmlspecialchars($delivery_notes); ?></textarea>
                </div>

                <div class="profile-divider"></div>
                <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Payment Details</h3>

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

                <?php if ($stock_error): ?>
                    <a href="cart.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem;">Review Cart Stock</a>
                <?php else: ?>
                    <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Pay LKR <?php echo number_format($total, 2); ?></button>
                <?php endif; ?>
            </form>
        </div>

        <div style="width: 350px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow); height: fit-content;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Order Summary</h3>
            <div style="margin-bottom: 1rem;">
                <?php foreach ($cart_products as $product): ?>
                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem;">
                        <span><?php echo htmlspecialchars($product['name']); ?> x <?php echo (int) $product['quantity']; ?></span>
                        <span>LKR <?php echo number_format((float) $product['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr style="margin: 1rem 0; border: 0; border-top: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 1.2rem; color: var(--primary-color);">
                <span>Total</span>
                <span>LKR <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
