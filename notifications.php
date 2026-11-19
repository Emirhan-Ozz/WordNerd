<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$list = notifications_for($me['username']);
$actors = [];
foreach (users_by_names(array_unique(array_column($list, 'actor'))) as $u) {
    $actors[$u['username']] = $u;
}

page_header('notifications', 'Bildirimler', '');
?>
<section class="card">
    <h2 data-i18n="notifications">Bildirimler</h2>
    <?php if (!$list): ?>
        <p class="muted" data-i18n="no_notifications">Henüz bildirim yok.</p>
    <?php else: ?>
        <ul class="user-list notif-list">
            <?php foreach ($list as $n):
                $actor = $n['actor'];
                $href = 'profile.php?u=' . rawurlencode($actor);
                $params = ['u' => '@' . $actor];
                if ($n['type'] === 'challenge_done') {
                    $href = 'challenge.php?id=' . rawurlencode($n['data']['id']);
                    $params['r'] = $n['data']['won'] ? $n['data']['attempts'] . '/' . MAX_GUESSES : 'X/' . MAX_GUESSES;
                } elseif ($n['type'] === 'passed') {
                    $href = 'leaderboard.php';
                    $params['p'] = $n['data']['points'];
                }
            ?>
                <li class="<?= $n['read'] ? '' : 'unread' ?>">
                    <a class="player" href="<?= e($href) ?>">
                        <?= avatar_tag(avatar_of($actors[$actor] ?? []), 36) ?>
                        <span data-i18n="notif_<?= e($n['type']) ?>" data-i18n-params="<?= e(json_encode($params, JSON_UNESCAPED_UNICODE)) ?>"><?= e($actor) ?></span>
                    </a>
                    <small class="muted"><?= e(date('d.m.Y H:i', strtotime($n['at']))) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php
mark_all_read($me['username']);
page_footer();
