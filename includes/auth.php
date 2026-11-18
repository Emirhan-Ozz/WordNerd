<?php
function find_user(string $username): ?array
{
    foreach (read_json('users.json') as $user) {
        if (strcasecmp($user['username'], $username) === 0) {
            return $user;
        }
    }
    return null;
}

function register_user(string $username, string $password, string $password2): ?string
{
    $username = trim($username);

    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
        return 'err_username_format';
    }
    if (mb_strlen($password) < 6) {
        return 'err_password_short';
    }
    if ($password !== $password2) {
        return 'err_password_match';
    }
    if (find_user($username) !== null) {
        return 'err_username_taken';
    }

    $users = read_json('users.json');
    $users[] = [
        'username'   => $username,
        'password'   => password_hash($password, PASSWORD_DEFAULT),
        'role'       => count($users) === 0 ? 'admin' : 'user',
        'created_at' => date('c'),
    ];
    write_json('users.json', $users);
    return null;
}

function login_user(string $username, string $password): bool
{
    $user = find_user(trim($username));
    if ($user === null || !password_verify($password, $user['password'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['username'] = $user['username'];
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

function current_user(): ?array
{
    if (empty($_SESSION['username'])) {
        return null;
    }
    $user = find_user($_SESSION['username']);
    if ($user === null) {
        unset($_SESSION['username']);
    }
    return $user;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('403 - Bu sayfa sadece yöneticiler içindir.');
    }
    return $user;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function check_csrf(): bool
{
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}
