<?php
function read_json(string $file, array $default = []): array
{
    $path = DATA_DIR . '/' . $file;
    if (!file_exists($path)) {
        return $default;
    }

    $content = file_get_contents($path);
    if ($content === false || trim($content) === '') {
        return $default;
    }

    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

function write_json(string $file, array $data): bool
{
    $path = DATA_DIR . '/' . $file;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}
