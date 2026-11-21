<?php
/**
 * Helper Functions
 */

require_once __DIR__ . '/db.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function format_money($amount) {
    return CURRENCY . number_format((float)$amount, 0);
}

function generate_order_number() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function is_admin() {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

function get_cart() {
    return $_SESSION['cart'] ?? [];
}

function cart_count() {
    $cart = get_cart();
    return array_sum(array_column($cart, 'qty'));
}

function cart_total($order_type = null) {
    $cart = get_cart();
    if (empty($cart)) return 0;
    $total = 0;
    $user = current_user();
    $type = $order_type ?? ($user['customer_type'] ?? 'retail');

    foreach ($cart as $item) {
        $price = ($type === 'wholesale' && $item['qty'] >= $item['wholesale_min_qty'])
            ? $item['wholesale_price']
            : $item['retail_price'];
        $total += $price * $item['qty'];
    }
    return $total;
}

function add_to_cart($product_id, $qty = 1) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if (!$product) return false;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $key = (string)$product_id;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'unit' => $product['unit'],
            'retail_price' => $product['retail_price'],
            'wholesale_price' => $product['wholesale_price'],
            'wholesale_min_qty' => $product['wholesale_min_qty'],
            'stock' => $product['stock'],
            'image' => $product['image'],
            'qty' => $qty,
        ];
    }
    // Cap at stock
    if ($_SESSION['cart'][$key]['qty'] > $product['stock']) {
        $_SESSION['cart'][$key]['qty'] = $product['stock'];
    }
    return true;
}

function update_cart_qty($product_id, $qty) {
    $key = (string)$product_id;
    if (!isset($_SESSION['cart'][$key])) return false;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$key]);
    } else {
        $stock = $_SESSION['cart'][$key]['stock'];
        $_SESSION['cart'][$key]['qty'] = min($qty, $stock);
    }
    return true;
}

function remove_from_cart($product_id) {
    unset($_SESSION['cart'][(string)$product_id]);
}

function clear_cart() {
    unset($_SESSION['cart']);
}

function get_product_price($product, $qty = 1, $force_type = null) {
    $user = current_user();
    $type = $force_type ?? ($user['customer_type'] ?? 'retail');
    if ($type === 'wholesale' && $qty >= $product['wholesale_min_qty']) {
        return $product['wholesale_price'];
    }
    return $product['retail_price'];
}

function flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function product_image_emoji($image_key) {
    $map = [
        'rice' => '🍚', 'beans' => '🫘', 'garri' => '🥣', 'semolina' => '🌾',
        'flour' => '🍞', 'oil' => '🫒', 'palm-oil' => '🟥', 'sardine' => '🐟',
        'tomato' => '🍅', 'beans-can' => '🥫', 'corned-beef' => '🥩',
        'noodles' => '🍜', 'milk' => '🥛', 'milo' => '🍫', 'drinks' => '🥤',
        'maggi' => '🧂', 'curry' => '🍛', 'thyme' => '🌿', 'pepper' => '🌶️',
        'detergent' => '🧼', 'soap' => '🧴', 'tissue' => '🧻', 'eggs' => '🥚',
        'butter' => '🧈', 'biscuits' => '🍪', 'chinchin' => '🥨',
    ];
    return $map[$image_key] ?? '🛒';
}
