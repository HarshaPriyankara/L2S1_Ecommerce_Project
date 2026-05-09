<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_start_secure_session();

$type = $_GET['type'] ?? 'general';

$errors = [
    'product' => [
        'icon' => 'fa-leaf',
        'eyebrow' => 'Product unavailable',
        'title' => 'We could not find that product.',
        'message' => 'It may have been removed, deleted by an admin, or the link may be incorrect.',
        'primary_label' => 'Browse Products',
        'primary_url' => 'index.php#products',
        'secondary_label' => 'Go Home',
        'secondary_url' => 'index.php',
    ],
    'order' => [
        'icon' => 'fa-receipt',
        'eyebrow' => 'Order unavailable',
        'title' => 'We could not open that order.',
        'message' => 'The order may not exist, or it may belong to another account.',
        'primary_label' => 'View Order History',
        'primary_url' => 'order_history.php',
        'secondary_label' => 'Continue Shopping',
        'secondary_url' => 'index.php#products',
    ],
    'general' => [
        'icon' => 'fa-circle-info',
        'eyebrow' => 'Page unavailable',
        'title' => 'Something is not available.',
        'message' => 'The page you requested could not be found.',
        'primary_label' => 'Go Home',
        'primary_url' => 'index.php',
        'secondary_label' => 'Browse Products',
        'secondary_url' => 'index.php#products',
    ],
];

$error = $errors[$type] ?? $errors['general'];
http_response_code(404);

include 'includes/header.php';
?>

<div class="friendly-error-page">
    <section class="friendly-error-panel">
        <div class="friendly-error-icon">
            <i class="fas <?php echo htmlspecialchars($error['icon']); ?>"></i>
        </div>
        <span class="eyebrow"><?php echo htmlspecialchars($error['eyebrow']); ?></span>
        <h2><?php echo htmlspecialchars($error['title']); ?></h2>
        <p><?php echo htmlspecialchars($error['message']); ?></p>
        <div class="friendly-error-actions">
            <a href="<?php echo htmlspecialchars($error['primary_url']); ?>" class="btn btn-primary">
                <?php echo htmlspecialchars($error['primary_label']); ?>
            </a>
            <a href="<?php echo htmlspecialchars($error['secondary_url']); ?>" class="btn btn-outline">
                <?php echo htmlspecialchars($error['secondary_label']); ?>
            </a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
