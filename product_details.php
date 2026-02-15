<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$product_id = $_GET['id'];
$product_id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $product_id AND is_deleted = 0";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='container'><p>Product not found.</p></div>";
    include 'includes/footer.php';
    exit();
}

$product = $result->fetch_assoc();

// Calculate Average Rating
$rating_sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = $product_id";
$rating_result = $conn->query($rating_sql);
$rating_row = $rating_result->fetch_assoc();
$avg_rating = round($rating_row['avg_rating'], 1);

// Fetch Reviews
$reviews_sql = "SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = $product_id ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_sql);
?>

<div class="container">
    <div style="display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap;">
        <!-- Product Image -->
        <div style="flex: 1; min-width: 300px;">
            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; border-radius: var(--radius); box-shadow: var(--shadow);">
        </div>

        <!-- Product Info -->
        <div style="flex: 1; min-width: 300px;">
            <span style="background: var(--secondary-color); color: var(--primary-dark); padding: 0.2rem 0.8rem; border-radius: 50px; font-size: 0.9rem; font-weight: bold;"><?php echo htmlspecialchars($product['category']); ?></span>
            <h1 style="color: var(--primary-dark); margin-top: 0.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div style="margin-bottom: 1rem; color: #f39c12;">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $avg_rating) {
                        echo '<i class="fas fa-star"></i>';
                    } elseif ($i - 0.5 <= $avg_rating) {
                        echo '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                echo " (" . ($reviews_result->num_rows) . " reviews)";
                ?>
            </div>

            <p style="font-size: 1.25rem; font-weight: bold; color: var(--primary-color); margin-bottom: 1rem;">LKR <?php echo number_format($product['price'], 2); ?></p>
            
            <p style="margin-bottom: 2rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem;">
                <form action="cart.php" method="POST" style="flex: 1;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="add_to_wishlist.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_wishlist" class="btn btn-outline" style="border-color: #e74c3c; color: #e74c3c;">
                            <i class="fas fa-heart"></i> Wishlist
                        </button>
                    </form>
                <?php else: ?>
                   <a href="login.php" class="btn btn-outline" style="border-color: #e74c3c; color: #e74c3c;"><i class="fas fa-heart"></i> Wishlist</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div style="margin-top: 6rem; padding-bottom: 4rem;">
        <div class="category-header" style="margin-bottom: 3rem;">
            <h2 class="category-title" style="font-size: 2.22rem;">Customer Reviews</h2>
            <div class="category-line"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 4rem; align-items: start;">
            <!-- Review Form -->
            <div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="review-form">
                        <h3>Share Your Experience</h3>
                        <form action="submit_review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Your Rating</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                            </div>

                            <div class="form-group" style="margin-top: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Your Comment</label>
                                <textarea name="comment" class="form-control" rows="4" placeholder="Tell others what you think about this product..." required style="resize: none; border-radius: var(--radius-md);"></textarea>
                            </div>
                            
                            <button type="submit" name="submit_review" class="btn btn-primary" style="width: 100%; margin-top: 1rem; border-radius: 50px;">
                                Post Review
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="background: var(--primary-light); padding: 2rem; border-radius: var(--radius-md); text-align: center; border: 1px dashed var(--primary-color);">
                        <i class="fas fa-lock" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 1rem; display: block;"></i>
                        <p style="margin-bottom: 1.5rem; color: var(--primary-dark);">Please login to share your experience with this product.</p>
                        <a href="login.php" class="btn btn-primary" style="border-radius: 50px;">Login Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Review List -->
            <div class="reviews-list">
                <?php
                if ($reviews_result->num_rows > 0) {
                    while($review = $reviews_result->fetch_assoc()) {
                        $initial = strtoupper(substr($review['user_name'], 0, 1));
                        ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-user">
                                    <div class="avatar"><?php echo $initial; ?></div>
                                    <div>
                                        <span><?php echo htmlspecialchars($review['user_name']); ?></span>
                                        <div class="review-stars">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $review['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="review-comment">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div style="text-align: center; padding: 4rem 2rem; background: #fff; border-radius: var(--radius-md); border: 1px solid #eee;">
                        <i class="far fa-comment-dots" style="font-size: 4rem; color: #ddd; margin-bottom: 1.5rem; display: block;"></i>
                        <h3 style="color: #999;">No reviews yet</h3>
                        <p style="color: #bbb;">Be the first one to share your thoughts!</p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
