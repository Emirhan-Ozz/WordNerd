<?php
function is_valid_mode(string $lang, int $length): bool
{
    return in_array($lang, LANGS, true) && in_array($length, LENGTHS, true);
}

function load_words(string $lang, int $length): array
{
    static $cache = [];
    $key = "{$lang}_{$length}";

    if (!isset($cache[$key])) {
        $cache[$key] = read_json("words/words_{$key}.json", ['answers' => [], 'valid' => []]);
    }
    return $cache[$key];
}

function is_valid_word(string $word, string $lang, int $length): bool
{
    $words = load_words($lang, $length);
    $word  = lower($word, $lang);

    return mb_strlen($word, 'UTF-8') === $length
        && in_array($word, $words['valid'], true);
}

function daily_word(string $lang, int $length, ?string $date = null): string
{
    $date    = $date ?? date('Y-m-d');
    $answers = load_words($lang, $length)['answers'];
    $index   = crc32("wordnerd-{$date}-{$lang}-{$length}") % count($answers);
    return $answers[$index];
}

function random_word(string $lang, int $length): string
{
    $answers = load_words($lang, $length)['answers'];
    return $answers[array_rand($answers)];
}
