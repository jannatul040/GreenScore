<?php
require_once __DIR__ . '/../../includes/init.php';
require_once ROOT_PATH . '/includes/connect_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }

    $tip_id  = (int) ($_POST['id'] ?? 0);
    $user_id = (int) $_SESSION['user_id'];

    if ($tip_id > 0) {
        $stmt = mysqli_prepare($link,
            "DELETE FROM community_tips WHERE id = ? AND user_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $tip_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
header('Location: ' . BASE_URL . '/pages/community/community.php');
exit();