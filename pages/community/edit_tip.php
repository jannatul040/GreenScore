<?php
require_once __DIR__ . '/../../includes/init.php';
require_once ROOT_PATH . '/includes/connect_db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $tip_id  = (int) ($_POST['tip_id']  ?? 0);
    $message = trim($_POST['message']   ?? '');
    $user_id = (int) $_SESSION['user_id'];

    if (!empty($message) && $tip_id > 0 && strlen($message) <= 500) {
        $stmt = mysqli_prepare($link,
            "UPDATE community_tips SET message = ? WHERE id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'sii', $message, $tip_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['toast_success'] = 'Your tip has been updated.';
    }
}

mysqli_close($link);
header('Location: ' . BASE_URL . '/pages/community/community.php');
exit();