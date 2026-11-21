<?php
require_once '../includes/functions.php';
require_admin();

$page_title = 'Admin Dashboard';

// Stats
$stats = [
    'products' => db()->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'orders' => db()->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending' => db()->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'revenue' => db()->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn(),
    'customers' => db()->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];

$recent_orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();

require_once '../includes/header.php';
?>

<div class="section-title">
    <span>Admin Dashboard</span>
    <a href="../index.php" style="font-size:13px;color:var(--primary)">← Storefront</a>
</div>

<div class="admin-stats">
    <div class="stat-card">
        <div class="value"><?= $stats['orders'] ?></div>
        <div class="label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= $stats['pending'] ?></div>
        <div class="label">Pending</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= format_money($stats['revenue']) ?></div>
        <div class="label">Revenue</div>
    </div>
    <div class="stat-card">
        <div class="value"><?= $stats['products'] ?></div>
        <div class="label">Products</div>
    </div>
</div>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
    <a href="products.php" class="btn btn-primary btn-sm"><i class="fas fa-box"></i> Manage Products</a>
    <a href="orders.php" class="btn btn-outline btn-sm"><i class="fas fa-receipt"></i> Manage Orders</a>
</div>

<div class="section-title"><span>Recent Orders</span></div>
<?php if (empty($recent_orders)): ?>
    <p class="text-muted">No orders yet.</p>
<?php else: ?>
    <?php foreach ($recent_orders as $o): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-number"><?= e($o['order_number']) ?></div>
                    <div class="order-date"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></div>
                </div>
                <span class="status status-<?= e($o['status']) ?>"><?= e($o['status']) ?></span>
            </div>
            <div style="font-size:13px">
                <?= e($o['customer_name']) ?> · <?= format_money($o['total']) ?> · 
                <span style="text-transform:capitalize"><?= e($o['order_type']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
