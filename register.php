<?php
$page_title = 'Register';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $customer_type = $_POST['customer_type'] ?? 'retail';

    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check existing
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare("
                INSERT INTO users (name, email, phone, password, role, customer_type)
                VALUES (?, ?, ?, ?, 'customer', ?)
            ");
            $stmt->execute([$name, $email, $phone, $hash, $customer_type]);
            $_SESSION['user_id'] = db()->lastInsertId();
            flash('Account created successfully! Welcome aboard.');
            redirect('index.php');
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-card">
    <h1>Create Account</h1>
    <p class="subtitle">Join as Retail or Wholesale customer</p>

    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:16px"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="080xxxxxxxx">
        </div>
        <div class="form-group">
            <label>Account Type</label>
            <select name="customer_type" class="form-control">
                <option value="retail" <?= ($_POST['customer_type'] ?? '') === 'retail' ? 'selected' : '' ?>>🛒 Retail — Everyday prices</option>
                <option value="wholesale" <?= ($_POST['customer_type'] ?? '') === 'wholesale' ? 'selected' : '' ?>>📦 Wholesale — Bulk discounts</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <p class="text-center mt-3 text-muted" style="font-size:14px">
        Already have an account? <a href="login.php" style="color:var(--primary);font-weight:600">Login</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
