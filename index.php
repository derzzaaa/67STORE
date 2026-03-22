<?php
/**
 * Главная страница - 7-Eleven Shop
 * Дизайн на основе скриншота 1
 */

$pageTitle = 'Home';
require_once 'includes/header.php';

// Получаем категории и товары
$categories = getCategories();
$hotDeals = getProducts(null, 8, true); // Только hot deals
$allProducts = getProducts(null, 12); // Все товары
?>

<!-- Hero Banner -->
<section class="promo-banner">
    <div class="promo-content">
        <h2>
            <?php echo t('promo_take'); ?> <span class="promo-highlight"><?php echo t('promo_off'); ?></span><br>
            <?php echo t('promo_your_first_order'); ?>
        </h2>
        <p class="promo-subtitle">
            <?php echo t('promo_use_code'); ?> <span class="promo-code-inline">FRESH7</span> <?php echo t('promo_subtitle_text'); ?>
        </p>

        <!-- Countdown -->
        <div class="banner-countdown">
            <div class="banner-countdown-unit">
                <span class="num" id="bc-h">00</span>
                <span class="lbl">Hours</span>
            </div>
            <div class="banner-countdown-unit">
                <span class="num" id="bc-m">00</span>
                <span class="lbl">Mins</span>
            </div>
            <div class="banner-countdown-unit">
                <span class="num" id="bc-s">00</span>
                <span class="lbl">Secs</span>
            </div>
        </div>

        <a href="<?php echo url('promotions.php'); ?>" class="btn-claim">
            <?php echo t('promo_claim'); ?>
        </a>
    </div>
</section>

<script>
(function() {
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
        var now = new Date(), end = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
        var d = Math.max(0, Math.floor((end - now) / 1000));
        document.getElementById('bc-h').textContent = pad(Math.floor(d / 3600));
        document.getElementById('bc-m').textContent = pad(Math.floor((d % 3600) / 60));
        document.getElementById('bc-s').textContent = pad(d % 60);
    }
    tick(); setInterval(tick, 1000);
})();
</script>

<!-- Categories -->
<section class="categories">
    <div class="container">
        <h3 class="categories-title"><?php echo t('quick_categories'); ?></h3>
        <div class="categories-grid">
            <a href="<?php echo url('catalog.php'); ?>" class="category-item">
                <div class="category-icon-wrap">&#128722;</div>
                <span class="category-name"><?php echo t('cat_all'); ?></span>
            </a>
            <?php
            $catIcons = [
                'hot-deals'    => '&#128293;',  // 🔥
                'beverages'    => '&#129381;',  // 🧃
                'snacks'       => '&#127871;',  // 🍿
                'fresh-food'   => '&#127828;',  // 🍔
                'frozen-treats'=> '&#127846;',  // 🍦
                'essentials'   => '&#129524;',  // 🧴
            ];
            foreach ($categories as $category):
                $icon = $catIcons[$category['slug']] ?? '&#128230;';
            ?>
                <a href="<?php echo url('catalog.php?category=' . $category['slug']); ?>" class="category-item">
                    <div class="category-icon-wrap"><?php echo $icon; ?></div>
                    <span class="category-name"><?php echo e($category['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Hot Deals Products -->
<?php if (!empty($hotDeals)): ?>
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🔥 <?php echo t('all_hot_deals'); ?></h2>
        </div>

        <div class="products-grid">
            <?php foreach ($hotDeals as $product): ?>
                <div class="product-card" style="border: 2px solid #C8C8C8;">
                    <!-- Badges -->
                    <div class="product-badges">
                        <?php if ($product['deal_ends_at']): ?>
                            <div class="badge badge-timer">
                                <i class="far fa-clock"></i>
                                <span data-end-time="<?php echo $product['deal_ends_at']; ?>">
                                    <?php echo getTimeRemaining($product['deal_ends_at']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['discount_percent']): ?>
                            <div class="badge badge-discount">
                                -<?php echo $product['discount_percent']; ?>%
                            </div>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo url('product.php?id=' . $product['id']); ?>">
                        <div class="product-image-wrapper">
                            <img 
                                src="<?php echo $product['image'] ? '/assets/images/' . $product['image'] : 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=' . urlencode($product['name']); ?>" 
                                alt="<?php echo e($product['name']); ?>"
                                class="product-image"
                                onerror="this.src='https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=<?php echo urlencode($product['name']); ?>'"
                            >
                        </div>
                    </a>

                    <!-- Product Info -->
                    <div class="product-category"><?php echo e($product['category_label'] ?? $product['category_name']); ?></div>
                    <h3 class="product-name">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>" style="text-decoration: none; color: inherit;">
                            <?php echo e($product['name']); ?>
                        </a>
                    </h3>

                    <!-- Price and Add to Cart -->
                    <div class="product-price-row">
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                            <?php if ($product['old_price']): ?>
                                <span class="price-old"><?php echo formatPrice($product['old_price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button 
                            class="btn-add-cart" 
                            data-product-id="<?php echo $product['id']; ?>"
                            title="Add to cart"
                        >
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- All Products -->
<section class="products-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('popular_products'); ?></h2>
            <a href="<?php echo url('catalog.php'); ?>" style="color: var(--color-primary); font-weight: 600;">
                <?php echo t('view_all'); ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="products-grid">
            <?php foreach ($allProducts as $product): ?>
                <div class="product-card" style="border: 2px solid #C8C8C8;">
                    <!-- Badges -->
                    <?php if ($product['is_hot_deal'] || $product['discount_percent']): ?>
                    <div class="product-badges">
                        <?php if ($product['deal_ends_at']): ?>
                            <div class="badge badge-timer">
                                <i class="far fa-clock"></i>
                                <span data-end-time="<?php echo $product['deal_ends_at']; ?>">
                                    <?php echo getTimeRemaining($product['deal_ends_at']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['discount_percent']): ?>
                            <div class="badge badge-discount">
                                -<?php echo $product['discount_percent']; ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Product Image -->
                    <a href="product.php?id=<?php echo $product['id']; ?>">
                        <div class="product-image-wrapper">
                            <img 
                                src="<?php echo $product['image'] ? '/assets/images/' . $product['image'] : 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=' . urlencode($product['name']); ?>" 
                                alt="<?php echo e($product['name']); ?>"
                                class="product-image"
                                onerror="this.src='https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=<?php echo urlencode($product['name']); ?>'"
                            >
                        </div>
                    </a>

                    <!-- Product Info -->
                    <div class="product-category"><?php echo e($product['category_label'] ?? $product['category_name']); ?></div>
                    <h3 class="product-name"><?php echo e($product['name']); ?></h3>

                    <!-- Price and Add to Cart -->
                    <div class="product-price-row">
                        <div class="product-price">
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                            <?php if ($product['old_price']): ?>
                                <span class="price-old"><?php echo formatPrice($product['old_price']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button 
                            class="btn-add-cart" 
                            data-product-id="<?php echo $product['id']; ?>"
                            title="Add to cart"
                        >
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
