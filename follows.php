<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$owner = find_user((string)($_GET['u'] ?? '')) ?? $me;
$type = ($_GET['type'] ?? '') === 'following' ? 'following' : 'followers';
$names = $type === 'following' ? following_of($owner['username']) : followers_of($owner['username']);
$list = users_by_names($names);

page_header($type, $type === 'following' ? 'Takip edilenler' : 'Takipçiler', '');
?>
<section class="card">
    <div class="card-head">
        <h2><a href="profile.php?u=<?= e(rawurlencode($owner['username'])) ?>">@<?= e($owner['username']) ?></a>
            · <span data-i18n="<?= $type ?>"><?= $type ?></span> (<?= count($list) ?>)</h2>
        <nav class="tabs">
            <a href="?u=<?= e(rawurlencode($owner['username'])) ?>&type=followers" class="<?= $type === 'followers' ? 'active' : '' ?>" data-i18n="followers">Takipçiler</a>
            <a href="?u=<?= e(rawurlencode($owner['username'])) ?>&type=following" class="<?= $type === 'following' ? 'active' : '' ?>" data-i18n="following">Takip edilenler</a>
        </nav>
    </div>
    <?php if (!$list): ?>
        <p class="muted" data-i18n="nobody_here">Burada henüz kimse yok.</p>
    <?php else: ?>
        <ul class="user-list">
            <?php foreach ($list as $u): $av = avatar_of($u); ?>
                <li>
                    <a class="player" href="profile.php?u=<?= e(rawurlencode($u['username'])) ?>">
                        <?= avatar_tag($av, 40) ?>
                        <div>
                            <div>@<?= e($u['username']) ?></div>
                            <?php if ($av['title'] !== 'none'): ?><small class="muted" data-i18n="title_<?= e($av['title']) ?>"><?= e($av['title']) ?></small><?php endif; ?>
                        </div>
                    </a>
                    <?= follow_button($me['username'], $u['username']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
