<?php
/**
 * Страница каталога товаров
 */

$pageTitle = 'Categories';
require_once 'includes/header.php';

// Получаем категорию из параметра
$categorySlug = $_GET['category'] ?? null;
$categories = getCategories();

// Найти текущую категорию
$currentCategory = null;
$categoryId = null;
if ($categorySlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $currentCategory = $cat;
            $categoryId = $cat['id'];
            break;
        }
    }
}

$searchQuery = $_GET['search'] ?? null;
$limit = 24;

if ($searchQuery) {
    global $pdo;
    
    // ======== ДЕМОНСТРАЦИЯ УЯЗВИМОСТИ: SQL ИНЪЕКЦИЯ ========
    // УЯЗВИМЫЙ КОД (сейчас АКТИВЕН для демонстрации взлома)
    // Хакер может ввести: ' OR '1'='1
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE '%" . $searchQuery . "%' ORDER BY p.created_at DESC LIMIT $limit";
    $products = $pdo->query($sql)->fetchAll();

    /* БЕЗОПАСНЫЙ КОД (сейчас ЗАКОММЕНТИРОВАН, раскомментировать после демонстрации)
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ? ORDER BY p.created_at DESC LIMIT " . (int)$limit;
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $searchQuery . '%']);
    $products = $stmt->fetchAll();
    */
} else {
    $products = getProducts($categoryId, $limit);
}
?>

<div class="container" style="padding: 40px 0;">
    <?php if ($searchQuery): ?>
        <h1 style="font-size: 32px; margin-bottom: 8px;">
            Search Results
        </h1>
        
        <!-- ======== ДЕМОНСТРАЦИЯ УЯЗВИМОСТИ: XSS ======== -->
        <!-- УЯЗВИМЫЙ КОД (сейчас АКТИВЕН для демонстрации взлома) -->
        <!-- Хакер может ввести: <script>alert('XSS')</script> -->
        <div style="font-size: 18px; color: var(--color-gray); margin-bottom: 24px;">
            You searched for: <strong><?php echo $searchQuery; ?></strong>
        </div>
        
        <!-- БЕЗОПАСНЫЙ КОД (сейчас ЗАКОММЕНТИРОВАН, раскомментировать после демонстрации):
        <div style="font-size: 18px; color: var(--color-gray); margin-bottom: 24px;">
            You searched for: <strong><?php echo e($searchQuery); ?></strong>
        </div>
        -->
    <?php else: ?>
        <h1 style="font-size: 32px; margin-bottom: 24px;">
            <?php echo $currentCategory ? e($currentCategory['name']) : t('all_deals'); ?>
        </h1>
    <?php endif; ?>

    <!-- Category Filter -->
    <div style="display: flex; gap: 12px; margin-bottom: 32px; flex-wrap: wrap;">
        <a href="<?php echo url('catalog.php'); ?>" 
           class="<?php echo !$categorySlug ? 'active' : ''; ?>"
           style="padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 2px solid #E0E0E0; <?php echo !$categorySlug ? 'background: var(--color-primary); color: white;' : 'background: var(--color-light-gray); color: var(--color-dark);'; ?>">
            <?php echo t('all_deals'); ?>
        </a>
        <?php foreach ($categories as $category): ?>
            <a href="<?php echo url('catalog.php?category=' . $category['slug']); ?>" 
               class="<?php echo $categorySlug === $category['slug'] ? 'active' : ''; ?>"
               style="padding: 8px 16px; border-radius: 8px; font-weight: 600; border: 2px solid #E0E0E0; <?php echo $categorySlug === $category['slug'] ? 'background: var(--color-primary); color: white;' : 'background: var(--color-light-gray); color: var(--color-dark);'; ?>">
                <?php echo $category['icon']; ?> <?php echo e($category['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Products Grid -->
    <?php if (!empty($products)): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" style="border: 2px solid #E0E0E0;">
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
            <i class="fas fa-box-open" style="font-size: 64px; color: var(--color-gray); margin-bottom: 16px;"></i>
            <h2 style="color: var(--color-gray);">No products found</h2>
            <p style="color: var(--color-gray);">Try selecting a different category</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
