<?php
// ── Session cookie configuration ────────────────────────────
// Must be set BEFORE session_start()
session_set_cookie_params([
    'lifetime' => 0,           // Cookie expires when browser closes
    'path'     => '/',
    'secure'   => false,       // Set to true when served over HTTPS
    'httponly' => true,        // JavaScript cannot access the session cookie
    'samesite' => 'Strict',    // Cookie not sent on cross-site requests (CSRF layer)
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Session idle timeout — 30 minutes ───────────────────────
// If the user has been inactive for 30 min, destroy their session
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['_last_activity'])) {
    if (time() - $_SESSION['_last_activity'] > $timeout) {
        $_SESSION = [];
        session_destroy();
        session_start();
    }
}
$_SESSION['_last_activity'] = time();

// ── Session fixation protection ──────────────────────────────
// Regenerate session ID every 15 minutes to make fixation attacks impractical.
// login_action.php regenerates immediately on login — this handles long sessions.
if (!isset($_SESSION['_created'])) {
    $_SESSION['_created'] = time();
} elseif (time() - $_SESSION['_created'] > 900) {
    session_regenerate_id(true);
    $_SESSION['_created'] = time();
}

// ── Security headers ─────────────────────────────────────────
// Prevent clickjacking — page cannot be embedded in an iframe
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing — browser must respect declared Content-Type
header('X-Content-Type-Options: nosniff');

// Control how much referrer info is sent to other sites
header('Referrer-Policy: strict-origin-when-cross-origin');

// Basic XSS protection for older browsers (modern browsers use CSP)
header('X-XSS-Protection: 1; mode=block');

// Permissions policy — disable features this app doesn't use
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// ── ROOT_PATH ────────────────────────────────────────────────
define('ROOT_PATH', dirname(__DIR__));

// ── BASE_URL auto-detection ──────────────────────────────────
// Works on any machine — localhost subfolder, server root, live server
if (!defined('BASE_URL')) {
    $rootDir = str_replace('\\', '/', ROOT_PATH);
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $base    = rtrim(str_replace($docRoot, '', $rootDir), '/');
    define('BASE_URL', $base);
}

// ── CSRF token ───────────────────────────────────────────────
// Generated once per session, available to every page automatically
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}