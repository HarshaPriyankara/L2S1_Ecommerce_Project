<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

if (empty($_SESSION['checkout_details']) || ($_SESSION['checkout_details']['payment_method'] ?? '') !== 'card') {
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

if (isset($_POST['pay_card'])) {
    ayurora_require_valid_csrf();

    $card_name = ayurora_clean_text($_POST['card_name'] ?? '', 80);
    $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } elseif ($card_name === null) {
        $message = 'Please enter the cardholder name.';
    } elseif (!preg_match('/^[0-9]{13,19}$/', $card_number)) {
        $message = 'Please enter a valid card number.';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiry)) {
        $message = 'Please enter expiry date as MM/YY.';
    } elseif (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $message = 'Please enter a valid CVV.';
    } else {
        $_SESSION['card_payment_verification'] = [
            'otp' => '111111',
            'reference' => 'CARD-' . date('YmdHis') . '-' . substr($card_number, -4),
            'last_four' => substr($card_number, -4),
            'created_at' => time(),
        ];

        header('Location: payment_card_otp.php');
        exit();
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Card Payment</h2>

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
                <span><i class="fas fa-credit-card"></i></span>
                <div>
                    <h3>Secure Card Details</h3>
                    <p>Enter your card details to complete the payment securely.</p>
                </div>
            </div>

            <form method="POST" action="payment_card.php">
                <?php echo ayurora_csrf_field(); ?>
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" name="card_name" class="form-control" placeholder="Name on card" required>
                </div>
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" class="form-control" inputmode="numeric" autocomplete="cc-number" placeholder="4111 1111 1111 1111" required>
                </div>
                <div class="payment-field-row">
                    <div class="form-group">
                        <label>Expiry</label>
                        <input type="text" name="expiry" class="form-control" placeholder="MM/YY" required>
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="password" name="cvv" class="form-control" inputmode="numeric" placeholder="123" required>
                    </div>
                </div>
                <button type="submit" name="pay_card" class="btn btn-primary checkout-main-button">Pay LKR <?php echo number_format((float) $summary['total'], 2); ?></button>
                <a href="checkout.php" class="btn btn-outline checkout-secondary-button">Back to Checkout</a>
            </form>
        </section>

        <aside class="checkout-summary">
            <h3>Payment Summary</h3>
            <div class="checkout-summary-meta">
                <div><span>Delivery</span><span><?php echo htmlspecialchars($delivery_options[$summary['delivery_method']]['label']); ?></span></div>
                <div><span>Delivery Fee</span><span><?php echo $summary['delivery_fee'] > 0 ? 'LKR ' . number_format((float) $summary['delivery_fee'], 2) : 'Free'; ?></span></div>
                <div><span>Payment</span><span>Card Payment</span></div>
            </div>
            <div class="checkout-total-row">
                <span>Total</span>
                <span>LKR <?php echo number_format((float) $summary['total'], 2); ?></span>
            </div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
