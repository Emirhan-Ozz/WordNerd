<?php
require_once __DIR__ . '/config.php';
$me = require_login();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_csrf()) {
        $message = ['err_csrf', 'error'];
    } elseif (($_POST['action'] ?? '') === 'password') {
        $err = change_password($me['username'], (string)($_POST['current'] ?? ''), (string)($_POST['new'] ?? ''), (string)($_POST['repeat'] ?? ''));
        $message = $err === null ? ['msg_password_changed', 'success'] : [$err, 'error'];
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $admins = array_filter(read_json('users.json'), fn($u) => $u['role'] === 'admin');
        if (!password_verify((string)($_POST['password'] ?? ''), $me['password'])) {
            $message = ['err_current_password', 'error'];
        } elseif ($me['role'] === 'admin' && count($admins) <= 1) {
            $message = ['err_last_admin', 'error'];
        } else {
            delete_account($me['username']);
            logout_user();
            header('Location: login.php?deleted=1');
            exit;
        }
    }
}

page_header('settings', 'Ayarlar', '');
?>
<?= $message ? alert_box($message[0], $message[1]) : '' ?>

<section class="card auth">
    <h2 data-i18n="change_password">Şifre değiştir</h2>
    <form method="post" class="form">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">
        <label><span data-i18n="current_password">Mevcut şifre</span>
            <input type="password" name="current" required autocomplete="current-password"></label>
        <label><span data-i18n="new_password">Yeni şifre</span>
            <input type="password" name="new" required minlength="6" autocomplete="new-password"></label>
        <label><span data-i18n="password_again">Şifre (tekrar)</span>
            <input type="password" name="repeat" required minlength="6" autocomplete="new-password"></label>
        <button class="btn primary" data-i18n="save">Kaydet</button>
    </form>
</section>

<section class="card auth danger-zone">
    <h2 data-i18n="delete_account">Hesabı sil</h2>
    <p class="muted" data-i18n="delete_account_hint">Hesabın, skorların, takiplerin, bildirimlerin ve meydan okumaların kalıcı olarak silinir.</p>
    <form method="post" class="form" onsubmit="return confirm(WN.t('confirm_delete_account'))">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <label><span data-i18n="current_password">Mevcut şifre</span>
            <input type="password" name="password" required autocomplete="current-password"></label>
        <button class="btn danger" data-i18n="delete_account">Hesabı sil</button>
    </form>
</section>
<?php page_footer(); ?>
