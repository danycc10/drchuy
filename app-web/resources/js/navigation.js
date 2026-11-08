export function initNavigation(document) {
    const window = document.defaultView;
    const toggle = document.querySelector('[data-menu-toggle]');
    const sidebar = document.querySelector('#admin-navigation');
    if (!toggle || !sidebar) return;

    const close = sidebar.querySelector('[data-menu-close]');
    const backdrop = document.querySelector('[data-nav-backdrop]');
    const workspace = document.querySelector('.admin-workspace');
    const skipLink = document.querySelector('.skip-link');
    const mobile = window.matchMedia('(max-width: 800px)');
    let opened = false;
    document.body.classList.add('navigation-ready');

    function setOpen(value, restoreFocus = true) {
        const wasOpen = opened;
        opened = value && mobile.matches;
        document.body.classList.toggle('nav-open', opened);
        toggle.setAttribute('aria-expanded', String(opened));
        sidebar.inert = mobile.matches && !opened;
        workspace.inert = opened;
        if (skipLink) skipLink.inert = opened;
        backdrop.hidden = !opened;
        if (opened) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            close.focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
            if (wasOpen && restoreFocus) {
                (mobile.matches ? toggle : sidebar.querySelector('[aria-current=page]') ?? sidebar.querySelector('a')).focus();
            }
        }
    }

    toggle.addEventListener('click', () => setOpen(!opened));
    close.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    mobile.addEventListener('change', () => {
        // When resizing to mobile, do not leave focus inside the collapsed sidebar.
        const focusedInSidebar = sidebar.contains(document.activeElement);
        setOpen(false);
        if (mobile.matches && focusedInSidebar) toggle.focus();
    });
    window.addEventListener('pageshow', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (!opened) return;
        if (event.key === 'Escape') { event.preventDefault(); setOpen(false); }
        if (event.key === 'Tab') {
            const items = [...sidebar.querySelectorAll('a[href], button:not([disabled])')];
            const first = items[0];
            const last = items.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            } else if (!sidebar.contains(document.activeElement)) {
                event.preventDefault(); first.focus();
            }
        }
    });
    setOpen(false, false);
}
