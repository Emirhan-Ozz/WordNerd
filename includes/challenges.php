<?php
function get_challenge(string $id): ?array
{
    if (!preg_match('/^[a-f0-9]{12}$/', $id)) {
        return null;
    }
    return read_json('challenges.json')[$id] ?? null;
}

function challenge_result(array $game): array
{
    return [
        'won'      => $game['status'] === 'won',
        'attempts' => count($game['rows']),
        'hard'     => $game['hard'],
        'words'    => array_column($game['rows'], 'word'),
        'at'       => date('c'),
    ];
}

function create_challenge(string $creator, array $game): string
{
    $all = read_json('challenges.json');
    do {
        $id = bin2hex(random_bytes(6));
    } while (isset($all[$id]));

    $all[$id] = [
        'id'         => $id,
        'creator'    => $creator,
        'lang'       => $game['lang'],
        'answer'     => $game['answer'],
        'created_at' => date('c'),
        'results'    => [$creator => challenge_result($game)],
    ];
    write_json('challenges.json', $all);
    return $id;
}

function add_challenge_result(string $id, string $username, array $game): void
{
    $all = read_json('challenges.json');
    if (!isset($all[$id]) || isset($all[$id]['results'][$username])) {
        return;
    }
    $result = challenge_result($game);
    $all[$id]['results'][$username] = $result;
    write_json('challenges.json', $all);

    notify($all[$id]['creator'], 'challenge_done', $username, [
        'id'       => $id,
        'won'      => $result['won'],
        'attempts' => $result['attempts'],
    ]);
}

function remove_challenges_of(string $username): void
{
    $all = read_json('challenges.json');
    foreach ($all as $id => $c) {
        if ($c['creator'] === $username) {
            unset($all[$id]);
            continue;
        }
        unset($all[$id]['results'][$username]);
    }
    write_json('challenges.json', $all);
}
