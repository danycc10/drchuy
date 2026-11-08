export function initPublicNavigation(document) {
    const window = document.defaultView;
    const menu = document.querySelector('#site-navigation');
    const toggle = document.querySelector('.nav-toggle');
    if (menu && toggle) {
        document.body.classList.add('navigation-ready');
        const mobile = window.matchMedia('(max-width: 1000px)');
        function close(restoreFocus = false) {
            menu.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            if (restoreFocus) toggle.focus();
        }
        toggle.addEventListener('click', () => {
            const opened = menu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(opened));
        });
        menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
            close();
            const target = document.getElementById(link.hash.slice(1));
            if (target) {
                target.setAttribute('tabindex', '-1');
                target.focus({ preventScroll: true });
            }
        }));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && menu.classList.contains('open')) {
                event.preventDefault(); close(true);
            }
        });
        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) close();
        });
        document.addEventListener('focusin', (event) => {
            if (!menu.contains(event.target) && !toggle.contains(event.target)) close();
        });
        mobile.addEventListener('change', () => close(mobile.matches && menu.contains(document.activeElement)));
        window.addEventListener('pageshow', () => close());
    }

    const language = document.querySelector('[data-language-switch]');
    if (language) {
        const syncAnchor = () => { language.hash = window.location.hash; };
        syncAnchor();
        window.addEventListener('hashchange', syncAnchor);
    }
}
