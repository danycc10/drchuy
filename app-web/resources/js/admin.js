document.querySelector('[data-menu-toggle]')?.addEventListener('click', (event) => {
    const opened = document.body.classList.toggle('nav-open');
    event.currentTarget.setAttribute('aria-expanded', String(opened));
});
const editor = document.querySelector('[data-editor]');
let dirty = false;
editor?.addEventListener('input', () => {
    dirty = true;
    document.querySelector('[data-dirty-note]')?.removeAttribute('hidden');
});
editor?.addEventListener('submit', () => { dirty = false; });
window.addEventListener('beforeunload', (event) => {
    if (dirty) { event.preventDefault(); event.returnValue = ''; }
});
document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (dirty || !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            if (dirty) window.alert('Guarda primero tus cambios como borrador.');
        }
    });
});
