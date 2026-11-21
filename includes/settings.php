<?php
/**
 * App Settings helper (WhatsApp, payments, etc.)
 */

function get_setting(string $key, $default = null) {
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

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

function set_setting(string $key, $value) {
    try {
        $stmt = db()->prepare("
            INSERT INTO settings (key, value) VALUES (?, ?)
            ON CONFLICT(key) DO UPDATE SET value = excluded.value
        ");
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

function whatsapp_link(string $message = '') {
    $number = get_setting('whatsapp_number', '');
    if (!$number) return null;
    $number = preg_replace('/[^0-9]/', '', $number);
    $text = urlencode($message ?: 'Hello, I have a question about FreshProvision store.');
    return "https://wa.me/{$number}?text={$text}";
}
