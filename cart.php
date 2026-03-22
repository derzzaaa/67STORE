<?php
/**
 * Страница корзины - дизайн на основе скриншота 2
 */

$pageTitle = 'Your Cart';
require_once 'includes/header.php';

$cartItems = getCartItems();
$totals = calculateCartTotal();
$suggestedProducts = getProducts(null, 5); // Для блока "Complete Your Order"
?>

<div class="container">
    <?php if (empty($cartItems)): ?>
        <!-- Пустая корзина -->
        <div style="text-align: center; padding: 80px 20px;">
            <i class="fas fa-shopping-cart" style="font-size: 80px; color: var(--color-gray); margin-bottom: 20px;"></i>
            <h2 style="font-size: 32px; margin-bottom: 12px;">Your Cart is Empty</h2>
            <p style="color: var(--color-gray); margin-bottom: 32px;">Add some products to get started!</p>
            <a href="index.php" class="btn-checkout">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <!-- Корзина с товарами -->
        <div style="padding: 20px 0;">
            <a href="index.php" style="color: var(--color-primary); font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <i class="fas fa-arrow-left"></i> Continue Shopping
            </a>
        </div>

        <div class="cart-container">
            <!-- Левая часть: Список товаров -->
            <div class="cart-items" style="border: 2px solid #E0E0E0;">
                <h1 class="cart-title">Your Cart (<?php echo count($cartItems); ?> items)</h1>

                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <!-- Изображение товара -->
                        <img 
                            src="<?php echo $item['image'] ? '/assets/images/' . $item['image'] : 'https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=' . urlencode(substr($item['name'], 0, 1)); ?>" 
                            alt="<?php echo e($item['name']); ?>"
                            class="cart-item-image"
                            onerror="this.src='https://via.placeholder.com/80x80/FF6B35/FFFFFF?text=<?php echo urlencode(substr($item['name'], 0, 1)); ?>'"
                        >

                        <!-- Информация о товаре -->
                        <div class="cart-item-info">
                            <h3><?php echo e($item['name']); ?></h3>
                            <p class="cart-item-desc"><?php echo e($item['description'] ?? substr($item['name'], 0, 50)); ?></p>
                            
                            <div class="cart-item-actions">
                                <!-- Счетчик количества -->
                                <div class="quantity-control">
                                    <button 
                                        class="btn-quantity" 
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)"
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                    <button 
                                        class="btn-quantity" 
                                        onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <!-- Кнопка удаления -->
                                <button 
                                    class="btn-remove" 
                                    onclick="removeFromCart(<?php echo $item['id']; ?>)"
                                >
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>

                        <!-- Цена -->
                        <div class="cart-item-price">
                            <div class="item-total"><?php echo formatPrice($item['total']); ?></div>
                            <div style="font-size: 14px; color: var(--color-gray); margin-top: 4px;">
                                <?php echo formatPrice($item['price']); ?> each
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Правая часть: Order Summary -->
            <div class="order-summary" style="border: 2px solid #E0E0E0;">
                <h3 class="summary-title">Order Summary</h3>

                <!-- Promo Code -->
                <div class="promo-input-group">
                    <input 
                        type="text" 
                        class="promo-input" 
                        id="promoCode" 
                        placeholder="Enter code"
                        value="<?php echo isset($_SESSION['promo_code']) ? e($_SESSION['promo_code']) : ''; ?>"
                    >
                    <button class="btn-apply" onclick="applyPromoCode()">Apply</button>
                </div>

                <!-- Subtotal -->
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($totals['subtotal']); ?></span>
                </div>

                <!-- Delivery Fee -->
                <div class="summary-row">
                    <span>Delivery Fee 
                        <i class="fas fa-info-circle" style="color: var(--color-gray); font-size: 12px;" title="Free delivery on orders over $20"></i>
                    </span>
                    <?php if ($totals['delivery_fee'] == 0): ?>
                        <span class="delivery-free">FREE</span>
                    <?php else: ?>
                        <span><?php echo formatPrice($totals['delivery_fee']); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Tax -->
                <div class="summary-row">
                    <span>Estimated Tax</span>
                    <span><?php echo formatPrice($totals['tax']); ?></span>
                </div>

                <!-- Discount -->
                <?php if ($totals['discount'] > 0): ?>
                <div class="summary-row" style="color: var(--color-success);">
                    <span>Discount</span>
                    <span>-<?php echo formatPrice($totals['discount']); ?></span>
                </div>
                <?php endif; ?>

                <!-- Total -->
                <div class="summary-row total">
                    <span>Total</span>
                    <span class="amount"><?php echo formatPrice($totals['total']); ?></span>
                </div>

                <!-- Checkout Button -->
                <a href="checkout.php" class="btn-checkout">
                    Proceed to Checkout <i class="fas fa-arrow-right"></i>
                </a>

                <!-- Payment Methods -->
                <div style="margin-top: 20px; text-align: center;">
                    <div style="font-size: 12px; color: var(--color-gray); margin-bottom: 8px;">We accept</div>
                    <div style="display: flex; gap: 8px; justify-content: center; opacity: 0.6;">
                        <i class="fab fa-cc-visa" style="font-size: 24px;"></i>
                        <i class="fab fa-cc-mastercard" style="font-size: 24px;"></i>
                        <i class="fab fa-cc-amex" style="font-size: 24px;"></i>
                        <i class="fab fa-cc-paypal" style="font-size: 24px;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Your Order (как на скриншоте 2) -->
        <div class="complete-order">
            <h3>Complete Your Order</h3>
            <div class="suggested-products">
                <?php foreach ($suggestedProducts as $product): ?>
                    <div class="product-card" style="padding: 12px; display: flex; flex-direction: column; height: 100%; border: 2px solid #E0E0E0;">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-image-wrapper" style="height: 140px;">
                                <img 
                                    src="<?php echo $product['image'] ? '/assets/images/' . $product['image'] : 'https://via.placeholder.com/200x140/FF6B35/FFFFFF?text=' . urlencode(substr($product['name'], 0, 10)); ?>" 
                                    alt="<?php echo e($product['name']); ?>"
                                    class="product-image"
                                    onerror="this.src='https://via.placeholder.com/200x140/FF6B35/FFFFFF?text=<?php echo urlencode(substr($product['name'], 0, 10)); ?>'"
                                >
                            </div>
                            <h4 style="font-size: 14px; margin: 8px 0; flex: 1;"><?php echo e($product['name']); ?></h4>
                        </a>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                            <span style="font-weight: 700; color: var(--color-secondary);"><?php echo formatPrice($product['price']); ?></span>
                            <button 
                                class="btn-add-cart" 
                                data-product-id="<?php echo $product['id']; ?>"
                                data-reload="true"
                                style="width: 32px; height: 32px; font-size: 14px;"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
