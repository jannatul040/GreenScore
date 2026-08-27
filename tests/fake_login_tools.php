<?php
// Fake login tools for PHPUnit — mirrors the real validate() signature
// Uses password_hash so tests exercise the same bcrypt path as production

function validate($link, $email = '', $pwd = '') {
    $errors = [];

    if (empty($email)) $errors[] = 'Enter your email address.';
    if (empty($pwd))   $errors[] = 'Enter your password.';

    if (!empty($errors)) {
        return [false, $errors];
    }

    $test_users = [
        'admin@example.com' => [
            'password' => password_hash('adminpass123', PASSWORD_DEFAULT),
            'id'       => 1,
            'username' => 'Admin User',
            'email'    => 'admin@example.com',
            'role'     => 'admin',
        ],
        'user@example.com' => [
            'password' => password_hash('userpass123', PASSWORD_DEFAULT),
            'id'       => 2,
            'username' => 'Test User',
            'email'    => 'user@example.com',
            'role'     => 'user',
        ],
    ];

    if (isset($test_users[$email])) {
        if (password_verify($pwd, $test_users[$email]['password'])) {
            return [true, $test_users[$email]];
        }
        return [false, ['Incorrect password.']];
    }

    return [false, ['Email address and password not found.']];
}

function load($page = 'index.php') {
    // Suppress redirects during testing
}