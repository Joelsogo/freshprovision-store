<?php
$page_title = 'My Orders';
require_once 'includes/functions.php';
require_login();

$user = current_user();

if (is_admin()) {
    // Admins see all orders
    $orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 50")->fetchAll();
} else {
    $stmt = db()->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="section-title">
    <span><?= is_admin() ? 'All Orders' : 'My Orders' ?></span>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No orders yet</h3>
        <p>Your order history will appear here</p>
        <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
    </div>
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
            <div style="font-size:13px;margin-bottom:8px">
                <span style="text-transform:capitalize"><?= e($order['order_type']) ?></span> · 
                <?= e($order['customer_name']) ?> · 
                <?= e($order['customer_phone']) ?>
            </div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px">
                <?php foreach ($order_items as $i => $item): ?>
                    <?= e($item['product_name']) ?> × <?= $item['quantity'] ?><?= $i < count($order_items) - 1 ? ', ' : '' ?>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center">
                <strong style="color:var(--primary);font-size:16px"><?= format_money($order['total']) ?></strong>
                <span style="font-size:12px;color:var(--text-muted)"><?= e(str_replace('_', ' ', $order['payment_method'])) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
