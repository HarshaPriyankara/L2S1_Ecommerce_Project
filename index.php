<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Close the default container for full-width home page sections -->
</main>

<!-- Premium Hero Section -->
<section class="premium-hero">
    <div class="hero-bg-media">
        <img src="assets/images/Ayurveda-Hero-Bg.png" alt="Ayurveda Background">
    </div>
    <div class="hero-overlay"></div>
    <div class="premium-hero-content">
        <span class="hero-tagline reveal">Authentic & Pure</span>
        <h1 class="reveal" style="animation-delay: 0.2s;">Discover Ancient <br><span style="color: var(--accent-color);">Sri Lankan</span> Healing</h1>
        <p class="hero-description reveal" style="animation-delay: 0.4s;">
            Purely organic, traditionally crafted, and ethically sourced. Experience the wisdom of Ayurveda in its most premium form.
        </p>
        <div class="hero-actions reveal" style="animation-delay: 0.6s;">
            <a href="#products" class="btn btn-accent btn-lg" style="padding: 1.2rem 3rem; font-size: 1.1rem; border-radius: 50px;">Shop Collection</a>
            <a href="#philosophy" class="btn btn-outline btn-lg" style="padding: 1.2rem 3rem; font-size: 1.1rem; border-radius: 50px; border-color: white; color: white; margin-left: 1rem;">Our Story</a>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section style="background: var(--white); border-bottom: 1px solid #eee; padding: 3rem 0; position: relative; z-index: 10;">
    <div class="container" style="max-width: 1400px; margin: 0 auto;">
        <div class="feature-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
            <div class="feature-item">
                <i class="fas fa-seedling" style="color: var(--accent-color); font-size: 2rem; margin-bottom: 1rem;"></i>
                <h4 style="color: var(--primary-color);">100% Organic</h4>
                <p style="font-size: 0.85rem; color: #666;">Sourced from local farms</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-vial" style="color: var(--accent-color); font-size: 2rem; margin-bottom: 1rem;"></i>
                <h4 style="color: var(--primary-color);">Lab Tested</h4>
                <p style="font-size: 0.85rem; color: #666;">Certified for purity</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-shipping-fast" style="color: var(--accent-color); font-size: 2rem; margin-bottom: 1rem;"></i>
                <h4 style="color: var(--primary-color);">Fast Delivery</h4>
                <p style="font-size: 0.85rem; color: #666;">Islandwide shipping</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-shield-heart" style="color: var(--accent-color); font-size: 2rem; margin-bottom: 1rem;"></i>
                <h4 style="color: var(--primary-color);">Authentic Recipe</h4>
                <p style="font-size: 0.85rem; color: #666;">Ancient traditional methods</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Philosophy Section -->
<section id="philosophy" class="philosophy-section">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div class="philosophy-grid">
            <div class="philosophy-image-stack">
                <img src="assets/images/image1.png" alt="Ayurveda Process" class="img-main">
                <img src="assets/images/image2.png" alt="Ayurveda Herbs" class="img-sub floating">
            </div>
            <div class="philosophy-text">
                <span style="color: var(--accent-color); font-weight: 700; text-transform: uppercase; letter-spacing: 3px;">Our Roots</span>
                <h2 style="font-size: 3rem; margin: 1.5rem 0;">Handcrafted with Nature’s Finest</h2>
                <p style="color: #555; font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                    For centuries, Sri Lankan Ayurveda has been a beacon of natural healing. At PosMini, we preserve this sacred tradition by combining ancient wisdom with modern precision. Every product is a testament to the healing power of the earth.
                </p>
                <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <h3 style="color: var(--primary-color); font-size: 1.8rem;">15+</h3>
                        <p style="font-size: 0.9rem; color: #777;">Years of Heritage</p>
                    </div>
                    <div style="width: 1px; background: #ddd; height: 50px;"></div>
                    <div>
                        <h3 style="color: var(--primary-color); font-size: 1.8rem;">100%</h3>
                        <p style="font-size: 0.9rem; color: #777;">Natural Ingredients</p>
                    </div>
                </div>
                <a href="about.php" class="btn btn-outline" style="border-radius: 50px;">Learn More</a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Banner Section -->
<section style="margin: 8rem 0; background: var(--primary-color); color: white; padding: 6rem 0; position: relative; overflow: hidden;">
    <img src="assets/images/homeimage.jpeg" alt="Nature" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15;">
    <div class="container" style="position: relative; z-index: 2; text-align: center; max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 3rem; margin-bottom: 1.5rem;">The Secret of Longevity</h2>
        <p style="font-size: 1.2rem; opacity: 0.8; margin-bottom: 3rem;">
            "Ayurveda is not just a medicine, it is a way of life." Embrace the gift of nature today.
        </p>
        <div style="display: inline-flex; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 60px; backdrop-filter: blur(10px);">
             <span style="padding: 10px 30px;">Premium Quality</span>
             <span style="padding: 10px 30px; background: var(--accent-color); border-radius: 50px; color: var(--primary-dark); font-weight: 700;">Exclusive Offer</span>
             <span style="padding: 10px 30px;">Authentic Sources</span>
        </div>
    </div>
