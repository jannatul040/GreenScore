<?php
require_once __DIR__ . '/../../includes/init.php';
require_once ROOT_PATH . '/includes/connect_db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    include ROOT_PATH . '/403.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/admin/admin_feedback.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token.');
}

$stmt = mysqli_prepare($link,
    "UPDATE feedback
     SET visible_to_public = ?,
         admin_response    = ?,
         admin_username    = ?,
         admin_response_at = NOW()
     WHERE id = ?"
);

foreach ($_POST['admin_response'] as $id => $response) {
    $id         = (int) $id;
    $response   = trim($response);
    $is_public  = isset($_POST['visible_to_public'][$id]) ? 1 : 0;
    $admin_user = $_SESSION['username'];

    mysqli_stmt_bind_param($stmt, 'issi', $is_public, $response, $admin_user, $id);
    mysqli_stmt_execute($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($link);

header('Location: ' . BASE_URL . '/pages/admin/admin_feedback.php?updated=1');
exit();