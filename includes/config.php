<?php
/**
 * FreshProvision — Configuration
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

define('ROOT_PATH', dirname(__DIR__));
define('DB_PATH', ROOT_PATH . '/data/store.db');

$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', (substr($script_dir, -6) === '/admin') ? '../' : '');

define('APP_NAME', 'FreshProvision');
define('APP_TAGLINE', 'Wholesale & Retail Provision Store');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

date_default_timezone_set('Africa/Lagos');

if (!is_dir(ROOT_PATH . '/data')) @mkdir(ROOT_PATH . '/data', 0755, true);
if (!is_dir(ROOT_PATH . '/uploads/products')) @mkdir(ROOT_PATH . '/uploads/products', 0755, true);

require_once __DIR__ . '/security.php';
security_headers();
