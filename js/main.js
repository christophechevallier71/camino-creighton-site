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

  // "Pour qui" info popups (Méthode page)
  const pourquiSection = document.getElementById('pourqui');
  if (pourquiSection) {
    const modal = pourquiSection.querySelector('.pourqui-modal');
    const panels = Array.from(pourquiSection.querySelectorAll('.pourqui-modal-panel'));
    const openModal = (key) => {
      panels.forEach(p => { p.hidden = p.dataset.cardPanel !== key; });
      modal.hidden = false;
    };
    const closeModal = () => { modal.hidden = true; };
    pourquiSection.querySelectorAll('.usage-more').forEach(btn => {
      btn.addEventListener('click', () => openModal(btn.dataset.cardTarget));
    });
    modal.querySelector('.pourqui-modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
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
