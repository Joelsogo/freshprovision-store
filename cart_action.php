<?php
require_once 'includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? 'cart.php';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        if (add_to_cart($product_id, $qty)) {
            flash('Product added to cart!');
        } else {
            flash('Could not add product.', 'error');
        }
        break;

    case 'update':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);
        update_cart_qty($product_id, $qty);
        flash('Cart updated');
        break;

    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
        remove_from_cart($product_id);
        flash('Item removed from cart');
        break;

    case 'clear':
        clear_cart();
        flash('Cart cleared');
        break;
}

header('Location: ' . $redirect);
exit;
