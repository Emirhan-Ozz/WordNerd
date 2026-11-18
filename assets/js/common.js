(function () {
  WN.store = {
    get(key, fallback = null) {
      try { const v = localStorage.getItem(key); return v === null ? fallback : JSON.parse(v); }
      catch (e) { return fallback; }
    },
    set(key, value) {
      try { localStorage.setItem(key, JSON.stringify(value)); } catch (e) { }
    },
  };

  const themeSelect = document.getElementById('themeSelect');
  const currentTheme = document.documentElement.dataset.theme || 'classic';
  if (themeSelect) {
    themeSelect.value = currentTheme;
    themeSelect.addEventListener('change', () => {
      document.documentElement.dataset.theme = themeSelect.value;
      try { localStorage.setItem('wn_theme', themeSelect.value); } catch (e) {}
    });
  }

  const langBtn = document.getElementById('uiLangBtn');
  function refreshLangButton() {
    if (langBtn) langBtn.textContent = WN.uiLang() === 'tr' ? 'EN' : 'TR';
  }
  if (langBtn) {
    langBtn.addEventListener('click', () => {
      try { localStorage.setItem('wn_ui_lang', WN.uiLang() === 'tr' ? 'en' : 'tr'); } catch (e) {}
      WN.applyI18n();
      refreshLangButton();
      document.dispatchEvent(new Event('wn:langchange'));
    });
  }
  WN.applyI18n();
  refreshLangButton();

  document.querySelectorAll('form[data-remember]').forEach(form => {
    const key = form.dataset.remember;
    const saved = WN.store.get(key);

    if (!location.search && saved && Object.values(saved).some(v => v)) {
      location.replace('?' + new URLSearchParams(saved).toString());
      return;
    }
    WN.store.set(key, Object.fromEntries(new FormData(form)));
  });
})();
