<?php
/**
 * Страница горячих предложений (Hot Deals)
 */

$pageTitle = 'Hot Deals';
require_once 'includes/header.php';

// Получаем только горячие предложения
$hotDeals = getProducts(null, 24, true); // true = только hot deals
?>

<div class="container" style="padding: 40px 0;">
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 42px; margin-bottom: 16px;">
            🔥 <?php echo t('all_hot_deals'); ?>
        </h1>
        <p style="font-size: 18px; color: var(--color-gray);">
            <?php echo t('hot_deals_subtitle'); ?>
        </p>
    </div>

    <!-- Hot Deals Grid -->
    <?php if (!empty($hotDeals)): ?>
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

                    <!-- Product Image -->
                    <a href="<?php echo url('product.php?id=' . $product['id']); ?>">
                        <div class="product-image-wrapper">
                            <img 
                                src="<?php echo $product['image'] ? '/assets/images/' . $product['image'] : 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=' . urlencode($product['name']); ?>" 
                                alt="<?php echo e($product['name']); ?>"
                                class="product-image"
                                onerror="this.src='https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=<?php echo urlencode($product['name']); ?>'">
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
                            title="<?php echo t('add_to_cart'); ?>"
                        >
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-fire" style="font-size: 64px; color: var(--color-gray); margin-bottom: 16px;"></i>
            <h2 style="color: var(--color-gray);">
                <?php echo t('no_active_deals'); ?>
            </h2>
            <p style="color: var(--color-gray);">
                <?php echo t('check_back_later'); ?>
            </p>
            <a href="<?php echo url('catalog.php'); ?>" class="btn-claim" style="margin-top: 24px; display: inline-block;">
                <?php echo t('view_all'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
