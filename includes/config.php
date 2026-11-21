<?php
/**
 * Provision Store E-Commerce - Configuration
 * Pure PHP + SQLite | Wholesale & Retail
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Paths
define('ROOT_PATH', dirname(__DIR__));
// SQLite database path (make sure the data/ folder is writable on your host)
define('DB_PATH', ROOT_PATH . '/data/store.db');

// Base URL for assets/links (handles /admin/ subdirectory)
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', (substr($script_dir, -6) === '/admin') ? '../' : '');

// App settings
define('APP_NAME', 'FreshProvision');
define('APP_TAGLINE', 'Wholesale & Retail Provision Store');
define('CURRENCY', '₦');
define('CURRENCY_CODE', 'NGN');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Africa/Lagos');

// Ensure data directory exists
if (!is_dir(ROOT_PATH . '/data')) {
    mkdir(ROOT_PATH . '/data', 0755, true);
}
