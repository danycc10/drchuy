const menu = document.querySelector('#site-navigation');
const toggle = document.querySelector('.nav-toggle');
toggle?.addEventListener('click', () => {
    const opened = menu.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(opened));
});
menu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    menu.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
}));
document.querySelector('[data-language-switch]')?.addEventListener('click', (event) => {
    if (location.hash) event.currentTarget.hash = location.hash;
});
