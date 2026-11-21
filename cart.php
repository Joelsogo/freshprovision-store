<?php
$page_title = 'Cart';
require_once 'includes/header.php';

$cart = get_cart();
$user = current_user();
$order_type = $user['customer_type'] ?? 'retail';
?>

<div class="section-title">
    <span>Your Cart</span>
    <?php if (!empty($cart)): ?>
        <form action="cart_action.php" method="POST" style="display:inline">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btn btn-sm" style="color:var(--danger);background:none;border:none;font-size:13px">Clear all</button>
        </form>
    <?php endif; ?>
</div>

<?php if (empty($cart)): ?>
    <div class="empty-state">
        <i class="fas fa-shopping-basket"></i>
        <h3>Your cart is empty</h3>
        <p>Browse our provision store and add items</p>
        <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
    </div>
<?php else: ?>
    <?php foreach ($cart as $item): 
        $unit_price = ($order_type === 'wholesale' && $item['qty'] >= $item['wholesale_min_qty'])
            ? $item['wholesale_price']
            : $item['retail_price'];
        $line_total = $unit_price * $item['qty'];
        $is_wholesale_price = ($order_type === 'wholesale' && $item['qty'] >= $item['wholesale_min_qty']);
    ?>
        <div class="cart-item">
            <div class="cart-item-img"><?= product_image_emoji($item['image']) ?></div>
            <div class="cart-item-info">
                <div class="cart-item-name"><?= e($item['name']) ?></div>
                <div class="cart-item-price">
                    <?= format_money($unit_price) ?> / <?= e($item['unit']) ?>
                    <?php if ($is_wholesale_price): ?>
                        <span style="font-size:11px;color:var(--accent);font-weight:600"> · Wholesale</span>
                    <?php endif; ?>
                </div>
                <div class="qty-control">
                    <form action="cart_action.php" method="POST" style="display:flex;align-items:center;gap:8px">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" name="qty" value="<?= max(1, $item['qty'] - 1) ?>" class="qty-btn" data-action="minus">−</button>
                        <span class="qty-value"><?= $item['qty'] ?></span>
                        <button type="submit" name="qty" value="<?= $item['qty'] + 1 ?>" class="qty-btn" data-action="plus">+</button>
                    </form>
                    <form action="cart_action.php" method="POST" style="margin-left:auto">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="qty-btn" style="color:var(--danger)" title="Remove">
                            <i class="fas fa-trash-alt" style="font-size:13px"></i>
                        </button>
                    </form>
                </div>
                <div style="font-size:13px;font-weight:600;margin-top:6px">
                    Subtotal: <?= format_money($line_total) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="cart-summary">
        <div class="summary-row">
            <span>Items</span>
            <span><?= cart_count() ?></span>
        </div>
        <div class="summary-row">
            <span>Order Type</span>
            <span style="text-transform:capitalize"><?= e($order_type) ?></span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span><?= format_money(cart_total($order_type)) ?></span>
        </div>
        <a href="checkout.php" class="btn btn-primary btn-block mt-3">
            Proceed to Checkout <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <?php if ($order_type === 'retail'): ?>
        <p class="text-center text-muted mt-3" style="font-size:13px">
            <i class="fas fa-info-circle"></i> Switch to <strong>Wholesale</strong> account for bulk discounts
            <?php if (!$user): ?>
                — <a href="register.php" style="color:var(--primary)">Register</a>
            <?php elseif ($user['customer_type'] === 'retail'): ?>
                — <a href="account.php" style="color:var(--primary)">Upgrade in Account</a>
            <?php endif; ?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
