<?php
/**
 * Страница оформления заказа (Checkout)
 */

require_once 'includes/functions.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: ' . url('login.php?redirect=checkout.php'));
    exit;
}

$user = getCurrentUser();
$error = '';
$orderCreated = false;
$createdOrder = null;
$createdItems = [];

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $deliveryType = $_POST['delivery_type'] ?? 'delivery';
        $paymentType = $_POST['payment_type'] ?? 'online';
        
        $address = '';
        if ($deliveryType === 'delivery') {
            $name = trim($_POST['full_name'] ?? '');
            $street = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $zip = trim($_POST['zip_code'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            if (empty($name) || empty($street) || empty($city)) {
                $error = 'Please fill in all required delivery fields';
            } else {
                $address = "$name\n$street\n$city, $zip\nTel: $phone";
            }
        } else {
            $address = 'Store Pickup';
        }
        
        if (empty($error)) {
            $result = createOrder($user['id'], $address, $deliveryType, $paymentType);
            
            if ($result['success']) {
                $orderCreated = true;
                $createdOrder = getOrderById($result['order_id'], $user['id']);
                $createdItems = getOrderItems($result['order_id']);
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Если заказ НЕ создан — нужны данные корзины
if (!$orderCreated) {
    $cartItems = getCartItems();
    $totals = calculateCartTotal();
    
    if (empty($cartItems) && empty($error)) {
        header('Location: ' . url('cart.php'));
        exit;
    }
}

$pageTitle = $orderCreated ? 'Order Confirmed' : 'Checkout';
require_once 'includes/header.php';
$csrfToken = generateCsrfToken();
?>

<?php if ($orderCreated && $createdOrder): ?>
<!-- ====================================================
     ЗАКАЗ ОФОРМЛЕН — ТАЙМЕР 60 МИНУТ
     ==================================================== -->
<div class="container">
    <div class="order-success-page">
        <!-- Иконка успеха -->
        <div class="success-icon-wrap">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <div class="success-rings"></div>
        </div>

        <h1 class="order-success-title"><?php echo t('payment_success'); ?></h1>
        <p class="order-success-subtitle">
            <?php echo t('order_number'); ?><strong>#<?php echo $createdOrder['id']; ?></strong>
        </p>

        <!-- ТАЙМЕР 60 МИНУТ -->
        <div class="order-timer-card">
            <div class="timer-header">
                <i class="fas fa-motorcycle"></i>
                <span>Estimated delivery</span>
            </div>
            <div class="timer-countdown" id="order-timer">
                <div class="timer-unit">
                    <span class="timer-num" id="timer-min">60</span>
                    <span class="timer-label">min</span>
                </div>
                <div class="timer-sep">:</div>
                <div class="timer-unit">
                    <span class="timer-num" id="timer-sec">00</span>
                    <span class="timer-label">sec</span>
                </div>
            </div>
            <div class="timer-progress-wrap">
                <div class="timer-progress-bar" id="timer-bar"></div>
            </div>
        </div>

        <!-- Детали заказа -->
        <div class="order-success-details">
            <h3><?php echo t('order_details'); ?></h3>
            
            <div class="order-success-items">
                <?php foreach ($createdItems as $item): ?>
                    <div class="order-success-item">
                        <img src="<?php echo $item['image'] ? '/assets/images/' . $item['image'] : 'https://via.placeholder.com/48x48/FF6B35/FFFFFF?text=' . urlencode(substr($item['product_name'], 0, 1)); ?>" 
                             alt="<?php echo e($item['product_name']); ?>"
                             onerror="this.src='https://via.placeholder.com/48x48/FF6B35/FFFFFF?text=<?php echo urlencode(substr($item['product_name'], 0, 1)); ?>'">
                        <div class="order-success-item-info">
                            <span class="order-success-item-name"><?php echo e($item['product_name']); ?></span>
                            <span class="order-success-item-qty">× <?php echo $item['quantity']; ?></span>
                        </div>
                        <span class="order-success-item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-success-totals">
                <div class="order-success-row">
                    <span><?php echo t('subtotal'); ?></span>
                    <span><?php echo formatPrice($createdOrder['subtotal']); ?></span>
                </div>
                <div class="order-success-row">
                    <span><?php echo t('delivery_fee'); ?></span>
                    <span><?php echo $createdOrder['delivery_fee'] == 0 ? t('free') : formatPrice($createdOrder['delivery_fee']); ?></span>
                </div>
                <div class="order-success-row">
                    <span><?php echo t('estimated_tax'); ?></span>
                    <span><?php echo formatPrice($createdOrder['tax']); ?></span>
                </div>
                <?php if ($createdOrder['discount'] > 0): ?>
                <div class="order-success-row" style="color: var(--color-success);">
                    <span><?php echo t('discount'); ?></span>
                    <span>-<?php echo formatPrice($createdOrder['discount']); ?></span>
                </div>
                <?php endif; ?>
                <div class="order-success-row total">
                    <span><?php echo t('total'); ?></span>
                    <span><?php echo formatPrice($createdOrder['total']); ?></span>
                </div>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="order-success-actions">
            <a href="<?php echo url('account.php?tab=orders'); ?>" class="btn-checkout">
                <i class="fas fa-box"></i> <?php echo t('go_to_orders'); ?>
            </a>
            <a href="<?php echo url('index.php'); ?>" class="btn-secondary">
                <i class="fas fa-shopping-cart"></i> <?php echo t('continue_shopping'); ?>
            </a>
        </div>
    </div>
</div>

<script>
// 60-минутный таймер
(function() {
    let totalSeconds = 60 * 60; // 60 минут
    const totalStart = totalSeconds;
    const minEl = document.getElementById('timer-min');
    const secEl = document.getElementById('timer-sec');
    const barEl = document.getElementById('timer-bar');

    function tick() {
        if (totalSeconds <= 0) {
            minEl.textContent = '00';
            secEl.textContent = '00';
            barEl.style.width = '100%';
            return;
        }

        totalSeconds--;
        const m = Math.floor(totalSeconds / 60);
        const s = totalSeconds % 60;
        minEl.textContent = String(m).padStart(2, '0');
        secEl.textContent = String(s).padStart(2, '0');

        const pct = ((totalStart - totalSeconds) / totalStart) * 100;
        barEl.style.width = pct + '%';
    }

    setInterval(tick, 1000);
})();
</script>

<?php else: ?>
<!-- ====================================================
     ФОРМА ОФОРМЛЕНИЯ ЗАКАЗА
     ==================================================== -->
<div class="container">
    <div style="padding: 20px 0;">
        <a href="<?php echo url('cart.php'); ?>" style="color: var(--color-primary); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> <?php echo t('continue_shopping'); ?>
        </a>
    </div>

    <h1 class="checkout-title"><?php echo t('checkout'); ?></h1>

    <?php if ($error): ?>
        <div class="checkout-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="checkout-form">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

        <div class="checkout-grid">
            <!-- Левая часть: Формы -->
            <div class="checkout-forms">

                <!-- Способ доставки -->
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-truck"></i> <?php echo t('delivery_method'); ?>
                    </h2>
                    <div class="delivery-options">
                        <label class="delivery-option active" id="opt-delivery">
                            <input type="radio" name="delivery_type" value="delivery" checked>
                            <div class="option-content">
                                <i class="fas fa-home"></i>
                                <div>
                                    <strong><?php echo t('home_delivery'); ?></strong>
                                    <span class="option-price">
                                        <?php echo $totals['delivery_fee'] == 0 ? t('free') : formatPrice($totals['delivery_fee']); ?>
                                    </span>
                                </div>
                            </div>
                        </label>
                        <label class="delivery-option" id="opt-pickup">
                            <input type="radio" name="delivery_type" value="pickup">
                            <div class="option-content">
                                <i class="fas fa-store"></i>
                                <div>
                                    <strong><?php echo t('store_pickup'); ?></strong>
                                    <span class="option-price"><?php echo t('free'); ?></span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Адрес доставки -->
                <div class="checkout-section" id="delivery-address-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-map-marker-alt"></i> <?php echo t('delivery_info'); ?>
                    </h2>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label><?php echo t('full_name'); ?> *</label>
                            <input type="text" name="full_name" 
                                   value="<?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>"
                                   placeholder="<?php echo t('full_name'); ?>" id="input-fullname">
                        </div>
                        <div class="form-group full">
                            <label><?php echo t('delivery_address'); ?> *</label>
                            <input type="text" name="address" 
                                   placeholder="<?php echo t('delivery_address'); ?>" id="input-address">
                        </div>
                        <div class="form-group">
                            <label><?php echo t('city'); ?> *</label>
                            <input type="text" name="city" placeholder="<?php echo t('city'); ?>" id="input-city">
                        </div>
                        <div class="form-group">
                            <label><?php echo t('zip_code'); ?></label>
                            <input type="text" name="zip_code" placeholder="<?php echo t('zip_code'); ?>" id="input-zip">
                        </div>
                        <div class="form-group full">
                            <label><?php echo t('phone_number'); ?></label>
                            <input type="tel" name="phone" 
                                   value="<?php echo e($user['phone'] ?? ''); ?>"
                                   placeholder="<?php echo t('phone_number'); ?>" id="input-phone">
                        </div>
                    </div>
                </div>

                <!-- Способ оплаты -->
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-credit-card"></i> <?php echo t('payment_method'); ?>
                    </h2>
                    <div class="delivery-options">
                        <label class="delivery-option active" id="opt-online">
                            <input type="radio" name="payment_type" value="online" checked>
                            <div class="option-content">
                                <i class="fas fa-credit-card"></i>
                                <div>
                                    <strong><?php echo t('pay_online'); ?></strong>
                                    <span class="option-price">
                                        <i class="fab fa-cc-visa"></i>
                                        <i class="fab fa-cc-mastercard"></i>
                                    </span>
                                </div>
                            </div>
                        </label>
                        <label class="delivery-option" id="opt-cash">
                            <input type="radio" name="payment_type" value="cash">
                            <div class="option-content">
                                <i class="fas fa-money-bill-wave"></i>
                                <div>
                                    <strong><?php echo t('pay_cash'); ?></strong>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Правая часть: Итого -->
            <div class="checkout-summary">
                <div class="order-summary" style="border: 2px solid #E0E0E0;">
                    <h3 class="summary-title"><?php echo t('order_summary'); ?></h3>

                    <!-- Товары -->
                    <div class="checkout-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item-info">
                                    <span class="checkout-item-name"><?php echo e($item['name']); ?></span>
                                    <span class="checkout-item-qty">× <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="checkout-item-price"><?php echo formatPrice($item['total']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span><?php echo t('subtotal'); ?></span>
                        <span><?php echo formatPrice($totals['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('delivery_fee'); ?></span>
                        <?php if ($totals['delivery_fee'] == 0): ?>
                            <span class="delivery-free"><?php echo t('free'); ?></span>
                        <?php else: ?>
                            <span><?php echo formatPrice($totals['delivery_fee']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('estimated_tax'); ?></span>
                        <span><?php echo formatPrice($totals['tax']); ?></span>
                    </div>
                    <?php if ($totals['discount'] > 0): ?>
                    <div class="summary-row" style="color: var(--color-success);">
                        <span><?php echo t('discount'); ?></span>
                        <span>-<?php echo formatPrice($totals['discount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span><?php echo t('total'); ?></span>
                        <span class="amount"><?php echo formatPrice($totals['total']); ?></span>
                    </div>

                    <button type="submit" class="btn-checkout" id="btn-place-order">
                        <?php echo t('place_order'); ?> <i class="fas fa-arrow-right"></i>
                    </button>

                    <div style="margin-top: 16px; text-align: center;">
                        <i class="fas fa-lock" style="color: var(--color-success); margin-right: 4px;"></i>
                        <span style="font-size: 12px; color: var(--color-gray);"><?php echo t('secure_payment_note'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Переключение опций доставки
document.querySelectorAll('.delivery-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const group = this.closest('.delivery-options');
        group.querySelectorAll('.delivery-option').forEach(opt => opt.classList.remove('active'));
        this.closest('.delivery-option').classList.add('active');
        
        if (this.name === 'delivery_type') {
            const addressSection = document.getElementById('delivery-address-section');
            addressSection.style.display = this.value === 'delivery' ? 'block' : 'none';
        }
    });
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
