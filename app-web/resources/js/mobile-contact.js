export function initMobileContact(document) {
    const bar = document.querySelector('.mobile-contact');
    if (!bar) return;
    const window = document.defaultView;
    const update = () => {
        document.body.classList.toggle('search-is-focused', document.activeElement?.matches('[data-service-query]') ?? false);
        const height = Math.ceil(bar.getBoundingClientRect().height);
        document.documentElement.style.setProperty('--mobile-contact-height', `${height}px`);
    };
    if (window.ResizeObserver) new window.ResizeObserver(update).observe(bar);
    window.addEventListener('resize', update);
    window.addEventListener('pageshow', update);
    document.addEventListener('focusin', update);
    document.addEventListener('focusout', () => window.queueMicrotask(update));
    update();
}
