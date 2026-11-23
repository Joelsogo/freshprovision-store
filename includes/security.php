<?php
/**
 * Security helpers — CSRF, rate limiting, headers, input sanitization
 */

function security_headers(): void {
    if (headers_sent()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://js.paystack.co https://checkout.flutterwave.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: https: blob:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self' https://api.paystack.co https://api.flutterwave.com; frame-src https://js.paystack.co https://checkout.flutterwave.com;");
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool {
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || empty($_SESSION['_csrf'])) return false;
    return hash_equals($_SESSION['_csrf'], $token);
}

function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        http_response_code(403);
        flash('Security check failed. Please try again.', 'error');
        $ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $ref);
        exit;
    }
}

function rate_limit(string $key, int $max = 10, int $window = 60): bool {
    $now = time();
    if (!isset($_SESSION['_rl'][$key])) {
        $_SESSION['_rl'][$key] = ['count' => 0, 'start' => $now];
    }
    $bucket = &$_SESSION['_rl'][$key];
    if ($now - $bucket['start'] > $window) {
        $bucket = ['count' => 0, 'start' => $now];
    }
    $bucket['count']++;
    return $bucket['count'] <= $max;
}

function require_rate_limit(string $key, int $max = 10, int $window = 60): void {
    if (!rate_limit($key, $max, $window)) {
        http_response_code(429);
        flash('Too many attempts. Please wait a moment and try again.', 'error');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
}

function sanitize_string(?string $s, int $maxLen = 500): string {
    $s = trim((string)$s);
    $s = strip_tags($s);
    if (mb_strlen($s) > $maxLen) $s = mb_substr($s, 0, $maxLen);
    return $s;
}

function sanitize_email(?string $email): string {
    $email = filter_var(trim((string)$email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function sanitize_int($v, int $min = 0, int $max = PHP_INT_MAX): int {
    return max($min, min($max, (int)$v));
}

function sanitize_float($v, float $min = 0, float $max = 1e12): float {
    return max($min, min($max, (float)$v));
}

function secure_session_regenerate(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
