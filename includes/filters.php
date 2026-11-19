<?php
function read_filters(array $fields): array
{
    $f = [];
    if (in_array('lang', $fields, true)) {
        $f['lang'] = in_array($_GET['lang'] ?? '', LANGS, true) ? $_GET['lang'] : '';
    }
    if (in_array('period', $fields, true)) {
        $f['period'] = in_array($_GET['period'] ?? '', ['today', 'week'], true) ? $_GET['period'] : '';
    }
    if (in_array('scope', $fields, true)) {
        $f['scope'] = ($_GET['scope'] ?? '') === 'following' ? 'following' : '';
    }
    return $f;
}

function filter_form(array $f, string $storageKey, array $hidden = []): void
{
    $opt = function (string $value, string $current, string $key, string $label) {
        $sel = $value === $current ? ' selected' : '';
        return '<option value="' . e($value) . '"' . $sel . ' data-i18n="' . e($key) . '">' . e($label) . '</option>';
    };
    ?>
    <form method="get" class="filters" data-remember="<?= e($storageKey) ?>">
        <?php foreach ($hidden as $name => $value): ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
        <?php endforeach; ?>
        <?php if (array_key_exists('lang', $f)): ?>
        <select name="lang" onchange="this.form.submit()">
            <?= $opt('', $f['lang'], 'all_langs', 'Tüm diller') ?>
            <?= $opt('tr', $f['lang'], 'lang_tr', 'Türkçe') ?>
            <?= $opt('en', $f['lang'], 'lang_en', 'İngilizce') ?>
        </select>
        <?php endif; ?>
        <?php if (array_key_exists('period', $f)): ?>
        <select name="period" onchange="this.form.submit()">
            <?= $opt('', $f['period'], 'period_all', 'Tüm zamanlar') ?>
            <?= $opt('week', $f['period'], 'period_week', 'Son 7 gün') ?>
            <?= $opt('today', $f['period'], 'period_today', 'Bugün') ?>
        </select>
        <?php endif; ?>
        <?php if (array_key_exists('scope', $f)): ?>
        <select name="scope" onchange="this.form.submit()">
            <?= $opt('', $f['scope'], 'scope_all', 'Herkes') ?>
            <?= $opt('following', $f['scope'], 'scope_following', 'Takip ettiklerim') ?>
        </select>
        <?php endif; ?>
        <noscript><button class="btn small">Filtrele</button></noscript>
    </form>
    <?php
}
