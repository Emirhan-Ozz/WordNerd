<?php
function http_get_json(string $url): array
{
    $body = false;
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'WordNerd/1.0 (student project)',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    } elseif (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http' => [
            'timeout'       => 6,
            'ignore_errors' => true,
            'header'        => "User-Agent: WordNerd/1.0 (student project)\r\nAccept: application/json\r\n",
        ]]);
        $body = @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int)$m[1];
        }
    }

    if ($body === false || $status === 0) {
        return ['status' => 0, 'data' => null];
    }
    return ['status' => $status, 'data' => json_decode($body, true)];
}

function fetch_definition_tr(string $word): ?array
{
    $res = http_get_json('https://sozluk.gov.tr/gts?ara=' . rawurlencode($word));
    if ($res['status'] !== 200 || !is_array($res['data'])) {
        return null;
    }
    return parse_tdk($res['data'], $word);
}

function parse_tdk(array $data, string $word): array
{
    if (isset($data['error']) || !isset($data[0])) {
        return ['found' => false];
    }

    $meanings = [];
    foreach ($data as $entry) {
        foreach ($entry['anlamlarListe'] ?? [] as $a) {
            $pos = $a['ozelliklerListe'][0]['tam_adi'] ?? '';
            $meanings[] = [
                'pos'     => $pos,
                'text'    => trim(strip_tags($a['anlam'] ?? '')),
                'example' => trim(strip_tags($a['orneklerListe'][0]['ornek'] ?? '')),
            ];
            if (count($meanings) >= 3) {
                break 2;
            }
        }
    }
    if (!$meanings) {
        return ['found' => false];
    }
    return [
        'found'    => true,
        'meanings' => $meanings,
        'phonetic' => '',
        'audio'    => '',
        'source'   => 'TDK Güncel Türkçe Sözlük',
        'url'      => 'https://sozluk.gov.tr/?kelime=' . rawurlencode($word),
    ];
}

function fetch_definition_en(string $word): ?array
{
    $res = http_get_json('https://api.dictionaryapi.dev/api/v2/entries/en/' . rawurlencode($word));
    if ($res['status'] === 404) {
        return ['found' => false];
    }
    if ($res['status'] !== 200 || !isset($res['data'][0])) {
        return null;
    }
    return parse_dictionaryapi($res['data'], $word);
}

function parse_dictionaryapi(array $data, string $word): array
{
    $meanings = [];
    $phonetic = '';
    $audio = '';
    foreach ($data as $entry) {
        $phonetic = $phonetic ?: ($entry['phonetic'] ?? '');
        foreach ($entry['phonetics'] ?? [] as $p) {
            $phonetic = $phonetic ?: ($p['text'] ?? '');
            if ($audio === '' && !empty($p['audio'])) {
                $audio = (strpos($p['audio'], '//') === 0 ? 'https:' : '') . $p['audio'];
            }
        }
        foreach ($entry['meanings'] ?? [] as $m) {
            foreach ($m['definitions'] ?? [] as $d) {
                $meanings[] = [
                    'pos'     => $m['partOfSpeech'] ?? '',
                    'text'    => $d['definition'] ?? '',
                    'example' => $d['example'] ?? '',
                ];
                if (count($meanings) >= 3) {
                    break 3;
                }
                break;
            }
        }
    }
    if (!$meanings) {
        return ['found' => false];
    }
    return [
        'found'    => true,
        'meanings' => $meanings,
        'phonetic' => $phonetic,
        'audio'    => $audio,
        'source'   => 'Free Dictionary API',
        'url'      => 'https://en.wiktionary.org/wiki/' . rawurlencode($word),
    ];
}

function get_definition(string $word, string $lang): ?array
{
    $key = "{$lang}:{$word}";
    $cache = read_json('definitions.json');
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $def = $lang === 'tr' ? fetch_definition_tr($word) : fetch_definition_en($word);
    if ($def === null) {
        return null;
    }

    $def['checked_at'] = date('c');
    if (!$def['found']) {
        $def['reviewed'] = false;
    }
    $cache = read_json('definitions.json');
    $cache[$key] = $def;
    write_json('definitions.json', $cache);
    return $def;
}

function missing_definitions(): array
{
    $list = [];
    foreach (read_json('definitions.json') as $key => $def) {
        if (empty($def['found']) && empty($def['reviewed'])) {
            [$lang, $word] = explode(':', $key, 2);
            $list[] = ['lang' => $lang, 'word' => $word, 'checked_at' => $def['checked_at'] ?? ''];
        }
    }
    return $list;
}

function mark_definition_reviewed(string $lang, string $word): void
{
    $cache = read_json('definitions.json');
    $key = "{$lang}:{$word}";
    if (isset($cache[$key])) {
        $cache[$key]['reviewed'] = true;
        write_json('definitions.json', $cache);
    }
}
