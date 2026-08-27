<?php
require_once __DIR__ . '/../../includes/init.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit();
}

// Redirect GET requests — this page only handles POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/user/view_cards.php');
    exit();
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed.');
}

require_once ROOT_PATH . '/includes/connect_db.php';

$action = $_POST['action'] ?? null;
$userId = (int) $_SESSION['user_id'];

if ($action === 'add') {
    $cardNumber = trim($_POST['card_number'] ?? '');
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    $cardHolder = trim($_POST['card_name']   ?? '');
    $cvv        = trim($_POST['cvv']         ?? '');

    $date = DateTime::createFromFormat('Y-m-d', $expiryDate);
    if (!$date) die('Invalid date format.');
    $expiryDateFormatted = $date->format('Y-m-d');

    $stmt = mysqli_prepare($link,
        "INSERT INTO credit_cards (user_id, card_number, expiry_date, cardholder_name, cvv)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'issss',
        $userId, $cardNumber, $expiryDateFormatted, $cardHolder, $cvv
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($link);

    $_SESSION['toast_success'] = 'Card added successfully.';
    header('Location: ' . BASE_URL . '/pages/user/view_cards.php');
    exit();
}

if ($action === 'update') {
    $cardId     = (int) ($_POST['card_id']   ?? 0);
    $cardNumber = trim($_POST['card_number'] ?? '');
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    $cardHolder = trim($_POST['card_name']   ?? '');
    $cvv        = trim($_POST['cvv']         ?? '');

    $date = DateTime::createFromFormat('Y-m-d', $expiryDate);
    if (!$date) die('Invalid date format.');
    $expiryDateFormatted = $date->format('Y-m-d');

    $stmt = mysqli_prepare($link,
        "UPDATE credit_cards
         SET card_number = ?, expiry_date = ?, cardholder_name = ?, cvv = ?
         WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ssssii',
        $cardNumber, $expiryDateFormatted, $cardHolder, $cvv, $cardId, $userId
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($link);

    $_SESSION['toast_success'] = 'Card updated successfully.';
    header('Location: ' . BASE_URL . '/pages/user/view_cards.php');
    exit();
}

if ($action === 'delete') {
    $cardId = (int) ($_POST['card_id'] ?? 0);

    $stmt = mysqli_prepare($link,
        "DELETE FROM credit_cards WHERE id = ? AND user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ii', $cardId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($link);

    $_SESSION['toast_success'] = 'Card deleted.';
    header('Location: ' . BASE_URL . '/pages/user/view_cards.php');
    exit();
}

mysqli_close($link);
header('Location: ' . BASE_URL . '/pages/user/view_cards.php');
exit();