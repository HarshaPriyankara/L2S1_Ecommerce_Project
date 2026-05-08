<?php
include 'includes/db.php';
include 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$search_like = '%' . $search . '%';
$selected_category = trim($_GET['category'] ?? '');
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? max(0, (float) $_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? max(0, (float) $_GET['max_price']) : null;
$price_min_limit = 0;
$price_max_limit = 5000;
$price_step = 50;
$has_price_filter = $min_price !== null || $max_price !== null;
$has_category_filter = $selected_category !== '';
$has_product_filters = $search !== '' || $has_price_filter || $has_category_filter;

if ($min_price !== null && $max_price !== null && $min_price > $max_price) {
    [$min_price, $max_price] = [$max_price, $min_price];
}

$display_min_price = $min_price ?? $price_min_limit;
$display_max_price = $max_price ?? $price_max_limit;

$filter_sql = "is_deleted = 0";
$filter_types = "";
$filter_params = [];

if ($search !== '') {
    $filter_sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $filter_types .= "sss";
    array_push($filter_params, $search_like, $search_like, $search_like);
}

if ($selected_category !== '') {
    $filter_sql .= " AND category = ?";
    $filter_types .= "s";
    $filter_params[] = $selected_category;
}

if ($min_price !== null) {
    $filter_sql .= " AND price >= ?";
    $filter_types .= "d";
    $filter_params[] = $min_price;
}

if ($max_price !== null) {
    $filter_sql .= " AND price <= ?";
    $filter_types .= "d";
    $filter_params[] = $max_price;
}

function bind_stmt_params($stmt, $types, &$params) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
}
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
            <a href="#philosophy" class="btn btn-outline btn-lg hero-story-btn">Our Story</a>
        </div>
    </div>
</section>

<!-- Features Bar -->
<section class="home-features">
    <div class="container home-wide-container">
        <div class="feature-grid home-feature-grid">
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
    <div class="container home-narrow-container">
        <div class="philosophy-grid">
            <div class="philosophy-image-stack">
                <img src="assets/images/image1.png" alt="Ayurveda Process" class="img-main">
                <img src="assets/images/image2.png" alt="Ayurveda Herbs" class="img-sub floating">
            </div>
            <div class="philosophy-text">
                <span style="color: var(--accent-color); font-weight: 700; text-transform: uppercase; letter-spacing: 3px;">Our Roots</span>
                <h2 style="font-size: 3rem; margin: 1.5rem 0;">Handcrafted with Nature’s Finest</h2>
                <p style="color: #555; font-size: 1.1rem; line-height: 1.8; margin-bottom: 2rem;">
                    For centuries, Sri Lankan Ayurveda has been a beacon of natural healing. At AYURORA, we preserve this sacred tradition by combining ancient wisdom with modern precision. Every product is a testament to the healing power of the earth.
                </p>
                <div class="philosophy-stats">
                    <div>
                        <h3 style="color: var(--primary-color); font-size: 1.8rem;">15+</h3>
                        <p style="font-size: 0.9rem; color: #777;">Years of Heritage</p>
                    </div>
                    <div class="philosophy-stat-divider"></div>
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
<section class="home-banner">
    <img src="assets/images/homeimage.jpeg" alt="Nature" class="home-banner-bg">
    <div class="container home-banner-content">
        <h2>The Secret of Longevity</h2>
        <p>
            "Ayurveda is not just a medicine, it is a way of life." Embrace the gift of nature today.
        </p>
        <div class="home-banner-pills">
             <span>Premium Quality</span>
             <span class="active">Exclusive Offer</span>
             <span>Authentic Sources</span>
        </div>
    </div>
</section>

