<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<!-- Close the default container to allow full-width sections -->
</main>

<!-- Premium Hero Section for About Us -->
<section class="premium-hero" style="height: 60vh; min-height: 400px; background: linear-gradient(rgba(27,60,45,0.7), rgba(27,60,45,0.9)), url('assets/images/homeimage.jpeg') center/cover;">
    <div class="premium-hero-content" style="max-width: 900px; padding: 2rem; margin: 0 auto;">
        <span class="hero-tagline reveal" style="color: var(--accent-color);">Our Heritage & Philosophy</span>
        <h1 class="reveal" style="animation-delay: 0.2s; font-size: 4rem; margin-bottom: 1rem; line-height: 1.2;">The PosMini <br> Experience</h1>
        <p class="hero-description reveal" style="animation-delay: 0.4s; font-size: 1.2rem; color: #fff;">
            Rooted in authentic Sri Lankan traditions and crafted for modern wellness to restore your body, mind, and spirit.
        </p>
    </div>
</section>

<!-- Our Story & Philosophy Section -->
<section style="padding: 7rem 0; background: var(--bg-color);">
    <div class="container" style="max-width: 1000px; margin: 0 auto; text-align: center;">
        <span style="color: var(--accent-color); font-weight: 700; text-transform: uppercase; letter-spacing: 3px;">Since Our Foundation</span>
        <h2 style="font-size: 3.5rem; color: var(--primary-color); margin: 1.5rem 0;">15+ Years of Trust & Purity</h2>
        <div style="width: 80px; height: 4px; background: var(--accent-color); margin: 0 auto 3rem;"></div>
        
        <p style="color: #444; font-size: 1.2rem; line-height: 1.9; margin-bottom: 2rem;">
            PosMini Ayurveda started with a core belief: true healing originates from the earth. For over 15 years, we have been honoring and preserving the ancient, sacred traditions of Sri Lankan Ayurveda. Our journey is driven by a profound commitment to delivering healing in its absolute purest form.
        </p>
        <p style="color: #444; font-size: 1.2rem; line-height: 1.9; margin-bottom: 4rem;">
            We hand-select nature's finest herbs, oils, and spices, combining age-old wisdom with modern precision. Every product created in our facility is a symbol of our dedication to your health, offering 100% natural, chemical-free solutions devoid of artificial additives.
        </p>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem; margin-top: 5rem; text-align: left;">
            <div style="background: var(--white); padding: 3rem 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-premium); transition: var(--transition);" class="product-card-premium">
                <i class="fas fa-leaf" style="font-size: 3.5rem; color: var(--accent-color); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--primary-color); font-size: 1.6rem; margin-bottom: 1rem;">100% Natural</h3>
                <p style="color: #666; font-size: 1rem; line-height: 1.6;">No artificial colors, synthetic preservatives, or harmful chemicals. Just the purest elements extracted safely from nature.</p>
            </div>
            
            <div style="background: var(--white); padding: 3rem 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-premium); transition: var(--transition);" class="product-card-premium">
                <i class="fas fa-hands-helping" style="font-size: 3.5rem; color: var(--accent-color); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--primary-color); font-size: 1.6rem; margin-bottom: 1rem;">Ethically Sourced</h3>
                <p style="color: #666; font-size: 1rem; line-height: 1.6;">We work intimately with local Sri Lankan farmers to ensure fair trade, equitable labor, and sustainable harvesting methods.</p>
            </div>

            <div style="background: var(--white); padding: 3rem 2.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-premium); transition: var(--transition);" class="product-card-premium">
                <i class="fas fa-flask" style="font-size: 3.5rem; color: var(--accent-color); margin-bottom: 1.5rem;"></i>
                <h3 style="color: var(--primary-color); font-size: 1.6rem; margin-bottom: 1rem;">Lab Certified</h3>
                <p style="color: #666; font-size: 1rem; line-height: 1.6;">Ancient Ayurvedic wisdom rigorously verified by modern science. Every batch is stringently tested for ultimate safety and purity.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to action -->
<section style="padding: 6rem 0; background: var(--primary-color); text-align: center; color: var(--white); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <h2 style="font-size: 3rem; margin-bottom: 1.5rem; color: #fff;">Experience The Gentle Healing</h2>
        <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 3rem; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.7;">
            Immerse yourself in our carefully curated collection of premium Ayurvedic remedies and daily wellness applications designed to naturally restore harmony.
        </p>
        <a href="index.php#products" class="btn btn-accent btn-lg" style="border-radius: 50px;">Explore Our Collection</a>
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
// Open a dummy main tag because footer.php closes one
echo '<main class="container">'; 
include 'includes/footer.php'; 
?>
