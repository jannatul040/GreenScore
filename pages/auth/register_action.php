<?php
require_once __DIR__ . '/../../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/auth/register.php');
    exit();
}

require ROOT_PATH . '/includes/connect_db.php';

$errors = [];

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $errors[] = 'Invalid CSRF token.';
}

// ── Collect and sanitise inputs ──────────────────────────────
$fn             = trim($_POST['username']       ?? '');
$e              = trim($_POST['email']          ?? '');
$company_name   = trim($_POST['company_name']   ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$phone_number   = trim(preg_replace('/[^\d+\-\s()]/', '', $_POST['phone_number'] ?? ''));
$pass1          = $_POST['pass1'] ?? '';
$pass2          = $_POST['pass2'] ?? '';

// ── Length limits — match DB column sizes ────────────────────
// new_users: username varchar(50), email varchar(100),
//            company_name varchar(100), contact_person varchar(100),
//            phone_number varchar(20)
if (empty($fn)) {
    $errors[] = 'Enter your name.';
} elseif (strlen($fn) > 50) {
    $errors[] = 'Name cannot exceed 50 characters.';
}

if (empty($e)) {
    $errors[] = 'Enter your email address.';
} elseif (strlen($e) > 100) {
    $errors[] = 'Email cannot exceed 100 characters.';
} elseif (!filter_var($e, FILTER_VALIDATE_EMAIL) || !preg_match('/\.[a-zA-Z]{2,}$/', $e)) {
    $errors[] = 'Enter a valid email address (e.g. name@example.com).';
}

if (empty($company_name)) {
    $errors[] = 'Enter your company name.';
} elseif (strlen($company_name) > 100) {
    $errors[] = 'Company name cannot exceed 100 characters.';
}

if (empty($contact_person)) {
    $errors[] = "Enter the contact person's name.";
} elseif (strlen($contact_person) > 100) {
    $errors[] = "Contact person's name cannot exceed 100 characters.";
}

// ── Phone validation ─────────────────────────────────────────
// Stripped to digits only for length check, allow +, -, spaces, ()
$digits_only = preg_replace('/\D/', '', $phone_number);
if (empty($phone_number)) {
    $errors[] = 'Enter a phone number.';
} elseif (strlen($digits_only) < 7) {
    $errors[] = 'Phone number must have at least 7 digits.';
} elseif (strlen($digits_only) > 15) {
    $errors[] = 'Phone number cannot exceed 15 digits (international standard).';
}

// ── Password complexity ──────────────────────────────────────
$common_passwords = [
    'password', 'password1', '12345678', '123456789', 'qwerty123',
    'iloveyou', 'sunshine', 'princess', 'letmein1', 'welcome1',
    'monkey123', 'dragon12', 'football', 'baseball', 'abc12345',
];

if (empty($pass1)) {
    $errors[] = 'Enter your password.';
} elseif (strlen($pass1) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
} elseif (strlen($pass1) > 72) {
    // bcrypt silently truncates at 72 bytes — reject anything longer
    $errors[] = 'Password cannot exceed 72 characters.';
} elseif (!preg_match('/[A-Z]/', $pass1)) {
    $errors[] = 'Password must contain at least one uppercase letter.';
} elseif (!preg_match('/[a-z]/', $pass1)) {
    $errors[] = 'Password must contain at least one lowercase letter.';
} elseif (!preg_match('/[0-9]/', $pass1)) {
    $errors[] = 'Password must contain at least one number.';
} elseif (in_array(strtolower($pass1), $common_passwords)) {
    $errors[] = 'That password is too common. Please choose a stronger one.';
} elseif ($pass1 !== $pass2) {
    $errors[] = 'Passwords do not match.';
} else {
    $p = password_hash(trim($pass1), PASSWORD_DEFAULT);
}

// ── Duplicate email check ────────────────────────────────────
if (empty($errors)) {
    $stmt = mysqli_prepare($link, "SELECT id FROM new_users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $e);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) !== 0) {
        $errors[] = 'That email address is already registered.';
    }
    mysqli_stmt_close($stmt);
}

// ── Redirect back on errors ──────────────────────────────────
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old']    = [
        'username'       => $fn,
        'email'          => $e,
        'company_name'   => $company_name,
        'contact_person' => $contact_person,
        'phone_number'   => $phone_number,
    ];
    mysqli_close($link);
    header('Location: ' . BASE_URL . '/pages/auth/register.php');
    exit();
}

// ── Insert new user ──────────────────────────────────────────
$stmt = mysqli_prepare($link,
    "INSERT INTO new_users (username, email, password, company_name, contact_person, phone_number)
     VALUES (?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, 'ssssss', $fn, $e, $p, $company_name, $contact_person, $phone_number);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    $user_id = mysqli_insert_id($link);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($link,
        "INSERT INTO green_calculator_results
            (user_id, total_score, green_count, amber_count, red_count,
             award_level, emoji, feedback_message, shortfall, donation_cost)
         VALUES (?, 0, 0, 0, 0, 'Initial Registration 🎟️', '🎟️',
                 'Thank you for joining GreenScore!', 0, 99.00)"
    );
    mysqli_stmt_bind_param($stmt2, 'i', $user_id);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    mysqli_close($link);

    $_SESSION['register_success'] = 'Account created successfully. You can now log in.';
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
    exit();
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    $_SESSION['register_errors'] = ['Registration failed. Please try again.'];
    header('Location: ' . BASE_URL . '/pages/auth/register.php');
    exit();
}