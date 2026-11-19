<?php
function following_of(string $username): array
{
    $user = find_user($username);
    return $user['following'] ?? [];
}

function followers_of(string $username): array
{
    $list = [];
    foreach (read_json('users.json') as $u) {
        if (in_array($username, $u['following'] ?? [], true)) {
            $list[] = $u['username'];
        }
    }
    return $list;
}

function is_following(string $me, string $target): bool
{
    return in_array($target, following_of($me), true);
}

function set_follow(string $me, string $target, bool $follow): bool
{
    if ($me === $target || find_user($target) === null) {
        return false;
    }
    $wasFollowing = is_following($me, $target);
    $users = read_json('users.json');
    foreach ($users as &$u) {
        if ($u['username'] === $me) {
            $list = array_values(array_diff($u['following'] ?? [], [$target]));
            if ($follow) {
                $list[] = $target;
            }
            $u['following'] = $list;
        }
    }
    unset($u);
    write_json('users.json', $users);
    if ($follow && !$wasFollowing) {
        notify($target, 'follow', $me);
    }
    return true;
}

function remove_from_all_follows(string $username): void
{
    $users = read_json('users.json');
    foreach ($users as &$u) {
        if (isset($u['following'])) {
            $u['following'] = array_values(array_diff($u['following'], [$username]));
        }
    }
    unset($u);
    write_json('users.json', $users);
}

function users_by_names(array $names): array
{
    $all = [];
    foreach (read_json('users.json') as $u) {
        $all[$u['username']] = $u;
    }
    $list = [];
    foreach ($names as $n) {
        if (isset($all[$n])) {
            $list[] = $all[$n];
        }
    }
    return $list;
}

function search_users(string $query): array
{
    $q = ltrim(trim($query), '@');
    if ($q === '') {
        return [];
    }
    $exact = [];
    $starts = [];
    $contains = [];
    foreach (read_json('users.json') as $u) {
        $name = $u['username'];
        if (strcasecmp($name, $q) === 0) {
            $exact[] = $u;
        } elseif (stripos($name, $q) === 0) {
            $starts[] = $u;
        } elseif (stripos($name, $q) !== false) {
            $contains[] = $u;
        }
    }
    return array_slice(array_merge($exact, $starts, $contains), 0, 50);
}

function follow_button(string $me, string $target): string
{
    if ($me === $target) {
        return '';
    }
    $following = is_following($me, $target);
    return '<form method="post" action="follow.php" class="follow-form">' . csrf_field()
        . '<input type="hidden" name="target" value="' . e($target) . '">'
        . '<input type="hidden" name="action" value="' . ($following ? 'unfollow' : 'follow') . '">'
        . '<button class="btn small ' . ($following ? 'ghost' : 'primary') . '" data-i18n="' . ($following ? 'unfollow' : 'follow') . '">'
        . ($following ? 'Takipten çık' : 'Takip et') . '</button></form>';
}

function follow_preview(string $owner, string $type, array $names): string
{
    $count = count($names);
    $href = 'follows.php?u=' . rawurlencode($owner) . '&type=' . $type;
    $html = '<div class="follow-stat"><a href="' . e($href) . '"><b>' . $count . '</b> <span data-i18n="' . $type . '">' . $type . '</span></a>';
    if ($count > 0) {
        $html .= '<div class="follow-pop" role="tooltip">';
        foreach (users_by_names(array_slice($names, 0, 5)) as $u) {
            $html .= '<a class="pop-user" href="profile.php?u=' . rawurlencode($u['username']) . '">'
                . avatar_tag(avatar_of($u), 26) . '<span>@' . e($u['username']) . '</span></a>';
        }
        if ($count > 5) {
            $html .= '<a class="pop-more" href="' . e($href) . '">… <span data-i18n="see_all">tümünü gör</span> (' . $count . ')</a>';
        }
        $html .= '</div>';
    }
    return $html . '</div>';
}
