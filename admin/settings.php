<?php
require_once '../includes/functions.php';
require_admin();
$page_title = 'Store Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['whatsapp_number', 'store_phone', 'store_address', 'paystack_public_key', 'paystack_secret_key', 'payment_enabled'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        if ($f === 'payment_enabled') {
            $val = isset($_POST['payment_enabled']) ? '1' : '0';
        }
        set_setting($f, $val);
    }
    flash('Settings saved successfully');
    redirect('settings.php');
}

$whatsapp = get_setting('whatsapp_number', '');
$phone = get_setting('store_phone', '');
$address = get_setting('store_address', '');
$pk = get_setting('paystack_public_key', '');
$sk = get_setting('paystack_secret_key', '');
$pay_enabled = get_setting('payment_enabled', '0') === '1';

require_once '../includes/header.php';
?>

<div class="section-title">
    <span>Store Settings</span>
    <a href="index.php" style="font-size:13px;color:var(--primary)">← Dashboard</a>
</div>

<form method="POST" class="auth-card" style="max-width:100%">
    <h3 style="margin-bottom:16px">📱 Contact & WhatsApp</h3>
    
    <div class="form-group">
        <label>WhatsApp Number (with country code)</label>
        <input type="text" name="whatsapp_number" class="form-control" 
               value="<?= e($whatsapp) ?>" placeholder="2348012345678">
        <small class="text-muted">Used for the floating WhatsApp chat button. Example: 2348012345678</small>
    </div>
    
    <div class="form-group">
        <label>Store Phone</label>
        <input type="text" name="store_phone" class="form-control" value="<?= e($phone) ?>">
    </div>
    
    <div class="form-group">
        <label>Store Address</label>
        <textarea name="store_address" class="form-control" rows="2"><?= e($address) ?></textarea>
    </div>

    <hr style="margin:24px 0;border:none;border-top:1px solid var(--border)">

    <h3 style="margin-bottom:16px">💳 Paystack Payment</h3>
    <p class="text-muted" style="font-size:13px;margin-bottom:14px">
        Get your keys from <a href="https://dashboard.paystack.com/#/settings/developer" target="_blank" style="color:var(--primary)">Paystack Dashboard</a>
    </p>
    
    <div class="form-group">
        <label>
            <input type="checkbox" name="payment_enabled" value="1" <?= $pay_enabled ? 'checked' : '' ?>>
            Enable online card/transfer payments
        </label>
    </div>
    
    <div class="form-group">
        <label>Paystack Public Key</label>
        <input type="text" name="paystack_public_key" class="form-control" 
               value="<?= e($pk) ?>" placeholder="pk_test_... or pk_live_...">
    </div>
    
    <div class="form-group">
        <label>Paystack Secret Key</label>
        <input type="password" name="paystack_secret_key" class="form-control" 
               value="<?= e($sk) ?>" placeholder="sk_test_... or sk_live_...">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Save Settings</button>
</form>

<?php require_once '../includes/footer.php'; ?>
