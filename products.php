<?php
$page_title = 'Shop';
require_once 'includes/header.php';

$category_slug = $_GET['category'] ?? null;
$search = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? null;

// Update user type preference if logged in and type requested
if ($type && is_logged_in() && in_array($type, ['retail', 'wholesale'])) {
    $stmt = db()->prepare("UPDATE users SET customer_type = ? WHERE id = ?");
    $stmt->execute([$type, $_SESSION['user_id']]);
    // Refresh user
    unset($GLOBALS['user']);
}

$sql = "SELECT p.*, c.name as category_name, c.slug as cat_slug, c.icon as cat_icon 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";
$params = [];

if ($category_slug) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
}
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY p.name ASC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$user = current_user();
?>

<div class="section-title">
    <span>All Products</span>
    <span class="text-muted" style="font-size:13px"><?= count($products) ?> items</span>
</div>

<!-- Search -->
<form method="GET" action="products.php" class="mb-3">
    <?php if ($category_slug): ?>
        <input type="hidden" name="category" value="<?= e($category_slug) ?>">
    <?php endif; ?>
    <div style="display:flex;gap:8px">
        <input type="search" name="q" class="form-control" placeholder="Search products..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </div>
</form>

<!-- Categories -->
<div class="categories-scroll">
    <a href="products.php" class="cat-chip <?= !$category_slug ? 'active' : '' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
        <a href="products.php?category=<?= e($cat['slug']) ?>" class="cat-chip <?= $category_slug === $cat['slug'] ? 'active' : '' ?>">
            <?= $cat['icon'] ?> <?= e($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>No products found</h3>
        <p>Try a different search or category</p>
        <a href="products.php" class="btn btn-primary mt-2">View All</a>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $p): 
            $display_price = get_product_price($p, 1);
            $is_wholesale_eligible = $user && $user['customer_type'] === 'wholesale';
        ?>
            <div class="product-card">
                <div class="product-img"><?= product_image_emoji($p['image']) ?></div>
                <div class="product-body">
                    <div class="product-name"><?= e($p['name']) ?></div>
                    <div class="product-unit">per <?= e($p['unit']) ?> · Stock: <?= $p['stock'] ?></div>
                    <div class="price-row">
                        <span class="price-retail"><?= format_money($p['retail_price']) ?></span>
                        <span class="price-wholesale">
                            Wholesale: <?= format_money($p['wholesale_price']) ?>
                            <?php if ($p['wholesale_min_qty'] > 1): ?>
                                (min <?= $p['wholesale_min_qty'] ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                    <form action="cart_action.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn btn-primary btn-sm btn-block" <?= $p['stock'] < 1 ? 'disabled' : '' ?>>
                            <?= $p['stock'] < 1 ? 'Out of Stock' : '<i class="fas fa-plus"></i> Add to Cart' ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
