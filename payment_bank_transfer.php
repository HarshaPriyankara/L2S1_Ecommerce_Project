<?php
include 'includes/db.php';
require_once 'includes/checkout_helpers.php';
ayurora_require_login();

if (empty($_SESSION['checkout_details']) || ($_SESSION['checkout_details']['payment_method'] ?? '') !== 'bank_transfer') {
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

if (isset($_POST['confirm_bank_transfer'])) {
    ayurora_require_valid_csrf();

    $account_name = ayurora_clean_text($_POST['account_name'] ?? '', 80);
    $transfer_reference = ayurora_clean_text($_POST['transfer_reference'] ?? '', 80);

    if ($stock_error !== '') {
        $message = 'Please update your cart. ' . $stock_error;
    } elseif ($account_name === null) {
        $message = 'Please enter the account holder name.';
    } elseif ($transfer_reference === null) {
        $message = 'Please enter the bank transfer reference.';
    } else {
        $reference = 'BANK-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', $transfer_reference));
        $order_id = ayurora_create_order_from_checkout($conn, 'verification_pending', 'pending', $reference, $message);

        if ($order_id) {
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit();
        }
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 class="section-title">Bank Transfer</h2>

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
                <span><i class="fas fa-university"></i></span>
                <div>
                    <h3>Transfer Details</h3>
                    <p>Transfer the total amount to the account below, then enter your reference to place the order.</p>
                </div>
            </div>

            <div class="payment-info-box payment-bank-box">
                <div><span>Bank</span><strong>Commercial Bank</strong></div>
                <div><span>Account Name</span><strong>AYURORA Wellness Pvt Ltd</strong></div>
                <div><span>Account Number</span><strong>1234567890</strong></div>
                <div><span>Branch</span><strong>Colombo</strong></div>
                <div><span>Amount</span><strong>LKR <?php echo number_format((float) $summary['total'], 2); ?></strong></div>
            </div>

            <form method="POST" action="payment_bank_transfer.php">
                <?php echo ayurora_csrf_field(); ?>
                <div class="form-group">
                    <label>Account Holder Name</label>
                    <input type="text" name="account_name" class="form-control" placeholder="Name used for transfer" required>
                </div>
                <div class="form-group">
                    <label>Transfer Reference</label>
                    <input type="text" name="transfer_reference" class="form-control" placeholder="Bank slip/reference number" required>
                </div>
                <button type="submit" name="confirm_bank_transfer" class="btn btn-primary checkout-main-button">Submit Transfer Details</button>
                <a href="checkout.php" class="btn btn-outline checkout-secondary-button">Back to Checkout</a>
            </form>
        </section>

        <aside class="checkout-summary">
            <h3>Payment Summary</h3>
            <div class="checkout-summary-meta">
                <div><span>Delivery</span><span><?php echo htmlspecialchars($delivery_options[$summary['delivery_method']]['label']); ?></span></div>
                <div><span>Delivery Fee</span><span><?php echo $summary['delivery_fee'] > 0 ? 'LKR ' . number_format((float) $summary['delivery_fee'], 2) : 'Free'; ?></span></div>
                <div><span>Payment</span><span>Bank Transfer</span></div>
            </div>
            <div class="checkout-total-row">
                <span>Total</span>
                <span>LKR <?php echo number_format((float) $summary['total'], 2); ?></span>
            </div>
        </aside>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
