<?php
const BACKUP_FILES = [
    'users.json', 'scores.json', 'definitions.json', 'challenges.json', 'notifications.json',
    'words/words_tr_5.json', 'words/words_en_5.json',
];
const BACKUP_KEEP = 10;

function make_backup(): array
{
    $files = [];
    foreach (BACKUP_FILES as $file) {
        $files[$file] = read_json($file);
    }
    return ['app' => 'WordNerd', 'version' => 1, 'created_at' => date('c'), 'files' => $files];
}

function backup_filename(string $prefix): string
{
    return $prefix . '-' . date('Ymd-His') . '.json';
}

function save_auto_backup(): string
{
    $dir = DATA_DIR . '/backups';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $name = 'auto-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.json';
    file_put_contents($dir . '/' . $name, json_encode(make_backup(), JSON_UNESCAPED_UNICODE), LOCK_EX);

    $all = list_auto_backups();
    foreach (array_slice($all, BACKUP_KEEP) as $old) {
        @unlink($dir . '/' . $old['name']);
    }
    return $name;
}

function list_auto_backups(): array
{
    $list = [];
    foreach (glob(DATA_DIR . '/backups/*.json') ?: [] as $path) {
        $list[] = ['name' => basename($path), 'size' => filesize($path), 'time' => filemtime($path)];
    }
    usort($list, fn($a, $b) => $b['time'] <=> $a['time']);
    return $list;
}

function read_auto_backup(string $name): ?string
{
    if (!preg_match('/^auto-\d{8}-\d{6}-[a-f0-9]{4}\.json$/', $name)) {
        return null;
    }
    $path = DATA_DIR . '/backups/' . $name;
    return is_file($path) ? file_get_contents($path) : null;
}

function validate_backup($data): ?string
{
    if (!is_array($data) || ($data['app'] ?? '') !== 'WordNerd' || !is_array($data['files'] ?? null)) {
        return 'err_backup_invalid';
    }
    foreach ($data['files'] as $file => $content) {
        if (!in_array($file, BACKUP_FILES, true) || !is_array($content)) {
            return 'err_backup_invalid';
        }
    }
    $users = $data['files']['users.json'] ?? null;
    if (!is_array($users) || !array_filter($users, fn($u) => ($u['role'] ?? '') === 'admin')) {
        return 'err_backup_no_admin';
    }
    foreach (['words/words_tr_5.json', 'words/words_en_5.json'] as $w) {
        if (empty($data['files'][$w]['answers'])) {
            return 'err_backup_invalid';
        }
    }
    return null;
}

function restore_backup(array $data): void
{
    save_auto_backup();
    foreach ($data['files'] as $file => $content) {
        write_json($file, $content);
    }
}
