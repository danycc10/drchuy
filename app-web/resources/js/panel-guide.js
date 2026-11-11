export function initPanelGuide(document) {
    const guide = document.querySelector('[data-panel-guide]');
    if (!guide) return;
    const window = document.defaultView;
    const steps = [...guide.querySelectorAll('[data-guide-step]')];
    const open = document.querySelector('[data-guide-open]');
    const close = guide.querySelector('[data-guide-close]');
    const previous = guide.querySelector('[data-guide-previous]');
    const next = guide.querySelector('[data-guide-next]');
    const heading = guide.querySelector('h2');
    let index = 0;
    const remember = (dismissed) => { try { window.localStorage.setItem(guide.dataset.guideKey, dismissed ? 'closed' : 'open'); } catch { /* Still works when browser storage is unavailable. */ } };
    function render() {
        steps.forEach((step, stepIndex) => { step.hidden = stepIndex !== index; });
        previous.disabled = index === 0;
        next.textContent = index === steps.length - 1 ? 'Terminar guía' : 'Siguiente';
        guide.querySelector('[data-guide-progress]').textContent = `Paso ${index + 1} de ${steps.length}: ${steps[index].querySelector('h3').textContent}`;
    }
    function dismiss() {
        guide.hidden = true; open.hidden = false; open.setAttribute('aria-expanded', 'false'); remember(true); open.focus();
    }
    open.addEventListener('click', () => {
        guide.hidden = false; open.hidden = true; open.setAttribute('aria-expanded', 'true'); index = 0; render(); remember(false); heading.focus();
    });
    close.addEventListener('click', dismiss);
    previous.addEventListener('click', () => { if (index > 0) { index--; render(); if (previous.disabled) next.focus(); } });
    next.addEventListener('click', () => { if (index === steps.length - 1) dismiss(); else { index++; render(); } });
    let dismissed = false;
    try { dismissed = window.localStorage.getItem(guide.dataset.guideKey) === 'closed'; } catch { /* Show the guide normally. */ }
    if (new window.URL(window.location.href).searchParams.get('guide') === '1') dismissed = false;
    guide.hidden = dismissed; open.hidden = !dismissed; open.setAttribute('aria-expanded', String(!dismissed));
    close.hidden = false; guide.querySelector('[data-guide-navigation]').hidden = false;
    render();
}
