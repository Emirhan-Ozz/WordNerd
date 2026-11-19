<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$q = trim((string)($_GET['q'] ?? ''));
$results = search_users($q);

page_header('search', 'Ara', '');
?>
<section class="card">
    <div class="card-head">
        <h2 data-i18n="search_users">Kullanıcı ara</h2>
        <form method="get" class="filters">
            <input name="q" value="<?= e($q) ?>" placeholder="@kullanıcı" data-i18n-placeholder="search_placeholder" autofocus>
            <button class="btn small primary" data-i18n="search">Ara</button>
        </form>
    </div>
    <?php if ($q === ''): ?>
        <p class="muted" data-i18n="search_hint">Bir kullanıcı adı yaz, örneğin @emirhan.</p>
    <?php elseif (!$results): ?>
        <p class="muted" data-i18n="no_results">Eşleşen kullanıcı bulunamadı.</p>
    <?php else: ?>
        <ul class="user-list">
            <?php foreach ($results as $u): $av = avatar_of($u); ?>
                <li>
                    <a class="player" href="profile.php?u=<?= e(rawurlencode($u['username'])) ?>">
                        <?= avatar_tag($av, 40) ?>
                        <div>
                            <div>@<?= e($u['username']) ?></div>
                            <small class="muted"><?= count(followers_of($u['username'])) ?> <span data-i18n="followers">takipçi</span></small>
                        </div>
                    </a>
                    <?= follow_button($me['username'], $u['username']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
