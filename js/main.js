// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('nav-toggle');
  const menu = document.getElementById('mobile-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const isOpen = !menu.hidden;
      menu.hidden = isOpen;
      toggle.setAttribute('aria-expanded', String(!isOpen));
    });
    menu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // FAQ accordion (one open at a time per list)
  document.querySelectorAll('.faq-list').forEach(list => {
    const items = Array.from(list.querySelectorAll('.faq-item'));
    items.forEach(item => {
      const btn = item.querySelector('.faq-btn');
      const answer = item.querySelector('.faq-answer');
      const icon = item.querySelector('.faq-icon');
      btn.addEventListener('click', () => {
        const isOpen = btn.getAttribute('aria-expanded') === 'true';
        items.forEach(other => {
          const otherBtn = other.querySelector('.faq-btn');
          const otherAnswer = other.querySelector('.faq-answer');
          const otherIcon = other.querySelector('.faq-icon');
          otherBtn.setAttribute('aria-expanded', 'false');
          otherAnswer.hidden = true;
          otherIcon.textContent = '+';
        });
        if (!isOpen) {
          btn.setAttribute('aria-expanded', 'true');
          answer.hidden = false;
          icon.textContent = '−';
        }
      });
    });
  });

  // Contact page: path selector (Réserver une séance vs Poser une question)
  const pathReserver = document.getElementById('path-reserver');
  const pathQuestion = document.getElementById('path-question');
  const formReserver = document.getElementById('form-reserver');
  const formQuestion = document.getElementById('form-question');
  if (pathReserver && pathQuestion && formReserver && formQuestion) {
    const setContactMode = (mode) => {
      const isReserver = mode !== 'question';
      pathReserver.classList.toggle('active', isReserver);
      pathQuestion.classList.toggle('active', !isReserver);
      formReserver.classList.toggle('active', isReserver);
      formQuestion.classList.toggle('active', !isReserver);
    };
    const params = new URLSearchParams(window.location.search);
    setContactMode(params.get('mode') === 'question' ? 'question' : 'reserver');
    pathReserver.addEventListener('click', () => setContactMode('reserver'));
    pathQuestion.addEventListener('click', () => setContactMode('question'));
    document.querySelectorAll('.js-select-reserver').forEach(btn => {
      btn.addEventListener('click', () => setContactMode('reserver'));
    });
  }


  // Cotignac page: letter form (status message after buzon.php redirects back, 8 MB check, page language)
  const cartaForm = document.getElementById('form-carta');
  if (cartaForm) {
    const showStatus = (code) => {
      cartaForm.querySelectorAll('[data-carta-msg]').forEach(el => { el.hidden = el.dataset.cartaMsg !== code; });
    };
    const code = new URLSearchParams(window.location.search).get('carta');
    if (code) {
      showStatus(code);
      if (code === 'ok') cartaForm.reset();
      cartaForm.scrollIntoView({ block: 'center' });
      if (window.history.replaceState) window.history.replaceState(null, '', window.location.pathname + '#carta');
    }
    const fileInput = cartaForm.querySelector('input[type="file"]');
    if (fileInput) {
      fileInput.addEventListener('change', () => {
        const f = fileInput.files[0];
        if (f && f.size > 8 * 1024 * 1024) { fileInput.value = ''; showStatus('toobig'); } else { showStatus(''); }
      });
    }
    cartaForm.addEventListener('submit', () => {
      const lang = cartaForm.querySelector('input[name="lang"]');
      if (lang) lang.value = (document.documentElement.lang || 'es').slice(0, 2);
    });
  }
  // Scroll-reveal: fade up each top-level section as it enters view (skip the hero)
  const sections = Array.from(document.querySelectorAll('main > section')).slice(1);
  sections.forEach(el => el.classList.add('reveal'));
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    sections.forEach(el => io.observe(el));
  } else {
    sections.forEach(el => el.classList.add('is-visible'));
  }
});
