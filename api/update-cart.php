<?php
/**
 * API: Обновление количества товара в корзине
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

// ── Защита: только AJAX запросы ───────────────────────────────────────────
if (
    ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' ||
    ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest'
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// ── CSRF-защита ───────────────────────────────────────────────────────────
if (!validateCsrfToken($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit;
}

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID and quantity are required']);
    exit;
}

$productId = (int)$input['product_id'];
$quantity  = (int)$input['quantity'];

if ($productId <= 0 || $quantity < 0 || $quantity > 99) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid values']);
    exit;
}

$result = updateCartItem($productId, $quantity);

if ($result) {
    echo json_encode([
        'success'    => true,
        'message'    => 'Cart updated',
        'cart_count' => getCartCount()
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
}
