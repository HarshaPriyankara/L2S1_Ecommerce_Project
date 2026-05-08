<?php
include 'includes/db.php';
include 'includes/order_status.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$user_id = (int) $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$order_columns = [
    'shipping_address' => "ALTER TABLE orders ADD COLUMN shipping_address TEXT NULL AFTER status",
    'phone' => "ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL AFTER shipping_address",
    'delivery_notes' => "ALTER TABLE orders ADD COLUMN delivery_notes TEXT NULL AFTER phone",
    'delivery_method' => "ALTER TABLE orders ADD COLUMN delivery_method VARCHAR(50) NULL AFTER delivery_notes",
    'delivery_fee' => "ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0 AFTER delivery_method",
    'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL AFTER delivery_fee",
];

foreach ($order_columns as $column => $alter_sql) {
    $column_check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
    if ($column_check && $column_check->num_rows === 0) {
        $conn->query($alter_sql);
    }
}

if ($order_id <= 0) {
    header('Location: order_history.php');
    exit();
}

if ($is_admin) {
    $order_stmt = $conn->prepare(
        'SELECT o.id, o.total_price, o.status, o.shipping_address, o.phone, o.delivery_notes, o.delivery_method, o.delivery_fee, o.payment_method, o.created_at, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         INNER JOIN users u ON o.user_id = u.id
         WHERE o.id = ?
         LIMIT 1'
    );
    $order_stmt->bind_param('i', $order_id);
} else {
    $order_stmt = $conn->prepare(
        'SELECT o.id, o.total_price, o.status, o.shipping_address, o.phone, o.delivery_notes, o.delivery_method, o.delivery_fee, o.payment_method, o.created_at, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         INNER JOIN users u ON o.user_id = u.id
         WHERE o.id = ? AND o.user_id = ?
         LIMIT 1'
    );
    $order_stmt->bind_param('ii', $order_id, $user_id);
}

$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();
$order_stmt->close();

if (!$order) {
    header('Location: order_history.php');
    exit();
}

$items = [];
$item_stmt = $conn->prepare(
    'SELECT oi.quantity, oi.price, p.name, p.image
     FROM order_items oi
     LEFT JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC'
);
$item_stmt->bind_param('i', $order_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

while ($item = $item_result->fetch_assoc()) {
    $items[] = $item;
}

$item_stmt->close();

$delivery_labels = [
    'standard' => 'Standard Delivery',
    'express' => 'Express Delivery',
    'pickup' => 'Store Pickup',
];
$payment_labels = [
    'card' => 'Card Payment',
    'cod' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer',
];
$delivery_label = $delivery_labels[$order['delivery_method'] ?? ''] ?? 'Not provided';
$payment_label = $payment_labels[$order['payment_method'] ?? ''] ?? 'Not provided';

include 'includes/header.php';
?>

<div class="order-confirmation-page">
    <section class="confirmation-panel">
        <div class="confirmation-icon">
            <i class="fas fa-check"></i>
        </div>
        <span class="eyebrow">Order confirmed</span>
        <h2 class="section-title">Thank you for shopping with AYURORA.</h2>
        <p>Your order has been placed successfully. You can track it anytime from your order history.</p>

        <div class="confirmation-meta">
            <div>
                <span>Order Number</span>
                <strong>#<?php echo (int) $order['id']; ?></strong>
            </div>
            <div>
                <span>Order Date</span>
                <strong><?php echo date('F j, Y', strtotime($order['created_at'])); ?></strong>
            </div>
            <div>
                <span>Status</span>
                <strong><?php echo htmlspecialchars(ucfirst($order['status'])); ?></strong>
            </div>
            <div>
                <span>Total</span>
                <strong>LKR <?php echo number_format((float) $order['total_price'], 2); ?></strong>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="order_history.php" class="btn btn-primary">View Order History</a>
            <a href="index.php#products" class="btn btn-outline">Continue Shopping</a>
        </div>
    </section>

    <section class="shipping-summary">
        <h3>Order Progress</h3>
        <?php render_order_status_tracker($order['status']); ?>
    </section>

    <section class="shipping-summary">
        <h3>Delivery Details</h3>
        <div class="shipping-summary-grid">
            <div>
                <span>Shipping Address</span>
                <strong><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?: 'Not provided')); ?></strong>
            </div>
            <div>
                <span>Phone Number</span>
                <strong><?php echo htmlspecialchars($order['phone'] ?: 'Not provided'); ?></strong>
            </div>
            <div>
                <span>Delivery Notes</span>
                <strong><?php echo nl2br(htmlspecialchars($order['delivery_notes'] ?: 'No notes')); ?></strong>
            </div>
            <div>
                <span>Delivery Method</span>
                <strong><?php echo htmlspecialchars($delivery_label); ?><br><?php echo (float) $order['delivery_fee'] > 0 ? 'LKR ' . number_format((float) $order['delivery_fee'], 2) : 'Free'; ?></strong>
            </div>
            <div>
                <span>Payment Method</span>
                <strong><?php echo htmlspecialchars($payment_label); ?></strong>
            </div>
        </div>
    </section>

    <section class="order-card">
        <div class="order-card-header">
            <div>
                <span class="order-number">Order #<?php echo (int) $order['id']; ?></span>
                <span class="order-date"><?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?></span>
                <?php if ($is_admin): ?>
                    <span class="customer-line">
                        <?php echo htmlspecialchars($order['customer_name']); ?> &middot;
                        <?php echo htmlspecialchars($order['customer_email']); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="order-card-summary">
                <span class="status-badge status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>">
                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                </span>
                <strong>LKR <?php echo number_format((float) $order['total_price'], 2); ?></strong>
            </div>
        </div>

        <div class="order-items">
            <?php foreach ($items as $item): ?>
                <?php
                $product_name = $item['name'] ?: 'Product no longer available';
                $item_total = (float) $item['price'] * (int) $item['quantity'];
                ?>
                <div class="order-item">
                    <img src="<?php echo htmlspecialchars(product_image_path($item['image'] ?? '')); ?>" alt="<?php echo htmlspecialchars($product_name); ?>">
                    <div class="order-item-details">
                        <h3><?php echo htmlspecialchars($product_name); ?></h3>
                        <span>Qty <?php echo (int) $item['quantity']; ?> x LKR <?php echo number_format((float) $item['price'], 2); ?></span>
                    </div>
                    <strong>LKR <?php echo number_format($item_total, 2); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
