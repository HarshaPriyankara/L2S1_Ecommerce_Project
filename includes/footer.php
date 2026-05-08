    </main>

    <footer>
        <div class="footer-content container">
            <div class="footer-section">
                <a href="index.php" class="logo" style="color: var(--white); margin-bottom: 1.5rem;">
                    <img src="assets/images/ayurora-logo-small.png" alt="AYURORA logo" class="brand-logo" width="24" height="24">
                    <span class="brand-name">AYURORA</span>
                </a>
                <p>Preserving the ancient wisdom of Sri Lankan Ayurveda. We bring nature's purest healing to your modern lifestyle with premium, authentic products.</p>
                <div class="social-links" style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <a href="#" style="font-size: 1.2rem; color: var(--accent-color);"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="font-size: 1.2rem; color: var(--accent-color);"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="font-size: 1.2rem; color: var(--accent-color);"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Discover</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#products">All Products</a></li>
                    <li><a href="wishlist.php">Wishlist</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fas fa-map-marker-alt" style="color: var(--accent-color); margin-right: 0.5rem;"></i> No 123, Galle Road, Colombo</p>
                <p><i class="fas fa-envelope" style="color: var(--accent-color); margin-right: 0.5rem;"></i> wellness@ayurora.lk</p>
                <p><i class="fas fa-phone" style="color: var(--accent-color); margin-right: 0.5rem;"></i> +94 77 123 4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> AYURORA. Crafted for Wellness.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var cartToastTimer;

            function showCartToast() {
                var toast = document.querySelector('.cart-toast');

                if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'cart-toast';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                    toast.innerHTML = '<div><strong>Added to cart</strong><span>You can keep shopping or view your cart when ready.</span></div><a href="cart.php">View Cart</a>';
                    document.body.appendChild(toast);
                }

                toast.classList.remove('is-hiding');
                toast.classList.add('is-visible');
                window.clearTimeout(cartToastTimer);
                cartToastTimer = window.setTimeout(function () {
                    toast.classList.add('is-hiding');
                }, 3500);
            }

            function updateCartCount(count) {
                var cartIcon = document.querySelector('.cart-icon');
                if (!cartIcon) {
                    return;
                }

                var badge = cartIcon.querySelector('.cart-count');

                if (count > 0 && !badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-count';
                    cartIcon.appendChild(badge);
                }

                if (badge) {
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'flex' : 'none';
                }
            }

            document.querySelectorAll('form[action="cart.php"]').forEach(function (form) {
                if (!form.querySelector('[name="add_to_cart"]')) {
                    return;
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    var submitter = event.submitter || form.querySelector('[name="add_to_cart"]');
                    var formData = new FormData(form);

                    if (submitter && submitter.name) {
                        formData.append(submitter.name, submitter.value || '1');
                    }

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Cart request failed');
                            }

                            return response.json();
                        })
                        .then(function (data) {
                            if (!data.success) {
                                throw new Error('Cart update failed');
                            }

                            updateCartCount(parseInt(data.cart_count, 10) || 0);
                            showCartToast();
                        })
                        .catch(function () {
                            form.submit();
                        });
                });
            });
        });
    </script>
</body>
</html>
