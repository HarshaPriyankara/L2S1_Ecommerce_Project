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

            function setupPriceSlider() {
                var minSlider = document.getElementById('min_price_slider');
                var maxSlider = document.getElementById('max_price_slider');
                var minValue = document.getElementById('min_price_value');
                var maxValue = document.getElementById('max_price_value');
                var minLabel = document.getElementById('min_price_label');
                var maxLabel = document.getElementById('max_price_label');

                if (!minSlider || !maxSlider || !minValue || !maxValue || !minLabel || !maxLabel) {
                    return;
                }

                function formatPrice(value) {
                    return 'LKR ' + Number(value).toLocaleString();
                }

                function syncPriceSlider() {
                    var min = parseInt(minSlider.value, 10);
                    var max = parseInt(maxSlider.value, 10);

                    if (min > max) {
                        var active = document.activeElement === minSlider ? 'min' : 'max';
                        if (active === 'min') {
                            max = min;
                            maxSlider.value = max;
                        } else {
                            min = max;
                            minSlider.value = min;
                        }
                    }

                    minValue.value = min;
                    maxValue.value = max;
                    minLabel.textContent = formatPrice(min);
                    maxLabel.textContent = formatPrice(max);
                }

                minSlider.addEventListener('input', syncPriceSlider);
                maxSlider.addEventListener('input', syncPriceSlider);
                syncPriceSlider();
            }

            function setupCheckoutOptions() {
                var checkoutForm = document.getElementById('checkout-form');
                if (!checkoutForm) {
                    return;
                }

                var itemsTotal = Number(checkoutForm.getAttribute('data-items-total')) || 0;
                var deliveryLabel = document.getElementById('delivery-summary-label');
                var deliveryFee = document.getElementById('delivery-summary-fee');
                var paymentLabel = document.getElementById('payment-summary-label');
                var checkoutTotal = document.getElementById('checkout-total');
                var placeOrderButton = document.getElementById('place-order-button');

                function money(value) {
                    return 'LKR ' + Number(value).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                function syncCheckoutOptions() {
                    var selectedDelivery = checkoutForm.querySelector('input[name="delivery_method"]:checked');
                    var selectedPayment = checkoutForm.querySelector('input[name="payment_method"]:checked');
                    var fee = selectedDelivery ? Number(selectedDelivery.getAttribute('data-fee')) || 0 : 0;
                    var total = itemsTotal + fee;

                    if (deliveryLabel && selectedDelivery) {
                        deliveryLabel.textContent = selectedDelivery.getAttribute('data-label') || 'Delivery';
                    }

                    if (deliveryFee) {
                        deliveryFee.textContent = fee > 0 ? money(fee) : 'Free';
                    }

                    if (paymentLabel && selectedPayment) {
                        paymentLabel.textContent = selectedPayment.getAttribute('data-label') || 'Payment';
                    }

                    if (checkoutTotal) {
                        checkoutTotal.textContent = money(total);
                    }

                    if (placeOrderButton) {
                        placeOrderButton.textContent = 'Continue to Payment - ' + money(total);
                    }
                }

                checkoutForm.querySelectorAll('input[name="delivery_method"], input[name="payment_method"]').forEach(function (input) {
                    input.addEventListener('change', syncCheckoutOptions);
                });

                syncCheckoutOptions();
            }

            function showCartToast(message, isError) {
                var toast = document.querySelector('.cart-toast');

                if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'cart-toast';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('aria-live', 'polite');
                    document.body.appendChild(toast);
                }

                toast.className = 'cart-toast' + (isError ? ' cart-toast-error' : '');
                toast.innerHTML = isError
                    ? '<div><strong>Cart not updated</strong><span>' + message + '</span></div>'
                    : '<div><strong>Added to cart</strong><span>You can keep shopping or view your cart when ready.</span></div><a href="cart.php">View Cart</a>';
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
                                showCartToast(data.message || 'Cart update failed', true);
                                return;
                            }

                            updateCartCount(parseInt(data.cart_count, 10) || 0);
                            showCartToast('', false);
                        })
                        .catch(function () {
                            form.submit();
                        });
                });
            });

            setupPriceSlider();
            setupCheckoutOptions();
        });
    </script>
</body>
</html>
