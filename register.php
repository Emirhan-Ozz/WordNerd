<?php
require_once __DIR__ . '/config.php';

if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    if (!check_csrf()) {
        $error = 'err_csrf';
    } else {
        $error = register_user($username, $_POST['password'] ?? '', $_POST['password2'] ?? '');
        if ($error === null) {
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

page_header('register_title', 'Kayıt ol');
?>
<section class="card auth">
    <h2 data-i18n="register_title">Kayıt ol</h2>
    <?= alert_box($error) ?>

    <form method="post" class="form">
        <?= csrf_field() ?>
        <label>
            <span data-i18n="username">Kullanıcı adı</span>
            <input name="username" value="<?= e($username) ?>" required pattern="[A-Za-z0-9_]{3,20}"
                   maxlength="20" autofocus autocomplete="username">
            <small class="muted" data-i18n="username_hint">3-20 karakter: harf, rakam, alt çizgi</small>
        </label>
        <label>
            <span data-i18n="password">Şifre</span>
            <input name="password" type="password" required minlength="6" autocomplete="new-password">
        </label>
        <label>
            <span data-i18n="password_again">Şifre (tekrar)</span>
            <input name="password2" type="password" required minlength="6" autocomplete="new-password">
        </label>
        <button class="btn primary" data-i18n="register_btn">Kayıt ol</button>
    </form>
    <p class="muted"><span data-i18n="have_account">Zaten hesabın var mı?</span>
        <a href="login.php" data-i18n="login_link">Giriş yap</a></p>
</section>
<?php page_footer(); ?>
