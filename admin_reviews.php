<?php
include 'includes/db.php';
require_once 'includes/security.php';
ayurora_require_admin();

$message = '';
$error = '';

if (isset($_POST['delete_review'])) {
    ayurora_require_valid_csrf();

    $review_id = ayurora_int_input($_POST['review_id'] ?? null);

    if ($review_id === null) {
        $error = 'Invalid review selected.';
    } else {
        $delete_stmt = $conn->prepare('DELETE FROM reviews WHERE id = ?');
        $delete_stmt->bind_param('i', $review_id);

        if ($delete_stmt->execute()) {
            $message = 'Review deleted successfully.';
        } else {
            $error = 'Could not delete review. Please try again.';
        }

        $delete_stmt->close();
    }
}

$reviews = [];
$reviews_sql = "
    SELECT r.id, r.rating, r.comment, r.created_at,
           u.name AS user_name, u.email AS user_email,
           p.id AS product_id, p.name AS product_name, p.image AS product_image
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.id
    INNER JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC, r.id DESC
";
$reviews_result = $conn->query($reviews_sql);

while ($review = $reviews_result->fetch_assoc()) {
    $reviews[] = $review;
}

include 'includes/header.php';
?>

<div class="admin-reviews-page">
    <div class="order-history-header">
        <div>
            <span class="eyebrow">Admin</span>
            <h2 class="section-title">Review Moderation</h2>
        </div>
        <a href="admin.php" class="btn btn-outline">Product Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <div class="empty-state">
            <i class="fas fa-star-half-stroke"></i>
            <h3>No reviews yet</h3>
            <p>Customer reviews will appear here for moderation.</p>
        </div>
    <?php else: ?>
        <div class="review-moderation-list">
            <?php foreach ($reviews as $review): ?>
                <article class="admin-review-card">
                    <div class="admin-review-product">
                        <img src="<?php echo htmlspecialchars(product_image_path($review['product_image'])); ?>" alt="<?php echo htmlspecialchars($review['product_name']); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($review['product_name']); ?></h3>
                            <a href="product_details.php?id=<?php echo (int) $review['product_id']; ?>" class="auth-link">View product</a>
                        </div>
                    </div>

                    <div class="admin-review-body">
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?php echo $i <= (int) $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <span class="review-date">
                            <?php echo htmlspecialchars($review['user_name']); ?> &middot;
                            <?php echo htmlspecialchars($review['user_email']); ?> &middot;
                            <?php echo date('M j, Y - g:i A', strtotime($review['created_at'])); ?>
                        </span>
                    </div>

                    <form method="POST" action="admin_reviews.php" onsubmit="return confirm('Delete this review?');">
                        <?php echo ayurora_csrf_field(); ?>
                        <input type="hidden" name="review_id" value="<?php echo (int) $review['id']; ?>">
                        <button type="submit" name="delete_review" class="btn-outline admin-delete-review">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
