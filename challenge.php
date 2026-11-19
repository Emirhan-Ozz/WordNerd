<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$challenge = get_challenge((string)($_GET['id'] ?? ''));
if ($challenge === null) {
    http_response_code(404);
}

$results = $challenge['results'] ?? [];
$played = isset($results[$me['username']]);
$users = [];
foreach (users_by_names(array_keys($results)) as $u) {
    $users[$u['username']] = $u;
}
uasort($results, function ($a, $b) {
    return [$b['won'], -$a['attempts']] <=> [$a['won'], -$b['attempts']];
});

page_header('challenge_title', 'Meydan okuma', '');
?>
<section class="card">
    <?php if ($challenge === null): ?>
        <h2 data-i18n="challenge_title">Meydan okuma</h2>
        <p class="muted" data-i18n="err_challenge_not_found">Meydan okuma bulunamadı.</p>
    <?php else: ?>
        <div class="card-head">
            <div>
                <h2><span data-i18n="challenge_by">Meydan okuyan</span>:
                    <a href="profile.php?u=<?= e(rawurlencode($challenge['creator'])) ?>">@<?= e($challenge['creator']) ?></a></h2>
                <p class="muted small"><?= $challenge['lang'] === 'tr' ? 'Türkçe' : 'English' ?> · <?= e(substr($challenge['created_at'], 0, 10)) ?>
                    · <?= count($results) ?> <span data-i18n="players">oyuncu</span></p>
            </div>
            <?php if (!$played): ?>
                <a class="btn primary" href="index.php?challenge=<?= e($challenge['id']) ?>" data-i18n="play_challenge">Meydan okumayı oyna</a>
            <?php else: ?>
                <span class="answer"><?= e(upper($challenge['answer'], $challenge['lang'])) ?></span>
            <?php endif; ?>
        </div>

        <div class="challenge-share">
            <input type="text" readonly id="challengeShareLink" data-id="<?= e($challenge['id']) ?>" aria-label="Meydan okuma linki">
            <button type="button" class="btn small" id="copyChallengeBtn" data-i18n="copy_link">Linki kopyala</button>
        </div>

        <?php if (!$played): ?>
            <p class="muted" data-i18n="challenge_hidden">Kendi sonucunu görmeden önce diğerlerinin tahminleri gizli. Sadece renkleri görebilirsin.</p>
        <?php endif; ?>

        <div class="challenge-results">
            <?php foreach ($results as $name => $r): ?>
                <div class="challenge-card <?= $name === $me['username'] ? 'me' : '' ?>">
                    <a class="player" href="profile.php?u=<?= e(rawurlencode($name)) ?>">
                        <?= avatar_tag(avatar_of($users[$name] ?? []), 36) ?>
                        <b>@<?= e($name) ?></b>
                    </a>
                    <div class="challenge-score"><?= $r['won'] ? $r['attempts'] : 'X' ?>/<?= MAX_GUESSES ?><?= $r['hard'] ? '*' : '' ?></div>
                    <div class="mini-grid">
                        <?php foreach ($r['words'] as $w): $marks = evaluate_guess($w, $challenge['answer']); ?>
                            <div class="mini-row">
                                <?php if ($played): ?>
                                    <span class="mini-word mono"><?= e(upper($w, $challenge['lang'])) ?></span>
                                <?php endif; ?>
                                <span class="sq-row"><?php foreach ($marks as $m): ?><i class="sq <?= $m ?>"></i><?php endforeach; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<script>
(function () {
    const input = document.getElementById('challengeShareLink');
    if (!input) return;
    input.value = new URL('index.php?challenge=' + input.dataset.id, location.href).href;
    document.getElementById('copyChallengeBtn').addEventListener('click', async () => {
        input.select();
        try { await navigator.clipboard.writeText(input.value); } catch (e) { document.execCommand('copy'); }
        document.getElementById('copyChallengeBtn').textContent = WN.t('copied');
    });
})();
</script>
<?php page_footer(); ?>
