<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'err_method'], 405);
}

$user = current_user();
if ($user === null) {
    json_response(['ok' => false, 'error' => 'err_auth'], 401);
}
if (!check_csrf()) {
    json_response(['ok' => false, 'error' => 'err_csrf'], 403);
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) {
    json_response(['ok' => false, 'error' => 'err_bad_request'], 400);
}

$lang   = (string)($in['lang'] ?? '');
$length = (int)($in['length'] ?? 0);
$mode   = (string)($in['mode'] ?? '');

if (!is_valid_mode($lang, $length) || !in_array($mode, MODES, true)) {
    json_response(['ok' => false, 'error' => 'err_bad_request'], 400);
}

switch ($in['action'] ?? '') {

    case 'start':
        $saved = (is_array($in['saved'] ?? null) && ($in['savedDate'] ?? '') === date('Y-m-d'))
            ? $in['saved'] : [];
        $state = start_game($user, $lang, $length, $mode, !empty($in['hard']), $saved, !empty($in['new']));
        json_response(['ok' => true, 'state' => $state]);

    case 'guess':
        $key = game_key($mode, $lang, $length);
        if (!isset($_SESSION['games'][$key])) {
            json_response(['ok' => false, 'error' => 'err_no_game'], 409);
        }
        $game = $_SESSION['games'][$key];

        if ($mode === 'daily' && $game['date'] !== date('Y-m-d')) {
            json_response(['ok' => false, 'error' => 'err_new_day'], 409);
        }

        $err = apply_guess($game, (string)($in['word'] ?? ''));
        if ($err !== null) {
            json_response(['ok' => false, 'error' => $err[0], 'params' => $err[1]]);
        }

        finish_if_needed($game, $user['username']);
        $_SESSION['games'][$key] = $game;
        json_response(['ok' => true, 'state' => public_state($game)]);

    default:
        json_response(['ok' => false, 'error' => 'err_bad_request'], 400);
}
