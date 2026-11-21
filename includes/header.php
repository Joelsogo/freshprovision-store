<?php
require_once __DIR__ . '/functions.php';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$user = current_user();
$cart_items = cart_count();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#166534">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= e($page_title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Top Header -->
    <header class="app-header">
        <div class="header-top">
            <a href="<?= BASE_URL ?>index.php" class="logo">
                <span class="logo-icon">🥬</span>
                <div>
                    <strong><?= e(APP_NAME) ?></strong>
                    <small><?= e(APP_TAGLINE) ?></small>
                </div>
            </a>
            <div class="header-actions">
                <?php if ($user): ?>
                    <a href="<?= BASE_URL ?>account.php" class="icon-btn" title="Account">
                        <i class="fas fa-user-circle"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="icon-btn" title="Login">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>cart.php" class="icon-btn cart-btn" title="Cart">
                    <i class="fas fa-shopping-basket"></i>
                    <?php if ($cart_items > 0): ?>
                        <span class="badge"><?= $cart_items ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
        <?php if ($user && $user['customer_type'] === 'wholesale'): ?>
            <div class="wholesale-banner">
                <i class="fas fa-warehouse"></i> Wholesale Mode Active — Better prices on bulk orders
            </div>
        <?php endif; ?>
    </header>

    <?php if ($flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
            <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
        </div>
    <?php endif; ?>

    <main class="app-main">
