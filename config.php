<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Europe/Istanbul');

define('DATA_DIR',  __DIR__ . '/data');

define('LANGS',       ['tr', 'en']);
define('LENGTHS',     [5]);
define('MODES',       ['daily', 'unlimited']);
define('MAX_GUESSES', 6);

define('SESSION_LIFETIME', 60 * 60 * 24 * 30);
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/words.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game.php';
require_once __DIR__ . '/includes/scores.php';
require_once __DIR__ . '/includes/rewards.php';
require_once __DIR__ . '/includes/dictionary.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/filters.php';
