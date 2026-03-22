<?php
/**
 * API: Применение промокода
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

// ── Rate limiting для промокодов (10 попыток за 10 мин) ───────────────────
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rl = checkRateLimit('promo_' . $ip, 10, 600);
if (!$rl['allowed']) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again later.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Promo code is required']);
    exit;
}

// Санитизируем код — только буквы, цифры, дефис
$code = strtoupper(preg_replace('/[^A-Z0-9\-]/i', '', trim($input['code'])));

if (empty($code) || strlen($code) > 30) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid promo code format']);
    exit;
}

$result = applyPromoCode($code);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Promo code applied successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code']);
}
