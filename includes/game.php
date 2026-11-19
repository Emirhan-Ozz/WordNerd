<?php
function game_key(string $mode, string $lang, int $length): string
{
    return "{$mode}_{$lang}_{$length}";
}

function evaluate_guess(string $guess, string $answer): array
{
    $g = letters($guess);
    $a = letters($answer);
    $n = count($a);
    $marks = array_fill(0, $n, 'absent');
    $left  = [];

    for ($i = 0; $i < $n; $i++) {
        if ($g[$i] === $a[$i]) {
            $marks[$i] = 'correct';
        } else {
            $left[$a[$i]] = ($left[$a[$i]] ?? 0) + 1;
        }
    }
    for ($i = 0; $i < $n; $i++) {
        if ($marks[$i] !== 'correct' && !empty($left[$g[$i]])) {
            $marks[$i] = 'present';
            $left[$g[$i]]--;
        }
    }
    return $marks;
}

function hard_mode_error(string $guess, array $rows, string $lang): ?array
{
    $g = letters($guess);
    foreach ($rows as $row) {
        $prev = letters($row['word']);
        $need = [];
        foreach ($row['marks'] as $i => $mark) {
            if ($mark === 'correct' && $g[$i] !== $prev[$i]) {
                return ['err_hard_green', ['n' => $i + 1, 'l' => upper($prev[$i], $lang)]];
            }
            if ($mark !== 'absent') {
                $need[$prev[$i]] = ($need[$prev[$i]] ?? 0) + 1;
            }
        }
        $have = array_count_values($g);
        foreach ($need as $letter => $count) {
            if (($have[$letter] ?? 0) < $count) {
                return ['err_hard_yellow', ['l' => upper($letter, $lang)]];
            }
        }
    }
    return null;
}

function new_game(string $lang, int $length, string $mode, bool $hard, ?string $answer = null): array
{
    if ($answer === null) {
        $answer = $mode === 'daily' ? daily_word($lang, $length) : random_word($lang, $length);
    }
    return [
        'lang'   => $lang,
        'length' => $length,
        'mode'   => $mode,
        'hard'   => $hard,
        'date'   => date('Y-m-d'),
        'answer' => $answer,
        'rows'   => [],
        'status' => 'playing',
    ];
}

function apply_guess(array &$game, string $word): ?array
{
    if ($game['status'] !== 'playing') {
        return ['err_finished', []];
    }
    $word = lower($word, $game['lang']);

    if (mb_strlen($word, 'UTF-8') !== $game['length']) {
        return ['err_length', ['n' => $game['length']]];
    }
    if (!is_valid_word($word, $game['lang'], $game['length'])) {
        return ['err_not_in_list', []];
    }
    if ($game['hard']) {
        $err = hard_mode_error($word, $game['rows'], $game['lang']);
        if ($err !== null) {
            return $err;
        }
    }

    $marks = evaluate_guess($word, $game['answer']);
    $game['rows'][] = ['word' => $word, 'marks' => $marks];

    if ($word === $game['answer']) {
        $game['status'] = 'won';
    } elseif (count($game['rows']) >= MAX_GUESSES) {
        $game['status'] = 'lost';
    }
    return null;
}

function finish_if_needed(array &$game, string $username): void
{
    if ($game['status'] === 'playing' || !empty($game['saved'])) {
        return;
    }
    if ($game['mode'] === 'challenge') {
        add_challenge_result($game['challenge'], $username, $game);
    } elseif ($game['mode'] === 'daily') {
        $before = daily_points($username);
        add_score($username, $game);
        notify_passed($username, $before, daily_points($username));
    } else {
        add_score($username, $game);
    }
    $game['saved'] = true;
}

function public_state(array $game): array
{
    $state = [
        'lang'   => $game['lang'],
        'length' => $game['length'],
        'mode'   => $game['mode'],
        'hard'   => $game['hard'],
        'date'   => $game['date'],
        'max'    => MAX_GUESSES,
        'rows'   => $game['rows'],
        'status' => $game['status'],
    ];
    if ($game['status'] !== 'playing') {
        $state['answer'] = $game['answer'];
    }
    if ($game['mode'] === 'challenge') {
        $state['challenge'] = $game['challenge'];
        $state['creator'] = $game['creator'];
    }
    if (!empty($game['challenge_id'])) {
        $state['challenge'] = $game['challenge_id'];
    }
    return $state;
}

function start_game(array $user, string $lang, int $length, string $mode, bool $hard, array $saved, bool $forceNew): array
{
    $key   = game_key($mode, $lang, $length);
    $game  = $_SESSION['games'][$key] ?? null;
    $today = date('Y-m-d');

    if ($mode === 'daily') {
        $done = find_daily_score($user['username'], $lang, $length, $today);
        if ($done !== null) {
            $game = new_game($lang, $length, 'daily', $done['hard']);
            foreach ($done['words'] as $w) {
                $game['rows'][] = ['word' => $w, 'marks' => evaluate_guess($w, $game['answer'])];
            }
            $game['status'] = $done['won'] ? 'won' : 'lost';
            $game['saved']  = true;
        } elseif ($game === null || $game['date'] !== $today) {
            $game = new_game($lang, $length, 'daily', $hard);
            foreach (array_slice($saved, 0, MAX_GUESSES) as $w) {
                if (!is_string($w) || apply_guess($game, $w) !== null) {
                    break;
                }
            }
            finish_if_needed($game, $user['username']);
        }
    } else {
        if ($game === null || ($forceNew && $game['status'] !== 'playing')) {
            $game = new_game($lang, $length, 'unlimited', $hard);
        }
    }

    if ($game['status'] === 'playing' && count($game['rows']) === 0) {
        $game['hard'] = $hard;
    }

    $_SESSION['games'][$key] = $game;
    return public_state($game);
}

function start_challenge(array $user, array $challenge, bool $hard): array
{
    $key = 'challenge_' . $challenge['id'];
    $done = $challenge['results'][$user['username']] ?? null;
    $game = $_SESSION['games'][$key] ?? null;

    if ($done !== null) {
        $game = new_game($challenge['lang'], 5, 'challenge', $done['hard'], $challenge['answer']);
        foreach ($done['words'] as $w) {
            $game['rows'][] = ['word' => $w, 'marks' => evaluate_guess($w, $game['answer'])];
        }
        $game['status'] = $done['won'] ? 'won' : 'lost';
        $game['saved'] = true;
    } elseif ($game === null) {
        $game = new_game($challenge['lang'], 5, 'challenge', $hard, $challenge['answer']);
    }
    $game['challenge'] = $challenge['id'];
    $game['creator'] = $challenge['creator'];

    if ($game['status'] === 'playing' && count($game['rows']) === 0) {
        $game['hard'] = $hard;
    }
    $_SESSION['games'][$key] = $game;
    return public_state($game);
}
