<?php
$page_title = 'Product';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT p.*, c.name as category_name, c.slug as cat_slug, c.icon as cat_icon 
    FROM products p LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash('Product not found', 'error');
    redirect('products.php');
}

$page_title = $product['name'];
$user = current_user();
$order_type = $user['customer_type'] ?? 'retail';

// Related products
$related = db()->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY RANDOM() LIMIT 4");
$related->execute([$product['category_id'], $product['id']]);
$related_products = $related->fetchAll();

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <a href="index.php">Home</a> / 
    <a href="products.php?category=<?= e($product['cat_slug']) ?>"><?= e($product['category_name']) ?></a> / 
    <span><?= e($product['name']) ?></span>
</div>

<div class="product-detail">
    <div class="product-detail-img">
        <div class="big-emoji"><?= product_image_emoji($product['image']) ?></div>
        <?php if ($product['stock'] < 10 && $product['stock'] > 0): ?>
            <span class="stock-badge low">Only <?= $product['stock'] ?> left</span>
        <?php elseif ($product['stock'] < 1): ?>
            <span class="stock-badge out">Out of Stock</span>
        <?php endif; ?>
    </div>

    <div class="product-detail-info">
        <div class="product-cat"><?= $product['cat_icon'] ?> <?= e($product['category_name']) ?></div>
        <h1><?= e($product['name']) ?></h1>
        <p class="product-desc"><?= e($product['description'] ?: 'Quality provision product from FreshProvision store.') ?></p>

        <div class="price-box">
            <div class="price-main">
                <span class="label">Retail</span>
                <span class="amount"><?= format_money($product['retail_price']) ?></span>
                <span class="unit">/ <?= e($product['unit']) ?></span>
            </div>
            <div class="price-wholesale-box">
                <span class="label">Wholesale</span>
                <span class="amount"><?= format_money($product['wholesale_price']) ?></span>
                <span class="note">Min. <?= $product['wholesale_min_qty'] ?> <?= e($product['unit']) ?>s</span>
            </div>
        </div>

        <div class="stock-info">
            <i class="fas fa-box"></i>
            <?php if ($product['stock'] > 0): ?>
                <span class="in-stock"><?= $product['stock'] ?> in stock</span>
            <?php else: ?>
                <span class="out-stock">Currently unavailable</span>
            <?php endif; ?>
        </div>

        <?php if ($product['stock'] > 0): ?>
            <form action="cart_action.php" method="POST" class="add-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="qty-selector">
                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                    <input type="number" name="qty" id="qty" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-shopping-basket"></i> Add to Cart
                </button>
            </form>
        <?php endif; ?>

        <div class="product-meta">
            <div><i class="fas fa-truck"></i> Fast delivery available</div>
            <div><i class="fas fa-shield-alt"></i> Quality guaranteed</div>
            <div><i class="fas fa-undo"></i> Easy returns</div>
        </div>
    </div>
</div>

<?php if (!empty($related_products)): ?>
<section class="mt-3">
    <div class="section-title"><span>You may also like</span></div>
    <div class="product-grid">
        <?php foreach ($related_products as $p): ?>
            <a href="product.php?id=<?= $p['id'] ?>" class="product-card">
                <div class="product-img"><?= product_image_emoji($p['image']) ?></div>
                <div class="product-body">
                    <div class="product-name"><?= e($p['name']) ?></div>
                    <div class="price-retail"><?= format_money($p['retail_price']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script>
function changeQty(delta) {
    const input = document.getElementById('qty');
    let val = parseInt(input.value) || 1;
    val = Math.max(1, Math.min(<?= $product['stock'] ?>, val + delta));
    input.value = val;
}
</script>

<?php require_once 'includes/footer.php'; ?>
