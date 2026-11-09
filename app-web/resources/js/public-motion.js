// Progressive enhancement: content remains visible if animation APIs are unavailable.
export function initPublicMotion(document) {
    const window = document.defaultView;
    const header = document.querySelector('.site-header');
    const locationBar = document.querySelector('.location-bar');
    if (header && locationBar && window.IntersectionObserver) {
        const headerObserver = new window.IntersectionObserver(([entry]) => {
            header.classList.toggle('is-scrolled', !entry.isIntersecting);
        });
        headerObserver.observe(locationBar);
    }

    const preference = window.matchMedia?.('(prefers-reduced-motion: reduce)');
    if (!preference || typeof window.Element.prototype.animate !== 'function') return;

    const active = new Map();
    const revealed = new WeakSet();
    let observer;
    function finishAll() {
        observer?.disconnect();
        active.forEach((animation) => animation.cancel());
        active.clear();
    }
    function reveal(element, delay = 0, portrait = false) {
        if (revealed.has(element)) return;
        revealed.add(element);
        if (preference.matches || element.contains(document.activeElement)) return;
        const animation = element.animate([
            { opacity: 0.55, transform: portrait ? 'translateY(12px) scale(0.985)' : 'translateY(14px)' },
            { opacity: 1, transform: 'none' },
        ], { duration: portrait ? 650 : 500, delay, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'backwards' });
        active.set(element, animation);
        const forget = () => { if (active.get(element) === animation) active.delete(element); };
        animation.onfinish = forget;
        animation.oncancel = forget;
    }

    preference.addEventListener('change', (event) => {
        if (event.matches) finishAll();
    });
    // A keyboard or anchor destination should be readable immediately.
    document.addEventListener('focusin', (event) => {
        active.forEach((animation, element) => {
            if (element.contains(event.target)) { animation.cancel(); active.delete(element); }
        });
    });
    window.addEventListener('hashchange', finishAll);
    window.addEventListener('pagehide', finishAll);

    if (preference.matches || window.location.hash || window.scrollY > 80) return;
    document.querySelectorAll('.hero-copy > .eyebrow, .hero-copy > h1, .hero-intro').forEach((element, index) => reveal(element, index * 60));
    const portrait = document.querySelector('.hero-image');
    if (portrait) reveal(portrait, 90, true);

    if (window.IntersectionObserver) {
        observer = new window.IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                observer.unobserve(entry.target);
                const gallery = entry.target.closest('.office-gallery');
                const delay = gallery ? Math.min([...gallery.children].indexOf(entry.target) * 60, 120) : 0;
                reveal(entry.target, delay);
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.care-inner, .section-heading, .physician-image, .physician-inner > div:last-child, .office-gallery figure, .contact-grid > div').forEach((element) => observer.observe(element));
    }
}
