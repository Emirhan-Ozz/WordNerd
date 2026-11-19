<?php
function page_header(string $titleKey, string $titleTr, string $active = ''): void
{
    $user = current_user();
    $nav = [
        'index.php'       => ['nav_play', 'Oyna'],
        'profile.php'     => ['nav_profile', 'Profil'],
        'leaderboard.php' => ['nav_leaderboard', 'Liderlik'],
    ];
    if ($user !== null && $user['role'] === 'admin') {
        $nav['admin.php'] = ['nav_admin', 'Yönetim'];
    }
    ?>
<!DOCTYPE html>
<html lang="tr" data-theme="classic">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <title><?= e($titleTr) ?> · WordNerd</title>
    <script>
        try {
            var theme = localStorage.getItem('wn_theme') || 'classic';
            if (theme === 'claude') { theme = 'chill'; localStorage.setItem('wn_theme', theme); }
            document.documentElement.dataset.theme = theme;
            if (localStorage.getItem('wn_colorblind') === 'on' || localStorage.getItem('wn_contrast') === 'high') { document.documentElement.dataset.colorblind = 'on'; }
            document.documentElement.lang = localStorage.getItem('wn_ui_lang') || 'tr';
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%236aaa64'/><text x='16' y='23' font-size='20' font-family='Arial' font-weight='bold' fill='white' text-anchor='middle'>W</text></svg>">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">Word<span>Nerd</span></a>

    <?php if ($user !== null): ?>
    <nav class="nav">
        <?php foreach ($nav as $href => [$key, $label]): ?>
            <a href="<?= $href ?>" class="<?= $active === $href ? 'active' : '' ?>" data-i18n="<?= $key ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php if ($user !== null): ?>
    <form class="search-box" method="get" action="search.php" role="search">
        <input name="q" placeholder="@kullanıcı ara" data-i18n-placeholder="search_placeholder" aria-label="Kullanıcı ara" data-i18n-title="search_users">
    </form>
    <?php endif; ?>

    <div class="tools">
        <select id="themeSelect" title="Tema" data-i18n-title="theme">
            <option value="classic" data-i18n="theme_classic">Classic</option>
            <option value="aqua" data-i18n="theme_aqua">Aqua</option>
            <option value="neon" data-i18n="theme_neon">Neon</option>
            <option value="granite" data-i18n="theme_granite">Granite</option>
            <option value="chill" data-i18n="theme_chill">Chill</option>
            <option value="ruby" data-i18n="theme_ruby">Ruby</option>
        </select>
        <button id="colorblindBtn" class="btn small" type="button" aria-pressed="false" title="Renk körü modu" data-i18n-title="colorblind_mode" aria-label="Renk körü modu"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor"/></svg></button>
        <button id="uiLangBtn" class="btn small" type="button" title="Arayüz dili">EN</button>
        <?php if ($user !== null): ?>
            <?php $unread = unread_count($user['username']); ?>
            <a class="icon-btn" href="notifications.php" title="Bildirimler" data-i18n-title="notifications" aria-label="Bildirimler">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z M10 20a2 2 0 0 0 4 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
                <?php if ($unread > 0): ?><span class="badge-dot"><?= $unread > 9 ? '9+' : $unread ?></span><?php endif; ?>
            </a>
            <a class="icon-btn" href="settings.php" title="Ayarlar" data-i18n-title="settings" aria-label="Ayarlar">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </a>
            <a class="who" href="profile.php" title="Profil" data-i18n-title="nav_profile">
                <?= avatar_tag(avatar_of($user), 30) ?>
                <span>@<?= e($user['username']) ?></span>
            </a>
            <form method="post" action="logout.php" class="logout-form">
                <?= csrf_field() ?>
                <button class="btn small ghost" data-i18n="logout">Çıkış</button>
            </form>
        <?php endif; ?>
    </div>
</header>
<main class="page">
<h1 class="sr-only" data-i18n="<?= e($titleKey) ?>"><?= e($titleTr) ?></h1>
    <?php
}

function page_footer(array $scripts = []): void
{
    ?>
</main>
<footer class="footer">WordNerd · Made by Emirhan Öz</footer>
<script src="assets/js/i18n.js"></script>
<script src="assets/js/common.js"></script>
<script src="assets/js/avatar.js"></script>
<?php foreach ($scripts as $src): ?>
<script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
    <?php
}

function alert_box(?string $key, string $type = 'error'): string
{
    if ($key === null) {
        return '';
    }
    return '<div class="alert ' . e($type) . '" data-i18n="' . e($key) . '">' . e($key) . '</div>';
}
