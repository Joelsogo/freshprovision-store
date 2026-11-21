<?php
$page_title = 'Login';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('account.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        flash('Welcome back, ' . $user['name'] . '!');
        $redirect = $_GET['redirect'] ?? 'index.php';
        redirect($redirect);
    } else {
        $error = 'Invalid email or password';
    }
}

require_once 'includes/header.php';
?>

<div class="auth-card">
    <h1>Login</h1>
    <p class="subtitle">Access your wholesale or retail account</p>

    <?php if ($error): ?>
        <div class="flash flash-error" style="margin-bottom:16px"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required
                   value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <p class="text-center mt-3 text-muted" style="font-size:14px">
        Don't have an account? <a href="register.php" style="color:var(--primary);font-weight:600">Register</a>
    </p>

    <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted)">
        <strong>Demo accounts:</strong><br>
        Admin: admin@freshprovision.ng / admin123<br>
        Customer: customer@example.com / customer123
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