</section>

<!-- Product Section -->
<section id="products" style="padding: 6rem 0; background: #fff;">
    <div class="container" style="max-width: 1400px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 5rem;">
            <span style="color: var(--accent-color); font-weight: 700; text-transform: uppercase;">Curated For You</span>
            <h2 style="font-size: 3.5rem; color: var(--primary-color); margin-top: 1rem;">Premium Collection</h2>
            <div style="width: 80px; height: 4px; background: var(--accent-color); margin: 2rem auto;"></div>
        </div>

        <!-- Category Navigation -->
        <div class="category-nav" style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; margin-bottom: 4rem;">
            <?php
            $cat_result_nav = $conn->query("SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category");
            if ($cat_result_nav->num_rows > 0) {
                while($nav_row = $cat_result_nav->fetch_assoc()) {
                    $cat_name = $nav_row['category'];
                    echo '<a href="#' . strtolower(str_replace(' ', '-', $cat_name)) . '" class="btn-outline" style="padding: 0.6rem 1.5rem; font-size: 0.85rem; border-radius: 50px;">' . htmlspecialchars($cat_name) . '</a>';
                }
            }
            ?>
        </div>

        <?php
        $cat_sql = "SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category";
        $cat_result = $conn->query($cat_sql);

        if ($cat_result->num_rows > 0) {
            while($cat_row = $cat_result->fetch_assoc()) {
                $category = $cat_row['category'];
                $cat_id = strtolower(str_replace(' ', '-', $category));
                ?>
                <div id="<?php echo $cat_id; ?>" class="category-header" style="margin-top: 6rem;">
                    <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                    <div class="category-line"></div>
                </div>
                
                <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 3rem; align-items: stretch;">
                    <?php
                    $sql = "SELECT * FROM products WHERE is_deleted = 0 AND category = '" . $conn->real_escape_string($category) . "' ORDER BY created_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            ?>
                            <div class="product-card-premium">
                                <a href="product_details.php?id=<?php echo $row['id']; ?>" class="product-link" style="display: flex; flex-direction: column; height: 100%;">
                                    <div class="premium-img-container">
                                        <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    </div>
                                    <div class="product-info" style="padding: 0.5rem 0; flex: 1; display: flex; flex-direction: column;">
                                        <h3 class="product-title" style="font-size: 1.4rem; color: var(--primary-color); margin-bottom: 0.5rem; min-height: 3.4rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($row['name']); ?></h3>
                                        
                                        <?php
                                        $rating_sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = " . $row['id'];
                                        $rating_res = $conn->query($rating_sql);
                                        $rating_row = $rating_res->fetch_assoc();
                                        $avg = round($rating_row['avg_rating'] ?? 0, 1);
                                        ?>
                                        <div class="rating-stars" style="color: #f39c12; margin: 0.5rem 0;">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $avg) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                            <span style="color: #999; font-size: 0.8rem; margin-left: 5px;">(<?php echo number_format($avg, 1); ?>)</span>
                                        </div>

                                        <p class="product-desc" style="margin-bottom: 2rem; color: #666; font-size: 0.95rem; flex: 1;">
                                            <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                            <span class="product-price" style="margin: 0; font-size: 1.5rem; color: var(--primary-color);">LKR <?php echo number_format($row['price'], 2); ?></span>
                                            <form action="cart.php" method="POST">
                                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 45px; height: 45px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
        }
        ?>
    </div>
</section>

<!-- CTA Section -->
<section style="padding: 10rem 0; background: var(--bg-color); text-align: center;">
    <div class="container">
        <div style="background: var(--white); padding: 5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-premium); border: 1px solid rgba(0,0,0,0.05);">
            <div class="floating" style="display: inline-block; margin-bottom: 2rem;">
                <i class="fas fa-leaf" style="font-size: 3rem; color: var(--accent-color);"></i>
            </div>
            <h2 style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1.5rem;">Join Our Wellness Circle</h2>
            <p style="color: #777; max-width: 600px; margin: 0 auto 3rem;">
                Subscribe to receive wellness tips, traditional recipes, and exclusive access to new launches.
            </p>
            <div style="max-width: 500px; margin: 0 auto; display: flex; gap: 1rem;">
                <input type="email" placeholder="Email Address" style="flex: 1; padding: 1.2rem; border-radius: 50px; border: 1px solid #ddd; outline: none; font-size: 1rem;">
                <button class="btn btn-primary" style="border-radius: 50px; padding: 0 2.5rem;">Subscribe</button>
            </div>
        </div>
    </div>
</section>

<style>
/* Local style to handle the header main tag conflict */
main.container {
    max-width: 100%;
    padding: 0;
    margin: 0;
}
</style>

<?php 
// Open a dummy main tag because footer.php likely closes it
echo '<main class="container">'; 
include 'includes/footer.php'; 
?>
