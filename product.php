<?php
/**
 * Динамическая страница продукта — product.php?id=X
 */

require_once 'includes/functions.php';

// ── Защита входных данных ─────────────────────────────────────────────────
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: catalog.php');
    exit;
}

// ── Получаем продукт ──────────────────────────────────────────────────────
$product = getProductById($productId);

if (!$product) {
    http_response_code(404);
    $pageTitle = '404 - Not Found';
    require_once 'includes/header.php';
    echo '<div class="container" style="text-align:center; padding: 80px 20px;">
        <h1 style="font-size: 48px;"><i class="fas fa-ban" style="color: var(--color-gray);"></i></h1>
        <h2>Product not found</h2>
        <a href="catalog.php" class="btn-checkout" style="display:inline-block; margin-top:24px;">Back to Catalog</a>
    </div>';
    require_once 'includes/footer.php';
    exit;
}

// ── Похожие продукты той же категории ────────────────────────────────────
$related = getProducts($product['category_id'], 4);
$related = array_filter($related, fn($p) => $p['id'] != $productId);
$related = array_slice($related, 0, 4);

$pageTitle = e($product['name']);
require_once 'includes/header.php';
?>

<div class="container" style="padding: 24px 0 60px;">

    <!-- ── Хлебные крошки ─────────────────────────────────────────────── -->
    <nav style="font-size: 13px; color: var(--color-gray); margin-bottom: 24px;">
        <a href="<?php echo url('index.php'); ?>" style="color: var(--color-gray); text-decoration: none;">Home</a>
        <span style="margin: 0 8px;">›</span>
        <a href="<?php echo url('catalog.php'); ?>" style="color: var(--color-gray); text-decoration: none;">Catalog</a>
        <?php if ($product['category_name']): ?>
            <span style="margin: 0 8px;">›</span>
            <a href="<?php echo url('catalog.php?category=' . ($product['category_slug'] ?? '')); ?>" style="color: var(--color-gray); text-decoration: none;">
                <?php echo e($product['category_name']); ?>
            </a>
        <?php endif; ?>
        <span style="margin: 0 8px;">›</span>
        <span style="color: var(--color-dark); font-weight: 500;"><?php echo e($product['name']); ?></span>
    </nav>

    <!-- ── Основной блок продукта ─────────────────────────────────────── -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start;">

        <!-- Изображение -->
        <div style="position: sticky; top: 80px;">
            <div style="background: #FAFAFA; border-radius: 20px; padding: 32px; border: 2px solid #C8C8C8; text-align: center; position: relative; overflow: hidden;">
                <?php if ($product['is_hot_deal']): ?>
                    <div style="position: absolute; top: 16px; left: 16px; background: var(--color-primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;"><i class="fas fa-fire"></i> HOT DEAL</div>
                <?php endif; ?>
                <?php if ($product['discount_percent']): ?>
                    <div style="position: absolute; top: 16px; right: 16px; background: #FFC107; color: #333; padding: 4px 10px; border-radius: 20px; font-size: 13px; font-weight: 700;">-<?php echo $product['discount_percent']; ?>%</div>
                <?php endif; ?>
                <img
                    src="<?php echo $product['image'] ? '/assets/images/' . e($product['image']) : 'https://via.placeholder.com/400x400/FF6B35/FFFFFF?text=' . urlencode($product['name']); ?>"
                    alt="<?php echo e($product['name']); ?>"
                    onerror="this.src='https://via.placeholder.com/400x400/FF6B35/FFFFFF?text=<?php echo urlencode($product['name']); ?>'"
                    style="max-width: 100%; max-height: 380px; object-fit: contain; border-radius: 12px;"
                >
            </div>
        </div>

        <!-- Информация о продукте -->
        <div>
            <!-- Категория и рейтинг -->
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <?php if ($product['category_label'] ?? $product['category_name']): ?>
                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 1px; color: var(--color-gray); text-transform: uppercase;">
                        <?php echo e($product['category_label'] ?? $product['category_name']); ?>
                    </span>
                <?php endif; ?>
                <?php if (!empty($product['rating'])): ?>
                    <div style="display: flex; align-items: center; gap: 4px; background: #FFF9E6; border-radius: 8px; padding: 4px 10px;">
                        <span style="color: #FFC107; font-size: 14px;">★</span>
                        <span style="font-weight: 700; font-size: 14px;"><?php echo number_format($product['rating'], 1); ?></span>
                        <?php if (!empty($product['reviews_count'])): ?>
                            <span style="color: var(--color-gray); font-size: 12px;">(<?php echo number_format($product['reviews_count']); ?> reviews)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Название -->
            <h1 style="font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 8px; color: var(--color-dark);">
                <?php echo e($product['name']); ?>
            </h1>

            <?php if ($product['weight']): ?>
                <p style="color: var(--color-gray); font-size: 15px; margin-bottom: 16px;"><?php echo e($product['weight']); ?></p>
            <?php endif; ?>

            <!-- Цена -->
            <div style="margin-bottom: 28px;">
                <span style="font-size: 42px; font-weight: 800; color: var(--color-dark);">
                    <?php echo formatPrice($product['price']); ?>
                </span>
                <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                    <span style="font-size: 24px; color: var(--color-gray); text-decoration: line-through; margin-left: 12px;">
                        <?php echo formatPrice($product['old_price']); ?>
                    </span>
                    <span style="font-size: 14px; color: #4CAF50; font-weight: 600; margin-left: 8px;">
                        You save <?php echo formatPrice($product['old_price'] - $product['price']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <div style="width: 10px; height: 10px; background: #4CAF50; border-radius: 50%;"></div>
                    <span style="color: #4CAF50; font-weight: 600; font-size: 14px;">In Stock (<?php echo $product['stock']; ?> units)</span>
                </div>
            <?php else: ?>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <div style="width: 10px; height: 10px; background: var(--color-primary); border-radius: 50%;"></div>
                    <span style="color: var(--color-primary); font-weight: 600; font-size: 14px;">Out of Stock</span>
                </div>
            <?php endif; ?>

            <!-- Количество + Кнопка в корзину -->
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px;">
                <div style="display: flex; align-items: center; border: 2px solid #E5E5EA; border-radius: 12px; overflow: hidden;">
                    <button onclick="changeQty(-1)" style="width: 44px; height: 48px; border: none; background: transparent; font-size: 22px; cursor: pointer; color: var(--color-dark);">−</button>
                    <span id="qty-display" style="width: 44px; text-align: center; font-weight: 700; font-size: 18px;">1</span>
                    <button onclick="changeQty(1)" style="width: 44px; height: 48px; border: none; background: transparent; font-size: 22px; cursor: pointer; color: var(--color-dark);">+</button>
                </div>
                <button
                    class="btn-checkout"
                    style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 18px; padding: 14px 24px;"
                    onclick="addProductToCart(<?php echo $product['id']; ?>)"
                    <?php echo $product['stock'] <= 0 ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : ''; ?>
                >
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>

            <?php if ($product['deal_ends_at'] && strtotime($product['deal_ends_at']) > time()): ?>
                <div style="background: #FFF3CD; border: 1px solid #FFC107; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px;">
                    <i class="far fa-clock" style="color: #FF9800;"></i>
                    <strong style="color: #E65100;"> Deal ends in:</strong>
                    <span id="product-timer" data-end-time="<?php echo $product['deal_ends_at']; ?>" style="font-weight: 700; color: var(--color-primary); font-size: 18px; margin-left: 8px;">
                        <?php echo getTimeRemaining($product['deal_ends_at']) ?? 'Soon'; ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Питательная ценность -->
            <?php if ($product['calories'] || $product['proteins'] || $product['fats'] || $product['carbs']): ?>
                <div style="background: #F8F8F8; border-radius: 16px; padding: 20px; margin-bottom: 24px; border: 1px solid #E5E5EA;">
                    <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 16px; color: var(--color-dark);">Nutrition Facts (per 100g)</h3>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; text-align: center;">
                        <?php
                        $nutrients = [
                            ['value' => $product['proteins'], 'label' => 'Protein'],
                            ['value' => $product['fats'],     'label' => 'Fats'],
                            ['value' => $product['carbs'],    'label' => 'Carbs'],
                            ['value' => $product['calories'], 'label' => 'Kcal'],
                        ];
                        foreach ($nutrients as $n): if ($n['value'] !== null): ?>
                            <div style="background: white; border-radius: 10px; padding: 14px 8px; border: 1px solid #E5E5EA;">
                                <div style="font-size: 22px; font-weight: 800; color: var(--color-dark);"><?php echo $n['value']; ?></div>
                                <div style="font-size: 12px; color: var(--color-gray); margin-top: 4px;"><?php echo $n['label']; ?></div>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Описание -->
            <?php if ($product['description']): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Description</h3>
                    <p style="color: #555; line-height: 1.7; font-size: 15px;"><?php echo nl2br(e($product['description'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Состав -->
            <?php if ($product['ingredients']): ?>
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 8px;">Ingredients</h3>
                    <p style="color: #555; line-height: 1.7; font-size: 14px;"><?php echo e($product['ingredients']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Характеристики (таблица) -->
            <?php
            $specs = [
                'Weight'             => $product['weight'] ?? null,
                'Brand'              => $product['brand'] ?? null,
                'Country'            => $product['country'] ?? null,
                'Shelf Life'         => $product['shelf_life'] ?? null,
                'Storage Conditions' => $product['storage_conditions'] ?? null,
            ];
            $specs = array_filter($specs);
            ?>
            <?php if ($specs): ?>
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px;">Details</h3>
                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                        <?php foreach ($specs as $key => $val): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 10px 0; color: var(--color-gray); width: 50%;"><?php echo $key; ?></td>
                                <td style="padding: 10px 0; font-weight: 600; text-align: right; color: var(--color-dark);"><?php echo e($val); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Похожие продукты ───────────────────────────────────────────── -->
    <?php if ($related): ?>
        <div style="margin-top: 60px;">
            <h2 style="font-size: 26px; font-weight: 700; margin-bottom: 24px;">
                <span style="color: var(--color-primary);"><i class="fas fa-shopping-cart"></i></span> You May Also Like
            </h2>
            <div class="products-grid">
                <?php foreach ($related as $rel): ?>
                    <a href="<?php echo url('product.php?id=' . $rel['id']); ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-card" style="border: 2px solid #C8C8C8; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
                             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.12)'"
                             onmouseout="this.style.transform=''; this.style.boxShadow=''">
                            <div class="product-image-wrapper">
                                <img
                                    src="<?php echo $rel['image'] ? '/assets/images/' . e($rel['image']) : 'https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=' . urlencode($rel['name']); ?>"
                                    alt="<?php echo e($rel['name']); ?>"
                                    class="product-image"
                                    onerror="this.src='https://via.placeholder.com/300x200/FF6B35/FFFFFF?text=<?php echo urlencode($rel['name']); ?>'"
                                >
                            </div>
                            <div class="product-category"><?php echo e($rel['category_label'] ?? $rel['category_name']); ?></div>
                            <h3 class="product-name"><?php echo e($rel['name']); ?></h3>
                            <div class="product-price-row">
                                <div class="product-price">
                                    <span class="price-current"><?php echo formatPrice($rel['price']); ?></span>
                                    <?php if ($rel['old_price']): ?>
                                        <span class="price-old"><?php echo formatPrice($rel['old_price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn-add-cart" data-product-id="<?php echo $rel['id']; ?>" onclick="event.preventDefault(); addToCart(<?php echo $rel['id']; ?>)">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Управление количеством
let qty = 1;
function changeQty(delta) {
    qty = Math.max(1, Math.min(99, qty + delta));
    document.getElementById('qty-display').textContent = qty;
}

// Добавляем в корзину с нужным количеством
async function addProductToCart(productId) {
    const btn = event.currentTarget;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

    try {
        const response = await fetch('api/add-to-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: qty, csrf_token: getCsrfToken() })
        });
        const data = await response.json();
        if (data.success) {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.cart_count;
                cartCount.style.display = 'flex';
            }
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.style.background = '#4CAF50';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                btn.style.background = '';
            }, 2000);
        } else {
            showToast(data.message || 'Error', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
        }
    } catch (err) {
        showToast('Network error', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
    }
}

// Таймер обратного отсчёта
const productTimer = document.getElementById('product-timer');
if (productTimer) {
    function updateProductTimer() {
        const endTime = new Date(productTimer.dataset.endTime).getTime();
        const distance = endTime - Date.now();
        if (distance <= 0) {
            productTimer.textContent = 'EXPIRED';
            return;
        }
        const h = Math.floor(distance / 3600000);
        const m = Math.floor((distance % 3600000) / 60000);
        const s = Math.floor((distance % 60000) / 1000);
        productTimer.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }
    updateProductTimer();
    setInterval(updateProductTimer, 1000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
