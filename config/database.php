<?php
/**
 * Конфигурация подключения к базе данных (Docker)
 */

// Настройки базы данных — из переменных окружения Docker
define('DB_HOST',    getenv('DB_HOST')    ?: 'db');
define('DB_NAME',    getenv('MYSQL_DATABASE') ?: 'seven_eleven_shop');
define('DB_USER',    getenv('MYSQL_USER')  ?: 'root');
define('DB_PASS',    getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Создание подключения к БД
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact administrator.");
}

// Настройки приложения
define('SITE_NAME', '7 SELECT');
define('SITE_URL',  ''); // В Docker сайт всегда в корне

define('TAX_RATE',                0.08);  // 8% налог
define('FREE_DELIVERY_THRESHOLD', 20.00); // Бесплатная доставка от $20
define('DELIVERY_FEE',            3.00);  // Стоимость доставки

// Кодировка PHP
mb_internal_encoding('UTF-8');

// ── Настройки безопасности сессии (ОБЯЗАТЕЛЬНО до session_start) ──────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
