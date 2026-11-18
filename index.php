<?php
require_once __DIR__ . '/config.php';
$user = require_login();

page_header('nav_play', 'Oyna', 'index.php');
?>
<section class="game-controls">
    <div class="seg" id="langSeg" role="group" aria-label="Kelime dili">
        <button type="button" data-value="tr">Türkçe</button>
        <button type="button" data-value="en">English</button>
    </div>
    <div class="seg" id="modeSeg" role="group">
        <button type="button" data-value="daily" data-i18n="mode_daily">Günlük</button>
        <button type="button" data-value="unlimited" data-i18n="mode_unlimited">Sınırsız</button>
    </div>
    <div class="hard-wrap" id="hardWrap">
        <label class="hard-switch">
            <input type="checkbox" id="hardToggle" role="switch" aria-describedby="hardTip">
            <span class="track"><span class="knob"></span></span>
            <span class="hard-label" data-i18n="hard_mode">Zor mod</span>
            <svg class="lock-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path class="shackle" d="M8 11V8a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                <rect x="5" y="11" width="14" height="10" rx="2.5" fill="currentColor"/>
            </svg>
        </label>
        <div class="tooltip" id="hardTip" role="tooltip">
            <b data-i18n="hard_mode">Zor mod</b>
            <span data-i18n="hard_tip">Yeşil harfler aynı yerde kalmalı, sarı harfler sonraki tahminlerde kullanılmalı. Liderlikte +1 puan.</span>
            <span class="tip-locked" data-i18n="hard_locked">Zor mod sadece ilk tahminden önce değiştirilebilir.</span>
        </div>
    </div>
</section>

<p class="game-sub" id="gameSub"></p>

<div id="toast" class="toast" role="status" aria-live="polite"></div>

<section id="board" class="board" aria-label="Oyun tahtası"></section>

<section id="keyboard" class="keyboard" aria-label="Klavye"></section>

<dialog id="resultDialog" class="dialog" data-avatar="<?= e(json_encode(avatar_of($user))) ?>">
    <div class="result-head">
        <span class="avatar" id="resultAvatar" style="width:72px;height:72px"></span>
        <div>
            <h2 id="resultTitle"></h2>
            <p><span data-i18n="the_word_was">Kelime:</span> <strong id="resultAnswer" class="answer"></strong></p>
        </div>
    </div>
    <div class="definition" id="definitionBox" aria-live="polite"></div>
    <pre id="shareText" class="share-text"></pre>
    <div class="dialog-actions">
        <button type="button" class="btn primary" id="shareBtn" data-i18n="share">Sonucu kopyala</button>
        <button type="button" class="btn" id="newGameBtn" data-i18n="new_game">Yeni oyun</button>
        <a class="btn ghost" href="profile.php" data-i18n="nav_profile">Profil</a>
        <button type="button" class="btn ghost" id="closeDialogBtn" data-i18n="close">Kapat</button>
    </div>
</dialog>
<?php page_footer(['assets/js/game.js']); ?>
