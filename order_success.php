<?php
$page_title = 'Order Confirmed';
require_once 'includes/header.php';

$order_number = $_GET['order'] ?? '';
$order = null;
if ($order_number) {
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$order_number]);
    $order = $stmt->fetch();
}
?>

<div class="empty-state" style="padding-top:32px">
    <div style="font-size:64px;margin-bottom:12px">✅</div>
    <h2 style="color:var(--primary);margin-bottom:8px">Order Placed!</h2>
    <?php if ($order): ?>
        <p class="text-muted mb-2">Order Number</p>
        <p style="font-size:20px;font-weight:700;margin-bottom:16px"><?= e($order['order_number']) ?></p>
        <p>Total: <strong><?= format_money($order['total']) ?></strong></p>
        <p class="text-muted" style="font-size:13px;margin-top:8px">
            Status: <span class="status status-<?= e($order['status']) ?>"><?= e($order['status']) ?></span>
        </p>
        <p style="margin-top:16px;font-size:14px">
            We'll contact you on <strong><?= e($order['customer_phone']) ?></strong> to confirm delivery.
        </p>
    <?php else: ?>
        <p>Thank you for your order!</p>
    <?php endif; ?>

    <div class="mt-3" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <a href="orders.php" class="btn btn-primary">View My Orders</a>
        <a href="products.php" class="btn btn-outline">Continue Shopping</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
