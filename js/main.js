window.addEventListener('load', () => {
  const splash = document.getElementById('splash');
  if (splash) setTimeout(() => splash.classList.add('hide'), 2200);
});

const navbar = document.getElementById('navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 60);
  });
}

const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobileNav');
if (hamburger && mobileNav) {
  hamburger.addEventListener('click', () => {
    const open = mobileNav.classList.toggle('open');
    hamburger.classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  document.querySelectorAll('.mobile-link, .mobile-nav .btn').forEach(link => {
    link.addEventListener('click', () => {
      mobileNav.classList.remove('open');
      hamburger.classList.remove('open');
      document.body.style.overflow = '';
    });
  });
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
document.querySelectorAll('a[href*="whatsapp.com"], a[href*="wa.me"], a[href*="api.whatsapp.com"]').forEach((link) => {
  link.addEventListener('click', () => {
    if (typeof gtag === 'function') {
      gtag('event', 'whatsapp_click', { event_category: 'contato', event_label: 'WhatsApp' });
    }
  });
});

const whatsappInput = document.getElementById('whatsapp');
if (whatsappInput) {
  whatsappInput.addEventListener('input', (e) => {
    let v = e.target.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    if (v.length > 6) v = '(' + v.slice(0, 2) + ') ' + v.slice(2, 7) + '-' + v.slice(7);
    else if (v.length > 2) v = '(' + v.slice(0, 2) + ') ' + v.slice(2);
    else if (v.length > 0) v = '(' + v;
    e.target.value = v;
  });
}

window.handleSubmit = function (e) {
  e.preventDefault();
  const nome = document.getElementById('nome').value;
  const wp = document.getElementById('whatsapp').value;
  const servico = document.getElementById('servico').value;
  const msg = document.getElementById('mensagem').value;
  const text = encodeURIComponent(
    `Olá, Dra. Cyntia! Meu nome é ${nome}.\n` +
    `Tenho interesse em: ${servico}.\n` +
    (msg ? `Mensagem: ${msg}\n` : '') +
    `Meu WhatsApp: ${wp}`
  );
  window.open(`https://api.whatsapp.com/send/?phone=5531988648482&text=${text}&type=phone_number&app_absent=0`, '_blank');
};
