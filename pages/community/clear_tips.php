<?php
require_once __DIR__ . '/../../includes/init.php';
require_once ROOT_PATH . '/includes/connect_db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token.');
}

$user_id = (int) $_SESSION['user_id'];
$stmt    = mysqli_prepare($link, "DELETE FROM community_tips WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
$_SESSION['toast_success'] = 'All your tips have been cleared.';

mysqli_close($link);
header('Location: ' . BASE_URL . '/pages/community/community.php');
exit();