<?php
function change_password(string $username, string $current, string $new, string $repeat): ?string
{
    $user = find_user($username);
    if ($user === null || !password_verify($current, $user['password'])) {
        return 'err_current_password';
    }
    if (mb_strlen($new) < 6) {
        return 'err_password_short';
    }
    if ($new !== $repeat) {
        return 'err_password_match';
    }

    $users = read_json('users.json');
    foreach ($users as &$u) {
        if ($u['username'] === $username) {
            $u['password'] = password_hash($new, PASSWORD_DEFAULT);
        }
    }
    unset($u);
    write_json('users.json', $users);
    return null;
}

function delete_account(string $username): void
{
    write_json('users.json', array_values(array_filter(read_json('users.json'), fn($u) => $u['username'] !== $username)));
    write_json('scores.json', array_values(array_filter(read_json('scores.json'), fn($s) => $s['username'] !== $username)));
    remove_from_all_follows($username);
    remove_notifications_of($username);
    remove_challenges_of($username);
}
