<?php
$page_title = 'Checkout';
require_once 'includes/functions.php';

$cart = get_cart();
if (empty($cart)) {
    flash('Your cart is empty', 'error');
    redirect('cart.php');
}

$user = current_user();
$order_type = $user['customer_type'] ?? 'retail';
$total = cart_total($order_type);

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $payment = $_POST['payment_method'] ?? 'cash_on_delivery';

    if (!$name || !$phone || !$address) {
        flash('Please fill in all required fields', 'error');
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            $order_number = generate_order_number();
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, order_number, customer_name, customer_phone, customer_email, 
                    shipping_address, order_type, subtotal, total, payment_method, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $user['id'] ?? null,
                $order_number,
                $name,
                $phone,
                $email,
                $address,
                $order_type,
                $total,
                $total,
                $payment,
                $notes
            ]);
            $order_id = $pdo->lastInsertId();

            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($cart as $item) {
                $unit_price = ($order_type === 'wholesale' && $item['qty'] >= $item['wholesale_min_qty'])
                    ? $item['wholesale_price']
                    : $item['retail_price'];
                $line_total = $unit_price * $item['qty'];

                $item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['name'],
                    $item['qty'],
                    $unit_price,
                    $line_total
                ]);

                // Reduce stock
                $stock_stmt->execute([$item['qty'], $item['id'], $item['qty']]);
            }

            $pdo->commit();
            clear_cart();
            flash("Order placed successfully! Order #{$order_number}");
            redirect('order_success.php?order=' . urlencode($order_number));
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('Order failed: ' . $e->getMessage(), 'error');
        }
    }
}

require_once 'includes/header.php';
?>

<div class="section-title"><span>Checkout</span></div>

<div class="cart-summary mb-3">
    <div class="summary-row">
        <span>Items (<?= cart_count() ?>)</span>
        <span><?= format_money($total) ?></span>
    </div>
    <div class="summary-row">
        <span>Order Type</span>
        <span style="text-transform:capitalize;font-weight:600"><?= e($order_type) ?></span>
    </div>
    <div class="summary-row total">
        <span>Total to Pay</span>
        <span><?= format_money($total) ?></span>
    </div>
</div>

<form method="POST" action="checkout.php" class="auth-card" style="max-width:100%">
    <h2 style="font-size:18px;margin-bottom:16px">Delivery Details</h2>

    <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="name" class="form-control" required
               value="<?= e($user['name'] ?? $_POST['name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone" class="form-control" required
               value="<?= e($user['phone'] ?? $_POST['phone'] ?? '') ?>" placeholder="080xxxxxxxx">
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= e($user['email'] ?? $_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Delivery Address *</label>
        <textarea name="address" class="form-control" rows="3" required
                  placeholder="Street, Area, City"><?= e($_POST['address'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label>Order Notes (optional)</label>
        <textarea name="notes" class="form-control" rows="2"
                  placeholder="Any special instructions..."><?= e($_POST['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label>Payment Method</label>
        <select name="payment_method" class="form-control">
            <option value="cash_on_delivery">Cash on Delivery</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="pos">POS on Delivery</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary btn-block" onclick="setLoading(this)">
        <i class="fas fa-check"></i> Place Order — <?= format_money($total) ?>
    </button>
</form>

<?php require_once 'includes/footer.php'; ?>
