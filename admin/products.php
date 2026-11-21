<?php
require_once '../includes/functions.php';
require_admin();
$page_title = 'Manage Products & Stock';

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
            flash('Product added successfully');
        } else {
            $data[] = $id;
            $stmt = db()->prepare("UPDATE products SET name=?, description=?, category_id=?, unit=?, retail_price=?, wholesale_price=?, wholesale_min_qty=?, stock=?, image=? WHERE id=?");
            $stmt->execute($data);
            flash('Product updated successfully');
        }
        redirect('products.php');
    }

    if ($action === 'adjust_stock') {
        $id = (int)$_POST['id'];
        $adjustment = (int)$_POST['adjustment'];
        $stmt = db()->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $current = (int)$stmt->fetchColumn();
        $newStock = max(0, $current + $adjustment);
        db()->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$newStock, $id]);
        flash('Stock updated to ' . $newStock);
        redirect('products.php');
    }

    if ($action === 'set_stock') {
        $id = (int)$_POST['id'];
        $stock = max(0, (int)$_POST['stock']);
        db()->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$stock, $id]);
        flash('Stock set to ' . $stock);
        redirect('products.php');
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        db()->prepare("UPDATE products SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
        flash('Product status updated');
        redirect('products.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        db()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        flash('Product deleted');
        redirect('products.php');
    }
}

$products = db()->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.stock ASC, p.name")->fetchAll();
$categories = db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$low_stock_count = 0;
foreach ($products as $p) {
    if ($p['stock'] < 15 && $p['is_active']) $low_stock_count++;
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

require_once '../includes/header.php';
?>

<div class="section-title">
    <span>Products & Stock</span>
    <a href="index.php" style="font-size:13px;color:var(--primary)">← Dashboard</a>
</div>

<?php if ($low_stock_count > 0): ?>
<div class="flash flash-error" style="margin-bottom:16px">
    <strong>⚠️ Low Stock Alert:</strong> <?= $low_stock_count ?> product(s) have less than 15 units left.
</div>
<?php endif; ?>

<div class="auth-card" style="max-width:100%;margin-bottom:20px">
    <h3 style="margin-bottom:14px"><?= $edit ? '✏️ Edit Product' : '➕ Add New Product' ?></h3>
    <form method="POST">
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        
        <div class="form-group">
            <label>Product Name *</label>
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
                <label>Unit (bag, carton...)</label>
                <input type="text" name="unit" class="form-control" value="<?= e($edit['unit'] ?? 'unit') ?>">
            </div>
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= e($edit['stock'] ?? 0) ?>" required>
            </div>
            <div class="form-group">
                <label>Retail Price (₦) *</label>
                <input type="number" step="0.01" name="retail_price" class="form-control" required value="<?= e($edit['retail_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Wholesale Price (₦) *</label>
                <input type="number" step="0.01" name="wholesale_price" class="form-control" required value="<?= e($edit['wholesale_price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Wholesale Min Qty</label>
                <input type="number" name="wholesale_min_qty" class="form-control" value="<?= e($edit['wholesale_min_qty'] ?? 10) ?>">
            </div>
            <div class="form-group">
                <label>Image Key</label>
                <input type="text" name="image" class="form-control" value="<?= e($edit['image'] ?? 'product') ?>" placeholder="rice, oil, noodles...">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><?= $edit ? 'Update Product' : 'Add Product' ?></button>
        <?php if ($edit): ?>
            <a href="products.php" class="btn btn-outline btn-block mt-2">Cancel Edit</a>
        <?php endif; ?>
    </form>
</div>

<div class="section-title"><span>All Products (<?= count($products) ?>)</span></div>

<?php foreach ($products as $p): 
    $is_low = $p['stock'] < 15;
?>
    <div class="order-card" style="<?= $is_low ? 'border-left:4px solid var(--danger)' : '' ?>">
        <div class="order-header">
            <div>
                <div class="order-number">
                    <?= product_image_emoji($p['image']) ?> <?= e($p['name']) ?>
                    <?php if (!$p['is_active']): ?> <span class="status status-cancelled">Hidden</span><?php endif; ?>
                </div>
                <div class="order-date"><?= e($p['cat_name']) ?> · per <?= e($p['unit']) ?></div>
            </div>
            <div style="text-align:right">
                <div style="font-size:20px;font-weight:700;color:<?= $is_low ? 'var(--danger)' : 'var(--primary)' ?>">
                    <?= $p['stock'] ?>
                </div>
                <div style="font-size:11px;color:var(--text-muted)">in stock</div>
            </div>
        </div>

        <div style="font-size:13px;margin-bottom:12px">
            Retail: <strong><?= format_money($p['retail_price']) ?></strong> · 
            Wholesale: <strong><?= format_money($p['wholesale_price']) ?></strong>
            (min <?= $p['wholesale_min_qty'] ?>)
        </div>

        <div style="background:#f8fafc;padding:10px;border-radius:8px;margin-bottom:10px">
            <div style="font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text-muted)">Quick Stock Adjust</div>
            <form method="POST" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                <input type="hidden" name="action" value="adjust_stock">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" name="adjustment" value="-10" class="btn btn-sm btn-outline">−10</button>
                <button type="submit" name="adjustment" value="-1" class="btn btn-sm btn-outline">−1</button>
                <button type="submit" name="adjustment" value="1" class="btn btn-sm btn-outline">+1</button>
                <button type="submit" name="adjustment" value="10" class="btn btn-sm btn-outline">+10</button>
                <button type="submit" name="adjustment" value="50" class="btn btn-sm btn-outline">+50</button>
            </form>
            <form method="POST" style="display:flex;gap:6px;align-items:center;margin-top:8px">
                <input type="hidden" name="action" value="set_stock">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="number" name="stock" class="form-control" style="width:90px;padding:6px 10px" value="<?= $p['stock'] ?>" min="0">
                <button type="submit" class="btn btn-sm btn-primary">Set Exact</button>
            </form>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">Edit Full</a>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline"><?= $p['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product permanently?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>
