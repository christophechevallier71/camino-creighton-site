// Simple i18n: text lives in lang/*.json, HTML elements are tagged with data-i18n.
// The static HTML already contains the Spanish text as a fallback (most
// clients are in Madrid, and this is also what Google indexes by default),
// so this degrades gracefully if the fetch fails — the page still reads
// correctly.
(function () {
  // Hostinger's CDN caches static files for 7 days — bump this on every
  // content change so lang/*.json is fetched fresh instead of from cache.
  const ASSET_VERSION = '8';
  const STORAGE_KEY = 'camino_lang';
  const DEFAULT_LANG = 'es';
  // Add 'en' here once its lang/en.json file is translated — that alone
  // is enough to turn the FR/ES/EN switcher on for it too.
  const AVAILABLE_LANGS = ['fr', 'es'];

  function getStoredLang() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return AVAILABLE_LANGS.includes(saved) ? saved : DEFAULT_LANG;
    } catch (e) {
      return DEFAULT_LANG;
    }
  }

  function getValue(dict, path) {
    return path.split('.').reduce((obj, key) => (obj && obj[key] !== undefined) ? obj[key] : undefined, dict);
  }

  function applyTranslations(dict) {
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const value = getValue(dict, el.getAttribute('data-i18n'));
      if (value !== undefined) el.innerHTML = value;
    });
    document.querySelectorAll('[data-i18n-alt]').forEach(el => {
      const value = getValue(dict, el.getAttribute('data-i18n-alt'));
      if (value !== undefined) el.setAttribute('alt', value);
    });
    document.querySelectorAll('[data-i18n-content]').forEach(el => {
      const value = getValue(dict, el.getAttribute('data-i18n-content'));
      if (value !== undefined) el.setAttribute('content', value);
    });
    document.querySelectorAll('[data-i18n-title]').forEach(el => {
      const value = getValue(dict, el.getAttribute('data-i18n-title'));
      if (value !== undefined) el.setAttribute('title', value);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
      const value = getValue(dict, el.getAttribute('data-i18n-placeholder'));
      if (value !== undefined) el.setAttribute('placeholder', value);
    });
  }

  function setActiveLangButton(lang) {
    document.querySelectorAll('.lang-switch [data-lang]').forEach(btn => {
      btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
    });
    document.documentElement.setAttribute('lang', lang);
  }

  function loadLang(lang) {
    fetch('lang/' + lang + '.json?v=' + ASSET_VERSION)
      .then(res => res.json())
      .then(dict => {
        applyTranslations(dict);
        setActiveLangButton(lang);
      })
      .catch(() => { /* static Spanish markup already shown — nothing to do */ });
  }

  window.setCaminoLang = function (lang) {
    if (!AVAILABLE_LANGS.includes(lang)) return;
    try { localStorage.setItem(STORAGE_KEY, lang); } catch (e) {}
    loadLang(lang);
  };

  document.addEventListener('DOMContentLoaded', () => {
    loadLang(getStoredLang());
    document.querySelectorAll('.lang-switch button[data-lang]').forEach(btn => {
      btn.addEventListener('click', () => window.setCaminoLang(btn.getAttribute('data-lang')));
    });
  });
})();
