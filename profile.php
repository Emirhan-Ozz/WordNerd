<?php
require_once __DIR__ . '/config.php';
$user = require_login();

$allDaily = daily_scores($user['username']);
$ach = achievements($allDaily);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (check_csrf() && ($_POST['action'] ?? '') === 'save_avatar') {
        save_avatar($user['username'], validate_avatar($_POST['avatar'] ?? [], $ach));
        $_SESSION['flash'] = ['msg_avatar_saved', 'success'];
    } else {
        $_SESSION['flash'] = ['err_csrf', 'error'];
    }
    header('Location: profile.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$avatar = avatar_of($user);
$streak = streaks($allDaily);
$points = array_sum(array_map('game_points', $allDaily));

$tab = ($_GET['tab'] ?? '') === 'unlimited' ? 'unlimited' : 'daily';
$f = read_filters(['lang']);
$scores = filter_scores($f + ['username' => $user['username'], 'mode' => $tab]);
$stats = user_stats($scores);
$tabStreak = $tab === 'daily' ? streaks($scores) : ['current' => $stats['streak'], 'best' => $stats['best']];
$maxDist = max(1, max($stats['dist']));
$recent = array_slice(array_reverse($scores), 0, 10);

$rewardItems = [];
foreach (AVATAR_CATALOG as $slot => $items) {
    foreach ($items as $item => $need) {
        if ($need !== null && $slot !== 'title') {
            $rewardItems[$need][] = "{$slot}_{$item}";
        }
        if ($need !== null && $slot === 'title') {
            $rewardItems[$need][] = "title_{$item}";
        }
    }
}

$colorSlots = ['skin', 'hairColor', 'bg'];
$shapeSlots = ['hair', 'face', 'outfit', 'hat', 'glasses'];

page_header('nav_profile', 'Profil', 'profile.php');
?>
<?= $flash ? alert_box($flash[0], $flash[1]) : '' ?>

<section class="card profile-head">
    <div class="profile-avatar"><?= avatar_tag($avatar, 120) ?></div>
    <div class="profile-info">
        <h2>@<?= e($user['username']) ?></h2>
        <?php if ($avatar['title'] !== 'none'): ?>
            <span class="badge accent" data-i18n="title_<?= e($avatar['title']) ?>"><?= e($avatar['title']) ?></span>
        <?php endif; ?>
        <div class="profile-stats">
            <div><b>🔥 <?= $streak['current'] ?></b><span data-i18n="daily_streak">Günlük seri</span></div>
            <div><b><?= $streak['best'] ?></b><span data-i18n="best_streak">En iyi seri</span></div>
            <div><b><?= $points ?></b><span data-i18n="points">Puan</span></div>
            <div><b><?= count(array_filter($ach, fn($a) => $a['unlocked'])) ?>/<?= count($ach) ?></b><span data-i18n="rewards">Ödüller</span></div>
        </div>
    </div>
    <button type="button" class="btn primary" id="editAvatarBtn" data-i18n="edit_avatar">Avatarı düzenle</button>
</section>

<section class="card" id="avatarEditor" hidden>
    <div class="card-head">
        <h2 data-i18n="edit_avatar">Avatarı düzenle</h2>
    </div>
    <form method="post" class="avatar-editor" id="avatarForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_avatar">
        <div class="editor-preview"><span class="avatar" id="avatarPreview" style="width:160px;height:160px" data-avatar="<?= e(json_encode($avatar)) ?>"></span></div>
        <div class="editor-options">
            <?php foreach (array_merge($colorSlots, $shapeSlots) as $slot): ?>
            <fieldset class="opt-group">
                <legend data-i18n="slot_<?= $slot ?>"><?= $slot ?></legend>
                <div class="opts <?= in_array($slot, $colorSlots, true) ? 'swatches' : 'thumbs' ?>">
                <?php foreach (AVATAR_CATALOG[$slot] as $item => $need):
                    $locked = $need !== null && !$ach[$need]['unlocked'];
                    $preview = array_merge($avatar, [$slot => $item]);
                ?>
                    <label class="opt <?= $locked ? 'locked' : '' ?>" <?= $locked ? 'data-need="' . e($need) . '"' : '' ?> data-i18n-title="item_<?= $slot ?>_<?= $item ?>">
                        <input type="radio" name="avatar[<?= $slot ?>]" value="<?= e($item) ?>"
                               <?= $avatar[$slot] === $item ? 'checked' : '' ?> <?= $locked ? 'disabled' : '' ?>>
                        <?php if (in_array($slot, $colorSlots, true)): ?>
                            <span class="swatch" data-slot="<?= $slot ?>" data-item="<?= e($item) ?>"></span>
                        <?php else: ?>
                            <span class="avatar thumb" data-slot="<?= $slot ?>" data-avatar="<?= e(json_encode($preview)) ?>"></span>
                        <?php endif; ?>
                        <?php if ($locked): ?><span class="lock" aria-hidden="true">🔒</span><?php endif; ?>
                    </label>
                <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endforeach; ?>
            <fieldset class="opt-group">
                <legend data-i18n="slot_title">Unvan</legend>
                <select name="avatar[title]">
                    <?php foreach (AVATAR_CATALOG['title'] as $item => $need):
                        $locked = $need !== null && !$ach[$need]['unlocked']; ?>
                        <option value="<?= e($item) ?>" <?= $avatar['title'] === $item ? 'selected' : '' ?> <?= $locked ? 'disabled' : '' ?>
                                data-i18n="title_<?= e($item) ?>"><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
        </div>
        <div class="editor-actions">
            <button class="btn primary" data-i18n="save">Kaydet</button>
            <button type="button" class="btn ghost" id="cancelAvatarBtn" data-i18n="cancel">Vazgeç</button>
        </div>
    </form>
</section>

<section class="card">
    <h2 data-i18n="rewards">Ödüller</h2>
    <p class="muted" data-i18n="rewards_hint">Ödüller sadece günlük oyunlardan kazanılır.</p>
    <div class="achievements">
        <?php foreach ($ach as $a): ?>
        <div class="ach <?= $a['unlocked'] ? 'done' : '' ?>">
            <div class="ach-icon" aria-hidden="true"><?= $a['unlocked'] ? '🏆' : '🔒' ?></div>
            <div class="ach-body">
                <b data-i18n="ach_<?= $a['id'] ?>"><?= $a['id'] ?></b>
                <small class="muted">
                    <?php foreach ($rewardItems[$a['id']] ?? [] as $i => $itemKey): ?>
                        <?= $i > 0 ? ' + ' : '' ?><span data-i18n="item_<?= e($itemKey) ?>"><?= e($itemKey) ?></span>
                    <?php endforeach; ?>
                </small>
                <div class="progress" title="<?= $a['progress'] ?>/<?= $a['target'] ?>">
                    <span style="width: <?= round($a['progress'] / $a['target'] * 100) ?>%"></span>
                </div>
                <small class="muted"><?= $a['progress'] ?> / <?= $a['target'] ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="card">
    <div class="card-head">
        <nav class="tabs">
            <a href="?tab=daily" class="<?= $tab === 'daily' ? 'active' : '' ?>" data-i18n="mode_daily">Günlük</a>
            <a href="?tab=unlimited" class="<?= $tab === 'unlimited' ? 'active' : '' ?>" data-i18n="mode_unlimited">Sınırsız</a>
        </nav>
        <?php filter_form($f, 'wn_profile_filter_' . $tab, ['tab' => $tab]); ?>
    </div>

    <div class="tiles">
        <div class="tile"><b><?= $stats['played'] ?></b><span data-i18n="played">Oynanan</span></div>
        <div class="tile"><b>%<?= $stats['win_rate'] ?></b><span data-i18n="win_rate">Kazanma</span></div>
        <div class="tile"><b><?= $tabStreak['current'] ?></b><span data-i18n="<?= $tab === 'daily' ? 'streak_days' : 'streak' ?>">Seri</span></div>
        <div class="tile"><b><?= $tabStreak['best'] ?></b><span data-i18n="best_streak">En iyi seri</span></div>
    </div>

    <h3 data-i18n="distribution">Tahmin dağılımı</h3>
    <div class="dist">
        <?php foreach ($stats['dist'] as $try => $count): ?>
            <div class="dist-row">
                <span class="dist-label"><?= $try ?></span>
                <div class="dist-bar" style="width: <?= max(6, round($count / $maxDist * 100)) ?>%"><?= $count ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3 data-i18n="recent_games">Son oyunlar</h3>
    <?php if (!$recent): ?>
        <p class="muted" data-i18n="no_games">Henüz oyun yok.</p>
    <?php else: ?>
        <table class="table">
            <thead><tr>
                <th data-i18n="date">Tarih</th><th data-i18n="lang">Dil</th>
                <th data-i18n="word">Kelime</th><th data-i18n="result">Sonuç</th>
                <?php if ($tab === 'daily'): ?><th data-i18n="points">Puan</th><?php endif; ?>
            </tr></thead>
            <tbody>
            <?php foreach ($recent as $s): ?>
                <tr>
                    <td><?= e($s['date']) ?></td>
                    <td><?= e(strtoupper($s['lang'])) ?><?= $s['hard'] ? ' · <span data-i18n="hard_short">zor</span>' : '' ?></td>
                    <td class="mono"><?= e(upper($s['answer'] ?? (end($s['words']) ?: ''), $s['lang'])) ?></td>
                    <td><?= $s['won'] ? '✅ ' . $s['attempts'] . '/' . MAX_GUESSES : '❌ X/' . MAX_GUESSES ?></td>
                    <?php if ($tab === 'daily'): ?><td><?= game_points($s) ?></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php page_footer(['assets/js/profile.js']); ?>
