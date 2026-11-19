<?php
require_once __DIR__ . '/config.php';
$user = require_login();

$f = read_filters(['lang', 'period', 'scope']);
$query = $f + ['mode' => 'daily'];
if ($f['scope'] === 'following') {
    $query['usernames'] = array_merge(following_of($user['username']), [$user['username']]);
}
$rows = daily_leaderboard(filter_scores($query));

page_header('nav_leaderboard', 'Liderlik', 'leaderboard.php');
?>
<section class="card">
    <div class="card-head">
        <div>
            <h2 data-i18n="leaderboard_title">Liderlik tablosu</h2>
            <p class="muted small" data-i18n="leaderboard_hint">Sadece günlük kelimeler sayılır. 1. denemede 6 puan … 6. denemede 1 puan, zor modda +1.</p>
        </div>
        <?php filter_form($f, 'wn_lb_filter'); ?>
    </div>

    <?php if (!$rows): ?>
        <p class="muted" data-i18n="no_games">Henüz oyun yok.</p>
    <?php else: ?>
        <table class="table leaderboard">
            <thead><tr>
                <th>#</th><th data-i18n="player">Oyuncu</th><th data-i18n="points">Puan</th>
                <th data-i18n="wins">Kazanılan</th><th data-i18n="played">Oynanan</th>
                <th data-i18n="avg_guesses">Ort. deneme</th><th data-i18n="daily_streak">Günlük seri</th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $i => $r): ?>
                <tr class="<?= $r['username'] === $user['username'] ? 'me' : '' ?>">
                    <td class="rank"><?= ['🥇', '🥈', '🥉'][$i] ?? $i + 1 ?></td>
                    <td>
                        <a class="player" href="profile.php?u=<?= e(rawurlencode($r['username'])) ?>">
                            <?= avatar_tag($r['avatar'], 36) ?>
                            <div>
                                <div><?= e($r['username']) ?></div>
                                <?php if ($r['title'] !== 'none'): ?>
                                    <small class="muted" data-i18n="title_<?= e($r['title']) ?>"><?= e($r['title']) ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    </td>
                    <td><b><?= $r['points'] ?></b></td>
                    <td><?= $r['wins'] ?></td>
                    <td><?= $r['played'] ?></td>
                    <td><?= $r['avg'] ?? '–' ?></td>
                    <td><?= $r['streak'] > 0 ? '🔥 ' . $r['streak'] : '–' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
