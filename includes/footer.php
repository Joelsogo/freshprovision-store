</main>

    <!-- Bottom Navigation (Mobile App Style) -->
    <nav class="bottom-nav">
        <a href="<?= BASE_URL ?>index.php" class="nav-item <?= $current_page === 'index' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?= BASE_URL ?>products.php" class="nav-item <?= $current_page === 'products' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i>
            <span>Shop</span>
        </a>
        <a href="<?= BASE_URL ?>cart.php" class="nav-item <?= $current_page === 'cart' ? 'active' : '' ?>">
            <i class="fas fa-shopping-basket"></i>
            <span>Cart</span>
            <?php if ($cart_items > 0): ?>
                <span class="nav-badge"><?= $cart_items ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>orders.php" class="nav-item <?= $current_page === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i>
            <span>Orders</span>
        </a>
        <a href="<?= BASE_URL . (is_logged_in() ? 'account.php' : 'login.php') ?>" class="nav-item <?= in_array($current_page, ['account', 'login', 'register']) ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Account</span>
        </a>
    </nav>

    <?php
    $wa = function_exists('whatsapp_link') ? whatsapp_link() : null;
    if ($wa):
    ?>
    <a href="<?= e($wa) ?>" class="whatsapp-float" target="_blank" rel="noopener" title="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
