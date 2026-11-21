<?php
require_once '../includes/functions.php';
require_admin();
$page_title = 'Manage Products';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            trim($_POST['name'] ?? ''),
            trim($_POST['description'] ?? ''),
            (int)($_POST['category_id'] ?? 1),
            trim($_POST['unit'] ?? 'unit'),
            (float)($_POST['retail_price'] ?? 0),
            (float)($_POST['wholesale_price'] ?? 0),
            (int)($_POST['wholesale_min_qty'] ?? 10),
            (int)($_POST['stock'] ?? 0),
            trim($_POST['image'] ?? 'product'),
        ];
        if ($action === 'add') {
            $stmt = db()->prepare("INSERT INTO products (name, description, category_id, unit, retail_price, wholesale_price, wholesale_min_qty, stock, image) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute($data);
            flash('Product added');
        } else {
            $data[] = $id;
            $stmt = db()->prepare("UPDATE products SET name=?, description=?, category_id=?, unit=?, retail_price=?, wholesale_price=?, wholesale_min_qty=?, stock=?, image=? WHERE id=?");
            $stmt->execute($data);
            flash('Product updated');
        }
        redirect('products.php');
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        db()->prepare("UPDATE products SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        flash('Product status updated');
        redirect('products.php');
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        flash('Product deleted');
        redirect('products.php');
    }
}

$products = db()->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name")->fetchAll();
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

require_once '../includes/header.php';
?>

<div class="section-title">
    <span>Products</span>
    <a href="index.php" style="font-size:13px;color:var(--primary)">← Dashboard</a>
</div>

<div class="auth-card" style="max-width:100%;margin-bottom:20px">
    <h3 style="margin-bottom:14px"><?= $edit ? 'Edit Product' : 'Add New Product' ?></h3>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required value="<?= e($edit['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="2"><?= e($edit['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="form-group">
                <label>Unit</label>
                <input type="text" name="unit" class="form-control" value="<?= e($edit['unit'] ?? 'unit') ?>">
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" class="form-control" value="<?= e($edit['stock'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label>Retail Price (₦)</label>
                <input type="number" step="0.01" name="retail_price" class="form-control" required value="<?= e($edit['retail_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Wholesale Price (₦)</label>
                <input type="number" step="0.01" name="wholesale_price" class="form-control" required value="<?= e($edit['wholesale_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Wholesale Min Qty</label>
                <input type="number" name="wholesale_min_qty" class="form-control" value="<?= e($edit['wholesale_min_qty'] ?? 10) ?>">
            </div>
            <div class="form-group">
                <label>Image Key</label>
                <input type="text" name="image" class="form-control" value="<?= e($edit['image'] ?? 'product') ?>" placeholder="rice, oil, etc">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><?= $edit ? 'Update' : 'Add Product' ?></button>
        <?php if ($edit): ?>
            <a href="products.php" class="btn btn-outline btn-block mt-2">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<?php foreach ($products as $p): ?>
    <div class="order-card">
        <div class="order-header">
            <div>
                <div class="order-number"><?= product_image_emoji($p['image']) ?> <?= e($p['name']) ?></div>
                <div class="order-date"><?= e($p['cat_name']) ?> · Stock: <?= $p['stock'] ?> · <?= $p['is_active'] ? 'Active' : 'Hidden' ?></div>
            </div>
        </div>
        <div style="font-size:13px;margin-bottom:10px">
            Retail: <strong><?= format_money($p['retail_price']) ?></strong> · 
            Wholesale: <strong><?= format_money($p['wholesale_price']) ?></strong> (min <?= $p['wholesale_min_qty'] ?>)
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline"><?= $p['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>
