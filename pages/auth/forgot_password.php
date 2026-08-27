<?php
require_once __DIR__ . '/../../includes/init.php';
require_once ROOT_PATH . '/includes/connect_db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$errors  = [];
$success = false;
$b       = BASE_URL;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email']       ?? '');
    $pass1 = $_POST['new_password']     ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (empty($email))      $errors[] = 'Email is required.';
    if (strlen($pass1) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($pass1 !== $pass2)  $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = mysqli_prepare($link, "SELECT id FROM new_users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $uid);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($uid) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $u    = mysqli_prepare($link, "UPDATE new_users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($u, 'si', $hash, $uid);
            mysqli_stmt_execute($u);
            mysqli_stmt_close($u);
            $success = true;
        } else {
            $errors[] = 'No account found with that email address.';
        }
    }
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include ROOT_PATH . '/includes/head.php'; ?>
    <title>Reset Password | GreenScore</title>
    <meta name="description" content="Reset your GreenScore account password.">
    <style>
        .auth-wrapper { max-width: 500px; margin: 0 auto; }
        .form-label   { color: #222; font-weight: 500; }
        footer        { position: relative; z-index: 1; background-color: #fff;
                        padding: 2rem 0; width: 100%; }
    </style>
</head>
<body class="bg-page overlay-50"
      style="background-image: url('<?= $b ?>/assets/images/forest-hero.jpg');">
<?php include ROOT_PATH . '/includes/nav.php'; ?>

<div class="container content-wrapper">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 class="text-success text-center mb-4">🔄 Reset Your Password</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ Password updated successfully.
                    <a href="<?= $b ?>/pages/auth/login.php" class="alert-link">Log in now</a>.
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control"
                               required minlength="8" autocomplete="new-password">
                        <div class="form-text text-muted">Minimum 8 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control"
                               required minlength="8" autocomplete="new-password">
                    </div>
                    <button class="btn btn-success w-100 py-2">Reset Password</button>
                    <div class="text-center mt-3">
                        <a href="<?= $b ?>/pages/auth/login.php" class="text-muted">
                            ← Back to Login
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>