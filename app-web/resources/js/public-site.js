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
        menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
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
    const languageForm = document.querySelector('[data-language-form]');
    if (languageForm) {
        const syncAnchor = () => {
            const anchor = window.location.hash.slice(1);
            languageForm.elements.anchor.value = ['services', 'physician', 'office', 'location', 'hours', 'faq'].includes(anchor) ? anchor : '';
        };
        syncAnchor();
        window.addEventListener('hashchange', syncAnchor);
        languageForm.addEventListener('submit', syncAnchor);
    }
}

export function initServiceSearch(document) {
    const search = document.querySelector('[data-service-search]');
    if (!search) return;
    const query = search.querySelector('[data-service-query]');
    const counter = search.querySelector('[data-service-count]');
    const empty = document.querySelector('[data-service-empty]');
    const normalize = (text) => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase().trim();
    const groups = [...document.querySelectorAll('[data-service-group]')];
    const categoryButtons = [...search.querySelectorAll('[data-service-filter]')];
    const categorySelect = search.querySelector('[data-service-category-select]');
    if (!categoryButtons.length) return;
    let category = (categoryButtons.find((button) => button.getAttribute('aria-pressed') === 'true') ?? categoryButtons[0]).dataset.serviceFilter;
    const focused = document.querySelector('[data-preview-focus]');
    const focusedCategory = focused?.closest('[data-service-group]')?.dataset.serviceCategory;
    if (categoryButtons.some((button) => button.dataset.serviceFilter === focusedCategory)) category = focusedCategory;
    const services = [...document.querySelectorAll('[data-service-item]')].map((item) => ({
        item,
        details: item.querySelector('details'),
        text: normalize(`${item.querySelector('h4').textContent} ${item.querySelector('.service-description p').textContent} ${item.closest('[data-service-group]').querySelector('h3').textContent}`),
        category: item.closest('[data-service-group]').dataset.serviceCategory,
        wasOpen: item.querySelector('details').open,
    }));
    let filtering = false;

    function filter() {
        const terms = normalize(query.value).split(/\s+/).filter(Boolean);
        const active = terms.length > 0;
        if (active && !filtering) services.forEach((service) => { service.wasOpen = service.details.open; });
        let count = 0;
        services.forEach((service) => {
            const matches = service.category === category && terms.every((term) => service.text.includes(term));
            service.item.hidden = !matches;
            if (active) service.details.open = matches;
            else if (filtering) service.details.open = service.wasOpen;
            if (matches) count++;
        });
        groups.forEach((group) => { group.hidden = !group.querySelector('[data-service-item]:not([hidden])'); });
        counter.textContent = counter.dataset.template.replace('{count}', count).replace('{total}', services.filter((service) => service.category === category).length);
        empty.hidden = count > 0;
        search.querySelector('[data-clear-service-search]').disabled = query.value.length === 0;
        categoryButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.serviceFilter === category)));
        if (categorySelect) categorySelect.value = category;
        filtering = active;
    }
    const clear = () => { query.value = ''; filter(); query.focus(); };
    categoryButtons.forEach((button) => button.addEventListener('click', () => { category = button.dataset.serviceFilter; filter(); }));
    categorySelect?.addEventListener('change', () => {
        if (categoryButtons.some((button) => button.dataset.serviceFilter === categorySelect.value)) category = categorySelect.value;
        filter();
    });
    query.addEventListener('input', filter);
    query.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && query.value) { event.preventDefault(); clear(); }
    });
    document.querySelectorAll('[data-clear-service-search]').forEach((button) => button.addEventListener('click', clear));
    const window = document.defaultView;
    window.addEventListener('pageshow', filter);
    // A direct link to a service card selects its category and opens it, even when another category was active.
    function revealFromHash(scroll) {
        const service = services.find((entry) => entry.item.id && entry.item.id === window.location.hash.slice(1));
        if (!service) return false;
        category = service.category;
        query.value = '';
        filter();
        service.details.open = true;
        service.wasOpen = true;
        service.item.setAttribute('tabindex', '-1');
        service.item.focus({ preventScroll: true });
        if (scroll) service.item.scrollIntoView?.({ block: 'start' });
        return true;
    }
    window.addEventListener('hashchange', () => revealFromHash(true));
    search.hidden = false;
    filter();
    if (focused && !focused.hidden) {
        query.value = ''; filter();
        const reveal = () => { focused.focus({ preventScroll: true }); focused.scrollIntoView?.({ block: 'center', behavior: 'auto' }); };
        if (window.requestAnimationFrame) window.requestAnimationFrame(reveal);
        else reveal();
    } else if (revealFromHash(false)) {
        const reveal = () => services.find((entry) => entry.item.id === window.location.hash.slice(1))?.item.scrollIntoView?.({ block: 'start' });
        if (window.requestAnimationFrame) window.requestAnimationFrame(reveal);
        else reveal();
    }
}
