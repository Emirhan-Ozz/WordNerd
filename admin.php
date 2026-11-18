<?php
require_once __DIR__ . '/config.php';
$me = require_admin();

$tab    = in_array($_GET['tab'] ?? '', ['words', 'dict', 'users', 'scores'], true) ? $_GET['tab'] : 'words';
$lang   = in_array($_GET['lang'] ?? '', LANGS, true) ? $_GET['lang'] : 'tr';
$length = 5;
$q      = trim((string)($_GET['q'] ?? ''));

$alphabet = [
    'tr' => '/^[abcçdefgğhıijklmnoöprsştuüvyz]+$/u',
    'en' => '/^[a-z]+$/',
];

function back(string $msgKey, string $type = 'success'): void
{
    $_SESSION['flash'] = [$msgKey, $type];
    header('Location: admin.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        back('err_csrf', 'error');
    }
    $action = $_POST['action'] ?? '';
    $file   = "words/words_{$lang}_{$length}.json";

    switch ($action) {
        case 'add_word':
            $word = lower((string)($_POST['word'] ?? ''), $lang);
            if (mb_strlen($word, 'UTF-8') !== $length || !preg_match($alphabet[$lang], $word)) {
                back('err_word_format', 'error');
            }
            $words = load_words($lang, $length);
            if (in_array($word, $words['answers'], true)) {
                back('err_word_exists', 'error');
            }
            $words['answers'][] = $word;
            if (!in_array($word, $words['valid'], true)) {
                $words['valid'][] = $word;
                sort($words['valid']);
            }
            write_json($file, $words);
            back('msg_word_added');

        case 'remove_word':
            $word  = (string)($_POST['word'] ?? '');
            $words = load_words($lang, $length);
            if (count($words['answers']) <= 1) {
                back('err_last_word', 'error');
            }
            $words['answers'] = array_values(array_diff($words['answers'], [$word]));
            write_json($file, $words);
            back('msg_word_removed');

        case 'dict_ok':
            mark_definition_reviewed((string)($_POST['lang'] ?? ''), (string)($_POST['word'] ?? ''));
            back('msg_saved');

        case 'dict_remove':
            $dlang = in_array($_POST['lang'] ?? '', LANGS, true) ? $_POST['lang'] : 'tr';
            $word  = (string)($_POST['word'] ?? '');
            $words = load_words($dlang, 5);
            if (count($words['answers']) > 1) {
                $words['answers'] = array_values(array_diff($words['answers'], [$word]));
                write_json("words/words_{$dlang}_5.json", $words);
            }
            mark_definition_reviewed($dlang, $word);
            back('msg_word_removed');

        case 'toggle_role':
            $users = read_json('users.json');
            foreach ($users as &$u) {
                if ($u['username'] === ($_POST['username'] ?? '') && $u['username'] !== $me['username']) {
                    $u['role'] = $u['role'] === 'admin' ? 'user' : 'admin';
                }
            }
            unset($u);
            write_json('users.json', $users);
            back('msg_saved');

        case 'delete_user':
            $name = (string)($_POST['username'] ?? '');
            if ($name === $me['username']) {
                back('err_self_delete', 'error');
            }
            write_json('users.json', array_values(array_filter(read_json('users.json'), fn($u) => $u['username'] !== $name)));
            write_json('scores.json', array_values(array_filter(read_json('scores.json'), fn($s) => $s['username'] !== $name)));
            back('msg_deleted');

        case 'delete_score':
            $id = (string)($_POST['id'] ?? '');
            write_json('scores.json', array_values(array_filter(read_json('scores.json'), fn($s) => $s['id'] !== $id)));
            back('msg_deleted');
    }
    back('err_bad_request', 'error');
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

page_header('nav_admin', 'Yönetim', 'admin.php');
?>
<section class="card">
    <div class="card-head">
        <h2 data-i18n="admin_title">Yönetim paneli</h2>
        <nav class="tabs">
            <a href="?tab=words" class="<?= $tab === 'words' ? 'active' : '' ?>" data-i18n="tab_words">Kelimeler</a>
            <a href="?tab=dict" class="<?= $tab === 'dict' ? 'active' : '' ?>" data-i18n="tab_dict">Sözlük kontrolü</a>
            <a href="?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>" data-i18n="tab_users">Kullanıcılar</a>
            <a href="?tab=scores" class="<?= $tab === 'scores' ? 'active' : '' ?>" data-i18n="tab_scores">Skorlar</a>
        </nav>
    </div>
    <?= $flash ? alert_box($flash[0], $flash[1]) : '' ?>

<?php if ($tab === 'words'):
    $words   = load_words($lang, $length);
    $answers = $words['answers'];
    if ($q !== '') {
        $needle  = lower($q, $lang);
        $answers = array_values(array_filter($answers, fn($w) => strpos($w, $needle) !== false));
    }
    $shown = array_slice($answers, 0, 400);
?>
    <form method="get" class="filters">
        <input type="hidden" name="tab" value="words">
        <select name="lang" onchange="this.form.submit()">
            <option value="tr" <?= $lang === 'tr' ? 'selected' : '' ?> data-i18n="lang_tr">Türkçe</option>
            <option value="en" <?= $lang === 'en' ? 'selected' : '' ?> data-i18n="lang_en">İngilizce</option>
        </select>
        <input name="q" value="<?= e($q) ?>" placeholder="Ara" data-i18n-placeholder="search">
        <button class="btn small" data-i18n="search">Ara</button>
    </form>

    <p class="muted">
        <span data-i18n="answer_count">Cevap listesi</span>: <b><?= count($words['answers']) ?></b> ·
        <span data-i18n="valid_count">Geçerli tahmin</span>: <b><?= count($words['valid']) ?></b>
    </p>

    <form method="post" class="filters">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_word">
        <input name="word" required maxlength="<?= $length ?>" minlength="<?= $length ?>"
               placeholder="Yeni kelime" data-i18n-placeholder="new_word">
        <button class="btn primary small" data-i18n="add">Ekle</button>
    </form>

    <form method="post" class="chips" onsubmit="return confirm(WN.t('confirm_delete'))">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="remove_word">
        <?php foreach ($shown as $w): ?>
            <button class="chip" name="word" value="<?= e($w) ?>" title="Sil" data-i18n-title="delete">
                <?= e(upper($w, $lang)) ?> <span aria-hidden="true">×</span>
            </button>
        <?php endforeach; ?>
    </form>
    <?php if (count($answers) > count($shown)): ?>
        <p class="muted"><?= count($shown) ?> / <?= count($answers) ?> — <span data-i18n="use_search">daha fazlası için arama yap</span></p>
    <?php endif; ?>

<?php elseif ($tab === 'dict'):
    $missing = missing_definitions();
?>
    <p class="muted" data-i18n="dict_hint">Oyun sonunda sözlükte anlamı bulunamayan cevap kelimeleri. Kelime gerçekse "Kalsın" de, değilse cevap listesinden çıkar.</p>
    <?php if (!$missing): ?>
        <p class="muted" data-i18n="dict_empty">Kontrol edilecek kelime yok.</p>
    <?php else: ?>
    <table class="table">
        <thead><tr><th data-i18n="word">Kelime</th><th data-i18n="lang">Dil</th><th data-i18n="date">Tarih</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($missing as $m): ?>
            <tr>
                <td class="mono"><?= e(upper($m['word'], $m['lang'])) ?></td>
                <td><?= e(strtoupper($m['lang'])) ?></td>
                <td><?= e(substr($m['checked_at'], 0, 10)) ?></td>
                <td class="row-actions">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="lang" value="<?= e($m['lang']) ?>">
                        <input type="hidden" name="word" value="<?= e($m['word']) ?>">
                        <button class="btn small" name="action" value="dict_ok" data-i18n="keep_word">Kalsın</button>
                        <button class="btn small danger" name="action" value="dict_remove" data-i18n="remove_word">Listeden çıkar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

<?php elseif ($tab === 'users'):
    $users  = read_json('users.json');
    $counts = array_count_values(array_column(read_json('scores.json'), 'username'));
?>
    <table class="table">
        <thead><tr>
            <th data-i18n="username">Kullanıcı adı</th><th data-i18n="role">Rol</th>
            <th data-i18n="played">Oynanan</th><th data-i18n="joined">Kayıt</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['username']) ?></td>
                <td><span class="badge <?= $u['role'] === 'admin' ? 'accent' : '' ?>" data-i18n="role_<?= e($u['role']) ?>"><?= e($u['role']) ?></span></td>
                <td><?= $counts[$u['username']] ?? 0 ?></td>
                <td><?= e(substr($u['created_at'], 0, 10)) ?></td>
                <td class="row-actions">
                    <?php if ($u['username'] !== $me['username']): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="username" value="<?= e($u['username']) ?>">
                        <button class="btn small" name="action" value="toggle_role"
                                data-i18n="<?= $u['role'] === 'admin' ? 'make_user' : 'make_admin' ?>">Rol değiştir</button>
                        <button class="btn small danger" name="action" value="delete_user"
                                onclick="return confirm(WN.t('confirm_delete'))" data-i18n="delete">Sil</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php else:
    $scores = array_slice(array_reverse(read_json('scores.json')), 0, 100);
?>
    <?php if (!$scores): ?>
        <p class="muted" data-i18n="no_games">Henüz oyun yok.</p>
    <?php else: ?>
    <table class="table">
        <thead><tr>
            <th data-i18n="date">Tarih</th><th data-i18n="player">Oyuncu</th><th data-i18n="mode">Mod</th>
            <th data-i18n="word">Kelime</th><th data-i18n="result">Sonuç</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($scores as $s): ?>
            <tr>
                <td><?= e(substr($s['created_at'], 0, 16)) ?></td>
                <td><?= e($s['username']) ?></td>
                <td><?= e(strtoupper($s['lang'])) ?> · <span data-i18n="mode_<?= e($s['mode']) ?>"><?= e($s['mode']) ?></span></td>
                <td class="mono"><?= e(implode(' → ', array_map(fn($w) => upper($w, $s['lang']), $s['words']))) ?></td>
                <td><?= $s['won'] ? '✅ ' . $s['attempts'] : '❌' ?></td>
                <td>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                        <button class="btn small danger" name="action" value="delete_score"
                                onclick="return confirm(WN.t('confirm_delete'))" data-i18n="delete">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
<?php endif; ?>
</section>
<?php page_footer(); ?>
