export function initMedia(document) {
    document.querySelectorAll('[data-media-select]').forEach((select) => {
        select.addEventListener('change', () => {
            const image = document.getElementById(select.dataset.previewId);
            const selected = select.selectedOptions[0];
            if (image && selected?.dataset.src) image.src = selected.dataset.src;
        });
    });
    const form = document.querySelector('[data-media-upload]');
    if (!form) return;
    const button = form.querySelector('[data-upload-button]');
    const label = form.querySelector('[data-upload-label]');
    let uploading = false;
    form.addEventListener('submit', (event) => {
        if (uploading) { event.preventDefault(); return; }
        uploading = true;
        button.disabled = true;
        label.textContent = 'Subiendo y optimizando…';
        form.setAttribute('aria-busy', 'true');
    });
    document.defaultView.addEventListener('pageshow', () => {
        uploading = false;
        button.disabled = false;
        label.textContent = 'Subir fotografía';
        form.removeAttribute('aria-busy');
    });
}
