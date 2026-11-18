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

    <div class="tools">
        <select id="themeSelect" title="Tema" data-i18n-title="theme">
            <option value="classic" data-i18n="theme_classic">Classic</option>
            <option value="aqua" data-i18n="theme_aqua">Aqua</option>
            <option value="neon" data-i18n="theme_neon">Neon</option>
            <option value="granite" data-i18n="theme_granite">Granite</option>
            <option value="chill" data-i18n="theme_chill">Chill</option>
        </select>
        <button id="uiLangBtn" class="btn small" type="button" title="Arayüz dili">EN</button>
        <?php if ($user !== null): ?>
            <a class="who" href="profile.php" title="Profil" data-i18n-title="nav_profile">
                <?= avatar_tag(avatar_of($user), 30) ?>
                <span>@<?= e($user['username']) ?></span>
            </a>
            <a class="btn small ghost" href="logout.php" data-i18n="logout">Çıkış</a>
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
