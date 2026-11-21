<?php
require_once '../includes/functions.php';
require_admin();
$page_title = 'Manage Orders';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = db()->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    flash('Order status updated');
    redirect('orders.php');
}

$orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="section-title">
    <span>All Orders</span>
    <a href="index.php" style="font-size:13px;color:var(--primary)">← Dashboard</a>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state"><i class="fas fa-receipt"></i><h3>No orders yet</h3></div>
<?php else: ?>
    <?php foreach ($orders as $order): 
        $items = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$order['id']]);
        $order_items = $items->fetchAll();
    ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-number"><?= e($order['order_number']) ?></div>
                    <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                </div>
                <span class="status status-<?= e($order['status']) ?>"><?= e($order['status']) ?></span>
            </div>
            <div style="font-size:13px;margin-bottom:6px">
                <strong><?= e($order['customer_name']) ?></strong> · <?= e($order['customer_phone']) ?><br>
                <?= e($order['shipping_address']) ?>
            </div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px">
                <?php foreach ($order_items as $item): ?>
                    • <?= e($item['product_name']) ?> × <?= $item['quantity'] ?> = <?= format_money($item['total_price']) ?><br>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <strong style="color:var(--primary)"><?= format_money($order['total']) ?></strong>
                <form method="POST" style="display:flex;gap:6px;align-items:center">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="status" class="form-control" style="width:auto;padding:6px 10px;font-size:13px">
                        <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
