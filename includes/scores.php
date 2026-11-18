<?php
function add_score(string $username, array $game): void
{
    $scores = read_json('scores.json');
    $scores[] = [
        'id'         => uniqid('', true),
        'username'   => $username,
        'lang'       => $game['lang'],
        'length'     => $game['length'],
        'mode'       => $game['mode'],
        'hard'       => $game['hard'],
        'won'        => $game['status'] === 'won',
        'attempts'   => count($game['rows']),
        'words'      => array_column($game['rows'], 'word'),
        'answer'     => $game['answer'],
        'date'       => $game['date'],
        'created_at' => date('c'),
    ];
    write_json('scores.json', $scores);
}

function find_daily_score(string $username, string $lang, int $length, string $date): ?array
{
    foreach (read_json('scores.json') as $s) {
        if ($s['username'] === $username && $s['mode'] === 'daily'
            && $s['lang'] === $lang && $s['length'] === $length && $s['date'] === $date) {
            return $s;
        }
    }
    return null;
}

function filter_scores(array $f): array
{
    $since = null;
    if (($f['period'] ?? '') === 'today') {
        $since = date('Y-m-d');
    } elseif (($f['period'] ?? '') === 'week') {
        $since = date('Y-m-d', strtotime('-6 days'));
    }

    return array_values(array_filter(read_json('scores.json'), function ($s) use ($f, $since) {
        return in_array($s['length'], LENGTHS, true)
            && (empty($f['username']) || $s['username'] === $f['username'])
            && (empty($f['lang'])     || $s['lang'] === $f['lang'])
            && (empty($f['length'])   || $s['length'] === (int)$f['length'])
            && (empty($f['mode'])     || $s['mode'] === $f['mode'])
            && ($since === null       || $s['date'] >= $since);
    }));
}

function user_stats(array $scores): array
{
    $played = count($scores);
    $wins   = 0;
    $dist   = array_fill(1, MAX_GUESSES, 0);
    $streak = 0;
    $best   = 0;

    foreach ($scores as $s) {
        if ($s['won']) {
            $wins++;
            $dist[$s['attempts']]++;
            $streak++;
            $best = max($best, $streak);
        } else {
            $streak = 0;
        }
    }

    return [
        'played'   => $played,
        'wins'     => $wins,
        'win_rate' => $played > 0 ? round($wins / $played * 100) : 0,
        'streak'   => $streak,
        'best'     => $best,
        'dist'     => $dist,
    ];
}
