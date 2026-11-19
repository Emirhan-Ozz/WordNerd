(function () {
  const editor = document.getElementById('avatarEditor');
  const form = document.getElementById('avatarForm');
  const preview = document.getElementById('avatarPreview');
  if (!editor || !form) return;

  document.getElementById('editAvatarBtn').addEventListener('click', () => {
    editor.hidden = false;
    editor.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  document.getElementById('cancelAvatarBtn').addEventListener('click', () => {
    form.reset();
    update();
    editor.hidden = true;
  });

  form.querySelectorAll('.swatch').forEach(el => {
    if (el.dataset.slot === 'bg') {
      el.style.background = WN.avatarBgCss(el.dataset.item);
    } else {
      const map = { skin: WN.avatarColors.SKIN, hairColor: WN.avatarColors.HAIR }[el.dataset.slot];
      el.style.background = map[el.dataset.item];
    }
  });

  function lockTitles() {
    form.querySelectorAll('.opt.locked').forEach(el => {
      el.title = WN.t('locked_by', { a: WN.t('ach_' + el.dataset.need) });
    });
    form.querySelectorAll('select option:disabled').forEach(opt => {
      opt.textContent = WN.t(opt.dataset.i18n) + ' 🔒';
    });
  }

  function current() {
    const data = {};
    new FormData(form).forEach((v, k) => {
      const m = k.match(/^avatar\[(\w+)\]$/);
      if (m) data[m[1]] = v;
    });
    return data;
  }

  function update() {
    const a = current();
    preview.dataset.avatar = JSON.stringify(a);
    form.querySelectorAll('.avatar.thumb').forEach(el => {
      const slot = el.dataset.slot;
      const extra = slot === 'hair' ? { hat: 'none' } : {};
      el.dataset.avatar = JSON.stringify(Object.assign({}, a, extra, { [slot]: el.closest('.opt').querySelector('input').value }));
    });
    WN.renderAvatars(form);
  }

  form.addEventListener('change', update);
  document.addEventListener('wn:langchange', lockTitles);
  lockTitles();
  update();
})();