<!-- Product Section -->
<section id="products" style="padding: 6rem 0; background: #fff;">
    <div class="container home-wide-container">
        <div style="text-align: center; margin-bottom: 5rem;">
            <span style="color: var(--accent-color); font-weight: 700; text-transform: uppercase;">Curated For You</span>
            <h2 style="font-size: 3.5rem; color: var(--primary-color); margin-top: 1rem;">Premium Collection</h2>
            <div style="width: 80px; height: 4px; background: var(--accent-color); margin: 2rem auto;"></div>
        </div>

        <form class="product-search" method="GET" action="index.php#products">
            <div class="product-search-main">
                <label>Search</label>
                <div class="product-search-field">
                    <i class="fas fa-search"></i>
                    <input type="search" name="search" placeholder="Product, category, ingredient..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="category-filter-field">
                <label>Category / Type</label>
                <div class="category-select-wrap">
                    <i class="fas fa-layer-group"></i>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php
                        $category_options = $conn->query("SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category");
                        while ($category_option = $category_options->fetch_assoc()):
                            $category_value = $category_option['category'];
                        ?>
                            <option value="<?php echo htmlspecialchars($category_value); ?>" <?php echo $selected_category === $category_value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category_value); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="price-filter-field">
                <label>Price Range</label>
                <input type="hidden" name="min_price" id="min_price_value" value="<?php echo $has_price_filter ? htmlspecialchars((string) $display_min_price) : ''; ?>">
                <input type="hidden" name="max_price" id="max_price_value" value="<?php echo $has_price_filter ? htmlspecialchars((string) $display_max_price) : ''; ?>">
                <div class="price-slider-values">
                    <span id="min_price_label">LKR <?php echo number_format($display_min_price, 0); ?></span>
                    <span id="max_price_label">LKR <?php echo number_format($display_max_price, 0); ?></span>
                </div>
                <div class="price-slider">
                    <div class="price-slider-track"></div>
                    <input type="range" id="min_price_slider" min="<?php echo $price_min_limit; ?>" max="<?php echo $price_max_limit; ?>" step="<?php echo $price_step; ?>" value="<?php echo htmlspecialchars((string) $display_min_price); ?>">
                    <input type="range" id="max_price_slider" min="<?php echo $price_min_limit; ?>" max="<?php echo $price_max_limit; ?>" step="<?php echo $price_step; ?>" value="<?php echo htmlspecialchars((string) $display_max_price); ?>">
                </div>
            </div>
            <div class="product-search-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-sliders"></i> Apply</button>
                <?php if ($has_product_filters): ?>
                    <a href="index.php#products" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($has_product_filters): ?>
            <p class="search-summary">
                <?php if ($search !== ''): ?>
                    Showing results for <strong><?php echo htmlspecialchars($search); ?></strong>
                <?php else: ?>
                    Showing filtered products
                <?php endif; ?>
                <?php if ($has_price_filter): ?>
                    <span>
                        Price:
                        <?php echo $min_price !== null ? 'LKR ' . number_format($min_price, 2) : 'Any'; ?>
                        -
                        <?php echo $max_price !== null ? 'LKR ' . number_format($max_price, 2) : 'Any'; ?>
                    </span>
                <?php endif; ?>
                <?php if ($has_category_filter): ?>
                    <span>Category: <?php echo htmlspecialchars($selected_category); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <!-- Category Navigation -->
        <div class="category-nav" style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; margin-bottom: 4rem;">
            <?php
            if ($has_product_filters) {
                $cat_nav_stmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE $filter_sql ORDER BY category");
                bind_stmt_params($cat_nav_stmt, $filter_types, $filter_params);
                $cat_nav_stmt->execute();
                $cat_result_nav = $cat_nav_stmt->get_result();
            } else {
                $cat_result_nav = $conn->query("SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category");
            }

            if ($cat_result_nav->num_rows > 0) {
                while($nav_row = $cat_result_nav->fetch_assoc()) {
                    $cat_name = $nav_row['category'];
                    echo '<a href="#' . strtolower(str_replace(' ', '-', $cat_name)) . '" class="btn-outline" style="padding: 0.6rem 1.5rem; font-size: 0.85rem; border-radius: 50px;">' . htmlspecialchars($cat_name) . '</a>';
                }
            }

            if (isset($cat_nav_stmt)) {
                $cat_nav_stmt->close();
            }
            ?>
        </div>

        <?php
        if ($has_product_filters) {
            $cat_stmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE $filter_sql ORDER BY category");
            bind_stmt_params($cat_stmt, $filter_types, $filter_params);
            $cat_stmt->execute();
            $cat_result = $cat_stmt->get_result();
        } else {
            $cat_sql = "SELECT DISTINCT category FROM products WHERE is_deleted = 0 ORDER BY category";
            $cat_result = $conn->query($cat_sql);
        }

        if ($cat_result->num_rows > 0) {
            while($cat_row = $cat_result->fetch_assoc()) {
                $category = $cat_row['category'];
                $cat_id = strtolower(str_replace(' ', '-', $category));
                ?>
                <div id="<?php echo $cat_id; ?>" class="category-header" style="margin-top: 6rem;">
                    <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                    <div class="category-line"></div>
                </div>
                
                <div class="product-grid home-product-grid">
                    <?php
                    if ($has_product_filters) {
                        $product_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND $filter_sql ORDER BY created_at DESC");
                        $product_filter_types = 's' . $filter_types;
                        $product_filter_params = array_merge([$category], $filter_params);
                        bind_stmt_params($product_stmt, $product_filter_types, $product_filter_params);
                        $product_stmt->execute();
                        $result = $product_stmt->get_result();
                    } else {
                        $sql = "SELECT * FROM products WHERE is_deleted = 0 AND category = '" . $conn->real_escape_string($category) . "' ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                    }

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $stock_quantity = (int) ($row['stock_quantity'] ?? 0);
                            $in_stock = $stock_quantity > 0;
                            ?>
                            <div class="product-card-premium">
                                <a href="product_details.php?id=<?php echo $row['id']; ?>" class="product-link" style="display: flex; flex-direction: column; height: 100%;">
                                    <div class="premium-img-container">
                                        <img src="<?php echo htmlspecialchars(product_image_path($row['image'])); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    </div>
                                    <div class="product-info" style="padding: 0.5rem 0; flex: 1; display: flex; flex-direction: column;">
                                        <h3 class="product-title" style="font-size: 1.4rem; color: var(--primary-color); margin-bottom: 0.5rem; min-height: 3.4rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($row['name']); ?></h3>
                                        
                                        <?php
                                        $rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = " . $row['id'];
                                        $rating_res = $conn->query($rating_sql);
                                        $rating_row = $rating_res->fetch_assoc();
                                        $avg = round($rating_row['avg_rating'] ?? 0, 1);
                                        $review_count = (int) ($rating_row['review_count'] ?? 0);
                                        ?>
                                        <div class="rating-stars" style="color: #f39c12; margin: 0.5rem 0;">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo ($i <= $avg) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                            <span style="color: #999; font-size: 0.8rem; margin-left: 5px;">
                                                <?php echo $review_count > 0 ? number_format($avg, 1) . ' / 5 (' . $review_count . ')' : 'No reviews'; ?>
                                            </span>
                                        </div>

                                        <p class="product-desc" style="margin-bottom: 2rem; color: #666; font-size: 0.95rem; flex: 1;">
                                            <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <span class="stock-badge <?php echo $in_stock ? 'in-stock' : 'out-stock'; ?>">
                                            <?php echo $in_stock ? 'In stock' : 'Out of stock'; ?>
                                        </span>
                                        <?php if ($in_stock): ?>
                                            <span class="stock-note"><?php echo $stock_quantity; ?> available</span>
                                        <?php else: ?>
                                            <span class="stock-note">Currently unavailable</span>
                                        <?php endif; ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                            <span class="product-price" style="margin: 0; font-size: 1.5rem; color: var(--primary-color);">LKR <?php echo number_format($row['price'], 2); ?></span>
                                            <?php if ($in_stock): ?>
                                                <form action="cart.php" method="POST">
                                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php#products">
                                                    <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 45px; height: 45px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-disabled" style="width: 45px; height: 45px; border-radius: 50%; padding: 0;" disabled>
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                    }

                    if (isset($product_stmt)) {
                        $product_stmt->close();
                        unset($product_stmt);
                    }
                    ?>
                </div>
                <?php
            }
        } else {
            ?>
            <div style="text-align: center; padding: 4rem 2rem; background: var(--primary-light); border-radius: var(--radius-md);">
                <?php if ($has_product_filters): ?>
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">No matching products found</h3>
                    <p style="color: var(--text-light); margin-bottom: 1.5rem;">Try another search term or adjust the price range.</p>
                    <a href="index.php#products" class="btn btn-primary">View All Products</a>
                <?php else: ?>
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">No products found</h3>
                    <p style="color: var(--text-light);">Import <code>database.sql</code> again or run <code>seed_products.php</code> once to add the sample items.</p>
                <?php endif; ?>
            </div>
            <?php
        }

        if (isset($cat_stmt)) {
            $cat_stmt->close();
        }
        ?>
    </div>
</section>

<!-- CTA Section -->
<section class="home-cta">
    <div class="container">
        <div class="home-cta-card">
            <div class="floating" style="display: inline-block; margin-bottom: 2rem;">
                <i class="fas fa-leaf" style="font-size: 3rem; color: var(--accent-color);"></i>
            </div>
            <h2>Join Our Wellness Circle</h2>
            <p>
                Subscribe to receive wellness tips, traditional recipes, and exclusive access to new launches.
            </p>
            <div class="home-subscribe-form">
                <input type="email" placeholder="Email Address">
                <button class="btn btn-primary">Subscribe</button>
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
