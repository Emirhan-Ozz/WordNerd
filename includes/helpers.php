<?php
function e($text): string
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function lower(string $text, string $lang): string
{
    $text = trim($text);
    if ($lang === 'tr') {
        $text = str_replace(['I', 'İ'], ['ı', 'i'], $text);
    }
    return mb_strtolower($text, 'UTF-8');
}

function upper(string $text, string $lang): string
{
    if ($lang === 'tr') {
        $text = str_replace(['i', 'ı'], ['İ', 'I'], $text);
    }
    return mb_strtoupper($text, 'UTF-8');
}

function letters(string $word): array
{
    return mb_str_split($word, 1, 'UTF-8');
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
