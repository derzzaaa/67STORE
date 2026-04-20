<?php
/**
 * Вспомогательные функции для работы с приложением
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/lang.php';

// ============================================================
// SECURITY FUNCTIONS
// ============================================================

/**
 * Генерация CSRF-токена
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF-токена
 */
function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting — защита от брутфорса и спама
 * @param string $key    Уникальный ключ (например 'login_ip_1.2.3.4')
 * @param int $maxAttempts Макс. попыток
 * @param int $windowSeconds Окно в секундах
 */
function checkRateLimit($key, $maxAttempts = 5, $windowSeconds = 900) {
    $now = time();
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = ['count' => 0, 'start' => $now];
    }

    $rl = &$_SESSION['rate_limits'][$key];

    // Сброс окна если время истекло
    if ($now - $rl['start'] > $windowSeconds) {
        $rl = ['count' => 0, 'start' => $now];
    }

    $rl['count']++;

    if ($rl['count'] > $maxAttempts) {
        $remaining = $windowSeconds - ($now - $rl['start']);
        return ['allowed' => false, 'retry_after' => $remaining];
    }

    return ['allowed' => true, 'attempts_left' => $maxAttempts - $rl['count']];
}

/**
 * Сброс rate limit после успешного действия
 */
function resetRateLimit($key) {
    unset($_SESSION['rate_limits'][$key]);
}

/**
 * Безопасный редирект только на внутренние страницы
 */
function safeRedirect($url, $default = 'index.php') {
    // Разрешаем только относительные пути без // и протоколов
    if (preg_match('#^(https?:)?//#i', $url) || strpos($url, '://') !== false) {
        $url = $default;
    }
    // Убираем нулевые байты и управляющие символы
    $url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);
    header('Location: ' . $url);
    exit;
}

/**
 * Генерация правильного URL
 */
function url($path = '') {
    // Убираем начальный слеш если есть
    $path = ltrim($path, '/');
    
    if (SITE_URL === '') {
        // В Docker - просто путь
        return $path ? '/' . $path : '/';
    } else {
        // В Laragon - с префиксом
        return SITE_URL . ($path ? '/' . $path : '');
    }
}

/**
 * Получить все товары или товары по категории
 */
function getProducts($categoryId = null, $limit = null, $isHotDeal = null) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1";
    
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($isHotDeal !== null) {
        $sql .= " AND p.is_hot_deal = ?";
        $params[] = $isHotDeal;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Получить товар по ID
 */
function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Получить все категории
 */
function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC");
    return $stmt->fetchAll();
}

/**
 * Добавить товар в корзину
 */
function addToCart($productId, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    return true;
}

/**
 * Обновить количество товара в корзине
 */
function updateCartItem($productId, $quantity) {
    if ($quantity <= 0) {
        removeFromCart($productId);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    return true;
}

/**
 * Удалить товар из корзины
 */
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    return true;
}

/**
 * Получить товары из корзины
 */
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    global $pdo;
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();
    
    // Добавляем количество к каждому товару
    foreach ($products as &$product) {
        $product['quantity'] = $_SESSION['cart'][$product['id']];
        $product['total'] = $product['price'] * $product['quantity'];
    }
    
    return $products;
}

/**
 * Получить количество товаров в корзине
 */
function getCartCount() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Рассчитать итоговую сумму корзины
 */
function calculateCartTotal() {
    $items = getCartItems();
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['total'];
    }
    
    $tax = $subtotal * TAX_RATE;
    $deliveryFee = $subtotal >= FREE_DELIVERY_THRESHOLD ? 0 : DELIVERY_FEE;
    
    // Применение промокода если есть
    $discount = 0;
    if (isset($_SESSION['promo_code'])) {
        $promo = getPromoCode($_SESSION['promo_code']);
        if ($promo) {
            if ($promo['discount_percent']) {
                $discount = $subtotal * ($promo['discount_percent'] / 100);
            } else {
                $discount = $promo['discount_amount'];
            }
        }
    }
    
    $total = $subtotal + $tax + $deliveryFee - $discount;
    
    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'delivery_fee' => $deliveryFee,
        'discount' => $discount,
        'total' => max(0, $total)
    ];
}

