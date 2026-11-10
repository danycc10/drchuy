// Keep ordinary anchor links and browser history intact; only enhance orientation.
export function initActiveSections(document) {
    const window = document.defaultView;
    const header = document.querySelector('.site-header');
    const links = [...document.querySelectorAll('#site-navigation a[href^="#"]')];
    const destinations = links.map((link) => ({ link, target: document.getElementById(link.hash.slice(1)) }))
        .filter(({ target }) => target);
    if (!header || !destinations.length) return;

    let scheduled = false;
    let offset = 0;
    function update() {
        scheduled = false;
        const nextOffset = Math.ceil(header.getBoundingClientRect().height) + 16;
        if (offset !== nextOffset) {
            offset = nextOffset;
            document.documentElement.style.setProperty('--anchor-offset', `${offset}px`);
        }
        // Read immediately below the sticky header, including when its size changes.
        const readingLine = Math.min(offset + 1, window.innerHeight - 1);
        let current = null;
        destinations.forEach((destination) => {
            const rect = destination.target.getBoundingClientRect();
            if (rect.height <= 0 || rect.top > readingLine || rect.bottom <= readingLine) return;
            // Hours is inside Contact: prefer the more specific visible destination.
            if (!current || current.target.contains(destination.target)) current = destination;
        });
        links.forEach((link) => {
            if (link === current?.link) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    }
    const schedule = () => {
        if (scheduled) return;
        scheduled = true;
        if (window.requestAnimationFrame) window.requestAnimationFrame(update);
        else window.setTimeout(update, 16);
    };
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    window.addEventListener('hashchange', schedule);
    window.addEventListener('pageshow', schedule);
    document.addEventListener('load', schedule, true);
    document.addEventListener('toggle', schedule, true);
    if (window.ResizeObserver) {
        const observer = new window.ResizeObserver(schedule);
        observer.observe(header);
        destinations.forEach(({ target }) => observer.observe(target));
    }
    update();
}
