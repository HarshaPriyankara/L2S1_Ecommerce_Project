<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

if (empty($_SESSION['checkout_details']) || ($_SESSION['checkout_details']['payment_method'] ?? '') !== 'cod') {
    header('Location: checkout.php');
    exit();
}

$message = '';
$stock_error = '';
$checkout = $_SESSION['checkout_details'];
$summary = ayurora_load_cart_summary($conn, $_SESSION['cart'] ?? [], $checkout['delivery_method'] ?? 'standard', $stock_error);
$delivery_options = ayurora_delivery_options();

if (empty($summary['products'])) {
    header('Location: checkout.php');
    exit();
}

if (isset($_POST['confirm_cod'])) {
    ayurora_require_valid_csrf();

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } else {
        $reference = 'COD-' . date('YmdHis');
        $order_id = ayurora_create_order_from_checkout($conn, 'pending', 'pending', $reference, $message);

        if ($order_id) {
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit();
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Cash on Delivery</h2>

    <?php if ($message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="checkout-layout payment-layout">
        <section class="checkout-panel">
            <div class="checkout-progress">
                <span class="is-active">Cart</span>
                <span class="is-active">Checkout</span>
                <span class="is-active">Payment</span>
                <span>Confirmation</span>
            </div>

            <div class="payment-method-header">
                <span><i class="fas fa-truck"></i></span>
                <div>
                    <h3>Confirm Delivery Payment</h3>
                    <p>You will pay the courier when your order is delivered.</p>
                </div>
            </div>

            <div class="payment-info-box">
                <strong>Delivery Address</strong>
                <p><?php echo nl2br(htmlspecialchars($checkout['shipping_address'])); ?></p>
                <strong>Phone</strong>
                <p><?php echo htmlspecialchars($checkout['phone']); ?></p>
            </div>

            <form method="POST" action="payment_cod.php">
                <?php echo ayurora_csrf_field(); ?>
                <button type="submit" name="confirm_cod" class="btn btn-primary checkout-main-button">Confirm Order - LKR <?php echo number_format((float) $summary['total'], 2); ?></button>
                <a href="checkout.php" class="btn btn-outline checkout-secondary-button">Back to Checkout</a>
            </form>
        </section>

        <aside class="checkout-summary">
            <h3>Payment Summary</h3>
            <div class="checkout-summary-meta">
                <div><span>Delivery</span><span><?php echo htmlspecialchars($delivery_options[$summary['delivery_method']]['label']); ?></span></div>
                <div><span>Delivery Fee</span><span><?php echo $summary['delivery_fee'] > 0 ? 'LKR ' . number_format((float) $summary['delivery_fee'], 2) : 'Free'; ?></span></div>
                <div><span>Payment</span><span>Cash on Delivery</span></div>
            </div>
            <div class="checkout-total-row">
                <span>Amount to Pay</span>
                <span>LKR <?php echo number_format((float) $summary['total'], 2); ?></span>
            </div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
