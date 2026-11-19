<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$target = (string)($_POST['target'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf()) {
    set_follow($me['username'], $target, ($_POST['action'] ?? '') === 'follow');
}

$back = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($back === '' || parse_url($back, PHP_URL_HOST) !== parse_url('http://' . $host, PHP_URL_HOST)) {
    $back = 'profile.php?u=' . rawurlencode($target);
}
header('Location: ' . $back);
exit;
