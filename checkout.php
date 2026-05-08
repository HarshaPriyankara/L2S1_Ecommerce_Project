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
$cart_items = $_SESSION['cart'];
$cart_products = [];
$total = 0;

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

    $order_stmt = $conn->prepare('INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, ?)');
    $order_stmt->bind_param('ids', $user_id, $total, $status);

    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;
        $item_stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');

        foreach ($cart_products as $product) {
            $product_id = (int) $product['id'];
            $quantity = (int) $product['quantity'];
            $price = (float) $product['price'];
            $item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
            $item_stmt->execute();
        }

        $item_stmt->close();
        $order_stmt->close();
        unset($_SESSION['cart']);

        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit();
    }

    $message = 'Error placing order: ' . $conn->error;
    $order_stmt->close();
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Checkout</h2>

    <?php if ($message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px; background: var(--white); padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow);">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Billing Details</h3>
            <form id="checkout-form" method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" class="form-control" readonly>
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

                <button type="submit" name="place_order" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Pay LKR <?php echo number_format($total, 2); ?></button>
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
