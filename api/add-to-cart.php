<?php
/**
 * API: Добавление товара в корзину
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

// ── Защита: только AJAX запросы с того же сайта ────────────────────────────
if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest'
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

// ── CSRF-защита ───────────────────────────────────────────────────────────
if (!validateCsrfToken($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit;
}

if (!isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = (int)$input['product_id'];
$quantity  = isset($input['quantity']) ? (int)$input['quantity'] : 1;

// Ограничение: корректные значения
if ($productId <= 0 || $quantity <= 0 || $quantity > 99) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
    exit;
}

// Проверяем существование товара
$product = getProductById($productId);
if (!$product) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Добавляем в корзину
$result = addToCart($productId, $quantity);

if ($result) {
    echo json_encode([
        'success'    => true,
        'message'    => 'Product added to cart',
        'cart_count' => getCartCount()
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product']);
}
