<?php
const NOTIFICATION_LIMIT = 50;

function notify(string $to, string $type, string $actor, array $data = []): void
{
    if ($to === $actor || find_user($to) === null) {
        return;
    }
    $all = read_json('notifications.json');
    $list = $all[$to] ?? [];
    array_unshift($list, [
        'id'    => bin2hex(random_bytes(6)),
        'type'  => $type,
        'actor' => $actor,
        'data'  => $data,
        'at'    => date('c'),
        'read'  => false,
    ]);
    $all[$to] = array_slice($list, 0, NOTIFICATION_LIMIT);
    write_json('notifications.json', $all);
}

function notifications_for(string $username): array
{
    return read_json('notifications.json')[$username] ?? [];
}

function unread_count(string $username): int
{
    return count(array_filter(notifications_for($username), fn($n) => !$n['read']));
}

function mark_all_read(string $username): void
{
    $all = read_json('notifications.json');
    if (empty($all[$username])) {
        return;
    }
    foreach ($all[$username] as &$n) {
        $n['read'] = true;
    }
    unset($n);
    write_json('notifications.json', $all);
}

function remove_notifications_of(string $username): void
{
    $all = read_json('notifications.json');
    unset($all[$username]);
    foreach ($all as $to => $list) {
        $all[$to] = array_values(array_filter($list, fn($n) => $n['actor'] !== $username));
    }
    write_json('notifications.json', $all);
}

function daily_points(string $username): int
{
    return array_sum(array_map('game_points', daily_scores($username)));
}

function notify_passed(string $username, int $before, int $after): void
{
    if ($after <= $before) {
        return;
    }
    $friends = array_unique(array_merge(following_of($username), followers_of($username)));
    foreach ($friends as $friend) {
        $points = daily_points($friend);
        if ($points >= $before && $points < $after) {
            notify($friend, 'passed', $username, ['points' => $after]);
        }
    }
}
