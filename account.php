<?php
/**
 * Личный кабинет пользователя
 */

$pageTitle = 'My Account';
require_once 'includes/header.php';

// Проверка авторизации
if (!isLoggedIn()) {
    header('Location: ' . url('login.php?redirect=account.php'));
    exit;
}

// Обработка выхода
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    if (validateCsrfToken($_GET['token'] ?? '')) {
        logoutUser();
        header('Location: ' . url('index.php'));
        exit;
    }
}

$user = getCurrentUser();
$orders = getUserOrders($user['id']);
$activeTab = $_GET['tab'] ?? 'profile';
?>

<div class="container">
    <div class="account-page">
        <!-- Заголовок -->
        <div class="account-header">
            <div class="account-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h1 class="account-name"><?php echo e($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="account-email"><?php echo e($user['email']); ?></p>
            </div>
        </div>

        <!-- Табы -->
        <div class="account-tabs">
            <a href="<?php echo url('account.php?tab=profile'); ?>" 
               class="account-tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" id="tab-profile">
                <i class="fas fa-user-circle"></i> <?php echo t('profile'); ?>
            </a>
            <a href="<?php echo url('account.php?tab=orders'); ?>" 
               class="account-tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" id="tab-orders">
                <i class="fas fa-box"></i> <?php echo t('my_orders'); ?>
                <?php if (count($orders) > 0): ?>
                    <span class="tab-badge"><?php echo count($orders); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Содержимое табов -->
        <div class="account-content">

            <?php if ($activeTab === 'profile'): ?>
            <!-- ======== ТАБ: ПРОФИЛЬ ======== -->
            <div class="profile-section">
                <div class="profile-card">
                    <h2 class="profile-card-title">
                        <i class="fas fa-id-card"></i> <?php echo t('personal_info'); ?>
                    </h2>

                    <div class="profile-info-grid">
                        <div class="profile-info-item">
                            <div class="profile-info-label"><?php echo t('first_name'); ?></div>
                            <div class="profile-info-value"><?php echo e($user['first_name']); ?></div>
                        </div>
                        <div class="profile-info-item">
                            <div class="profile-info-label"><?php echo t('last_name'); ?></div>
                            <div class="profile-info-value"><?php echo e($user['last_name']); ?></div>
                        </div>
                        <div class="profile-info-item">
                            <div class="profile-info-label"><?php echo t('email'); ?></div>
                            <div class="profile-info-value"><?php echo e($user['email']); ?></div>
                        </div>
                        <div class="profile-info-item">
                            <div class="profile-info-label"><?php echo t('phone'); ?></div>
                            <div class="profile-info-value"><?php echo $user['phone'] ? e($user['phone']) : '—'; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Статистика -->
                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-number"><?php echo count($orders); ?></div>
                        <div class="stat-label"><?php echo t('my_orders'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52,199,89,0.15); color: #34C759;"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => ($o['payment_status'] ?? '') === 'paid')); ?></div>
                        <div class="stat-label"><?php echo t('payment_paid'); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(255,149,0,0.15); color: #FF9500;"><i class="fas fa-clock"></i></div>
                        <div class="stat-number"><?php echo count(array_filter($orders, fn($o) => ($o['payment_status'] ?? 'unpaid') === 'unpaid')); ?></div>
                        <div class="stat-label"><?php echo t('payment_unpaid'); ?></div>
                    </div>
                </div>

                <!-- Кнопка выхода -->
                <a href="<?php echo url('account.php?action=logout&token=' . $csrfToken); ?>" 
                   class="btn-logout" id="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <?php echo t('logout'); ?>
                </a>
            </div>

            <?php elseif ($activeTab === 'orders'): ?>
            <!-- ======== ТАБ: ЗАКАЗЫ ======== -->
            <div class="orders-section">
                <?php if (empty($orders)): ?>
                    <!-- Пустой список заказов -->
                    <div class="orders-empty">
                        <div class="orders-empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3><?php echo t('no_orders'); ?></h3>
                        <p><?php echo t('no_orders_text'); ?></p>
                        <a href="<?php echo url('index.php'); ?>" class="btn-checkout">
                            <i class="fas fa-shopping-cart"></i> <?php echo t('start_shopping'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Таблица заказов (десктоп) -->
                    <div class="orders-table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th><?php echo t('order_number'); ?></th>
                                    <th><?php echo t('date'); ?></th>
                                    <th><i class="fas fa-motorcycle"></i> Delivery</th>
                                    <th><?php echo t('status'); ?></th>
                                    <th><?php echo t('payment'); ?></th>
                                    <th><?php echo t('amount'); ?></th>
                                    <th><?php echo t('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                        $statusClass = 'status-' . $order['status'];
                                        $paymentClass = 'payment-' . ($order['payment_status'] ?? 'unpaid');
                                    ?>
                                    <?php
                                        $orderTime = strtotime($order['created_at']);
                                        $endTime = $orderTime + 3600; // +60 мин
                                        $remaining = $endTime - time();
                                        $isDelivered = $remaining <= 0 || $order['status'] === 'completed';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="order-id">#<?php echo $order['id']; ?></span>
                                        </td>
                                        <td>
                                            <span class="order-date"><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($order['status'] === 'cancelled'): ?>
                                                <span class="delivery-timer-badge cancelled"><i class="fas fa-times-circle"></i> —</span>
                                            <?php elseif ($isDelivered): ?>
                                                <span class="delivery-timer-badge delivered"><i class="fas fa-check-circle"></i> Delivered</span>
                                            <?php else: ?>
                                                <div class="delivery-timer-inline" data-end="<?php echo $endTime; ?>">
                                                    <i class="fas fa-motorcycle"></i>
                                                    <span class="delivery-timer-text"></span>
                                                    <div class="delivery-mini-bar"><div class="delivery-mini-fill" style="width: <?php echo min(100, ((3600 - $remaining) / 3600) * 100); ?>%"></div></div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-status <?php echo $statusClass; ?>">
                                                <?php echo t('order_' . $order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-status <?php echo $paymentClass; ?>">
                                                <?php echo t('payment_' . ($order['payment_status'] ?? 'unpaid')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="order-total"><?php echo formatPrice($order['total']); ?></span>
                                        </td>
                                        <td>
                                            <div class="order-actions">
                                                <?php if (($order['payment_status'] ?? 'unpaid') === 'unpaid' && $order['payment_type'] === 'online'): ?>
                                                    <a href="<?php echo url('payment.php?order_id=' . $order['id']); ?>" class="btn-pay-now" id="pay-order-<?php echo $order['id']; ?>">
                                                        <i class="fas fa-credit-card"></i> <?php echo t('pay_now'); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo url('payment.php?order_id=' . $order['id'] . '&view=details'); ?>" class="btn-view-details" id="details-order-<?php echo $order['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Карточки заказов (мобильный) -->
                    <div class="orders-cards-mobile">
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $orderTime = strtotime($order['created_at']);
                                $endTime = $orderTime + 3600;
                                $remaining = $endTime - time();
                                $isDelivered = $remaining <= 0 || $order['status'] === 'completed';
                            ?>
                            <div class="order-card-mobile">
                                <div class="order-card-header">
                                    <span class="order-id">#<?php echo $order['id']; ?></span>
                                    <span class="order-date"><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <!-- Mobile delivery timer -->
                                <?php if ($order['status'] !== 'cancelled' && !$isDelivered): ?>
                                <div class="delivery-timer-inline mobile" data-end="<?php echo $endTime; ?>">
                                    <i class="fas fa-motorcycle"></i>
                                    <span class="delivery-timer-text"></span>
                                    <div class="delivery-mini-bar"><div class="delivery-mini-fill" style="width: <?php echo min(100, ((3600 - $remaining) / 3600) * 100); ?>%"></div></div>
                                </div>
                                <?php elseif ($isDelivered && $order['status'] !== 'cancelled'): ?>
                                <div style="margin-bottom: 8px;"><span class="delivery-timer-badge delivered"><i class="fas fa-check-circle"></i> Delivered</span></div>
                                <?php endif; ?>
                                <div class="order-card-body">
                                    <div class="order-card-badges">
                                        <span class="badge-status status-<?php echo $order['status']; ?>">
                                            <?php echo t('order_' . $order['status']); ?>
                                        </span>
                                        <span class="badge-status payment-<?php echo $order['payment_status'] ?? 'unpaid'; ?>">
                                            <?php echo t('payment_' . ($order['payment_status'] ?? 'unpaid')); ?>
                                        </span>
                                    </div>
                                    <span class="order-total"><?php echo formatPrice($order['total']); ?></span>
                                </div>
                                <div class="order-card-actions">
                                    <?php if (($order['payment_status'] ?? 'unpaid') === 'unpaid' && $order['payment_type'] === 'online'): ?>
                                        <a href="<?php echo url('payment.php?order_id=' . $order['id']); ?>" class="btn-pay-now">
                                            <i class="fas fa-credit-card"></i> <?php echo t('pay_now'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo url('payment.php?order_id=' . $order['id'] . '&view=details'); ?>" class="btn-view-details">
                                        <i class="fas fa-eye"></i> <?php echo t('view_details'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Live delivery countdown timers
(function() {
    function updateDeliveryTimers() {
        const timers = document.querySelectorAll('.delivery-timer-inline');
        const now = Math.floor(Date.now() / 1000);

        timers.forEach(el => {
            const end = parseInt(el.dataset.end);
            const remaining = end - now;

            if (remaining <= 0) {
                el.innerHTML = '<span class="delivery-timer-badge delivered"><i class="fas fa-check-circle"></i> Delivered</span>';
                return;
            }

            const min = Math.floor(remaining / 60);
            const sec = remaining % 60;
            const pct = Math.min(100, ((3600 - remaining) / 3600) * 100);

            const textEl = el.querySelector('.delivery-timer-text');
            const fillEl = el.querySelector('.delivery-mini-fill');

            if (textEl) textEl.textContent = String(min).padStart(2,'0') + ':' + String(sec).padStart(2,'0');
            if (fillEl) fillEl.style.width = pct + '%';
        });
    }

    updateDeliveryTimers();
    setInterval(updateDeliveryTimers, 1000);
})();
</script>

<?php require_once 'includes/footer.php'; ?>
