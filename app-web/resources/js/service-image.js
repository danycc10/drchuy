export function initServiceImage(document) {
    const section = document.querySelector('[data-service-image]');
    if (!section) return;
    const window = document.defaultView;
    const form = section.closest('form');
    const select = section.querySelector('[data-media-select]');
    const preview = section.querySelector('[data-crop-preview]');
    const axes = [...section.querySelectorAll('[data-crop-axis]')];
    const updateCrop = () => {
        const option = select.selectedOptions[0];
        preview.src = option.dataset.display || option.dataset.src;
        preview.style.objectPosition = select.value ? axes.map((axis) => `${axis.value}%`).join(' ') : '50% 50%';
    };
    axes.forEach((axis) => axis.addEventListener('input', updateCrop));
    select.addEventListener('change', updateCrop);
    updateCrop();
    if (!window.fetch || !window.FormData || !window.AbortController) return;
    section.querySelector('[data-inline-upload]').hidden = false;
    const button = section.querySelector('[data-inline-upload-button]');
    const status = section.querySelector('[data-upload-status]');
    const file = section.querySelector('[data-upload-file]');
    const title = section.querySelector('[data-upload-title]');
    let pending = false;
    let controller;
    form.addEventListener('submit', (event) => {
        if (!pending) return;
        event.preventDefault();
        status.textContent = 'Espera a que termine la carga antes de guardar.';
        status.focus();
    }, true);
    button.addEventListener('click', async () => {
        if (pending) return;
        const image = file.files[0];
        if (!image || !title.value.trim()) {
            status.textContent = 'Escribe un nombre y selecciona una imagen.';
            (!title.value.trim() ? title : file).focus();
            return;
        }
        if (image.size > 8 * 1024 * 1024) {
            status.textContent = 'La imagen debe pesar como máximo 8 MB.';
            file.focus();
            return;
        }
        pending = true;
        button.disabled = true;
        section.setAttribute('aria-busy', 'true');
        status.textContent = 'Subiendo y optimizando imagen…';
        controller = new window.AbortController();
        const body = new window.FormData();
        body.append('_token', form.querySelector('[name="_token"]').value);
        body.append('title', title.value.trim());
        body.append('image', image);
        try {
            const response = await window.fetch(section.dataset.uploadUrl, { method: 'POST', body, headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: controller.signal });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = response.status === 419 || response.status === 401
                    ? 'Tu sesión venció. Conserva tus textos antes de volver a iniciar sesión.'
                    : Object.values(data.errors || {}).flat()[0] || data.message || 'No se pudo subir. Revisa la conexión e intenta de nuevo.';
                throw new Error(message);
            }
            const option = new window.Option(data.title, String(data.id), false, true);
            option.dataset.src = data.thumb;
            option.dataset.display = data.url;
            select.add(option);
            select.value = String(data.id);
            axes.forEach((axis) => { axis.value = '50'; });
            select.dispatchEvent(new window.Event('change', { bubbles: true }));
            section.dispatchEvent(new window.Event('image:uploaded'));
            file.value = '';
            title.value = '';
            status.textContent = 'Imagen seleccionada. Ajusta el encuadre y guarda el borrador.';
        } catch (error) {
            status.textContent = error.name === 'AbortError' ? 'Carga interrumpida. Revisa la biblioteca antes de volver a subir.' : error.message;
        } finally {
            pending = false;
            button.disabled = false;
            section.removeAttribute('aria-busy');
        }
    });
    window.addEventListener('pagehide', () => controller?.abort());
}
