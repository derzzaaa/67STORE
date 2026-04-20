/**
 * Main JavaScript file — с поддержкой CSRF защиты
 */

// ── Получаем CSRF токен из мета-тега ──────────────────────────────────────
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');

    toastMessage.textContent = message;
    toast.style.display = 'block';

    if (type === 'error') {
        toast.style.background = 'var(--color-primary)';
    } else {
        toast.style.background = 'var(--color-success)';
    }

    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}

// Add to cart
async function addToCart(productId, quantity = 1, reloadAfter = false) {
    try {
        const response = await fetch('api/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity, csrf_token: getCsrfToken() })
        });

        const data = await response.json();

        if (data.success) {
            // Update cart count
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.cart_count;
                cartCount.style.display = 'flex';
            }

            showToast('Product added to cart!');

            if (reloadAfter) {
                setTimeout(() => window.location.reload(), 700);
            }
        } else {
            showToast(data.message || 'Failed to add product', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// Update cart item quantity
async function updateCartQuantity(productId, quantity) {
    try {
        const response = await fetch('api/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity, csrf_token: getCsrfToken() })
        });

        const data = await response.json();

        if (data.success) {
            // Reload page to update totals
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to update cart', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// Remove from cart
async function removeFromCart(productId) {

    try {
        const response = await fetch('api/update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: 0, csrf_token: getCsrfToken() })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to remove item', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// Clear cart
async function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) {
        return;
    }

    try {
        const response = await fetch('api/clear-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ csrf_token: getCsrfToken() })
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            showToast(data.message || 'Failed to clear cart', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// Apply promo code
async function applyPromoCode() {
    const promoInput = document.getElementById('promoCode');
    const code = promoInput.value.trim();

    if (!code) {
        showToast('Please enter a promo code', 'error');
        return;
    }

    try {
        const response = await fetch('api/apply-promo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code: code })
        });

        const data = await response.json();

        if (data.success) {
            showToast('Promo code applied!');
            window.location.reload();
        } else {
            showToast(data.message || 'Invalid promo code', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    }
}

// Update countdown timers
function updateTimers() {
    const timers = document.querySelectorAll('[data-end-time]');

    timers.forEach(timer => {
        const endTime = new Date(timer.dataset.endTime).getTime();
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            timer.textContent = 'EXPIRED';
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timer.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    });
}

// Initialize timers
if (document.querySelectorAll('[data-end-time]').length > 0) {
    updateTimers();
    setInterval(updateTimers, 1000);
}

// Search functionality — removed client side search for demo purposes
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    // Allows normal form submission on Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.closest('form').submit();
        }
    });
}

// Add animation for add to cart button
document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.btn-add-cart');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const reloadAfter = this.dataset.reload === 'true';

            // Add animation
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);

            addToCart(productId, 1, reloadAfter);
        });
    });
});
