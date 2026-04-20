<?php
/**
 * Страница оплаты / деталей заказа
 */

$pageTitle = 'Payment';
require_once 'includes/header.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: ' . url('login.php?redirect=payment.php'));
    exit;
}

$user = getCurrentUser();
$orderId = (int)($_GET['order_id'] ?? 0);
$viewMode = $_GET['view'] ?? '';

if (!$orderId) {
    header('Location: ' . url('account.php?tab=orders'));
    exit;
}

$order = getOrderById($orderId, $user['id']);
if (!$order) {
    header('Location: ' . url('account.php?tab=orders'));
    exit;
}

$orderItems = getOrderItems($orderId);
$paymentSuccess = false;
$error = '';

// Обработка оплаты
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Имитация обработки оплаты (всегда успех)
        $result = updateOrderPaymentStatus($orderId, 'paid');
        if ($result) {
            $paymentSuccess = true;
            $order['payment_status'] = 'paid';
            $order['status'] = 'processing';
        } else {
            $error = 'Payment processing failed. Please try again.';
        }
    }
}

$isViewDetails = $viewMode === 'details' || ($order['payment_status'] ?? 'unpaid') === 'paid';
$csrfToken = generateCsrfToken();
?>

<div class="container">
    <div style="padding: 20px 0;">
        <a href="<?php echo url('account.php?tab=orders'); ?>" style="color: var(--color-primary); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> <?php echo t('my_orders'); ?>
        </a>
    </div>

    <?php if ($paymentSuccess): ?>
    <!-- ======== УСПЕШНАЯ ОПЛАТА ======== -->
    <div class="payment-success-container">
        <div class="payment-success-card">
            <div class="success-icon-wrap">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="success-rings"></div>
            </div>
            <h1><?php echo t('payment_success'); ?></h1>
            <p><?php echo t('payment_success_msg'); ?></p>
            <div class="success-order-info">
                <div class="success-info-row">
                    <span><?php echo t('order_number'); ?></span>
                    <strong>#<?php echo $order['id']; ?></strong>
                </div>
                <div class="success-info-row">
                    <span><?php echo t('amount'); ?></span>
                    <strong><?php echo formatPrice($order['total']); ?></strong>
                </div>
            </div>
            <a href="<?php echo url('account.php?tab=orders'); ?>" class="btn-checkout">
                <i class="fas fa-box"></i> <?php echo t('go_to_orders'); ?>
            </a>
        </div>
    </div>

    <?php elseif ($isViewDetails): ?>
    <!-- ======== ДЕТАЛИ ЗАКАЗА ======== -->
    <div class="order-details-page">
        <h1 class="checkout-title"><?php echo t('order_details'); ?> #<?php echo $order['id']; ?></h1>

        <div class="checkout-grid">
            <div class="checkout-forms">
                <!-- Статусы -->
                <div class="checkout-section">
                    <div class="order-detail-statuses">
                        <div class="detail-status-item">
                            <span class="detail-status-label"><?php echo t('status'); ?>:</span>
                            <span class="badge-status status-<?php echo $order['status']; ?>">
                                <?php echo t('order_' . $order['status']); ?>
                            </span>
                        </div>
                        <div class="detail-status-item">
                            <span class="detail-status-label"><?php echo t('payment'); ?>:</span>
                            <span class="badge-status payment-<?php echo $order['payment_status'] ?? 'unpaid'; ?>">
                                <?php echo t('payment_' . ($order['payment_status'] ?? 'unpaid')); ?>
                            </span>
                        </div>
                        <div class="detail-status-item">
                            <span class="detail-status-label"><?php echo t('date'); ?>:</span>
                            <span><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Позиции заказа -->
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-list"></i> <?php echo t('order_items'); ?>
                    </h2>
                    <div class="order-detail-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-detail-item">
                                <img src="<?php echo $item['image'] ? '/assets/images/' . $item['image'] : 'https://via.placeholder.com/60x60/FF6B35/FFFFFF?text=' . urlencode(substr($item['product_name'], 0, 1)); ?>" 
                                     alt="<?php echo e($item['product_name']); ?>"
                                     class="order-detail-item-img"
                                     onerror="this.src='https://via.placeholder.com/60x60/FF6B35/FFFFFF?text=<?php echo urlencode(substr($item['product_name'], 0, 1)); ?>'">
                                <div class="order-detail-item-info">
                                    <span class="order-detail-item-name"><?php echo e($item['product_name']); ?></span>
                                    <span class="order-detail-item-qty">× <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="order-detail-item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($order['delivery_address']): ?>
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-map-marker-alt"></i> <?php echo t('delivery_address'); ?>
                    </h2>
                    <p style="white-space: pre-line; color: var(--color-gray); line-height: 1.8;">
                        <?php echo e($order['delivery_address']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Итого -->
            <div class="checkout-summary">
                <div class="order-summary" style="border: 2px solid #E0E0E0;">
                    <h3 class="summary-title"><?php echo t('order_summary'); ?></h3>
                    <div class="summary-row">
                        <span><?php echo t('subtotal'); ?></span>
                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('delivery_fee'); ?></span>
                        <?php if ($order['delivery_fee'] == 0): ?>
                            <span class="delivery-free"><?php echo t('free'); ?></span>
                        <?php else: ?>
                            <span><?php echo formatPrice($order['delivery_fee']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('estimated_tax'); ?></span>
                        <span><?php echo formatPrice($order['tax']); ?></span>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div class="summary-row" style="color: var(--color-success);">
                        <span><?php echo t('discount'); ?></span>
                        <span>-<?php echo formatPrice($order['discount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span><?php echo t('total'); ?></span>
                        <span class="amount"><?php echo formatPrice($order['total']); ?></span>
                    </div>

                    <?php if (($order['payment_status'] ?? 'unpaid') === 'unpaid' && $order['payment_type'] === 'online'): ?>
                        <a href="<?php echo url('payment.php?order_id=' . $order['id']); ?>" class="btn-checkout">
                            <i class="fas fa-credit-card"></i> <?php echo t('pay_now'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ======== ФОРМА ОПЛАТЫ ======== -->
    <div class="payment-page">
        <h1 class="checkout-title"><?php echo t('pay_for_order'); ?> #<?php echo $order['id']; ?></h1>

        <?php if ($error): ?>
            <div class="checkout-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <div class="checkout-forms">
                <div class="checkout-section">
                    <h2 class="checkout-section-title">
                        <i class="fas fa-lock"></i> <?php echo t('secure_payment'); ?>
                    </h2>

                    <!-- Визуальная карточка -->
                    <div class="credit-card-visual">
                        <div class="card-chip"></div>
                        <div class="card-number-display" id="card-display">•••• •••• •••• ••••</div>
                        <div class="card-bottom">
                            <div>
                                <div class="card-label"><?php echo t('card_holder'); ?></div>
                                <div class="card-holder-display" id="holder-display"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            </div>
                            <div>
                                <div class="card-label"><?php echo t('expiry_date'); ?></div>
                                <div class="card-expiry-display" id="expiry-display">MM/YY</div>
                            </div>
                        </div>
                        <div class="card-brand">
                            <i class="fab fa-cc-visa" style="font-size: 32px;"></i>
                        </div>
                    </div>

                    <!-- Форма ввода -->
                    <form method="POST" action="" id="payment-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="pay" value="1">

                        <div class="payment-form-fields">
                            <div class="form-group full">
                                <label><?php echo t('card_number'); ?></label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-credit-card"></i>
                                    <input type="text" id="card-number" 
                                           placeholder="1234 5678 9012 3456" 
                                           maxlength="19" autocomplete="cc-number"
                                           required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo t('expiry_date'); ?></label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-calendar"></i>
                                    <input type="text" id="card-expiry" 
                                           placeholder="MM/YY" maxlength="5" 
                                           autocomplete="cc-exp" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>CVC</label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-lock"></i>
                                    <input type="text" id="card-cvc" 
                                           placeholder="123" maxlength="4" 
                                           autocomplete="cc-csc" required>
                                </div>
                            </div>
                            <div class="form-group full">
                                <label><?php echo t('card_holder'); ?></label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="card-holder" 
                                           value="<?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>"
                                           placeholder="<?php echo t('card_holder'); ?>" 
                                           autocomplete="cc-name" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-pay" id="btn-pay">
                            <i class="fas fa-lock"></i>
                            <?php echo t('pay_amount'); ?> <?php echo formatPrice($order['total']); ?>
                        </button>

                        <div style="margin-top: 16px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-shield-alt" style="color: var(--color-success);"></i>
                            <span style="font-size: 12px; color: var(--color-gray);"><?php echo t('secure_payment_note'); ?></span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Итого -->
            <div class="checkout-summary">
                <div class="order-summary" style="border: 2px solid #E0E0E0;">
                    <h3 class="summary-title"><?php echo t('order_details'); ?></h3>

                    <div class="checkout-items">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="checkout-item">
                                <div class="checkout-item-info">
                                    <span class="checkout-item-name"><?php echo e($item['product_name']); ?></span>
                                    <span class="checkout-item-qty">× <?php echo $item['quantity']; ?></span>
                                </div>
                                <span class="checkout-item-price"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span><?php echo t('subtotal'); ?></span>
                        <span><?php echo formatPrice($order['subtotal']); ?></span>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('delivery_fee'); ?></span>
                        <?php if ($order['delivery_fee'] == 0): ?>
                            <span class="delivery-free"><?php echo t('free'); ?></span>
                        <?php else: ?>
                            <span><?php echo formatPrice($order['delivery_fee']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="summary-row">
                        <span><?php echo t('estimated_tax'); ?></span>
                        <span><?php echo formatPrice($order['tax']); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span><?php echo t('total'); ?></span>
                        <span class="amount"><?php echo formatPrice($order['total']); ?></span>
                    </div>

                    <!-- Платёжные системы -->
                    <div style="margin-top: 20px; text-align: center;">
                        <div style="font-size: 12px; color: var(--color-gray); margin-bottom: 8px;"><?php echo t('we_accept'); ?></div>
                        <div style="display: flex; gap: 12px; justify-content: center; opacity: 0.6;">
                            <i class="fab fa-cc-visa" style="font-size: 28px;"></i>
                            <i class="fab fa-cc-mastercard" style="font-size: 28px;"></i>
                            <i class="fab fa-cc-amex" style="font-size: 28px;"></i>
                            <i class="fab fa-cc-paypal" style="font-size: 28px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Живое обновление визуальной карты
const cardNumber = document.getElementById('card-number');
const cardExpiry = document.getElementById('card-expiry');
const cardHolder = document.getElementById('card-holder');

if (cardNumber) {
    // Форматирование номера карты
    cardNumber.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        let formatted = v.replace(/(\d{4})(?=\d)/g, '$1 ');
        this.value = formatted;
        
        document.getElementById('card-display').textContent = 
            formatted || '•••• •••• •••• ••••';
        
        // Определение бренда
        const brand = document.querySelector('.card-brand i');
        if (v.startsWith('4')) {
            brand.className = 'fab fa-cc-visa';
        } else if (v.startsWith('5') || v.startsWith('2')) {
            brand.className = 'fab fa-cc-mastercard';
        } else if (v.startsWith('3')) {
            brand.className = 'fab fa-cc-amex';
        } else {
            brand.className = 'fab fa-cc-visa';
        }
    });

    // Форматирование срока
    cardExpiry.addEventListener('input', function(e) {
        let v = this.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 2) {
            v = v.substring(0, 2) + '/' + v.substring(2);
        }
        this.value = v;
        document.getElementById('expiry-display').textContent = v || 'MM/YY';
    });

    // Имя владельца
    cardHolder.addEventListener('input', function(e) {
        document.getElementById('holder-display').textContent = 
            this.value || '<?php echo e($user['first_name'] . ' ' . $user['last_name']); ?>';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
