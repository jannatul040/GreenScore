<?php
require_once __DIR__ . '/../../includes/init.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id']) || !isset($_GET['shortfall'])) {
    header('Location: ' . BASE_URL . '/pages/calculator/green_calculator.php');
    exit();
}

$b         = BASE_URL;
$shortfall = max(0, min(100, (int) $_GET['shortfall']));
$cost      = number_format($shortfall * 10, 2); // calculated server-side, not from GET
$username  = $_SESSION['username'];
$user_id   = (int) $_SESSION['user_id'];

$donated       = false;
$donate_error  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donate'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    require ROOT_PATH . '/includes/connect_db.php';

    $award         = 'Certificate of Gold 🥇';
    $emoji         = '🥇';
    $message       = "Thank you for your contribution! You've unlocked full recognition!";
    $new_shortfall = 0;

    $stmt = mysqli_prepare($link,
        "SELECT id FROM green_calculator_results
         WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $last_id = (int) $row['id'];
        $stmt = mysqli_prepare($link,
            "UPDATE green_calculator_results
             SET award_level = ?, emoji = ?, feedback_message = ?,
                 shortfall = ?, donation_cost = ?, submitted_at = NOW()
             WHERE id = ?"
        );
        $cost_float = (float) str_replace(',', '', $cost);
        mysqli_stmt_bind_param($stmt, 'sssidi',
            $award, $emoji, $message, $new_shortfall, $cost_float, $last_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $donated = true;
    } else {
        $donate_error = true;
    }

    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include ROOT_PATH . '/includes/head.php'; ?>
    <title>Buy Sustainability Points | GreenScore</title>
    <style>
        .buy-card { max-width: 620px; width: 100%; }
        .content-wrapper { display: flex; justify-content: center; }
    </style>
</head>
<body class="bg-page overlay-50"
      style="background-image: url('<?= $b ?>/assets/images/forest-hero.jpg');">
<?php include ROOT_PATH . '/includes/nav.php'; ?>

<div class="page-wrapper">
    <div class="container content-wrapper">
        <div class="card-bg p-4 text-center buy-card" style="color: #333;">
            <h1 class="text-success mb-3">💸 Support Your Score</h1>
            <p class="lead">Hello <strong><?= htmlspecialchars($username) ?></strong>,</p>

            <?php if ($donated): ?>
                <div class="alert alert-success mt-3">
                    🎉 Thank you! Your certificate has been updated to
                    <strong>Certificate of Gold 🥇</strong>.
                </div>
                <a href="<?= $b ?>/pages/calculator/certificate_preview.php?id=<?= $last_id ?>"
                   class="btn btn-success mt-3 me-2">
                    📄 View Your Certificate
                </a>
                <a href="<?= $b ?>/pages/calculator/certificate_history.php"
                   class="btn btn-outline-secondary mt-3">
                    📜 Certificate History
                </a>

            <?php elseif ($donate_error): ?>
                <div class="alert alert-danger mt-3">
                    ⚠️ No previous certificate found to update. Please complete the
                    <a href="<?= $b ?>/pages/calculator/green_calculator.php">Green Calculator</a> first.
                </div>

            <?php else: ?>
                <p class="mt-3">
                    You're currently <strong><?= $shortfall ?> points</strong> short of a perfect score.
                </p>
                <p>
                    Contributing <strong>£<?= $cost ?></strong> will close the gap
                    and upgrade your certificate to <strong>Gold 🥇</strong>.
                </p>

                <div class="alert alert-info mt-3 text-start">
                    <strong>What you get:</strong>
                    <ul class="mb-0 mt-1">
                        <li>Your latest certificate upgraded to <strong>Gold</strong></li>
                        <li>£<?= $cost ?> contributed to global sustainability initiatives</li>
                        <li>Your score gap closed to zero</li>
                    </ul>
                </div>

                <form method="POST" class="mt-4">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" name="donate" class="btn btn-warning btn-lg px-5">
                        ✅ Confirm £<?= $cost ?> Contribution
                    </button>
                    <div class="mt-3">
                        <a href="<?= $b ?>/pages/calculator/green_calculator.php"
                           class="btn btn-outline-secondary">⬅ Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include ROOT_PATH . '/includes/footer.php'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>