(function () {
  const ALPHABET = {
    tr: 'abcçdefgğhıijklmnoöprsştuüvyz',
    en: 'abcdefghijklmnopqrstuvwxyz',
  };
  const KEYBOARD = {
    tr: ['qwertyuıopğü', 'asdfghjklşi', 'zxcvbnmöç'],
    en: ['qwertyuiop', 'asdfghjkl', 'zxcvbnm'],
  };
  const EMOJI = { correct: '🟩', present: '🟨', absent: '⬜' };

  const $ = id => document.getElementById(id);
  const board = $('board'), keyboard = $('keyboard'), toast = $('toast'), sub = $('gameSub');
  const dialog = $('resultDialog');

  const settings = Object.assign({ lang: 'tr', length: 5, mode: 'daily', hard: false },
                                 WN.store.get('wn_game_settings', {}));
  settings.length = 5;

  let state = null;
  let current = '';
  let busy = false;

  const locale = () => (settings.lang === 'tr' ? 'tr-TR' : 'en-US');
  const up = s => s.toLocaleUpperCase(locale());
  const low = s => s.toLocaleLowerCase(locale());
  const dailyKey = () => `wn_daily_${settings.lang}_${settings.length}`;

  function showToast(text, ms = 1800) {
    toast.textContent = text;
    toast.classList.add('show');
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => toast.classList.remove('show'), ms);
  }

  async function api(action, data) {
    const res = await fetch('api/game.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF': document.querySelector('meta[name="csrf"]').content,
      },
      body: JSON.stringify(Object.assign({ action }, settings, data)),
    });
    if (res.status === 401) { location.href = 'login.php'; return null; }
    return res.json();
  }

  function bindSegment(id, field, parse = v => v) {
    const seg = $(id);
    seg.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.blur();
        if (busy) return;
        settings[field] = parse(btn.dataset.value);
        saveSettings();
        start();
      });
    });
  }
  function syncControls() {
    for (const [id, field] of [['langSeg', 'lang'], ['modeSeg', 'mode']]) {
      $(id).querySelectorAll('button').forEach(b => {
        b.classList.toggle('active', b.dataset.value === String(settings[field]));
      });
    }
    $('hardToggle').checked = settings.hard;
    updateLock();
  }
  function updateLock() {
    const locked = !!(state && state.rows.length > 0);
    $('hardWrap').classList.toggle('locked', locked);
    $('hardToggle').setAttribute('aria-disabled', locked ? 'true' : 'false');
  }
  function saveSettings() { WN.store.set('wn_game_settings', settings); }

  bindSegment('langSeg', 'lang');
  bindSegment('modeSeg', 'mode');

  $('hardToggle').addEventListener('change', e => {
    e.target.blur();
    if (state && state.status === 'playing' && state.rows.length > 0) {
      e.target.checked = state.hard;
      const wrap = $('hardWrap');
      wrap.classList.remove('nope');
      void wrap.offsetWidth;
      wrap.classList.add('nope');
      showToast(WN.t('hard_locked'), 2500);
      return;
    }
    settings.hard = e.target.checked;
    saveSettings();
    start();
  });

  async function start(isNew = false) {
    busy = true;
    current = '';
    syncControls();
    const saved = WN.store.get(dailyKey());
    try {
      const data = await api('start', {
        new: isNew,
        saved: settings.mode === 'daily' && saved ? saved.words : [],
        savedDate: saved ? saved.date : '',
      });
      if (!data) return;
      if (!data.ok) { showToast(WN.t(data.error)); return; }
      state = data.state;
      settings.hard = state.hard;
      syncControls();
      render();
      if (state.status !== 'playing') setTimeout(openResult, 300);
    } catch (err) {
      showToast(WN.t('err_network'));
    } finally {
      busy = false;
    }
  }

  async function submit() {
    if (busy || !state || state.status !== 'playing') return;
    if ([...current].length !== state.length) {
      shake();
      showToast(WN.t('err_length', { n: state.length }));
      return;
    }
    busy = true;
    try {
      const data = await api('guess', { word: current });
      if (!data) return;
      if (!data.ok) {
        shake();
        showToast(WN.t(data.error, data.params));
        return;
      }
      state = data.state;
      current = '';
      if (state.mode === 'daily') {
        WN.store.set(dailyKey(), { date: state.date, words: state.rows.map(r => r.word) });
      }
      render(true);
      if (state.status !== 'playing') {
        setTimeout(openResult, state.length * 250 + 500);
      }
    } catch (err) {
      showToast(WN.t('err_network'));
    } finally {
      busy = false;
    }
  }

  function press(key) {
    if (!state || state.status !== 'playing' || busy) return;
    if (key === 'enter') return submit();
    if (key === 'back') {
      current = [...current].slice(0, -1).join('');
    } else if ([...current].length < state.length && ALPHABET[settings.lang].includes(key)) {
      current += key;
    }
    renderCurrentRow();
  }

  document.addEventListener('keydown', e => {
    if (e.ctrlKey || e.metaKey || e.altKey) return;
    if (['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(document.activeElement.tagName) && e.key === 'Enter') return;
    const ae = document.activeElement;
    if (['SELECT', 'TEXTAREA'].includes(ae.tagName) || (ae.tagName === 'INPUT' && ae.type !== 'checkbox')) return;
    if (dialog.open) return;
    if (e.key === 'Enter') { e.preventDefault(); press('enter'); }
    else if (e.key === 'Backspace') { e.preventDefault(); press('back'); }
    else if ([...e.key].length === 1) press(low(e.key));
  });

  function render(animateLast = false) {
    sub.textContent = state.mode === 'daily'
      ? WN.t('sub_daily', { date: state.date })
      : WN.t('sub_unlimited');

    board.style.setProperty('--cols', state.length);
    board.innerHTML = '';
    for (let r = 0; r < state.max; r++) {
      const row = document.createElement('div');
      row.className = 'row';
      const done = state.rows[r];
      const letters = done ? [...done.word] : [];
      for (let c = 0; c < state.length; c++) {
        const tile = document.createElement('div');
        tile.className = 'tile';
        if (done) {
          tile.textContent = up(letters[c]);
          tile.classList.add('filled', done.marks[c]);
          if (animateLast && r === state.rows.length - 1) {
            tile.classList.add('reveal');
            tile.style.animationDelay = (c * 250) + 'ms';
          }
        }
        row.appendChild(tile);
      }
      board.appendChild(row);
    }
    renderCurrentRow();
    renderKeyboard();
    updateLock();
  }

  function renderCurrentRow() {
    if (!state || state.status !== 'playing') return;
    const row = board.children[state.rows.length];
    if (!row) return;
    const letters = [...current];
    [...row.children].forEach((tile, i) => {
      tile.textContent = letters[i] ? up(letters[i]) : '';
      tile.classList.toggle('filled', !!letters[i]);
    });
  }

  function renderKeyboard() {
    const rank = { absent: 1, present: 2, correct: 3 };
    const best = {};
    state.rows.forEach(r => [...r.word].forEach((ch, i) => {
      const m = r.marks[i];
      if (!best[ch] || rank[m] > rank[best[ch]]) best[ch] = m;
    }));

    keyboard.innerHTML = '';
    KEYBOARD[settings.lang].forEach((line, i) => {
      const row = document.createElement('div');
      row.className = 'kb-row';
      if (i === KEYBOARD[settings.lang].length - 1) row.appendChild(keyButton('enter', WN.t('enter'), 'wide'));
      [...line].forEach(ch => {
        const btn = keyButton(ch, up(ch), best[ch] || '');
        if (!ALPHABET[settings.lang].includes(ch)) btn.disabled = true;
        row.appendChild(btn);
      });
      if (i === KEYBOARD[settings.lang].length - 1) row.appendChild(keyButton('back', '⌫', 'wide'));
      keyboard.appendChild(row);
    });
  }

  function keyButton(key, label, cls) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'key ' + cls;
    b.textContent = label;
    b.addEventListener('click', () => { press(key); b.blur(); });
    return b;
  }

  function shake() {
    const row = board.children[state ? state.rows.length : 0];
    if (!row) return;
    row.classList.remove('shake');
    void row.offsetWidth;
    row.classList.add('shake');
  }

  function shareText() {
    const head = `WordNerd ${state.lang.toUpperCase()} ${state.length} · ` +
      (state.mode === 'daily' ? `${WN.t('share_daily')} ${state.date}` : WN.t('share_unlimited'));
    const score = (state.status === 'won' ? state.rows.length : 'X') + '/' + state.max + (state.hard ? '*' : '');
    const grid = state.rows.map(r => r.marks.map(m => EMOJI[m]).join('')).join('\n');
    return `${head} · ${score}\n\n${grid}`;
  }

  const definitions = {};

  function esc(t) {
    const d = document.createElement('div');
    d.textContent = t;
    return d.innerHTML;
  }

  function renderDefinition(def) {
    const box = $('definitionBox');
    if (!def) {
      box.innerHTML = `<p class="muted">${esc(WN.t('def_unavailable'))}</p>`;
      return;
    }
    if (!def.found) {
      box.innerHTML = `<p class="muted">${esc(WN.t('def_not_found'))}</p>`;
      return;
    }
    const items = def.meanings.map(m => `<li>${m.pos ? `<em>${esc(m.pos)}</em> ` : ''}${esc(m.text)}${m.example ? `<br><span class="example">“${esc(m.example)}”</span>` : ''}</li>`).join('');
    const audio = def.audio ? `<button type="button" class="btn small" id="playAudio" aria-label="${esc(WN.t('listen'))}">🔊 ${esc(WN.t('listen'))}</button>` : '';
    box.innerHTML = `<div class="def-head"><b>${esc(WN.t('meaning'))}</b>${def.phonetic ? `<span class="muted">${esc(def.phonetic)}</span>` : ''}${audio}</div>
      <ol>${items}</ol>
      <small class="muted">${esc(WN.t('source'))}: <a href="${esc(def.url)}" target="_blank" rel="noopener">${esc(def.source)}</a></small>`;
    if (def.audio) $('playAudio').addEventListener('click', () => new Audio(def.audio).play().catch(() => {}));
  }

  async function loadDefinition() {
    const key = state.lang + ':' + state.answer;
    if (key in definitions) return renderDefinition(definitions[key]);
    $('definitionBox').innerHTML = `<p class="muted">${esc(WN.t('def_loading'))}</p>`;
    try {
      const res = await fetch(`api/define.php?lang=${state.lang}&word=${encodeURIComponent(state.answer)}`);
      const data = await res.json();
      definitions[key] = data.ok ? data.definition : null;
    } catch (e) {
      definitions[key] = null;
    }
    if (dialog.open) renderDefinition(definitions[key]);
  }

  function openResult() {
    if (!state || state.status === 'playing') return;
    $('resultTitle').textContent = state.status === 'won'
      ? WN.t('win_titles')[state.rows.length - 1]
      : WN.t('lose_title');
    $('resultAnswer').textContent = up(state.answer);
    const av = $('resultAvatar');
    av.dataset.avatar = dialog.dataset.avatar;
    av.dataset.mood = state.status === 'won' ? 'happy' : 'sad';
    av.classList.toggle('bounce', state.status === 'won');
    WN.renderAvatars(dialog);
    $('shareText').textContent = shareText() + (state.mode === 'daily' ? '\n\n' + WN.t('daily_done') : '');
    $('newGameBtn').hidden = state.mode !== 'unlimited';
    if (!dialog.open) dialog.showModal();
    loadDefinition();
  }


  async function copy(text) {
    try {
      await navigator.clipboard.writeText(text);
    } catch (e) {
      const ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      ta.remove();
    }
    showToast(WN.t('copied'));
  }

  $('shareBtn').addEventListener('click', () => copy(shareText()));
  $('newGameBtn').addEventListener('click', () => { dialog.close(); start(true); });
  $('closeDialogBtn').addEventListener('click', () => dialog.close());

  document.addEventListener('wn:langchange', () => {
    if (!state) return;
    render();
    if (dialog.open) openResult();
  });

  start();
})();
