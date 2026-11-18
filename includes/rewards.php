<?php
const AVATAR_CATALOG = [
    'skin'      => ['s1' => null, 's2' => null, 's3' => null, 's4' => null, 's5' => null, 's6' => null],
    'hair'      => ['short' => null, 'long' => null, 'curly' => null, 'spiky' => null, 'none' => null],
    'hairColor' => ['h1' => null, 'h2' => null, 'h3' => null, 'h4' => null, 'h5' => null, 'h6' => null],
    'face'      => ['smile' => null, 'surprised' => null, 'calm' => null,
                    'wink' => 'streak3', 'starry' => 'first_try', 'fire' => 'hard10'],
    'bg'        => ['b1' => null, 'b2' => null, 'b3' => null, 'b4' => null,
                    'b5' => null, 'b6' => null, 'b7' => null, 'b8' => null],
    'outfit'    => ['tshirt' => null, 'hoodie' => 'streak7', 'leather' => 'hard10',
                    'suit' => 'wins25', 'cape' => 'streak30'],
    'hat'       => ['none' => null, 'beanie' => 'first_win', 'headphones' => 'streak14',
                    'gradcap' => 'wins25', 'crown' => 'streak60'],
    'glasses'   => ['none' => null, 'glasses' => 'bilingual'],
    'title'     => ['none' => null, 'warming' => 'streak3', 'weekly' => 'streak7', 'oneshot' => 'first_try',
                    'bilingual' => 'bilingual', 'star' => 'streak30', 'legend' => 'streak60'],
];

const AVATAR_DEFAULT = [
    'skin' => 's2', 'hair' => 'short', 'hairColor' => 'h1', 'face' => 'smile',
    'bg' => 'b1', 'outfit' => 'tshirt', 'hat' => 'none', 'glasses' => 'none', 'title' => 'none',
];

const ACHIEVEMENTS = [
    'first_win' => 1,
    'streak3'   => 3,
    'streak7'   => 7,
    'first_try' => 1,
    'bilingual' => 2,
    'streak14'  => 14,
    'hard10'    => 10,
    'wins25'    => 25,
    'streak30'  => 30,
    'streak60'  => 60,
];

function daily_scores(string $username): array
{
    return filter_scores(['username' => $username, 'mode' => 'daily']);
}

function game_points(array $s): int
{
    if (!$s['won']) {
        return 0;
    }
    return (MAX_GUESSES + 1 - $s['attempts']) + ($s['hard'] ? 1 : 0);
}

function won_dates(array $dailyScores): array
{
    $dates = [];
    foreach ($dailyScores as $s) {
        if ($s['won']) {
            $dates[$s['date']] = true;
        }
    }
    return $dates;
}

function streaks(array $dailyScores): array
{
    $dates = won_dates($dailyScores);

    $current = 0;
    $day = date('Y-m-d');
    if (!isset($dates[$day])) {
        $day = date('Y-m-d', strtotime('-1 day'));
    }
    while (isset($dates[$day])) {
        $current++;
        $day = date('Y-m-d', strtotime($day . ' -1 day'));
    }

    $best = 0;
    $run = 0;
    $prev = null;
    $sorted = array_keys($dates);
    sort($sorted);
    foreach ($sorted as $d) {
        $run = ($prev !== null && date('Y-m-d', strtotime($prev . ' +1 day')) === $d) ? $run + 1 : 1;
        $best = max($best, $run);
        $prev = $d;
    }

    return ['current' => $current, 'best' => $best];
}

function achievements(array $dailyScores): array
{
    $wins = array_filter($dailyScores, fn($s) => $s['won']);
    $streak = streaks($dailyScores);
    $langs = array_unique(array_column($wins, 'lang'));
    $firstTry = count(array_filter($wins, fn($s) => $s['attempts'] === 1));
    $hardWins = count(array_filter($wins, fn($s) => $s['hard']));

    $progress = [
        'first_win' => count($wins),
        'streak3'   => $streak['best'],
        'streak7'   => $streak['best'],
        'first_try' => $firstTry,
        'bilingual' => count($langs),
        'streak14'  => $streak['best'],
        'hard10'    => $hardWins,
        'wins25'    => count($wins),
        'streak30'  => $streak['best'],
        'streak60'  => $streak['best'],
    ];

    $list = [];
    foreach (ACHIEVEMENTS as $id => $target) {
        $list[$id] = [
            'id'       => $id,
            'target'   => $target,
            'progress' => min($progress[$id], $target),
            'unlocked' => $progress[$id] >= $target,
        ];
    }
    return $list;
}

function avatar_of(array $user): array
{
    $avatar = array_merge(AVATAR_DEFAULT, $user['avatar'] ?? []);
    foreach ($avatar as $slot => $value) {
        if (!isset(AVATAR_CATALOG[$slot]) || !array_key_exists($value, AVATAR_CATALOG[$slot])) {
            $avatar[$slot] = AVATAR_DEFAULT[$slot];
        }
    }
    return $avatar;
}

function validate_avatar(array $input, array $achievements): array
{
    $clean = [];
    foreach (AVATAR_DEFAULT as $slot => $default) {
        $value = (string)($input[$slot] ?? $default);
        if (!array_key_exists($value, AVATAR_CATALOG[$slot])) {
            $value = $default;
        }
        $need = AVATAR_CATALOG[$slot][$value];
        if ($need !== null && empty($achievements[$need]['unlocked'])) {
            $value = $default;
        }
        $clean[$slot] = $value;
    }
    return $clean;
}

function save_avatar(string $username, array $avatar): void
{
    $users = read_json('users.json');
    foreach ($users as &$u) {
        if ($u['username'] === $username) {
            $u['avatar'] = $avatar;
        }
    }
    unset($u);
    write_json('users.json', $users);
}

function avatar_tag(array $avatar, int $size, string $mood = ''): string
{
    $json = e(json_encode($avatar));
    $moodAttr = $mood !== '' ? ' data-mood="' . e($mood) . '"' : '';
    return '<span class="avatar" style="width:' . $size . 'px;height:' . $size . 'px" data-avatar="' . $json . '"' . $moodAttr . '></span>';
}

function daily_leaderboard(array $scores): array
{
    $users = [];
    foreach (read_json('users.json') as $u) {
        $users[$u['username']] = $u;
    }

    $history = [];
    foreach (filter_scores(['mode' => 'daily']) as $s) {
        $history[$s['username']][] = $s;
    }

    $table = [];
    foreach ($scores as $s) {
        $u = $s['username'];
        $table[$u] ??= ['username' => $u, 'points' => 0, 'played' => 0, 'wins' => 0, 'attempt_sum' => 0];
        $table[$u]['played']++;
        $table[$u]['points'] += game_points($s);
        if ($s['won']) {
            $table[$u]['wins']++;
            $table[$u]['attempt_sum'] += $s['attempts'];
        }
    }

    foreach ($table as $name => &$row) {
        $row['avg'] = $row['wins'] > 0 ? round($row['attempt_sum'] / $row['wins'], 2) : null;
        $row['streak'] = streaks($history[$name] ?? [])['current'];
        $row['avatar'] = avatar_of($users[$name] ?? []);
        $row['title'] = $row['avatar']['title'];
    }
    unset($row);

    usort($table, function ($a, $b) {
        return [$b['points'], $b['wins'], $a['avg'] ?? 99] <=> [$a['points'], $a['wins'], $b['avg'] ?? 99];
    });
    return array_values($table);
}
