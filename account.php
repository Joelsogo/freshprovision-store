<?php
$page_title = 'My Account';
require_once 'includes/functions.php';
require_login();

$user = current_user();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $type = $_POST['customer_type'] ?? $user['customer_type'];
        if ($name) {
            $stmt = db()->prepare("UPDATE users SET name = ?, phone = ?, customer_type = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $type, $user['id']]);
            flash('Profile updated successfully');
            redirect('account.php');
        }
    } elseif ($action === 'logout') {
        session_destroy();
        redirect('index.php');
    }
}

require_once 'includes/header.php';
$user = current_user(); // refresh
?>

<div class="section-title"><span>My Account</span></div>

<div class="auth-card" style="max-width:100%">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;font-size:24px">
            <i class="fas fa-user"></i>
        </div>
        <div>
            <strong style="font-size:18px"><?= e($user['name']) ?></strong>
            <div class="text-muted" style="font-size:13px"><?= e($user['email']) ?></div>
            <span class="status status-<?= $user['customer_type'] === 'wholesale' ? 'delivered' : 'pending' ?>" style="margin-top:4px">
                <?= ucfirst($user['customer_type']) ?> Customer
            </span>
        </div>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Customer Type</label>
            <select name="customer_type" class="form-control">
                <option value="retail" <?= $user['customer_type'] === 'retail' ? 'selected' : '' ?>>🛒 Retail</option>
                <option value="wholesale" <?= $user['customer_type'] === 'wholesale' ? 'selected' : '' ?>>📦 Wholesale (bulk discounts)</option>
            </select>
            <small class="text-muted">Wholesale prices apply when you order the minimum quantity for each product.</small>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
    </form>

    <hr style="margin:24px 0;border:none;border-top:1px solid var(--border)">

    <a href="orders.php" class="btn btn-outline btn-block mb-2">
        <i class="fas fa-receipt"></i> My Orders
    </a>

    <?php if (is_admin()): ?>
        <a href="admin/" class="btn btn-accent btn-block mb-2">
            <i class="fas fa-cog"></i> Admin Panel
        </a>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn btn-danger btn-block">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
