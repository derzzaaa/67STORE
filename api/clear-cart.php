<?php
/**
 * API: Очистка корзины
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

$_SESSION['cart'] = [];

echo json_encode([
    'success' => true,
    'message' => 'Cart cleared',
    'cart_count' => 0
]);
