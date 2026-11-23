<?php
/**
 * App Settings — currency, WhatsApp, payments, banners, news helpers
 */

function get_setting(string $key, $default = null) {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    try {
        $stmt = db()->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['value'] : $default;
        return $cache[$key];
    } catch (Exception $e) {
        return $default;
    }
}

function set_setting(string $key, $value): void {
    try {
        $stmt = db()->prepare("INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value");
        $stmt->execute([$key, $value]);
    } catch (Exception $e) {
        $exists = db()->prepare("SELECT 1 FROM settings WHERE key = ?");
        $exists->execute([$key]);
        if ($exists->fetch()) {
            db()->prepare("UPDATE settings SET value = ? WHERE key = ?")->execute([$value, $key]);
        } else {
            db()->prepare("INSERT INTO settings (key, value) VALUES (?, ?)")->execute([$key, $value]);
        }
    }
}

function currency_symbol(): string {
    return get_setting('currency_symbol', '₦');
}

function currency_code(): string {
    return get_setting('currency_code', 'NGN');
}

function whatsapp_link(string $message = ''): ?string {
    $number = get_setting('whatsapp_number', '');
    if (!$number) return null;
    $number = preg_replace('/[^0-9]/', '', $number);
    $text = urlencode($message ?: 'Hello, I have a question about ' . APP_NAME);
    return "https://wa.me/{$number}?text={$text}";
}

function active_promotions(): array {
    try {
        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare("SELECT * FROM promotions WHERE is_active = 1 AND (starts_at IS NULL OR starts_at <= ?) AND (ends_at IS NULL OR ends_at >= ?) ORDER BY id DESC");
        $stmt->execute([$now, $now]);
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function active_banners(): array {
    try {
        $stmt = db()->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function active_news(int $limit = 5): array {
    try {
        $stmt = db()->prepare("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        return [];
    }
}

function apply_promo_to_total(float $total): array {
    $promos = active_promotions();
    $best = null;
    $discount = 0.0;
    foreach ($promos as $p) {
        if ($p['type'] === 'percent') {
            $d = $total * ((float)$p['value'] / 100);
        } else {
            $d = (float)$p['value'];
        }
        if ($p['min_order'] > 0 && $total < (float)$p['min_order']) continue;
        if ($d > $discount) {
            $discount = $d;
            $best = $p;
        }
    }
    $discount = min($discount, $total);
    return ['promo' => $best, 'discount' => $discount, 'final' => max(0, $total - $discount)];
}
