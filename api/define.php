<?php
require_once __DIR__ . '/../config.php';

if (current_user() === null) {
    json_response(['ok' => false, 'error' => 'err_auth'], 401);
}

$lang = (string)($_GET['lang'] ?? '');
$word = lower((string)($_GET['word'] ?? ''), $lang);

if (!in_array($lang, LANGS, true) || !in_array($word, load_words($lang, 5)['answers'], true)) {
    json_response(['ok' => false, 'error' => 'err_bad_request'], 400);
}

$def = get_definition($word, $lang);
if ($def === null) {
    json_response(['ok' => false, 'error' => 'err_dict_unavailable']);
}
json_response(['ok' => true, 'definition' => $def]);
