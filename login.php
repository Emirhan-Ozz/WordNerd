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
    } elseif (login_user($username, $_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'err_login';
    }
}

page_header('login_title', 'Giriş yap');
?>
<section class="card auth">
    <h2 data-i18n="login_title">Giriş yap</h2>
    <?= alert_box($error) ?>
    <?php if (isset($_GET['deleted'])): ?>
        <?= alert_box('msg_account_deleted', 'success') ?>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
        <?= alert_box('msg_registered', 'success') ?>
    <?php endif; ?>

    <form method="post" class="form">
        <?= csrf_field() ?>
        <label>
            <span data-i18n="username">Kullanıcı adı</span>
            <input name="username" value="<?= e($username) ?>" required autofocus autocomplete="username">
        </label>
        <label>
            <span data-i18n="password">Şifre</span>
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <button class="btn primary" data-i18n="login_btn">Giriş yap</button>
    </form>
    <p class="muted"><span data-i18n="no_account">Hesabın yok mu?</span>
        <a href="register.php" data-i18n="register_link">Kayıt ol</a></p>
</section>
<?php page_footer(); ?>
