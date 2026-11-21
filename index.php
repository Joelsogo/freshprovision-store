<?php
$page_title = 'Home';
require_once 'includes/header.php';

// Get categories
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Featured / recent products
$products = db()->query("SELECT p.*, c.name as category_name, c.icon as cat_icon 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.id DESC LIMIT 8")->fetchAll();

$user = current_user();
?>

<div class="hero">
    <h1>Welcome to <?= e(APP_NAME) ?></h1>
    <p>Your trusted provision store for wholesale & retail supplies</p>
    <?php if ($user): ?>
        <p style="font-size:13px;opacity:0.85">Hello, <?= e($user['name']) ?>!</p>
    <?php endif; ?>
    <div class="mode-toggle">
        <a href="products.php?type=retail" class="<?= (!$user || $user['customer_type'] === 'retail') ? 'active' : '' ?>">
            🛒 Retail
        </a>
        <a href="products.php?type=wholesale" class="<?= ($user && $user['customer_type'] === 'wholesale') ? 'active' : '' ?>">
            📦 Wholesale
        </a>
    </div>
</div>

<section>
    <div class="section-title">
        <span>Categories</span>
        <a href="products.php" style="font-size:13px;color:var(--primary)">See all</a>
    </div>
    <div class="categories-scroll">
        <?php foreach ($categories as $cat): ?>
            <a href="products.php?category=<?= e($cat['slug']) ?>" class="cat-chip">
                <?= $cat['icon'] ?> <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-title">
        <span>Popular Products</span>
        <a href="products.php" style="font-size:13px;color:var(--primary)">View all</a>
    </div>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <div class="product-img"><?= product_image_emoji($p['image']) ?></div>
                <div class="product-body">
                    <div class="product-name"><?= e($p['name']) ?></div>
                    <div class="product-unit">per <?= e($p['unit']) ?></div>
                    <div class="price-row">
                        <span class="price-retail"><?= format_money($p['retail_price']) ?></span>
                        <span class="price-wholesale">Wholesale: <?= format_money($p['wholesale_price']) ?> (min <?= $p['wholesale_min_qty'] ?>)</span>
                    </div>
                    <form action="cart_action.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn btn-primary btn-sm btn-block" <?= $p['stock'] < 1 ? 'disabled' : '' ?>>
                            <?= $p['stock'] < 1 ? 'Out of Stock' : '<i class="fas fa-plus"></i> Add' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="mt-3 text-center">
    <a href="products.php" class="btn btn-outline">Browse All Products</a>
</div>

<?php require_once 'includes/footer.php'; ?>
