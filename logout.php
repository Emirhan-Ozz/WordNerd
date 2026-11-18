<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_csrf()) {
    logout_user();
}
header('Location: login.php');
exit;
