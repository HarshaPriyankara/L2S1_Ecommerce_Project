<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

if (
    empty($_SESSION['checkout_details'])
    || ($_SESSION['checkout_details']['payment_method'] ?? '') !== 'card'
    || empty($_SESSION['card_payment_verification'])
) {
    header('Location: checkout.php');
    exit();
}

$message = '';
$stock_error = '';
$checkout = $_SESSION['checkout_details'];
$verification = $_SESSION['card_payment_verification'];
$summary = ayurora_load_cart_summary($conn, $_SESSION['cart'] ?? [], $checkout['delivery_method'] ?? 'standard', $stock_error);
$delivery_options = ayurora_delivery_options();
$otp_expired = time() - (int) ($verification['created_at'] ?? 0) > 300;

if (empty($summary['products'])) {
    header('Location: checkout.php');
    exit();
}

if (isset($_POST['resend_otp'])) {
    ayurora_require_valid_csrf();

    $_SESSION['card_payment_verification']['otp'] = '111111';
    $_SESSION['card_payment_verification']['created_at'] = time();
    $verification = $_SESSION['card_payment_verification'];
    $otp_expired = false;
    $message = 'A new OTP has been generated.';
}

if (isset($_POST['confirm_otp'])) {
    ayurora_require_valid_csrf();

    $otp = preg_replace('/\D/', '', $_POST['otp_code'] ?? '');

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } elseif ($otp_expired) {
        $message = 'This OTP has expired. Please request a new OTP.';
    } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {
        $message = 'Please enter the 6 digit OTP code.';
    } elseif (!hash_equals((string) ($verification['otp'] ?? ''), $otp)) {
        $message = 'Invalid OTP code. Please try again.';
    } else {
        $order_id = ayurora_create_order_from_checkout($conn, 'paid', 'processing', $verification['reference'], $message);

        if ($order_id) {
            unset($_SESSION['card_payment_verification']);
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit();
        }
    }
}

include 'includes/header.php';
?>

<div class="container payment-page payment-otp-page">
    <h2 class="section-title">Card Verification</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo isset($_POST['resend_otp']) ? 'alert-success' : 'alert-error'; ?>"><?php echo htmlspecialchars($message); ?></div>
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
                <span><i class="fas fa-shield-alt"></i></span>
                <div>
                    <h3>Enter OTP Code</h3>
                    <p>Confirm the one-time password sent for the card ending in <?php echo htmlspecialchars($verification['last_four'] ?? ''); ?>.</p>
                </div>
            </div>

            <form method="POST" action="payment_card_otp.php" class="otp-action-form">
                <?php echo ayurora_csrf_field(); ?>
                <div class="form-group">
                    <label>One-Time Password</label>
                    <input type="password" name="otp_code" class="form-control otp-input" inputmode="numeric" maxlength="6" placeholder="Enter 6 digit code" required>
                </div>
                <button type="submit" name="confirm_otp" class="btn btn-primary checkout-main-button">Confirm Payment</button>
            </form>

            <form method="POST" action="payment_card_otp.php" class="otp-action-form">
                <?php echo ayurora_csrf_field(); ?>
                <button type="submit" name="resend_otp" class="btn btn-outline checkout-secondary-button">Resend OTP</button>
            </form>

            <a href="payment_card.php" class="btn btn-outline checkout-secondary-button">Back to Card Details</a>
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