/**
 * Проверить промокод
 */
function getPromoCode($code) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM promo_codes 
                           WHERE code = ? 
                           AND is_active = 1 
                           AND valid_from <= CURDATE() 
                           AND valid_to >= CURDATE()
                           AND (usage_limit IS NULL OR used_count < usage_limit)");
    $stmt->execute([$code]);
    return $stmt->fetch();
}

/**
 * Применить промокод
 */
function applyPromoCode($code) {
    $promo = getPromoCode($code);
    if ($promo) {
        $_SESSION['promo_code'] = $code;
        return true;
    }
    return false;
}

/**
 * Проверка авторизации
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Получить текущего пользователя
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, phone FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Регистрация пользователя
 */
function registerUser($email, $password, $firstName, $lastName, $phone = null) {
    global $pdo;
    
    // Проверка существования email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Вставка пользователя
    $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, phone) 
                           VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$email, $hashedPassword, $firstName, $lastName, $phone])) {
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        return ['success' => true, 'user_id' => $userId];
    }
    
    return ['success' => false, 'message' => 'Registration failed'];
}

/**
 * Авторизация пользователя
 */
function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true); // Защита от session fixation
        $_SESSION['user_id'] = $user['id'];
        return ['success' => true, 'user_id' => $user['id']];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}

/**
 * Выход пользователя
 */
function logoutUser() {
    unset($_SESSION['user_id']);
    return true;
}

/**
 * Форматирование цены
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Безопасный вывод HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Получить оставшееся время до окончания акции
 */
function getTimeRemaining($endTime) {
    $now = time();
    $end = strtotime($endTime);
    $diff = $end - $now;
    
    if ($diff <= 0) {
        return null;
    }
    
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $seconds = $diff % 60;
    
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

// ============================================================
// ORDER FUNCTIONS
// ============================================================

/**
 * Получить заказы пользователя
 */
function getUserOrders($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Получить позиции заказа
 */
function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, p.image FROM order_items oi 
                           LEFT JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

/**
 * Получить заказ по ID (с проверкой пользователя)
 */
function getOrderById($orderId, $userId = null) {
    global $pdo;
    $sql = "SELECT * FROM orders WHERE id = ?";
    $params = [$orderId];
    
    if ($userId !== null) {
        $sql .= " AND user_id = ?";
        $params[] = $userId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Создать заказ из корзины
 */
function createOrder($userId, $deliveryAddress, $deliveryType, $paymentType) {
    global $pdo;
    
    $cartItems = getCartItems();
    if (empty($cartItems)) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }
    
    $totals = calculateCartTotal();
    
    try {
        $pdo->beginTransaction();
        
        // Создание заказа
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, subtotal, tax, delivery_fee, discount, promo_code, status, delivery_address, delivery_type, payment_type) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
        $stmt->execute([
            $userId,
            $totals['total'],
            $totals['subtotal'],
            $totals['tax'],
            $totals['delivery_fee'],
            $totals['discount'],
            $_SESSION['promo_code'] ?? null,
            $deliveryAddress,
            $deliveryType,
            $paymentType
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Добавление позиций заказа
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($cartItems as $item) {
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        $pdo->commit();
        
        // Очищаем корзину и промокод
        $_SESSION['cart'] = [];
        unset($_SESSION['promo_code']);
        
        return ['success' => true, 'order_id' => $orderId];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return ['success' => false, 'message' => 'Order creation failed'];
    }
}

/**
 * Обновить статус оплаты заказа
 */
function updateOrderPaymentStatus($orderId, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, status = CASE WHEN ? = 'paid' THEN 'processing' ELSE status END WHERE id = ?");
    $stmt->execute([$status, $status, $orderId]);
    return $stmt->rowCount() > 0;
}

/**
 * Получить название статуса заказа
 */
function getOrderStatusLabel($status) {
    $labels = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ];
    return $labels[$status] ?? $status;
}

/**
 * Получить название статуса оплаты
 */
function getPaymentStatusLabel($status) {
    $labels = [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'failed' => 'Failed'
    ];
    return $labels[$status] ?? $status;
}
