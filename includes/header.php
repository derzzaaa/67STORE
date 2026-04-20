<?php
/**
 * Header компонент - шапка сайта
 */
require_once __DIR__ . '/../includes/functions.php';

// ── HTTP Security Headers ──────────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; img-src 'self' data: https://via.placeholder.com;");

$cartCount = getCartCount();
$currentUser = getCurrentUser();
$currentLang = getCurrentLanguage();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo t('site_name'); ?></title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="<?php echo $csrfToken; ?>">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <!-- Logo -->
                <a href="<?php echo url('index.php'); ?>" class="logo">
                    <div class="logo-icon">6</div>
                    <span><?php echo t('site_name'); ?></span>
                </a>

                <!-- Search Bar -->
                <form action="catalog.php" method="GET" class="search-bar" style="margin: 0;">
                    <i class="fas fa-search search-icon"></i>
                    <input 
                        type="text" 
                        name="search"
                        class="search-input" 
                        placeholder="<?php echo t('search_placeholder'); ?>"
                        id="searchInput"
                    >
                </form>

                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Language Switcher -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="?lang=en" class="<?php echo $currentLang === 'en' ? 'active' : ''; ?>" 
                           style="padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 14px; <?php echo $currentLang === 'en' ? 'background: var(--color-primary); color: white;' : 'color: var(--color-gray);'; ?>">
                            EN
                        </a>
                        <a href="?lang=ru" class="<?php echo $currentLang === 'ru' ? 'active' : ''; ?>" 
                           style="padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 14px; <?php echo $currentLang === 'ru' ? 'background: var(--color-primary); color: white;' : 'color: var(--color-gray);'; ?>">
                            RU
                        </a>
                    </div>

                    <?php if ($currentUser): ?>
                        <a href="<?php echo url('account.php'); ?>" class="btn-login">
                            <i class="fas fa-user"></i> <?php echo e($currentUser['first_name']); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo url('login.php'); ?>" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> <?php echo t('login'); ?>
                        </a>
                    <?php endif; ?>

                    <a href="<?php echo url('cart.php'); ?>" class="cart-icon">
                        <i class="fas fa-shopping-cart" style="font-size: 24px;"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count" id="cartCount"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="<?php echo url('index.php'); ?>" class="nav-link"><?php echo t('nav_categories'); ?></a></li>
                    <li><a href="<?php echo url('promotions.php'); ?>" class="nav-link"><?php echo t('nav_hot_deals'); ?></a></li>
                    <li><a href="<?php echo url('catalog.php'); ?>" class="nav-link"><?php echo t('nav_fresh_food'); ?></a></li>
                    <li><a href="<?php echo url('index.php'); ?>#rewards" class="nav-link"><?php echo t('nav_rewards'); ?></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
